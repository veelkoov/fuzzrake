package tracking

import config.Configuration
import data.CreatorItems
import database.Database
import tracking.contents.ProcessedItem
import web.snapshots.Snapshot
import tracking.steps.Processor
import tracking.steps.SnapshotsProvider
import tracking.steps.Updater
import tracking.updating.DbState
import tracking.website.Strategy
import web.snapshots.SnapshotsManager
import java.util.stream.Collectors

class Tracker(private val config: Configuration) {
    fun run() {
        val database = Database(config.databasePath)

        database.transaction {
            val snapshotsManager = SnapshotsManager(config.snapshotsStoreDirPath)
            val provider = SnapshotsProvider(snapshotsManager)
            val processor = Processor()
            val updater = Updater(DbState.getAsOfNow(provider.getCreators()))

            provider
                .getSnapshotsStream()
                .map(::filterAndConvertSnapshotsToProcessedItems)
                .map(processor::process)
                .collect(Collectors.toList())
                .map(updater::save)

            updater.finalize()
        }
    }

    private fun filterAndConvertSnapshotsToProcessedItems(snapshots: CreatorItems<Snapshot>): CreatorItems<ProcessedItem> {
        val items = snapshots.items.map {
            ProcessedItem(
                snapshots.creator,
                snapshots.creatorId,
                it.metadata.url,
                Strategy.forUrl(it.metadata.url),
                it.contents
            )
        }
        // TODO: Reject texts > 1 MiB

        return CreatorItems(snapshots.creator, snapshots.creatorId, items)
    }
}
