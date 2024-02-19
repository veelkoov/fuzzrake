package tracking.processing

import testUtils.getCreatorData
import testUtils.getUrl
import tracking.contents.CreatorItems
import tracking.contents.ProcessedItem
import tracking.statuses.OfferStatus
import tracking.statuses.Status
import tracking.website.StandardStrategy
import kotlin.test.*

class DetectorTest {
    private lateinit var subject: Detector

    @BeforeTest
    fun beforeTest() {
        subject = Detector()
    }

    private fun getTestInput(vararg contents: String): CreatorItems<ProcessedItem> {
        val creatorData = getCreatorData()

        return CreatorItems(creatorData, contents.map { ProcessedItem(creatorData, getUrl(), StandardStrategy, it) })
    }

    @Test
    fun `Single page, one status`() {
        val result = subject.detectIn(getTestInput("commissions are open"))

        assertFalse(result.issues)
        assertEquals(1, result.items.size)
        assertContains(result.items, OfferStatus("Commissions", Status.OPEN))
    }

    @Test
    fun `Single page, no status`() {
        val result = subject.detectIn(getTestInput("commissions are unknown"))

        assertTrue(result.issues)
        assertEquals(0, result.items.size)
    }

    @Test
    fun `Single page, one joined status`() {
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
        val result = subject.detectIn(getTestInput("commissions are open, however commissions are closed, but we are unsure if commissions are closed"))

        assertTrue(result.issues)
        assertEquals(0, result.items.size)
    }

    @Test
    fun `Single page, joined status partially conflicting`() {
        val result = subject.detectIn(getTestInput("commissions and quotes are open, however commissions are closed"))

        assertTrue(result.issues)
        assertEquals(1, result.items.size)
        assertContains(result.items, OfferStatus("Quotes", Status.OPEN))
    }

    @Test
    fun `Single page, two statuses partially conflicting`() {
        val result = subject.detectIn(getTestInput("commissions are open and quotes are open, however commissions are closed"))

        assertTrue(result.issues)
        assertEquals(1, result.items.size)
        assertContains(result.items, OfferStatus("Quotes", Status.OPEN))
    }

    @Test
    fun `Single page, duplicated`() {
        val result = subject.detectIn(getTestInput("commissions are open and commissions are open too"))

        assertTrue(result.issues)
        assertEquals(1, result.items.size)
        assertContains(result.items, OfferStatus("Commissions", Status.OPEN))
    }

    @Test
    fun `Two pages, both OK, same offer and status`() {
        val result = subject.detectIn(getTestInput("commissions are open", "we are open for commissions"))

        assertFalse(result.issues)
        assertEquals(1, result.items.size)
        assertContains(result.items, OfferStatus("Commissions", Status.OPEN))
    }

    @Test
    fun `Two pages, both OK, different offer and status`() {
        val result = subject.detectIn(getTestInput("commissions are open", "quotes are closed"))

        assertFalse(result.issues)
        assertEquals(2, result.items.size)
        assertContains(result.items, OfferStatus("Commissions", Status.OPEN))
        assertContains(result.items, OfferStatus("Quotes", Status.CLOSED))
    }

    @Test
    fun `Two pages, one OK, one empty`() {
        val result = subject.detectIn(getTestInput("commissions are open", "quotes are unknown"))

        assertTrue(result.issues)
        assertEquals(1, result.items.size)
        assertContains(result.items, OfferStatus("Commissions", Status.OPEN))
    }

    @Test
    fun `Two pages, simple conflict`() {
        val result = subject.detectIn(getTestInput("commissions are closed", "we are open for commissions"))

        assertTrue(result.issues)
        assertEquals(0, result.items.size)
    }

    @Test
    fun `Two pages, one with 2 statuses, one single status, conflicting`() {
        val result = subject.detectIn(getTestInput("commissions and quotes are closed", "we are open for commissions"))

        assertTrue(result.issues)
        assertEquals(1, result.items.size)
        assertContains(result.items, OfferStatus("Quotes", Status.CLOSED))
    }

    @Test
    fun `Two pages, one with conflict, one single status for same offer`() {
        val result = subject.detectIn(getTestInput("commissions are closed, however commissions are open", "we are open for commissions"))

        assertTrue(result.issues)
        assertEquals(0, result.items.size)
    }
}
