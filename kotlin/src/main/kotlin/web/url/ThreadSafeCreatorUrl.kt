package web.url

import data.ThreadSafe
import database.helpers.getState
import time.UTC
import tracking.website.Strategy
import java.time.LocalDateTime
import database.entities.CreatorUrl as CreatorUrlEntity

class ThreadSafeCreatorUrl(
    entity: CreatorUrlEntity,
    strategy: Strategy = Strategy.forUrl(entity.url),
): AbstractUrl(entity.url, strategy) {
    private val entity = ThreadSafe(entity)
    private var lastSuccess: LocalDateTime? = null
    private var lastFailure: LocalDateTime? = null
    private var lastFailureCode: Int? = null
    private var lastFailureReason: String? = null

    override fun recordSuccessfulFetch() {
        lastSuccess = UTC.Now.dateTime()
    }

    override fun recordFailedFetch(code: Int, reason: String) {
        lastFailure = UTC.Now.dateTime()
        lastFailureCode = code
        lastFailureReason = reason
    }

    fun commit() {
        val state = entity.get().getState()

        lastSuccess?.let { state.lastSuccessUtc = it }
        lastFailure?.let { state.lastFailureUtc = it }
        lastFailureCode?.let { state.lastFailureCode = it }
        lastFailureReason?.let { state.lastFailureReason = it }
    }
}
