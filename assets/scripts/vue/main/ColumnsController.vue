<template>
  <li v-for="(label, name) in columns.columns" :data-col-name="name" @click.prevent="toggle(name, $event)">
    <a class="dropdown-item" :class="{ active: columns.isVisible(name) }" href="#">{{ label }}</a>
  </li>
</template>

<script lang="ts">
import ColumnsManager from './ColumnsManager';
import {Options, Vue} from 'vue-class-component';

@Options({
  props: {
    columns: ColumnsManager
  }
})
export default class ColumnsController extends Vue {
  private columns!: ColumnsManager;

  private toggle(columnName: string, event: UIEvent): void {
    this.columns.toggle(columnName);
    this.columns.save();
  }
}
</script>
