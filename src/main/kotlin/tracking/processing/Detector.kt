package tracking.processing

import tracking.contents.CreatorItems
import io.github.oshai.kotlinlogging.KotlinLogging
import tracking.contents.ProcessedItem
import tracking.patterns.Factory
import tracking.statuses.Offer
import tracking.statuses.OfferStatus
import tracking.statuses.OfferStatusException
import tracking.statuses.OffersStatuses
import tracking.statuses.processed.ProcessedOfferStatus
import tracking.statuses.processed.ProcessedOffersStatuses
import tracking.statuses.processed.ProcessedStatus

private val logger = KotlinLogging.logger {}

class Detector {
    private val matchers = Factory.getOffersStatuses()
    private val analyser = GroupNamesAnalyser()

    fun detectIn(input: CreatorItems<ProcessedItem>): OffersStatuses {
        val theCreatorId = input.getCreatorId()

        val offerToStatus = mutableMapOf<Offer, ProcessedStatus>()
        var issues = false

        input.items.forEach { item ->
            val allDetectedOs = detectIn(item)
            issues = issues || allDetectedOs.issues

            allDetectedOs.items.forEach { oneDetectedOs ->
                val nextOffer = oneDetectedOs.offer
                val nextStatus = oneDetectedOs.status

                when (offerToStatus[nextOffer]) {
                    null -> {
                        offerToStatus[nextOffer] = nextStatus
                    }

                    ProcessedStatus.CONFLICT -> {
                    }

                    nextStatus -> { // Two different URLs hold the same offer status - OK
                    }

                    else -> {
                        offerToStatus[nextOffer] = ProcessedStatus.CONFLICT
                        issues = true
                        logger.warn("$theCreatorId Contradicting offer statuses for '$nextOffer'")
                    }
                }
            }
        }

        return OffersStatuses(osMapToSet(offerToStatus), issues, input.items.map { it.url })
    }

    private fun detectIn(input: ProcessedItem): ProcessedOffersStatuses {
        val theCreatorId = input.getCreatorId()
        val theUrl = input.url.getUrl()

        val offerToStatus = mutableMapOf<Offer, ProcessedStatus>()
        var issues = false

        input.contents = matchers.matchIn(input.contents) { match ->
            val allDetectedOs: List<OfferStatus>

            try {
                allDetectedOs = analyser.detectIn(match.groups)
            } catch (exception: OfferStatusException) {
                issues = true
                logger.warn("$theCreatorId $theUrl: ${exception.requireMessage()}")

                return@matchIn
            }

            allDetectedOs.forEach { oneDetectedOs ->
                val nextOffer = oneDetectedOs.offer
                val nextStatus = ProcessedStatus.from(oneDetectedOs.status)

                when (offerToStatus[nextOffer]) {
                    null -> {
                        offerToStatus[nextOffer] = nextStatus
                    }

                    ProcessedStatus.CONFLICT -> {
                    }

                    nextStatus -> {
                        issues = true
                        logger.warn("$theCreatorId $theUrl: Duplicated offer status for '$nextOffer'")
                    }

                    else -> {
                        offerToStatus[nextOffer] = ProcessedStatus.CONFLICT
                        issues = true
                        logger.warn("$theCreatorId $theUrl: Contradicting offer statuses for '$nextOffer'")
                    }
                }
            }
        }

        if (offerToStatus.isEmpty()) {
            issues = true
            logger.warn("$theCreatorId $theUrl: No statuses detected")
        }

        return ProcessedOffersStatuses(posMapToSet(offerToStatus), issues)
    }

    private fun posMapToSet(offerToStatus: MutableMap<Offer, ProcessedStatus>): Set<ProcessedOfferStatus> {
        return offerToStatus.map { (offer, status) -> ProcessedOfferStatus(offer, status) }.toSet()
    }

    private fun osMapToSet(offerToStatus: MutableMap<Offer, ProcessedStatus>): Set<OfferStatus> {
        return offerToStatus
            .filterNot { (_, status) ->
                status == ProcessedStatus.CONFLICT
            }
            .map { (offer, status) ->
                OfferStatus(offer, status.asStatus())
            }
            .toSet()
    }
}
