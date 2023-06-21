package data

import database.entities.Creator

data class CreatorItems<T>(
    val creator: Creator,
    val creatorId: String,
    val items: List<T>,
)
