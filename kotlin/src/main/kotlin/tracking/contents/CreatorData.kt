package tracking.contents

import data.ThreadSafe
import database.entities.Creator

data class CreatorData(
    val creatorId: String,
    val creatorAliases: List<String>,
    val creator: ThreadSafe<Creator>,
)
