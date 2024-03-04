package database.tables

import org.jetbrains.exposed.dao.id.IntIdTable

object CreatorSpecies : IntIdTable("creators_species") {
    val creator = reference("artisan_id", Creators)
    val specie = reference("specie_id", Species)
}
