import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'row', 'empty'];

    filter() {
        const query = this.inputTarget.value.toLocaleLowerCase('hu').trim();
        let visible = 0;

        this.rowTargets.forEach((row) => {
            const matches = row.dataset.filterText.includes(query);
            row.hidden = !matches;
            if (matches) visible += 1;
        });

        this.emptyTarget.hidden = visible !== 0;
    }
}
