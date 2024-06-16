package filters

import data.FILTER_VALUE_OTHER
import data.FILTER_VALUE_UNKNOWN
import kotlinx.serialization.Serializable

@Serializable
data class SpecialItem(
    val label: String,
    val value: String,
    val count: Int,
) {
    companion object {
        fun newUnknown(count: Int) = SpecialItem("Unknown", FILTER_VALUE_UNKNOWN, count)
        fun newOther(count: Int) = SpecialItem("Other", FILTER_VALUE_OTHER, count)
    }
}
