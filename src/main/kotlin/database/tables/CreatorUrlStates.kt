package database.tables

import org.jetbrains.exposed.dao.id.IntIdTable

object CreatorUrlStates : IntIdTable("artisans_urls") {
    val creator = reference("artisan_id", Creators)
    val type = varchar("type", 32)
    val url = varchar("url", 1024)
}
