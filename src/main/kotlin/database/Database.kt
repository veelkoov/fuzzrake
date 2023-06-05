package database

import org.jetbrains.exposed.sql.Database
import org.jetbrains.exposed.sql.transactions.TransactionManager
import java.sql.Connection

object Database {
    private const val dbPath = "/home/fuzzrake/var/db.sqlite" // TODO: Parameters
    private const val isolationLevel = Connection.TRANSACTION_SERIALIZABLE // or TRANSACTION_READ_UNCOMMITTED

    private val database = Database.connect("jdbc:sqlite:$dbPath", "org.sqlite.JDBC")

    init {
        TransactionManager.manager.defaultIsolationLevel = isolationLevel
    }

    fun get() = database
}
