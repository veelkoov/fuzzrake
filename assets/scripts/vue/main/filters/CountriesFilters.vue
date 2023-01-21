<template>
  <fieldset class="region">
    <div class="row">
      <div class="col-sm-12">
        <WrappedSpecialItems group-name="countries" :items="Static.getFiltersData().countries.specialItems"/>
      </div>
    </div>
  </fieldset>

  <fieldset v-for="region in Static.getFiltersData().countries.items" class="region">
    <legend>
      {{ region.label }} <span class="count">({{ region.count }})</span>

      <AllNoneInvertLinks class="countries"/>
    </legend>

    <div class="row">
      <div v-for="country in region.value" class="col-sm-6 col-lg-3">
        <WrappedCheckBox group-name="countries" :value="country.value" :count="country.count"
                         :label="country.label" :label-html-prefix="getHtmlPrefix(country)"/>
      </div>
    </div>
  </fieldset>
</template>

<script lang="ts">
import AllNoneInvertLinks from './AllNoneInvertLinks.vue';
import CheckBox from './CheckBox.vue';
import Static, {StringItem} from '../../../Static';
import WrappedCheckBox from './WrappedCheckBox.vue';
import WrappedSpecialItems from './WrappedSpecialItems.vue';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {WrappedSpecialItems, WrappedCheckBox, CheckBox, AllNoneInvertLinks},
  computed: {
    Static() {
      return Static;
    },
  },
})
export default class CountriesFilters extends Vue {
  private getHtmlPrefix(country: StringItem): string {
    return `<span class="flag-icon flag-icon-${country.value.toLowerCase()}"></span>`;
  }
}
</script>
