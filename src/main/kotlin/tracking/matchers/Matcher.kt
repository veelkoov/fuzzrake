package tracking.matchers

import tracking.matchers.regex.RegexJ
import tracking.matchers.regex.MatchResultJ

class Matcher(
    private val regex: RegexJ,
) : Usable {
    private var wasUsed = false

    constructor(pattern: String, options: Set<RegexOption>): this(RegexJ(pattern, options))

    fun matchIn(subject: String): MatchResultJ? {
        val result = regex.find(subject)

        if (result != null) {
            wasUsed = true
        }

        return result
    }

    override fun wasUsed() = wasUsed
}
