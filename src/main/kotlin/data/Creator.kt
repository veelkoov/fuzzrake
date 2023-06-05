package data

import database.Creator

/**
 * Return creator's current name and former names.
 */
fun Creator.getAliases(): List<String> {
    return listOf(name).plus(formerly.unpack())
}
