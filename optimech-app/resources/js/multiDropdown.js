document.addEventListener('alpine:init', () => {
    Alpine.data('multiDropdown', () => ({
        open: false,
        selected: [],
        options: [],

        init() {
            // Initialization logic here
        },

        toggle() {
            this.open = !this.open;
        },

        selectOption(option) {
            if (this.selected.includes(option)) {
                this.selected = this.selected.filter(item => item !== option);
            } else {
                this.selected.push(option);
            }
        },

        isSelected(option) {
            return this.selected.includes(option);
        }
    }));
});
