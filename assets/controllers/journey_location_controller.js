import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['building', 'floor', 'room'];

    connect() {
        this.filterFloors();
        this.filterRooms();
    }

    buildingChanged() {
        this.floorTarget.value = '';
        this.roomTarget.value = '';
        this.filterFloors();
        this.filterRooms();
    }

    floorChanged() {
        this.roomTarget.value = '';
        this.filterRooms();
    }

    filterFloors() {
        this.filter(this.floorTarget, 'buildingId', this.buildingTarget.value);
    }

    filterRooms() {
        this.filter(this.roomTarget, 'floorId', this.floorTarget.value);
    }

    filter(select, key, selectedId) {
        for (const option of select.options) {
            if (!option.value) continue;
            const visible = Boolean(selectedId) && option.dataset[key] === selectedId;
            option.hidden = !visible;
            option.disabled = !visible;
        }
        select.disabled = !selectedId;
    }
}
