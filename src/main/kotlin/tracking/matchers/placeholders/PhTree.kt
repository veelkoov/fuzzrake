package tracking.matchers.placeholders

class PhTree(value: Any?) {
    private val itemsList: List<String>?
    private val itemsMap: Map<String, PhTree>?

    init {
        when (value) {
            is List<*> -> {
                itemsList = value.map {
                    if (it is String) it else throw IllegalArgumentException() // TODO: Debug message
                }

                itemsMap = null
            }

            is Map<*, *> -> {
                itemsList = null

                itemsMap = value.entries.associate {
                    val itemKey = it.key

                    if (itemKey !is String) {
                        throw IllegalArgumentException() // TODO: Debug message
                    }

                    itemKey to PhTree(it.value)
                }
            }

            else -> {
                throw IllegalArgumentException() // TODO: Debug message
            }
        }
    }

    constructor(vararg mapItems: Pair<String, Any>): this(mapItems.toMap())
    constructor(vararg listItems: String): this(listItems.toList())

    fun isLeaf() = itemsList != null

    fun getList(): List<String> {
        if (null == itemsList) {
            throw UnsupportedOperationException() // TODO: Debug message
        }

        return itemsList
    }

    fun getMap(): Map<String, PhTree>
    {
        if (null == itemsMap) {
            throw UnsupportedOperationException() // TODO: Debug message
        }

        return itemsMap
    }
}
