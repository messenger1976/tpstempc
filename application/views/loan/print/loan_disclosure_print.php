<?php
/**
 * Disclosure Statement: browser preview / print page (two copies on one sheet).
 *
 * Markup and print CSS follow the source document
 * (TPSTEMCO_Disclosure_Statement.html); the figures come from
 * Loan_model::loan_form_prefill() (form code "disclosure").
 *
 * Expects: $form, $maker, $company, $reference, $pdf_url, $forms_url.
 */
$this->load->view('loan/print/partials/loan_form_open', array(
    'page_title' => isset($page_title) ? $page_title : 'TAPSTEMCO - Disclosure Statement',
    'reference' => isset($reference) ? $reference : '',
    'pdf_url' => isset($pdf_url) ? $pdf_url : '',
    'forms_url' => isset($forms_url) ? $forms_url : '',
    'company' => isset($company) ? $company : null,
    'form_no_header' => '',
    'include_letterhead' => false,
));

/* Actual cooperative crest for each copy, inlined so the headless PDF renderer
   always has it (drawn badge only as a fallback). */
$form_logo_url = (isset($logo_override) && $logo_override !== '') ? (string) $logo_override : '';
if ($form_logo_url === '' && function_exists('loan_form_logo_src')) {
    $form_logo_url = loan_form_logo_src(($company && !empty($company->logo)) ? $company->logo : '');
}
$form_logo_ok = ($form_logo_url !== '');

$input = function ($value, $class = '') {
    return '<input type="text" readonly="readonly" class="' . $class . '" value="'
        . htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8') . '">';
};

/* Rows of the statement. "p_dr"/"p_cr" reproduce the Peso prefix the source form
   prints inside the AMOUNT cells of the Loan Granted and Net Proceeds rows. */
$rows = array(
    array('label' => 'Loan Granted (Amount Financed)', 'key' => 'loan_granted', 'cell' => 'font-semibold', 'p_dr' => TRUE, 'p_cr' => TRUE),
    array('label' => 'Less: Financed Charges', 'key' => 'financed_charges', 'cell' => 'font-semibold italic', 'p_dr' => FALSE, 'p_cr' => FALSE),
    array('label' => 'Interest on Loans (30%pa)(2%/mo)', 'key' => 'interest', 'cell' => 'pl-4', 'p_dr' => FALSE, 'p_cr' => FALSE),
    array('label' => 'Filing Fee', 'key' => 'filing_fee', 'cell' => 'pl-4', 'p_dr' => FALSE, 'p_cr' => FALSE),
    array('label' => 'Service Fee', 'key' => 'service_fee', 'cell' => 'pl-4', 'p_dr' => FALSE, 'p_cr' => FALSE),
    array('label' => 'Savings Deposit', 'key' => 'savings_deposit', 'cell' => 'pl-4', 'p_dr' => FALSE, 'p_cr' => FALSE),
    array('label' => 'Paid-Up Capital Share', 'key' => 'paid_up_share', 'cell' => 'pl-4', 'p_dr' => FALSE, 'p_cr' => FALSE),
    array('label' => 'Insurance', 'key' => 'insurance', 'cell' => 'pl-4', 'p_dr' => FALSE, 'p_cr' => FALSE),
    array('label' => 'Loan Balance', 'key' => 'loan_balance', 'cell' => 'pl-4', 'p_dr' => FALSE, 'p_cr' => FALSE),
    array('label' => 'Others:', 'key' => 'others', 'cell' => 'pl-4', 'p_dr' => FALSE, 'p_cr' => FALSE),
    array('label' => 'Total Deductions:', 'key' => 'total_deductions', 'cell' => 'font-semibold', 'p_dr' => FALSE, 'p_cr' => FALSE),
    array('label' => 'NET PROCEEDS OF LOAN', 'key' => 'net_proceeds', 'cell' => 'font-bold', 'p_dr' => FALSE, 'p_cr' => TRUE, 'top' => TRUE),
);

/**
 * One copy of the statement. Rendered twice: original on top, duplicate below.
 */
$render_copy = function () use ($form, $input, $rows, $form_logo_ok, $form_logo_url) {
    $html = '<div class="space-y-2">';

    /* ------------------------------------------------------------- header -- */
    $html .= '<div class="relative flex items-center justify-center border-b border-gray-300 pb-2">'
        . '<div class="absolute left-0 top-0 w-16 h-16 flex items-center justify-center">'
        . ($form_logo_ok
            ? '<img src="' . htmlspecialchars($form_logo_url, ENT_QUOTES, 'UTF-8') . '" alt="Cooperative logo" class="w-14 h-14 object-contain print-exact">'
            : '<div class="w-14 h-14 rounded-full border-2 border-green-700 p-0.5 flex items-center justify-center text-center text-[7px] font-bold text-green-800 relative print-exact">'
                . '<div class="absolute inset-0 rounded-full border border-yellow-500"></div>'
                . '<div><div class="text-[8px] font-extrabold text-red-600">TAPSTEMCO</div>'
                . '<div class="leading-tight text-[6px]">TEACHERS &amp; EMPLOYEES</div></div></div>')
        . '</div>'
        . '<div class="text-center">'
        . '<h1 class="text-xs md:text-sm font-bold tracking-tight text-gray-900 leading-snug uppercase">'
        . 'TALIBON PUBLIC SCHOOL TEACHERS AND EMPLOYEES<br>MULTIPURPOSE COOPERATIVE (TAPSTEMCO)'
        . '</h1>'
        . '<p class="text-[11px] text-gray-700">Purok 1 North Road San Jose, Talibon, Bohol</p>'
        . '<p class="text-[11px] text-gray-700 italic">Reg/Conf Nos. ' . htmlspecialchars(defined('TAPSTEMCO_FORM_REG_NO') ? TAPSTEMCO_FORM_REG_NO : '', ENT_QUOTES, 'UTF-8') . '</p>'
        . '</div></div>';

    $html .= '<div class="text-center"><h2 class="text-xs font-extrabold tracking-wider uppercase">DISCLOSURE STATEMENT</h2></div>';

    /* ------------------------------------------------------------- payee --- */
    $html .= '<div class="grid grid-cols-12 gap-2 text-xs font-medium">'
        . '<div class="col-span-8 space-y-1">'
        . '<div class="flex items-baseline"><span class="w-16">Payee:</span>' . $input($form['payee'], 'underline-input flex-1') . '</div>'
        . '<div class="flex items-baseline"><span class="w-16">Address:</span>' . $input($form['address'], 'underline-input flex-1') . '</div>'
        . '</div>'
        . '<div class="col-span-4 space-y-1">'
        . '<div class="flex items-baseline"><span class="w-12 text-right mr-2">No.</span>' . $input($form['no'], 'underline-input flex-1') . '</div>'
        . '<div class="flex items-baseline"><span class="w-12 text-right mr-2">Date:</span>' . $input($form['date'], 'underline-input flex-1') . '</div>'
        . '</div></div>';

    /* ------------------------------------------------------------- table --- */
    $html .= '<div class="overflow-x-auto"><table class="w-full text-xs border border-gray-800 border-collapse">'
        . '<thead>'
        . '<tr class="border-b border-gray-800">'
        . '<th rowspan="2" class="border-r border-gray-800 px-1 py-0.5 text-center font-bold w-1/2">PARTICULARS</th>'
        . '<th colspan="2" class="px-1 py-0.5 text-center font-bold">AMOUNT</th>'
        . '</tr>'
        . '<tr class="border-b border-gray-800">'
        . '<th class="border-r border-gray-800 px-1 py-0.5 text-center font-bold w-1/4">DR</th>'
        . '<th class="px-1 py-0.5 text-center font-bold w-1/4">CR</th>'
        . '</tr>'
        . '</thead><tbody class="divide-y divide-gray-800">';

    foreach ($rows as $row) {
        $extra = !empty($row['top']) ? ' border-t-2 border-gray-800' : '';
        $label_class = trim('border-r border-gray-800 px-1 py-0.5 ' . $row['cell']);

        $cell_dr = $row['p_dr']
            ? '<div class="flex items-center px-1"><span class="mr-1">P</span>' . $input($form[$row['key'] . '_dr'], 'w-full bg-transparent outline-none') . '</div>'
            : $input($form[$row['key'] . '_dr'], 'w-full bg-transparent outline-none px-1');
        $cell_cr = $row['p_cr']
            ? '<div class="flex items-center px-1"><span class="mr-1">P</span>' . $input($form[$row['key'] . '_cr'], 'w-full bg-transparent outline-none') . '</div>'
            : $input($form[$row['key'] . '_cr'], 'w-full bg-transparent outline-none px-1');

        $html .= '<tr class="' . trim($extra) . '">'
            . '<td class="' . htmlspecialchars($label_class, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td class="border-r border-gray-800 px-1 py-0.5">' . $cell_dr . '</td>'
            . '<td class="px-1 py-0.5">' . $cell_cr . '</td>'
            . '</tr>';
    }
    $html .= '</tbody></table></div>';

    /* -------------------------------------------------------- signatures --- */
    $html .= '<div class="grid grid-cols-2 gap-8 text-xs pt-1 items-end">'
        . '<div class="space-y-3">'
        . '<span class="font-semibold block">APPROVED:</span>'
        . '<div class="text-center w-48">' . $input($form['loan_officer'], 'underline-input w-full text-center')
        . '<span class="text-[10px] block mt-0.5">Loan Officer</span></div>'
        . '<div class="text-center w-48 pt-1">' . $input($form['manager_chairman'], 'underline-input w-full text-center')
        . '<span class="text-[10px] block mt-0.5">Manager/Chairman</span></div>'
        . '</div>'
        . '<div class="text-right space-y-3">'
        . '<span class="font-semibold block pr-8">CONFORME:</span>'
        . '<div class="text-center w-64 ml-auto">' . $input($form['payee_conforme'], 'underline-input w-full text-center')
        . '<span class="text-[10px] block mt-0.5">Signature of Payee over printed name</span></div>'
        . '</div></div>';

    return $html . '</div>';
};
?>

<div class="space-y-6">
    <?php echo $render_copy(); ?>

    <div class="relative flex items-center justify-center my-2">
        <div class="border-t border-dashed border-gray-400 w-full"></div>
        <span class="absolute bg-white px-3 text-[10px] text-gray-400 italic">&#9986; Cut Here (Duplicate Copy)</span>
    </div>

    <?php echo $render_copy(); ?>
</div>

<?php $this->load->view('loan/print/partials/loan_form_close'); ?>
