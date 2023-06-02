package tracking.matchers

object Workarounds {
    private val groupsRgx = Regex("\\(\\?<([a-zA-Z0-9]+)>\\)")

    fun possibleGroups(regex: Regex): List<String> {
        return groupsRgx.findAll(regex.pattern).map { it.groups[1]!!.value }.toList()
    }

    fun matchedGroups(result: MatchResult, possible: List<String>): List<Pair<String, String>> {
        return possible
            .map { groupName ->
                groupName to (result.groups[groupName]?.value ?: return@map null)
            }
            .filterNotNull()
    }
}
