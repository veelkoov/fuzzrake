declare const DATA_BRIDGE: { [key: string]: string };

export default abstract class Static {
  public static getApiUrl(path: string): string {
    return DATA_BRIDGE.apiBaseUrl + path;
  }

  public static getGoogleRecaptchaSiteKey(): string {
    return DATA_BRIDGE.googleRecaptchaSiteKey;
  }
}
