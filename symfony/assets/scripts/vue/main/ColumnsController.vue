<template>
  <span></span>
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

  private toggle(columnName: string): void {
    this.columns.toggle(columnName);
    this.columns.save();
  }
}
</script>
