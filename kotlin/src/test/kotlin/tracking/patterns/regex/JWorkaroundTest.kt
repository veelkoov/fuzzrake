package tracking.patterns.regex

import kotlin.test.Test
import kotlin.test.assertEquals
import kotlin.test.assertNotNull

class JWorkaroundTest {
    @Test
    fun find() {
        val letters = "(?<letters>[a-z]+)"
        val digits = "(?<digits>[0-9]+)"
        val regex = RegexJ("$letters$digits|$digits$letters")

        val result1 = regex.find("---word1234---")

        assertNotNull(result1)
        assertEquals("word1234", result1.value)
        assertEquals(2, result1.groups.size)
        assertEquals("1234", result1.groups.toMap()["digits"])
        assertEquals("word", result1.groups.toMap()["letters"])

        val result2 = regex.find("---1234word---")

        assertNotNull(result2)
        assertEquals("1234word", result2.value)
        assertEquals(2, result2.groups.size)
        assertEquals("1234", result2.groups.toMap()["digits"])
        assertEquals("word", result2.groups.toMap()["letters"])
    }
}
