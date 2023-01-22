<template>
  <fieldset class="region">
    <div class="row">
      <div class="col-sm-12">
        <WrappedSpecialItems :group-name="groupName" :items="countriesData.specialItems"/>
      </div>
    </div>
  </fieldset>

  <fieldset v-for="region in countriesData.items" class="region">
    <legend>
      {{ region.label }} <span class="count">({{ region.count }})</span>

      <AllNoneInvertLinks :class="groupName"/>
    </legend>

    <div class="row">
      <div v-for="country in region.value" class="col-sm-6 col-lg-3">
        <WrappedCheckBox :group-name="groupName" :value="country.value" :count="country.count"
                         :label="country.label" :label-html-prefix="getHtmlPrefix(country)"/>
      </div>
    </div>
  </fieldset>
</template>

<script lang="ts">
import AllNoneInvertLinks from '../AllNoneInvertLinks.vue';
import Static, {Countries, StringItem} from '../../../../Static';
import WrappedCheckBox from '../WrappedCheckBox.vue';
import WrappedSpecialItems from '../WrappedSpecialItems.vue';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {WrappedSpecialItems, WrappedCheckBox, AllNoneInvertLinks},
  props: {
    groupName: {type: String, required: true},
  }
})
export default class CountriesFilter extends Vue {
  private getHtmlPrefix(country: StringItem): string {
    return `<span class="flag-icon flag-icon-${country.value.toLowerCase()}"></span>`;
  }

  get countriesData(): Countries {
    return Static.getFiltersData().countries;
  }
}
</script>
