package species

import data.Resource
import data.Yaml
import io.github.oshai.kotlinlogging.KotlinLogging
import species.yaml.YamlSpecies
import species.yaml.YamlSubspecies

private val logger = KotlinLogging.logger {}

class SpeciesLoader(resource: String = "/species.yaml") {
    private val builder = Species.Builder()
    private val result: Species

    init {
        val yamlSpecies = Yaml.parse(Resource.read(resource), YamlSpecies::class.java)

        yamlSpecies.validChoices.forEach { (name, subspecies) ->
            builder.addRootSpecie(createSpecie(name, subspecies))
        }

        result = builder.getResult()
    }

    fun get() = result

    private fun createSpecie(nameOptFlag: String, subspecies: YamlSubspecies?): Specie.Builder {
        val hidden = nameOptFlag.startsWith("i_")
        val name = nameOptFlag.removePrefix("i_")

        val specie = builder.getByNameCreatingMissing(name, hidden)

        if (specie.getHidden() != hidden) {
            logger.warn { "Repeated specie $name was declared hidden=${specie.getHidden()} and now is declared hidden=$hidden, ignoring the change." }
        }

        if (subspecies == null) {
            return specie
        }

        subspecies.getItems().forEach { (name, subspecies) ->
            specie.addChild(createSpecie(name, subspecies))
        }

        return specie
    }
}
