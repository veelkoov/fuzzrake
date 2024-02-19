package tracking.contents

import tracking.website.Strategy
import web.url.Url

data class ProcessedItem(
    val creatorData: CreatorData,
    val url: Url,
    val strategy: Strategy,
    var contents: String,
) {
    fun getCreatorId() = creatorData.creatorId
}
