<template>
  <div
    id="artisanUpdatesModal"
    class="modal fade"
    tabindex="-1"
    aria-hidden="true"
  >
    <div class="modal-dialog">
      <div id="artisanUpdatesModalContent" class="modal-content">
        <div class="modal-header">
          <h5 id="updateRequestLabel" class="modal-title">
            Outdated/inaccurate information:<br />
            {{ artisan.name }}
          </h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          />
        </div>

        <div class="modal-body">
          <p>
            <strong>If you are the maker - please</strong>
            <a :href="Static.getIuFormRedirectUrl(artisan.lastMakerId)"
              >fill the update form</a
            >.
          </p>
          <p>
            Otherwise you can
            <a
              :href="Static.getFeedbackFormPath(artisan.lastMakerId)"
              target="_blank"
              >submit the feedback form</a
            >.
          </p>
        </div>

        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import Artisan from "../../class/Artisan";
import MainState from "./MainState";
import Static from "../../Static";
import { Options, Vue } from "vue-class-component";

@Options({
  computed: {
    Static() {
      return Static;
    },
  },
  props: {
    state: { type: MainState, required: true },
  },
})
export default class UpdatesPopUp extends Vue {
  private state!: MainState;

  private get artisan(): Artisan {
    return this.state.subjectArtisan;
  }
}
</script>
