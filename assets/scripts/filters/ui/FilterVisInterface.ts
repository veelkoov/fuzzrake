import FilterInterface from '../data/FilterInterface';

export default interface FilterVisInterface {
    isActive(): boolean;

    restoreChoices(): void;

    saveChoices(): void;

    getFilterId(): string;

    getFilter(): FilterInterface;
}
