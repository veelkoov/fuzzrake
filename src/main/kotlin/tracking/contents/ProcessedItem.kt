package tracking.contents

import database.Creator
import tracking.website.Strategy

data class ProcessedItem(
    val sourceUrl: String,
    var contents: String,
    val creator: Creator,
    val strategy: Strategy,
)
