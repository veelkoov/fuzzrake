package tracking.statuses

import tracking.Creator

data class OffersStatuses(
    val creator: Creator,
    val items: Map<String, OfferStatus>,
    val unsure: Boolean,
)
