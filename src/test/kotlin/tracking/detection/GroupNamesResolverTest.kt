package tracking.detection

import org.junit.jupiter.api.Test

import org.junit.jupiter.api.Assertions.*

class GroupNamesResolverTest {
    @Test
    fun offersFrom() {
        val subject = GroupNamesResolver()

        kotlin.test.assertEquals(listOf("HandpawsCommissions", "SockpawsCommissions"),subject.offersFrom("HandpawsCmsAndSockpawsCms"))

        kotlin.test.assertEquals(listOf("Commissions", "Quotes"),subject.offersFrom("CommissionsAndQuotes"))
    }
}