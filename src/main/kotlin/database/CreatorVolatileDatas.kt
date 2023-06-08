package database

import org.jetbrains.exposed.dao.id.IntIdTable
import org.jetbrains.exposed.sql.SizedIterable
import org.jetbrains.exposed.sql.SqlExpressionBuilder.inList
import org.jetbrains.exposed.sql.javatime.datetime

object CreatorVolatileDatas : IntIdTable("artisans_volatile_data") {
    val creator = reference("artisan_id", Creators)
    val lastCsUpdateUtc = datetime("last_cs_update").nullable() // TODO: Rename to last_cs_update_utc
    val csTrackerIssue = bool("cs_tracker_issue").default(false)
    val lastBpUpdateUtc = datetime("last_bp_update").nullable() // TODO: Remove
    val bpTrackerIssue = bool("bp_tracker_issue").default(false) // TODO: Remove

    fun allBelongingTo(creators: Iterable<Creator>): SizedIterable<CreatorVolatileData> {
        val creatorEntityIds = creators.map { it.id }

        return CreatorVolatileData
            .find(creator inList creatorEntityIds)
    }
}
