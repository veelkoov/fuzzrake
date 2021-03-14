export default class Specie {
    public readonly children: Set<Specie> = new Set<Specie>(); // FIXME: Should be read-only
    public readonly parents: Set<Specie> = new Set<Specie>(); // FIXME: Should be read-only

    public constructor(public readonly name) {
    }

    public getAncestors(): Set<Specie> {
        return this.getTree(s => s.parents);
    }

    public getDescendants(): Set<Specie> {
        return this.getTree(s => s.children);
    }

    public getTree(retrievalFunc: (Specie) => Set<Specie>): Set<Specie> {
        let result = new Set<Specie>(retrievalFunc(this));

        for (let parentOrChild of retrievalFunc(this)) {
            this.getTreeRecursionSafely(retrievalFunc, parentOrChild, result);
        }

        return result;
    }

    private getTreeRecursionSafely(retrievalFunc: (Specie) => Set<Specie>, specie: Specie, result: Set<Specie>): void {
        for (let parentOrChild of retrievalFunc(specie)) {
            if (parentOrChild === this) {
                throw new Error(`Recursion in specie: {this.name}`);
            }

            if (!(result.has(parentOrChild))) {
                result.add(parentOrChild);
                this.getTreeRecursionSafely(retrievalFunc, parentOrChild, result);
            }
        }
    }
}
