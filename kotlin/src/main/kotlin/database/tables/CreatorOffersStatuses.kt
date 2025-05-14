package database.tables

import org.jetbrains.exposed.dao.id.IntIdTable

object CreatorOffersStatuses : IntIdTable("creators_offers_statuses") {
    val creator = reference("creator_id", Creators)
    val offer = varchar("offer", 32)
    val isOpen = bool("is_open")
}
