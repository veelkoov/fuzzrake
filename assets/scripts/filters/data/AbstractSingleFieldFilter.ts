import AbstractBaseFilter from "./AbstractBaseFilter";

export default abstract class AbstractSingleFieldFilter<T> extends AbstractBaseFilter<T> {
    protected readonly fieldName: string;

    protected constructor(fieldName: string) {
        super();
        this.fieldName = fieldName;
    }

    public getStorageName(): string {
        return this.fieldName;
    }
}
