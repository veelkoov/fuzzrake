<template>
  <input class="form-check-input" type="checkbox" :id="id" :name="filter.groupName + '[]'" :data-label="label"
         :value="value" @input="changed" :checked="checked"/>

  <label class="form-check-label" :for="id">
    <span v-if="labelHtmlPrefix" v-html="labelHtmlPrefix"></span>
    {{ label }}

    <span v-if="null !== count" class="count">({{ count }})</span>
  </label>
</template>

<script lang="ts">
import Filter from './Filter';
import getUniqueInt from '../../../class/Counter';
import {AnyOptions} from '../../../Static';
import {Options, Vue} from 'vue-class-component';

@Options({
  props: {
    count: {type: Number, required: false}, // TODO: #76 Species count, should not be nullable
    filter: {type: Filter, required: true},
    label: {type: String, required: true},
    labelHtmlPrefix: {type: String, required: false},
    value: {type: String, required: true},
  },
})
export default class CheckBox extends Vue {
  private id: string = 'checkbox' + getUniqueInt();
  private filter!: Filter<AnyOptions>;
  private label!: string;
  private value!: string;

  private changed(event: Event): void {
    this.checked = (event.target as HTMLInputElement).checked;
  }

  private set checked(checked: boolean)
  {
    this.filter.state.set(this.value, this.label, checked);
  }

  public get checked(): boolean {
    return this.filter.state.get(this.value);
  }

  public check(): void {
    this.checked = true;
  }

  public uncheck(): void {
    this.checked = false;
  }

  public invert(): void {
    this.checked = !this.checked;
  }
}
</script>
