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
        class="countries"
        @all="checkboxes.get(region['label'])!.all()"
        @none="checkboxes.get(region['label'])!.none()"
        @invert="checkboxes.get(region['label'])!.invert()"
      />
    </legend>

    <div class="row">
      <div
        v-for="country in region.subitems"
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

<script lang="ts">
import AllNoneInvertLinks from "../AllNoneInvertLinks.vue";
import CheckBox from "../CheckBox.vue";
import CheckBoxes from "../CheckBoxes";
import Filter from "../Filter";
import SpecialItems from "../SpecialItems.vue";
import { Item } from "../../../../Static";
import { Options, Vue } from "vue-class-component";
import { PropType } from "vue";

@Options({
  components: { SpecialItems, CheckBox, AllNoneInvertLinks },
  props: {
    filter: { type: Object as PropType<Filter>, required: true },
  },
})
export default class CountriesFilter extends Vue {
  private filter!: Filter;
  private readonly checkboxes = new Map<string, CheckBoxes>();

  public mounted(): void {
    for (const index in this.filter.options.items) {
      const region = this.filter.options.items[index];

      this.checkboxes.set(
        region["label"],
        new CheckBoxes(this.$refs[region["label"]] as (typeof CheckBox)[]),
      );
    }
  }

  private getHtmlPrefix(country: Item): string {
    return `<span class="flag-icon flag-icon-${country.value.toLowerCase()}"></span>`;
  }
}
</script>
