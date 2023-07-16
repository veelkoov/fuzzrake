package e2e

import config.Configuration
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
import testUtils.toOfferStatus
import time.UTC
import tracking.Tracker
import tracking.TrackerOptions
import tracking.contents.CreatorItems
import tracking.contents.ProcessedItem
import tracking.contents.TrackedContentsProvider
import tracking.website.StandardStrategy
import web.url.Url
import kotlin.test.*

class TrackerTest {
    @TestFactory
    fun `Tracker tests`() = mapOf(

        "Everything gets reset when there are no tracked URLs" to TrackerTestCaseData(
            mapOf(),
            true,
            listOf(),
        ) {
            assertFalse(it.getVolatileData().csTrackerIssue)
            assertNull(it.getVolatileData().lastCsUpdateUtc)
            assertTrue(it.offersStatuses.empty())
        },

        "After a check, time of last update is not null" to TrackerTestCaseData(
            mapOf("http://localhost/" to "whatever, doesn't need to be successful"),
            false,
            listOf(),
        ) {
            assertNotNull(it.getVolatileData().lastCsUpdateUtc)
        },

        "A successful check resets the error state and returns proper results" to TrackerTestCaseData(
            mapOf("http://localhost/" to "commissions: open"),
            true,
            listOf("+Commissions"),
        ) {
            assertFalse(it.getVolatileData().csTrackerIssue)
        },

    ).map { (displayName, caseData) ->
        dynamicTest(displayName) {
            disposableDatabase { database ->
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
                    Configuration("/dev/null", "/dev/null"),
                    TrackerOptions(false),
                    provider,
                    database,
                )

                subject.run()

                val result = Creator.findById(id)

                assertNotNull(result)
                caseData.asserts(result)

                assertEquals(
                    caseData.expectedOffersStatuses.map { it.toOfferStatus() }.toSet(),
                    result.offersStatuses.map { it.toOfferStatus() }.toSet(),
                )

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
