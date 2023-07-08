package database.helpers

import database.entities.CreatorUrl
import database.entities.CreatorUrlState

fun CreatorUrl.getState(): CreatorUrlState {
    return if (states.empty()) {
        CreatorUrlState.new {
            this.url = this@getState
        }
    } else {
        states.single()
    }
}
