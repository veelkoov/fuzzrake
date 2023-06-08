package database

import org.jetbrains.exposed.dao.IntEntity
import org.jetbrains.exposed.dao.IntEntityClass
import org.jetbrains.exposed.dao.id.EntityID

class CreatorOfferStatus(id: EntityID<Int>) : IntEntity(id) {
    companion object : IntEntityClass<CreatorOfferStatus>(CreatorOffersStatuses)

    var creator by Creator referencedOn CreatorOffersStatuses.creator
    var offer by CreatorOffersStatuses.offer
    var isOpen by CreatorOffersStatuses.isOpen
}
