<template>
  <fieldset class="species">
    <div class="row">
      <div class="col">
        <SpecialItems :filter="filter"/>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <SpeciesChoices :species="filter.options.items" :filter="filter"/>
      </div>
    </div>

    <div class="row">
      <div class="col text-end">
        <a class="btn btn-secondary" href="#filters-top">Top</a>
      </div>
    </div>
  </fieldset>
</template>

<script lang="ts">
import Filter from '../Filter';
import SpeciesChoices from './SpeciesChoices.vue';
import SpecialItems from '../SpecialItems.vue';
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';
import {SpeciesOptions} from '../../../../Static';

@Options({
  components: {SpeciesChoices, SpecialItems},
  props: {
    filter: {type: Object as PropType<Filter<SpeciesOptions>>, required: true},
  }
})
export default class SpeciesFilter extends Vue {
}
// private refreshDescendantsMarkers(): void {
//     if (this.markers) {
//         this.markers.hide();
//
//         this.$checkboxes.filter(':checked').each((index, element) => {
//             this.markersByDescendantSpecie[element.value].show();
//         });
//     }
// }
//
// private grabMarkers(): JQuery<HTMLSpanElement> {
//     return jQuery(`${this.bodySelector} span.descendants-indicator`);
// }
//
// private getMarkersByDescendantSpecies(): { [specieName: string]: JQuery<HTMLSpanElement> } {
//     let result = {};
//
//     let markersBySpecie = this.getMarkersBySpecie();
//     let checkboxesValues = new Set<string>(this.$checkboxes.map(function (): string {
//         return <string>$(this).val();
//     }).get());
//
//     for (let specieName of checkboxesValues) {
//         result[specieName] = this.getMarkersForDescentantSpecie(specieName, markersBySpecie);
//     }
//
//     return result;
// }
//
// private getMarkersForDescentantSpecie(specieName: string, markersBySpecie: { [specieName: string]: JQuery<HTMLSpanElement> }): JQuery<HTMLSpanElement> {
//     let result = jQuery<HTMLSpanElement>();
//
//     if (this.species.list.hasOwnProperty(specieName)) {
//         for (let ancestor of this.species.list[specieName].getAncestors()) {
//             result = result.add(markersBySpecie[ancestor.name].toArray());
//         }
//     }
//
//     return result;
// }
//
// private getMarkersBySpecie(): { [specieName: string]: JQuery<HTMLSpanElement> } {
//     let result = {};
//
//     for (let specie in this.species.list) {
//         result[specie] = this.$checkboxes.filter(`[value="${specie}"]`).siblings('label').find('span.descendants-indicator');
//     }
//
//     return result;
// }
</script>
