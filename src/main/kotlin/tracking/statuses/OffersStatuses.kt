package tracking.statuses

import tracking.creator.Creator

data class OffersStatuses(
    val creator: Creator, // TODO: Try to eliminate, wrap in CreatorItems?
    val items: Set<OfferStatus>,
    val issues: Boolean,
)
