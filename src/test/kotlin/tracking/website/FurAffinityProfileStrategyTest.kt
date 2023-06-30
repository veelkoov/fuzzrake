package tracking.website

import org.junit.jupiter.api.Assertions.*
import org.junit.jupiter.api.Test

class FurAffinityProfileStrategyTest {
    private val subject = FurAffinityProfileStrategy

    @Test
    fun `Simplified, working scenario`() {
        val input = "<html><head><body><div id=\"page-userpage\"><div class=\"userpage-profile\">Expected description</div></div></body></head></html>"
        val result = subject.filterContents(input)

        assertEquals("Expected description", result)
    }

    @Test
    fun `Failed matching`() {
        val input = "<html><head><body><div id=\"page-userpage\"><div class=\"wrong-class\">Description</div></div></body></head></html>"
        val result = subject.filterContents(input)

        assertEquals(input, result)
    }
}
