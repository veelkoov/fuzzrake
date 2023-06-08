package tracking.updating

import database.*
import org.jetbrains.exposed.sql.SqlExpressionBuilder.inList
import org.jetbrains.exposed.sql.transactions.transaction

class DbState(
    private val statuses: MutableMap<Creator, List<CreatorOfferStatus>>,
    private val volatileDatas: MutableMap<Creator, CreatorVolatileData>,
) {
    fun finalize() {

    }

    companion object {
        fun getAsOfNow(): DbState {
            return transaction(Database.get()) {
                val statuses = CreatorOfferStatus.all().groupBy { it.creator }

                val creatorEntityIds = statuses.keys.map { it.id }

                val volatileDatas = CreatorVolatileData
                    .find(CreatorVolatileDatas.creator inList creatorEntityIds)
                    .associateBy { it.creator }

                DbState(statuses.toMutableMap(), volatileDatas.toMutableMap())
            }
        }
    }
}
