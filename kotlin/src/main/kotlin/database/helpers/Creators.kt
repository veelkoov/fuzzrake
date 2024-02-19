package database.helpers

import database.entities.Creator
import database.tables.Creators
import org.jetbrains.exposed.sql.SizedIterable

fun Creators.getActive(): SizedIterable<Creator> {
    return Creator.find { inactiveReason eq "" }
}
