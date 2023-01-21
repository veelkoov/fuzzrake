<template>
  <div class="form-check form-check-inline">
    <CheckBox :group-name="groupName" :value="value" :count="count"
              :label-html-prefix="labelHtmlPrefix" :label="label" :label-html-suffix="labelHtmlSuffix"/>
  </div>
</template>

<script lang="ts">
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';
import CheckBox from './CheckBox.vue';

@Options({
  components: {CheckBox},
  props: {
    groupName: {type: String, required: true},
    value: {type: String, required: true},
    count: {type: Object as PropType<number|null>, required: true},
    labelHtmlPrefix: {type: String, required: false},
    label: {type: String, required: true},
    labelHtmlSuffix: {type: String, required: false},
  }
})
export default class WrappedCheckBox extends Vue {
}
</script>
