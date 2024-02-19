package tracking.patterns.placeholders

class ResolverFactory {
    private val validPlaceholder = Regex("^ ?[A-Z_&-]+ ?$")

    fun create(tree: PhTree): Resolver {
        if (tree.isLeaf()) {
            throw IllegalArgumentException("Root placeholders item must be a map")
        }

        val list = flatten(tree)

        validateList(list)

        var placeholders = list.placeholders.toMap()
        var result: Resolver
        var changed: Boolean

        do {
            result = Resolver(placeholders)
            changed = false

            placeholders = placeholders.mapValues {
                val oldValue = it.value
                val newValue = result.resolve(oldValue)

                if (newValue != oldValue) {
                    changed = true

                    newValue
                } else {
                    oldValue
                }
            }
        } while (changed)

        return result
    }

    private fun validateList(flattened: PhList) {
        assureUnique(flattened.groups, "Group names")
        assureUnique(flattened.placeholders.map { it.first }, "Placeholders")

        flattened.placeholders.map { (placeholder, _) -> placeholder }.filterNot { validPlaceholder.matches(it) }
            .let { list ->
                if (list.isNotEmpty()) {
                    val itemsCommaSeparated = list.joinToString(", ") { "'$list'" }

                    throw IllegalArgumentException("Invalid placeholders: $itemsCommaSeparated")
                }
            }
    }

    private fun assureUnique(items: List<String>, itemsDesc: String) {
        items.groupBy { it }.filterNot { (_, list) -> 1 == list.size }.let { list ->
            if (list.isNotEmpty()) {
                val itemsCommaSeparated = list.keys.joinToString(", ") { "'$it'" }

                throw IllegalArgumentException("$itemsDesc duplicated: $itemsCommaSeparated")
            }
        }
    }

    private fun alternative(items: List<String>, groupName: String): String {
        val groupNamePart = if ("" == groupName) "" else "?<$groupName>"

        return "($groupNamePart${items.joinToString("|")})"
    }

    private fun flatten(tree: PhTree): PhList {
        val resultP = mutableListOf<Pair<String, String>>()
        val resultG = mutableListOf<String>()

        tree.getMap().entries.forEach { (placeholderWithOptionalGroup, contents) ->
            val parts = placeholderWithOptionalGroup.split('=', limit = 2)
            val placeholder = parts[0]
            val subItemNamedGroup = parts.getOrElse(1) { "" }

            if ("" != subItemNamedGroup) {
                resultG.add(subItemNamedGroup)
            }

            if (contents.isLeaf()) {
                resultP.add(placeholder to alternative(contents.getList(), subItemNamedGroup))
            } else {
                val subResult = flatten(contents)

                val childPlaceholders = subResult.placeholders.map { it.second }
                resultP.add(placeholder to alternative(childPlaceholders, subItemNamedGroup))

                resultP.addAll(subResult.placeholders)
                resultG.addAll(subResult.groups)
            }
        }

        return PhList(resultP, resultG)
    }
}
