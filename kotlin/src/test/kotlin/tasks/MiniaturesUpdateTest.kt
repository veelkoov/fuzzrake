package tasks

import data.UrlType
import database.entities.Creator
import database.entities.CreatorUrl
import database.helpers.getMiniatureUrls
import database.helpers.getPhotoUrls
import io.mockk.every
import io.mockk.mockk
import org.jetbrains.exposed.dao.entityCache
import org.jetbrains.exposed.dao.id.EntityID
import tasks.miniaturesUpdate.FurtrackMiniatureUrlResolver
import tasks.miniaturesUpdate.ScritchMiniatureUrlResolver
import testUtils.disposableDatabase
import testUtils.getNullConfiguration
import web.url.Url
import kotlin.test.Test
import kotlin.test.assertEquals
import kotlin.test.assertNotNull

class MiniaturesUpdateTest {
    @Test
    fun execute() = disposableDatabase { database, transaction ->
        // "min_" is a prefix only in retrieved miniatures, not in the initial state miniatures
        val emptyPhotosAndMiniaturesStayEmptyId = setupCreatorGetId("CREAT01", listOf(), listOf())

        val equalNumberOfPhotosAndMiniaturesCauseNoChangeId = setupCreatorGetId("CREAT02",
            listOf("http://CREAT02_photo_1", "http://CREAT02_photo_2"),
            listOf("CREAT02_miniature_1", "CREAT02_miniature_2"))

        val miniaturesClearedWhenPhotosEmptyId = setupCreatorGetId("CREAT03", listOf(),
            listOf("CREAT03_miniature_1", "CREAT03_miniature_2"))

        val miniatureRemainWhenOnePhotoIsUnsupportedId = setupCreatorGetId("CREAT04",
            listOf("http://pic_CREAT04_scritch_1", "http://pic_CREAT04_furtrack_1", "http://bad_CREAT04"),
            listOf("CREAT04_miniature_1"))

        val successfulMixedRetrievalsId = setupCreatorGetId("CREAT05",
            listOf("http://pic_CREAT05_furtrack_1", "http://pic_CREAT05_scritch_1", "http://pic_CREAT05_furtrack_2"), listOf())

        val removeAndAddId = setupCreatorGetId("CREAT06",
            listOf("http://pic_CREAT06_scritch_1", "http://pic_CREAT06_furtrack_4"), // The number needs to differ, otherwise it won't notice any difference
            listOf("http://min_CREAT06_scritch_1", "http://min_CREAT06_furtrack_2", "http://min_CREAT06_furtrack_3"))

        val duplicatedPhotosAreIgnoredId = setupCreatorGetId("CREAT07",
            listOf("http://pic_CREAT07_scritch_1", "http://pic_CREAT07_scritch_1"),
            listOf("CREAT07_from_duplicate"))

        transaction.commit()
        transaction.entityCache.flush()

        val scritch = mockk<ScritchMiniatureUrlResolver>()
        every { scritch.supports(any())} answers { (it.invocation.args[0] as String).contains("scritch") }
        every { scritch.getMiniatureUrl(any())} answers {
            (it.invocation.args[0] as Url).getUrl().replace("pic_", "min_")
        }

        val furtrack = mockk<FurtrackMiniatureUrlResolver>()
        every { furtrack.supports(any())} answers { (it.invocation.args[0] as String).contains("furtrack") }
        every { furtrack.getMiniatureUrl(any())} answers {
            (it.invocation.args[0] as Url).getUrl().replace("pic_", "min_")
        }

        // Execution

        val subject = MiniaturesUpdate(getNullConfiguration(), database, scritch, furtrack)
        subject.execute()

        transaction.commit()
        transaction.entityCache.flush()

        // Verification

        validateCreator(emptyPhotosAndMiniaturesStayEmptyId, setOf(), setOf())

        validateCreator(equalNumberOfPhotosAndMiniaturesCauseNoChangeId,
            setOf("http://CREAT02_photo_1", "http://CREAT02_photo_2"),
            setOf("CREAT02_miniature_1", "CREAT02_miniature_2"))

        validateCreator(miniaturesClearedWhenPhotosEmptyId, setOf(), setOf())

        validateCreator(miniatureRemainWhenOnePhotoIsUnsupportedId,
            setOf("http://pic_CREAT04_scritch_1", "http://pic_CREAT04_furtrack_1", "http://bad_CREAT04"),
            setOf("CREAT04_miniature_1"))

        validateCreator(successfulMixedRetrievalsId,
            setOf("http://pic_CREAT05_furtrack_1", "http://pic_CREAT05_scritch_1", "http://pic_CREAT05_furtrack_2"),
            setOf("http://min_CREAT05_furtrack_1", "http://min_CREAT05_scritch_1", "http://min_CREAT05_furtrack_2"))

        validateCreator(removeAndAddId,
            setOf("http://pic_CREAT06_scritch_1", "http://pic_CREAT06_furtrack_4"),
            setOf("http://min_CREAT06_scritch_1", "http://min_CREAT06_furtrack_4"))

        validateCreator(duplicatedPhotosAreIgnoredId,
            setOf("http://pic_CREAT07_scritch_1"),
            setOf("CREAT07_from_duplicate"))
    }

    private fun setupCreatorGetId(creatorId: String, photos: List<String>, miniatures: List<String>): EntityID<Int> {
        val creator = Creator.new { this.creatorId = creatorId }

        photos.forEach {
            CreatorUrl.new {
                this.creator = creator
                url = it
                type = UrlType.URL_PHOTOS.name
            }
        }

        miniatures.forEach {
            CreatorUrl.new {
                this.creator = creator
                url = it
                type = UrlType.URL_MINIATURES.name
            }
        }

        return creator.id
    }

    private fun validateCreator(id: EntityID<Int>, expectedPhotos: Set<String>, expectedMiniatures: Set<String>) {
        val creator = Creator.findById(id)
        assertNotNull(creator)

        assertEquals(expectedPhotos, creator.getPhotoUrls().map { it.url }.toSet())
        assertEquals(expectedMiniatures, creator.getMiniatureUrls().map { it.url }.toSet())
    }
}
