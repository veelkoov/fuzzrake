package tracking.website

import io.github.oshai.kotlinlogging.KotlinLogging
import org.jsoup.Jsoup
import web.url.FreeUrl
import web.url.Url

private val logger = KotlinLogging.logger {}

/**
 * As of 2024-06-14 the Twitter's behavior is to send around 3700 bytes of the page,
 * often enough to cover the description and title.
 */
object TwitterProfileStrategy : Strategy {
    private val cookieInitUrl = FreeUrl("https://twitter.com/", StandardStrategy)
    private val loginLocationHeader = Regex("^location: \\S+login\\S+$", setOf(RegexOption.MULTILINE, RegexOption.IGNORE_CASE))
    private val profileUrlRegex = Regex("https?://(m\\.|www\\.)?twitter\\.com/[^/?]+(\\?.*)?")

    override fun isSuitableFor(url: String) = profileUrlRegex.matches(url)

    override fun filterContents(input: String): String {
        val document = Jsoup.parse(input)

        val ogDescriptionNodes = document.head().selectXpath("//meta[@property='og:description']") // grep-code-og-description-removal
        val ogTitleNodes = document.head().selectXpath("//meta[@property='og:title']")

        if (ogDescriptionNodes.isEmpty() || ogTitleNodes.isEmpty()) {
            logger.warn { "Failed reading og:title and/or og:description nodes" }

            return input
        }

        val ogTitle: String = ogTitleNodes.attr("content")
        val ogDescription: String = ogDescriptionNodes.attr("content")

        return ogTitle + "\n" + ogDescription
    }

    override fun getLatentCode(url: Url, contents: String, originalCode: Int): Int {
        return if (originalCode == 302 && contents.contains(loginLocationHeader)) {
            401 // SPACE KAREN
        } else {
            originalCode
        }
    }

    override fun getCookieInitUrl() = cookieInitUrl
}
