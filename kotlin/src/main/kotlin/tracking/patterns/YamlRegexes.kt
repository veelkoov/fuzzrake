package tracking.patterns

import com.fasterxml.jackson.annotation.JsonProperty
import tracking.patterns.placeholders.PhTree

data class YamlRegexes(
    @JsonProperty("false_positives")
    val falsePositives: List<String>,

    @JsonProperty("offers_statuses")
    val offersStatuses: List<String>,

    val placeholders: PhTree,

    val cleaners: Map<String, String>,
)
