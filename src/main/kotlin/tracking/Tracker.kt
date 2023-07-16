package tracking

import config.Configuration
import tracking.contents.CreatorItems
import data.ThreadSafe
import database.Database
import database.entities.Creator
import database.helpers.aliases
import database.helpers.lastCreatorId
import database.tables.CreatorUrls
import database.tables.Creators
import org.jetbrains.exposed.dao.with
import tracking.contents.CreatorData
import tracking.contents.TrackedContentsProvider
import tracking.processing.Processor
import web.url.CreatorUrl
import web.url.Url
import java.util.stream.Collectors
import database.entities.CreatorUrl as CreatorUrlEntity

class Tracker(
    private val config: Configuration,
    private val options: TrackerOptions,
    private val provider: TrackedContentsProvider = TrackedContentsProvider(config, options),
    private val database: Database = Database(config.databasePath)
) {
    private val processor = Processor()
    private val updater = Updater()

    fun run() {
        database.transaction {
            val creatorsTrackingUrls = getCreatorsTrackingUrls()

            creatorsTrackingUrls
                .map { (creator, urls) -> getCreatorItemsUrlsFrom(creator, urls) }
                // Multithreading starts. Since now, no DB operations can take place.
                .parallelStream()
                .map(provider::createProcessedItems)
                .map(processor::process)
                .collect(Collectors.toList())
                // Multithreading ends. DB operations allowed.
                .map(updater::save)

            creatorsTrackingUrls.forEach { (_, urls) -> urls.forEach(CreatorUrl::commit) }
        }
    }

    private fun getCreatorsTrackingUrls(): Map<Creator, List<CreatorUrl>> { // TODO: Yuck
        val withUrls: Map<Creator, List<CreatorUrl>> =
            CreatorUrlEntity.find { CreatorUrls.type eq "URL_COMMISSIONS" } // TODO: Enum!
                .with(CreatorUrlEntity::creator, CreatorUrlEntity::states, Creator::creatorIds, Creator::volatileData, Creator::offersStatuses)
                .groupBy { it.creator }.mapValues { (_, urls) -> urls.map { CreatorUrl(ThreadSafe(it), it.url) } }

        val withoutUrls: Map<Creator, List<CreatorUrl>> =
            Creator.find { Creators.id notInList withUrls.keys.map { it.id } }
                .with(Creator::offersStatuses, Creator::volatileData)
                .associateWith { listOf() }

        return withUrls.plus(withoutUrls)
    }

    private fun getCreatorItemsUrlsFrom(creator: Creator, urls: List<CreatorUrl>): CreatorItems<Url> {
        return CreatorItems(CreatorData(creator.lastCreatorId(), creator.aliases(), ThreadSafe(creator)), urls)
    }
}
