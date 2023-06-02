package tracking.contents

import tracking.creator.Creator
import tracking.website.Strategy

data class ProcessedItem(
    val sourceUrl: String,
    var contents: String,
    val creator: Creator,
    val strategy: Strategy,
)
