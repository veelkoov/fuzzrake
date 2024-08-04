<template>
<span></span>
</template>

<script lang="ts">
import AgesDescription from '../AgesDescription.vue';
import Artisan from '../../../class/Artisan';
import CardLink from './CardLink.vue';
import MainState from '../MainState';
import OptionalComSep from '../OptionalComSep.vue';
import OptionalList from './../OptionalList.vue';
import OptionalText from '../OptionalText.vue';
import Static from '../../../Static';
import UnknownValue from './../UnknownValue.vue';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {
    OptionalComSep,
    OptionalText, AgesDescription, CardLink, Optional: OptionalComSep, UnknownValue, OptionalList},
  computed: {
    Static() {
      return Static;
    },
  },
  props: {
    state: {type: MainState, required: true},
  },
})
export default class Link extends Vue {
  private state!: MainState;

  private static readonly MONTHS: { [key: string]: string; } = {
    '01': 'Jan',
    '02': 'Feb',
    '03': 'Mar',
    '04': 'Apr',
    '05': 'May',
    '06': 'Jun',
    '07': 'Jul',
    '08': 'Aug',
    '09': 'Sep',
    '10': 'Oct',
    '11': 'Nov',
    '12': 'Dec',
  };

  private get artisan(): Artisan {
    return this.state.subjectArtisan;
  }

  private commaSeparated(list: string[]): string {
    return list.join(', ');
  }

  private getSinceText(): string {
    if (this.artisan.since === '') {
      return '';
    }

    let parts = this.artisan.since.split('-');

    return Link.MONTHS[parts[1]] + ' ' + parts[0];
  }

  private hasPhotos(): boolean {
    return 0 !== this.artisan.photoUrls.length && this.artisan.miniatureUrls.length === this.artisan.photoUrls.length;
  }

  private getCompletenessText(): string {
    if (this.artisan.completeness >= Artisan.DATA_COMPLETE_LEVEL_PERFECT) {
      return 'Awesome! ❤️';
    } else if (this.artisan.completeness >= Artisan.DATA_COMPLETE_LEVEL_GREAT) {
      return 'Great!'
    } else if (this.artisan.completeness >= Artisan.DATA_COMPLETE_LEVEL_GOOD) {
      return 'Good job!'
    } else if (this.artisan.completeness >= Artisan.DATA_COMPLETE_LEVEL_OK) {
      return 'Some updates might be helpful...';
    } else {
      return 'Yikes! :( Updates needed!';
    }
  }
}
</script>

<style scoped>
  .nl2br {
    white-space: pre-wrap;
  }
</style>
