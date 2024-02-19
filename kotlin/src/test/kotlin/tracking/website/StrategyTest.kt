package tracking.website


import kotlin.test.Test
import kotlin.test.assertEquals
import kotlin.test.assertNotEquals
import kotlin.test.assertNull

class StrategyTest {
    @Test
    fun `Twitter profile strategy is not used for cookie init URL`() {
        assertNull(Strategy.forUrl(TwitterProfileStrategy.getCookieInitUrl().getUrl()).getCookieInitUrl())
    }

    @Test
    fun `Instagram profile strategy is not used for cookie init URL`() {
        assertNull(Strategy.forUrl(InstagramProfileStrategy.getCookieInitUrl().getUrl()).getCookieInitUrl())
    }

    @Test
    fun `Twitter profile strategy gets used when supposed`() {
        assertEquals(TwitterProfileStrategy, Strategy.forUrl("https://twitter.com/getfursuit"))
        assertEquals(TwitterProfileStrategy, Strategy.forUrl("https://twitter.com/getfursuit?s=09"))
    }

    @Test
    fun `Instagram profile strategy gets used when supposed`() {
        assertEquals(InstagramProfileStrategy, Strategy.forUrl("https://www.instagram.com/getfursu.it/"))
        assertEquals(InstagramProfileStrategy, Strategy.forUrl("https://www.instagram.com/getfursu.it"))
        assertNotEquals(InstagramProfileStrategy, Strategy.forUrl("https://www.instagram.com/p/Ct6gnaEtZtJ/"))
        assertNotEquals(InstagramProfileStrategy, Strategy.forUrl("https://www.instagram.com/s/aAaAaAaAaAaAaAaAaAaAaAaAaAaAaAaAaAaA"))
    }

    @Test
    fun `Trello strategy gets used`() {
        assertEquals(TrelloStrategy, Strategy.forUrl("https://trello.com/b/aBcDeFgHi/some-test-name"))
        assertEquals(TrelloStrategy, Strategy.forUrl("https://trello.com/b/aBcDeFgHi"))
        assertEquals(TrelloStrategy, Strategy.forUrl("https://trello.com/b/aBcDeFgHi/"))
        assertEquals(TrelloStrategy, Strategy.forUrl("https://trello.com/c/aBcDeFgHi/some-test-description"))
        assertEquals(TrelloStrategy, Strategy.forUrl("https://trello.com/c/aBcDeFgHi"))
        assertEquals(TrelloStrategy, Strategy.forUrl("https://trello.com/c/aBcDeFgHi/"))
    }

    @Test
    fun `Fur Affinity profile strategy gets used`() {
        assertEquals(FurAffinityProfileStrategy, Strategy.forUrl("http://www.furaffinity.net/user/lisoov/#profile"))
        assertEquals(FurAffinityProfileStrategy, Strategy.forUrl("https://www.furaffinity.net/user/lisoov/"))
        assertEquals(FurAffinityProfileStrategy, Strategy.forUrl("https://www.furaffinity.net/user/lisoov#profile/"))
        assertEquals(FurAffinityProfileStrategy, Strategy.forUrl("https://www.furaffinity.net/user/lisoov"))
        assertNotEquals(FurAffinityProfileStrategy, Strategy.forUrl("https://www.furaffinity.net/journal/0000000/"))
    }
}
