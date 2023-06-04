package tracking.steps

import org.junit.jupiter.api.DynamicTest
import org.junit.jupiter.api.TestFactory
import testUtils.ProcessorTestCaseData
import tracking.contents.ProcessedItem
import tracking.creator.Creator
import tracking.creator.CreatorItems
import tracking.statuses.OfferStatus
import tracking.statuses.Status
import tracking.website.StandardStrategy
import kotlin.test.assertEquals

class ProcessorTest {
    @TestFactory
    fun process(): List<DynamicTest> {
        val subject = Processor()

        return getTestCases().map { caseData ->
            DynamicTest.dynamicTest(caseData.name) {
                val creator = Creator(listOf())
                val processedItem = ProcessedItem("", caseData.input, creator, StandardStrategy)
                val input = CreatorItems(creator, listOf(processedItem))

                val result = subject.process(input)

                assertEquals(caseData.expectIssues, result.issues)
                assertEquals(caseData.offersStatuses, result.items)
            }
        }
    }

    private fun getTestCases(): List<ProcessorTestCaseData> {
        return javaClass
            .getResource("/tracking/processor_test_cases_data.txt")!!.readText()
            .split("\n================================================================\n")
            .map { caseDataText ->
                try {
                    val (input, metadata) = caseDataText.split("\n--------------------------------\n", limit = 2)
                    val metadataLines = metadata.trimEnd().split("\n").toMutableList()

                    val name = metadataLines.removeFirst()
                    val expectIssues = name.endsWith(" (issues expected)")

                    val offersStatuses = metadataLines.map {
                        when (it[0]) {
                            '+' -> OfferStatus(it.drop(1), Status.OPEN)
                            '-' -> OfferStatus(it.drop(1), Status.CLOSED)
                            else -> throw IllegalArgumentException()
                        }
                    }.toSet()

                    ProcessorTestCaseData(name, input, offersStatuses, expectIssues)
                } catch (exception: RuntimeException) {
                    throw IllegalArgumentException("Wrong case data text: '$caseDataText'", exception)
                }
            }
    }
}
