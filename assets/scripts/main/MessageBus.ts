import {DataRow} from './DataManager';
import Artisan from '../class/Artisan';

export type QueryUpdateCallback = (newQuery: string, newActiveFiltersCount: number) => void;
export type DataChangeCallback = (newData: DataRow[]) => void;
export type SubjectArtisanChangeCallback = (newSubject: Artisan) => void;

export default class MessageBus {
    private queryUpdateListeners: QueryUpdateCallback[] = [];
    private dataChangeListeners: DataChangeCallback[] = [];
    private subjectArtisanChangeListeners: SubjectArtisanChangeCallback[] = [];

    public listenQueryUpdates(listener: QueryUpdateCallback): void {
        this.queryUpdateListeners.push(listener);
    }

    public notifyQueryUpdate(newQuery: string, newActiveFiltersCount: number): void {
        this.queryUpdateListeners.forEach(callback => callback(newQuery, newActiveFiltersCount));
    }

    public listenDataChanges(listener: DataChangeCallback): void {
        this.dataChangeListeners.push(listener);
    }

    public notifyDataChange(newData: DataRow[]): void {
        this.dataChangeListeners.forEach(callback => callback(newData));
    }

    public listenSubjectArtisanChanges(listener: SubjectArtisanChangeCallback) {
        this.subjectArtisanChangeListeners.push(listener);
    }

    public notifySubjectArtisanChange(newSubjectArtisan: Artisan): void {
        this.subjectArtisanChangeListeners.forEach(callback => callback(newSubjectArtisan));
    }
}

const messageBus = new MessageBus();

export function getMessageBus(): MessageBus {
    return messageBus;
}
