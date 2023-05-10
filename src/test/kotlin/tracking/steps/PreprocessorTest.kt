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
            "an CREATOR_NAME work",
        ),
        Triple(
            "An Intergalactic House of Pancake's work",
            listOf("Intergalactic House of Pancakes"),
            "an CREATOR_NAME work",
        ),
        Triple(
            "About Intergalactic Pancake's work",
            listOf("Intergalactic Pancake"),
            "about CREATOR_NAME's work",
        ),
        Triple( // Multiple aliases, 's form, case-insensitive, "creator" in aliases
            "asdf Studio's uiop Creator asdf Studios zxcv",
            listOf("StUdIoS", "cReatOR"),
            "asdf CREATOR_NAME uiop CREATOR_NAME asdf CREATOR_NAME zxcv",
        ),
    ).map { (input, aliases, expected) ->
        dynamicTest("Test input: '${input}'") {
            val result = subject.preprocess(input, aliases)

            assertEquals(expected, result)
        }
    }

    @TestFactory
    fun `False positives are being removed`() = mapOf(
        "even though you're closed for commissions" to "",
        "while mine commissions are open" to "",
        "if my quotes open" to "",
        "- art commissions are open" to "- are open",
        "after the commissions close" to "",
        "although comms are closed" to "",
        "as soon as we're open" to "",
        "next commissions opening" to "",
        "commissions: open January" to "",
        "The Creator is now opening for quotes a few weeks before commission slots open" to "",
        "when do you open for" to "",
        "when i'm taking" to "",
        "when will you start taking new commissions?" to "?",
    ).map { (input, expected) ->
        dynamicTest("Test input: '${input}'") {
            val result = subject.preprocess(input, listOf("The Creator"))

            assertEquals(expected, result)
        }
    }
}
