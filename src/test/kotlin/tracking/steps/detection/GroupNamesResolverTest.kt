package tracking.steps.detection

import org.junit.jupiter.api.Test
import kotlin.test.assertEquals

class GroupNamesResolverTest {
    @Test
    fun offersFrom() {
        val subject = GroupNamesResolver()

        assertEquals(listOf("HandpawsCommissions", "SockpawsCommissions"), subject.offersFrom("HandpawsCmsAndSockpawsCms"))

        assertEquals(listOf("Commissions", "Quotes"), subject.offersFrom("CommissionsAndQuotes"))
    }
}
