<style>
/* Shared DataTables chrome — matches compact list layout */
.member-list-page .dataTables_wrapper,
.jer-page .dataTables_wrapper,
.vt-page .dataTables_wrapper,
.loan-ledger-page .dataTables_wrapper {
    padding: 4px 8px 8px;
}
.member-list-page .dataTables_wrapper .dataTables_length,
.member-list-page .dataTables_wrapper .dataTables_filter,
.member-list-page .dataTables_wrapper .dataTables_info,
.jer-page .dataTables_wrapper .dataTables_length,
.jer-page .dataTables_wrapper .dataTables_filter,
.jer-page .dataTables_wrapper .dataTables_info,
.vt-page .dataTables_wrapper .dataTables_length,
.vt-page .dataTables_wrapper .dataTables_filter,
.vt-page .dataTables_wrapper .dataTables_info,
.loan-ledger-page .dataTables_wrapper .dataTables_length,
.loan-ledger-page .dataTables_wrapper .dataTables_filter,
.loan-ledger-page .dataTables_wrapper .dataTables_info {
    color: #676a6c;
    font-size: 12px;
    font-weight: 600;
}
.member-list-page .dataTables_wrapper .dataTables_filter input,
.member-list-page .dataTables_wrapper .dataTables_length select,
.jer-page .dataTables_wrapper .dataTables_filter input,
.jer-page .dataTables_wrapper .dataTables_length select,
.vt-page .dataTables_wrapper .dataTables_filter input,
.vt-page .dataTables_wrapper .dataTables_length select,
.loan-ledger-page .dataTables_wrapper .dataTables_filter input,
.loan-ledger-page .dataTables_wrapper .dataTables_length select {
    border: 1px solid #e5e6e7;
    border-radius: 6px;
    height: 32px;
    padding: 4px 8px;
    background: #fafbfc;
    color: #2f4050;
    font-weight: 700;
    outline: none;
    box-shadow: none;
}
.member-list-page .dataTables_wrapper .dataTables_filter input:focus,
.member-list-page .dataTables_wrapper .dataTables_length select:focus,
.jer-page .dataTables_wrapper .dataTables_filter input:focus,
.jer-page .dataTables_wrapper .dataTables_length select:focus,
.vt-page .dataTables_wrapper .dataTables_filter input:focus,
.vt-page .dataTables_wrapper .dataTables_length select:focus,
.loan-ledger-page .dataTables_wrapper .dataTables_filter input:focus,
.loan-ledger-page .dataTables_wrapper .dataTables_length select:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.member-list-page .dataTables_wrapper .dataTables_paginate,
.jer-page .dataTables_wrapper .dataTables_paginate,
.vt-page .dataTables_wrapper .dataTables_paginate,
.loan-ledger-page .dataTables_wrapper .dataTables_paginate {
    padding-top: 8px;
}
.member-list-page .dataTables_wrapper .dataTables_paginate .paginate_button,
.jer-page .dataTables_wrapper .dataTables_paginate .paginate_button,
.vt-page .dataTables_wrapper .dataTables_paginate .paginate_button,
.loan-ledger-page .dataTables_wrapper .dataTables_paginate .paginate_button {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    margin: 0 2px !important;
    padding: 6px 10px !important;
    border: 1px solid #e1e5e8 !important;
    border-radius: 8px !important;
    background: #fff !important;
    color: #676a6c !important;
    font-weight: 600;
    font-size: 12px;
    line-height: 1.2;
    box-shadow: none !important;
}
.member-list-page .dataTables_wrapper .dataTables_paginate .paginate_button.current,
.jer-page .dataTables_wrapper .dataTables_paginate .paginate_button.current,
.vt-page .dataTables_wrapper .dataTables_paginate .paginate_button.current,
.loan-ledger-page .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #1ab394 !important;
    border-color: #1ab394 !important;
    color: #fff !important;
    box-shadow: 0 2px 8px rgba(26,179,148,0.28) !important;
}
.member-list-page .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled),
.jer-page .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled),
.vt-page .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled),
.loan-ledger-page .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
    background: #e8f8f5 !important;
    border-color: #1ab394 !important;
    color: #1ab394 !important;
}
.member-list-page .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
.jer-page .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
.vt-page .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
.loan-ledger-page .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
    opacity: .45;
    cursor: default !important;
}
.member-list-page .dt-buttons .btn,
.jer-page .dt-buttons .btn,
.vt-page .dt-buttons .btn,
.loan-ledger-page .dt-buttons .btn {
    border-radius: 6px;
    font-weight: 600;
    font-size: 12px;
    padding: 5px 10px;
    margin: 0 4px 6px 0;
    background: #fff;
    border: 1px solid #e1e5e8;
    color: #676a6c;
    box-shadow: none;
}
.member-list-page .dt-buttons .btn:hover,
.jer-page .dt-buttons .btn:hover,
.vt-page .dt-buttons .btn:hover,
.loan-ledger-page .dt-buttons .btn:hover {
    background: #e8f8f5;
    border-color: #1ab394;
    color: #1ab394;
}
.member-list-page .dataTables_wrapper table.dataTable,
.jer-page .dataTables_wrapper table.dataTable,
.vt-page .dataTables_wrapper table.dataTable,
.loan-ledger-page .dataTables_wrapper table.dataTable {
    width: 100% !important;
    margin: 0 !important;
    border-collapse: separate !important;
    border-spacing: 0;
}
.member-list-page .dataTables_wrapper table.dataTable > thead > tr > th,
.jer-page .dataTables_wrapper table.dataTable > thead > tr > th,
.vt-page .dataTables_wrapper table.dataTable > thead > tr > th,
.loan-ledger-page .dataTables_wrapper table.dataTable > thead > tr > th {
    background: linear-gradient(180deg, #fbfcfd 0%, #f4f7f8 100%);
    border-bottom: 1px solid #e7eaec !important;
    color: #5a5e63;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .02em;
    white-space: normal;
    line-height: 1.25;
    padding: 8px 6px !important;
    vertical-align: middle;
}
.member-list-page .dataTables_wrapper table.dataTable > tbody > tr > td,
.jer-page .dataTables_wrapper table.dataTable > tbody > tr > td,
.vt-page .dataTables_wrapper table.dataTable > tbody > tr > td,
.loan-ledger-page .dataTables_wrapper table.dataTable > tbody > tr > td {
    padding: 6px 6px !important;
    font-size: 12px;
    vertical-align: middle;
}
.member-list-page .dataTables_wrapper table.dataTable > thead > tr > th,
.member-list-page .dataTables_wrapper table.dataTable > tbody > tr > td,
.member-list-page .dataTables_wrapper table.dataTable > tfoot > tr > th,
.member-list-page .dataTables_wrapper table.dataTable > tfoot > tr > td,
.jer-page .dataTables_wrapper table.dataTable > thead > tr > th,
.jer-page .dataTables_wrapper table.dataTable > tbody > tr > td,
.jer-page .dataTables_wrapper table.dataTable > tfoot > tr > th,
.jer-page .dataTables_wrapper table.dataTable > tfoot > tr > td,
.vt-page .dataTables_wrapper table.dataTable > thead > tr > th,
.vt-page .dataTables_wrapper table.dataTable > tbody > tr > td,
.loan-ledger-page .dataTables_wrapper table.dataTable > thead > tr > th,
.loan-ledger-page .dataTables_wrapper table.dataTable > tbody > tr > td,
.loan-ledger-page .dataTables_wrapper table.dataTable > tfoot > tr > th,
.loan-ledger-page .dataTables_wrapper table.dataTable > tfoot > tr > td {
    border: 1px solid #e7eaec !important;
}
.member-list-page .dataTables_wrapper table.dataTable > thead > tr > th,
.jer-page .dataTables_wrapper table.dataTable > thead > tr > th,
.vt-page .dataTables_wrapper table.dataTable > thead > tr > th,
.loan-ledger-page .dataTables_wrapper table.dataTable > thead > tr > th {
    border-top: 0 !important;
}
.member-list-page .dataTables_wrapper table.dataTable > thead > tr > th:not(:first-child),
.member-list-page .dataTables_wrapper table.dataTable > tbody > tr > td:not(:first-child),
.member-list-page .dataTables_wrapper table.dataTable > tfoot > tr > th:not(:first-child),
.member-list-page .dataTables_wrapper table.dataTable > tfoot > tr > td:not(:first-child),
.jer-page .dataTables_wrapper table.dataTable > thead > tr > th:not(:first-child),
.jer-page .dataTables_wrapper table.dataTable > tbody > tr > td:not(:first-child),
.jer-page .dataTables_wrapper table.dataTable > tfoot > tr > th:not(:first-child),
.jer-page .dataTables_wrapper table.dataTable > tfoot > tr > td:not(:first-child),
.vt-page .dataTables_wrapper table.dataTable > thead > tr > th:not(:first-child),
.vt-page .dataTables_wrapper table.dataTable > tbody > tr > td:not(:first-child),
.loan-ledger-page .dataTables_wrapper table.dataTable > thead > tr > th:not(:first-child),
.loan-ledger-page .dataTables_wrapper table.dataTable > tbody > tr > td:not(:first-child),
.loan-ledger-page .dataTables_wrapper table.dataTable > tfoot > tr > th:not(:first-child),
.loan-ledger-page .dataTables_wrapper table.dataTable > tfoot > tr > td:not(:first-child) {
    border-left: 0 !important;
}
.member-list-page .dataTables_wrapper table.dataTable > tfoot > tr > th,
.member-list-page .dataTables_wrapper table.dataTable > tfoot > tr > td,
.jer-page .dataTables_wrapper table.dataTable > tfoot > tr > th,
.jer-page .dataTables_wrapper table.dataTable > tfoot > tr > td,
.loan-ledger-page .dataTables_wrapper table.dataTable > tfoot > tr > th,
.loan-ledger-page .dataTables_wrapper table.dataTable > tfoot > tr > td {
    padding: 8px 6px !important;
    font-size: 12px;
    background: #f8fafb;
    border-top: 2px solid #e7eaec;
}
.member-list-page .dataTables_wrapper table.dataTable > tbody > tr.odd,
.jer-page .dataTables_wrapper table.dataTable > tbody > tr.odd,
.vt-page .dataTables_wrapper table.dataTable > tbody > tr.odd,
.loan-ledger-page .dataTables_wrapper table.dataTable > tbody > tr.odd {
    background-color: #fcfdfd !important;
}
.member-list-page .dataTables_wrapper table.dataTable > tbody > tr.even,
.jer-page .dataTables_wrapper table.dataTable > tbody > tr.even,
.vt-page .dataTables_wrapper table.dataTable > tbody > tr.even,
.loan-ledger-page .dataTables_wrapper table.dataTable > tbody > tr.even {
    background-color: #fff !important;
}
.member-list-page .dataTables_wrapper table.dataTable > tbody > tr:hover,
.member-list-page .dataTables_wrapper table.dataTable > tbody > tr.odd:hover,
.member-list-page .dataTables_wrapper table.dataTable > tbody > tr.even:hover,
.jer-page .dataTables_wrapper table.dataTable > tbody > tr:hover,
.jer-page .dataTables_wrapper table.dataTable > tbody > tr.odd:hover,
.jer-page .dataTables_wrapper table.dataTable > tbody > tr.even:hover,
.vt-page .dataTables_wrapper table.dataTable > tbody > tr:hover,
.vt-page .dataTables_wrapper table.dataTable > tbody > tr.odd:hover,
.vt-page .dataTables_wrapper table.dataTable > tbody > tr.even:hover,
.loan-ledger-page .dataTables_wrapper table.dataTable > tbody > tr:hover,
.loan-ledger-page .dataTables_wrapper table.dataTable > tbody > tr.odd:hover,
.loan-ledger-page .dataTables_wrapper table.dataTable > tbody > tr.even:hover {
    background-color: #f3fbf8 !important;
}
.member-list-page .dataTables_wrapper table.dataTable > tbody > tr:hover > td,
.member-list-page .dataTables_wrapper table.dataTable > tbody > tr.odd:hover > td,
.member-list-page .dataTables_wrapper table.dataTable > tbody > tr.even:hover > td,
.jer-page .dataTables_wrapper table.dataTable > tbody > tr:hover > td,
.jer-page .dataTables_wrapper table.dataTable > tbody > tr.odd:hover > td,
.jer-page .dataTables_wrapper table.dataTable > tbody > tr.even:hover > td,
.vt-page .dataTables_wrapper table.dataTable > tbody > tr:hover > td,
.vt-page .dataTables_wrapper table.dataTable > tbody > tr.odd:hover > td,
.vt-page .dataTables_wrapper table.dataTable > tbody > tr.even:hover > td,
.loan-ledger-page .dataTables_wrapper table.dataTable > tbody > tr:hover > td,
.loan-ledger-page .dataTables_wrapper table.dataTable > tbody > tr.odd:hover > td,
.loan-ledger-page .dataTables_wrapper table.dataTable > tbody > tr.even:hover > td {
    background-color: #f3fbf8 !important;
}
.member-list-page .dataTables_wrapper table.dataTable.no-footer,
.jer-page .dataTables_wrapper table.dataTable.no-footer,
.vt-page .dataTables_wrapper table.dataTable.no-footer,
.loan-ledger-page .dataTables_wrapper table.dataTable.no-footer {
    border-bottom: 0;
}
</style>
