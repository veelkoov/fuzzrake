import AbstractBaseFilterVis from "./AbstractBaseFilterVis";
import FilterInterface from "../data/FilterInterface";
import AnyOrOtherSetFilter from "../data/AnyOrOtherSetFilter";
import AnyNoOthersSetFilter from "../data/AnyNoOthersSetFilter";
import AllOrOtherSetFilter from "../data/AllOrOtherSetFilter";

export default class SetFilterVis extends AbstractBaseFilterVis {
    public constructor(idPart: string, fieldName: string, isAnd: boolean, hasOther: boolean) {
        super(idPart, SetFilterVis.getFilter(fieldName, isAnd, hasOther));
    }

    private static getFilter(fieldName: string, isAnd: boolean, hasOther: boolean): FilterInterface {
        if (isAnd) {
            return SetFilterVis.getAllFilter(fieldName, hasOther);
        } else {
            return SetFilterVis.getAnyFilter(fieldName, hasOther);
        }
    }

    private static getAnyFilter(fieldName: string, hasOther: boolean): FilterInterface {
        if (hasOther) {
            return new AnyOrOtherSetFilter(fieldName);
        } else {
            return new AnyNoOthersSetFilter(fieldName);
        }
    }

    private static getAllFilter(fieldName: string, hasOther: boolean): FilterInterface {
        if (hasOther) {
            return new AllOrOtherSetFilter(fieldName);
        } else {
            throw new Error('AllNoOthersSetFilter not implemented');
        }
    }
}
