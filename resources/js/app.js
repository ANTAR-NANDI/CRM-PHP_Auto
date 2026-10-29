import './bootstrap';

import Alpine from 'alpinejs';
import $ from 'jquery';
import select2 from 'select2/dist/js/select2.full';
import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import 'datatables.net-buttons-dt';
import 'datatables.net-buttons-dt/css/buttons.dataTables.css';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import pdfMake from 'pdfmake/build/pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';
import 'select2/dist/css/select2.css';

window.Alpine = Alpine;
window.$ = window.jQuery = $;
select2(window, $);

pdfMake.addVirtualFileSystem(pdfFonts);
DataTable.Buttons.pdfMake(pdfMake);

document.addEventListener('DOMContentLoaded', () => {
    const activityTable = document.querySelector('#activities-table');
    if (activityTable) {
        new DataTable(activityTable, {
            pageLength: 25,
            order: [[1, 'desc']],
            layout: {
                topStart: { buttons: [{ extend: 'pdfHtml5', text: 'Export PDF', title: 'CRM Activity Report', exportOptions: { columns: ':not(:last-child)' } }] },
                topEnd: 'search',
            },
            language: { search: 'Search activities:' },
        });
    }

    const budgetTable = document.querySelector('#budgets-table');
    if (budgetTable) {
        new DataTable(budgetTable, {
            pageLength: 25,
            order: [[2, 'desc']],
            layout: { topStart: { buttons: [{ extend: 'pdfHtml5', text: 'Export PDF', title: 'Event Budget Report' }] }, topEnd: 'search' },
            language: { search: 'Search budgets:' },
        });
    }
});

Alpine.start();
