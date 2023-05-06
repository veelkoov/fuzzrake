package tracking.steps

import org.junit.jupiter.api.DynamicTest
import org.junit.jupiter.api.DynamicTest.dynamicTest
import org.junit.jupiter.api.TestFactory
import kotlin.test.Test
import kotlin.test.assertEquals

class PreprocessorTest {
    private val subject = Preprocessor()

    private fun cleanersTestsData() = mapOf<String, String>(
        "***open***" to "open",
        "!closed!" to "closed",
        " ❗&nbsp;" to " ! ",
    )

    @TestFactory
    fun testCleaners() = cleanersTestsData().map { (input, expected) ->
        dynamicTest("${Preprocessor::class.simpleName} for '${input}'") {
            val result = subject.preprocess(input)

            assertEquals(expected, result)
        }
    }
}
