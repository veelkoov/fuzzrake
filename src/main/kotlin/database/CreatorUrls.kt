package database

import org.jetbrains.exposed.dao.id.IntIdTable

object CreatorUrls : IntIdTable("artisans_urls") {
    val creator = reference("artisan_id", Creators)
    val type = varchar("type", 32)
    val url = varchar("url", 1024)
}
