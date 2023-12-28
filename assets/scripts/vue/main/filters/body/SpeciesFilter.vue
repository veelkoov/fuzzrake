<template>
  <fieldset class="species">
    <div class="row">
      <div class="col">
        <SpecialItems :filter="filter" />
      </div>
    </div>

    <div class="row">
      <div class="col">
        <SpeciesChoices :species="filter.options.items" :filter="filter" />
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

@Options({
  components: {SpeciesChoices, SpecialItems},
  props: {
    filter: {type: Object as PropType<Filter>, required: true},
  }
})
export default class SpeciesFilter extends Vue {
  mounted() {
    // FIXME: https://github.com/veelkoov/fuzzrake/pull/187/files
    // eslint-disable-next-line no-undef
    jQuery('#filtersModal .specie .toggle').on('click', function (): void {
      // FIXME: https://github.com/veelkoov/fuzzrake/pull/187/files
      // eslint-disable-next-line no-undef
      jQuery(this).parents('.specie').nextAll('.subspecies').first().toggle(250);
    });
  }
}
</script>
