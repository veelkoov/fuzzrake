package data

import database.entities.Creator

data class CreatorItems<T>(
    val creator: ThreadSafe<Creator>,
    val creatorId: String,
    val creatorAliases: List<String>,
    val items: List<T>,
)
