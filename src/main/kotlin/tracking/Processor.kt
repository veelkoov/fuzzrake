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
        provider.get().map { snapshots ->
            val texts = snapshots.items.map { Text(it.contents) }
            texts.forEach { it.unused = preprocessor.preprocess(it.unused, snapshots.creator.aliases) }

            CreatorItems(snapshots.creator, texts)
        }.map { texts ->
            detector.detect(texts)
        }.map { statuses ->
            updater.save(statuses)
        }
    }
}
