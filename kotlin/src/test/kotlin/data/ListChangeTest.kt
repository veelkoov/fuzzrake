package data

import kotlin.test.Test
import kotlin.test.assertEquals

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
