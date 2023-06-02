package tracking

import tracking.contents.ProcessedItem
import tracking.creator.CreatorItems
import tracking.steps.SnapshotsProvider
import tracking.steps.Detector
import tracking.steps.Updater
import tracking.steps.Preprocessor
import tracking.website.Strategy
import java.util.stream.Collectors

class Processor {
    private val provider = SnapshotsProvider()
    private val preprocessor = Preprocessor()
    private val detector = Detector()
    private val updater = Updater()

    fun run() {
        provider.get()
            .map { snapshots ->
                val items = snapshots.items.map {
                    ProcessedItem(
                        it.url,
                        it.contents,
                        snapshots.creator,
                        Strategy.forUrl(it.url)
                    )
                }
                // TODO: Reject texts > 1 MiB

                items.forEach { preprocessor.preprocess(it) }

                CreatorItems(snapshots.creator, items)
            }
            .map { texts ->
                detector.detectIn(texts)
            }
            .map { statuses ->
                updater.save(statuses)
            }
            .collect(Collectors.toList())
    }
}
