package tasks.miniaturesUpdate

import io.ktor.http.*
import testUtils.ExpectedHttpCall
import testUtils.getHttpClientMock
import web.url.FreeUrl
import kotlin.test.*

class FurtrackMiniatureUrlResolverTest{
    @Test
    fun getMiniatureUrl() {
        val httpClient = getHttpClientMock(
            ExpectedHttpCall(
                "https://solar.furtrack.com/view/post/49767",
                null,
                mapOf(),
                "{\"post\": {\"postStub\": \"49767-b29a0ffc76f98b18ebb5a0a7e394bbab\", \"metaFiletype\": \"jpg\"}}",
                mapOf(),
            ),
            ExpectedHttpCall(
                "https://solar.furtrack.com/view/post/41933",
                null,
                mapOf(),
                "{\"post\": {\"postStub\": \"41933-68dcba69d82cdafe787b42f2a52b49b6\", \"metaFiletype\": \"jpg\"}}",
                mapOf(),
            ),
        )

        val subject = FurtrackMiniatureUrlResolver(httpClient)

        assertEquals(
            "https://orca.furtrack.com/gallery/thumb/49767-b29a0ffc76f98b18ebb5a0a7e394bbab.jpg",
            subject.getMiniatureUrl(FreeUrl("https://www.furtrack.com/p/49767"))
        )
        assertEquals(
            "https://orca.furtrack.com/gallery/thumb/41933-68dcba69d82cdafe787b42f2a52b49b6.jpg",
            subject.getMiniatureUrl(FreeUrl("https://www.furtrack.com/p/41933"))
        )
    }

    @Test
    fun `Test non-200 HTTP response`() {
        val httpClient = getHttpClientMock(
            ExpectedHttpCall(
                "https://solar.furtrack.com/view/post/49767",
                null,
                mapOf(),
                "",
                mapOf(),
                HttpStatusCode.InternalServerError,
            ),
        )

        val subject = FurtrackMiniatureUrlResolver(httpClient)

        val exception = assertFailsWith <MiniatureUrlResolverException> {
            subject.getMiniatureUrl(FreeUrl("https://www.furtrack.com/p/49767"))
        }

        assertEquals(exception.message, "Non-200 HTTP response code")
    }

    @Test
    fun `Test wrong JSON response`() {
         val httpClient = getHttpClientMock(
            ExpectedHttpCall(
                "https://solar.furtrack.com/view/post/49767",
                null,
                mapOf(),
                "This is unparseable",
                mapOf(),
            ),
        )

        val subject = FurtrackMiniatureUrlResolver(httpClient)
        val exception = assertFailsWith <MiniatureUrlResolverException> {
            subject.getMiniatureUrl(FreeUrl("https://www.furtrack.com/p/49767"))
        }

        assertEquals(exception.message, "Wrong JSON data")
    }
}
