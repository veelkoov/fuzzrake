package species.yaml

import com.fasterxml.jackson.annotation.JsonIgnoreProperties
import com.fasterxml.jackson.annotation.JsonProperty

data class YamlSpecies(
    @JsonProperty("parameters")
    val parameters: Parameters,
) {
    data class Parameters(
        @JsonProperty("species_definitions")
        val speciesDefinitions: SpeciesDefinitions,
    ) {
        @JsonIgnoreProperties("regex_prefix", "regex_suffix", "replacements")
        data class SpeciesDefinitions(
            @JsonProperty("valid_choices")
            val validChoices: Map<String, YamlSubspecies?>,
        )
    }
}
