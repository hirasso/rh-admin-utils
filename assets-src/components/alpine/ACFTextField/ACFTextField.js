/**
 * Applies a mask to an ACF text field
 */
export default () => {
  return {
    init() {
      if (this.$root.closest(".acf-clone") !== null) return;

      this.applyMask();
    },

    applyMask() {
      const mask = this.$root.getAttribute("data-rhau-input-mask");
      if (!mask) return;

      const input = this.$root.querySelector(
        ".acf-input-wrap > input:first-child"
      );
      input.setAttribute("rhau-x-mask", mask);
    },
  };
};
