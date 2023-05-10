package tracking.steps

import tracking.matchers.Factory

private const val CREATOR_NAME = "CREATOR_NAME"

class Preprocessor {
    private val cleaners = Factory.getCleaners()
    private val falsePositives = Factory.getFalsePositives()

    fun preprocess(input: String, creatorAliases: List<String>): String
    {
        var result = input

//        $contents = $this->applyFilters($url, $inputText); // TODO
//        $contents = $this->extractFromJson($contents); // TODO
        result = result.lowercase()
        result = cleaners.replaceIn(result)
        result = replaceCreatorAliases(result, creatorAliases)
        result = falsePositives.replaceIn(result)

        return result
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
