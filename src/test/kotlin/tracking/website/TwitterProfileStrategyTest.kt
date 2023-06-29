package tracking.website

import data.Resource
import org.junit.jupiter.api.Assertions.assertEquals
import org.junit.jupiter.api.Test

class TwitterProfileStrategyTest {
    private val subject = TwitterProfileStrategy

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
        val result = subject.filterContents("{\"oops\": \"This is not a HTML\"}")

        assertEquals("{\"oops\": \"This is not a HTML\"}", result)
    }

    @Test
    fun `Missing og_description meta element`() {
        val result = subject.filterContents("<html><head></head></html>")

        assertEquals("<html><head></head></html>", result)
    }
}
