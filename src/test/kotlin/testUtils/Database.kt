package testUtils

import database.Database
import database.tables.*
import org.jetbrains.exposed.sql.SchemaUtils

fun disposableDatabase(block: (database: Database) -> Unit) {
    val database = Database(":memory:")

    database.transaction {
        SchemaUtils.create(
            CreatorIds,
            CreatorOffersStatuses,
            Creators,
            CreatorUrls,
            CreatorUrlStates,
            CreatorVolatileDatas,
            Events,
        )

        block(database)
    }
}
