declare const DATA_BRIDGE: { [key: string]: string };
declare const FILTERS_OPTIONS: FiltersOptions;
declare const TOTAL_ARTISANS_COUNT: number;
declare const fliSetLoading: (isLoading: boolean) => void;
declare const loadFuzzrakeData: () => void;

export type SpecialItem = {
  value: string;
  label: string;
  count: number;
  faIcon: string;
};
export type SpecialItems = Array<SpecialItem>;
export type Item = {
  value: string;
  label: string;
  count: number;
  subitems: Items;
};
export type Items = Array<Item>;

export type FilterOptions = { items: Items; specialItems: SpecialItems };

export type FiltersOptions = {
  orderTypes: FilterOptions;
  styles: FilterOptions;
  paymentPlans: FilterOptions;
  features: FilterOptions;
  productionModels: FilterOptions;
  openFor: FilterOptions;
  languages: FilterOptions;
  countries: FilterOptions;
  states: FilterOptions;
  species: FilterOptions;
  inactive: FilterOptions;
};

export default abstract class Static {
  public static getMainPath(): string {
    return DATA_BRIDGE.mainPath;
  }

  public static getShouldKnowPath(): string {
    return DATA_BRIDGE.shouldKnowPath;
  }

  public static getTrackingPath(): string {
    return DATA_BRIDGE.trackingPath;
  }

  public static getTrackingLimitationsPath(): string {
    return DATA_BRIDGE.trackingLimitationsPath;
  }

  public static getIuFormRedirectUrl(artisanId: string): string {
    return DATA_BRIDGE.iuFormRedirectUrl.replace("MAKER_ID", artisanId);
  }

  public static getFeedbackFormPath(artisanId: string): string {
    return DATA_BRIDGE.feedbackFormPath.replace("MAKER_ID", artisanId);
  }

  public static getArtisanEditPath(artisanId: string): string {
    return DATA_BRIDGE.artisanEditPath.replace("MAKER_ID", artisanId);
  }

  public static getTrackingFailedImgSrc(): string {
    return DATA_BRIDGE.trackingFailedImgSrc;
  }

  public static getApiUrl(path: string): string {
    return DATA_BRIDGE.apiBaseUrl + path;
  }

  public static getGoogleRecaptchaSiteKey(): string {
    return DATA_BRIDGE.googleRecaptchaSiteKey;
  }

  public static getEnvironment(): string {
    return DATA_BRIDGE.environment;
  }

  public static getTotalArtisansCount(): number {
    return TOTAL_ARTISANS_COUNT;
  }

  public static getFiltersOptions(): FiltersOptions {
    return FILTERS_OPTIONS;
  }

  public static showLoadingIndicator(): void {
    fliSetLoading(true);
  }

  public static hideLoadingIndicator(): void {
    fliSetLoading(false);
  }

  public static loadFuzzrakeData(): void {
    loadFuzzrakeData();
  }
}
