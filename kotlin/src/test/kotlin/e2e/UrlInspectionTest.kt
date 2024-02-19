package e2e

import config.Configuration
import database.entities.Creator
import database.entities.CreatorUrl
import database.helpers.getState
import io.ktor.client.*
import io.ktor.client.engine.mock.*
import io.ktor.utils.io.*
import org.jetbrains.exposed.dao.entityCache
import testUtils.disposableDatabase
import testUtils.disposableDirectory
import tasks.UrlsInspection
import tasks.UrlsInspectionOptions
import testUtils.getNullConfiguration
import web.client.FastHttpClient
import web.snapshots.SnapshotsManager
import kotlin.test.Test
import kotlin.test.assertEquals
import kotlin.test.assertNotNull
import kotlin.test.assertNull

class UrlInspectionTest {
    @Test
    fun `Inspecting perform proper changes in the DB`() = disposableDatabase { database, transaction ->
        val creator = Creator.new { }
        val creatorId = creator.id

        CreatorUrl.new {
            this.creator = creator
            url = "https://getfursu.it"
            type = "URL_WEBSITE" // TODO: Enum
        }

        transaction.entityCache.clear(true)

        val creatorBefore = Creator.findById(creatorId)
        assertNotNull(creatorBefore)
        assertEquals(1, creatorBefore.creatorUrls.count())
        val urlBefore = creatorBefore.creatorUrls.first()
        assertNull(urlBefore.getState().lastFailureUtc)
        assertNull(urlBefore.getState().lastSuccessUtc)

        val mockEngine = MockEngine { _ ->
            respond(content = ByteReadChannel(""))
        }
        val httpClient = FastHttpClient(HttpClient(mockEngine))

        disposableDirectory { tempDirPath ->
            val snapshotsManager = SnapshotsManager(tempDirPath.toString(), httpClient)

            val subject = UrlsInspection(
                getNullConfiguration(),
                UrlsInspectionOptions(1),
                database = database,
                snapshotsManager = snapshotsManager,
            )

            subject.run()
        }

        transaction.entityCache.clear(true)

        val creatorAfter = Creator.findById(creatorId)
        assertNotNull(creatorAfter)
        assertEquals(1, creatorAfter.creatorUrls.count())
        val urlAfter = creatorAfter.creatorUrls.first()
        assertNull(urlAfter.getState().lastFailureUtc)
        assertNotNull(urlAfter.getState().lastSuccessUtc)
    }
}
