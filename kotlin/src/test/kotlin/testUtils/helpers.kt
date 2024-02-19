package testUtils

import species.Specie

fun specieNamesSet(species: Iterable<Specie>) = species.map { it.name }.toSet()
