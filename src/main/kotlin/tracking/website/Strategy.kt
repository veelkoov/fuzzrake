package tracking.website

interface Strategy {
    fun filter(input: String): String;

    companion object {
        fun forUrl(url: String): Strategy {
            return StandardStrategy
        }
    }
}
