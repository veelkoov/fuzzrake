package tracking.website

import web.url.Url

interface Strategy {
    fun isSuitableFor(url: String): Boolean
    fun filterContents(input: String): String = input
    fun getUrlForTracking(url: Url): Url = url
    fun getCookieInitUrl(): Url? = null

    companion object {
        fun forUrl(url: String): Strategy {
            return when {
                TwitterProfileStrategy
                    .isSuitableFor(url) -> TwitterProfileStrategy

                InstagramProfileStrategy
                    .isSuitableFor(url) -> InstagramProfileStrategy

                TrelloStrategy
                    .isSuitableFor(url) -> TrelloStrategy

                FurAffinityProfileStrategy
                    .isSuitableFor(url) -> FurAffinityProfileStrategy

                else -> StandardStrategy
            }
        }
    }
}
