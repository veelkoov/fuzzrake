package tracking.matchers.regex

import kotlin.text.Regex

class RegexJ(pattern: String, options: Set<RegexOption>) {
    init {
        if (pattern == "") {
            throw IllegalArgumentException("Regex pattern cannot be empty")
        }
    }

    private val jWorkaround = JWorkaround(pattern)
    private val regex = Regex(jWorkaround.getPattern(), options)

    constructor(pattern: String) : this(pattern, setOf())

    fun find(input: CharSequence, startIndex: Int = 0): MatchResultJ? {
        return regex.find(input, startIndex)?.let { matchResult -> MatchResultJ(matchResult, jWorkaround) }
    }

    fun replace(subject: String, replacement: String): String {
        return regex.replace(subject, replacement)
    }
}
