import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['sidebar', 'button'];

    toggle() {
        const open = this.sidebarTarget.classList.toggle('is-open');
        this.buttonTarget.setAttribute('aria-expanded', String(open));
    }
}
