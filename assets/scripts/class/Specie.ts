export default class Specie {
    public readonly children: Set<Specie> = new Set<Specie>();
    public readonly parents: Set<Specie> = new Set<Specie>();

    public constructor(public readonly name) {
    }

    public getAncestors(): Set<Specie>
    {
        let result = new Set<Specie>(this.parents);

        for (let parent of this.parents) {
            this.getAncestorsRecursionSafely(parent, result);
        }

        return result;
    }

    private getAncestorsRecursionSafely(specie: Specie, result: Set<Specie>): void
    {
        for (let parent of specie.parents) {
            if (parent === this) {
                throw new Error(`Recursion in specie: {this.name}`);
            }

            if (!(result.has(parent))) {
                result.add(parent);
                this.getAncestorsRecursionSafely(parent, result);
            }
        }
    }
}
