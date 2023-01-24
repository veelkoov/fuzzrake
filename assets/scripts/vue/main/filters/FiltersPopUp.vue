<template>
  <div class="modal fade" id="filtersModal" tabindex="-1" aria-labelledby="filtersTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content" id="filters-top">
        <div class="modal-header">
          <h5 class="modal-title" id="filtersTitle">
            Filters
          </h5>
          <span>
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Apply</button>
          </span>
        </div>
        <div class="modal-body" id="filters-body">
          <div class="row">
            <div class="col">
              <template v-for="filter in filters">
                <CtrlButton :filter="filter"/> <wbr>
              </template>
            </div>
          </div>

          <form id="filters">
            <BodyContainer v-for="filter in filters" :filter="filter"/>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import BodyContainer from './BodyContainer.vue';
import CtrlButton from './CtrlButton.vue';
import Filter from '../../../main/Filter';
import Static, {AnyOptions} from '../../../Static';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {BodyContainer, CtrlButton},
})
export default class FiltersPopUp extends Vue {
  private filters: Array<Filter<AnyOptions>>;

  public created(): void {
    this.filters = [
      new Filter('countries', 'Countries', 'CountriesFilter',
          'CountriesHelp', Static.getFiltersOptions().countries),
      new Filter('states', 'States', 'MultiselectFilter',
          'StatesHelp', Static.getFiltersOptions().states),
      new Filter('languages', 'Languages', 'MultiselectFilter',
          'LanguagesHelp', Static.getFiltersOptions().languages),
      new Filter('styles', 'Styles', 'MultiselectFilter',
          'StylesHelp', Static.getFiltersOptions().styles),
      new Filter('features', 'Features', 'MultiselectFilter',
          'FeaturesHelp', Static.getFiltersOptions().features),
      new Filter('orderTypes', 'Order types', 'MultiselectFilter',
          'OrderTypesHelp', Static.getFiltersOptions().orderTypes),
      new Filter('productionModels', 'Production models', 'MultiselectFilter',
          'ProductionModelsHelp', Static.getFiltersOptions().productionModels),
      new Filter('openFor', 'Open for', 'MultiselectFilter',
          'OpenForHelp', Static.getFiltersOptions().commissionStatuses),
      new Filter('species', 'Species', 'SpeciesFilter',
          'SpeciesHelp', Static.getFiltersOptions().species),
      new Filter('paymentPlans', 'Payment plans', 'MultiselectFilter',
          'PaymentPlansHelp', Static.getFiltersOptions().paymentPlans),
    ];
  }
}
</script>
