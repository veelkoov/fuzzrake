package tracking.statuses

import tracking.creator.Creator

data class OffersStatuses(
    val creator: Creator,
    val items: List<OfferStatus>,
    val issues: Boolean,
)
