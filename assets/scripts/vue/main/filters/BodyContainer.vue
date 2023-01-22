<template>
  <div :id="'filter-body-' + groupName" class="collapse" data-bs-parent="#filters-body">
    <div class="text-end helphints-toggle">
      <button class="btn btn-success" type="button" data-bs-toggle="collapse" :data-bs-target="'#' + helpContainerId" aria-expanded="false" :aria-controls="helpContainerId">Help and hints</button>
    </div>

    <div class="collapse helphints-contents" :id="helpContainerId">
      <div class="card">
        <div class="card-body">
          <ul>
            <component :is="helpComponent"/>

            <li class="small">
              You can help make getfursu.it more helpful and complete. If you plan to contact a maker, who e.g. matched <em>Unknown</em> (didn't supplied some information), you can ask them to fill the missing information, e.g.:

              <em>I found you on getfursu.it, but some information is missing there. Please consider sending updates: <a :href="iuFormUrl" target="_blank">{{ iuFormUrl }}</a> .</em><!-- FIXME: Full URL -->

              Thank you! <i class="fas fa-heart"></i>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <component :is="filterComponent" :group-name="groupName" :filter-data="filterData"/>
  </div>
</template>

<script lang="ts">
import CountriesFilter from './body/CountriesFilter.vue';
import CountriesHelp from './help/CountriesHelp.vue';
import FeaturesHelp from './help/FeaturesHelp.vue';
import LanguagesHelp from './help/LanguagesHelp.vue';
import MultiselectFilter from './body/MultiselectFilter.vue';
import OpenForHelp from './help/OpenForHelp.vue';
import OrderTypesHelp from './help/OrderTypesHelp.vue';
import PaymentPlansHelp from './help/PaymentPlansHelp.vue';
import ProductionModelsHelp from './help/ProductionModelsHelp.vue';
import SpeciesHelp from './help/SpeciesHelp.vue';
import StatesHelp from './help/StatesHelp.vue';
import Static, {MultiselectFilterData} from '../../../Static';
import StylesHelp from './help/StylesHelp.vue';
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';

@Options({
  components: {
    CountriesFilter, MultiselectFilter,
    CountriesHelp, FeaturesHelp, LanguagesHelp, OpenForHelp, OrderTypesHelp, PaymentPlansHelp, ProductionModelsHelp, SpeciesHelp, StatesHelp, StylesHelp,
  },
  props: {
    groupName: {type: String, required: true},
    filterComponent: {type: String, required: true},
    helpComponent: {type: String, required: true},
    filterData: {type: Object as PropType<MultiselectFilterData>, required: false},
  }
})
export default class BodyContainer extends Vue {
  private groupName!: string;

  get helpContainerId(): string {
    return `${this.groupName}Help`;
  }

  get iuFormUrl(): string {
    return Static.getIuFormRedirectUrl('')
  }
}
</script>
