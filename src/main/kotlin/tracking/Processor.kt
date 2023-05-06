package tracking

import tracking.snapshots.CreatorItems
import tracking.steps.SnapshotsProvider
import tracking.steps.Detector
import tracking.steps.Updater
import tracking.steps.Preprocessor

class Processor {
    private val provider = SnapshotsProvider()
    private val preprocessor = Preprocessor()
    private val detector = Detector()
    private val updater = Updater()

    fun run() {
        provider.get().map { items ->
            val texts = items.items.map { item ->
                Text(item.contents, preprocessor.preprocess(item.contents))
            }

            CreatorItems(items.creator, texts)
        }.map { items ->
            detector.detect(items)
        }.map { statuses ->
            updater.save(statuses)
        }
    }
}
