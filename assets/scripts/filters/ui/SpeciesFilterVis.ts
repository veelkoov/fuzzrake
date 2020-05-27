import AbstractBaseFilterVis from "./AbstractBaseFilterVis";
import SpeciesFilter from "../data/SpeciesFilter";
import Species from "../../class/Species";

export default class SpeciesFilterVis extends AbstractBaseFilterVis {
    public constructor(idPart: string, fieldNameIn: string, fieldNameOut: string, species: Species) {
        super(idPart, new SpeciesFilter(fieldNameIn, fieldNameOut, species));
    }
}
