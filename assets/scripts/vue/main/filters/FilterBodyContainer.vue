<template>
  <div :id="'filter-body-' + groupName" class="collapse" data-bs-parent="#filters-parents">
    <div class="text-end helphints-toggle">
      <button class="btn btn-success" type="button" data-bs-toggle="collapse" :data-bs-target="'#' + helphintsContainerId" aria-expanded="false" :aria-controls="helphintsContainerId">Help and hints</button>
    </div>

    <div class="collapse helphints-contents" :id="helphintsContainerId">
      <div class="card">
        <div class="card-body">
          <ul>
            {% block contents %}{% endblock %} <!-- TODO -->

            <li class="small">
              You can help make getfursu.it more helpful and complete. If you plan to contact a maker, who e.g. matched <em>Unknown</em> (didn't supplied some information), you can ask them to fill the missing information, e.g.:

              <em>I found you on getfursu.it, but some information is missing there. Please consider sending updates: <a :href="iuFormUrl" target="_blank">{{ iuFormUrl }}</a> .</em><!-- FIXME: Full URL -->

              Thank you! <i class="fas fa-heart"></i>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <component :is="component" :group-name="groupName"></component>
  </div>
</template>

<script lang="ts">
import {Options, Vue} from 'vue-class-component';
import {PropType} from 'vue';
import CountriesFilters from './body/CountriesFilters.vue';
import Static from '../../../Static';

@Options({
  components: {CountriesFilters},
  props: {
    groupName: {type: String, required: true},
    component: {type: String, required: true},
  }
})
export default class FilterBodyContainer extends Vue {
  private groupName!: string;

  get helphintsContainerId(): string {
    return `${this.groupName}Help`;
  }

  get iuFormUrl(): string {
    return Static.getIuFormRedirectUrl('')
  }
}
</script>
