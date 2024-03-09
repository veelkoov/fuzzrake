package database.tables

import org.jetbrains.exposed.dao.id.IntIdTable

object KotlinData : IntIdTable("kotlin_data") {
    val name = varchar("name", 255)
    val json = text("json")
}
