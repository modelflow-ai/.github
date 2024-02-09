import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['dropdown', 'icon'];

    connect() {
        window.addEventListener('click', (event) => {
            this.closeDropdown(event);
        });
    }

    toggleDropdown() {
        this.dropdownTarget.classList.toggle('hidden');
        this.iconTarget.classList.toggle("rotate-180");
    }

    closeDropdown(event) {
        if (!this.dropdownTarget.contains(event.target) && !this.element.contains(event.target)) {
            this.dropdownTarget.classList.add('hidden');
        }
    }
}
