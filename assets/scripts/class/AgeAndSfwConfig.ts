import Storage from "./Storage";

export default class AgeAndSfwConfig {
    private static instance: AgeAndSfwConfig = null;

    private _isFilled: boolean;
    private _isAdult: boolean;
    private _wantsSfw: boolean;
    private _makerMode: boolean;

    private constructor() {
        this._isFilled = Storage.getBoolean(Storage.FILLED, false);
        this._isAdult = Storage.getBoolean(Storage.IS_ADULT, false);
        this._wantsSfw = Storage.getBoolean(Storage.WANTS_SFW, true);
        this._makerMode = Storage.getBoolean(Storage.MAKER_MODE, false);
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

    public getMakerMode(): boolean {
        return this._makerMode;
    }

    public setMakerMode(value: boolean): void {
        this._makerMode = value;
    }

    public save(): void {
        Storage.saveBoolean(Storage.FILLED, this._isFilled, 3600);
        Storage.saveBoolean(Storage.IS_ADULT, this._isAdult, 3600)
        Storage.saveBoolean(Storage.WANTS_SFW, this._wantsSfw, 3600);
        Storage.saveBoolean(Storage.MAKER_MODE, this._makerMode, 3600);
    }
}
