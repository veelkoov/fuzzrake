package testUtils

import database.entities.Creator

data class TrackerTestCaseData(
    val urlToContents: Map<String, String>,
    val hadIssuesPreviously: Boolean,
    val expectedOffersStatuses: List<String>,
    val asserts: (result: Creator) -> Unit,
)
