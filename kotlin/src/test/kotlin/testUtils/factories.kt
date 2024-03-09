package testUtils

import config.Configuration
import data.ThreadSafe
import database.entities.Creator
import io.mockk.mockk
import tracking.contents.CreatorData
import web.snapshots.Snapshot
import web.snapshots.SnapshotMetadata
import web.url.FreeUrl
import web.url.Url

fun getCreatorData(
    creatorId: String = "",
    aliases: List<String> = listOf(),
): CreatorData {
    val creator = ThreadSafe(mockk<Creator>())

    return CreatorData(creatorId, aliases, creator)
}

fun getSnapshot(
    contents: String = "",
    url: String = "",
    ownerName: String = "",
    retrievedAt: String = "",
    httpCode: Int = 0,
    headers: Map<String, List<String>> = mapOf(),
    errors: List<String> = listOf(),
): Snapshot {
    return Snapshot(contents, SnapshotMetadata(url, ownerName, retrievedAt, httpCode, headers, 0, errors))
}

fun getUrl(
    url: String = "http://localhost/",
): Url {
    return FreeUrl(url)
}

fun getNullConfiguration() = Configuration("/dev/null", "/dev/null")
