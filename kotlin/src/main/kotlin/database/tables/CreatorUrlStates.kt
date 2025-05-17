package database.tables

import org.jetbrains.exposed.dao.id.IntIdTable
import org.jetbrains.exposed.sql.javatime.datetime

object CreatorUrlStates : IntIdTable("creators_urls_states") {
    val url = reference("creator_url_id", CreatorUrls).uniqueIndex()

    val lastSuccessUtc = datetime("last_success").nullable() // TODO: Rename column
    val lastFailureUtc = datetime("last_failure").nullable() // TODO: Rename column
    val lastFailureCode = integer("last_failure_code").default(0)
    val lastFailureReason = varchar("last_failure_reason", 512).default("")
}
