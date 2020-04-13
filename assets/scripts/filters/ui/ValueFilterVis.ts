import AbstractBaseFilterVis from "./AbstractBaseFilterVis";
import ValueFilter from "../data/ValueFilter";

export default class ValueFilterVis extends AbstractBaseFilterVis {
    public constructor(idPart: string, fieldName: string) {
        super(idPart, new ValueFilter(fieldName));
    }
}
