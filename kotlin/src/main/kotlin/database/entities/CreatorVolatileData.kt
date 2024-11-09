package database.entities

import database.tables.CreatorVolatileDatas
import org.jetbrains.exposed.dao.IntEntity
import org.jetbrains.exposed.dao.IntEntityClass
import org.jetbrains.exposed.dao.id.EntityID

class CreatorVolatileData(id: EntityID<Int>) : IntEntity(id) {
    companion object : IntEntityClass<CreatorVolatileData>(CreatorVolatileDatas)

    var creator by Creator referencedOn CreatorVolatileDatas.creator
    var lastCsUpdateUtc by CreatorVolatileDatas.lastCsUpdateUtc
    var csTrackerIssue by CreatorVolatileDatas.csTrackerIssue
}
