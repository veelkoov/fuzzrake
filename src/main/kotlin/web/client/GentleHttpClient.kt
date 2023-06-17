package web.client

import web.snapshots.Snapshot

class GentleHttpClient : HttpClientInterface {
    private val client = FastHttpClient()
    private val queue = HostCallsQueue()

    override fun get(url: String): Snapshot {
        return queue.patiently(url) {
            client.get(url)
        }
    }
}
