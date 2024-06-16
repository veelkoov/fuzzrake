package tasks

import config.Configuration
import data.KotlinDataManager
import data.KotlinDataManager.FEATURES_FILTER
import data.KotlinDataManager.LANGUAGES_FILTER
import data.KotlinDataManager.ORDER_TYPES_FILTER
import data.KotlinDataManager.PRODUCTION_MODELS_FILTER
import data.KotlinDataManager.SPECIES_FILTER
import data.KotlinDataManager.STYLES_FILTER
import data.definitions.Field
import database.Database
import filters.FilterData
import tasks.filtersUpdate.SpeciesFilterUpdate
import tasks.filtersUpdate.ValueFieldFilterUpdate

class FiltersUpdate(
    private val config: Configuration,
    private val database: Database = Database(config.databasePath),
) {
    fun execute() {
        database.transaction {
            update(SPECIES_FILTER, SpeciesFilterUpdate().getFilterData())
            update(PRODUCTION_MODELS_FILTER, ValueFieldFilterUpdate(Field.PRODUCTION_MODELS).getFilterData())
            update(STYLES_FILTER, ValueFieldFilterUpdate(Field.STYLES, Field.OTHER_STYLES).getFilterData())
            update(ORDER_TYPES_FILTER, ValueFieldFilterUpdate(Field.ORDER_TYPES, Field.OTHER_ORDER_TYPES).getFilterData())
            update(FEATURES_FILTER, ValueFieldFilterUpdate(Field.FEATURES, Field.OTHER_FEATURES).getFilterData())
            update(LANGUAGES_FILTER, ValueFieldFilterUpdate(Field.LANGUAGES).getFilterData())
        }
    }

    private fun update(filter: String, data: FilterData) {
        KotlinDataManager.set(filter, data)
    }
}
