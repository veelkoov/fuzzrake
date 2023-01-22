<template>
  <fieldset>
    <div class="row">
      <div class="col-sm-12">
        <AllNoneInvertLinks class="simple"/>

        <WrappedSpecialItems :group-name="groupName" :items="filterData.specialItems"/>
      </div>
    </div>

    <div class="row">
      <div v-for="item in filterData.items" class="col-sm-6 col-lg-3">
        <WrappedCheckBox :group-name="groupName" :value="item.value" :count="item.count"
                         :label="item.label"/>
      </div>
    </div>
  </fieldset>
</template>

<script lang="ts">
import AllNoneInvertLinks from '../AllNoneInvertLinks.vue';
import WrappedCheckBox from '../WrappedCheckBox.vue';
import WrappedSpecialItems from '../WrappedSpecialItems.vue';
import {MultiselectFilterData} from '../../../../Static';
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';

@Options({
  components: {WrappedSpecialItems, WrappedCheckBox, AllNoneInvertLinks},
  props: {
    groupName: {type: String, required: true},
    filterData: {type: Object as PropType<MultiselectFilterData>, required: false},
  }
})
export default class MultiselectFilter extends Vue {
}
</script>
