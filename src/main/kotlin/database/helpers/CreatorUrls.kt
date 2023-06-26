package database.helpers

import database.entities.Creator
import database.entities.CreatorUrl
import database.tables.CreatorUrls
import org.jetbrains.exposed.dao.with
import org.jetbrains.exposed.sql.SizedIterable

fun CreatorUrls.findTracking(): SizedIterable<CreatorUrl> = CreatorUrl
    .find { type eq "URL_COMMISSIONS" } // TODO: Enum!
    .with(CreatorUrl::creator, Creator::creatorIds)
