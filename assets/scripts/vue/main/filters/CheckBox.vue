<template>
  <input class="form-check-input" type="checkbox" :id="id" :name="groupName + '[]'" :value="value"
         data-label="{{ label }}"/>

  <label class="form-check-label" :for="id">
    <span v-if="'' !== labelHtmlPrefix" v-html="labelHtmlPrefix"></span>
    {{ label }}
    <span v-if="'' !== labelHtmlSuffix" v-html="labelHtmlSuffix"></span>

    <span v-if="null !== count" class="count">({{ count }})</span>
  </label>
</template>

<script lang="ts">
import getUniqueInt from '../../../class/Counter';
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';

@Options({
  props: {
    groupName: {type: String, required: true},
    value: {type: String, required: true},
    count: {type: Object as PropType<number|null>, required: true},
    labelHtmlPrefix: {type: String, required: false},
    label: {type: String, required: true},
    labelHtmlSuffix: {type: String, required: false},
  }
})
export default class CheckBox extends Vue {
  private id: string = 'checkbox' + getUniqueInt();
}
</script>
