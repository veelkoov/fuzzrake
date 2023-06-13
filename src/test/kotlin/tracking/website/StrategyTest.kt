package tracking.website

import org.junit.jupiter.api.Test

import org.junit.jupiter.api.Assertions.*

class StrategyTest {
    @Test
    fun `Twitter profile strategy gets used`() {
        assertEquals(TwitterProfileStrategy, Strategy.forUrl("https://twitter.com/getfursuit"))
    }

    @Test
    fun `Instagram profile strategy gets used`() {
        assertEquals(InstagramProfileStrategy, Strategy.forUrl("https://www.instagram.com/getfursu.it/"))
    }
}
