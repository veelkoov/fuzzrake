<template>
  <template v-for="specie in species" :key="specie.label">
    <div class="btn-group specie" role="group">
      <span class="btn btn-outline-secondary">
        <CheckBox :filter="filter" :count="specie.count" :label="specie.label" :value="specie.label" />

        <span v-if="anySubspecieChecked(specie)" class="descendants-indicator">
          <wbr> <i class="fas fa-tasks" />
        </span>
      </span>

      <span v-if="0 !== specie.subitems.length" class="btn btn-outline-secondary toggle">
        <i class="fas fa-caret-right" />
      </span>
    </div>

    <template v-if="0 !== specie.subitems.length">
      <br>
      <fieldset class="subspecies">
        <SpeciesChoices :filter="filter" :species="specie.subitems" />
      </fieldset>
    </template>
  </template>
</template>

<script lang="ts">
import CheckBox from '../CheckBox.vue';
import Filter from '../Filter';
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';
import {Item, Items} from '../../../../Static';

@Options({
  components: {CheckBox, SpeciesChoices},
  props: {
    filter: {type: Object as PropType<Filter>, required: true},
    species: {type: Object as PropType<Items>, required: true},
  }
})
export default class SpeciesChoices extends Vue {
  private filter!: Filter;

  private anySubspecieChecked(specie: Item): boolean {
    for (const subspecie of specie.subitems) {
      if (this.filter.state.get(subspecie.label)) {
        return true;
      }

      if (0 !== subspecie.subitems.length && this.anySubspecieChecked(subspecie)) {
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
