package web.client

import web.snapshots.Snapshot
import web.url.Url

interface HttpClientInterface {
    fun fetch(url: Url): Snapshot
}
