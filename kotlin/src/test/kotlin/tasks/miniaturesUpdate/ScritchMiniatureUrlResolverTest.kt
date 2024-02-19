package tasks.miniaturesUpdate

import testUtils.ExpectedHttpCall
import testUtils.getHttpClientMock
import web.url.FreeUrl
import kotlin.test.*

class ScritchMiniatureUrlResolverTest {
    @Test
    fun getMiniatureUrl() {
        val httpClient = getHttpClientMock(
            ExpectedHttpCall(
                "https://scritch.es/",
                null,
                mapOf(),
                "",
                mapOf("Set-Cookie" to "csrf-token=%21%40%23%24%25%5E%26*%28%29; path=/; SameSite=Strict"),
            ),
            ExpectedHttpCall(
                "https://scritch.es/graphql",
                "{\"operationName\": \"Medium\", \"variables\": {\"id\": \"847486df-64fc-45a2-b74b-11fd87fe43ca\"}, \"query\": \"query Medium(\$id: ID!, \$tagging: Boolean) { medium(id: \$id, tagging: \$tagging) { thumbnail } }\"}",
                mapOf("authorization" to "Scritcher !@#\$%^&*()", "X-CSRF-Token" to "!@#\$%^&*()"),
                "{\"data\": {\"medium\": {\"thumbnail\": \"https://storage.scritch.es/scritch/45fbfc5483674d20dfd4cf6a342ea6653bd70440/thumbnail_9989c527-725a-4e98-b916-004c7ed91716.jpeg\"}}}",
                mapOf(),
            ),
            ExpectedHttpCall(
                "https://scritch.es/graphql",
                "{\"operationName\": \"Medium\", \"variables\": {\"id\": \"b4a47593-f0e2-43b4-bc74-df6b9c3f555f\"}, \"query\": \"query Medium(\$id: ID!, \$tagging: Boolean) { medium(id: \$id, tagging: \$tagging) { thumbnail } }\"}",
                mapOf("authorization" to "Scritcher !@#\$%^&*()", "X-CSRF-Token" to "!@#\$%^&*()"),
                "{\"data\": {\"medium\": {\"thumbnail\": \"https://storage.scritch.es/scritch/2a8ff452966723efe44ac65db076778e299e6824/thumbnail_77263eca-0ac2-4446-b86d-1f1fe21569a6.jpeg\"}}}",
                mapOf(),
            ),
        )

        val subject = ScritchMiniatureUrlResolver(httpClient)

        assertEquals(
            "https://storage.scritch.es/scritch/45fbfc5483674d20dfd4cf6a342ea6653bd70440/thumbnail_9989c527-725a-4e98-b916-004c7ed91716.jpeg",
            subject.getMiniatureUrl(FreeUrl("https://scritch.es/pictures/847486df-64fc-45a2-b74b-11fd87fe43ca"))
        )
        assertEquals(
            "https://storage.scritch.es/scritch/2a8ff452966723efe44ac65db076778e299e6824/thumbnail_77263eca-0ac2-4446-b86d-1f1fe21569a6.jpeg",
            subject.getMiniatureUrl(FreeUrl("https://scritch.es/pictures/b4a47593-f0e2-43b4-bc74-df6b9c3f555f"))
        )
    }

    @Test
    fun `Test handling missing csrf-token cookie`()
    {
        val httpClient = getHttpClientMock(
            ExpectedHttpCall("https://scritch.es/", null, mapOf(), "", mapOf()),
        )

        val subject = ScritchMiniatureUrlResolver(httpClient)

        val exception = assertFailsWith <MiniatureUrlResolverException> {
            subject.getMiniatureUrl(FreeUrl("https://scritch.es/pictures/b4a47593-f0e2-43b4-bc74-df6b9c3f555f"))
        }

        assertEquals(exception.message, "Missing csrf-token cookie")
    }
}
