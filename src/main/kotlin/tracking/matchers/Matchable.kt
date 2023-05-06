package tracking.matchers

interface Matchable {
    fun replaceIn(subject: String): String
    fun wasUsed(): Boolean
}
