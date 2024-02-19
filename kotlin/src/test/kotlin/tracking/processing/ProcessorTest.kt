package tracking.processing

import data.Resource
import org.junit.jupiter.api.DynamicTest.dynamicTest
import org.junit.jupiter.api.TestFactory
import testUtils.ProcessorTestCaseData
import testUtils.getCreatorData
import testUtils.getUrl
import testUtils.toOfferStatus
import tracking.contents.CreatorItems
import tracking.contents.ProcessedItem
import tracking.website.StandardStrategy
import kotlin.test.BeforeTest
import kotlin.test.assertEquals

class ProcessorTest {
    private lateinit var subject: Processor

    @BeforeTest
    fun beforeTest() {
        subject = Processor()
    }

    @TestFactory
    fun process() = getProcessTestData().map { caseData ->
        dynamicTest(caseData.name) {
            val creatorData = getCreatorData()

            val input = CreatorItems(creatorData, listOf(
                ProcessedItem(creatorData, getUrl(), StandardStrategy, caseData.input),
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
                        it.toOfferStatus()
                    }.toSet()

                    ProcessorTestCaseData(name, input, offersStatuses, expectIssues)
                } catch (exception: RuntimeException) {
                    throw IllegalArgumentException("Wrong case data text: '$caseDataText'", exception)
                }
            }
    }
}
