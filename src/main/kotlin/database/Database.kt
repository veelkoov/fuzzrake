package database

import org.jetbrains.exposed.sql.Database
import org.jetbrains.exposed.sql.transactions.transactionManager
import java.sql.Connection
import org.jetbrains.exposed.sql.transactions.transaction as exposedTransaction

class Database(dbPath: String) {
    private val isolationLevel = Connection.TRANSACTION_SERIALIZABLE // or TRANSACTION_READ_UNCOMMITTED

    private val database = Database.connect("jdbc:sqlite:$dbPath", "org.sqlite.JDBC")

    init {
        database.transactionManager.defaultIsolationLevel = isolationLevel
    }

    fun <T> transaction(function: () -> T): T {
        return exposedTransaction(database) {
            function()
        }
    }
}
