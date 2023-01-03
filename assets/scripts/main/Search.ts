import {makerIdRegexp} from '../consts';

export default class Search {
    private _textLc: string = '';
    private _textUc: string = '';
    private _isMakerId: boolean = false;

    set text(value: string) {
        this._textLc = value.trim().toLowerCase();
        this._textUc = this._textLc.toUpperCase();
        this._isMakerId = makerIdRegexp.test(this._textUc);
    }

    get textLc(): string {
        return this._textLc;
    }

    get textUc(): string {
        return this._textUc;
    }

    get isMakerId(): boolean {
        return this._isMakerId;
    }
}
