package tracking.website

import data.Resource
import web.url.FreeUrl
import kotlin.test.Test
import kotlin.test.assertEquals

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
        val input = "{\"graphql\": {\"user\": {\"biography\": \"Expected description\"}}}"
        val result = subject.filterContents(input)

        assertEquals("Expected description", result)
    }

    @Test
    fun `Empty description`() {
        val input = "{\"graphql\": {\"user\": {\"biography\": \"\"}}}"
        val result = subject.filterContents(input)

        assertEquals("", result)
    }

    @Test
    fun `Unparseable input`() {
        val input = "<p>Oops, this is not JSON</p>"
        val result = subject.filterContents(input)

        assertEquals(input, result)
    }

    @Test
    fun `Missing expected field`() {
        val input = "{\"graphql\": {\"user\": {\"wrongfield\": \"a text\"}}}"
        val result = subject.filterContents(input)

        assertEquals(input, result)
    }
}
