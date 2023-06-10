package testUtils

import database.*
import org.jetbrains.exposed.sql.SchemaUtils

fun <T>disposableTransaction(block: () -> T): T {
    return Database(":memory:").transaction {
        SchemaUtils.create(Creators, CreatorOffersStatuses, CreatorUrls, CreatorVolatileDatas)

        block()
    }
}
