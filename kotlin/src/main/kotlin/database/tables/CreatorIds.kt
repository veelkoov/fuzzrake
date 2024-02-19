package database.tables

import org.jetbrains.exposed.dao.id.IntIdTable

object CreatorIds : IntIdTable("maker_ids") {
    val creator = reference("artisan_id", Creators)
    val creatorId = varchar("maker_id", 7)
}
