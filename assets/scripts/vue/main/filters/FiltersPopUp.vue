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
              <CtrlButton group-name="countries"        label="Countries"/> <wbr>
              <CtrlButton group-name="states"           label="States"/> <wbr>
              <CtrlButton group-name="languages"        label="Languages"/> <wbr>
              <CtrlButton group-name="styles"           label="Styles"/> <wbr>
              <CtrlButton group-name="features"         label="Features"/> <wbr>
              <CtrlButton group-name="orderTypes"       label="Order types"/> <wbr>
              <CtrlButton group-name="productionModels" label="Production models"/> <wbr>
              <CtrlButton group-name="openFor"          label="Open for"/> <wbr>
              <CtrlButton group-name="species"          label="Species"/> <wbr>
              <CtrlButton group-name="paymentPlans"     label="Payment plans"/> <wbr>
            </div>
          </div>

          <form id="filters">
            <BodyContainer filter-component="CountriesFilter" help-component="CountriesHelp"
                           group-name="countries"/>

            <BodyContainer filter-component="MultiselectFilter" help-component="StatesHelp"
                           group-name="states" :filter-data="filtersData.states"/>

            <BodyContainer filter-component="MultiselectFilter" help-component="LanguagesHelp"
                           group-name="languages" :filter-data="filtersData.languages"/>

            <BodyContainer filter-component="MultiselectFilter" help-component="StylesHelp"
                           group-name="styles" :filter-data="filtersData.styles"/>

            <BodyContainer filter-component="MultiselectFilter" help-component="FeaturesHelp"
                           group-name="features" :filter-data="filtersData.features"/>

            <BodyContainer filter-component="MultiselectFilter" help-component="OrderTypesHelp"
                           group-name="orderTypes" :filter-data="filtersData.orderTypes"/>

            <BodyContainer filter-component="MultiselectFilter" help-component="ProductionModelsHelp"
                           group-name="productionModels" :filter-data="filtersData.productionModels"/>

            <BodyContainer filter-component="MultiselectFilter" help-component="OpenForHelp"
                           group-name="openFor" :filter-data="filtersData.commissionStatuses"/>

            <BodyContainer filter-component="MultiselectFilter" help-component="PaymentPlansHelp"
                           group-name="paymentPlans" :filter-data="filtersData.paymentPlans"/>

            <BodyContainer filter-component="SpeciesFilter" help-component="SpeciesHelp"
                           group-name="species"/>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import BodyContainer from './BodyContainer.vue';
import CtrlButton from './CtrlButton.vue';
import Static, {FiltersData} from '../../../Static';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {BodyContainer, CtrlButton},
})
export default class FiltersPopUp extends Vue {
  get filtersData(): FiltersData {
    return Static.getFiltersData();
  }
}
</script>
