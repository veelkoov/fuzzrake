package database.tables

import org.jetbrains.exposed.dao.id.IntIdTable

object CreatorOffersStatuses : IntIdTable("artisans_commissions_statuses") {
    val creator = reference("artisan_id", Creators)
    val offer = varchar("offer", 32)
    val isOpen = bool("is_open")
}
