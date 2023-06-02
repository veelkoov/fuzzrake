package tracking.matchers

import com.fasterxml.jackson.databind.ObjectMapper
import com.fasterxml.jackson.dataformat.yaml.YAMLFactory
import com.fasterxml.jackson.module.kotlin.KotlinModule
import tracking.matchers.placeholders.Resolver
import tracking.matchers.placeholders.ResolverFactory

object Factory {
    private val regexes: YamlRegexes
    private val resolver: Resolver

    init {
        val mapper = ObjectMapper(YAMLFactory())
        mapper.registerModule(KotlinModule.Builder().build())

        regexes = mapper.readValue(javaClass.getResource("/tracking/regexes.yaml"), YamlRegexes::class.java)

        resolver = ResolverFactory().create(regexes.placeholders)
    }

    fun getCleaners(): Replacements {
        val options = setOf(RegexOption.MULTILINE) // TODO: DOT_MATCHES_ALL?

        val replacements = regexes.cleaners.map { (pattern, replacement) ->
            Replacement(pattern, options, replacement)
        }

        return Replacements(replacements)
    }

    fun getFalsePositives(): Replacements {
        val options = setOf(RegexOption.MULTILINE) // TODO: DOT_MATCHES_ALL?

        return Replacements(resolver.resolveIn(regexes.falsePositives).map { pattern ->
            Replacement(pattern, options, "")
        })
    }

    fun getOffersStatuses(): List<Match> {
        val options = setOf(RegexOption.DOT_MATCHES_ALL) // TODO: Other?

        val result = regexes.offersStatuses.map { pattern ->
            Match(pattern, options)
        }

        return result
    }
}
