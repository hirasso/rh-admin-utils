/**
 * Converts an ACF textarea field to a code field
 */
export default () => {
  return {
    init() {
      this.renderSelect();
    },

    renderSelect() {
      const $filters = this.$root.querySelector(".filters");

      const $select = document.createElement("div");
      $select.classList.add("filter", "-order");
      $select.innerHTML = `<select data-rhau-action="order">
        <option value>Change Order</option>
        <option value="flip">Flip</option>
        <option value="alphabetically">Alphabetically</option>
      </select>`;

      $select.addEventListener('change', this.onSelectChange.bind(this));

      $filters.appendChild($select);

      const amount = $filters.querySelectorAll('.filter').length;
      $filters.classList.remove("-f4", "-f3", "-f2", "-f1");
      $filters.style.display = "flex";
      $filters.classList.add(`-f${amount}`);

    },

    /**
     * Handler for when the select changes it's value
     */
    onSelectChange(e) {
      switch (e.target.value) {
        case "flip":
          this.flipOrder();
          break;
        case "alphabetically":
          this.alphabeticalOrder();
          break;
      }
      e.target.value = "";
    },

    /**
     * Get elements needed for ordering
     * @returns {object}
     */
    getElements() {
      return {
        list: this.$root.querySelector(".values-list"),
        items: this.$root.querySelectorAll(".values-list li"),
      };
    },

    /**
     * Invert the order of items
     * @see https://stackoverflow.com/a/50948447/586823
     */
    flipOrder() {
      const { list, items } = this.getElements();
      list.append(...Array.from(items).reverse());
    },

    /**
     * Order items alphabetically
     * @see
     */
    alphabeticalOrder() {
      const { list, items } = this.getElements();
      list.append(
        ...Array.from(items).sort((a, b) =>
          a.innerText.localeCompare(b.innerText)
        )
      );
    },
  };
};
