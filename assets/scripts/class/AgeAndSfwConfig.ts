export default class AgeAndSfwConfig {
    private _isAdult: boolean = false;
    private _wantsSfw: boolean = true;

    public getIsAdult(): boolean {
        return this._isAdult;
    }

    public setIsAdult(value: boolean) {
        this._isAdult = value;
    }

    public getWantsSfw(): boolean {
        return this._wantsSfw;
    }

    public setWantsSfw(value: boolean) {
        this._wantsSfw = value;
    }
}
