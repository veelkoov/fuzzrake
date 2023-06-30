package data

import com.fasterxml.jackson.core.JsonParseException
import org.junit.jupiter.api.Assertions.*
import org.junit.jupiter.api.assertThrows
import kotlin.test.Test
import kotlin.test.assertIs

class JsonNavigatorTest {
    @Test
    fun `Exception on parsing empty string`() {
        val exception = assertThrows<JsonException> {
            JsonNavigator("")
        }

        assertEquals("No JSON content to parse", exception.message)
    }

    @Test
    fun `Exception on unparseable JSON`() {
        val exception = assertThrows<JsonException> {
            JsonNavigator("?")
        }

        assertIs<JsonParseException>(exception.cause)
    }

    @Test
    fun `Exception on traversing through a scalar`() {
        val exception = assertThrows<JsonException> {
            JsonNavigator("{\"scalar\": 0}").getString("scalar/inexistent")
        }

        assertEquals("Path /scalar is not an object", exception.message)
    }

    @Test
    fun `Exception on non-existent item, level 0`() {
        val exception = assertThrows<JsonException> {
            JsonNavigator("{\"not this\": {}}").getString("inexistent")
        }

        assertEquals("Path /inexistent does not exist", exception.message)
    }

    @Test
    fun `Exception on non-existent item, level 1`() {
        val exception = assertThrows<JsonException> {
            JsonNavigator("{\"path1\": {\"not this\": 0}}").getString("path1/inexistent")
        }

        assertEquals("Path /path1/inexistent does not exist", exception.message)
    }

    @Test
    fun `Exception on non-string item`() {
        val exception = assertThrows<JsonException> {
            JsonNavigator("{\"path1\": 0}").getString("path1")
        }

        assertEquals("Path /path1 is not a string", exception.message)
    }

    @Test
    fun `Exception on null item`() {
        val exception = assertThrows<JsonException> {
            JsonNavigator("{\"path1\": null}").getString("path1")
        }

        assertEquals("Path /path1 is not a string", exception.message)
    }

    @Test
    fun `Successful string retrieval`() {
        val result = JsonNavigator("{\"path1\": {\"path2\": \"the expected value\"}}").getString("path1/path2")

        assertEquals("the expected value", result)
    }
}
