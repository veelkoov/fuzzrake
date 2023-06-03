package tracking.matchers.regex

import org.junit.jupiter.api.Test

import kotlin.test.assertNotNull
import kotlin.test.assertEquals

class RegexJTest {
    @Test
    fun find() {
        val subject = RegexJ("(?<letters>[a-z]+)(?<digits>[0-9]+)|(?<digits>[0-9]+)(?<letters>[a-z]+)")

        val result1 = subject.find("---word1234---")

        assertNotNull(result1)
        assertEquals("word1234", result1.value)
        assertEquals(2, result1.groups.size)
        assertEquals("1234", result1.groups.toMap()["digits"])
        assertEquals("word", result1.groups.toMap()["letters"])

        val result2 = subject.find("---1234word---")

        assertNotNull(result2)
        assertEquals("1234word", result2.value)
        assertEquals(2, result2.groups.size)
        assertEquals("1234", result2.groups.toMap()["digits"])
        assertEquals("word", result2.groups.toMap()["letters"])
    }
}
