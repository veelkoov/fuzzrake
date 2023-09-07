<template>
  <fieldset>
    <div class="row">
      <div class="col-sm-12">
        <AllNoneInvertLinks
            v-if="0 !== filter.options.items.length"
            class="simple"
            @all="checkboxes.all()"
            @none="checkboxes.none()"
            @invert="checkboxes.invert()"
        />

        <SpecialItems :filter="filter"/>
      </div>
    </div>

    <div class="row">
      <div v-for="item in filter.options.items" :key="item.value" class="col-sm-6 col-lg-3">
        <div class="form-check form-check-inline">
          <CheckBox :filter="filter" :value="item.value" :count="item.count" :label="item.label" ref="checkboxes"/>
        </div>
      </div>
    </div>
  </fieldset>
</template>

<script lang="ts">
import AllNoneInvertLinks from '../AllNoneInvertLinks.vue';
import CheckBox from '../CheckBox.vue';
import CheckBoxes from '../CheckBoxes';
import Filter from '../Filter';
import SpecialItems from '../SpecialItems.vue';
import {MultiselectOptions} from '../../../../Static';
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';

@Options({
  components: {SpecialItems, CheckBox, AllNoneInvertLinks},
  props: {
    filter: {type: Object as PropType<Filter<MultiselectOptions>>, required: true},
  }
})
export default class MultiselectFilter extends Vue {
  private checkboxes!: CheckBoxes;

  public mounted(): void {
    this.checkboxes = new CheckBoxes(this.$refs['checkboxes'] as typeof CheckBox[]);
  }
}
</script>
