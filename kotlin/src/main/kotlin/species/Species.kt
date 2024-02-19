package species

class Species(
    private val byName: Map<String, Specie>,
    private val asTree: List<Specie>,
) {
    fun getByName(name: String): Specie {
        return byName[name] ?: throw SpecieException("No specie named '$name'")
    }

    fun getNames() = byName.keys.toSet()
    fun hasName(name: String) = byName.containsKey(name)

    fun getVisibleNames() = byName.filterValues { !it.getHidden() }.keys.toSet()

    fun getAsTree() = asTree.toList()
    fun getFlat() = byName.values

    class Builder {
        private val byName: MutableMap<String, Specie.Builder> = mutableMapOf()
        private val asTree: MutableList<Specie.Builder> = mutableListOf()

        fun getByNameCreatingMissing(name: String, hidden: Boolean = false): Specie.Builder {
            return byName.computeIfAbsent(name) {
                Specie.Builder(name, hidden)
            }
        }

        fun addRootSpecie(rootSpecie: Specie.Builder) {
            asTree.add(rootSpecie)
        }

        fun getResult(): Species {
            return Species(
                byName.mapValues { it.value.getResult() },
                asTree.map { it.getResult() }
            )
        }
    }
}
