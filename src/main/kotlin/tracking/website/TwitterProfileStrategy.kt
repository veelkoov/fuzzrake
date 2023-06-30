package tracking.website

import data.JsonException
import data.JsonNavigator
import io.github.oshai.kotlinlogging.KotlinLogging
import org.jsoup.Jsoup
import web.url.FreeUrl

private val logger = KotlinLogging.logger {}

object TwitterProfileStrategy : Strategy {
    private val cookieInitUrl = FreeUrl("https://twitter.com/", StandardStrategy)
    private val profileUrlRegex = Regex("https?://(m\\.|www\\.)?twitter\\.com/[^/?]+(\\?.*)?")

    override fun isSuitableFor(url: String) = profileUrlRegex.matches(url)

    override fun filterContents(input: String): String {
        val document = Jsoup.parse(input)

        val ldJsonNodes = document
            .head()
            .selectXpath("//script[@type='application/ld+json'][contains(text(), 'ProfilePage')]")

        if (ldJsonNodes.isEmpty()) {
            logger.warn("Failed to XPath Twitter ProfilePage schema script node")

            return input
        }

        return try {
            val ldJsonData = JsonNavigator(ldJsonNodes.html())

            ldJsonData.getString("author/givenName") +
                    "\n" + ldJsonData.getString("author/description") +
                    "\n" + ldJsonData.getString("author/homeLocation/name")

        } catch (exception: JsonException) {
            logger.warn("Failed reading ProfilePage schema JSON", exception)

            input
        }
    }

    override fun getCookieInitUrl() = cookieInitUrl
}
