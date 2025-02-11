package tasks.filtersUpdate

import database.repositories.CreatorSpeciesRepository
import database.repositories.CreatorsRepository
import filters.FilterData
import filters.SpecialItem
import filters.StandardItem
import species.Specie
import species.SpeciesLoader

class SpeciesFilterUpdate {
    fun getFilterData(): FilterData {
        val stats: Map<String, Int> = CreatorSpeciesRepository.getActiveCreatorsSpecieNamesToCount()

        val items = getSpeciesList(SpeciesLoader().get().getAsTree(), stats)
        val specialItems = listOf(SpecialItem.newUnknown(countUnknown()))

        return FilterData(items, specialItems)
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
