package tracking.matchers

import tracking.matchers.regex.MatchResultJ


class Matchers(
    private val items: List<Matcher>,
) {
    fun matchIn(subject: String, withEach: (MatchResultJ) -> Unit): String {
        var result = subject

        items.forEach { matcher ->
            while (true) {
                val match = matcher.matchIn(result) ?: break
                result = result.replaceFirst(match.value, "")

                withEach(match)
            }
        }

        return result
    }
}
