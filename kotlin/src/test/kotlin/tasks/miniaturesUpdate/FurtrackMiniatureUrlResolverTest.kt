package tasks.miniaturesUpdate

import io.ktor.http.*
import testUtils.ExpectedHttpCall
import testUtils.getHttpClientMock
import web.url.FreeUrl
import kotlin.test.*

class FurtrackMiniatureUrlResolverTest{
    @Test
    fun `Test successful retrieval`() {
        val httpClient = getHttpClientMock(
            ExpectedHttpCall(
                "https://orca2.furtrack.com/thumb/49767.jpg",
                null,
                mapOf(),
                "",
                mapOf(),
            ),
            ExpectedHttpCall(
                "https://orca2.furtrack.com/thumb/41933.jpg",
                null,
                mapOf(),
                "",
                mapOf(),
            ),
        )

        val subject = FurtrackMiniatureUrlResolver(httpClient)

        assertEquals(
            "https://orca2.furtrack.com/thumb/49767.jpg",
            subject.getMiniatureUrl(FreeUrl("https://www.furtrack.com/p/49767"))
        )
        assertEquals(
            "https://orca2.furtrack.com/thumb/41933.jpg",
            subject.getMiniatureUrl(FreeUrl("https://www.furtrack.com/p/41933"))
        )
    }

    @Test
    fun `Test non-200 HTTP response`() {
        val httpClient = getHttpClientMock(
            ExpectedHttpCall(
                "https://orca2.furtrack.com/thumb/49767.jpg",
                null,
                mapOf(),
                "",
                mapOf(),
                HttpStatusCode.Forbidden,
            ),
        )

        val subject = FurtrackMiniatureUrlResolver(httpClient)

        val exception = assertFailsWith <MiniatureUrlResolverException> {
            subject.getMiniatureUrl(FreeUrl("https://www.furtrack.com/p/49767"))
        }

        assertEquals(exception.message, "Non-200 HTTP response code")
    }
}
