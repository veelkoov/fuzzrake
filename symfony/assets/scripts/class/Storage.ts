export default class Storage {
  private constructor() {
    throw new TypeError("Trying to instantiate an utility class");
  }

  public static getString(key: string, defaultValue: string): string {
    return this.get(key) ?? defaultValue;
  }

  public static saveString(
    key: string,
    value: string,
    expireSeconds: number | null = null,
  ): void {
    this.save(key, value, expireSeconds);
  }

  public static getBoolean(key: string, defaultValue: boolean): boolean {
    const result = this.get(key);

    switch (result) {
      case "1":
        return true;
      case "0":
        return false;
      default:
        return defaultValue;
    }
  }

  public static saveBoolean(
    key: string,
    value: boolean,
    expireSeconds: number | null = null,
  ): void {
    this.save(key, value ? "1" : "0", expireSeconds);
  }

  public static remove(key: string): void {
    try {
      localStorage.removeItem(this.dataPath(key));
      localStorage.removeItem(this.expiresPath(key));
    } catch (e) {
      console.log("Failed to remove data.", e);
    }
  }

  protected static get(key: string): string | null {
    try {
      if (this.removeExpired(key)) {
        return null;
      }

      return localStorage.getItem(this.dataPath(key));
    } catch (e) {
      console.log("Failed to load data.", e);

      return null;
    }
  }

  protected static save(
    key: string,
    value: string,
    expireSeconds: number | null = null,
  ): void {
    try {
      localStorage.setItem(this.dataPath(key), value.toString());

      if (null !== expireSeconds) {
        localStorage.setItem(
          this.expiresPath(key),
          (this.nowSeconds() + expireSeconds).toString(),
        );
      } else {
        localStorage.removeItem(this.expiresPath(key));
      }
    } catch (e) {
      console.log("Failed to save data.", e);
    }
  }

  private static nowSeconds(): number {
    return Math.round(Date.now() / 1000);
  }

  private static removeExpired(key: string): boolean {
    if (!this.hasExpired(key)) {
      return false;
    }

    this.remove(key);

    return true;
  }

  private static hasExpired(key: string): boolean {
    try {
      const expires = localStorage.getItem(this.expiresPath(key));

      if (null === expires || this.nowSeconds() < Number(expires)) {
        return false;
      }
    } catch (e) {
      console.log("Failed to check if expired.", e);
    }

    return true; // Safety net
  }

  private static expiresPath(key: string): string {
    return `storage/${key}/expires`;
  }

  private static dataPath(key: string): string {
    return `storage/${key}/data`;
  }
}
