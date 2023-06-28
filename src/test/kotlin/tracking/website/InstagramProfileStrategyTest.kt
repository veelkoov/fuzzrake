package tracking.website

import org.junit.jupiter.api.Assertions.*
import org.junit.jupiter.api.Test
import web.url.FreeUrl

class InstagramProfileStrategyTest {
    @Test
    fun coerceUrl() {
        val subject = InstagramProfileStrategy
        val expected = "https://www.instagram.com/getfursu.it/?__a=1&__d=dis"
        val input = FreeUrl("https://www.instagram.com/getfursu.it/")
        val result = subject.getUrlForTracking(input).getUrl()

        assertEquals(expected, result)
    }
}
