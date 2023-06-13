package tracking.website

import org.junit.jupiter.api.Assertions.*
import org.junit.jupiter.api.Test

class InstagramProfileStrategyTest {
    @Test
    fun coerceUrl() {
        val subject = InstagramProfileStrategy
        val expected = "https://www.instagram.com/getfursu.it/?__a=1&__d=dis"
        val result = subject.coerceUrl("https://www.instagram.com/getfursu.it/")

        assertEquals(expected, result)
    }
}
