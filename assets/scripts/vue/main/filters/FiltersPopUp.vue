<template>
<!--  {% macro body_other(group_name, helphints_tpl, body_tpl, filters) %}-->
<!--  <div id="filter-body-{{ group_name }}" class="collapse" data-bs-parent="#filters-parents">-->
<!--    {{ include('main/modals/filters/helphints/' ~ helphints_tpl) }}-->
<!--    {{ include('main/modals/filters/' ~ body_tpl, { filters: filters }) }}-->
<!--  </div>-->
<!--  {% endmacro %}-->

<!--  {% macro body_multiselect(group_name, helphints_tpl, filter_data) %}-->
<!--  <div id="filter-body-{{ group_name }}" class="collapse" data-bs-parent="#filters-parents">-->
<!--    {{ include('main/modals/filters/helphints/' ~ helphints_tpl) }}-->
<!--    {{ include('main/modals/filters/multiselect.html.twig') }}-->
<!--  </div>-->
<!--  {% endmacro %}-->

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
              <CtrlButton group-name="countries"          label="Countries"/> <wbr>
              <CtrlButton group-name="states"             label="States"/> <wbr>
              <CtrlButton group-name="languages"          label="Languages"/> <wbr>
              <CtrlButton group-name="styles"             label="Styles"/> <wbr>
              <CtrlButton group-name="features"           label="Features"/> <wbr>
              <CtrlButton group-name="orderTypes"         label="Order types"/> <wbr>
              <CtrlButton group-name="productionModels"   label="Production models"/> <wbr>
              <CtrlButton group-name="commissionStatuses" label="Open for"/> <wbr>
              <CtrlButton group-name="species"            label="Species"/> <wbr>
              <CtrlButton group-name="paymentPlans"       label="Payment plans"/> <wbr>
            </div>
          </div>

          <form id="filters">
            <FilterBodyContainer component="CountriesFilters" group-name="countries"/>
<!--            { { macros.body_other('countries',                'countries.html.twig',          'countries.html.twig', filters) }}-->
<!--            { { macros.body_multiselect('states',             'states.html.twig',             filters.states) }}-->
<!--            { { macros.body_multiselect('languages',          'languages.html.twig',          filters.languages) }}-->
<!--            { { macros.body_multiselect('styles',             'styles.html.twig',             filters.styles) }}-->
<!--            { { macros.body_multiselect('features',           'features.html.twig',           filters.features) }}-->
<!--            { { macros.body_multiselect('orderTypes',         'order_types.html.twig',        filters.orderTypes) }}-->
<!--            { { macros.body_multiselect('productionModels',   'production_models.html.twig',  filters.productionModels) }}-->
<!--            { { macros.body_multiselect('commissionStatuses', 'commissions_status.html.twig', filters.commissionStatuses) }}-->
<!--            { { macros.body_other('species',                  'species.html.twig',            'species.html.twig', filters) }}-->
<!--            { { macros.body_multiselect('paymentPlans',       'payment_plans.html.twig',      filters.paymentPlans) }}-->
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import CtrlButton from './CtrlButton.vue';
import FilterBodyContainer from './FilterBodyContainer.vue';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {FilterBodyContainer, CtrlButton},
})
export default class FiltersPopUp extends Vue {
}
</script>
