package tracking.steps

import org.junit.jupiter.api.Assertions.*
import org.junit.jupiter.api.Test
import tracking.contents.ProcessedItem
import tracking.creator.Creator
import tracking.creator.CreatorItems
import tracking.statuses.OfferStatus
import tracking.statuses.Status
import tracking.website.StandardStrategy
import kotlin.test.assertEquals
import kotlin.test.assertTrue
import kotlin.test.assertFalse

class DetectorTest {
    private fun getTestInput(vararg contents: String): CreatorItems<ProcessedItem> {
        val creator = Creator(listOf())
        val sourceUrl = ""
        val strategy = StandardStrategy

        return CreatorItems(creator, contents.map { ProcessedItem(sourceUrl, it, creator, strategy) })
    }

    @Test
    fun `Single page, one status`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions are open"))

        assertFalse(result.issues)
        assertEquals(1, result.items.size)
        assertEquals(OfferStatus("Commissions", Status.OPEN), result.items[0])
    }

    @Test
    fun `Single page, no status`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions are unknown"))

        assertTrue(result.issues)
        assertEquals(0, result.items.size)
    }

    @Test
    fun `Single page, one joined status`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions and quotes are open"))

        assertFalse(result.issues)
        assertEquals(2, result.items.size)
        assertEquals(OfferStatus("Commissions", Status.OPEN), result.items[0])
        assertEquals(OfferStatus("Quotes", Status.OPEN), result.items[1])
    }

    @Test
    fun `Single page, conflicting`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions are open, however commissions are closed"))

        assertTrue(result.issues)
        assertEquals(0, result.items.size)
    }

    @Test
    fun `Single page, joined status partially conflicting`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions and quotes are open, however commissions are closed"))

        assertTrue(result.issues)
        assertEquals(1, result.items.size)
        assertEquals(OfferStatus("Quotes", Status.OPEN), result.items[0])
    }

    @Test
    fun `Single page, two statuses partially conflicting`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions are open and quotes are open, however commissions are closed"))

        assertTrue(result.issues)
        assertEquals(1, result.items.size)
        assertEquals(OfferStatus("Quotes", Status.OPEN), result.items[0])
    }

    @Test
    fun `Single page, duplicated`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions are open and commissions are open too"))

        assertTrue(result.issues)
        assertEquals(1, result.items.size)
        assertEquals(OfferStatus("Commissions", Status.OPEN), result.items[0])
    }

    @Test
    fun `Two pages, both OK`() {
        TODO()
    }

    @Test
    fun `Two pages, one OK, one empty`() {
        TODO()
    }

    @Test
    fun `Two pages, one with 2 statuses, one single status, conflicting`() {
        TODO()
    }

    @Test
    fun `Two pages, one with conflict, one single status for same offer`() {
        TODO()
    }
}
