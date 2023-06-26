package tracking.processing

import data.CreatorItems
import data.Resource
import data.ThreadSafe
import database.entities.Creator
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
    private val creator = ThreadSafe(disposableTransaction { Creator.new {} })
    private val subject = Processor()

    @TestFactory
    fun process() = getProcessTestData().map { caseData ->
        val creatorId = ""
        val creatorAliases = listOf<String>()
        val sourceUrl = ""
        val strategy = StandardStrategy

        DynamicTest.dynamicTest(caseData.name) {
            val input = CreatorItems(creator, creatorId, creatorAliases, listOf(
                ProcessedItem(creatorId, creatorAliases, sourceUrl, strategy, caseData.input),
            ))

            val result = subject.process(input)

            assertEquals(caseData.expectIssues, result.item.issues)
            assertEquals(caseData.offersStatuses, result.item.items)
        }
    }

    private fun getProcessTestData(): List<ProcessorTestCaseData> {
        return Resource.read("/tracking/processor_test_cases_data.txt")
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
