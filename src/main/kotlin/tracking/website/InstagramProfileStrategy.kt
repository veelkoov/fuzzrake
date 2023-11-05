package tracking.website

import data.JsonException
import data.JsonNavigator
import io.github.oshai.kotlinlogging.KotlinLogging
import web.url.FreeUrl
import web.url.Url
import web.url.UrlForTracking

private val logger = KotlinLogging.logger {}

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

    override fun filterContents(input: String): String {
        return try {
            JsonNavigator(input).getString("graphql/user/biography")
        } catch (exception: JsonException) {
            logger.warn(exception) { "Failed to parse Instagram user profile data" }

            input
        }
    }

    override fun getCookieInitUrl() = cookieInitUrl
}
