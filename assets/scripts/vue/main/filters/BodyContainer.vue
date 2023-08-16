<template>
  <div :id="'filter-body-' + filter.groupName" class="collapse" data-bs-parent="#filters-body">
    <div class="text-end helphints-toggle">
      <button class="btn btn-success" type="button" data-bs-toggle="collapse" :data-bs-target="'#' + helpContainerId" aria-expanded="false" :aria-controls="helpContainerId">Help and hints</button>
    </div>

    <div class="collapse helphints-contents" :id="helpContainerId">
      <div class="card">
        <div class="card-body">
          <ul>
            <component :is="filter.helpComponentName"/>

            <li class="small">
              You can help make getfursu.it more helpful and complete. If you plan to contact a maker, who e.g. matched <em>Unknown</em> (didn't supplied some information), you can ask them to fill the missing information, e.g.:

              <em>I found you on getfursu.it, but some information is missing there. Please consider sending updates: <a :href="iuFormUrl" target="_blank">{{ iuFormUrl }}</a> .</em>

              Thank you! <i class="fas fa-heart"></i>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <component :is="filter.bodyComponentName" :filter="filter"/>
  </div>
</template>

<script lang="ts">
import CountriesFilter from './body/CountriesFilter.vue';
import CountriesHelp from './help/CountriesHelp.vue';
import FeaturesHelp from './help/FeaturesHelp.vue';
import Filter from './Filter';
import InactiveHelp from './help/InactiveHelp.vue';
import LanguagesHelp from './help/LanguagesHelp.vue';
import MultiselectFilter from './body/MultiselectFilter.vue';
import OpenForHelp from './help/OpenForHelp.vue';
import OrderTypesHelp from './help/OrderTypesHelp.vue';
import PaymentPlansHelp from './help/PaymentPlansHelp.vue';
import ProductionModelsHelp from './help/ProductionModelsHelp.vue';
import SpeciesFilter from './body/SpeciesFilter.vue';
import SpeciesHelp from './help/SpeciesHelp.vue';
import StatesHelp from './help/StatesHelp.vue';
import Static, {AnyOptions} from '../../../Static';
import StylesHelp from './help/StylesHelp.vue';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {
    CountriesFilter, MultiselectFilter, SpeciesFilter,
    CountriesHelp, FeaturesHelp, LanguagesHelp, OpenForHelp, OrderTypesHelp, PaymentPlansHelp, ProductionModelsHelp, SpeciesHelp, StatesHelp, StylesHelp, InactiveHelp,
  },
  props: {
    filter: {type: Filter, required: true},
  }
})
export default class BodyContainer extends Vue {
  private filter!: Filter<AnyOptions>;

  get helpContainerId(): string {
    return `${this.filter.groupName}Help`;
  }

  get iuFormUrl(): string {
    return Static.getIuFormRedirectUrl('')
  }
}
</script>
