package time

import java.time.LocalDateTime
import java.time.ZoneId
import java.time.ZoneOffset

object UTC {
    val zoneId: ZoneId = ZoneId.of("UTC")
    val zoneOffset: ZoneOffset = ZoneOffset.UTC

    object Now {
        fun dateTime(): LocalDateTime = LocalDateTime.now(zoneId)
        fun epochSec(): Long = dateTime().toEpochSecond(zoneOffset)
    }
}
