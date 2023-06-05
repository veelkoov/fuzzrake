package database

import org.jetbrains.exposed.dao.id.IntIdTable

object Creators : IntIdTable("artisans") {
    val creatorId = varchar("maker_id", 16)
    val name = varchar("name", 128)
    val formerly = varchar("formerly", 256)
    val inactiveReason = varchar("inactive_reason", 512)

    val intro = varchar("intro", 512)
    val since = varchar("since", 16)

    val country = varchar("country", 16)
    val state = varchar("state", 32)
    val city = varchar("city", 32)

    val languages = varchar("languages", 256)

    val productionModels = varchar("production_models", 256)
    val productionModelsComment = varchar("production_models_comment", 256)

    val styles = varchar("styles", 1024)
    val otherStyles = varchar("other_styles", 1024)
    val stylesComment = varchar("styles_comment", 256)

    val orderTypes = varchar("order_types", 1024)
    val otherOrderTypes = varchar("other_order_types", 1024)
    val orderTypesComment = varchar("order_types_comment", 256)

    val features = varchar("features", 1024)
    val otherFeatures = varchar("other_features", 1024)
    val featuresComment = varchar("features_comment", 256)

    val paymentPlans = varchar("payment_plans", 256)
    val paymentMethods = varchar("payment_methods", 256)
    val currenciesAccepted = varchar("currencies_accepted", 64)

    val speciesDoes = varchar("species_does", 256)
    val speciesDoesnt = varchar("species_doesnt", 256)
    val speciesComment = varchar("species_comment", 256)

    val notes = text("notes", eagerLoading = true)

    val contactAllowed = varchar("contact_allowed", 16).nullable() // TODO: Enum
    val contactMethod = varchar("contact_method", 32)
    val contactInfoObfuscated = varchar("contact_info_obfuscated", 128)
}
