package tracking.contents

import config.Configuration
import io.github.oshai.kotlinlogging.KotlinLogging
import tracking.TrackerOptions
import tracking.website.Strategy
import web.snapshots.Snapshot
import web.snapshots.SnapshotsManager
import web.url.Url
import kotlin.math.roundToInt

private val MAX_SIZE: Int = (1.0 * 1024 * 1024).roundToInt()

private val logger = KotlinLogging.logger {}

class TrackedContentsProvider(
    config: Configuration,
    private val options: TrackerOptions,
) {
    private val snapshotsManager = SnapshotsManager(config.snapshotsStoreDirPath)

    fun createProcessedItems(urls: CreatorItems<Url>): CreatorItems<ProcessedItem> {
        val items = getProcessedItems(urls)

        return CreatorItems(urls.creatorData, items)
    }

    private fun getProcessedItems(urls: CreatorItems<Url>): List<ProcessedItem> {
        return urls.items
            .map(::getUrlForTracking)
            .map(::getSnapshotFromUrl)
            .map { (url, snapshot) ->
                val httpCode = snapshot.metadata.httpCode
                val size = snapshot.contents.length // TODO: These are not bytes

                val contents = if (httpCode != 200) {
                    logger.warn { "Skipping contents of ${snapshot.metadata.url} with HTTP code $httpCode" }

                    ""
                } else if (size > MAX_SIZE) {
                    logger.warn { "Truncating contents of ${snapshot.metadata.url} from $size to $MAX_SIZE" }

                    snapshot.contents.take(MAX_SIZE)
                } else {
                    snapshot.contents
                }

                ProcessedItem(
                    urls.creatorData,
                    url,
                    Strategy.forUrl(snapshot.metadata.url),
                    contents
                )
            }
    }

    private fun getSnapshotFromUrl(url: Url): Pair<Url, Snapshot> {
        return url to snapshotsManager.get(url, options.refetch)
    }

    private fun getUrlForTracking(url: Url): Url = url.getStrategy().getUrlForTracking(url)
}
