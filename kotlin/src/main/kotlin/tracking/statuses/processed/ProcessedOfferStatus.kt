package tracking.statuses.processed

import tracking.statuses.Offer

data class ProcessedOfferStatus(
    val offer: Offer,
    val status: ProcessedStatus,
)
