import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['dropdown'];

    connect() {
        let x = 0;
    }

    toggleDropdown() {
        this.dropdownTarget.classList.toggle('hidden');
    }

    closeDropdown(event) {
        if (!this.dropdownTarget.contains(event.target) && !this.element.contains(event.target)) {
            this.dropdownTarget.classList.add('hidden');
        }
    }
}
