package web.url

import tracking.website.Strategy

class FreeUrl(
    url: String,
    strategy: Strategy = Strategy.forUrl(url),
) : AbstractUrl(url, strategy) {

    override fun recordSuccessfulFetch() = Unit
    override fun recordFailedFetch(code: Int, reason: String) = Unit
}
