package tracking.website

import io.github.oshai.kotlinlogging.KotlinLogging
import org.jsoup.Jsoup

private val logger = KotlinLogging.logger {}

object FurAffinityProfileStrategy : Strategy {
    private val profileUrlRegex = Regex("^https?://(www\\.)?furaffinity\\.net/user/(?<username>[^/]+)/?([#?].*)?$")

    override fun isSuitableFor(url: String) = profileUrlRegex.matches(url)

    override fun filterContents(input: String): String {
        val document = Jsoup.parse(input)

        val element = document
            .body()
            .select("#page-userpage div.userpage-profile")

        return if (!element.isEmpty()) {
            element.html()
        } else {
            logger.warn("Failed to parse Fur Affinity user profile content")

            input
        }
    }
}
