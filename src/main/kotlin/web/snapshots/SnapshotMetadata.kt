package web.snapshots

import kotlinx.serialization.Serializable

@Serializable
data class SnapshotMetadata(
    val url: String,
    val ownerName: String, // TODO: Eliminate/update, we have aliases
    val retrievedAt: String,
    val httpCode: Int,
    val headers: Map<String, List<String>>,
    val childCount: Int,
    val errors: List<String>,
)
