export default class ColumnsManager {
    public readonly columns = {
        'makerId':          'Maker ID',
        'state':            'State',
        'languages':        'Languages',
        'productionModels': 'Production models',
        'styles':           'Styles',
        'types':            'Types',
        'features':         'Features',
        'species':          'Species',
        'commissions':      'Commissions',
        'links':            'Links',
    }

    private readonly visible: Set<string> = new Set(['styles', 'commissions', 'links']);

    private static readonly STORAGE_VERSION: string = '2';

    public count(): number {
        return Object.keys(this.columns).length
    }

    public isVisible(columnName: string): boolean {
        return this.visible.has(columnName);
    }

    public toggle(columnName: string): void {
        if (this.isVisible(columnName)) {
            this.visible.delete(columnName);
        } else {
            this.visible.add(columnName);
        }
    }

    public save(): void {
        try {
            localStorage['columns/version'] = ColumnsManager.STORAGE_VERSION;
            localStorage['columns/state'] = this.toString();
        } catch (e) {
            // Not allowed? - I don't care then
        }
    }

    public load(): void {
        let state: string = localStorage['columns/state'];

        if (localStorage['columns/version'] === ColumnsManager.STORAGE_VERSION && state) {
            this.visible.clear();

            state.split(',').forEach((item) => {
                if (item in this.columns) this.visible.add(item);
            });
        }
    }

    public toString(): string {
        let result = [];

        for (let value of this.visible.values()) {
            result.push(value);
        }

        return result.join(',');
    }
}
