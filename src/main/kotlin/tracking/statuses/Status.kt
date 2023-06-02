package tracking.statuses

enum class Status {
    OPEN,
    CLOSED;

    companion object {
        private const val groupNamePrefix = "Status"

        fun isStatusGroup(groupName: String) = groupName.startsWith(Status.groupNamePrefix)

        fun fromGroupName(name: String): Status {
            return when (name) {
                "${groupNamePrefix}Open" -> OPEN
                "${groupNamePrefix}Closed" -> CLOSED
                else -> throw IllegalArgumentException("Cannot match a status to the group name: '$name'")
            }
        }
    }
}
