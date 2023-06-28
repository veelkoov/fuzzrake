package tracking.contents

import config.Configuration
import data.CreatorItems
import tracking.website.Strategy
import web.client.CookieEagerHttpClient
import web.client.FastHttpClient
import web.client.GentleHttpClient
import web.snapshots.Snapshot
import web.snapshots.SnapshotsManager
import web.url.Url

class TrackedContentsProvider(config: Configuration) {
    private val httpClient = CookieEagerHttpClient(GentleHttpClient(FastHttpClient()))
    private val snapshotsManager = SnapshotsManager(config.snapshotsStoreDirPath)

    fun createProcessesItems(urls: CreatorItems<Url>): CreatorItems<ProcessedItem> {
        val items = getProcessedItems(urls)

        return CreatorItems(urls.creator, urls.creatorId, urls.creatorAliases, items)
    }

    private fun getProcessedItems(urls: CreatorItems<Url>): List<ProcessedItem> {
        return urls.items
            .map(::getUrlForTracking)
            .map(::getSnapshotFromUrl)
            // TODO: Reject texts > 1 MiB
            .map {
                ProcessedItem(
                    urls.creatorId,
                    urls.creatorAliases,
                    it.metadata.url,
                    Strategy.forUrl(it.metadata.url),
                    it.contents
                )
            }
    }

    private fun getSnapshotFromUrl(url: Url): Snapshot = snapshotsManager.get(url, httpClient::get)

    private fun getUrlForTracking(url: Url): Url = url.getStrategy().getUrlForTracking(url)
}
