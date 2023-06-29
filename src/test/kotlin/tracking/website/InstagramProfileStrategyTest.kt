package tracking.website

import data.Resource
import org.junit.jupiter.api.Assertions.*
import org.junit.jupiter.api.DynamicTest
import org.junit.jupiter.api.DynamicTest.dynamicTest
import org.junit.jupiter.api.Test
import org.junit.jupiter.api.TestFactory
import web.url.FreeUrl

class InstagramProfileStrategyTest {
    private val subject = InstagramProfileStrategy

    @Test
    fun coerceUrl() {
        val expected = "https://www.instagram.com/getfursu.it/?__a=1&__d=dis"
        val input = FreeUrl("https://www.instagram.com/getfursu.it/")
        val result = subject.getUrlForTracking(input).getUrl()

        assertEquals(expected, result)
    }

    @Test
    fun `Real-life, working scenario`() {
        val result = subject.filterContents(Resource.read("/tracking/instagram_getfursuit_contents.txt"))

        assertEquals("Single-wolf (tired) powered service running on pancakes. 🎶 🎤 🦖 🎸 🎹 🥁 🤘 🎶", result)
    }

    @Test
    fun `Simplified, working scenario`() {
        val result = subject.filterContents("{\"graphql\": {\"user\": {\"biography\": \"Expected description\"}}}")

        assertEquals("Expected description", result)
    }

    @Test
    fun `Empty description`() {
        val result = subject.filterContents("{\"graphql\": {\"user\": {\"biography\": \"\"}}}")

        assertEquals("", result)
    }

    @TestFactory
    fun `Test filtering failures`() = listOf(
        "<p>Oops, this is not JSON</p>",
        "0", // Wrong root type
        "{\"graphql\": \"Oh no\"}", // Wrong middle type
        "{\"graphql\": {\"us__er\": {\"biography\": \"Expected description\"}}}", // Missing middle key
        "{\"graphql\": {\"user\": {\"biography\": 0}}}", // Wrong leaf type
        "{\"graphql\": {\"user\": {\"biography\": null}}}", // Null leaf
    ).map { input ->
        dynamicTest(input) {
            val result = subject.filterContents(input)

            assertEquals(input, result)
        }
    }
}
