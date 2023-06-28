package web.client

import web.snapshots.Snapshot
import web.url.Url

interface HttpClientInterface {
    fun get(url: Url): Snapshot
}
