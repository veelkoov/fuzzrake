package web.client

import io.github.oshai.kotlinlogging.KotlinLogging
import io.ktor.client.*
import io.ktor.client.call.*
import io.ktor.client.engine.java.*
import io.ktor.client.network.sockets.*
import io.ktor.client.plugins.*
import io.ktor.client.plugins.compression.*
import io.ktor.client.plugins.cookies.*
import io.ktor.client.request.*
import io.ktor.util.*
import kotlinx.coroutines.runBlocking
import time.UTC
import web.snapshots.Snapshot
import web.snapshots.SnapshotMetadata
import web.url.Url
import java.net.UnknownHostException
import javax.net.ssl.SSLHandshakeException

private val logger = KotlinLogging.logger {}

class FastHttpClient : HttpClientInterface {
    private val client = HttpClient(Java) {
        /*install(Logging) {
            this.sanitizeHeader {
                it.equals("content-security-policy", true) // SPAM
            }
        }*/
        install(HttpCookies)
        install(ContentEncoding) {
            // deflate(1.0F)
            gzip(0.9F)
        }
        install(UserAgent) {
            agent = "Mozilla/5.0 (compatible; GetFursuitBot/0.10; Ktor/Apache5; +https://getfursu.it/)"
        }
        install(HttpTimeout) {
            requestTimeoutMillis = 30_000
        }
    }

    override fun get(url: Url): Snapshot {
        var contents = ""
        var headers = mapOf<String, List<String>>()
        var httpCode = 0
        val errors = mutableListOf<String>()
        var exception: Throwable? = null

        try {
            runBlocking {
                logger.info("Retrieving: '${url.getUrl()}'")

                val response = client.request(url.getUrl())

                logger.info("Got response: '${url.getUrl()}'")

                contents = response.body<String>()
                headers = response.headers.toMap()
                httpCode = url.getStrategy().getLatentCode(url, contents, response.status.value)
            }
        } catch (caught: UnknownHostException) {
            exception = caught
        } catch (caught: ConnectTimeoutException) {
            exception = caught
        } catch (caught: SocketTimeoutException) {
            exception = caught
        } catch (caught: SSLHandshakeException) {
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
}
