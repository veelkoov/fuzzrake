package database.tables

import org.jetbrains.exposed.dao.id.IntIdTable
import org.jetbrains.exposed.sql.javatime.datetime

object Events : IntIdTable("events") {
    val timestamp = datetime("timestamp")
    val type = varchar("type", 16).default("")
    val creatorName = varchar("creator_name", 256)
    val newCreatorsCount = integer("new_creators_count").default(0)
    val updatedCreatorsCount = integer("updated_creators_count").default(0)
    val reportedUpdatedCreatorsCount = integer("reported_updated_creators_count").default(0)
    val gitCommits = varchar("git_commits", 256).default("")
    val checkedUrls = varchar("checked_urls", 1024).default("")
    val description = text("description", eagerLoading = true).default("")
    val noLongerOpenFor = varchar("no_longer_open_for", 256).default("")
    val nowOpenFor = varchar("now_open_for", 256).default("")
    val trackingIssues = bool("tracking_issues").default(false)
}
