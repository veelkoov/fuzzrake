package tracking.website

import data.Resource
import kotlin.test.Test
import kotlin.test.assertEquals

class TwitterProfileStrategyTest {
    private val subject = TwitterProfileStrategy

    @Test
    fun `Real-life, working scenario`() {
        val result = subject.filterContents(Resource.read("/tracking/twitter_getfursuit_contents.txt"))

        assertEquals("getfursu.it 🥌\nJOIN: https://t.co/3g0nZbnhqL 👀\n\nSingle-wolf (tired) powered service running on pancakes.\n\n🎶 🎤 🦖 🎸 🎹 🥁 🤘 🎶\nBeauharnois (the webserver)", result)
    }

    @Test
    fun `Simplified, working scenario`() {
        val input = "<html><head><script type=\"application/ld+json\">{\"@type\":\"ProfilePage\",\"author\":{\"description\":\"Expected description\",\"givenName\":\"Expected name\",\"homeLocation\":{\"name\":\"Expected location\"}}}</script></head></html>"
        val result = subject.filterContents(input)

        assertEquals("Expected name\nExpected description\nExpected location", result)
    }

    @Test
    fun `Empty description`() {
        val input = "<html><head><script type=\"application/ld+json\">{\"@type\":\"ProfilePage\",\"author\":{\"description\":\"\",\"givenName\":\"\",\"homeLocation\":{\"name\":\"\"}}}</script></head></html>"
        val result = subject.filterContents(input)

        assertEquals("\n\n", result)
    }

    @Test
    fun `Unparseable input`() {
        val input = "{\"oops\": \"This is not a HTML\"}"
        val result = subject.filterContents(input)

        assertEquals(input, result)
    }

    @Test
    fun `Missing script ld+json meta element`() {
        val input = "<html><head></head></html>"
        val result = subject.filterContents(input)

        assertEquals(input, result)
    }

    @Test
    fun `Missing expected ld+json field`() {
        val input = "<html><head><script type=\"application/ld+json\">{\"@type\":\"ProfilePage\",\"author\":{\"description\":\"Description\",\"givenName\":\"Name\",\"homeLocation\":{\"wrongfieldname\":\"Location\"}}}</script></head></html>"
        val result = subject.filterContents(input)

        assertEquals(input, result)
    }
}
