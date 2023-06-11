package database

import tracking.statuses.OfferStatus
import tracking.statuses.Status

fun CreatorOfferStatus.toOfferStatus() = OfferStatus(offer, Status.fromBoolean(isOpen))
