import Specie from "./Specie";
import DataBridge from "../class/DataBridge";
import Initialization from "./Initialization";

export default class Species {
    private static INSTANCE: Species = null;

    public readonly flat: { [specieName: string]: Specie } = {}; // FIXME: Should be read-only
    public readonly tree: Array<Specie>; // FIXME: Should be read-only

    public static get(): Species {
        if (Species.INSTANCE == null) {
            Species.INSTANCE = new Species(DataBridge.getSpecies());
        }

        return Species.INSTANCE;
    }

    public static initWithArtisansUpdate(): (() => void)[] {
        return Initialization.initWithArtisansUpdate();
    }

    public constructor(species: object) {
        this.tree = this.buildTreeFrom(species, null);
    }

    private buildTreeFrom(species: object, parent: Specie) {
        let result = Array<Specie>();

        for (let specieName in species) {
            let specie = this.getUniqueSpecie(specieName);

            this.updateParentsChildren(specie, parent, species[specieName]);

            result.push(specie);
        }

        return result;
    }

    private updateParentsChildren(specie: Specie, parent: Specie, subspecies: object): void {
        if (parent !== null) {
            specie.parents.add(parent);
        }

        for (let child of this.buildTreeFrom(subspecies, specie)) {
            specie.children.add(child);
        }
    }

    private getUniqueSpecie(specieName: string) {
        if (!(specieName in this.flat)) {
            this.flat[specieName] = new Specie(specieName);
        }

        return this.flat[specieName];
    }
}
