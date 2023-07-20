package database.helpers

import database.entities.CreatorUrlState
import java.time.LocalDateTime

fun CreatorUrlState.lastFetchTime(): LocalDateTime? {
    val lastFailure = this.lastFailure
    val lastSuccess = this.lastSuccess

    return if (lastFailure != null && lastSuccess != null) {
        if (lastFailure.isAfter(lastSuccess)) lastFailure else lastSuccess
    } else {
        lastFailure ?: lastSuccess
    }
}
