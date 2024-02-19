package species

class CreatorSpeciesResolver(
    private val species: Species,
) {
    private val selfAndDescendantsCache = mutableMapOf<String, Set<String>>()
    private val mostSpecies: Specie = species.getByName("Most species") // grep-assumed-does-specie-when-artisan-has-only-doesnt
    private val other: Specie = species.getByName("Other") // grep-species-other

    fun resolveDoes(speciesDoes: Collection<String>, speciesDoesnt: Collection<String>): Set<String> {
        val assumedSpeciesDoes = if (speciesDoes.isEmpty() && speciesDoesnt.isNotEmpty()) {
            setOf(mostSpecies.name)
        } else {
            speciesDoes
        }

        val ordered = getOrderedDoesDoesnt(assumedSpeciesDoes, speciesDoesnt)

        val result = mutableSetOf<String>()

        ordered.forEach { (specie, does) ->
            val descendants = getVisibleSelfAndDescendants(specie)

            if (does) {
                descendants.forEach(result::add)
            } else {
                descendants.forEach(result::remove)
            }
        }

        return result
    }

    /**
     * @return Specie => Does?
     */
    fun getOrderedDoesDoesnt(speciesDoes: Collection<String>, speciesDoesnt: Collection<String>): Map<Specie, Boolean>
    {
        val knownDoes = speciesDoes.map(::getVisibleSpecieOrParentOrOtherForUnusual).flatten().toSet()
        val knownDoesnt = speciesDoesnt.map(::getVisibleSpecieOrEmptySetForUnusual).flatten().toSet()

        var result: List<Pair<Specie, Boolean>> = listOf<Pair<Specie, Boolean>>()
            .plus(knownDoes.map { specie -> specie to true })
            .plus(knownDoesnt.map { specie -> specie to false })

        result = result.sortedWith { item1: Pair<Specie, Boolean>, item2: Pair<Specie, Boolean> ->
            val depthDiff = item1.first.getDepth() - item2.first.getDepth()

            if (0 != depthDiff) { depthDiff } else {
                if (item2.second) 1 else 0 - if (item1.second) 1 else 0
            }
        }

        return result.toMap()
    }

    private fun getVisibleSelfAndDescendants(self: Specie): Set<String>
    {
        return selfAndDescendantsCache.computeIfAbsent(self.name) {
            self.getSelfAndDescendants().map { it.name }.filter { species.getVisibleNames().contains(it) }.toSet()
        }
    }

    private fun getVisibleSpecieOrParentOrOtherForUnusual(specieName: String): Set<Specie>
    {
        if (!species.hasName(specieName)) {
            return setOf(other)
        }

        val result = mutableSetOf<Specie>()
        val unresolved = mutableSetOf(species.getByName(specieName))

        while (unresolved.isNotEmpty()) {
            val specie = unresolved.first()

            if (specie.getHidden()) {
                unresolved.addAll(specie.getParents())
            } else {
                result.add(specie)
            }

            unresolved.remove(specie)
        }

        if (result.size == 0) {
            throw SpecieException("$specieName is hidden and does not have a single visible parent")
        }

        return result
    }

    private fun getVisibleSpecieOrEmptySetForUnusual(specieName: String): Set<Specie>
    {
        if (!species.hasName(specieName) || species.getByName(specieName).getHidden()) {
            return setOf()
        }

        return setOf(species.getByName(specieName))
    }
}
