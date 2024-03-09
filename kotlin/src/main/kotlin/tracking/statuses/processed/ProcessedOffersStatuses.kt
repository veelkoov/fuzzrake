package tracking.statuses.processed

data class ProcessedOffersStatuses(
    val items: Set<ProcessedOfferStatus>,
    val issues: Boolean,
)
