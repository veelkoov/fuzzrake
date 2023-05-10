package tracking.matchers.placeholders

data class PhList(
    val placeholders: List<Pair<String, String>>,
    val groups: List<String>,
)
