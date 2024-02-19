package species

import testUtils.specieNamesSet
import kotlin.test.*

class SpeciesLoaderTest {
    private val subject = SpeciesLoader("/species/validChoicesTest.yaml").get()

    @Test
    fun `Tree roots are as expected`() {
        assertEquals(
            setOf("Most species", "Other", "Third root"),
            specieNamesSet(subject.getAsTree()),
        )
    }

    @Test
    fun `Third root has no connections`() {
        assertEquals(0, subject.getByName("Third root").getParents().size)
        assertEquals(0, subject.getByName("Third root").getChildren().size)
    }

    @Test
    fun `Exception thrown for nonexistent specie`() {
        assertFailsWith<SpecieException> {
            subject.getByName("Nonexistent")
        }
    }

    @Test
    fun `Family properly constructed`() {
        assertEquals(
            setOf("Felines", "Panthers", "Deer", "Some deer specie"),
            specieNamesSet(subject.getByName("Mammals").getDescendants()),
        )
    }

    @Test
    fun `Hidden species are hidden`() {
        assertEquals(
            setOf("Some deer specie", "Other 1", "Other 2"),
            subject.getNames().minus(subject.getVisibleNames()),
        )
    }

    @Test
    fun `Built-in species loads`() {
        SpeciesLoader().get()
    }
}
