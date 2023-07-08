package web.url

import tracking.website.Strategy
import java.net.URL

abstract class AbstractUrl(
    private val url: String,
    private val strategy: Strategy,
) : Url {
    private val netUrl = URL(url)

    override fun getUrl() = url
    override fun getStrategy() = strategy
    override fun getHost(): String = netUrl.host
}
