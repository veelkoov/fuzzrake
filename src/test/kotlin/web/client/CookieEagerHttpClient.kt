package web.client

import io.mockk.*

import org.junit.jupiter.api.DynamicTest.dynamicTest
import org.junit.jupiter.api.TestFactory
import web.snapshots.Snapshot
import web.snapshots.SnapshotMetadata
import web.url.FreeUrl
import web.url.Url
import kotlin.test.assertEquals
import kotlin.test.assertIs

class CookieEagerHttpClientTest {
    private val getTestCases = mapOf(
        listOf(
            "https://www.instagram.com/getfursu.it/",
            "https://www.instagram.com/finland/",
        ) to listOf(
            "https://www.instagram.com/",
            "https://www.instagram.com/getfursu.it/",
            "https://www.instagram.com/finland/",
        ),
        listOf(
            "https://twitter.com/getfursuit",
            "https://twitter.com/veelkoov",
        ) to listOf(
            "https://twitter.com/",
            "https://twitter.com/getfursuit",
            "https://twitter.com/veelkoov",
        ),
    )

    @TestFactory
    fun get() = getTestCases.map { (requested, expected) ->
        dynamicTest(requested[0]) {
            val result = mutableListOf<String>()

            val clientMock = mockk<FastHttpClient>()
            every { clientMock.get(any<Url>()) } answers {
                val arg1 = it.invocation.args[0]!!
                assertIs<Url>(arg1)
                val url = arg1.getUrl()

                result.add(url)

                Snapshot("", SnapshotMetadata(url, "", "", 0, mapOf(), 0, listOf()))
            }

            val subject = CookieEagerHttpClient(clientMock)

            requested.forEach {
                subject.get(FreeUrl(it))
            }

            assertEquals(expected, result)
        }
    }
}
