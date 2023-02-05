<template>
  <template v-for="specie in species" :key="specie.label">
    <div class="btn-group specie" role="group">
        <span class="btn btn-outline-secondary"> <!-- TODO: #76 Species count -->
          <CheckBox :filter="filter" :count="specie.count" :label="specie.label" :value="specie.label"/>

          <span v-if="anySubspecieChecked(specie)" class="descendants-indicator">
            <wbr> <i class="fas fa-tasks"></i>
          </span>
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
  private filter!: Filter<SpeciesOptions>;

  private hasSubspecies(specie: SpecieItem): boolean {
    return 'string' !== typeof(specie.value);
  }

  private getSubspecies(specie: SpecieItem): SpecieItems {
    return specie.value as SpecieItems;
  }

  private anySubspecieChecked(specie: SpecieItem): boolean {
    if (!this.hasSubspecies(specie)) {
      return false;
    }

    for (const subspecie of (specie.value as SpecieItems)) {
      if (this.filter.state.get(subspecie.label)) {
        return true;
      }

      if (this.hasSubspecies(subspecie) && this.anySubspecieChecked(subspecie)) {
        return true;
      }
    }

    return false;
  }
}
</script>

<style scoped lang="scss">
.species {
  .specie {
    margin: 0.5ex 0.5ex 0 0;

    ::v-deep(label) {
      margin: 0 0 0 1ex;
    }

    button.toggle {
      padding-left: 2ex;
      padding-right: 2ex;
    }
  }

  .subspecies {
    margin-left: 1.5em;
    display: none;
  }
}
</style>
