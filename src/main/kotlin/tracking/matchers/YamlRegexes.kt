package tracking.matchers

import com.fasterxml.jackson.annotation.JsonProperty

data class YamlRegexes(
    @JsonProperty("false_positives")
    val falsePositives: List<String>,
    @JsonProperty("offers_statuses")
    val offersStatuses: List<String>,

    val placeholders: YamlPlaceholdersMap,
    val cleaners: Map<String, String>,
)

abstract class YamlPlaceholders {}

class YamlPlaceholdersMap(
    value: Map<Any?, Any?>,
) : YamlPlaceholders() {
    private val items: Map<String, YamlPlaceholders>

    init {
        items = value.entries.associate {
            val itemKey = it.key
            val itemVal = it.value

            if (itemKey !is String) {
                throw IllegalArgumentException() // TODO: Debug message
            }

            itemKey to when (itemVal) {
                is Map<*, *> -> YamlPlaceholdersMap(itemVal.toMap())
                is List<*> -> YamlPlaceholdersList(itemVal.toList())
                else -> throw IllegalArgumentException()// TODO: Debug message
            }
        }
    }
}

class YamlPlaceholdersList(
    value: List<Any?>,
) : YamlPlaceholders() {
    private val items: List<String>

    init {
        items = value.map {
            if (it is String) {
                it
            } else {
                throw IllegalArgumentException() // TODO: Debug message
            }
        }
    }
}
