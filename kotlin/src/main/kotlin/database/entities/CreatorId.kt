package database.entities

import database.tables.CreatorIds
import org.jetbrains.exposed.dao.IntEntity
import org.jetbrains.exposed.dao.IntEntityClass
import org.jetbrains.exposed.dao.id.EntityID

class CreatorId(id: EntityID<Int>) : IntEntity(id) {
    companion object : IntEntityClass<CreatorId>(CreatorIds)

    var creator by Creator referencedOn CreatorIds.creator
    var creatorId by CreatorIds.creatorId
}
