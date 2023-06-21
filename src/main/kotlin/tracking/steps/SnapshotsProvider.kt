package tracking.steps

import data.CreatorItems
import database.entities.Creator
import database.entities.CreatorUrl
import database.helpers.findCommissions
import database.helpers.lastCreatorId
import database.tables.CreatorUrls
import tracking.website.Strategy
import web.client.GentleHttpClient
import web.snapshots.Snapshot
import web.snapshots.SnapshotsManager
import java.util.stream.Stream

class SnapshotsProvider(private val snapshotsManager: SnapshotsManager) {
    private val httpClient = GentleHttpClient()
    private val creators = getCreatorsMap()

    private fun getCreatorsMap(): Map<Creator, List<CreatorUrl>> {
        return CreatorUrls
            .findCommissions()
            .groupBy { it.creator }
    }

    fun getSnapshotsStream(): Stream<CreatorItems<Snapshot>> { // FIXME: All of it
        return creators.entries
            .map { (creator, creatorUrls) ->
                CreatorItems(creator, creator.lastCreatorId(), creatorUrls)
            }
            .parallelStream()
            .map(::urlsToSnapshots)
    }

    private fun urlsToSnapshots(input: CreatorItems<CreatorUrl>): CreatorItems<Snapshot> {
        val snapshots = input.items.map {
            val url = Strategy
                .forUrl(it.url)
                .coerceUrl(it.url)

            snapshotsManager.get(url, httpClient::get)
        }

        return CreatorItems(input.creator, input.creatorId, snapshots)
    }

    fun getCreators() = creators.keys
}
