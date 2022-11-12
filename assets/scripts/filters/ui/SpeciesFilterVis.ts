import AbstractBaseFilterVis from './AbstractBaseFilterVis';
import AnySetUnOtFilter from '../data/AnySetUnOtFilter';

export default class SpeciesFilterVis extends AbstractBaseFilterVis {
    private readonly markersByDescendantSpecie: { [specieName: string]: JQuery<HTMLSpanElement> };
    private readonly markers: JQuery<HTMLSpanElement>;

    public constructor(idPart: string, fieldName: string) {
        super(idPart, new AnySetUnOtFilter(fieldName));

        this.markersByDescendantSpecie = this.getMarkersByDescendantSpecies();
        this.markers = this.grabMarkers();

        this.$checkboxes.on('change', (event) => {
            this.refreshCheckedAttributes(event.target);
            this.refreshDescendantsMarkers();
        });
    }

    protected refreshUi(): void {
        super.refreshUi();

        this.refreshDescendantsMarkers();
    }

    private refreshCheckedAttributes(changed: HTMLInputElement): void {
        this.$checkboxes.filter(`[value="${changed.value}"]`).each((index, element) => {
            if (element.checked !== changed.checked) {
                element.checked = changed.checked;
            }
        });
    }

    private refreshDescendantsMarkers(): void {
        if (this.markers) {
            this.markers.hide();

            this.$checkboxes.filter(':checked').each((index, element) => {
                this.markersByDescendantSpecie[element.value].show();
            });
        }
    }

    private grabMarkers(): JQuery<HTMLSpanElement> {
        return jQuery(`${this.bodySelector} span.descendants-indicator`);
    }

    private getMarkersByDescendantSpecies(): { [specieName: string]: JQuery<HTMLSpanElement> } {
        let result = {};

        let markersBySpecie = this.getMarkersBySpecie();
        let checkboxesValues = new Set<string>(this.$checkboxes.map(function (): string {
            return <string>$(this).val();
        }).get());

        for (let specieName of checkboxesValues) {
            result[specieName] = this.getMarkersForDescentantSpecie(specieName, markersBySpecie);
        }

        return result;
    }

    private getMarkersForDescentantSpecie(specieName: string, markersBySpecie: { [specieName: string]: JQuery<HTMLSpanElement> }): JQuery<HTMLSpanElement> {
        let result = jQuery<HTMLSpanElement>();

        // if (this.species.flat.hasOwnProperty(specieName)) {
        //     for (let ancestor of this.species.flat[specieName].getAncestors()) {
        //         result = result.add(markersBySpecie[ancestor.name].toArray());
        //     }
        // }

        return result;
    }

    private getMarkersBySpecie(): { [specieName: string]: JQuery<HTMLSpanElement> } {
        let result = {};

        // for (let specie in this.species.flat) {
        //     result[specie] = this.$checkboxes.filter(`[value="${specie}"]`).siblings('label').find('span.descendants-indicator');
        // }

        return result;
    }
}
