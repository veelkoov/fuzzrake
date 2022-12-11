<template>
  <div class="modal fade" id="artisanUpdatesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content" id="artisanUpdatesModalContent">
        <div class="modal-header">
          <h5 class="modal-title" id="updateRequestLabel">
            Outdated/inaccurate information:<br />
            {{ artisan.name }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <p>
            <strong>If you are the maker - please</strong> <a :href="DataBridge.getIuFormRedirectUrl(artisan.getLastMakerId())">fill the update form</a>.
          </p>
          <p>
            Otherwise you can <a :href="DataBridge.getFeedbackFormUrl(artisan.getLastMakerId())" target="_blank">submit the feedback form</a>.
          </p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import Artisan from '../class/Artisan';
import DataBridge from '../data/DataBridge';
import {getMessageBus} from '../main/MessageBus';
import {Options, Vue} from 'vue-class-component';

@Options({
  computed: {
    DataBridge() {
      return DataBridge;
    }
  },
})
export default class UpdatesPopUp extends Vue {
  private artisan: Artisan = Artisan.empty();

  public created(): void {
    getMessageBus().listenSubjectArtisanChanges((newSubject: Artisan) => this.artisan = newSubject);
  }
}
</script>
