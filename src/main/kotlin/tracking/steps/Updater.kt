package tracking.steps

import data.CreatorItem
import data.lastCreatorId
import database.Creator
import database.CreatorOfferStatus
import database.CreatorVolatileData
import io.github.oshai.kotlinlogging.KotlinLogging
import time.UTC
import tracking.statuses.Offer
import tracking.statuses.OffersStatuses
import tracking.updating.DbState

private val logger = KotlinLogging.logger {}

class Updater(private val dbState: DbState) {
    fun save(statuses: CreatorItem<OffersStatuses>) {
        logger.info("${statuses.creator.lastCreatorId()} Updating offers statuses: ${statuses.item}")

        updateVolatileData(statuses.creator, statuses.item)
        updateOffersStatuses(statuses.creator, statuses.item)
    }

    private fun updateVolatileData(creator: Creator, newOffersStatuses: OffersStatuses) {
        val dbEntity: CreatorVolatileData = dbState.getVolatileDataOf(creator)

        dbEntity.csTrackerIssue = newOffersStatuses.issues
        dbEntity.lastCsUpdateUtc = UTC.Now.dateTime() // TODO: Should this come from the snapshot retrieval time?
    }

    private fun updateOffersStatuses(creator: Creator, newOffersStatuses: OffersStatuses) {
        val remainingExistingEntities: MutableMap<Offer, CreatorOfferStatus> =
            dbState.consumeCreatorOfferStatuses(creator).associateBy { it.offer }.toMutableMap()

        newOffersStatuses.items.forEach { newOfferStatus ->
            val entity = remainingExistingEntities.remove(newOfferStatus.offer)
                ?: CreatorOfferStatus.new {
                    this.creator = creator
                    offer = newOfferStatus.offer
                }

            entity.isOpen = newOfferStatus.status.isOpen()
        }

        remainingExistingEntities.forEach { (_, unused) ->
            unused.delete()
        }
    }

    fun finalize() {
        dbState.getUnconsumedCreatorOfferStatuses().map(CreatorOfferStatus::delete)
    }
}
