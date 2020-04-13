import AbstractBaseFilterVis from "./AbstractBaseFilterVis";
import FilterInterface from "../data/FilterInterface";
import AnyOrOtherSetFilter from "../data/AnyOrOtherSetFilter";
import AnyNoOthersSetFilter from "../data/AnyNoOthersSetFilter";
import AllOrOtherSetFilter from "../data/AllOrOtherSetFilter";

export default class SetFilterVis<T> extends AbstractBaseFilterVis {
    public constructor(idPart: string, fieldName: string, isAnd: boolean, hasOther: boolean) {
        super(idPart, SetFilterVis.getFilter<T>(fieldName, isAnd, hasOther));
    }

    private static getFilter<T>(fieldName: string, isAnd: boolean, hasOther: boolean): FilterInterface {
        if (isAnd) {
            return SetFilterVis.getAllFilter(fieldName, hasOther);
        } else {
            return SetFilterVis.getAnyFilter(fieldName, hasOther);
        }
    }

    private static getAnyFilter<T>(fieldName: string, hasOther: boolean): FilterInterface {
        if (hasOther) {
            return new AnyOrOtherSetFilter<T>(fieldName);
        } else {
            return new AnyNoOthersSetFilter<T>(fieldName);
        }
    }

    private static getAllFilter<T>(fieldName: string, hasOther: boolean): FilterInterface {
        if (hasOther) {
            return new AllOrOtherSetFilter<T>(fieldName);
        } else {
            throw new Error('AllNoOthersSetFilter not implemented');
        }
    }
}
