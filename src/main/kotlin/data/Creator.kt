package data

import database.Creator

/**
 * Return creator's current name and former names.
 */
fun Creator.aliases(): List<String> {
    return listOf(name).plus(formerly.unpack())
}

/**
 * Return any available creator ID, preferring the current one.
 */
fun Creator.lastCreatorId(): String {
    return if (creatorId != "") creatorId else "TODO" // TODO: Get from creator IDs
}
