package tracking.website

import io.github.oshai.kotlinlogging.KotlinLogging
import org.jsoup.Jsoup
import web.url.Url

private val logger = KotlinLogging.logger {}

object FurAffinityProfileStrategy : Strategy {
    private const val FA_SYSTEM_ERROR_CONTENTS_SEARCH_STRING = "<title>System Error</title>"
    private const val FA_USER_NOT_FOUND_CONTENTS_SEARCH_STRING = "This user cannot be found."
    private const val FA_USER_PROFILE_REGISTERED_ONLY_SEARCH_STRING = "<div class=\"redirect-message\">" +
            "<p class=\"link-override\">The owner of this page has elected to make it available to registered users only."

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

    override fun getLatentCode(url: Url, contents: String, originalCode: Int): Int { // TODO: Test this; translated but no known cases currently
        if (originalCode != 200) {
            return originalCode
        }

        if (contents.contains(FA_USER_NOT_FOUND_CONTENTS_SEARCH_STRING)
            && contents.contains(FA_SYSTEM_ERROR_CONTENTS_SEARCH_STRING)
        ) {
            return 404
        }

        if (contents.contains(FA_USER_PROFILE_REGISTERED_ONLY_SEARCH_STRING)) {
            return 401
        }

        return originalCode
    }
}
