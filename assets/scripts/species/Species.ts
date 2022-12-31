import Specie from './Specie';
import Static from '../Static';

export default class Species {
    private static INSTANCE: Species = null;

    public readonly list: { [specieName: string]: Specie } = {}; // FIXME: Should be read-only

    public static get(): Species {
        if (Species.INSTANCE == null) {
            Species.INSTANCE = new Species(Static.getSpecies());
        }

        return Species.INSTANCE;
    }

    public constructor(species: object) {
        this.extendListWith(species, null);
    }

    private extendListWith(species: object, parent: Specie): void {
        for (let specieName in species) {
            let specie = this.getByNameOrCreate(specieName);

            if (parent !== null) {
                specie.parents.add(parent);
            }

            this.extendListWith(species[specieName], specie);
        }
    }

    private getByNameOrCreate(specieName: string) {
        if (!(specieName in this.list)) {
            this.list[specieName] = new Specie(specieName);
        }

        return this.list[specieName];
    }
}
