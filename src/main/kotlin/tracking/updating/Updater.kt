package tracking.updating

import tracking.contents.CreatorItem
import data.ListChange
import data.pack
import database.entities.Creator
import database.entities.CreatorOfferStatus
import database.entities.CreatorVolatileData
import database.entities.Event
import database.helpers.*
import io.github.oshai.kotlinlogging.KotlinLogging
import time.UTC
import tracking.statuses.Offer
import tracking.statuses.OfferStatus
import tracking.statuses.OffersStatuses
import java.util.Objects

private val logger = KotlinLogging.logger {}

class Updater {
    fun save(statuses: CreatorItem<OffersStatuses>) {
        val creator = statuses.creatorData.creator.get()
        val volatileData: CreatorVolatileData = creator.getVolatileData()
        val encounteredIssues = statuses.item.issues

        val newOffersStatuses: Set<OfferStatus> = statuses.item.items
        val newOffers: Set<Offer> = newOffersStatuses.map { it.offer }.toSet()

        val newOpenFor = newOffersStatuses.filter { it.status.isOpen() }.map { it.offer }
        val openForChange = ListChange(creator.getOpenFor(), newOpenFor)

        volatileData.csTrackerIssue = getNewValLogged(creator, volatileData.csTrackerIssue, encounteredIssues, "Encountered issues")
        volatileData.lastCsUpdateUtc = UTC.Now.dateTime() // This is technically the time it got updated even though the webpage was checked a few minutes ago

        creator.offersStatuses.forEach {
            if (!newOffers.contains(it.offer)) {
                getNewValLogged(creator, it.toOfferStatus(), null, "Offer status")
                it.delete()
            }
        }

        newOffersStatuses.forEach { newOfferStatus ->
            val entity = creator.offersStatuses.singleOrNull { it.offer == newOfferStatus.offer }

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

        if (openForChange.changed()) {
            Event.new {
                checkedUrls = statuses.item.sourceUrls.map { it.getOriginalUrl() }.pack()
                noLongerOpenFor = openForChange.removed.pack()
                nowOpenFor = openForChange.added.pack()
                timestamp = UTC.Now.dateTime()
                trackingIssues = encounteredIssues
                type = "CS_UPDATED" // TODO: Enum
                artisanName = creator.name
            }
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
}
