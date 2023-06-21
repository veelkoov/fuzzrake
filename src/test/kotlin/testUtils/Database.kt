package testUtils

import database.*
import database.tables.CreatorOffersStatuses
import database.tables.CreatorUrls
import database.tables.CreatorVolatileDatas
import database.tables.Creators
import org.jetbrains.exposed.sql.SchemaUtils

fun <T>disposableTransaction(block: () -> T): T {
    return Database(":memory:").transaction {
        SchemaUtils.create(Creators, CreatorOffersStatuses, CreatorUrls, CreatorVolatileDatas)

        block()
    }
}
