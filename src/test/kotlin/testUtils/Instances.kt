package testUtils

import data.ThreadSafe
import database.entities.Creator
import tracking.contents.CreatorData
import web.url.FreeUrl
import web.url.Url

fun getCreatorData(creatorId: String = "", aliases: List<String> = listOf()): CreatorData {
     val creator = ThreadSafe(disposableTransaction { Creator.new {} })

     return CreatorData(creatorId, aliases, creator)
}

fun getUrl(url: String = "http://localhost/"): Url {
     return FreeUrl(url)
}
