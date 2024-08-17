<script lang="ts">
export default class Main extends Vue {
  private onChecklistDismissal(): void {
    nextTick(() => { // Checklist causes the user to be at the bottom of the table when it shows up
      // FIXME: https://github.com/veelkoov/fuzzrake/pull/187/files
      // eslint-disable-next-line no-undef
      const offset = jQuery('#data-table-content-container').offset() || {'top': 5};
      window.scrollTo(0, offset.top - 5);
    });
  }

  private disableMakerMode(): void {
    this.aasConfig.setMakerMode(false);
    this.aasConfig.save();
  }

  private onSetupFinished(): void {
    if (this.aasConfig.getMakerMode()) {
      this.messageBus.requestDataLoad('', false);
    }

    if (window.location.hash.match(makerIdHashRegexp)) {
      this.state.openCardForMakerId = window.location.hash.slice(1);

      if (!this.aasConfig.getMakerMode()) {
        this.messageBus.requestDataLoad('wantsSfw=0&isAdult=1&inactive[]=.&makerId=' + this.state.openCardForMakerId, true);
      }
    }
  }
}
</script>
