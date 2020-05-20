import AbstractBaseFilterVis from "./AbstractBaseFilterVis";
import SpeciesFilter from "../data/SpeciesFilter";

export default class SpeciesFilterVis<T> extends AbstractBaseFilterVis {
    public constructor(idPart: string, fieldNameIn: string, fieldNameOut: string) {
        super(idPart, new SpeciesFilter<T>(fieldNameIn, fieldNameOut));
    }
}
