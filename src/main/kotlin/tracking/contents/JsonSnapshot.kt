package tracking.contents

import kotlinx.serialization.Serializable

@Serializable
data class JsonSnapshot(
    val url: String,
    val ownerName: String, // Should not be used, we have aliases
    val retrievedAt: String,
    val httpCode: Int,
    val headers: Map<String, List<String>>,
    val childCount: Int,
    val errors: List<String>,
)
