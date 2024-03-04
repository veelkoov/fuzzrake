package filters

import kotlinx.serialization.Serializable

@Serializable
data class StandardItem(
    val label: String,
    val value: String,
    val count: Int,
    val subItems: List<StandardItem>,
)
