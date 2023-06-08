package tracking

import data.CreatorItems
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
        val provider = SnapshotsProvider()
        val processor = Processor()
        val dbState = DbState.getAsOfNow()
        val updater = Updater(dbState)

        provider
            .getSnapshotsStream()
            .map(::filterAndConvertSnapshotsToProcessedItems)
            .map(processor::process)
            .map(updater::save)
            .collect(Collectors.counting())

        dbState.finalize()
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
