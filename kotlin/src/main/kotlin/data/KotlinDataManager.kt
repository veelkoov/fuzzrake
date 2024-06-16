package data

import database.repositories.KotlinDataRepository
import kotlinx.serialization.encodeToString
import kotlinx.serialization.json.Json

object KotlinDataManager {
    const val SPECIES_FILTER = "species-filter"
    const val PRODUCTION_MODELS_FILTER = "production-models-filter"
    const val STYLES_FILTER = "styles-filter"
    const val ORDER_TYPES_FILTER = "order-types-filter"
    const val FEATURES_FILTER = "features-filter"
    const val LANGUAGES_FILTER = "languages-filter"

    inline fun <reified T>set(name: String, data: T) {
        KotlinDataRepository.replaceData(name, Json.encodeToString(data))
    }
}
