package tracking.detection

import org.junit.jupiter.api.Test
import org.junit.jupiter.api.Assertions.*
import org.junit.jupiter.api.assertThrows
import tracking.statuses.OfferStatusException

class MatchedGroupsTest {
    @Test
    fun `Exception thrown in corner cases`() {
        val subject = MatchedGroups()

        val ex1 = assertThrows<OfferStatusException> {
            subject.detectIn(listOf("StatusOpen" to "asdf"))
        }
        assertEquals("Did not detect offer", ex1.requireMessage())

        val ex2 = assertThrows<OfferStatusException> {
            subject.detectIn(listOf("Commissions" to "asdf"))
        }
        assertEquals("Did not detect status", ex2.requireMessage())

        val ex3 = assertThrows<OfferStatusException> {
            subject.detectIn(listOf("StatusOpen" to "asdf", "StatusOpen" to "qwer", "Quotes" to "asdf"))
        }
        assertEquals("Detected multiple statuses", ex3.requireMessage())

        val ex4 = assertThrows<OfferStatusException> {
            subject.detectIn(listOf("Commissions" to "asdf", "Commissions" to "qwer", "StatusOpen" to "asdf"))
        }
        assertEquals("Detected multiple offers", ex4.requireMessage())
    }
}
