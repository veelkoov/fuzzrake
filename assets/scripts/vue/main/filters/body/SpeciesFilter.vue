<template>
  <fieldset class="species">
    <div class="row">
      <div class="col">
        <WrappedSpecialItems :group-name="groupName" :items="speciesData.specialItems"/>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <SpeciesChoices :species="speciesData.items" :group-name="groupName"/>
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
import Static, {SpeciesFilterData} from '../../../../Static';
import WrappedSpecialItems from '../WrappedSpecialItems.vue';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {SpeciesChoices, WrappedSpecialItems, CheckBox},
  props: {
    groupName: {type: String, required: true},
  }
})
export default class SpeciesFilter extends Vue {
  get speciesData(): SpeciesFilterData {
    return Static.getFiltersData().species;
  }
}
</script>
