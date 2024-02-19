package tasks

import config.Configuration
import database.Database
import database.helpers.getState
import database.helpers.lastFetchTime
import database.tables.CreatorUrls
import org.jetbrains.exposed.dao.with
import time.UTC
import web.snapshots.SnapshotsManager
import web.url.CreatorUrl
import database.entities.CreatorUrl as CreatorUrlEntity

class UrlsInspection(
    private val config: Configuration,
    private val options: UrlsInspectionOptions,
    private val database: Database = Database(config.databasePath),
    private val snapshotsManager: SnapshotsManager = SnapshotsManager(config.snapshotsStoreDirPath)
) {
    private val notInspectedUrlTypes = listOf("URL_PHOTOS", "URL_MINIATURES", "URL_OTHER") // TODO: Doesn't belong here

    fun run() {
        database.transaction {
            val longTimeAgo = UTC.Now.dateTime().minusYears(20)

            CreatorUrlEntity.find { CreatorUrls.type notInList notInspectedUrlTypes }
                .with(CreatorUrlEntity::states)
                .sortedBy { it.getState().lastFetchTime() ?: longTimeAgo }
                .take(options.limit)
                .forEach {
                    snapshotsManager.get(CreatorUrl(it), true)
                }
        }
    }
}
