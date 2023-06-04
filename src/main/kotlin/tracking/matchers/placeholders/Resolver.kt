package tracking.matchers.placeholders

class Resolver(
    placeholders: Map<String, String>
) {
    private val placeholders = placeholders
        .mapKeys { getPlaceholderRegex(it.key) }
        .mapValues { Regex.escapeReplacement(it.value) }

    fun resolve(subject: String): String {
        var result = subject
        var changed: Boolean

        do {
            changed = false

            placeholders.forEach { (regex, replacement) ->
                val newResult = regex.replace(result, replacement)

                if (newResult != result) {
                    result = newResult
                    changed = true
                }
            }
        } while (changed)

        return result
    }

    fun resolveIn(subject: List<String>) = subject.map(::resolve)

    /**
     * Returns a regex which matches the given placeholder, ensuring we won't match anything
     * in the middle of a word/other placeholder.
     */
    private fun getPlaceholderRegex(placeholder: String): Regex {
        val start = if (placeholder.startsWith(" ")) "" else "(?<=^|[^A-Z_])"
        val end = if (placeholder.endsWith(" ")) "" else "(?=[^A-Z_]|$)"
        val escapedPlaceholder = Regex.escape(placeholder)

        return Regex("${start}${escapedPlaceholder}${end}")
    }
}
