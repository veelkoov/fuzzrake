package tracking.website

import data.JsonException
import data.JsonNavigator
import io.github.oshai.kotlinlogging.KotlinLogging
import org.jsoup.Jsoup
import org.jsoup.nodes.Document
import web.url.FreeUrl
import web.url.Url

private val logger = KotlinLogging.logger {}

object TwitterProfileStrategy : Strategy {
    private val cookieInitUrl = FreeUrl("https://twitter.com/", StandardStrategy)
    private val loginLocationHeader = Regex("^location: \\S+login\\S+$", setOf(RegexOption.MULTILINE, RegexOption.IGNORE_CASE))
    private val profileUrlRegex = Regex("https?://(m\\.|www\\.)?twitter\\.com/[^/?]+(\\?.*)?")

    override fun isSuitableFor(url: String) = profileUrlRegex.matches(url)

    override fun filterContents(input: String): String {
        val document = Jsoup.parse(input)

        return tryFromLdJsonProfilePage(document) ?: tryFromMeta(document) ?: input
    }

    private fun tryFromMeta(document: Document): String? {
        val ogDescriptionNodes = document.head().selectXpath("//meta[@property='og:description']") // grep-code-og-description-removal
        val ogTitleNodes = document.head().selectXpath("//meta[@property='og:title']")

        val ogTitle: String? = ogTitleNodes.attr("content")
        val ogDescription: String? = ogDescriptionNodes.attr("content")

        if (ogTitle == null || ogDescription == null) {
            logger.warn { "Failed reading og:title and/or og:description nodes" }

            return null
        }

        return ogTitle + "\n" + ogDescription
    }

    private fun tryFromLdJsonProfilePage(document: Document): String? {
        val ldJsonNodes = document
            .head()
            .selectXpath("//script[@type='application/ld+json'][contains(text(), 'ProfilePage')]")

        if (ldJsonNodes.isEmpty()) {
            logger.info { "Failed to XPath Twitter ProfilePage schema script node" }

            return null
        }

        return try {
            val ldJsonData = JsonNavigator(ldJsonNodes.html())

            ldJsonData.getString("author/givenName") +
                    "\n" + ldJsonData.getString("author/description") +
                    "\n" + ldJsonData.getString("author/homeLocation/name")
        } catch (exception: JsonException) {
            logger.warn(exception) { "Failed reading ProfilePage schema JSON" }

            null
        }
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
