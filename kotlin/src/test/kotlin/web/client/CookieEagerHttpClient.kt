package web.client

import io.mockk.every
import io.mockk.mockk
import org.junit.jupiter.api.DynamicTest.dynamicTest
import org.junit.jupiter.api.TestFactory
import testUtils.getSnapshot
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
            every { clientMock.fetch(any<Url>()) } answers {
                val arg1 = it.invocation.args[0]!!
                assertIs<Url>(arg1)
                val url = arg1.getUrl()

                result.add(url)

                getSnapshot(url = url)
            }

            val subject = CookieEagerHttpClient(clientMock)

            requested.forEach {
                subject.fetch(FreeUrl(it))
            }

            assertEquals(expected, result)
        }
    }
}
