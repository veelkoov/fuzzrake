package database.entities

import database.tables.CreatorSpecies
import org.jetbrains.exposed.dao.IntEntity
import org.jetbrains.exposed.dao.IntEntityClass
import org.jetbrains.exposed.dao.id.EntityID

class CreatorSpecie(id: EntityID<Int>) : IntEntity(id) {
    companion object : IntEntityClass<CreatorSpecie>(CreatorSpecies)

    var creator by Creator referencedOn CreatorSpecies.creator
    var specie by Specie referencedOn CreatorSpecies.specie
}
