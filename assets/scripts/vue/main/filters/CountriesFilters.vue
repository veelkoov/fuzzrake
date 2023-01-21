<template>
  <fieldset class="region">
    <div class="row">
      <div class="col-sm-12">
        <!-- TODO {{ macros.filter_special_items_wrapped(filters.countries, group_name) }} -->
      </div>
    </div>
  </fieldset>

  <fieldset v-for="region in Static.getRegions()" class="region">
    <legend>
      {{ region['name'] }} <span class="count">({{ region['m_count'] }})</span>

      <AllNoneInvertLinks class="countries"/>
    </legend>

    <div class="row">
      <div v-for="country in region['countries']" class="col-sm-6 col-lg-3">
        <WrappedCheckBox group-name="countries" :value="country.value" :count="country['m_count']"
                         :label="country['label']" :label-html-prefix="getHtmlPrefix(country.value)"/>
      </div>
    </div>
  </fieldset>
</template>

<script lang="ts">
import {Options, Vue} from 'vue-class-component';
import Static from '../../../Static';
import AllNoneInvertLinks from './AllNoneInvertLinks.vue';
import CheckBox from './CheckBox.vue';
import WrappedCheckBox from './WrappedCheckBox.vue';

@Options({
  components: {WrappedCheckBox, CheckBox, AllNoneInvertLinks},
  computed: {
    Static() {
      return Static;
    },
  },
})
export default class CountriesFilters extends Vue {
  private getHtmlPrefix(countryCode: string): string {
    return `<span class="flag-icon flag-icon-${countryCode.toLowerCase()}"></span>`;
  }
}
</script>
