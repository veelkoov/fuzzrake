import {DataRow} from './DataManager';

export type QueryUpdateCallback = (newQuery: string, newActiveFiltersCount: number) => void;
export type DataChangesCallback = (newData: DataRow[]) => void;

export default class MessageBus {
    private queryUpdateListeners: QueryUpdateCallback[] = [];
    private dataChangesListeners: DataChangesCallback[] = [];

    public listenQueryUpdate(listener: QueryUpdateCallback):void {
        this.queryUpdateListeners.push(listener);
    }

    public notifyQueryUpdate(newQuery: string, newActiveFiltersCount: number): void {
        this.queryUpdateListeners.forEach(callback => callback(newQuery, newActiveFiltersCount));
    }

    public listenDataChanges(listener: DataChangesCallback):void {
        this.dataChangesListeners.push(listener);
    }

    public notifyDataChanges(newData: DataRow[]): void {
        this.dataChangesListeners.forEach(callback => callback(newData));
    }
}

const messageBus = new MessageBus();

export function getMessageBus(): MessageBus {
    return messageBus;
}
