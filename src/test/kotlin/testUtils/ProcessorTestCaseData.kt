package testUtils

import tracking.statuses.OfferStatus

data class ProcessorTestCaseData(
    val name: String,
    val input: String,
    val offersStatuses: Set<OfferStatus>,
)
