package tracking.statuses.processed

import database.Creator

data class ProcessedOffersStatuses(
    val creator: Creator,
    val items: Set<ProcessedOfferStatus>,
    val issues: Boolean,
)
