package database.entities

import database.tables.Species
import org.jetbrains.exposed.dao.IntEntity
import org.jetbrains.exposed.dao.IntEntityClass
import org.jetbrains.exposed.dao.id.EntityID

class Specie(id: EntityID<Int>) : IntEntity(id) {
    companion object : IntEntityClass<Specie>(Species)

    var name by Species.name
}
