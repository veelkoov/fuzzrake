package web.client

import io.ktor.http.*
import web.client.utils.HostCallsQueue
import web.snapshots.Snapshot
import web.url.Url

class GentleHttpClient(private val client: HttpClientInterface) : HttpClientInterface {
    private val queue = HostCallsQueue()

    override fun fetch(url: Url, method: HttpMethod, addHeaders: Map<String, String>, payload: String?): Snapshot {
        return queue.patiently(url) {
            client.fetch(url, method, addHeaders, payload)
        }
    }

    override fun getSingleCookieValue(url: String, cookieName: String) = client.getSingleCookieValue(url, cookieName)
}
