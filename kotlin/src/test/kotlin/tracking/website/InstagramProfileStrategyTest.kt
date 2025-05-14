package tracking.website

import web.url.FreeUrl
import kotlin.test.Test
import kotlin.test.assertEquals

class InstagramProfileStrategyTest {
    private val subject = InstagramProfileStrategy

    @Test
    fun coerceUrl() {
        val expected = "https://www.instagram.com/getfursu.it/profilecard/"
        val input = FreeUrl("https://www.instagram.com/getfursu.it/")
        val result = subject.getUrlForTracking(input).getUrl()

        assertEquals(expected, result)
    }

    @Test
    fun `Simplified, working scenario`() {
        val input = "<html><head><meta content=\"Expected description\" property=\"description\"/></head></html>"
        val result = subject.filterContents(input)

        assertEquals("Expected description", result)
    }

    @Test
    fun `Empty description`() {
        val input = "<html><head><meta content=\"\" property=\"description\"/></head></html>"
        val result = subject.filterContents(input)

        assertEquals("", result)
    }

    @Test
    fun `Unparseable input`() {
        val input = "{\"oops\": \"This is not a HTML\"}"
        val result = subject.filterContents(input)

        assertEquals(input, result)
    }

    @Test
    fun `Missing expected field`() {
        val input = "<html><head><meta content=\"TheDescription\" property=\"not-description\"/></head></html>"
        val result = subject.filterContents(input)

        assertEquals(input, result)
    }
}
