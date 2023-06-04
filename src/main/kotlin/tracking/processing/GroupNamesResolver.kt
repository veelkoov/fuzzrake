package tracking.processing

import tracking.statuses.Offer

class GroupNamesResolver {
    fun offersFrom(groupName: String): List<Offer> {
        return groupName
            .split("And")
            .map(::prettify)
    }

    companion object {
        private val prettyNames = mapOf( // TODO: Consider using actually pretty names (capitalized?)
            "Cms"         to "COMMISSIONS",
            "Commissions" to "COMMISSIONS",
            "Quotes"      to "QUOTES",
            "Projects"    to "PROJECTS",
            "PreMades"    to "PRE-MADES",
            "HandpawsCms" to "HANDPAWS COMMISSIONS",
            "SockpawsCms" to "SOCKPAWS COMMISSIONS",
        )

        private fun prettify(input: String) = prettyNames.getOrDefault(input, input)
    }
}
