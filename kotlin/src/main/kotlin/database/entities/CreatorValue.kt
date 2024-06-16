package database.entities

import database.tables.CreatorValues
import org.jetbrains.exposed.dao.IntEntity
import org.jetbrains.exposed.dao.IntEntityClass
import org.jetbrains.exposed.dao.id.EntityID

class CreatorValue(id: EntityID<Int>) : IntEntity(id) {
    companion object : IntEntityClass<CreatorValue>(CreatorValues)

    var creator by Creator referencedOn CreatorValues.creator
    var fieldName by CreatorValues.fieldName
    var value by CreatorValues.value
}
