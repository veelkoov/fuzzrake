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
<!--              { { macros.ctrl_button('countries',          'Countries') }}-->
<!--              { { macros.ctrl_button('states',             'States') }}-->
<!--              { { macros.ctrl_button('languages',          'Languages') }}-->
<!--              { { macros.ctrl_button('styles',             'Styles') }}-->
<!--              { { macros.ctrl_button('features',           'Features') }}-->
<!--              { { macros.ctrl_button('orderTypes',         'Order types') }}-->
<!--              { { macros.ctrl_button('productionModels',   'Production models') }}-->
<!--              { { macros.ctrl_button('commissionStatuses', 'Open for') }}-->
<!--              { { macros.ctrl_button('species',            'Species') }}-->
<!--              { { macros.ctrl_button('paymentPlans',       'Payment plans') }}-->
            </div>
          </div>

          <form id="filters">
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
            <CountriesFilters />
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import {Options, Vue} from 'vue-class-component';
import CountriesFilters from './CountriesFilters.vue';

@Options({
  components: {CountriesFilters},
})
export default class FiltersPopUp extends Vue {
}
</script>
