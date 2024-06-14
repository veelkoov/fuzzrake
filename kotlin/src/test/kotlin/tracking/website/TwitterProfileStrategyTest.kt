package tracking.website

import kotlin.test.Test
import kotlin.test.assertEquals

class TwitterProfileStrategyTest {
    private val subject = TwitterProfileStrategy

    @Test
    fun `Simplified, working scenario`() {
        val input = "<html><head><meta content=\"TheTitle\" property=\"og:title\"/><meta content=\"TheDescription\" property=\"og:description\"/></head></html>"
        val result = subject.filterContents(input)

        assertEquals("TheTitle\nTheDescription", result)
    }

    @Test
    fun `Empty information`() {
        val input = "<html><head><meta content=\"\" property=\"og:title\"/><meta content=\"\" property=\"og:description\"/></head></html>"
        val result = subject.filterContents(input)

        assertEquals("\n", result)
    }

    @Test
    fun `Unparseable input`() {
        val input = "{\"oops\": \"This is not a HTML\"}"
        val result = subject.filterContents(input)

        assertEquals(input, result)
    }

    @Test
    fun `Missing expected head element`() {
        val input = "<html><head><meta content=\"TheDescription\" property=\"og:description\"/></head></html>"
        val result = subject.filterContents(input)

        assertEquals(input, result)
    }
}
