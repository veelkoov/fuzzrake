package tracking.matchers

import com.fasterxml.jackson.databind.ObjectMapper
import com.fasterxml.jackson.dataformat.yaml.YAMLFactory
import com.fasterxml.jackson.module.kotlin.KotlinModule
import tracking.matchers.placeholders.Resolver
import tracking.matchers.placeholders.ResolverFactory
import tracking.matchers.replace.RgxReplace

object Factory {
    private val regexes: YamlRegexes
    private val resolver: Resolver

    init {
        val mapper = ObjectMapper(YAMLFactory())
        mapper.registerModule(KotlinModule.Builder().build())

        regexes = mapper.readValue(javaClass.getResource("/tracking/regexes.yaml"), YamlRegexes::class.java)

        resolver = ResolverFactory().create(regexes.placeholders)
    }

    fun getCleaners(): Matchers {
        val options = setOf(RegexOption.MULTILINE)

        val replacements = regexes.cleaners.map { (pattern, replacement) ->
            RgxReplace(pattern, options, replacement)
        }

        return Matchers(replacements)
    }

    fun getFalsePositives(): Matchers {
        val options = setOf(RegexOption.MULTILINE)

        return Matchers(resolver.resolverIn(regexes.falsePositives).map { pattern ->
            RgxReplace(pattern, options, "")
        })
    }
}
