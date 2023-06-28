package web.url

import tracking.website.Strategy
import java.net.URL

abstract class AbstractUrl(
    url: String,
    private val strategy: Strategy,
) : Url {
    private val url = URL(url)

    override fun getStrategy() = strategy
    override fun getHost(): String = url.host
}
