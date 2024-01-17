package database.entities

import org.jetbrains.exposed.dao.IntEntity
import org.jetbrains.exposed.dao.IntEntityClass
import org.jetbrains.exposed.dao.id.EntityID
import database.tables.KotlinData as KotlinDataTable

class KotlinData(id: EntityID<Int>) : IntEntity(id) {
    companion object : IntEntityClass<KotlinData>(KotlinDataTable)

    var name by KotlinDataTable.name
    var json by KotlinDataTable.json
}
