package tracking.website

import org.junit.jupiter.api.Test

import org.junit.jupiter.api.Assertions.*
import testUtils.Resource

class TwitterProfileStrategyTest {
    @Test
    fun filter() {
        val subject = TwitterProfileStrategy
        val expected = "JOIN: https://t.co/3g0nZbnhqL \uD83D\uDC40\n\nSingle-wolf (tired) powered service running on pancakes.\n\n\uD83C\uDFB6 \uD83C\uDFA4 \uD83E\uDD96 \uD83C\uDFB8 \uD83C\uDFB9 \uD83E\uDD41 \uD83E\uDD18 \uD83C\uDFB6"
        val result = subject.filterContents(Resource.read("/tracking/twitter_getfursuit_contents.txt"))

        assertEquals(expected, result)
    }
}
