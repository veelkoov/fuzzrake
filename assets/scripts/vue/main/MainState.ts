export default class MainState {
    private _query: string = '';
    private _activeFiltersCount: number = 0;

    get query(): string {
        return this._query;
    }

    set query(value: string) {
        this._query = value;
    }

    get activeFiltersCount(): number {
        return this._activeFiltersCount;
    }

    set activeFiltersCount(value: number) {
        this._activeFiltersCount = value;
    }
}
