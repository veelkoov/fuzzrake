<template>
  <a v-if="url" class="btn btn-light" :class="addBtnClass" :href="url" target="_blank">
    <i :class="iconClass"></i> {{ label }}
  </a>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component';

@Options({
  props: {
    url: String,
    addBtnClass: String,
    iconClass: String,
    label: String,
  }
})
export default class CardLink extends Vue {
  url!: string;
  addBtnClass!: string;
  iconClass!: string;
  label!: string;
}
</script>
