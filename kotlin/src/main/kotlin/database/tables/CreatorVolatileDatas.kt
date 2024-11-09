package database.tables

import org.jetbrains.exposed.dao.id.IntIdTable
import org.jetbrains.exposed.sql.javatime.datetime

object CreatorVolatileDatas : IntIdTable("artisans_volatile_data") {
    val creator = reference("artisan_id", Creators)
    val lastCsUpdateUtc = datetime("last_cs_update").nullable() // TODO: Rename to last_cs_update_utc
    val csTrackerIssue = bool("cs_tracker_issue").default(false)
}
