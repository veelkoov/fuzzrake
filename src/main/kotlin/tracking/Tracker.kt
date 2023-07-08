package tracking

import config.Configuration
import data.CreatorItems
import data.ThreadSafe
import database.Database
import database.entities.Creator
import database.helpers.aliases
import database.helpers.findTracking
import database.helpers.lastCreatorId
import database.tables.CreatorUrls
import tracking.contents.TrackedContentsProvider
import tracking.processing.Processor
import tracking.updating.Updater
import tracking.updating.DbState
import web.url.CreatorUrl
import web.url.Url
import java.util.stream.Collectors

class Tracker(private val config: Configuration) {
    private val provider = TrackedContentsProvider(config)
    private val processor = Processor()

    fun run() {
        val database = Database(config.databasePath)

        database.transaction {
            val creatorsTrackingUrls: Map<Creator, List<CreatorUrl>> =
                CreatorUrls.findTracking().groupBy { it.creator }.mapValues { (_, urls) ->
                    urls.map { CreatorUrl(ThreadSafe(it), it.url) }
                }

            val updater = Updater(DbState.getAsOfNow(creatorsTrackingUrls.keys))

            creatorsTrackingUrls
                .map { (creator, urls) -> getCreatorItemsUrlsFrom(creator, urls) }
                .parallelStream()
                .map(provider::createProcessesItems)
                .map(processor::process)
                .collect(Collectors.toList())
                .map(updater::save)

            updater.finalize()
            creatorsTrackingUrls.forEach { (_, urls) -> urls.forEach(CreatorUrl::commit) }
        }
    }

    private fun getCreatorItemsUrlsFrom(creator: Creator, urls: List<CreatorUrl>): CreatorItems<Url> {
        return CreatorItems(ThreadSafe(creator), creator.lastCreatorId(), creator.aliases(), urls)
    }
}
