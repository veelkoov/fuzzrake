package testUtils

import database.entities.Creator

data class TrackerTestCaseData(
    val urlToContents: Map<String, String>,
    val hadIssuesPreviously: Boolean = false,
    val expectedIssues: Boolean? = null,
    val expectedOffersStatuses: List<String>? = null,
    val asserts: (result: Creator) -> Unit = {},
)
