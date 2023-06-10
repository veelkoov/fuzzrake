package tracking.steps

import data.CreatorItems
import database.Creator
import org.junit.jupiter.api.DynamicTest
import org.junit.jupiter.api.TestFactory
import testUtils.ProcessorTestCaseData
import testUtils.disposableTransaction
import tracking.contents.ProcessedItem
import tracking.statuses.OfferStatus
import tracking.statuses.Status
import tracking.website.StandardStrategy
import kotlin.test.assertEquals

class ProcessorTest {
    private val creator = disposableTransaction { Creator.new {} }

    @TestFactory
    fun process(): List<DynamicTest> {
        val subject = Processor()

        return getTestCases().map { caseData ->
            DynamicTest.dynamicTest(caseData.name) {
                val processedItem = ProcessedItem("", caseData.input, creator, StandardStrategy)
                val input = CreatorItems(creator, listOf(processedItem))

                val result = subject.process(input)

                assertEquals(caseData.expectIssues, result.item.issues)
                assertEquals(caseData.offersStatuses, result.item.items)
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
