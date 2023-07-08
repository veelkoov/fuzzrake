package database.tables

import org.jetbrains.exposed.dao.id.IntIdTable
import org.jetbrains.exposed.sql.javatime.datetime

object CreatorUrlStates : IntIdTable("artisans_urls_states") {
    val url = reference("artisan_url_id", CreatorUrls).uniqueIndex()

    val lastSuccess = datetime("last_success").nullable()
    val lastFailure = datetime("last_failure").nullable()
    val lastFailureCode = integer("last_failure_code").default(0)
    val lastFailureReason = varchar("last_failure_reason", 512).default("")
}
