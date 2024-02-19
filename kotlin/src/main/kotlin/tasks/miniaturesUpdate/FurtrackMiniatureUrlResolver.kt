package tasks.miniaturesUpdate

import data.JsonNavigator
import web.client.HttpClientInterface
import web.snapshots.Snapshot
import web.url.FreeUrl

class FurtrackMiniatureUrlResolver(
    httpClient: HttpClientInterface? = null,
) : JsonResponseBasedMiniatureUrlResolver(
    httpClient,
    "^https://www.furtrack.com/p/(?<pictureId>\\d+)\$",
) {
    override fun getResponseForPictureId(pictureId: String): Snapshot {
        return httpClient.fetch(FreeUrl("https://solar.furtrack.com/view/post/$pictureId"))
    }

    override fun miniatureUrlFromJsonData(data: JsonNavigator): String {
        val postStub = data.getNonEmptyString("post/postStub")
        val metaFiletype = data.getNonEmptyString("post/metaFiletype")

        return "https://orca.furtrack.com/gallery/thumb/$postStub.$metaFiletype"
    }
}
