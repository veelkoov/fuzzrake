package database.repositories

import database.tables.CreatorValues
import database.tables.Creators
import org.jetbrains.exposed.sql.ResultRow
import org.jetbrains.exposed.sql.andWhere
import org.jetbrains.exposed.sql.count
import org.jetbrains.exposed.sql.selectAll

object CreatorValuesRepository {
    fun countActiveCreatorsHavingAnyOf(fieldNames: Collection<String>): Long {
        return (CreatorValues innerJoin Creators)
            .selectAll()
            .where { Creators.inactiveReason eq "" }
            .andWhere { CreatorValues.fieldName inList fieldNames }
            .count()
    }

    fun getFieldValuesCountedFromActive(fieldName: String): Map<String, Long> {
        return (CreatorValues innerJoin Creators)
            .select(CreatorValues.value, CreatorValues.value.count())
            .where { Creators.inactiveReason eq "" }
            .andWhere { CreatorValues.fieldName eq fieldName }
            .groupBy(CreatorValues.value)
            .associate { row: ResultRow -> row[CreatorValues.value] to row[CreatorValues.value.count()] }
    }
}
