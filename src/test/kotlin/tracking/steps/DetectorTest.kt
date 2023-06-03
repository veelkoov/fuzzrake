package tracking.steps

import org.junit.jupiter.api.Assertions.*
import org.junit.jupiter.api.Test
import tracking.contents.ProcessedItem
import tracking.creator.Creator
import tracking.creator.CreatorItems
import tracking.statuses.OfferStatus
import tracking.statuses.Status
import tracking.website.StandardStrategy
import tracking.website.Strategy
import kotlin.test.assertEquals

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
        assertEquals(OfferStatus("commissions", Status.OPEN), result.items[0])
    }

    fun `Single page, no status`() {
        TODO()
    }

    fun `Single page, one joined status`() {
        TODO()
    }

    fun `Single page, joined status partially conflicting`() {
        TODO()
    }

    fun `Single page, conflicting`() {
        TODO()
    }

    fun `Single page, duplicated`() {
        TODO()
    }

    fun `Two pages, both OK`() {
        TODO()
    }

    fun `Two pages, one OK, one empty`() {
        TODO()
    }

    fun `Two pages, one with 2 statuses, one single status, conflicting`() {
        TODO()
    }

    fun `Two pages, one with conflict, one single status for same offer`() {
        TODO()
    }
}
