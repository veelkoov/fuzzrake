package tracking.contents

import tracking.website.Strategy

data class ProcessedItem(
    val creatorId: String,
    val creatorAliases: List<String>,
    val sourceUrl: String,
    val strategy: Strategy,
    var contents: String,
)
