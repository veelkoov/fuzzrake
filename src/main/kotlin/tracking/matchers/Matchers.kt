package tracking.matchers

class Matchers(
    private val items: List<Matchable>,
) {
    fun replaceIn(subject: String): String {
        var result = subject

        items.forEach { result = it.replaceIn(result) }

        return result
    }
}
