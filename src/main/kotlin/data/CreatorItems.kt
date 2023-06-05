package data

import database.Creator

data class CreatorItems<T>(
    val creator: Creator,
    val items: List<T>,
)
