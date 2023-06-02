package tracking.steps

import io.github.oshai.kotlinlogging.KotlinLogging
import tracking.contents.ProcessedItem
import tracking.creator.CreatorItems
import tracking.steps.detection.MatchedGroups
import tracking.matchers.Factory
import tracking.matchers.Workarounds
import tracking.statuses.*

private val logger = KotlinLogging.logger {}

class Detector {
    private val matchers = Factory.getOffersStatuses()
    private val groups = MatchedGroups()

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
                contents = contents.replaceFirst(match.value, "")

                val groups = Workarounds.matchedGroups(match, matcher.groups)
                val detected: List<OfferStatus>

                try {
                    detected = this.groups.detectIn(groups)
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
            }
        }

        if (offerToStatus.isEmpty()) {
            issues = true
            logger.warn("No statuses detected in '${item.sourceUrl}'")
        }

        return OffersStatuses(item.creator, osMapToList(offerToStatus), issues)
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
