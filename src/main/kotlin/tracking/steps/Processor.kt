package tracking.steps

import data.CreatorItem
import data.CreatorItems
import tracking.contents.ProcessedItem
import tracking.processing.Detector
import tracking.processing.Preprocessor
import tracking.statuses.OffersStatuses

class Processor {
    private val preprocessor = Preprocessor()
    private val detector = Detector()

    fun process(items: CreatorItems<ProcessedItem>): CreatorItem<OffersStatuses> {
        items.items.forEach { preprocessor.preprocess(it) }

        return CreatorItem(items.creator, detector.detectIn(items))
    }
}
