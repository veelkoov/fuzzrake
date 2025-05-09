package database.entities

import database.helpers.lastCreatorId
import database.tables.*
import org.jetbrains.exposed.dao.IntEntity
import org.jetbrains.exposed.dao.IntEntityClass
import org.jetbrains.exposed.dao.id.EntityID

class Creator(id: EntityID<Int>) : IntEntity(id) {
    companion object : IntEntityClass<Creator>(Creators)

    var creatorId by Creators.creatorId
    var name by Creators.name
    var formerly by Creators.formerly
    var inactiveReason by Creators.inactiveReason

    var intro by Creators.intro
    var since by Creators.since

    var country by Creators.country
    var state by Creators.state
    var city by Creators.city

    var productionModelsComment by Creators.productionModelsComment
    var stylesComment by Creators.stylesComment
    var orderTypesComment by Creators.orderTypesComment
    var featuresComment by Creators.featuresComment

    var paymentPlans by Creators.paymentPlans
    var paymentMethods by Creators.paymentMethods
    var currenciesAccepted by Creators.currenciesAccepted

    var speciesDoes by Creators.speciesDoes
    var speciesDoesnt by Creators.speciesDoesnt
    var speciesComment by Creators.speciesComment

    var notes by Creators.notes

    var contactAllowed by Creators.contactAllowed

    val creatorIds by CreatorId referrersOn CreatorIds.creator
    val creatorUrls by CreatorUrl referrersOn CreatorUrls.creator
    val offersStatuses by CreatorOfferStatus referrersOn CreatorOffersStatuses.creator
    val volatileData by CreatorVolatileData referrersOn CreatorVolatileDatas.creator // TODO: 1:1

    override fun toString(): String {
        return "${lastCreatorId()}[${id.value}]"
    }
}
