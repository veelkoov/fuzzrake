package time

import java.time.LocalDateTime
import java.time.ZoneId

object UTC {
    val timezone: ZoneId = ZoneId.of("UTC")

    object Now {
        fun dateTime(): LocalDateTime? {
            return LocalDateTime.now(timezone)
        }
    }
}
