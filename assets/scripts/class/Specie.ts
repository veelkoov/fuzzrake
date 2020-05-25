export default class Specie {
    public readonly children: Set<Specie> = new Set<Specie>();
    public readonly parents: Set<Specie> = new Set<Specie>();

    public constructor(public readonly name) {
    }
}
