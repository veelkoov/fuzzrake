package tracking.matchers

import kotlin.text.Regex

class Matcher(
    private val regex: Regex,
) : Usable {
    private var wasUsed = false
    val groups = Workarounds.possibleGroups(regex)


    constructor(pattern: String, options: Set<RegexOption>): this(Regex(pattern, options))

    fun matchIn(subject: String): MatchResult? {
        val result = regex.find(subject)

        if (result != null) {
            wasUsed = true
        }

        return result
    }

    override fun wasUsed() = wasUsed
}
