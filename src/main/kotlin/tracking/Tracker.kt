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
import database.entities.CreatorUrl as CreatorUrlEntity

class Tracker(private val config: Configuration) {
    private val provider = TrackedContentsProvider(config)
    private val processor = Processor()

    fun run() {
        val database = Database(config.databasePath)

        database.transaction {
            val creatorsTrackingUrls: Map<Creator, List<CreatorUrlEntity>> =
                CreatorUrls.findTracking().groupBy { it.creator }

            val updater = Updater(DbState.getAsOfNow(creatorsTrackingUrls.keys))

            creatorsTrackingUrls
                .map { (creator, urls) -> getCreatorItemsUrlsFrom(creator, urls) }
                .parallelStream()
                .map(provider::createProcessesItems)
                .map(processor::process)
                .collect(Collectors.toList())
                .map(updater::save)

            updater.finalize()
        }
    }

    private fun getCreatorItemsUrlsFrom(creator: Creator, urls: List<CreatorUrlEntity>): CreatorItems<Url> {
        return CreatorItems(ThreadSafe(creator), creator.lastCreatorId(), creator.aliases(), urls.map { CreatorUrl(it) })
    }
}
