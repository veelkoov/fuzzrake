package tracking.processing

import tracking.contents.ProcessedItem
import tracking.matchers.Factory

private const val CREATOR_NAME = "CREATOR_NAME"

class Preprocessor {
    private val cleaners = Factory.getCleaners()
    private val falsePositives = Factory.getFalsePositives()

    fun preprocess(item: ProcessedItem)
    {
        item.contents = item.strategy.filter(item.contents)
//        $contents = $this->extractFromJson($contents); // TODO
        item.contents = item.contents.lowercase()
        item.contents = cleaners.replaceIn(item.contents)
        item.contents = replaceCreatorAliases(item.contents, item.creator.aliases)
        item.contents = falsePositives.replaceIn(item.contents)
    }

    private fun replaceCreatorAliases(input: String, aliases: List<String>): String {
        var result = input

        aliases.map(String::lowercase).forEach { alias ->
            result = result.replace(alias, CREATOR_NAME)

            if (alias.length > 2 && alias.endsWith("s")) {
                /* Thank you, English language, I am enjoying this */
                result = result.replace(alias.dropLast(1) + "'s", CREATOR_NAME, true)
            }
        }

        return result
    }
}
