package tracking.website

import org.junit.jupiter.api.Test

import org.junit.jupiter.api.Assertions.*

class StrategyTest {
    @Test
    fun `Twitter profile strategy gets used`() {
        assertEquals(TwitterProfileStrategy, Strategy.forUrl("https://twitter.com/getfursuit"))
        assertEquals(TwitterProfileStrategy, Strategy.forUrl("https://twitter.com/getfursuit?s=09"))
    }

    @Test
    fun `Instagram profile strategy gets used`() {
        assertEquals(InstagramProfileStrategy, Strategy.forUrl("https://www.instagram.com/getfursu.it/"))
        assertEquals(InstagramProfileStrategy, Strategy.forUrl("https://www.instagram.com/getfursu.it"))
    }

    @Test
    fun `Trello strategy gets used`() {
        assertEquals(TrelloStrategy, Strategy.forUrl("https://trello.com/b/aBcDeFgHi/some-test-name"))
        assertEquals(TrelloStrategy, Strategy.forUrl("https://trello.com/b/aBcDeFgHi"))
        assertEquals(TrelloStrategy, Strategy.forUrl("https://trello.com/c/aBcDeFgHi/some-test-description"))
        assertEquals(TrelloStrategy, Strategy.forUrl("https://trello.com/c/aBcDeFgHi"))
    }
}
