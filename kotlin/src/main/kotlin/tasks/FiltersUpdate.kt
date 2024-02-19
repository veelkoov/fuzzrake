package tasks

import config.Configuration
import data.KotlinDataManager
import data.KotlinDataManager.SPECIES_FILTER
import database.Database
import database.repositories.CreatorSpeciesRepository
import database.repositories.CreatorsRepository
import filters.FilterData
import filters.SpecialItem
import filters.StandardItem
import species.Specie
import species.SpeciesLoader

class FiltersUpdate(
    private val config: Configuration,
    private val database: Database = Database(config.databasePath),
) {
    fun execute() {
        database.transaction {
            val stats: Map<String, Int> = CreatorSpeciesRepository.getActiveCreatorsSpecieNamesToCount()

            val items = getSpeciesList(SpeciesLoader().get().getAsTree(), stats)
            val specialItems = listOf(SpecialItem("Unknown", "?", countUnknown(), "unknown"))

            KotlinDataManager.set(SPECIES_FILTER, FilterData(items, specialItems))
        }
    }

    private fun countUnknown(): Int {
        val knownCount = CreatorSpeciesRepository.countActiveCreatorsHavingSpeciesDefined()
        val allCount = CreatorsRepository.countActive()

        return (allCount - knownCount).toInt()
    }

    private fun getSpeciesList(species: Collection<Specie>, stats: Map<String, Int>): List<StandardItem> {
        return species.filterNot(Specie::getHidden).map { specie -> specieToStandardItem(specie, stats) }
    }

    private fun specieToStandardItem(specie: Specie, stats: Map<String, Int>): StandardItem {
        return StandardItem(
            specie.name,
            specie.name,
            stats[specie.name] ?: 0,
            getSpeciesList(specie.getChildren(), stats),
        )
    }
}
