package e2e

import database.entities.Creator
import database.entities.CreatorOfferStatus
import database.entities.CreatorUrl
import database.entities.CreatorVolatileData
import database.helpers.getVolatileData
import database.helpers.toOfferStatus
import io.mockk.*
import org.junit.jupiter.api.DynamicTest.dynamicTest
import org.junit.jupiter.api.TestFactory
import testUtils.TrackerTestCaseData
import testUtils.disposableDatabase
import testUtils.getNullConfiguration
import testUtils.toOfferStatus
import time.UTC
import tracking.Tracker
import tracking.TrackerOptions
import tracking.contents.CreatorItems
import tracking.contents.ProcessedItem
import tracking.contents.TrackedContentsProvider
import tracking.website.StandardStrategy
import web.url.Url
import kotlin.test.assertEquals
import kotlin.test.assertNotNull
import kotlin.test.assertNull

class TrackerTest {
    private val failingUrl = "http://localhost/failing" to "nothing to detect here"
    private val commissionsOpenUrl = "http://localhost/success1" to "commissions: open"
    private val tradesClosedUrl = "http://localhost/success2" to "trades: closed"
    private val commissionsAndQuotesOpenUrl = "http://localhost/success3" to "commissions: open ; quotes: open"
    private val commissionsClosedUrl = "http://localhost/success4" to "commissions: closed"

    @TestFactory
    fun `Tracker tests`() = mapOf(

        "Everything gets reset when there are no tracked URLs" to TrackerTestCaseData(
            mapOf(),
            hadIssuesPreviously = true,

            expectedIssues = false,
            expectedOffersStatuses = listOf(),
        ) {
            assertNull(it.getVolatileData().lastCsUpdateUtc)
        },

        "After a check, time of last update is not null" to TrackerTestCaseData(
            mapOf(failingUrl),
        ) {
            assertNotNull(it.getVolatileData().lastCsUpdateUtc)
        },

        "A successful check resets the error state" to TrackerTestCaseData(
            mapOf(commissionsOpenUrl),
            hadIssuesPreviously = true,

            expectedIssues = false,
        ),

        "A successful check of a single URL returns proper results" to TrackerTestCaseData(
            mapOf(commissionsOpenUrl),

            expectedOffersStatuses = listOf("+Commissions"),
        ),

        "Empty result from single URL sets error state" to TrackerTestCaseData(
            mapOf(failingUrl),
            hadIssuesPreviously = false,

            expectedIssues = true,
        ),

        "One failed URL of multiple sets error state" to TrackerTestCaseData(
            mapOf(failingUrl, commissionsOpenUrl),
            hadIssuesPreviously = false,

            expectedIssues = true,
        ),

        "Results from two urls are properly gathered" to TrackerTestCaseData(
            mapOf(tradesClosedUrl, commissionsAndQuotesOpenUrl),

            expectedIssues = false,
            expectedOffersStatuses = listOf("-Trades", "+Commissions", "+Quotes")
        ),

        "Contradicting offer statuses remove the offer and set error state" to TrackerTestCaseData(
            mapOf(commissionsClosedUrl, commissionsAndQuotesOpenUrl),
            hadIssuesPreviously = false,

            expectedIssues = true,
            expectedOffersStatuses = listOf("+Quotes")
        ),

        "Duplicated offer statuses in different URL are OK" to TrackerTestCaseData(
            mapOf(commissionsOpenUrl, commissionsAndQuotesOpenUrl),
            hadIssuesPreviously = false,

            expectedIssues = false,
            expectedOffersStatuses = listOf("+Quotes", "+Commissions")
        ),

    ).map { (displayName, caseData) ->
        dynamicTest(displayName) {
            disposableDatabase { database, _ ->
                val creator = Creator.new {
                    creatorId = "CREATOR"
                }

                CreatorVolatileData.new {
                    this.creator = creator
                    lastCsUpdateUtc = UTC.Now.dateTime()
                    csTrackerIssue = caseData.hadIssuesPreviously
                }

                CreatorOfferStatus.new {
                    this.creator = creator
                    this.isOpen = true
                    this.offer = "Pancakes"
                }

                caseData.urlToContents.forEach { (url, _) ->
                    CreatorUrl.new {
                        this.creator = creator
                        type = "URL_COMMISSIONS" // TODO: Enum
                        this.url = url
                    }
                }

                val id = creator.id

                val provider = getProviderMock(caseData)

                val subject = Tracker(
                    getNullConfiguration(),
                    TrackerOptions(false),
                    provider,
                    database,
                )

                subject.run()

                val result = Creator.findById(id)

                assertNotNull(result)
                caseData.asserts(result)

                caseData.expectedIssues?.let { expected ->
                    assertEquals(expected, result.getVolatileData().csTrackerIssue,
                        "Tacker issues status is wrong")
                }

                caseData.expectedOffersStatuses?.let { expected ->
                    assertEquals(
                        expected.map { it.toOfferStatus() }.toSet(),
                        result.offersStatuses.map { it.toOfferStatus() }.toSet(),
                        "Detected offers statuses are wrong",
                    )
                }

                verify(exactly = 1) { provider.createProcessedItems(any()) }
                confirmVerified(provider)
            }
        }
    }

    private fun getProviderMock(caseData: TrackerTestCaseData): TrackedContentsProvider {
        val result = mockk<TrackedContentsProvider>()
        val slot = slot<CreatorItems<Url>>()

        every {
            result.createProcessedItems(capture(slot))
        } answers {
            getProcessedItemCreatorItemsAnswer(slot, caseData)
        }

        return result
    }

    private fun getProcessedItemCreatorItemsAnswer(
        slot: CapturingSlot<CreatorItems<Url>>,
        caseData: TrackerTestCaseData,
    ): CreatorItems<ProcessedItem> {
        return CreatorItems(
            slot.captured.creatorData,
            slot.captured.items.map {
                ProcessedItem(
                    slot.captured.creatorData,
                    it,
                    StandardStrategy,
                    caseData.urlToContents.getOrElse(it.getUrl()) {
                        throw IllegalArgumentException("${it.getUrl()} wasn't configured")
                    },
                )
            },
        )
    }
}
