package tracking.matchers

import org.junit.jupiter.api.Assertions.*
import org.junit.jupiter.api.Test

class PlaceholdersTest {
    @Test
    fun `List with secondary constructor, throwing on getMap()`() {
        val subject = Placeholders("a", "b", "c")

        assertTrue(subject.isLeaf())
        assertIterableEquals(listOf("a", "b", "c"), subject.getList())
        assertThrows(UnsupportedOperationException::class.java) { subject.getMap() }
    }

    @Test
    fun `Map of lists with secondary constructor, throwing on getList()`() {
        val subject = Placeholders(
            "a" to listOf("b", "c"),
            "d" to listOf("e", "f"),
        )

        assertFalse(subject.isLeaf())
        assertThrows(UnsupportedOperationException::class.java) { subject.getList() }
        assertIterableEquals(listOf("a", "d"), subject.getMap().keys)

        val aList = subject.getMap()["a"]
        assertNotNull(aList)
        assertTrue(aList!!.isLeaf())
        assertIterableEquals(listOf("b", "c"), aList.getList())

        val dList = subject.getMap()["d"]
        assertNotNull(dList)
        assertTrue(dList!!.isLeaf())
        assertIterableEquals(listOf("e", "f"), dList.getList())
    }

    @Test
    fun `Map of mixed with secondary constructor`() {
        val subject = Placeholders(
            "a" to listOf("b", "c"),
            "d" to mapOf("e" to listOf("f", "g")),
        )

        assertFalse(subject.isLeaf())
        assertIterableEquals(listOf("a", "d"), subject.getMap().keys)

        val aList = subject.getMap()["a"]
        assertNotNull(aList)
        assertTrue(aList!!.isLeaf())
        assertIterableEquals(listOf("b", "c"), aList.getList())

        val dMap = subject.getMap()["d"]
        assertNotNull(dMap)
        assertFalse(dMap!!.isLeaf())
        assertIterableEquals(listOf("e"), dMap.getMap().keys)

        val eList = dMap.getMap()["e"]
        assertNotNull(eList)
        assertTrue(eList!!.isLeaf())
        assertIterableEquals(listOf("f", "g"), eList.getList())
    }
}
