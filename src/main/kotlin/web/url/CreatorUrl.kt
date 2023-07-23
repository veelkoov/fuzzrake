package web.url

import database.helpers.getState
import time.UTC
import tracking.website.Strategy
import database.entities.CreatorUrl as CreatorUrlEntity

class CreatorUrl(
    private val entity: CreatorUrlEntity,
    strategy: Strategy = Strategy.forUrl(entity.url),
): AbstractUrl(entity.url, strategy) {
    override fun recordSuccessfulFetch() {
        entity.getState().lastSuccessUtc = UTC.Now.dateTime()
    }

    override fun recordFailedFetch(code: Int, reason: String) {
        with(entity.getState()) {
            lastFailureUtc = UTC.Now.dateTime()
            lastFailureCode = code
            lastFailureReason = reason
        }
    }
}
