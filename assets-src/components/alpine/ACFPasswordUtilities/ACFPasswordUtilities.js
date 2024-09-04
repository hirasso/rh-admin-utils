export default ({} = {}) => ({
  input: null,

  inputType: "password",
  value: "",
  defaultCopyText: "Copy",
  copyText: "",

  get copySupported() {
    return window.navigator.clipboard != null;
  },

  get toggleText() {
    return this.inputType === "password" ? "Reveal" : "Hide";
  },

  init() {
    this.copyText = this.defaultCopyText;
    this.input = this.$el
      .closest(".acf-input")
      .querySelector(".acf-input-wrap input");
    this.value = this.input.value;
    this.input.addEventListener("input", (e) => this.onInput());
    this.$refs.toggle.addEventListener("click", (e) => this.onToggleClick(e));
    this.$refs.generator.addEventListener("click", (e) =>
      this.onGeneratorClick(e),
    );

    this.$watch("inputType", (value) => this.inputTypeWatcher(value));
    this.$watch("value", (value) => this.valueWatcher(value));
  },

  valueWatcher(value) {
    this.copyText = this.defaultCopyText;
  },

  onInput() {
    this.value = this.input.value;
  },

  inputTypeWatcher(value) {
    this.input.type = this.inputType;
  },

  onToggleClick(e) {
    e.preventDefault();
    this.inputType = this.inputType === "password" ? "text" : "password";
  },

  onCopyClick(e) {
    const pw = this.value;
    window.navigator.clipboard.writeText(pw);
    this.copyText = "Copied!";
  },

  onGeneratorClick(e) {
    e.preventDefault();
    this.inputType = "text";
    this.value = this.input.value = this.generatePassword();
  },

  generatePassword() {
    const length = 12;
    const charset =
      "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567895!#.?";
    let result = "";
    for (var i = 0, n = charset.length; i < length; ++i) {
      result += charset.charAt(Math.round(Math.random() * n));
    }
    return result;
  },

  destroy() {},
});
