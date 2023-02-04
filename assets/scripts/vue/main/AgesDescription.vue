<template>
  <template v-if="addText">
    {{ getText() }}

    <UnknownValue v-if="isUnknown()" />
  </template>

  <i v-for="item in getClasses()" :key="item" class="ages" :class="item" />
</template>

<script lang="ts">
import Artisan from '../../class/Artisan';
import UnknownValue from './UnknownValue.vue';
import {ADULTS, MINORS, MIXED} from '../../consts';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {UnknownValue},
  props: {
    addText: Boolean,
    artisan: Artisan,
  },
})
export default class AgesDescription extends Vue {
  private artisan!: Artisan;
  private addText!: boolean;

  private isUnknown(): boolean {
    return !this.artisan.ages;
  }

  private getClasses(): string[] {
    switch (this.artisan.getAges()) {
      case MINORS:
        return ['fa-solid fa-user-minus'];
      case MIXED:
        return ['fa-solid fa-user-plus', 'fa-solid fa-user-minus'];
      case ADULTS:
        return [];
      default:
        return ['fa-solid fa-user'];
    }
  }

  private getText(): string {
    switch (this.artisan.getAges()) {
      case MINORS:
        return 'Everyone is under 18';
      case MIXED:
        return 'There is a mix of people over and under 18';
      case ADULTS:
        return 'Everyone is over 18';
      default:
        return '';
    }
  }
}
</script>
