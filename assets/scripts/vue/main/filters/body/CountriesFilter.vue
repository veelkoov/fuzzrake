<template>
  <fieldset class="region">
    <div class="row">
      <div class="col-sm-12">
        <WrappedSpecialItems :group-name="groupName" :items="filterData.specialItems" :state="state"/>
      </div>
    </div>
  </fieldset>

  <fieldset v-for="region in filterData.items" class="region">
    <legend>
      {{ region.label }} <span class="count">({{ region.count }})</span>

      <AllNoneInvertLinks class="countries"/>
    </legend>

    <div class="row">
      <div v-for="country in region.value" class="col-sm-6 col-lg-3">
        <WrappedCheckBox :group-name="groupName" :value="country.value" :count="country.count" :state="state"
                         :label="country.label" :label-html-prefix="getHtmlPrefix(country)"/>
      </div>
    </div>
  </fieldset>
</template>

<script lang="ts">
import AllNoneInvertLinks from '../AllNoneInvertLinks.vue';
import FilterState from '../../../../main/FilterState';
import WrappedCheckBox from '../WrappedCheckBox.vue';
import WrappedSpecialItems from '../WrappedSpecialItems.vue';
import {CountriesFilterData, StringItem} from '../../../../Static';
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';

@Options({
  components: {WrappedSpecialItems, WrappedCheckBox, AllNoneInvertLinks},
  props: {
    filterData: {type: Object as PropType<CountriesFilterData>, required: false},
    groupName: {type: String, required: true},
    state: {type: FilterState, required: true},
  }
})
export default class CountriesFilter extends Vue {
  private getHtmlPrefix(country: StringItem): string {
    return `<span class="flag-icon flag-icon-${country.value.toLowerCase()}"></span>`;
  }
}
</script>
