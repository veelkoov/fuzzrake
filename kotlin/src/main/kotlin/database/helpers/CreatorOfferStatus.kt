package database.helpers

import database.entities.CreatorOfferStatus
import tracking.statuses.OfferStatus
import tracking.statuses.Status

fun CreatorOfferStatus.toOfferStatus() = OfferStatus(offer, Status.fromBoolean(isOpen))
