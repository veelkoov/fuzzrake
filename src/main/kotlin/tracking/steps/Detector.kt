package tracking.steps

import io.github.oshai.kotlinlogging.KotlinLogging
import tracking.contents.ProcessedItem
import tracking.creator.CreatorItems
import tracking.matchers.Factory
import tracking.matchers.Workarounds
import tracking.statuses.*
import tracking.statuses.processed.*
import tracking.steps.detection.GroupNamesAnalyser

private val logger = KotlinLogging.logger {}

class Detector {
    private val matchers = Factory.getOffersStatuses()
    private val analyser = GroupNamesAnalyser()

    fun detectIn(input: CreatorItems<ProcessedItem>): OffersStatuses {
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
                        logger.warn("Contradicting offer statuses for '$nextOffer'") // TODO: Add creator ID
                    }
                }
            }
        }

        return OffersStatuses(input.creator, osMapToList(offerToStatus), issues)
    }

    private fun detectIn(input: ProcessedItem): ProcessedOffersStatuses {
        val offerToStatus = mutableMapOf<Offer, ProcessedStatus>()
        var issues = false

        input.contents = matchers.matchIn(input.contents) { match, matcher ->
            val groups = Workarounds.getMatchedGroups(match, matcher.groups)
            val allDetectedOs: List<OfferStatus>

            try {
                allDetectedOs = analyser.detectIn(groups)
            } catch (exception: OfferStatusException) {
                issues = true
                logger.warn("${input.sourceUrl}: ${exception.requireMessage()}")

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
                        logger.warn("${input.sourceUrl}: Duplicated offer status for '$nextOffer'")
                    }

                    else -> {
                        offerToStatus[nextOffer] = ProcessedStatus.CONFLICT
                        issues = true
                        logger.warn("${input.sourceUrl}: Contradicting offer statuses for '$nextOffer'")
                    }
                }
            }
        }

        if (offerToStatus.isEmpty()) {
            issues = true
            logger.warn("${input.sourceUrl}: No statuses detected")
        }

        return ProcessedOffersStatuses(input.creator, posMapToList(offerToStatus), issues)
    }

    private fun posMapToList(offerToStatus: MutableMap<Offer, ProcessedStatus>): List<ProcessedOfferStatus> {
        return offerToStatus.map { (offer, status) -> ProcessedOfferStatus(offer, status) }
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
