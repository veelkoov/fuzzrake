package database.tables

import org.jetbrains.exposed.dao.id.IntIdTable

object Species : IntIdTable("species") {
    val name = varchar("name", 1024)
}
