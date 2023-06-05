package tracking.steps

import database.CreatorUrl
import database.CreatorUrls
import database.Database
import org.jetbrains.exposed.sql.transactions.transaction
import tracking.contents.Snapshot
import tracking.creator.Creator
import tracking.creator.CreatorItems
import web.snapshots.Manager
import java.util.stream.Stream
import kotlin.streams.asStream

class SnapshotsProvider { // FIXME: All of it
    fun getSnapshotsStream(): Stream<CreatorItems<Snapshot>> {
        val creators = transaction(Database.get()) {
            CreatorUrl
                .find { CreatorUrls.type eq "URL_COMMISSIONS" } // TODO: Enum!
                .groupBy { it.creator }
        }

        return creators.asSequence()
            .asStream().parallel()
            .map { (creator, urls) ->
                CreatorItems(Creator(listOf(creator.name)), urls.map { Manager.get(it.url) }) // TODO: Aliases
            }
    }
}
