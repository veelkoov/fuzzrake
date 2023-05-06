package tracking.matchers.replace

import kotlin.text.Regex

class RgxReplace(
    private val regex: Regex,
    private val replacement: String,
) : AbstractReplace() {
    constructor(pattern: String, options: Set<RegexOption>, replacement: String): this(Regex(pattern, options), replacement)

    override fun doReplace(subject: String) = subject.replace(regex, replacement)
}
