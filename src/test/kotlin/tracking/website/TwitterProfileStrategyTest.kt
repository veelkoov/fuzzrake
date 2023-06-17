package tracking.website

import org.junit.jupiter.api.Assertions.assertEquals
import org.junit.jupiter.api.Test
import testUtils.Resource

class TwitterProfileStrategyTest {
    val subject = TwitterProfileStrategy

    @Test
    fun `Real-life, working scenario`() {
        val result = subject.filterContents(Resource.read("/tracking/twitter_getfursuit_contents.txt"))

        assertEquals("JOIN: https://t.co/3g0nZbnhqL 👀\n\nSingle-wolf (tired) powered service running on pancakes.\n\n🎶 🎤 🦖 🎸 🎹 🥁 🤘 🎶", result)
    }

    @Test
    fun `Simplified, working scenario`() {
        val result = subject.filterContents("<html><head><meta property=\"og:description\" content=\"Expected description\"></head></html>")

        assertEquals("Expected description", result)
    }

    @Test
    fun `Empty description`() {
        val result = subject.filterContents("<html><head><meta property=\"og:description\" content=\"\"></head></html>")

        assertEquals("", result)
    }

    @Test
    fun `Unparseable input`() {
        assertEquals(
            "{\"oops\": \"This is not a HTML\"}",
            subject.filterContents("{\"oops\": \"This is not a HTML\"}"),
        )
    }

    @Test
    fun `Missing og_description meta element`() {
        assertEquals(
            "<html><head></head></html>",
            subject.filterContents("<html><head></head></html>"),
        )
    }
}
