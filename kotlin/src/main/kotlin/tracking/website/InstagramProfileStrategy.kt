package tracking.website

import io.github.oshai.kotlinlogging.KotlinLogging
import org.jsoup.Jsoup
import web.url.Url
import web.url.UrlForTracking

private val logger = KotlinLogging.logger {}

object InstagramProfileStrategy : Strategy {
    private val instagramProfileUrl = Regex("^https?://(www\\.)?instagram\\.com/(?<username>[^/]+)/?$")

    override fun isSuitableFor(url: String) = instagramProfileUrl.matches(url)

    override fun getUrlForTracking(url: Url): Url {
        return instagramProfileUrl.matchEntire(url.getUrl())
            ?.run { UrlForTracking(url, "https://www.instagram.com/${groups["username"]!!.value}/profilecard/") }
            ?: url
    }

    override fun filterContents(input: String): String {
        val document = Jsoup.parse(input)

        val descriptionNodes = document.head().selectXpath("//meta[@property='description']")

        if (descriptionNodes.isEmpty()) {
            logger.warn { "Failed reading description node" }

            return input
        }

        return descriptionNodes.attr("content")
    }
}
