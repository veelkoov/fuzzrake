package database.tables

import org.jetbrains.exposed.dao.id.IntIdTable

object CreatorValues : IntIdTable("artisans_values") {
    val creator = reference("artisan_id", Creators)
    val fieldName = text("field_name")
    val value = text("value")
}
