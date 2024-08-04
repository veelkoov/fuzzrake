import Storage from '../class/Storage';

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

    private static readonly STORAGE_VERSION: string = '3';

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
        Storage.saveString('columns/version', ColumnsManager.STORAGE_VERSION);
        Storage.saveString('columns/state', this.toString());
    }

    public load(): void {
        const state: string = Storage.getString('columns/state', '');

        if ('' !== state && ColumnsManager.STORAGE_VERSION === Storage.getString('columns/version', '')) {
            this.visible.clear();

            state.split(',').forEach((item) => {
                if (item in this.columns) this.visible.add(item);
            });
        }
    }

    public toString(): string {
        const result = [];

        for (const value of this.visible.values()) {
            result.push(value);
        }

        return result.join(',');
    }
}
