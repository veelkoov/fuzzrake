import {DataRow} from './DataManager';
import Artisan from '../class/Artisan';

export type DataLoadRequestCallback = (newQuery: string, isExhaustive: boolean) => void;
export type DataChangeCallback = (newData: DataRow[]) => void;
export type SubjectArtisanChangeCallback = (newSubject: Artisan) => void;
export type SetupFinishedCallback = () => void;

export default class MessageBus {
    private dataChangeListeners: DataChangeCallback[] = [];
    private dataLoadRequestListeners: DataLoadRequestCallback[] = [];
    private subjectArtisanChangeListeners: SubjectArtisanChangeCallback[] = []; // TODO: Move to MainState
    private setupFinishedListeners: SetupFinishedCallback[] = [];

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

    public requestDataLoad(newQuery: string, isExhaustive: boolean): void {
        this.dataLoadRequestListeners.forEach(callback => callback(newQuery, isExhaustive));
    }

    public listenDataLoadRequests(listener: DataLoadRequestCallback): void {
        this.dataLoadRequestListeners.push(listener);
    }

    public notifySetupFinished(): void {
        this.setupFinishedListeners.forEach(callback => callback());
    }

    public listenSetupFinished(listener: SetupFinishedCallback): void {
        this.setupFinishedListeners.push(listener);
    }
}

const messageBus = new MessageBus();

export function getMessageBus(): MessageBus {
    return messageBus;
}
