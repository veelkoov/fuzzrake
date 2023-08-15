<script lang="ts">
import AllNoneInvertLinks from '../AllNoneInvertLinks.vue';
import CheckBox from '../CheckBox.vue';
import Filter from '../Filter';
import SpecialItems from '../SpecialItems.vue';
import {CountriesOptions, StringItem} from '../../../../Static';
import {defineComponent, PropType} from 'vue';

export default defineComponent({
  components: {SpecialItems, CheckBox, AllNoneInvertLinks},

  props: {
    filter: {
      type: Object as PropType<Filter<CountriesOptions>>,
      required: true,
    },
  },

  data() {
    return {
      checkboxes: new Map<string, Array<typeof CheckBox>>(),
    }
  },

  mounted(): void {
    for (const index in this.filter.options.items) {
      const region = this.filter.options.items[index];

      this.checkboxes.set(region['label'], this.$refs[region['label']] as Array<typeof CheckBox>);
    }
  },

  methods: {
    getHtmlPrefix(country: StringItem): string {
      return `<span class="flag-icon flag-icon-${country.value.toLowerCase()}"></span>`;
    },
  },
})
</script>

<template>
  <fieldset class="region">
    <div class="row">
      <div class="col-sm-12">
        <SpecialItems :filter="filter" />
      </div>
    </div>
  </fieldset>

  <fieldset
    v-for="region in filter.options.items"
    :key="region.label"
    class="region"
  >
    <legend>
      {{ region.label }} <span class="count">({{ region.count }})</span>

      <AllNoneInvertLinks
        :checkboxes="checkboxes.get(region['label'])"
        class="countries"
      />
    </legend>

    <div class="row">
      <div
        v-for="country in region.value"
        :key="country.value"
        class="col-sm-6 col-lg-3"
      >
        <div class="form-check form-check-inline">
          <CheckBox
            :ref="region.label"
            :filter="filter"
            :value="country.value"
            :count="country.count"
            :label="country.label"
            :label-html-prefix="getHtmlPrefix(country)"
          />
        </div>
      </div>
    </div>
  </fieldset>
</template>
