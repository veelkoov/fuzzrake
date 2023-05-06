package tracking.steps

import org.junit.jupiter.api.DynamicTest.dynamicTest
import org.junit.jupiter.api.TestFactory
import kotlin.test.Test
import kotlin.test.assertEquals

class PreprocessorTest {
    private val subject = Preprocessor()

    @TestFactory
    fun `Cleaner regexes are working`() = mapOf(
        "***open***" to "open",
        "!closed!" to "closed",
        " ❗&nbsp;" to " ! ",
    ).map { (input, expected) ->
        dynamicTest("Test input: '${input}'") {
            val result = subject.preprocess(input, listOf())

            assertEquals(expected, result)
        }
    }

    @Test
    fun `Input gets converted to lowercase`() {
        val result = subject.preprocess("AaBbCcDdEeFf", listOf())

        assertEquals("aabbccddeeff", result)
    }

    @TestFactory
    fun `Creator aliases are getting replaced with the name placeholder`() = listOf(
        Triple(
            "An Intergalactic House of Pancakes work",
            listOf("Intergalactic House of Pancakes"),
            "an STUDIO_NAME work",
        ),
        Triple(
            "An Intergalactic House of Pancake's work",
            listOf("Intergalactic House of Pancakes"),
            "an STUDIO_NAME work",
        ),
        Triple(
            "About Intergalactic Pancake's work",
            listOf("Intergalactic Pancake"),
            "about STUDIO_NAME's work",
        ),
        Triple( // Multiple aliases, 's form, case-insensitive
            "asdf Studio's uiop Creator asdf Studios zxcv",
            listOf("StUdIoS", "cReatOR"),
            "asdf STUDIO_NAME uiop STUDIO_NAME asdf STUDIO_NAME zxcv",
        ),
    ).map { (input, aliases, expected) ->
        dynamicTest("Test input: '${input}'") {
            val result = subject.preprocess(input, aliases)

            assertEquals(expected, result)
        }
    }
}
