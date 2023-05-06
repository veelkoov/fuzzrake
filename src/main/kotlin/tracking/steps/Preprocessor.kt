package tracking.steps

import tracking.matchers.Factory

private const val STUDIO_NAME = "STUDIO_NAME"

class Preprocessor {
    private val cleaners = Factory.getCleaners()

    fun preprocess(input: String, creatorAliases: List<String>): String
    {
        var result = input

//        $contents = $this->applyFilters($url, $inputText); // TODO
//        $contents = $this->extractFromJson($contents); // TODO
        result = result.lowercase()
        result = cleaners.replaceIn(result)
        result = replaceCreatorAliases(result, creatorAliases)
//        $contents = self::replaceArtisanName($artisanName, $contents); // TODO
//        $contents = $this->falsePositives->prune($contents); // TODO

        return result
    }

    private fun replaceCreatorAliases(input: String, aliases: List<String>): String {
        var result = input

        aliases.forEach { alias ->
            result = result.replace(alias, STUDIO_NAME, true)

            if (alias.length > 2 && alias.endsWith("s", true)) {
                /* Thank you, English language, I am enjoying this */
                result = result.replace(alias.dropLast(1) + "'s", STUDIO_NAME, true)
            }
        }

        return result
    }
}
