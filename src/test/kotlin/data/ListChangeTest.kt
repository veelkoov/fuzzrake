package data

import org.junit.jupiter.api.Assertions.*
import org.junit.jupiter.api.Test

class ListChangeTest {
    @Test
    fun calculatesProperly() {
        val before = listOf("aaa", "bbb")
        val after = listOf("bbb", "ccc")

        val subject = ListChange(before, after)

        assertEquals(listOf("aaa"), subject.removed)
        assertEquals(listOf("ccc"), subject.added)
    }
}
