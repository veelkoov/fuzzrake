<template>
  <fieldset class="region">
    <div class="row">
      <div class="col-sm-12">
        <SpecialItems :filter="filter"/>
      </div>
    </div>
  </fieldset>

  <fieldset v-for="region in filter.options.items" class="region">
    <legend>
      {{ region.label }} <span class="count">({{ region.count }})</span>

      <AllNoneInvertLinks class="countries"
                          @all="checkboxes[region['label']].all()"
                          @none="checkboxes[region['label']].none()"
                          @invert="checkboxes[region['label']].invert()"/>
    </legend>

    <div class="row">
      <div v-for="country in region.value" class="col-sm-6 col-lg-3">
        <div class="form-check form-check-inline">
          <CheckBox :filter="filter" :value="country.value" :count="country.count" :ref="region.label"
                    :label="country.label" :label-html-prefix="getHtmlPrefix(country)"/>
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
import {CountriesOptions, StringItem} from '../../../../Static';
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';

@Options({
  components: {SpecialItems, CheckBox, AllNoneInvertLinks},
  props: {
    filter: {type: Object as PropType<Filter<CountriesOptions>>, required: true},
  }
})
export default class CountriesFilter extends Vue {
  private filter!: Filter<CountriesOptions>;
  private readonly checkboxes = new Map<string, CheckBoxes>();

  public mounted(): void {
    for (const index in this.filter.options.items) {
      const region = this.filter.options.items[index];

      this.checkboxes[region['label']] = new CheckBoxes(this.$refs[region['label']] as CheckBox[]);
    }
  }

  private getHtmlPrefix(country: StringItem): string {
    return `<span class="flag-icon flag-icon-${country.value.toLowerCase()}"></span>`;
  }
}
</script>
