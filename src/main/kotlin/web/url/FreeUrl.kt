package web.url

import tracking.website.Strategy

class FreeUrl(
    private val url: String,
    private val strategy: Strategy = Strategy.forUrl(url),
) : AbstractUrl(url, strategy) {
    override fun getUrl() = url

    override fun recordSuccessfulFetch() = Unit
    override fun recordFailedFetch(code: Int, reason: String) = Unit
}
