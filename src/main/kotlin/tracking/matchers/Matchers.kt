package tracking.matchers

class Matchers(
    private val items: List<Matcher>,
) {
    fun matchIn(subject: String, withEach: (MatchResult, Matcher) -> Unit): String {
        var result = subject

        items.forEach { matcher ->
            while (true) {
                val match = matcher.matchIn(result) ?: break
                result = result.replaceFirst(match.value, "")

                withEach(match, matcher)
            }
        }

        return result
    }
}
