package web.snapshots

import kotlinx.serialization.Serializable
import time.UTC

@Serializable
data class SnapshotMetadata(
    val url: String,
    val ownerName: String, // TODO: Eliminate/update, we have aliases
    val retrievedAt: String,
    val httpCode: Int,
    val headers: Map<String, List<String>>,
    val childCount: Int,
    val errors: List<String>,
) {
    companion object {
        fun forError(url: String, owner: String, error: String) = SnapshotMetadata(
            url, owner, UTC.Now.dateTime().toString(), 0, mapOf(), 0, listOf(error)
        )
    }
}
