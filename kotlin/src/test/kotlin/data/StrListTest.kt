package data

import kotlin.test.Test
import kotlin.test.assertEquals

class StrListTest {
    @Test
    fun `Unpacking empty string results in an empty list and vice versa`() {
        assertEquals(listOf(), "".unpack())

        assertEquals("", listOf<String>().pack())
    }

    @Test
    fun pack() {
        assertEquals("a test item", listOf("a test item").pack())

        assertEquals("a test item\nanother test item", listOf("a test item", "another test item").pack())
    }

    @Test
    fun unpack() {
        assertEquals(listOf("a test item"), "a test item".unpack())

        assertEquals(listOf("a test item", "another test item"), "a test item\nanother test item".unpack())
    }

    @Test
    fun `Newlines in items are unsupported (yet)`() {
        val input = listOf("a test item", "a broken\nunsupported item")

        val result = input.pack().unpack()

        assertEquals(listOf("a test item", "a broken", "unsupported item"), result)
    }
}
