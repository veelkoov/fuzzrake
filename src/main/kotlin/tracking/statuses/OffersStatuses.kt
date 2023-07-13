package tracking.statuses

import web.url.Url

data class OffersStatuses(
    val items: Set<OfferStatus>,
    val issues: Boolean,
    val sourceUrls: List<Url>,
)
