<template>
  <template v-for="specie in species">
    <div class="btn-group specie" role="group">
        <span class="btn btn-outline-secondary"> <!-- TODO: #76 Species count -->
          <CheckBox :filter="filter" :count="specie.count" :label="specie.label" :value="specie.label"
                    label-html-suffix='<span class="descendants-indicator"><i class="fas fa-tasks"></i></span>'/>
        </span>

      <span v-if="hasSubspecies(specie)" class="btn btn-outline-secondary toggle">
        <i class="fas fa-caret-right"></i>
      </span>
    </div>

    <template v-if="hasSubspecies(specie)">
      <br>
      <fieldset class="subspecies">
        <SpeciesChoices :filter="filter" :species="getSubspecies(specie)"/>
      </fieldset>
    </template>
  </template>
</template>

<script lang="ts">
import CheckBox from '../CheckBox.vue';
import Filter from '../Filter';
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';
import {SpecieItem, SpecieItems, SpeciesOptions} from '../../../../Static';

@Options({
  components: {CheckBox, SpeciesChoices},
  props: {
    filter: {type: Object as PropType<Filter<SpeciesOptions>>, required: true},
    species: {type: Object as PropType<SpecieItems>, required: true},
  }
})
export default class SpeciesChoices extends Vue {
  private hasSubspecies(specie: SpecieItem): boolean {
    return 'string' !== typeof(specie.value);
  }

  private getSubspecies(specie: SpecieItem): SpecieItems {
    // @ts-ignore TODO: Should be able to check it
    return specie.value;
  }
}
</script>
