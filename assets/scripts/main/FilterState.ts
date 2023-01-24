export default class FilterState {
    public values: Set<string> = new Set<string>();
    public labels: Set<string> = new Set<string>();

    public get isActive(): boolean {
        return 0 !== this.values.size;
    }

    public get description(): string {
        return this.isActive ? Array.from(this.labels.values()).join(', ') : 'any'; // TODO
    }

    public get(value: string): boolean {
        return this.values.has(value);
    }

    public set(value: string, label: string, checked: boolean): void {
        if (checked) {
            this.values.add(value);
            this.labels.add(label);
        } else {
            this.values.delete(value);
            this.labels.delete(label);
        }
    }

    public reset(): void {
        this.values.clear();
        this.labels.clear();
    }
}
