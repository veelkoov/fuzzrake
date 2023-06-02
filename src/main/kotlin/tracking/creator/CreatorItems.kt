package tracking.creator

data class CreatorItems<T>(
    val creator: Creator,
    val items: List<T>,
)
