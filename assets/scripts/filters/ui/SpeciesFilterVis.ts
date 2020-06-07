import AbstractBaseFilterVis from "./AbstractBaseFilterVis";
import SpeciesFilter from "../data/SpeciesFilter";
import Species from "../../species/Species";

export default class SpeciesFilterVis extends AbstractBaseFilterVis {
    private readonly markersByDescendantSpecie: { [specieName: string]: JQuery<HTMLSpanElement> };
    private readonly markers: JQuery<HTMLSpanElement>;

    public constructor(idPart: string, fieldNameIn: string, fieldNameOut: string, private readonly species: Species) {
        super(idPart, new SpeciesFilter(fieldNameIn, fieldNameOut, species));

        this.markersByDescendantSpecie = this.getMarkersByDescendantSpecies();
        this.markers = this.grabMarkers(idPart);

        this.$checkboxes.on('change', (event) => {
            this.refreshCheckedAttributes(event.target);
            this.refreshDescentantsMarkers();
        });
    }

    protected refreshUi(): void {
        super.refreshUi();

        this.refreshDescentantsMarkers();
    }

    private refreshCheckedAttributes(changed: HTMLInputElement): void {
        this.$checkboxes.filter(`[value="${changed.value}"]`).each((index, element) => {
            if (element.checked !== changed.checked) {
                element.checked = changed.checked;
            }
        });
    }

    private refreshDescentantsMarkers(): void {
        if (this.markers) {
            this.markers.hide();

            this.$checkboxes.filter(':checked').each((index, element) => {
                this.markersByDescendantSpecie[element.value].show();
            });
        }
    }

    private grabMarkers(idPart: string): JQuery<HTMLSpanElement> {
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

        if (this.species.flat.hasOwnProperty(specieName)) {
            for (let ancestor of this.species.flat[specieName].getAncestors()) {
                result = result.add(markersBySpecie[ancestor.name].toArray());
            }
        }

        return result;
    }

    private getMarkersBySpecie(): { [specieName: string]: JQuery<HTMLSpanElement> } {
        let result = {};

        for (let specie in this.species.flat) {
            result[specie] = this.$checkboxes.filter(`[value="${specie}"]`).siblings('label').find('span.descendants-indicator');
        }

        return result;
    }
}
