package tracking.matchers

import com.fasterxml.jackson.annotation.JsonProperty

data class YamlRegexes(
    @JsonProperty("false_positives")
    val falsePositives: List<String>,

    @JsonProperty("offers_statuses")
    val offersStatuses: List<String>,

    val placeholders: Placeholders,

    val cleaners: Map<String, String>,
)
