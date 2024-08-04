<template>
<span></span>
</template>

<script lang="ts">
import Filter from './Filter';
import {Options, Vue} from 'vue-class-component';

@Options({
  props: {
    filter: {type: Filter, required: true},
  }
})
export default class CtrlButton extends Vue {
}
</script>
