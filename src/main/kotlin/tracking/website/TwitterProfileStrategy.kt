package tracking.website

import org.jsoup.Jsoup

object TwitterProfileStrategy : Strategy {
    private val profileUrlRegex = Regex("https?://(m\\.|www\\.)?twitter\\.com/[^/?]+(\\?.*)?")

    override fun isSuitableFor(url: String) = profileUrlRegex.matches(url)

    override fun filterContents(contents: String): String {
        return Jsoup.parse(contents)
            .head()
            .selectXpath("//meta[@property='og:description']")
            .attr("content")
    }
}
