package database.helpers

import database.entities.CreatorUrlState
import java.time.LocalDateTime

fun CreatorUrlState.lastFetchTime(): LocalDateTime? {
    val lastFailure = this.lastFailureUtc
    val lastSuccess = this.lastSuccessUtc

    return if (lastFailure != null && lastSuccess != null) {
        if (lastFailure.isAfter(lastSuccess)) lastFailure else lastSuccess
    } else {
        lastFailure ?: lastSuccess
    }
}
