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
        logger.info("Retrieving: '${url.getUrl()}'")

        try {
            val result = runBlocking {
                val response = client.request(url.getUrl())

                val contents = response.body<String>()
                val httpCode = url.getStrategy().getLatentCode(url, contents, response.status.value)

                val metadata = SnapshotMetadata(
                    url.getUrl(),
                    "", // FIXME
                    UTC.Now.dateTime().toString(), // FIXME
                    httpCode,
                    response.headers.toMap(),
                    0, // No chained retrievals currently
                    listOf(), // No errors
                )

                Snapshot(contents, metadata)
            }

            logger.info("Retrieved: '${url.getUrl()}'")

            return result
        } catch (exception: UnknownHostException) {
            return getFromException(url, exception)
        } catch (exception: ConnectTimeoutException) {
            return getFromException(url, exception)
        } catch (exception: SocketTimeoutException) {
            return getFromException(url, exception)
        }
    }

    private fun getFromException(url: Url, exception: Throwable): Snapshot {
        logger.info("Failed retrieving: '${url.getUrl()}'; $exception")

        return Snapshot.forError(url.getUrl(), "", exception.message ?: exception.toString())
    }
}
