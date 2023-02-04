import Artisan from '../../class/Artisan';
import Search from './Search';

export default class MainState {
    public openCardForMakerId: string = '';
    public query: string = '';
    public activeFiltersCount: number = 0;

    public readonly search: Search = new Search();
    public subjectArtisan: Artisan = Artisan.empty();
}
