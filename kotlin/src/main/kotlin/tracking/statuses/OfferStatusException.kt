package tracking.statuses

class OfferStatusException(message: String) : Exception(message) {
    fun requireMessage() = message ?: throw IllegalStateException("${OfferStatusException::class} requires a message")

    companion object {
        fun missingStatus() = OfferStatusException("Did not detect status")
        fun missingOffer() = OfferStatusException("Did not detect offer")
        fun multipleStatuses() = OfferStatusException("Detected multiple statuses")
        fun repeatedOffer() = OfferStatusException("The same offer has been matched multiple times")
    }
}
