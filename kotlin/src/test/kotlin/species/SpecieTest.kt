package species

import testUtils.specieNamesSet
import kotlin.test.Test
import kotlin.test.assertContains
import kotlin.test.assertEquals
import kotlin.test.assertFailsWith

class SpecieTest {
    @Test
    fun `Relationship is being set two-way`() {
        val parent = Specie.Builder("Parent A")
        val child = Specie.Builder("Child A")

        parent.addChild(child)

        assertContains(
            parent.getResult().getChildren(),
            child.getResult(),
        )
        assertContains(
            child.getResult().getParents(),
            parent.getResult(),
        )
    }

    @Test
    fun `Test getParents() and getAncestors() sets`() {
        val top1a = Specie.Builder("Top 1A")
        val top1b = Specie.Builder("Top 1B")
        val top2a = Specie.Builder("Top 2A")
        val top2b = Specie.Builder("Top 2B")
        val middle1 = Specie.Builder("Middle 1")
        val middle2 = Specie.Builder("Middle 2")
        val bottom = Specie.Builder("Bottom")

        middle1.addChild(bottom)
        middle2.addChild(bottom)

        top1a.addChild(middle1)
        top1b.addChild(middle1)

        top2a.addChild(middle2)
        top2b.addChild(middle2)

        assertEquals(
            setOf("Top 1A", "Top 1B"),
            specieNamesSet(middle1.getResult().getParents()),
        )
        assertEquals(
            middle1.getResult().getParents(),
            middle1.getResult().getAncestors(),
        )
        assertEquals(
            setOf("Top 1A", "Top 1B", "Top 2A", "Top 2B", "Middle 1", "Middle 2"),
            specieNamesSet(bottom.getResult().getAncestors()),
        )
    }

    @Test
    fun `Test getChildren(), getDescendants() and getSelfAndDescendants() sets`() {
        val top = Specie.Builder("Top")
        val middle1 = Specie.Builder("Middle 1")
        val middle2 = Specie.Builder("Middle 2")
        val bottom1a = Specie.Builder("Bottom 1A")
        val bottom1b = Specie.Builder("Bottom 1B")
        val bottom2a = Specie.Builder("Bottom 2A")
        val bottom2b = Specie.Builder("Bottom 2B")

        top.addChild(middle1)
        top.addChild(middle2)

        middle1.addChild(bottom1a)
        middle1.addChild(bottom1b)

        middle2.addChild(bottom2a)
        middle2.addChild(bottom2b)

        assertEquals(
            setOf("Bottom 1A", "Bottom 1B"),
            specieNamesSet(middle1.getResult().getChildren()),
        )
        assertEquals(
            middle1.getResult().getChildren(),
            middle1.getResult().getDescendants(),
        )
        assertEquals(
            setOf("Middle 1", "Middle 2", "Bottom 1A", "Bottom 1B", "Bottom 2A", "Bottom 2B"),
            specieNamesSet(top.getResult().getDescendants()),
        )
        assertEquals(
            setOf("Top", "Middle 1", "Middle 2", "Bottom 1A", "Bottom 1B", "Bottom 2A", "Bottom 2B"),
            specieNamesSet(top.getResult().getSelfAndDescendants()),
        )
    }

    @Test
    fun `Cannot recurse itself`() {
        val specie = Specie.Builder("Test specie")

        assertFailsWith<SpecieException> {
            specie.addChild(specie)
        }

        assertFailsWith<SpecieException> {
            specie.addChild(specie)
        }
    }

    @Test
    fun `Cannot recurse with multiple steps`() {
        val specieA = Specie.Builder("Test specie A")
        val specieB = Specie.Builder("Test specie B")
        val specieC = Specie.Builder("Test specie C")

        specieA.addChild(specieB)
        specieB.addChild(specieC)

        assertFailsWith<SpecieException> {
            specieB.addChild(specieA)
        }

        assertFailsWith<SpecieException> {
            specieC.addChild(specieA)
        }

        assertFailsWith<SpecieException> {
            specieC.addChild(specieB)
        }
    }

    @Test
    fun `Test depth calculation`() {
        // A
        // |
        // B
        // |\
        // C |
        // |/
        // D

        val specieA = Specie.Builder("Test specie A")
        val specieB = Specie.Builder("Test specie B")
        val specieC = Specie.Builder("Test specie C")
        val specieD = Specie.Builder("Test specie D")

        specieA.addChild(specieB)
        specieB.addChild(specieC)
        specieB.addChild(specieD)
        specieC.addChild(specieD)

        assertEquals(0, specieA.getResult().getDepth())
        assertEquals(1, specieB.getResult().getDepth())
        assertEquals(2, specieC.getResult().getDepth())
        assertEquals(3, specieD.getResult().getDepth())
    }
}
