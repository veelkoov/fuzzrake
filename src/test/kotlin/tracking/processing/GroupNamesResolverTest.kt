package tracking.processing

import org.junit.jupiter.api.Test
import kotlin.test.assertEquals

class GroupNamesResolverTest {
    @Test
    fun offersFrom() {
        val subject = GroupNamesResolver()

        assertEquals(
            listOf("Handpaws commissions", "Sockpaws commissions"),
            subject.offersFrom("HandpawsCmsAndSockpawsCms"),
        )

        assertEquals(
            listOf("Commissions", "Quotes"),
            subject.offersFrom("CommissionsAndQuotes"),
        )
    }
}
