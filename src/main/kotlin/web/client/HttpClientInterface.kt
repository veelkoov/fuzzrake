package web.client

import web.snapshots.Snapshot

interface HttpClientInterface {
    fun get(url: String): Snapshot
}
