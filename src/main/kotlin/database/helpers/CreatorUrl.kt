package database.helpers

import database.entities.CreatorUrl
import database.entities.CreatorUrlState
import io.github.oshai.kotlinlogging.KotlinLogging

private val logger = KotlinLogging.logger {}

fun CreatorUrl.getState(): CreatorUrlState { // grep-code-optional-1-to-1-retrieval
    return if (states.empty()) {
        logger.info("${CreatorUrlState::class} does not exist for ${this@getState}, creating...")

        CreatorUrlState.new {
            this.url = this@getState
        }
    } else {
        states.single()
    }
}
