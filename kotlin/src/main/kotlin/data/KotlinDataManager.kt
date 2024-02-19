package data

import database.repositories.KotlinDataRepository
import kotlinx.serialization.encodeToString
import kotlinx.serialization.json.Json

object KotlinDataManager {
    const val SPECIES_FILTER = "species-filter"

    inline fun <reified T>set(name: String, data: T) {
        KotlinDataRepository.replaceData(name, Json.encodeToString(data))
    }
}
