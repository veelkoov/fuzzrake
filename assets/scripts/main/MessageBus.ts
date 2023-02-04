import {DataRow} from './DataManager';
import Artisan from '../class/Artisan';

export type DataLoadRequestCallback = (newQuery: string, isExhaustive: boolean) => void;
export type DataChangeCallback = (newData: DataRow[]) => void;
export type SetupFinishedCallback = () => void;

export default class MessageBus {
    private dataChangeListeners: DataChangeCallback[] = [];
    private dataLoadRequestListeners: DataLoadRequestCallback[] = [];
    private setupFinishedListeners: SetupFinishedCallback[] = [];

    public listenDataChanges(listener: DataChangeCallback): void {
        this.dataChangeListeners.push(listener);
    }

    public notifyDataChange(newData: DataRow[]): void {
        this.dataChangeListeners.forEach(callback => callback(newData));
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
