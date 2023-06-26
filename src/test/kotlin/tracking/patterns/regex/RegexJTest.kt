package tracking.patterns.regex

import org.junit.jupiter.api.Assertions.*
import org.junit.jupiter.api.Test

class RegexJTest {
    @Test
    fun `Empty regex patterns are never intentional and should not be allowed`() {
        assertThrows(IllegalArgumentException::class.java) {
            RegexJ("")
        }

        // In case the world is ending, and you need one (...WHY?!)
        val uselessRegexMatchingZeroLengthStrings = RegexJ("((?=A)B)?")

        assertEquals("ABA", uselessRegexMatchingZeroLengthStrings.replace("B", "A"))
    }
}
