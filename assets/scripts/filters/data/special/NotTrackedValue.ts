import Artisan from '../../../class/Artisan'
import SpecialValue from './SpecialValue';;

export default class NotTrackedValue extends SpecialValue {
    public static readonly VALUE: string = '-'; // grep-special-value-not-tracked

    public constructor() {
        super(NotTrackedValue.VALUE);
    }

    public matches(artisan: Artisan): boolean {
        return this.selected && 0 === artisan.commissionsUrls.length;
    }
}
