package web.url

class UrlForTracking(
    private val original: Url,
    private val url: String,
) : AbstractUrl(url, original.getStrategy()) {
    override fun getUrl() = url
}
