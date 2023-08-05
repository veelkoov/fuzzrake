package tracking.website

import org.junit.jupiter.api.DynamicTest.dynamicTest
import org.junit.jupiter.api.TestFactory
import web.url.FreeUrl
import kotlin.test.assertEquals

class TrelloStrategyTest {
    private val subject = TrelloStrategy

    private val coerceUrlTestCases = mapOf(
        "https://trello.com/b/aBcDeFgHi/some-test-name"
                to "https://trello.com/1/boards/aBcDeFgHi?fields=name%2Cdesc&cards=visible&card_fields=name%2Cdesc",
        "https://trello.com/b/aBcDeFgHi"
                to "https://trello.com/1/boards/aBcDeFgHi?fields=name%2Cdesc&cards=visible&card_fields=name%2Cdesc",
        "https://trello.com/c/aBcDeFgHi/some-test-description"
                to "https://trello.com/1/cards/aBcDeFgHi?fields=name%2Cdesc",
        "https://trello.com/c/aBcDeFgHi"
                to "https://trello.com/1/cards/aBcDeFgHi?fields=name%2Cdesc",
    )

    @TestFactory
    fun coerceUrl() = coerceUrlTestCases.map { (input, expected) ->
        dynamicTest(input) {
            val result = subject.getUrlForTracking(FreeUrl(input)).getUrl()

            assertEquals(expected, result)
        }
    }
}
