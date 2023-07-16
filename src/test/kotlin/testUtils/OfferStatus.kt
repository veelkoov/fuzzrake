package testUtils

import tracking.statuses.OfferStatus
import tracking.statuses.Status


fun String.toOfferStatus() = when (this[0]) {
    '+' -> OfferStatus(this.drop(1), Status.OPEN)
    '-' -> OfferStatus(this.drop(1), Status.CLOSED)
    else -> throw IllegalArgumentException()
}
