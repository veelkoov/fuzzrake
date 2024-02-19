package species

class Specie(
    val name: String,
    private var hidden: Boolean,
) {
    private val parents: MutableSet<Specie> = mutableSetOf()
    private val children: MutableSet<Specie> = mutableSetOf()

    fun getParents() = parents.toSet()
    fun getChildren() = children.toSet()

    fun getAncestors(): Set<Specie> {
        return parents.plus(parents.map(Specie::getAncestors).flatten())
    }

    fun getDescendants(): Set<Specie> {
        return children.plus(children.map(Specie::getDescendants).flatten())
    }

    fun getSelfAndDescendants(): Set<Specie> {
        return getDescendants().plus(this)
    }

    fun getHidden() = hidden

    fun getDepth(): Int {
        return if (parents.isEmpty()) {
            0
        } else {
            parents.maxOf(Specie::getDepth) + 1
        }
    }

    class Builder(val name: String, hidden: Boolean = false) {
        private val result = Specie(name, hidden)

        private val parents: MutableSet<Builder> = mutableSetOf()
        private val children: MutableSet<Builder> = mutableSetOf()

        fun getHidden() = result.hidden

        fun getResult() = result

        fun addChild(child: Builder) {
            if (child == this) {
                throw SpecieException("Cannot add ${child.name} as a child of $name")
            }

            if (result.getAncestors().contains(child.result)) {
                throw SpecieException("Recursion when adding child ${child.name} to $name")
            }

            children.add(child)
            result.children.add(child.result)
            child.parents.add(this)
            child.result.parents.add(result)
        }
    }

    override fun toString() = name
}
