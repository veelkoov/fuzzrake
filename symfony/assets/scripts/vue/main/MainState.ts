import Search from './Search';

export default class MainState {
    public openCardForMakerId = '';
    public query = '';

    public readonly search: Search = new Search();
}
