package tracking.contents

import database.entities.Creator
import tracking.website.Strategy

data class ProcessedItem(
    val creator: Creator,
    val creatorId: String,
    val sourceUrl: String,
    val strategy: Strategy,
    var contents: String,
)
