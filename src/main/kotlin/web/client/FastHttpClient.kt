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
            socketTimeout = 10_000
            connectTimeout = 10_000
            connectionRequestTimeout = 20_000
        }
    }

    override fun get(url: String): Snapshot {
        logger.info("Retrieving: '$url'")

        try {
            val result = runBlocking {
                val response = client.request(url)

                val contents = response.body<String>()
                val metadata = SnapshotMetadata(
                    url,
                    "", // FIXME
                    UTC.Now.dateTime().toString(), // FIXME
                    response.status.value,
                    response.headers.toMap(),
                    0, // FIXME
                    listOf(), // FIXME
                )

                Snapshot(contents, metadata)
            }

            logger.info("Retrieved: '$url'")

            return result
        } catch (exception: UnknownHostException) {
            return getFromException(url, exception)
        } catch (exception: ConnectTimeoutException) {
            return getFromException(url, exception)
        } catch (exception: SocketTimeoutException) {
            return getFromException(url, exception)
        }
    }

    private fun getFromException(url: String, exception: Throwable): Snapshot {
        logger.info("Failed retrieving: '$url'; $exception")

        return Snapshot.forError(url, "", exception.message ?: exception.toString())
    }
}
