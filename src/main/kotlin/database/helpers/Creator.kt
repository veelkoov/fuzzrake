package database.helpers

import data.DataIntegrityFailure
import data.StrList
import data.UrlType
import data.unpack
import database.entities.Creator
import database.entities.CreatorUrl
import database.entities.CreatorVolatileData
import io.github.oshai.kotlinlogging.KotlinLogging

private val logger = KotlinLogging.logger {}

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

fun Creator.getVolatileData(): CreatorVolatileData { // grep-code-optional-1-to-1-retrieval
    return if (volatileData.empty()) {
        logger.info("${CreatorVolatileData::class} does not exist for ${this@getVolatileData}, creating...")

        CreatorVolatileData.new {
            this.creator = this@getVolatileData
        }
    } else {
        volatileData.single()
    }
}

fun Creator.getOpenFor(): StrList {
    return offersStatuses.filter { it.isOpen }.map { it.offer }
}

fun Creator.getClosedFor(): StrList {
    return offersStatuses.filterNot { it.isOpen }.map { it.offer }
}

fun Creator.getPhotoUrls(): List<CreatorUrl> {
    return creatorUrls.filter { it.type == UrlType.URL_PHOTOS.name }
}

fun Creator.getMiniatureUrls(): List<CreatorUrl> {
    return creatorUrls.filter { it.type == UrlType.URL_MINIATURES.name }
}
