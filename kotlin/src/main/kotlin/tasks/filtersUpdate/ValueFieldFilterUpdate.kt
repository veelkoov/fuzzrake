package tasks.filtersUpdate

import data.definitions.Field
import database.repositories.CreatorValuesRepository
import database.repositories.CreatorsRepository
import filters.FilterData
import filters.SpecialItem
import filters.StandardItem

class ValueFieldFilterUpdate(
    private val primary: Field,
    private val other: Field? = null,
) {
    fun getFilterData(): FilterData {
        val fieldNames = setOf(primary.name, other?.name).filterNotNull()

        val unknownCount = CreatorsRepository.countActive() -
                CreatorValuesRepository.countActiveCreatorsHavingAnyOf(fieldNames)

        val specialItems = mutableListOf(
            SpecialItem.newUnknown(unknownCount.toInt()),
        )

        if (other != null) {
            val otherCount = CreatorValuesRepository.countActiveCreatorsHavingAnyOf(setOf(other.name))

            specialItems.add(SpecialItem.newOther(otherCount.toInt()))
        }

        val items = CreatorValuesRepository.getFieldValuesCountedFromActive(primary.name)
            .map { (item: String, count: Long) -> StandardItem(item, item, count.toInt(), listOf()) }

        return FilterData(items, specialItems)
    }
}
