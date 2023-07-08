package database.entities

import database.tables.CreatorUrlStates
import org.jetbrains.exposed.dao.IntEntity
import org.jetbrains.exposed.dao.IntEntityClass
import org.jetbrains.exposed.dao.id.EntityID

class CreatorUrlState(id: EntityID<Int>) : IntEntity(id) {
    companion object : IntEntityClass<CreatorUrlState>(CreatorUrlStates)

    var url by CreatorUrl referencedOn CreatorUrlStates.url
    var lastSuccess by CreatorUrlStates.lastSuccess
    var lastFailure by CreatorUrlStates.lastFailure
    var lastFailureCode by CreatorUrlStates.lastFailureCode
    var lastFailureReason by CreatorUrlStates.lastFailureReason
}
