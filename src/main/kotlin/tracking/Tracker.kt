package tracking

import tracking.contents.ProcessedItem
import tracking.contents.Snapshot
import data.CreatorItems
import tracking.steps.Processor
import tracking.steps.SnapshotsProvider
import tracking.steps.Updater
import tracking.website.Strategy
import java.util.stream.Collectors

class Tracker {
    private val provider = SnapshotsProvider()
    private val processor = Processor()
    private val updater = Updater()

    fun run() {
        provider
            .getSnapshotsStream()
            .map(::filterAndConvertSnapshotsToProcessedItems)
            .map(processor::process)
            .map(updater::save)
            .collect(Collectors.counting())
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
