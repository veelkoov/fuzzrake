package database.helpers

import data.DataIntegrityFailure
import data.unpack
import database.entities.Creator

/**
 * Return creator's current name and former names.
 */
fun Creator.aliases(): List<String> {
    return listOf(name).plus(formerly.unpack()).filterNot { it == "" }
}

/**
 * Return any available creator ID, preferring the current one.
 */
fun Creator.lastCreatorId(): String {
    return if (creatorId != "") {
        creatorId
    } else {
        creatorIds.firstOrNull()?.creatorId
            ?: throw DataIntegrityFailure("No creator should exist with no creator ID")
    }
}
