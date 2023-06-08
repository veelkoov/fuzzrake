package data

import database.Creator

data class CreatorItem<T>(
    val creator: Creator,
    val item: T,
)
