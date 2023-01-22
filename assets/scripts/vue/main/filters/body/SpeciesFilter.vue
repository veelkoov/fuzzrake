<template>
  <fieldset class="species">
    <div class="row">
      <div class="col">
        <WrappedSpecialItems :group-name="groupName" :items="filterData.specialItems" :state="state"/>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <SpeciesChoices :species="filterData.items" :group-name="groupName" :state="state"/>
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
import SpeciesChoices from './SpeciesChoices.vue';
import Static, {MultiselectFilterData, SpeciesFilterData} from '../../../../Static';
import WrappedSpecialItems from '../WrappedSpecialItems.vue';
import {Options, Vue} from 'vue-class-component';
import FilterState from '../../../../main/FilterState';
import {PropType} from 'vue';

@Options({
  components: {SpeciesChoices, WrappedSpecialItems, CheckBox},
  props: {
    filterData: {type: Object as PropType<MultiselectFilterData>, required: false},
    groupName: {type: String, required: true},
    state: {type: FilterState, required: true},
  }
})
export default class SpeciesFilter extends Vue {
}
</script>
