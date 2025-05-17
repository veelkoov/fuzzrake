package database.tables

import org.jetbrains.exposed.dao.id.IntIdTable

object CreatorIds : IntIdTable("creator_ids") {
    val creator = reference("owner_creator_id", Creators)
    val creatorId = varchar("creator_id", 7)
}
