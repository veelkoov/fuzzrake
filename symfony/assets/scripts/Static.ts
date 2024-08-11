declare const DATA_BRIDGE: { [key: string]: string };

export type SpecialItem = { 'value': string, 'label': string, 'count': number, 'faIcon': string };
export type SpecialItems = Array<SpecialItem>;
export type Item = { 'value': string, 'label': string, 'count': number, 'subitems': Items };
export type Items = Array<Item>;

export type FilterOptions = { 'items': Items, 'specialItems': SpecialItems };

export default abstract class Static {
    public static getApiUrl(path: string): string {
        return DATA_BRIDGE.apiBaseUrl + path;
    }

    public static getGoogleRecaptchaSiteKey(): string {
        return DATA_BRIDGE.googleRecaptchaSiteKey;
    }
}
