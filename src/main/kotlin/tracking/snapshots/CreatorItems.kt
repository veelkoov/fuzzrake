package tracking.snapshots

import tracking.Creator

data class CreatorItems<T>(
    val creator: Creator,
    val items: List<T>,
)
