package species.yaml

import com.fasterxml.jackson.annotation.JsonProperty

data class YamlSpecies(
    @JsonProperty("valid_choices")
    val validChoices: Map<String, YamlSubspecies?>,
)
