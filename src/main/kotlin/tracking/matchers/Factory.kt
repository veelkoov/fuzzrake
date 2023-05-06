package tracking.matchers

import com.fasterxml.jackson.databind.ObjectMapper
import com.fasterxml.jackson.dataformat.yaml.YAMLFactory
import com.fasterxml.jackson.module.kotlin.KotlinModule
import tracking.matchers.replace.RgxReplace

object Factory {
    private val regexes: YamlRegexes

    init {
        val mapper = ObjectMapper(YAMLFactory())
        mapper.registerModule(KotlinModule.Builder().build())

        regexes = mapper.readValue(javaClass.getResource("/tracking/regexes.yaml"), YamlRegexes::class.java)
    }

    fun getCleaners(): Matchers {
        val options = setOf(RegexOption.IGNORE_CASE, RegexOption.MULTILINE)

        val replacements = regexes.cleaners.map { (pattern, replacement) ->
            RgxReplace(pattern, options, replacement)
        }

        return Matchers(replacements)
    }
}
