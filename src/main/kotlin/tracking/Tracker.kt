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
import web.url.ThreadSafeCreatorUrl
import web.url.Url
import java.util.stream.Collectors
import database.entities.CreatorUrl as CreatorUrlEntity

class Tracker( // TODO: Remove the leftovers in the database
    private val config: Configuration,
    private val options: TrackerOptions,
    private val provider: TrackedContentsProvider = TrackedContentsProvider(config, options),
    private val database: Database = Database(config.databasePath)
) {
    private val processor = Processor()
    private val updater = Updater()

    fun run() = database.transaction {
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

        creatorsTrackingUrls.forEach { (_, urls) -> urls.forEach(ThreadSafeCreatorUrl::commit) }
    }

    private fun getCreatorsTrackingUrls(): Map<Creator, List<ThreadSafeCreatorUrl>> { // TODO: Yuck
        val withUrls: Map<Creator, List<ThreadSafeCreatorUrl>> =
            CreatorUrlEntity.find { CreatorUrls.type eq "URL_COMMISSIONS" } // TODO: Enum!
                .with(CreatorUrlEntity::creator, CreatorUrlEntity::states, Creator::creatorIds, Creator::volatileData, Creator::offersStatuses)
                .groupBy { it.creator }.mapValues { (_, urls) -> urls.map { ThreadSafeCreatorUrl(it) } }

        val withoutUrls: Map<Creator, List<ThreadSafeCreatorUrl>> =
            Creator.find { Creators.id notInList withUrls.keys.map { it.id } }
                .with(Creator::offersStatuses, Creator::volatileData)
                .associateWith { listOf() }

        return withUrls.plus(withoutUrls)
    }

    private fun getCreatorItemsUrlsFrom(creator: Creator, urls: List<ThreadSafeCreatorUrl>): CreatorItems<Url> {
        return CreatorItems(CreatorData(creator.lastCreatorId(), creator.aliases(), ThreadSafe(creator)), urls)
    }
}
