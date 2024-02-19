package database

import org.jetbrains.exposed.sql.Database
import org.jetbrains.exposed.sql.Transaction
import org.jetbrains.exposed.sql.transactions.transactionManager
import java.sql.Connection
import org.jetbrains.exposed.sql.transactions.transaction as exposedTransaction
//import org.jetbrains.exposed.sql.StdOutSqlLogger
//import org.jetbrains.exposed.sql.addLogger

class Database(dbPath: String) {
    private val isolationLevel = Connection.TRANSACTION_SERIALIZABLE // or TRANSACTION_READ_UNCOMMITTED

    private val database = Database.connect("jdbc:sqlite:$dbPath", "org.sqlite.JDBC")

    init {
        database.transactionManager.defaultIsolationLevel = isolationLevel
    }

    fun <T> transaction(function: (transaction: Transaction) -> T): T {
        return exposedTransaction(database) {
            //this.addLogger(StdOutSqlLogger)

            function(this)
        }
    }
}
