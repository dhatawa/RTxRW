import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

Alpine.plugin(focus);

/**
 * Pratinjau nama & ukuran file sebelum diunggah, dengan validasi ukuran di sisi klien
 * (validasi sebenarnya tetap di server).
 */
Alpine.data('fileInput', (maxKb = 2048) => ({
    name: '',
    size: '',
    error: '',
    pick(event) {
        const file = event.target.files[0];
        this.error = '';
        if (!file) {
            this.name = '';
            this.size = '';
            return;
        }
        if (file.size > maxKb * 1024) {
            this.error = `Ukuran file ${(file.size / 1048576).toFixed(1)} MB melebihi batas 2 MB.`;
            event.target.value = '';
            this.name = '';
            return;
        }
        this.name = file.name;
        this.size = file.size >= 1048576 ? `${(file.size / 1048576).toFixed(1)} MB` : `${Math.round(file.size / 1024)} KB`;
    },
}));

/** Baris dinamis (persyaratan dokumen / isian tambahan) di form jenis surat. */
Alpine.data('repeater', (initial = [], blank = {}) => ({
    rows: initial.length ? initial : [],
    add() {
        this.rows.push({ ...blank, _key: Date.now() + Math.random() });
    },
    remove(index) {
        this.rows.splice(index, 1);
    },
    move(index, delta) {
        const target = index + delta;
        if (target < 0 || target >= this.rows.length) return;
        const [row] = this.rows.splice(index, 1);
        this.rows.splice(target, 0, row);
    },
}));

window.Alpine = Alpine;
Alpine.start();
