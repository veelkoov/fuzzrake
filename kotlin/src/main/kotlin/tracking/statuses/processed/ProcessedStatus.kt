package tracking.statuses.processed

import tracking.statuses.Status

enum class ProcessedStatus {
    OPEN,
    CLOSED,
    CONFLICT;

    fun asStatus(): Status {
        return when (this) {
            OPEN -> Status.OPEN
            CLOSED -> Status.CLOSED
            else -> throw IllegalStateException("$this cannot be converted to ${Status::class}")
        }
    }

    companion object {
        fun from(status: Status): ProcessedStatus {
            return when (status) {
                Status.CLOSED -> CLOSED
                Status.OPEN -> OPEN
            }
        }
    }
}
