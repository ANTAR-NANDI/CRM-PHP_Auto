import './bootstrap';

import Alpine from 'alpinejs';
import $ from 'jquery';
import select2 from 'select2';
import 'select2/dist/css/select2.css';

window.Alpine = Alpine;
window.$ = window.jQuery = $;

select2($);

const initializeSearchableSelects = (root = document) => {
    $(root).find('select:not(.select2-hidden-accessible):not([data-native-select])').each(function () {
        // Alpine-managed and template-generated selects need to remain native controls.
        if (this.hasAttribute('x-model') || this.hasAttribute(':name') || this.closest('template')) {
            return;
        }

        $(this).select2({
            width: '100%',
            dropdownAutoWidth: true,
        });
    });
};

document.addEventListener('DOMContentLoaded', () => initializeSearchableSelects());

Alpine.start();
