package tasks

import config.Configuration
import data.unpack
import database.Database
import database.entities.Creator
import database.entities.CreatorSpecie
import database.entities.Specie
import io.github.oshai.kotlinlogging.KotlinLogging
import species.CreatorSpeciesResolver
import species.SpecieException
import species.SpeciesLoader

private val logger = KotlinLogging.logger {}

class SpeciesSync(
    private val config: Configuration,
) {
    private val srcSpecies = SpeciesLoader().get()
    private val resolver = CreatorSpeciesResolver(srcSpecies)

    fun execute() {
        val db = Database(config.databasePath)

        db.transaction {
            val dbSpecies = Specie.all().associateBy(Specie::name).toMutableMap()

            createMissingSpeciesAddToMap(dbSpecies)

            Creator.all().forEach { creator ->
                syncCreatorSpecies(creator, dbSpecies)
            }

            removeObsoleteSpecies(dbSpecies)
        }

        logger.info { "Done." }
    }

    private fun syncCreatorSpecies(creator: Creator, dbSpecies: MutableMap<String, Specie>) {
        val namesDone = resolver.resolveDoes(creator.speciesDoes.unpack(), creator.speciesDoesnt.unpack())

        val namesMatched = srcSpecies.getFlat().filter { srcSpecie ->
            val srcSpecieAndDescendantNames = srcSpecie.getSelfAndDescendants().map { candidateSpecie -> candidateSpecie.name }

            namesDone.any { nameDone -> srcSpecieAndDescendantNames.contains(nameDone) }
        }.map { specieMatched -> specieMatched.name }

        namesMatched.minus(creator.species.map { it.specie.name }.toSet()).forEach { specieName ->
            logger.info { "Adding '$specieName' to $creator..." }

            CreatorSpecie.new {
                this.creator = creator
                this.specie = dbSpecies[specieName] ?: throw SpecieException("$creator resolved specie '$specieName' does not exist in the database")
            }
        }

        creator.species.associateBy { it.specie.name }.minus(namesMatched).forEach { (specieName, specie) ->
            logger.info { "Removing '${specieName}' from $creator..." }

            specie.delete()
        }
    }

    private fun createMissingSpeciesAddToMap(dbSpecies: MutableMap<String, Specie>) {
        val missingNames = srcSpecies.getVisibleNames().minus(dbSpecies.keys)

        missingNames.forEach { name ->
            logger.info { "Creating '$name' specie..." }

            val missingSpecie = Specie.new {
                this.name = name
            }

            dbSpecies[name] = missingSpecie
        }
    }

    private fun removeObsoleteSpecies(dbSpecies: MutableMap<String, Specie>) {
        dbSpecies.minus(srcSpecies.getVisibleNames()).forEach { (_, specie) ->
            logger.info { "Removing '${specie.name}' specie..." }

            specie.delete()
        }
    }
}
