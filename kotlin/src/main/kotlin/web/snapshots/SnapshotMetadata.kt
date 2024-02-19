package web.snapshots

import kotlinx.serialization.Serializable
import time.UTC

@Serializable
data class SnapshotMetadata(
    val url: String,
    val ownerName: String, // TODO: Change to creator ID
    val retrievedAt: String,
    val httpCode: Int,
    val headers: Map<String, List<String>>,
    val childCount: Int, // TODO: Eliminate
    val errors: List<String>,
) {
    companion object {
        fun forError(url: String, owner: String, error: String) = SnapshotMetadata(
            url, owner, UTC.Now.dateTime().toString(), 0, mapOf(), 0, listOf(error)
        )
    }
}
