package data

import database.entities.Creator

data class CreatorItem<T>(
    val creator: Creator,
    val creatorId: String,
    val item: T,
)
