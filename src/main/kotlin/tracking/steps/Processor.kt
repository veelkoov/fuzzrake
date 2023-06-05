package tracking.steps

import tracking.contents.ProcessedItem
import data.CreatorItems
import tracking.statuses.OffersStatuses
import tracking.processing.Detector
import tracking.processing.Preprocessor

class Processor {
    private val preprocessor = Preprocessor()
    private val detector = Detector()

    fun process(items: CreatorItems<ProcessedItem>): OffersStatuses {
        items.items.forEach { preprocessor.preprocess(it) }

        return detector.detectIn(items)
    }
}
