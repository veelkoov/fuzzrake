package filters

import kotlinx.serialization.Serializable

@Serializable
data class FilterData(
    val items: List<StandardItem>,
    val specialItems: List<SpecialItem>,
)
