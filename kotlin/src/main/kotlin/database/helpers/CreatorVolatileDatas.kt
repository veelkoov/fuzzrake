package database.helpers

import database.entities.Creator
import database.entities.CreatorVolatileData
import database.tables.CreatorVolatileDatas
import org.jetbrains.exposed.sql.SizedIterable
import org.jetbrains.exposed.sql.SqlExpressionBuilder.inList

fun CreatorVolatileDatas.allBelongingTo(creators: Iterable<Creator>): SizedIterable<CreatorVolatileData> {
    val creatorEntityIds = creators.map { it.id }

    return CreatorVolatileData
        .find(creator inList creatorEntityIds)
}
