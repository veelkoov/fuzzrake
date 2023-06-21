package tracking.updating

import database.helpers.lastCreatorId
import database.entities.Creator
import database.entities.CreatorOfferStatus
import database.entities.CreatorVolatileData
import database.helpers.allBelongingTo
import database.tables.CreatorVolatileDatas
import io.github.oshai.kotlinlogging.KotlinLogging

private val logger = KotlinLogging.logger {}

class DbState(
    private val statuses: MutableMap<Creator, List<CreatorOfferStatus>>,
    private val volatileDatas: Map<Creator, CreatorVolatileData>,
) {
    fun getVolatileDataOf(creator: Creator): CreatorVolatileData {
        return volatileDatas.getOrElse(creator) {
            logger.info("Missing volatile data entity for ${creator.lastCreatorId()}, will create one.")

            CreatorVolatileData.new {
                this.creator = creator
            }
        }
    }

    fun consumeCreatorOfferStatuses(creator: Creator): List<CreatorOfferStatus> {
        return (statuses.remove(creator) ?: listOf())
    }

    fun getUnconsumedCreatorOfferStatuses(): List<CreatorOfferStatus> {
        val result = statuses.values.flatten()

        statuses.clear()

        return result
    }

    companion object {
        fun getAsOfNow(creators: Set<Creator>): DbState {
            val statuses = CreatorOfferStatus
                .all()
                .groupBy { it.creator }

            val volatileDatas = CreatorVolatileDatas
                .allBelongingTo(creators)
                .associateBy { it.creator }

            return DbState(statuses.toMutableMap(), volatileDatas)
        }
    }
}
