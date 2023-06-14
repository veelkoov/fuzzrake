package tracking.website

import org.junit.jupiter.api.Test

import org.junit.jupiter.api.Assertions.*

class TrelloStrategyTest {
    val subject = TrelloStrategy

    @Test
    fun coerceUrl() {
        assertEquals(
            "https://trello.com/1/boards/aBcDeFgHi?fields=name%2Cdesc&cards=visible&card_fields=name%2Cdesc",
            TrelloStrategy.coerceUrl("https://trello.com/b/aBcDeFgHi/some-test-name"),
        )

        assertEquals(
            "https://trello.com/1/boards/aBcDeFgHi?fields=name%2Cdesc&cards=visible&card_fields=name%2Cdesc",
            TrelloStrategy.coerceUrl("https://trello.com/b/aBcDeFgHi"),
        )

        assertEquals(
            "https://trello.com/1/cards/aBcDeFgHi?fields=name%2Cdesc",
            TrelloStrategy.coerceUrl("https://trello.com/c/aBcDeFgHi/some-test-description"),
        )

        assertEquals(
            "https://trello.com/1/cards/aBcDeFgHi?fields=name%2Cdesc",
            TrelloStrategy.coerceUrl("https://trello.com/c/aBcDeFgHi"),
        )
    }
}
