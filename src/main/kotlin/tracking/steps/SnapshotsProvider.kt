package tracking.steps

import data.CreatorItems
import database.CreatorUrl
import database.CreatorUrls
import database.Database
import org.jetbrains.exposed.sql.transactions.transaction
import tracking.contents.Snapshot
import web.snapshots.Manager
import java.util.stream.Stream

class SnapshotsProvider { // FIXME: All of it
    fun getSnapshotsStream(): Stream<CreatorItems<Snapshot>> {
        val creators = transaction(Database.get()) {
            CreatorUrl
                .find { CreatorUrls.type eq "URL_COMMISSIONS" } // TODO: Enum!
                .groupBy { it.creator }
        }

        return creators.entries
            .parallelStream()
            .map { (creator, urls) ->
                CreatorItems(creator, urls.map { Manager.get(it.url) })
            }
    }
}
