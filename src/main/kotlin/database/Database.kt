package database

import org.jetbrains.exposed.sql.Database
import org.jetbrains.exposed.sql.StdOutSqlLogger
import org.jetbrains.exposed.sql.addLogger
import org.jetbrains.exposed.sql.transactions.TransactionManager
import java.sql.Connection
import org.jetbrains.exposed.sql.transactions.transaction as exposedTransaction

object Database {
    private const val dbPath = "/home/fuzzrake/var/db.sqlite" // TODO: Parameters
    private const val isolationLevel = Connection.TRANSACTION_SERIALIZABLE // or TRANSACTION_READ_UNCOMMITTED

    private val database = Database.connect("jdbc:sqlite:$dbPath", "org.sqlite.JDBC")

    init {
        TransactionManager.manager.defaultIsolationLevel = isolationLevel
    }

    fun <T> transaction(function: () -> T): T {
        return exposedTransaction(database) {
            this.addLogger(StdOutSqlLogger)

            function()
        }
    }
}
