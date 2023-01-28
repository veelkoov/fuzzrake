import Search from './Search';

export default class MainState {
    public openCardForMakerId: string = '';
    public query: string = '';
    public activeFiltersCount: number = 0;

    public search = new Search();
}
