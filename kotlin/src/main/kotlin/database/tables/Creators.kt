package database.tables

import org.jetbrains.exposed.dao.id.IntIdTable

object Creators : IntIdTable("artisans") {
    val creatorId = varchar("maker_id", 16).default("")
    val name = varchar("name", 128).default("")
    val formerly = varchar("formerly", 256).default("")
    val inactiveReason = varchar("inactive_reason", 512).default("")

    val intro = varchar("intro", 512).default("")
    val since = varchar("since", 16).default("")

    val country = varchar("country", 16).default("")
    val state = varchar("state", 32).default("")
    val city = varchar("city", 32).default("")

    val productionModelsComment = varchar("production_models_comment", 256).default("")
    val stylesComment = varchar("styles_comment", 256).default("")
    val orderTypesComment = varchar("order_types_comment", 256).default("")
    val featuresComment = varchar("features_comment", 256).default("")

    val paymentPlans = varchar("payment_plans", 256).default("")
    val paymentMethods = varchar("payment_methods", 256).default("")
    val currenciesAccepted = varchar("currencies_accepted", 64).default("")

    val speciesDoes = varchar("species_does", 256).default("")
    val speciesDoesnt = varchar("species_doesnt", 256).default("")
    val speciesComment = varchar("species_comment", 256).default("")

    val notes = text("notes", eagerLoading = true).default("")

    val contactAllowed = varchar("contact_allowed", 16).nullable() // TODO: Enum
    val emailAddressObfuscated = varchar("contact_info_obfuscated", 128).default("") // TODO: Rename
}
