package tracking.processing

import tracking.statuses.Offer

class GroupNamesResolver {
    fun offersFrom(groupName: String): List<Offer> {
        return groupName
            .replace("Cms", "Commissions")
            .split("And")
    }
}
