package tracking.website

interface Strategy {
    fun isSuitableFor(url: String): Boolean
    fun filterContents(contents: String): String = contents
    fun coerceUrl(url: String): String = url

    companion object {
        fun forUrl(url: String): Strategy {
            if (TwitterProfileStrategy.isSuitableFor(url)) {
                return TwitterProfileStrategy
            }

            if (InstagramProfileStrategy.isSuitableFor(url)) {
                return InstagramProfileStrategy
            }

            return StandardStrategy
        }
    }
}
