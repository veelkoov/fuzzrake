package tracking.website

object TrelloStrategy : Strategy {
    private val trelloBoardOrCardUrl = Regex("^https://trello.com/(?<type>[bc])/(?<id>[^/]+)(/.*)?$")

    override fun isSuitableFor(url: String) = trelloBoardOrCardUrl.matches(url)

    override fun coerceUrl(url: String): String {
        return trelloBoardOrCardUrl.matchEntire(url)
            .run {
                if (this == null) {
                    url
                } else if (groups["type"]?.value == "b") {
                    "https://trello.com/1/boards/${groups["id"]!!.value}?fields=name%2Cdesc&cards=visible&card_fields=name%2Cdesc"
                } else {
                    "https://trello.com/1/cards/${groups["id"]!!.value}?fields=name%2Cdesc"
                }
            }
    }
}
