package web.url

import tracking.website.Strategy
import database.entities.CreatorUrl as CreatorUrlEntity

class CreatorUrl(
    private val entity: CreatorUrlEntity,
): AbstractUrl(entity.url, Strategy.forUrl(entity.url)) {
    override fun getUrl() = entity.url
}
