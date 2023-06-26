package tracking.contents

import config.Configuration
import data.CreatorItems
import database.entities.CreatorUrl
import tracking.website.Strategy
import web.client.GentleHttpClient
import web.snapshots.Snapshot
import web.snapshots.SnapshotsManager

class TrackedContentsProvider(config: Configuration) {
    private val httpClient = GentleHttpClient()
    private val snapshotsManager = SnapshotsManager(config.snapshotsStoreDirPath)

    fun createProcessesItems(urls: CreatorItems<CreatorUrl>): CreatorItems<ProcessedItem> {
        val items = getProcessedItems(urls)

        return CreatorItems(urls.creator, urls.creatorId, urls.creatorAliases, items)
    }

    private fun getProcessedItems(urls: CreatorItems<CreatorUrl>): List<ProcessedItem> {
        return urls.items
            .map(::getCoercedUrl)
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

    private fun getSnapshotFromUrl(url: String): Snapshot = snapshotsManager.get(url, httpClient::get)

    private fun getCoercedUrl(it: CreatorUrl): String = Strategy.forUrl(it.url).coerceUrl(it.url)
}
