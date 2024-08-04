<template>
<span></span>
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
