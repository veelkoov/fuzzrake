package web.client

import io.github.oshai.kotlinlogging.KotlinLogging
import io.ktor.http.*
import web.snapshots.Snapshot
import web.url.Url

private val logger = KotlinLogging.logger {}

class CookieEagerHttpClient(private val client: HttpClientInterface) : HttpClientInterface {
    private val prefetched = mutableSetOf<String>()

    override fun fetch(url: Url, method: HttpMethod, addHeaders: Map<String, String>, payload: String?): Snapshot {
        url.getStrategy().getCookieInitUrl()?.let { cookieUrl ->
            if (!prefetched.contains(cookieUrl.getUrl())) {
                logger.info("${url.getUrl()} requires prefetch of ${cookieUrl.getUrl()}")

                client.fetch(cookieUrl)
                prefetched.add(cookieUrl.getUrl())
            }
        }

        return client.fetch(url, method, addHeaders, payload)
    }

    override fun getSingleCookieValue(url: String, cookieName: String) = client.getSingleCookieValue(url, cookieName)
}
