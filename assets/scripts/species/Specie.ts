export default class Specie {
    public readonly parents: Set<Specie> = new Set<Specie>(); // FIXME: Should be read-only
    private ancestors: Set<Specie> = null;

    public constructor(public readonly name) {
    }

    public getAncestors(): Set<Specie> {
        if (null === this.ancestors) {
            this.ancestors = new Set<Specie>();

            this.appendParents(this.ancestors);
        }

        return this.ancestors;
    }

    private appendParents(result: Set<Specie>): void {
        for (let parent of this.parents) {
            if (!result.has(parent)) {
                result.add(parent);
                parent.appendParents(result);
            }
        }
    }
}
