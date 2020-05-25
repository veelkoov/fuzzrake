import Specie from "./Specie";

export default class Species {
    public readonly flat: { [specieName: string]: Specie } = {};
    public readonly tree: Array<Specie>;

    public constructor(species: object) {
        this.tree = this.getTreeFor(species, null);
    }

    private getTreeFor(species: object, parent: Specie) {
        let result = Array<Specie>();

        for (let specieName in species) {
            result.push(this.getUpdatedSpecie(specieName, parent, species[specieName]));
        }

        return result;
    }

    private getUpdatedSpecie(specieName: string, parent: Specie, subspecies: object) {
        let specie = this.getSpecie(specieName);

        if (parent !== null) {
            specie.parents.add(parent);
        }

        for (let child of this.getTreeFor(subspecies, specie)) {
            specie.children.add(child);
        }

        return specie;
    }

    private getSpecie(specieName: string) {
        if (!(specieName in this.flat)) {
            this.flat[specieName] = new Specie(specieName);
        }

        return this.flat[specieName];
    }
}
