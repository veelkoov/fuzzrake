package web.client

import io.github.oshai.kotlinlogging.KotlinLogging
import io.ktor.client.*
import io.ktor.client.call.*
import io.ktor.client.engine.apache5.*
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

private val logger = KotlinLogging.logger {}

class FastHttpClient : HttpClientInterface {
    private val client = HttpClient(Apache5) {
        // install(Logging) {
        //     this.sanitizeHeader {
        //         it.equals("content-security-policy", true) // SPAM
        //     }
        // }
        install(HttpCookies)
        install(ContentEncoding) {
            // deflate(1.0F)
            gzip(0.9F)
        }
        install(UserAgent) {
            agent = "Mozilla/5.0 (compatible; GetFursuitBot/0.10; Ktor/Apache5; +https://getfursu.it/)"
        }
        engine {
            socketTimeout = 30_000
            connectTimeout = 10_000
            connectionRequestTimeout = 10_000
        }
    }

    override fun get(url: Url): Snapshot {
        var contents = ""
        var headers = mapOf<String, List<String>>()
        var httpCode = 0
        val errors = mutableListOf<String>()
        var exception: Throwable? = null

        try {
            logger.info("Retrieving: '${url.getUrl()}'")

            runBlocking {
                val response = client.request(url.getUrl())

                contents = response.body<String>()
                headers = response.headers.toMap()
                httpCode = url.getStrategy().getLatentCode(url, contents, response.status.value)
            }

            logger.info("Retrieved: '${url.getUrl()}'")
        } catch (caught: UnknownHostException) {
            exception = caught
        } catch (caught: ConnectTimeoutException) {
            exception = caught
        } catch (caught: SocketTimeoutException) {
            exception = caught
        }

        if (exception != null) {
            logger.info("Failed retrieving: '${url.getUrl()}'; $exception")

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
