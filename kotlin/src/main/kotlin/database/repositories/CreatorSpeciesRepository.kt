package database.repositories

import database.tables.CreatorSpecies
import database.tables.Creators
import database.tables.Species
import org.jetbrains.exposed.sql.count
import org.jetbrains.exposed.sql.select

object CreatorSpeciesRepository {
    fun countActiveCreatorsHavingSpeciesDefined() =
        (CreatorSpecies innerJoin Creators)
            .slice(CreatorSpecies.creator)
            .select { Creators.inactiveReason eq "" }
            .withDistinct()
            .count()

    fun getActiveCreatorsSpecieNamesToCount(): Map<String, Int> {
        return (CreatorSpecies innerJoin Species innerJoin Creators)
            .slice(Species.name, CreatorSpecies.specie.count())
            .select { Creators.inactiveReason eq "" }
            .groupBy(Species.name)
            .associate { it[Species.name] to it[CreatorSpecies.specie.count()].toInt() }
    }
}
