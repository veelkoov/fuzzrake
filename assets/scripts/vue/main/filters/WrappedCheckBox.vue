<template>
  <div class="form-check form-check-inline">
    <CheckBox :group-name="groupName" :value="value" :count="count" :state="state"
              :label-html-prefix="labelHtmlPrefix" :label="label"/>
  </div>
</template>

<script lang="ts">
import CheckBox from './CheckBox.vue';
import FilterState from '../../../main/FilterState';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {CheckBox},
  props: {
    count: {type: Number, required: true},
    groupName: {type: String, required: true},
    label: {type: String, required: true},
    labelHtmlPrefix: {type: String, required: false},
    state: {type: FilterState, required: true},
    value: {type: String, required: true},
  }
})
export default class WrappedCheckBox extends Vue {
}
</script>
