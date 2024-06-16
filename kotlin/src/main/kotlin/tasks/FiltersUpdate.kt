package tasks

import config.Configuration
import database.Database
import tasks.filtersUpdate.SpeciesFiltersUpdate

class FiltersUpdate(
    private val config: Configuration,
    private val database: Database = Database(config.databasePath),
) {
    fun execute() {
        database.transaction {
            SpeciesFiltersUpdate().run()
        }
    }
}
