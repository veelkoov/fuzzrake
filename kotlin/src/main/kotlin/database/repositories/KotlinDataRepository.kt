package database.repositories

import database.entities.KotlinData
import database.tables.KotlinData as KotlinDataTable

object KotlinDataRepository {
    fun replaceData(name: String, json: String) {
        val entities = KotlinData.find { KotlinDataTable.name eq name }

        val entity = if (entities.count() == 0L) {
            KotlinData.new {
                this.name = name
            }
        } else if (entities.count() == 1L) {
            entities.first()
        } else throw IllegalStateException("There are {${entities.count()} entries for '$name'")

        entity.json = json
    }
}
