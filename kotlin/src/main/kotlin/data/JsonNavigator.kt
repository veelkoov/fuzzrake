package data

import com.fasterxml.jackson.core.JsonFactory
import com.fasterxml.jackson.core.JsonParseException
import com.fasterxml.jackson.databind.JsonNode
import com.fasterxml.jackson.databind.ObjectMapper

class JsonNavigator(input: String) {
    private val nodes: JsonNode = parseJson(input)

    private fun parseJson(input: String): JsonNode {
        try {
            val result: JsonNode? = mapper.readTree(input)

            if (result == null || result.isEmpty) {
                throw JsonException("No JSON content to parse")
            }

            return result
        } catch (exception: JsonParseException) {
            throw JsonException("Failed to parse JSON", exception)
        }
    }

    fun getString(path: String): String {
        var currentNode: JsonNode = nodes
        var currentPath = "/"

        path.split("/").forEach {
            if (!currentNode.isObject) {
                throw JsonException("Path $currentPath is not an object")
            }

            if (!currentPath.endsWith("/")) {
                currentPath += "/"
            }

            currentPath += it

            currentNode = currentNode[it]
                ?: throw JsonException("Path $currentPath does not exist")
        }

        return currentNode.textValue()
            ?: throw JsonException("Path $currentPath is not a string")
    }

    fun getNonEmptyString(path: String): String {
        val result = getString(path)

        if ("" == result) {
            throw JsonException("Path $path is an empty string")
        }

        return result
    }

    companion object {
        private val mapper = ObjectMapper(JsonFactory())
    }
}
