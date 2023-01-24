<template>
  <fieldset class="species">
    <div class="row">
      <div class="col">
        <WrappedSpecialItems :filter="filter"/>
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
import CheckBox from '../CheckBox.vue';
import Filter from '../../../../main/Filter';
import SpeciesChoices from './SpeciesChoices.vue';
import WrappedSpecialItems from '../WrappedSpecialItems.vue';
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';
import {SpeciesOptions} from '../../../../Static';

@Options({
  components: {SpeciesChoices, WrappedSpecialItems, CheckBox},
  props: {
    filter: {type: Object as PropType<Filter<SpeciesOptions>>, required: true},
  }
})
export default class SpeciesFilter extends Vue {
}
</script>
