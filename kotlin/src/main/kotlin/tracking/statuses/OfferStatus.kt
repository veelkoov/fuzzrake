package tracking.statuses

data class OfferStatus(
    val offer: Offer,
    val status: Status,
) {
    override fun toString(): String {
        return (if (status.isOpen()) "+" else "-") + offer
    }
}
