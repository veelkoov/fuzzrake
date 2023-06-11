package tracking.website

import org.jsoup.Jsoup

object TwitterProfileStrategy : Strategy {
    override fun filter(input: String): String {
        return Jsoup.parse(input)
            .head()
            .selectXpath("//meta[@property='og:description']")
            .attr("content")
    }
}
