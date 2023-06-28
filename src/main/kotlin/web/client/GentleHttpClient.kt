package web.client

import web.client.utils.HostCallsQueue
import web.snapshots.Snapshot
import web.url.Url

class GentleHttpClient(private val client: HttpClientInterface) : HttpClientInterface {
    private val queue = HostCallsQueue()

    override fun get(url: Url): Snapshot {
        return queue.patiently(url) {
            client.get(url)
        }
    }
}
