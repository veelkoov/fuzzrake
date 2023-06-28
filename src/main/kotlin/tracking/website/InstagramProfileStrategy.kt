package tracking.website

import web.url.UrlForTracking
import web.url.FreeUrl
import web.url.Url

object InstagramProfileStrategy : Strategy {
    private val cookieInitUrl = FreeUrl("https://www.instagram.com/", StandardStrategy)
    private val instagramProfileUrl = Regex("^https?://(www\\.)?instagram\\.com/(?<username>[^/]+)/?$")

    override fun isSuitableFor(url: String) = instagramProfileUrl.matches(url)

    override fun getUrlForTracking(url: Url): Url {
        // Credit: https://github.com/postaddictme/instagram-php-scraper/blob/fcc7207f300aa55fa08dd01db31ba694d020f26f/src/InstagramScraper/Endpoints.php#L13

        return instagramProfileUrl.matchEntire(url.getUrl())
            ?.run { UrlForTracking(url, "https://www.instagram.com/${groups["username"]!!.value}/?__a=1&__d=dis") }
            ?: url
    }

    override fun getCookieInitUrl() = cookieInitUrl
}
