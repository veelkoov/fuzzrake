package tracking.updating

import data.CreatorItem
import database.helpers.lastCreatorId
import database.entities.Creator
import database.entities.CreatorOfferStatus
import database.entities.CreatorVolatileData
import database.helpers.toOfferStatus
import io.github.oshai.kotlinlogging.KotlinLogging
import time.UTC
import tracking.statuses.Offer
import tracking.statuses.OffersStatuses
import java.util.Objects

private val logger = KotlinLogging.logger {}

class Updater(private val dbState: DbState) {
    fun save(statuses: CreatorItem<OffersStatuses>) {
        updateOffersStatuses(statuses.creator.get(), statuses.item)
        updateVolatileData(statuses.creator.get(), statuses.item)
    }

    private fun updateVolatileData(creator: Creator, newOffersStatuses: OffersStatuses) {
        val dbEntity: CreatorVolatileData = dbState.getVolatileDataOf(creator)

        dbEntity.csTrackerIssue = getNewValLogged(creator, dbEntity.csTrackerIssue, newOffersStatuses.issues, "Encountered issues")
        dbEntity.lastCsUpdateUtc = UTC.Now.dateTime() // This is technically the time it got updated even though the webpage was checked a few minutes ago
    }

    private fun updateOffersStatuses(creator: Creator, newOffersStatuses: OffersStatuses) {
        val remainingExistingEntities: MutableMap<Offer, CreatorOfferStatus> =
            dbState.consumeCreatorOfferStatuses(creator).associateBy { it.offer }.toMutableMap()

        newOffersStatuses.items.forEach { newOfferStatus ->
            val entity = remainingExistingEntities.remove(newOfferStatus.offer)

            if (entity == null) {
                getNewValLogged(creator, null, newOfferStatus, "Offer status")

                CreatorOfferStatus.new {
                    this.creator = creator
                    offer = newOfferStatus.offer
                    isOpen = newOfferStatus.status.isOpen()
                }
            } else {
                val oldOfferStatus = entity.toOfferStatus()
                getNewValLogged(creator, oldOfferStatus, newOfferStatus, "Offer status")

                entity.isOpen = newOfferStatus.status.isOpen()
            }
        }

        remainingExistingEntities.forEach { (_, entity) ->
            getNewValLogged(creator, entity.toOfferStatus(), null, "Offer status")
            entity.delete()
        }
    }

    private fun <T> getNewValLogged(creator: Creator, oldValue: T, newValue: T, description: String): T {
        if (Objects.equals(oldValue, newValue)) {
            return oldValue
        }

        if (oldValue == null) {
            logger.info("${creator.lastCreatorId()} $description: added $newValue")
        } else if (newValue == null) {
            logger.info("${creator.lastCreatorId()} $description: removed $oldValue")
        } else {
            logger.info("${creator.lastCreatorId()} $description: changed from $oldValue to $newValue")
        }

        return newValue
    }

    fun finalize() {
        dbState.getUnconsumedCreatorOfferStatuses().map(CreatorOfferStatus::delete)
    }
}
