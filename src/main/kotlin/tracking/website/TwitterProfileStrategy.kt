package tracking.website

import io.github.oshai.kotlinlogging.KotlinLogging
import org.jsoup.Jsoup
import web.url.FreeUrl

private val logger = KotlinLogging.logger {}

object TwitterProfileStrategy : Strategy {
    private val cookieInitUrl = FreeUrl("https://twitter.com/", StandardStrategy)
    private val profileUrlRegex = Regex("https?://(m\\.|www\\.)?twitter\\.com/[^/?]+(\\?.*)?")

    override fun isSuitableFor(url: String) = profileUrlRegex.matches(url)

    override fun filterContents(contents: String): String {
        val document = Jsoup.parse(contents)

        val descriptionNode = document
            .head()
            .selectXpath("//meta[@property='og:description']")

        return if (!descriptionNode.isEmpty() && descriptionNode.hasAttr("content")) {
            descriptionNode.attr("content")
        } else {
            logger.warn("Failed to parse Twitter meta description content")

            contents
        }
    }

    override fun getCookieInitUrl() = cookieInitUrl
}
