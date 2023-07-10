package tracking.statuses

data class OffersStatuses(
    val items: Set<OfferStatus>,
    val issues: Boolean,
    val sourceUrls: List<String>,
)
