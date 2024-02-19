package tracking.patterns.regex

data class MatchResultJ(
    private val wrapped: MatchResult,
    private val jWorkaround: JWorkaround,
) {
    val groups = jWorkaround.getGroups(wrapped)
    val value = wrapped.value
}
