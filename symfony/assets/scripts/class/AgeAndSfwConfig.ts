import Storage from "./Storage";

export default class AgeAndSfwConfig {
  public static readonly FILLED = "aasc/filled";
  public static readonly IS_ADULT = "aasc/isAdult";
  public static readonly WANTS_SFW = "aasc/wantsSfw";
  public static readonly CREATOR_MODE = "aasc/creatorMode";

  private static instance: AgeAndSfwConfig | null = null;

  private _isFilled: boolean;
  private _isAdult: boolean;
  private _wantsSfw: boolean;
  private _creatorMode: boolean;

  private constructor() {
    this._isFilled = Storage.getBoolean(AgeAndSfwConfig.FILLED, false);
    this._isAdult = Storage.getBoolean(AgeAndSfwConfig.IS_ADULT, false);
    this._wantsSfw = Storage.getBoolean(AgeAndSfwConfig.WANTS_SFW, true);
    this._creatorMode = Storage.getBoolean(AgeAndSfwConfig.CREATOR_MODE, false);
  }

  public static getInstance(): AgeAndSfwConfig {
    if (null === this.instance) {
      this.instance = new AgeAndSfwConfig();
    }

    return this.instance;
  }

  public getIsFilled(): boolean {
    return this._isFilled;
  }

  public setIsFilled(value: boolean): void {
    this._isFilled = value;
  }

  public getIsAdult(): boolean {
    return this._isAdult;
  }

  public setIsAdult(value: boolean): void {
    this._isAdult = value;
  }

  public getWantsSfw(): boolean {
    return this._wantsSfw;
  }

  public setWantsSfw(value: boolean): void {
    this._wantsSfw = value;
  }

  public getCreatorMode(): boolean {
    return this._creatorMode;
  }

  public setCreatorMode(value: boolean): void {
    this._creatorMode = value;
  }

  public save(): void {
    Storage.saveBoolean(AgeAndSfwConfig.FILLED, this._isFilled, 3600);
    Storage.saveBoolean(AgeAndSfwConfig.IS_ADULT, this._isAdult, 3600);
    Storage.saveBoolean(AgeAndSfwConfig.WANTS_SFW, this._wantsSfw, 3600);
    Storage.saveBoolean(AgeAndSfwConfig.CREATOR_MODE, this._creatorMode, 3600);
  }
}
