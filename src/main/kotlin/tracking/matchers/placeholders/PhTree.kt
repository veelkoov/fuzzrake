package tracking.matchers.placeholders

class PhTree(value: Any?) {
    private val itemsList: List<String>?
    private val itemsMap: Map<String, PhTree>?

    init {
        when (value) {
            is List<*> -> {
                itemsList = value.map { item ->
                    if (item !is String) {
                        throw IllegalArgumentException("Expected List<String>, but one of the items was: $item")
                    }

                    item
                }

                itemsMap = null
            }

            is Map<*, *> -> {
                itemsList = null

                itemsMap = value.entries.associate { (itemKey, itemValue) ->
                    if (itemKey !is String) {
                        throw IllegalArgumentException("Expected Map<String, *>, but one of the keys was: $itemKey")
                    }

                    itemKey to PhTree(itemValue)
                }
            }

            else -> {
                throw IllegalArgumentException("Unable to process node: $value")
            }
        }
    }

    constructor(vararg mapItems: Pair<String, Any>): this(mapItems.toMap())
    constructor(vararg listItems: String): this(listItems.toList())

    fun isLeaf() = itemsList != null

    fun getList(): List<String> {
        if (null == itemsList) {
            throw UnsupportedOperationException("This item is not a list")
        }

        return itemsList
    }

    fun getMap(): Map<String, PhTree>
    {
        if (null == itemsMap) {
            throw UnsupportedOperationException("This item is not a map")
        }

        return itemsMap
    }
}
