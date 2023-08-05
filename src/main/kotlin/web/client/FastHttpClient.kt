package web.client

import io.github.oshai.kotlinlogging.KotlinLogging
import io.ktor.client.*
import io.ktor.client.call.*
import io.ktor.client.engine.*
import io.ktor.client.engine.java.*
import io.ktor.client.plugins.*
import io.ktor.client.plugins.compression.*
import io.ktor.client.plugins.cookies.*
import io.ktor.client.plugins.logging.*
import io.ktor.client.request.*
import io.ktor.client.statement.*
import io.ktor.http.*
import io.ktor.util.*
import kotlinx.coroutines.runBlocking
import time.UTC
import web.snapshots.Snapshot
import web.snapshots.SnapshotMetadata
import web.url.Url
import java.io.IOException

private val logger = KotlinLogging.logger {}

class FastHttpClient(
    client: HttpClient? = null,
) : HttpClientInterface {
    private val client: HttpClient = client ?: HttpClient(Java, getConfig())

    override fun fetch(url: Url, method: HttpMethod, addHeaders: Map<String, String>, payload: String?): Snapshot {
        var contents = ""
        var headers = mapOf<String, List<String>>()
        var httpCode = 0
        val errors = mutableListOf<String>()
        var exception: Throwable? = null

        try {
            runBlocking {
                logger.info("Retrieving: '${url.getUrl()}'")

                val response = client.request(url.getUrl()) {
                    this.method = method

                    addHeaders.forEach { (name, value) -> this.headers.append(name, value) }

                    payload?.let { setBody(it) }
                }

                logger.info("Got response: '${url.getUrl()}'")

                contents = response.body<String>()
                headers = response.headers.toMap()
                httpCode = correctHttpCode(url, contents, response)
            }
        } catch (caught: IOException) {
            exception = caught
        }

        if (exception != null) {
            logger.info("Exception caught during retrieving: '${url.getUrl()}'; $exception")

            errors.add("Exception: ${exception.message ?: exception.toString()}")
        } else if (200 != httpCode) {
            logger.info("Non-200 HTTP code ($httpCode): '${url.getUrl()}'")

            errors.add("HTTP status code $httpCode")
        }

        if (errors.isEmpty()) {
            url.recordSuccessfulFetch()
        } else {
            url.recordFailedFetch(httpCode, errors.joinToString(" / "))
        }

        val metadata = SnapshotMetadata(
            url.getUrl(),
            "", // FIXME
            UTC.Now.dateTime().toString(), // FIXME
            httpCode,
            headers,
            0, // No chained retrievals currently
            errors.toList(),
        )

        return Snapshot(contents, metadata)
    }

    override fun getSingleCookieValue(url: String, cookieName: String): String? = runBlocking {
        client.cookies(url)
            .filter { it.name.equals(cookieName, true) }
            .map { it.value }
            .singleOrNull()
            ?.decodeURLPart()
    }

    private fun correctHttpCode(url: Url, contents: String, response: HttpResponse): Int {
        val originalCode = response.status.value
        val correctedCode = url.getStrategy().getLatentCode(url, contents, originalCode)

        if (correctedCode != originalCode) {
            logger.info("Correcting HTTP code from $originalCode to 404 for ${url.getUrl()}")
        }

        return correctedCode
    }

    companion object {
        fun getConfig(
            logging: Boolean = false,
            retries: Boolean = false,
        ): HttpClientConfig<out HttpClientEngineConfig>.() -> Unit {
            return {
                if (logging) {
                    install(Logging) {
                        this.sanitizeHeader {
                            it.equals("content-security-policy", true) // SPAM
                        }
                        this.level = LogLevel.ALL
                    }
                }
                install(HttpCookies)
                install(ContentEncoding) {
                    // deflate(1.0F)
                    gzip(0.9F)
                }
                install(UserAgent) {
                    agent = "Mozilla/5.0 (compatible; GetFursuitBot/0.10; Ktor/Java; +https://getfursu.it/)"
                }
                install(HttpTimeout) {
                    requestTimeoutMillis = 30_000
                }
                if (retries) {
                    install(HttpRequestRetry) {
                        retryOnServerErrors(maxRetries = 1)
                        constantDelay(millis = 6_000, randomizationMs = 2_000)
                    }
                }
            }
        }
    }
}
