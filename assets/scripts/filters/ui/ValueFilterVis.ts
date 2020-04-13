import AbstractBaseFilterVis from "./AbstractBaseFilterVis";
import ValueFilter from "../data/ValueFilter";

export default class ValueFilterVis<T> extends AbstractBaseFilterVis {
    public constructor(idPart: string, fieldName: string) {
        super(idPart, new ValueFilter<T>(fieldName));
    }
}
