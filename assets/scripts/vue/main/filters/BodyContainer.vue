<template>
  <div :id="'filter-body-' + groupName" class="collapse" data-bs-parent="#filters-parents">
    <div class="text-end helphints-toggle">
      <button class="btn btn-success" type="button" data-bs-toggle="collapse" :data-bs-target="'#' + helpContainerId" aria-expanded="false" :aria-controls="helpContainerId">Help and hints</button>
    </div>

    <div class="collapse helphints-contents" :id="helpContainerId">
      <div class="card">
        <div class="card-body">
          <ul>
            <component :is="helpComponent"/>

            <li class="small">
              You can help make getfursu.it more helpful and complete. If you plan to contact a maker, who e.g. matched <em>Unknown</em> (didn't supplied some information), you can ask them to fill the missing information, e.g.:

              <em>I found you on getfursu.it, but some information is missing there. Please consider sending updates: <a :href="iuFormUrl" target="_blank">{{ iuFormUrl }}</a> .</em><!-- FIXME: Full URL -->

              Thank you! <i class="fas fa-heart"></i>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <component :is="filterComponent" :group-name="groupName"/>
  </div>
</template>

<script lang="ts">
import CountriesFilter from './body/CountriesFilter.vue';
import CountriesHelp from './help/CountriesHelp.vue';
import Static from '../../../Static';
import {Options, Vue} from 'vue-class-component';

@Options({
  components: {
    CountriesFilter, CountriesHelp,
  },
  props: {
    groupName: {type: String, required: true},
    filterComponent: {type: String, required: true},
    helpComponent: {type: String, required: true},
  }
})
export default class BodyContainer extends Vue {
  private groupName!: string;

  get helpContainerId(): string {
    return `${this.groupName}Help`;
  }

  get iuFormUrl(): string {
    return Static.getIuFormRedirectUrl('')
  }
}
</script>
