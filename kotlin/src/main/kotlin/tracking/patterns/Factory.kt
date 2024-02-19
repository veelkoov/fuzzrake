package tracking.patterns

import data.Resource
import data.Yaml
import tracking.patterns.placeholders.Resolver
import tracking.patterns.placeholders.ResolverFactory

object Factory {
    private val regexes: YamlRegexes =
        Yaml.parse(Resource.read("/tracking/regexes.yaml"), YamlRegexes::class.java)

    private val resolver: Resolver =
        ResolverFactory().create(regexes.placeholders)

    fun getCleaners(): Replacements {
        val options = setOf(RegexOption.DOT_MATCHES_ALL)

        val replacements = regexes.cleaners.map { (pattern, replacement) ->
            Replacement(pattern, options, replacement)
        }

        return Replacements(replacements)
    }

    fun getFalsePositives(): Replacements {
        val options = setOf(RegexOption.DOT_MATCHES_ALL, RegexOption.COMMENTS)

        val result = resolver.resolveIn(regexes.falsePositives).map { pattern ->
            Replacement(pattern, options, "")
        }

        return Replacements(result)
    }

    fun getOffersStatuses(): Matchers {
        val options = setOf(RegexOption.DOT_MATCHES_ALL, RegexOption.COMMENTS)

        val result = resolver.resolveIn(regexes.offersStatuses).map { pattern ->
            Matcher(pattern, options)
        }

        return Matchers(result)
    }
}
