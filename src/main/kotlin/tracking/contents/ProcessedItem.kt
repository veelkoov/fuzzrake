package tracking.contents

import tracking.website.Strategy

data class ProcessedItem(
    val creatorData: CreatorData,
    val sourceUrl: String,
    val strategy: Strategy,
    var contents: String,
) {
    fun getCreatorId() = creatorData.creatorId
}
