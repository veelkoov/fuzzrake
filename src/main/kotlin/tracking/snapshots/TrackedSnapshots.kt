package tracking.snapshots

import tracking.Creator

data class TrackedSnapshots(
    val creator: Creator,
    var snapshots: List<Snapshot>,
)
