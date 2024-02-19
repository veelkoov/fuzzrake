package tracking.patterns.regex

import kotlin.test.Test
import kotlin.test.assertEquals
import kotlin.test.assertFailsWith

class RegexJTest {
    @Test
    fun `Empty regex patterns are never intentional and should not be allowed`() {
        assertFailsWith<IllegalArgumentException> {
            RegexJ("")
        }

        // In case the world is ending, and you need one (...WHY?!)
        val uselessRegexMatchingZeroLengthStrings = RegexJ("((?=A)B)?")

        assertEquals("ABA", uselessRegexMatchingZeroLengthStrings.replace("B", "A"))
    }
}
