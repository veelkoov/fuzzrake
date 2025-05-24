package tasks

import config.Configuration
import data.UrlType
import database.Database
import database.entities.Creator
import database.helpers.getMiniatureUrls
import database.helpers.getPhotoUrls
import database.helpers.lastCreatorId
import database.tables.CreatorUrls
import io.github.oshai.kotlinlogging.KotlinLogging
import org.jetbrains.exposed.dao.with
import tasks.miniaturesUpdate.FurtrackMiniatureUrlResolver
import tasks.miniaturesUpdate.MiniatureUrlResolverException
import tasks.miniaturesUpdate.ScritchMiniatureUrlResolver
import database.entities.CreatorUrl as CreatorUrlEntity

private val logger = KotlinLogging.logger {}

class MiniaturesUpdate(
    private val config: Configuration,
    private val database: Database = Database(config.databasePath),
    private val scritch: ScritchMiniatureUrlResolver = ScritchMiniatureUrlResolver(),
    private val furtrack: FurtrackMiniatureUrlResolver = FurtrackMiniatureUrlResolver(),
) {
    private fun updatePhotos(creator: Creator) {
        val pictureUrls = creator.getPhotoUrls().associateBy { it.url }.values.toList() // Removing duplicate URLs
        val miniatureUrls = creator.getMiniatureUrls().associateBy { it.url }

        if (pictureUrls.isEmpty()) {
            if (miniatureUrls.isNotEmpty()) {
                logger.info { "Removing miniatures of ${creator.lastCreatorId()}" }

                miniatureUrls.forEach { (_, entity) -> entity.delete() }
            }

            return
        }

        if (pictureUrls.size == miniatureUrls.size) {
            return
        }

        if (!checkUrlsSupportedLogUnsupported(pictureUrls)) {
            return
        }

        logger.info { "Retrieving miniatures for ${creator.lastCreatorId()}" }

        val newUrls = retrieveMiniatureUrls(pictureUrls)
        miniatureUrls.minus(newUrls.toSet()).forEach { (_, entity) ->
            logger.info { "Removing old miniature of ${creator.lastCreatorId()}: '${entity.url}'" }

            entity.delete()
        }

        newUrls.minus(miniatureUrls.keys).forEach {
            logger.info { "Adding new miniature of ${creator.lastCreatorId()}: '${it}'" }

            CreatorUrlEntity.new {
                this.creator = creator
                type = UrlType.URL_MINIATURES.name
                url = it
            }
        }

        logger.info { "Successfully updated ${creator.lastCreatorId()}" }
    }

    private fun checkUrlsSupportedLogUnsupported(pictureUrls: List<CreatorUrlEntity>): Boolean {
        var result = true

        pictureUrls.filterNot { furtrack.supports(it.url) || scritch.supports(it.url) }.forEach {
            logger.warn { "Unsupported URL for ${it.creator.lastCreatorId()}: '${it.url}'" }

            result = false
        }

        return result
    }

    private fun retrieveMiniatureUrls(pictureUrls: List<CreatorUrlEntity>): List<String> {
        return pictureUrls.map {
            val param = web.url.CreatorUrl(it)

            if (furtrack.supports(it.url)) {
                furtrack.getMiniatureUrl(param)
            } else {
                scritch.getMiniatureUrl(param)
            }
        }
    }
}
