package tracking.steps

import tracking.Text
import tracking.snapshots.CreatorItems
import tracking.statuses.OffersStatuses

class Detector {
    fun detect(texts: CreatorItems<Text>): OffersStatuses {
        return OffersStatuses(texts.creator, mapOf(), false) // TODO
    }
}
