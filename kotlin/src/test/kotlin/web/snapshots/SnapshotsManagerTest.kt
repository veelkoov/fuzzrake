package web.snapshots

import io.mockk.CapturingSlot
import io.mockk.every
import io.mockk.mockk
import testUtils.disposableDirectory
import testUtils.getSnapshot
import web.client.HttpClientInterface
import web.url.FreeUrl
import java.time.LocalDateTime
import kotlin.test.Test
import kotlin.test.assertEquals

class SnapshotsManagerTest {
    @Test
    fun `Get creates proper snapshot from data received from the http client`() {
        val url = "https://getfursu.it/"
        val statusCode = 200
        val headers = mapOf(
            "content-type" to listOf("text/html"),
            "content-length" to listOf("4096")
        )
        val ownerName = "The amazing studio"
        val errors = listOf<String>()
        val contents = "test contents"
        val now = LocalDateTime.now().toString()

        val slot = CapturingSlot<FreeUrl>()
        val httpClientMock = mockk<HttpClientInterface>()
        every { httpClientMock.fetch(capture(slot)) } answers {
            getSnapshot(contents, slot.captured.getUrl(), ownerName, now, statusCode, headers, errors)
        }

        val result = disposableDirectory { tempDirectoryPath ->
            val subject = SnapshotsManager(tempDirectoryPath.toString(), httpClientMock)

            subject.get(FreeUrl(url), true)
        }

        assertEquals(contents, result.contents)
        assertEquals(url, result.metadata.url)
        assertEquals(statusCode, result.metadata.httpCode)
        assertEquals(headers, result.metadata.headers)
        assertEquals(ownerName, result.metadata.ownerName)
        assertEquals(now, result.metadata.retrievedAt)
        assertEquals(errors, result.metadata.errors)
    }
}
