package tracking.processing

import tracking.statuses.Offer

class GroupNamesResolver {
    fun offersFrom(groupName: String): List<Offer> {
        return groupName
            .split("And")
            .map(::prettify)
    }

    companion object {
        private val prettyNames = mapOf( // TODO... This now just don't make much sense
            "Cms"                 to "Commissions",
            "Commissions"         to "Commissions",
            "Quotes"              to "Quotes",
            "Projects"            to "Projects",
            "Premades"            to "Premades",
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
