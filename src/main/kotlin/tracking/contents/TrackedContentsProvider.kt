package tracking.contents

import config.Configuration
import io.github.oshai.kotlinlogging.KotlinLogging
import tracking.website.Strategy
import web.client.CookieEagerHttpClient
import web.client.FastHttpClient
import web.client.GentleHttpClient
import web.snapshots.Snapshot
import web.snapshots.SnapshotsManager
import web.url.Url
import kotlin.math.roundToInt

private val MAX_SIZE: Int = (1.5 * 1024 * 1024).roundToInt()

private val logger = KotlinLogging.logger {}

class TrackedContentsProvider(config: Configuration) {
    private val httpClient = CookieEagerHttpClient(GentleHttpClient(FastHttpClient()))
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

                val contents = if (size > MAX_SIZE || httpCode != 200) {
                    logger.warn("Skipping contents for ${snapshot.metadata.url} with HTTP code $httpCode and size $size")

                    ""
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

    private fun getSnapshotFromUrl(url: Url): Pair<Url, Snapshot> = url to snapshotsManager.get(url, httpClient::get)

    private fun getUrlForTracking(url: Url): Url = url.getStrategy().getUrlForTracking(url)
}
