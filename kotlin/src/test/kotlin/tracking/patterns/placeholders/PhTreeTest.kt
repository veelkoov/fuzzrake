package tracking.patterns.placeholders

import kotlin.test.*

class PhTreeTest {
    @Test
    fun `List with secondary constructor, throwing on getMap()`() {
        val subject = PhTree("a", "b", "c")

        assertTrue(subject.isLeaf())
        assertContentEquals(listOf("a", "b", "c"), subject.getList())
        assertFailsWith<UnsupportedOperationException> { subject.getMap() }
    }

    @Test
    fun `Map of lists with secondary constructor, throwing on getList()`() {
        val subject = PhTree(
            "a" to listOf("b", "c"),
            "d" to listOf("e", "f"),
        )

        assertFalse(subject.isLeaf())
        assertFailsWith<UnsupportedOperationException> { subject.getList() }
        assertContentEquals(listOf("a", "d"), subject.getMap().keys)

        val aList = subject.getMap()["a"]
        assertNotNull(aList)
        assertTrue(aList.isLeaf())
        assertContentEquals(listOf("b", "c"), aList.getList())

        val dList = subject.getMap()["d"]
        assertNotNull(dList)
        assertTrue(dList.isLeaf())
        assertContentEquals(listOf("e", "f"), dList.getList())
    }

    @Test
    fun `Map of mixed with secondary constructor`() {
        val subject = PhTree(
            "a" to listOf("b", "c"),
            "d" to mapOf("e" to listOf("f", "g")),
        )

        assertFalse(subject.isLeaf())
        assertContentEquals(listOf("a", "d"), subject.getMap().keys)

        val aList = subject.getMap()["a"]
        assertNotNull(aList)
        assertTrue(aList.isLeaf())
        assertContentEquals(listOf("b", "c"), aList.getList())

        val dMap = subject.getMap()["d"]
        assertNotNull(dMap)
        assertFalse(dMap.isLeaf())
        assertContentEquals(listOf("e"), dMap.getMap().keys)

        val eList = dMap.getMap()["e"]
        assertNotNull(eList)
        assertTrue(eList.isLeaf())
        assertContentEquals(listOf("f", "g"), eList.getList())
    }
}
