import AbstractBaseFilterVis from "./AbstractBaseFilterVis";
import SpeciesFilter from "../data/SpeciesFilter";
import Species from "../../class/Species";
import DataBridge from "../../class/DataBridge";

export default class SpeciesFilterVis extends AbstractBaseFilterVis {
    private readonly markersByDescendantSpecie: { [specieName: string]: JQuery<HTMLSpanElement> };
    private readonly markers: JQuery<HTMLSpanElement>;
    private readonly species: Species;

    public constructor(idPart: string, fieldNameIn: string, fieldNameOut: string, species: Species) {
        super(idPart, new SpeciesFilter(fieldNameIn, fieldNameOut, species));

        this.species = DataBridge.getSpecies();
        this.markersByDescendantSpecie = this.grabMarkersByDescendantSpecie();
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

    private grabMarkersByDescendantSpecie(): { [specieName: string]: JQuery<HTMLSpanElement> } {
        let result = {};

        let markersBySpecie = this.grabMarkersBySpecie();

        for (let specie in this.species.flat) {
            result[specie] = jQuery<HTMLSpanElement>();

            for (let ancestor of this.species.flat[specie].getAncestors()) {
                result[specie] = result[specie].add(markersBySpecie[ancestor.name].toArray());
            }
        }

        return result;
    }

    private grabMarkersBySpecie(): { [id: string]: JQuery<HTMLSpanElement> } {
        let result = {};

        for (let specie in this.species.flat) {
            result[specie] = this.$checkboxes.filter(`[value="${specie}"]`).siblings('label').find('span.descendants-indicator');
        }

        return result;
    }
}
