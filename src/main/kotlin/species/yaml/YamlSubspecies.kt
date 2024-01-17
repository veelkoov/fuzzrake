package species.yaml

import com.fasterxml.jackson.annotation.JsonAnySetter

class YamlSubspecies {
    private val items: MutableMap<String, YamlSubspecies> = mutableMapOf()

    @JsonAnySetter
    private fun set(name: String, value: YamlSubspecies) {
        items[name] = value
    }

    fun getItems() = items.toMap()
}
