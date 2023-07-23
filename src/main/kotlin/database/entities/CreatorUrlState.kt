package database.entities

import database.tables.CreatorUrlStates
import org.jetbrains.exposed.dao.IntEntity
import org.jetbrains.exposed.dao.IntEntityClass
import org.jetbrains.exposed.dao.id.EntityID

class CreatorUrlState(id: EntityID<Int>) : IntEntity(id) {
    companion object : IntEntityClass<CreatorUrlState>(CreatorUrlStates)

    var url by CreatorUrl referencedOn CreatorUrlStates.url
    var lastSuccessUtc by CreatorUrlStates.lastSuccessUtc
    var lastFailureUtc by CreatorUrlStates.lastFailureUtc
    var lastFailureCode by CreatorUrlStates.lastFailureCode
    var lastFailureReason by CreatorUrlStates.lastFailureReason
}
