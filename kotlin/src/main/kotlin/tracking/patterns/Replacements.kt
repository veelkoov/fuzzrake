package tracking.patterns

class Replacements(
    private val items: List<Replacement>,
) {
    fun replaceIn(subject: String): String {
        var result = subject

        items.forEach { result = it.replaceIn(result) }

        return result
    }
}
