<template>
  <li v-if="url">
    <a class="dropdown-item" :href="url" target="_blank">
      <i :class="iconClass"></i> {{ label }}
    </a>
  </li>
</template>

<script lang="ts">
import {Options, Vue} from 'vue-class-component';

@Options({
  props: {
    url: String,
    iconClass: String,
    label: String,
  }
})
export default class TblLink extends Vue {
  url!: string;
  iconClass!: string;
  label!: string;
}
</script>
