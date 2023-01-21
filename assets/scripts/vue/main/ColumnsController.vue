<template>
  <li v-for="(label, name) in columns.columns" :data-col-name="name" @click="toggle(name, $event)">
    <a class="dropdown-item" :class="{ active: columns.isVisible(name) }" href="#">{{ label }}</a>
  </li>
</template>

<script lang="ts">
import {Options, Vue} from 'vue-class-component';
import ColumnsManager from '../../main/ColumnsManager';

@Options({
  props: {
    columns: ColumnsManager
  }
})
export default class ColumnsController extends Vue {
  private columns!: ColumnsManager;

  private toggle(columnName: string, event: UIEvent): void {
    event.preventDefault();

    this.columns.toggle(columnName);
    this.columns.save();
  }
}
</script>
