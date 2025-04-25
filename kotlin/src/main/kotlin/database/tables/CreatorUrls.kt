package database.tables

import org.jetbrains.exposed.dao.id.IntIdTable

object CreatorUrls : IntIdTable("creators_urls") {
    val creator = reference("creator_id", Creators)
    val type = varchar("type", 32)
    val url = varchar("url", 1024)
}
