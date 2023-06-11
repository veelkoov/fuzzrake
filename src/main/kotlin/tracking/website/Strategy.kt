package tracking.website

interface Strategy {
    fun filter(input: String): String

    companion object {
        private val twitterProfileUrl = Regex("https?://(m\\.|www\\.)?twitter\\.com/[^/?]+(\\?.*)?")

        fun forUrl(url: String): Strategy {
            if (twitterProfileUrl.matches(url)) {
                return TwitterProfileStrategy
            }

            return StandardStrategy
        }
    }
}
