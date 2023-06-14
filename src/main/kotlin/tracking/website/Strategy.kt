package tracking.website

interface Strategy {
    fun isSuitableFor(url: String): Boolean
    fun filterContents(contents: String): String = contents
    fun coerceUrl(url: String): String = url

    companion object {
        fun forUrl(url: String): Strategy {
            return when {
                TwitterProfileStrategy
                    .isSuitableFor(url) -> TwitterProfileStrategy

                InstagramProfileStrategy
                    .isSuitableFor(url) -> InstagramProfileStrategy

                TrelloStrategy
                    .isSuitableFor(url) -> TrelloStrategy

                else -> StandardStrategy
            }
        }
    }
}
