package tracking.patterns.placeholders

import org.junit.jupiter.api.DynamicTest
import org.junit.jupiter.api.TestFactory
import kotlin.test.Test
import kotlin.test.assertEquals

class ResolverTest {
    @Test
    fun `Resolve nested`() {
        val subject = Resolver(mapOf(
            "A" to "B",   // none
            "E" to "F",   // AA->D->E+E->F+F
            "AA" to "D",  // first
            "C" to "DD",  // second
            "D" to "E+E", // AA->D->E+E
        ))

        val result = subject.resolve("AA+C")

        assertEquals("F+F+DD", result)
    }

    @TestFactory
    fun `Proper delimiting`() = mapOf(
        "X AND X" to "X( and )X",
        "XAND X" to "XAND X",
        "X ANDX" to "X ANDX",
        "XANDX" to "XANDX",
        " AND X" to "( and )X",
        "AND X" to "AND X",
        "X AND " to "X( and )",
        "X AND" to "X AND",
        " AND" to " AND",
        "AND " to "AND ",
        " AND " to "( and )",
        "AND" to "AND",
        "X AAAA X" to "X (aaaa) X",
        "XAAAA X" to "XAAAA X",
        "X AAAAX" to "X AAAAX",
        "XAAAAX" to "XAAAAX",
        " AAAA X" to " (aaaa) X",
        "AAAA X" to "(aaaa) X",
        "X AAAA " to "X (aaaa) ",
        "X AAAA" to "X (aaaa)",
        " AAAA" to " (aaaa)",
        "AAAA " to "(aaaa) ",
        " AAAA " to " (aaaa) ",
        "AAAA" to "(aaaa)",
    ).map { (input, expected) ->
        DynamicTest.dynamicTest("Test input: '${input}'") {
            val subject = Resolver(mapOf(
                " AND " to "( and )",
                "AAAA"  to "(aaaa)",
            ))

            val result = subject.resolve(input)

            assertEquals(expected, result)
        }
    }
}
