package tracking.matchers

import kotlin.text.Regex

class Replacement(
    private val regex: Regex,
    private val replacement: String,
) : Usable {
    private var wasUsed = false

    constructor(pattern: String, options: Set<RegexOption>, replacement: String): this(Regex(pattern, options), replacement)

    fun replaceIn(subject: String): String {
        val result = subject.replace(regex, replacement)

        if (result != subject) {
            wasUsed = true
        }

        return result
    }

    override fun wasUsed() = wasUsed
}
