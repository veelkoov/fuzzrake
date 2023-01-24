<template>
  <fieldset>
    <div class="row">
      <div class="col-sm-12">
        <AllNoneInvertLinks class="simple"/>

        <WrappedSpecialItems :filter="filter"/>
      </div>
    </div>

    <div class="row">
      <div v-for="item in filter.options.items" class="col-sm-6 col-lg-3">
        <WrappedCheckBox :filter="filter" :value="item.value" :count="item.count" :label="item.label"/>
      </div>
    </div>
  </fieldset>
</template>

<script lang="ts">
import AllNoneInvertLinks from '../AllNoneInvertLinks.vue';
import Filter from '../../../../main/Filter';
import WrappedCheckBox from '../WrappedCheckBox.vue';
import WrappedSpecialItems from '../WrappedSpecialItems.vue';
import {MultiselectOptions} from '../../../../Static';
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';

@Options({
  components: {WrappedSpecialItems, WrappedCheckBox, AllNoneInvertLinks},
  props: {
    filter: {type: Object as PropType<Filter<MultiselectOptions>>, required: true},
  }
})
export default class MultiselectFilter extends Vue {
}
</script>
