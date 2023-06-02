package tracking.detection

import tracking.statuses.Offer
import tracking.statuses.OfferStatus
import tracking.statuses.OfferStatusException
import tracking.statuses.Status

class MatchedGroups {
    fun detectIn(matchedGroups: List<Pair<String, String>>): List<OfferStatus> {
        var offers: List<Offer>? = null
        var status: Status? = null

        matchedGroups.forEach { (name, _) ->
            if (Status.isStatusGroup(name)) {
                if (null != status) {
                    throw OfferStatusException.multipleStatuses()
                }

                status = Status.fromGroupName(name)
            } else {
                if (null != offers) {
                    throw OfferStatusException.multipleOffers() // TODO: Which offers?
                }

                offers = GroupNamesResolver().offersFrom(name)
            }
        }

        if (offers == null) {
            throw OfferStatusException.missingOffer()
        }

        if (status == null) {
            throw OfferStatusException.missingStatus()
        }

        return offers!!.map { OfferStatus(it, status!!) }
    }
}
