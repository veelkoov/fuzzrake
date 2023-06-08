package tracking

import data.CreatorItems
import database.Database
import tracking.contents.ProcessedItem
import tracking.contents.Snapshot
import tracking.steps.Processor
import tracking.steps.SnapshotsProvider
import tracking.steps.Updater
import tracking.updating.DbState
import tracking.website.Strategy
import java.util.stream.Collectors

class Tracker {
    fun run() {
        Database.transaction {
            val provider = SnapshotsProvider()
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
                it.url,
                it.contents,
                snapshots.creator,
                Strategy.forUrl(it.url)
            )
        }
        // TODO: Reject texts > 1 MiB

        return CreatorItems(snapshots.creator, items)
    }
}
