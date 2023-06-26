package tracking.processing

import org.junit.jupiter.api.Test
import tracking.contents.ProcessedItem
import data.CreatorItems
import data.ThreadSafe
import database.entities.Creator
import testUtils.disposableTransaction
import tracking.statuses.OfferStatus
import tracking.statuses.Status
import tracking.website.StandardStrategy
import kotlin.test.assertContains
import kotlin.test.assertEquals
import kotlin.test.assertTrue
import kotlin.test.assertFalse

class DetectorTest {
    private val creator = ThreadSafe(disposableTransaction { Creator.new {} })

    private fun getTestInput(vararg contents: String): CreatorItems<ProcessedItem> {
        val sourceUrl = ""
        val creatorId = ""
        val aliases = listOf<String>()
        val strategy = StandardStrategy

        return CreatorItems(creator, creatorId,
            aliases, contents.map { ProcessedItem(creatorId, aliases, sourceUrl, strategy, it) })
    }

    @Test
    fun `Single page, one status`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions are open"))

        assertFalse(result.issues)
        assertEquals(1, result.items.size)
        assertContains(result.items, OfferStatus("Commissions", Status.OPEN))
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
        assertContains(result.items, OfferStatus("Commissions", Status.OPEN))
        assertContains(result.items, OfferStatus("Quotes", Status.OPEN))
    }

    @Test
    fun `Single page, conflicting`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions are open, however commissions are closed"))

        assertTrue(result.issues)
        assertEquals(0, result.items.size)
    }

    @Test
    fun `Single page, conflicting 3 times`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions are open, however commissions are closed, but we are unsure if commissions are closed"))

        assertTrue(result.issues)
        assertEquals(0, result.items.size)
    }

    @Test
    fun `Single page, joined status partially conflicting`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions and quotes are open, however commissions are closed"))

        assertTrue(result.issues)
        assertEquals(1, result.items.size)
        assertContains(result.items, OfferStatus("Quotes", Status.OPEN))
    }

    @Test
    fun `Single page, two statuses partially conflicting`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions are open and quotes are open, however commissions are closed"))

        assertTrue(result.issues)
        assertEquals(1, result.items.size)
        assertContains(result.items, OfferStatus("Quotes", Status.OPEN))
    }

    @Test
    fun `Single page, duplicated`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions are open and commissions are open too"))

        assertTrue(result.issues)
        assertEquals(1, result.items.size)
        assertContains(result.items, OfferStatus("Commissions", Status.OPEN))
    }

    @Test
    fun `Two pages, both OK, same offer and status`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions are open", "we are open for commissions"))

        assertFalse(result.issues)
        assertEquals(1, result.items.size)
        assertContains(result.items, OfferStatus("Commissions", Status.OPEN))
    }

    @Test
    fun `Two pages, both OK, different offer and status`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions are open", "quotes are closed"))

        assertFalse(result.issues)
        assertEquals(2, result.items.size)
        assertContains(result.items, OfferStatus("Commissions", Status.OPEN))
        assertContains(result.items, OfferStatus("Quotes", Status.CLOSED))
    }

    @Test
    fun `Two pages, one OK, one empty`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions are open", "quotes are unknown"))

        assertTrue(result.issues)
        assertEquals(1, result.items.size)
        assertContains(result.items, OfferStatus("Commissions", Status.OPEN))
    }

    @Test
    fun `Two pages, simple conflict`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions are closed", "we are open for commissions"))

        assertTrue(result.issues)
        assertEquals(0, result.items.size)
    }

    @Test
    fun `Two pages, one with 2 statuses, one single status, conflicting`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions and quotes are closed", "we are open for commissions"))

        assertTrue(result.issues)
        assertEquals(1, result.items.size)
        assertContains(result.items, OfferStatus("Quotes", Status.CLOSED))
    }

    @Test
    fun `Two pages, one with conflict, one single status for same offer`() {
        val subject = Detector()
        val result = subject.detectIn(getTestInput("commissions are closed, however commissions are open", "we are open for commissions"))

        assertTrue(result.issues)
        assertEquals(0, result.items.size)
    }
}
