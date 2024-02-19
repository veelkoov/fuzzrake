package tracking.website

import web.url.UrlForTracking
import web.url.Url

object TrelloStrategy : Strategy {
    private val trelloBoardOrCardUrl = Regex("^https://trello.com/(?<type>[bc])/(?<id>[^/]+)(/.*)?$")

    override fun isSuitableFor(url: String) = trelloBoardOrCardUrl.matches(url)

    override fun getUrlForTracking(url: Url): Url {
        return trelloBoardOrCardUrl.matchEntire(url.getUrl())
            .run {
                if (this == null) {
                    url
                } else if (groups["type"]?.value == "b") {
                    UrlForTracking(url, "https://trello.com/1/boards/${groups["id"]!!.value}?fields=name%2Cdesc&cards=visible&card_fields=name%2Cdesc")
                } else {
                    UrlForTracking(url, "https://trello.com/1/cards/${groups["id"]!!.value}?fields=name%2Cdesc")
                }
            }
    }
}
