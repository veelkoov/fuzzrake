import AbstractBaseFilterVis from "./AbstractBaseFilterVis";
import FilterInterface from "../data/FilterInterface";

export default class GenericFilterVis<T> extends AbstractBaseFilterVis {
    constructor(idPart: string, filter: FilterInterface) {
        super(idPart, filter);
    }
}
