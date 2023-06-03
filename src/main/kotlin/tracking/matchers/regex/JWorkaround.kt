package tracking.matchers.regex

class JWorkaround(
    private val pattern: String,
) {
    /**
     * Key = J-patched group name.
     * Value = original group name.
     */
    private val groupNames: Map<String, String> = getGroupNamesMap(pattern)

    fun getPattern(): String {
        var result = pattern

        groupNames.forEach { (groupNameJWorkaround, originalGroupName) ->
            result = result.replaceFirst("(?<$originalGroupName>", "(?<$groupNameJWorkaround>")
        }

        return result
    }

    fun getGroups(matchResult: MatchResult): List<Pair<String, String>> {
        return groupNames.mapNotNull { (patchedGroupName, originalGroupName) ->
            matchResult.groups[patchedGroupName]?.value?.let { groupValue -> originalGroupName to groupValue }
        }
    }

    companion object {
        private val namedGroupRegex = Regex("\\(\\?<([a-zA-Z0-9]+)>") // (?<abc>

        private fun getGroupNamesMap(pattern: String): Map<String, String> {
            val groupNames = namedGroupRegex.findAll(pattern).map { it.groups[1]!!.value }.toList()

            return groupNames
                .mapIndexed { index, groupName -> Pair("$groupName$index", groupName) }
                .toMap()
        }
    }
}
