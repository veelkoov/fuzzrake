<template>
  <div class="modal fade" id="filtersModal" tabindex="-1" aria-labelledby="filtersTitle" aria-hidden="true" ref="modal">
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
import Filter from './Filter';
import MainState from '../MainState';
import Static, {AnyOptions} from '../../../Static';
import {getMessageBus} from '../../../main/MessageBus';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {BodyContainer, CtrlButton},
  props: {
    state: {type: MainState, required: true},
  }
})
export default class FiltersPopUp extends Vue {
  private state!: MainState;
  private filters = new Array<Filter<AnyOptions>>();

  public created(): void {
    this.filters.push(...[
      new Filter('countries', 'Countries', 'CountriesFilter',
          'CountriesHelp', Static.getFiltersOptions().countries),
      new Filter('states', 'States', 'MultiselectFilter',
          'StatesHelp', Static.getFiltersOptions().states),
      new Filter('languages', 'Languages', 'MultiselectFilter',
          'LanguagesHelp', Static.getFiltersOptions().languages),
      new Filter('styles', 'Styles', 'MultiselectFilter',
          'StylesHelp', Static.getFiltersOptions().styles),
      new Filter('features', 'Features', 'MultiselectFilter',
          'FeaturesHelp', Static.getFiltersOptions().features, true),
      new Filter('orderTypes', 'Order types', 'MultiselectFilter',
          'OrderTypesHelp', Static.getFiltersOptions().orderTypes),
      new Filter('productionModels', 'Production models', 'MultiselectFilter',
          'ProductionModelsHelp', Static.getFiltersOptions().productionModels),
      new Filter('openFor', 'Open for', 'MultiselectFilter',
          'OpenForHelp', Static.getFiltersOptions().openFor),
      new Filter('species', 'Species', 'SpeciesFilter',
          'SpeciesHelp', Static.getFiltersOptions().species),
      new Filter('paymentPlans', 'Payment plans', 'MultiselectFilter',
          'PaymentPlansHelp', Static.getFiltersOptions().paymentPlans),
    ]);

    this.filters.forEach(filter => filter.restoreChoices());
  }

  public mounted(): void {
    (this.$refs['modal'] as HTMLElement).addEventListener('hidden.bs.modal', () => this.onModalClosed());

    this.updateState();
  }

  private onModalClosed(): void {
    this.filters.forEach(filter => filter.saveChoices());
    this.updateState();
    getMessageBus().requestDataLoad(this.state.query, false);
  }

  private updateState(): void {
    this.state.activeFiltersCount = this.getActiveFiltersCount();
    this.state.query = $('#filters').serialize(); // TODO: Optimize to avoid error 413 https://github.com/veelkoov/fuzzrake/issues/185
  }

  private getActiveFiltersCount(): number {
    return this.filters.map(filter => filter.state.isActive ? 1 : 0).reduce((sum, val) => sum + val, 0);
  }
}
</script>

<style scoped lang="scss">
::v-deep(fieldset legend) {
  font-size: 1rem;
  font-weight: bold;
  margin-bottom: 0;
  padding-top: .5rem;
}

::v-deep(.count) {
  font-size: 90%;
  color: #aaa;
}
</style>
