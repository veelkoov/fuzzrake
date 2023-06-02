package tracking.steps

import io.github.oshai.kotlinlogging.KotlinLogging
import tracking.contents.ProcessedItem
import tracking.creator.CreatorItems
import tracking.detection.GroupNamesResolver
import tracking.matchers.Factory
import tracking.matchers.Workarounds
import tracking.statuses.*

private val logger = KotlinLogging.logger {}

class Detector {
    private val matchers = Factory.getOffersStatuses()

    fun detectIn(items: CreatorItems<ProcessedItem>): OffersStatuses {
        var issues = false
        val offerToStatus = mutableMapOf<Offer, ProcessedStatus>()

        items.items.forEach { item ->
            val itemResult = detectIn(item)
            issues = issues || itemResult.issues

            itemResult.items.forEach { offerStatus ->
                val offer = offerStatus.offer
                val status = ProcessedStatus.from(offerStatus.status)

                // LOG
                if (offerToStatus.containsKey(offer)) {
                    if (offerToStatus[offer] != status) {
                        offerToStatus[offer] = ProcessedStatus.CONFLICT
                        issues = true
                    }
                } else {
                    offerToStatus[offer] = status
                }
            }
        }

        return OffersStatuses(items.creator, osMapToList(offerToStatus), issues)
    }

    private fun detectIn(item: ProcessedItem): OffersStatuses {
        var contents = item.contents
        val offerToStatus = mutableMapOf<Offer, ProcessedStatus>()
        var issues = false

        matchers.forEach { matcher ->
            while (true) {
                val match = matcher.matchIn(contents) ?: break
                val groups = Workarounds.matchedGroups(match, matcher.groups)
                val detected: List<OfferStatus>

                try {
                    detected = detectIn(groups)
                } catch (exception: OfferStatusException) {
                    logger.warn("${item.sourceUrl}: ${exception.requireMessage()}")
                    issues = true
                    continue
                }

                detected.forEach {
                    when (offerToStatus[it.offer]) {
                        null -> offerToStatus[it.offer] = ProcessedStatus.from(it.status)
                        ProcessedStatus.CONFLICT -> {}
                        ProcessedStatus.from(it.status) -> {
                            issues = true
                            // TODO: Log duplicated offer status
                        }
                        else -> {
                            issues = true
                            offerToStatus[it.offer] = ProcessedStatus.CONFLICT
                            // TODO: Log contradicting offer statuses
                        }
                    }
                }

                contents = contents.replaceFirst(match.value, "")
            }
        }

        if (offerToStatus.isEmpty()) {
            issues = true
            logger.warn("No statuses detected in '${item.sourceUrl}'") // TODO: Add maker and url
        }

        return OffersStatuses(item.creator, osMapToList(offerToStatus), issues)
    }

    private fun detectIn(matchedGroups: Map<String, String>): List<OfferStatus> {
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

    private fun osMapToList(offerToStatus: MutableMap<Offer, ProcessedStatus>): List<OfferStatus> {
        return offerToStatus
            .filterNot { (_, status) ->
                status == ProcessedStatus.CONFLICT
            }
            .map { (offer, status) ->
                OfferStatus(offer, status.asStatus())
            }
    }
}
