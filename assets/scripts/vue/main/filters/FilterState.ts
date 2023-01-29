export default class FilterState {
    private static readonly MAX_LABELS = 3;

    private static readonly UNKNOWN = '?'; // grep-special-value-unknown
    private static readonly TRACKING_ISSUES = '!'; // grep-special-value-tracking-issues
    private static readonly OTHER = '*'; // grep-special-value-other
    private static readonly NOT_TRACKED = '-'; // grep-special-value-not-tracked

    private static readonly VALUES_OF_FRONT_LABELS = new Set<string>([
        FilterState.UNKNOWN,
        FilterState.TRACKING_ISSUES,
        FilterState.NOT_TRACKED,
    ]);

    public constructor(
        public readonly isAndRelation: boolean,
    ) {
    }

    public readonly valuesToLabels = new Map<string, string>();

    public get isActive(): boolean {
        return 0 !== this.valuesToLabels.size;
    }

    public get(value: string): boolean {
        return this.valuesToLabels.has(value);
    }

    public set(value: string, label: string, checked: boolean): void {
        if (checked) {
            this.valuesToLabels.set(value, label);
        } else {
            this.valuesToLabels.delete(value);
        }
    }

    public reset(): void {
        this.valuesToLabels.clear();
    }

    public get description(): string {
        if (!this.isActive) {
            return 'any';
        }

        const frontLabels: string[] = [];
        const normalLabels: string[] = [];

        for (const [value, label] of this.valuesToLabels) {
            if (FilterState.VALUES_OF_FRONT_LABELS.has(value)) {
                frontLabels.push(label.toLowerCase());
            } else {
                normalLabels.push(label);
            }
        }

        let parts: string[] = [];
        parts.push(frontLabels.sort().join(', '));
        parts.push(this.formatLabels(normalLabels));

        return parts.filter(value => value.length > 0).join(' or ');
    }

    public formatLabels(labels: string[]): string {
        if (0 === labels.length) {
            return '';
        }

        if (1 === labels.length) {
            return labels[0];
        }

        const relation = this.isAndRelation ? 'all of' : 'any of';

        if (labels.length > FilterState.MAX_LABELS) {
            return relation + ' ' + labels.length + ' selected';
        } else {
            return relation + ': ' + labels.map((label) => label.replace(/ \(.+?\)$/, '')).sort().join(', '); // FIXME: #171 Glossary
        }
    }
}
