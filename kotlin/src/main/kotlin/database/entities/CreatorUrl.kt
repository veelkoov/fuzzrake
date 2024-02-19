package database.entities

import database.tables.CreatorUrlStates
import database.tables.CreatorUrls
import org.jetbrains.exposed.dao.IntEntity
import org.jetbrains.exposed.dao.IntEntityClass
import org.jetbrains.exposed.dao.id.EntityID

class CreatorUrl(id: EntityID<Int>) : IntEntity(id) {
    companion object : IntEntityClass<CreatorUrl>(CreatorUrls)

    var creator by Creator referencedOn CreatorUrls.creator
    var type by CreatorUrls.type // TODO: Enum!
    var url by CreatorUrls.url

    val states by CreatorUrlState referrersOn CreatorUrlStates.url // TODO: 1:1
}
