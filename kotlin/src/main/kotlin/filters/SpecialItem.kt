package filters

import kotlinx.serialization.Serializable

@Serializable
data class SpecialItem(
    val label: String,
    val value: String,
    val count: Int,
    val type: String,
)
