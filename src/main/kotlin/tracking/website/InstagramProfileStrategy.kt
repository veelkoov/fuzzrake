package tracking.website

object InstagramProfileStrategy : Strategy {
    private val instagramProfileUrl = Regex("^https?://(www\\.)?instagram\\.com/(?<username>[^/]+)/?$")

    override fun isSuitableFor(url: String) = instagramProfileUrl.matches(url)

    override fun coerceUrl(url: String): String {
        // Credit: https://github.com/postaddictme/instagram-php-scraper/blob/fcc7207f300aa55fa08dd01db31ba694d020f26f/src/InstagramScraper/Endpoints.php#L13

        return instagramProfileUrl.matchEntire(url)
            ?.run { "https://www.instagram.com/${groups["username"]!!.value}/?__a=1&__d=dis" }
            ?: url
    }
}
