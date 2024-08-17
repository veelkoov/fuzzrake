<script lang="ts">
export default class Main extends Vue {
  private disableMakerMode(): void {
    this.aasConfig.setMakerMode(false);
    this.aasConfig.save();
  }

  private onSetupFinished(): void {
    if (this.aasConfig.getMakerMode()) {
      this.messageBus.requestDataLoad('', false);
    }
  }
}
</script>
