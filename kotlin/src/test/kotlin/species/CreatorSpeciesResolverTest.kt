package species

import org.junit.jupiter.api.DynamicTest.dynamicTest
import org.junit.jupiter.api.TestFactory
import kotlin.test.Test
import kotlin.test.assertContains
import kotlin.test.assertEquals
import kotlin.test.assertFalse

class CreatorSpeciesResolverTest {
    // A test case
    data class TC<T>(
        val does: Collection<String>,
        val doesnt: Collection<String>,
        val expected: T,
    )

    /**
     * - Most species
     *   - Hidden
     * - Other
     */
    private fun getBasicSpecies(): Species {
        val species = Species.Builder()
        val mostSpecies = species.getByNameCreatingMissing("Most species")
        val hidden = species.getByNameCreatingMissing("Hidden", true)
        mostSpecies.addChild(hidden)
        val other = species.getByNameCreatingMissing("Other")

        species.addRootSpecie(mostSpecies)
        species.addRootSpecie(other)

        return species.getResult()
    }

    @Test
    fun `Empty both does and doesnt returns empty set`() {
        val subject = CreatorSpeciesResolver(getBasicSpecies())

        val result = subject.resolveDoes(setOf(), setOf())
        assertEquals(0, result.size)
    }

    @Test
    fun `Empty does and unknown doesn't returns 'Most species' only`() {
        val subject = CreatorSpeciesResolver(getBasicSpecies())

        val result = subject.resolveDoes(setOf(), setOf("Some unusual specie"))
        assertEquals(setOf("Most species"), result)
    }

    @Test
    fun `Hidden species are not returned`() {
        val subject = CreatorSpeciesResolver(getBasicSpecies())

        val result = subject.resolveDoes(setOf("Most species"), setOf())

        assertContains(result, "Most species")
        assertFalse(result.contains("Hidden"))
    }

    @Test
    fun `Other species are simplified`() {
        val subject = CreatorSpeciesResolver(getBasicSpecies())

        val result = subject.resolveDoes(setOf("Some weird specie"), setOf())

        assertContains(result, "Other")
        assertFalse(result.contains("Some weird specie"))
    }

    /**
     * - Most species
     *   - A
     *     - B
     *       - C
     *         - D
     * - Other
     */
    private fun getGetOrderedDoesDoesntSpecies(): Species {
        val species = Species.Builder()
        val mostSpecies = species.getByNameCreatingMissing("Most species")
        val a = species.getByNameCreatingMissing("A")
        val b = species.getByNameCreatingMissing("B")
        val c = species.getByNameCreatingMissing("C")
        val d = species.getByNameCreatingMissing("D")
        mostSpecies.addChild(a)
        a.addChild(b)
        b.addChild(c)
        c.addChild(d)
        val other = species.getByNameCreatingMissing("Other")

        species.addRootSpecie(mostSpecies)
        species.addRootSpecie(other)

        return species.getResult()
    }

    @TestFactory
    fun getOrderedDoesDoesnt() = listOf(
        TC(listOf("A", "C"), listOf("B", "D"), "+A -B +C -D"),
        TC(listOf("C", "A"), listOf("D", "B"), "+A -B +C -D"),
        TC(listOf("B", "D"), listOf("A", "C"), "-A +B -C +D"),
        TC(listOf("D", "B"), listOf("C", "A"), "-A +B -C +D"),
    ).map { case ->
        dynamicTest(case.toString()) {
            val subject = CreatorSpeciesResolver(getGetOrderedDoesDoesntSpecies())

            val result = subject.getOrderedDoesDoesnt(case.does, case.doesnt)
            val strResult = result.map { (specie, does) -> (if (does) "+" else "-") + specie.name }.joinToString(" ")

            assertEquals(case.expected, strResult)
        }
    }

    /**
     * - Most species
     *   - Mammals
     *     - Canines
     *       - Dogs
     *         - Corgis
     *         - Dalmatians
     *       - Wolves
     *     - Deers
     *   - With antlers
     *     - Deers
     * - Other
     */
    private fun getResolveDoesSpecies(): Species {
        val species = Species.Builder()
        val mostSpecies = species.getByNameCreatingMissing("Most species")
        val mammals = species.getByNameCreatingMissing("Mammals")
        val withAntlers = species.getByNameCreatingMissing("With antlers")
        val canines = species.getByNameCreatingMissing("Canines")
        val dogs = species.getByNameCreatingMissing("Dogs")
        val corgis = species.getByNameCreatingMissing("Corgis")
        val dalmatians = species.getByNameCreatingMissing("Dalmatians")
        val wolves = species.getByNameCreatingMissing("Wolves")
        val deers = species.getByNameCreatingMissing("Deers")
        mostSpecies.addChild(mammals)
        mostSpecies.addChild(withAntlers)
        mammals.addChild(canines)
        canines.addChild(dogs)
        dogs.addChild(corgis)
        dogs.addChild(dalmatians)
        canines.addChild(wolves)
        mammals.addChild(deers)
        withAntlers.addChild(deers)
        val other = species.getByNameCreatingMissing("Other")

        species.addRootSpecie(mostSpecies)
        species.addRootSpecie(other)

        return species.getResult()
    }

    @TestFactory
    fun resolveDoes() = listOf(
        // @formatter:off
        TC(setOf(),                       setOf(),                          setOf()),
        TC(setOf("Mammals", "Corgis"),    setOf("Canines", "With antlers"), setOf("Mammals", "Corgis")),
        TC(setOf("Mammals"),              setOf("With antlers", "Dogs"),    setOf("Mammals", "Canines", "Wolves")),
        TC(setOf("Mammals", "Deers"),     setOf("With antlers", "Dogs"),    setOf("Mammals", "Canines", "Wolves", "Deers")),
        TC(setOf("Dogs", "With antlers"), setOf(""),                        setOf("With antlers", "Deers", "Dogs", "Corgis", "Dalmatians")),
        TC(setOf("Dogs", "With antlers"), setOf("Deers"),                   setOf("With antlers", "Dogs", "Corgis", "Dalmatians")),
        TC(setOf("Dogs", "Pancakes"),     setOf(""),                        setOf("Other", "Dogs", "Corgis", "Dalmatians")),
        TC(setOf("Dogs", "Other"),        setOf("Dalmatians"),              setOf("Other", "Dogs", "Corgis")),
        // @formatter:on
    ).map { case ->
        dynamicTest(case.toString()) {
            val subject = CreatorSpeciesResolver(getResolveDoesSpecies())

            val result = subject.resolveDoes(case.does, case.doesnt)

            assertEquals(case.expected, result)
        }
    }
}