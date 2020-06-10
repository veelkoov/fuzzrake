import Species from "./Species";
import DataBridge from "../class/DataBridge";
import Artisan from "../class/Artisan";
import Specie from "./Specie";

export default class Initialization {
    private static readonly DEFAULT_DOES_WHEN_ONLY_DOESNT = 'Most species'; // grep-assumed-does-specie-when-artisan-has-only-doesnt

    public static initWithArtisansUpdate(): (() => void)[] {
        return [
            () => {
                Species.get(); // Assuring the singleton instance got constructed
            },
            () => {
                this.setHasOtherSpeciesDoesFiltersForAllArtisans();
                this.setSpeciesDoesDoesntFiltersForAllArtisans();
            },
        ];
    }

    private static setHasOtherSpeciesDoesFiltersForAllArtisans(): void {
        for (let artisan of DataBridge.getArtisans()) {
            Initialization.setHasOtherSpeciesDoesFiltersFor(artisan);
        }
    }

    public static setHasOtherSpeciesDoesFiltersFor(artisan: Artisan): void {
        let species: { [specieName: string]: Specie } = Species.get().flat;

        for (let specie of artisan.speciesDoes) {
            if (!species.hasOwnProperty(specie)) {
                artisan.setHasOtherSpeciesDoesFilters();
                break;
            }
        }
    }

    private static setSpeciesDoesDoesntFiltersForAllArtisans(): void {
        for (let artisan of DataBridge.getArtisans()) {
            artisan.setSpeciesDoesFilters(Initialization.getExpandedSet(artisan.speciesDoes));
            artisan.setSpeciesDoesntFilters(Initialization.getExpandedSet(artisan.speciesDoesnt));

            if (artisan.getSpeciesDoesFilters().size === 0 && artisan.getSpeciesDoesntFilters().size !== 0) {
                artisan.setSpeciesDoesFilters(new Set<string>([this.DEFAULT_DOES_WHEN_ONLY_DOESNT]))
            }
        }
    }

    private static getExpandedSet(speciesNames: string[]): Set<string> {
        let result: Set<string> = new Set<string>(speciesNames);
        let species: Species = Species.get();

        for (let specieName of speciesNames) {
            if (specieName in species.flat) {
                species.flat[specieName].getDescendants().forEach((_: Specie, value: Specie, __: Set<Specie>): void => {
                    result.add(value.name);
                });
            }
        }

        return result;
    }
}
