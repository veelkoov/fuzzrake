package tasks

import config.Configuration
import data.KotlinDataManager
import data.KotlinDataManager.SPECIES_FILTER
import database.Database
import filters.FilterData
import tasks.filtersUpdate.SpeciesFilterUpdate

class FiltersUpdate(
    private val config: Configuration,
    private val database: Database = Database(config.databasePath),
) {
    fun execute() {
        database.transaction {
            KotlinDataManager.set<FilterData>(SPECIES_FILTER, SpeciesFilterUpdate().getFilterData())
        }
    }
}
