package tracking.steps.detection

import org.junit.jupiter.api.Assertions.assertEquals
import org.junit.jupiter.api.Test
import org.junit.jupiter.api.assertThrows
import tracking.statuses.OfferStatusException
import tracking.statuses.Status

class GroupNamesAnalyserTest {
    @Test
    fun `Exception thrown in corner cases`() {
        val subject = GroupNamesAnalyser()

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

    @Test
    fun detectIn() {
        val subject = GroupNamesAnalyser()

        val result = subject.detectIn(listOf("StatusClosed" to "asdf", "CommissionsAndQuotes" to "qwer"))

        assertEquals(2, result.size)
        assertEquals(Status.CLOSED, result[0].status)
        assertEquals("Commissions", result[0].offer)
        assertEquals(Status.CLOSED, result[1].status)
        assertEquals("Quotes", result[1].offer)
    }
}
