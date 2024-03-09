package tracking.patterns.placeholders

import kotlin.test.Test
import kotlin.test.assertEquals
import kotlin.test.assertFailsWith

class ResolverFactoryTest {
    @Test
    fun `Building on flat list throws`() {
        val input = PhTree("a", "b", "c")

        assertFailsWith<IllegalArgumentException> {
            ResolverFactory().create(input)
        }
    }

    @Test
    fun `Duplicated group names throw`() {
        val input = PhTree(
            "ABCD=group1" to listOf("a", "b", "c"),
            "EFGH=group2" to mapOf(
                "IJKL=group1" to listOf("d", "e", "f"),
            ),
        )

        assertFailsWith<IllegalArgumentException> {
            ResolverFactory().create(input)
        }
    }

    @Test
    fun `Duplicated placeholders throw`() {
        val input = PhTree(
            "ABCD=group1" to listOf("a", "b", "c"),
            "EFGH=group2" to mapOf(
                "ABCD=group3" to listOf("d", "e", "f"),
            ),
        )

        assertFailsWith<IllegalArgumentException> {
            ResolverFactory().create(input)
        }
    }

    @Test
    fun `Invalid placeholder throws`() {
        val input = PhTree(
            "PLACehoLDER=group1" to listOf("a", "b", "c"),
        )

        assertFailsWith<IllegalArgumentException> {
            ResolverFactory().create(input)
        }
    }

    @Test
    fun `Generates proper product`() {
        val input = PhTree(
            "ABC=abc" to listOf("a", "b", "c"),
            "AABBCC=aabbcc" to listOf("ABC", "DEF"),
            "DEF" to listOf("d", "e", "f"),
        )

        val result = ResolverFactory().create(input)
            .resolve("<h1>DEF AABBCC</h1><p>DEF</p><p>ABCD</p>")

        assertEquals("<h1>(d|e|f) (?<aabbcc>(?<abc>a|b|c)|(d|e|f))</h1><p>(d|e|f)</p><p>ABCD</p>", result)
    }
}
