package tracking.processing

import tracking.statuses.Offer

class GroupNamesResolver {
    fun offersFrom(groupName: String): List<Offer> {
        return groupName
            .split("And")
            .map(::prettify)
    }

    companion object {
        private val prettyNames = mapOf(
            "Cms"                 to "Commissions",
            "HandpawsCms"         to "Handpaws commissions",
            "SockpawsCms"         to "Sockpaws commissions",
            "FullsuitCommissions" to "Fullsuit commissions",
            "PartialCommissions"  to "Partial commissions",
            "HeadCommissions"     to "Head commissions",
            "ArtisticLiberty"     to "Artistic liberty",
        )

        private fun prettify(input: String) = prettyNames.getOrDefault(input, input)
    }
}
