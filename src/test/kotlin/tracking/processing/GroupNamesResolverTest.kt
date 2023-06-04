package tracking.processing

import org.junit.jupiter.api.Test
import kotlin.test.assertEquals

class GroupNamesResolverTest {
    @Test
    fun offersFrom() {
        val subject = GroupNamesResolver()

        assertEquals(listOf("HANDPAWS COMMISSIONS", "SOCKPAWS COMMISSIONS"), subject.offersFrom("HandpawsCmsAndSockpawsCms"))

        assertEquals(listOf("COMMISSIONS", "QUOTES"), subject.offersFrom("CommissionsAndQuotes"))
    }
}
