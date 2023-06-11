package tracking.matchers

import tracking.matchers.regex.RegexJ

class Replacement(
    private val regex: RegexJ,
    private val replacement: String,
) : Usable {
    private var wasUsed = false

    constructor(pattern: String, options: Set<RegexOption>, replacement: String): this(RegexJ(pattern, options), replacement)

    fun replaceIn(subject: String): String {
        val result = regex.replace(subject, replacement)

        if (result != subject) {
            wasUsed = true
        }

        return result
    }

    override fun wasUsed() = wasUsed
}
