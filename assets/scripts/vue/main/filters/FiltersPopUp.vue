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
                <CtrlButton :group-name="filter.groupName" :label="filter.label" :state="filter.state"/> <wbr>
              </template>
            </div>
          </div>

          <form id="filters">
            <BodyContainer v-for="filter in filters"
                           :filter-component="filter.filterComponentName"
                           :help-component="filter.helpComponentName"
                           :group-name="filter.groupName"
                           :state="filter.state"
                           :filter-data="filter.data"
            />
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import BodyContainer from './BodyContainer.vue';
import CtrlButton from './CtrlButton.vue';
import FilterDef from '../../../main/FilterDef';
import FilterState from '../../../main/FilterState';
import Static from '../../../Static';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {BodyContainer, CtrlButton},
})
export default class FiltersPopUp extends Vue {
  private filters: Array<FilterDef>;

  public created(): void {
    this.filters = [
      new FilterDef('countries', 'Countries', 'CountriesFilter',
          'CountriesHelp', Static.getFiltersData().countries),
      new FilterDef('states', 'States', 'MultiselectFilter',
          'StatesHelp', Static.getFiltersData().states),
      new FilterDef('languages', 'Languages', 'MultiselectFilter',
          'LanguagesHelp', Static.getFiltersData().languages),
      new FilterDef('styles', 'Styles', 'MultiselectFilter',
          'StylesHelp', Static.getFiltersData().styles),
      new FilterDef('features', 'Features', 'MultiselectFilter',
          'FeaturesHelp', Static.getFiltersData().features),
      new FilterDef('orderTypes', 'Order types', 'MultiselectFilter',
          'OrderTypesHelp', Static.getFiltersData().orderTypes),
      new FilterDef('productionModels', 'Production models', 'MultiselectFilter',
          'ProductionModelsHelp', Static.getFiltersData().productionModels),
      new FilterDef('openFor', 'Open for', 'MultiselectFilter',
          'OpenForHelp', Static.getFiltersData().commissionStatuses),
      new FilterDef('species', 'Species', 'SpeciesFilter',
          'SpeciesHelp', Static.getFiltersData().species),
      new FilterDef('paymentPlans', 'Payment plans', 'MultiselectFilter',
          'PaymentPlansHelp', Static.getFiltersData().paymentPlans),
    ];
  }
}
</script>
