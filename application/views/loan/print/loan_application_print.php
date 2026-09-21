<?php
/**
 * TPSTEMPC-12 - Application for Loan: browser preview / print page.
 *
 * Markup and print CSS follow the source document
 * (TPSTEMPC-12_Loan_Application.html); values come from Loan_model::loan_form_prefill().
 *
 * Expects: $form, $maker, $co_makers, $company, $reference, $pdf_url, $forms_url.
 */
$this->load->view('loan/print/partials/loan_form_open', array(
    'page_title' => isset($page_title) ? $page_title : 'TAPSTEMCO - Form TPSTEMPC-12 Application for Loan',
    'reference' => isset($reference) ? $reference : '',
    'pdf_url' => isset($pdf_url) ? $pdf_url : '',
    'forms_url' => isset($forms_url) ? $forms_url : '',
    'company' => isset($company) ? $company : null,
    'form_no_header' => isset($form_no_header) ? $form_no_header : 'TPSTEMPC-12',
));

/* Read-only inputs: same elements/classes as the source form, so the preview and
   the printed sheet keep the exact layout, but the values are not edited here. */
$input = function ($value, $class = '') {
    return '<input type="text" readonly="readonly" class="underline-input ' . $class . '" value="'
        . htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8') . '">';
};
$checkbox = function ($checked) {
    return '<input type="checkbox" class="w-4 h-4 text-blue-600 rounded print-exact"'
        . (!empty($checked) ? ' checked="checked"' : '') . ' onclick="return false;">';
};

$balance_rows = array(
    array(
        'label' => 'Maker/Borrower',
        'name' => $maker['name'],
        'cbu' => $form['maker_cbu'],
        'savings' => $form['maker_savings'],
        'collateral' => $form['maker_collateral'],
        'loan_balance' => $form['maker_loan_balance'],
        'consumer_balance' => $form['maker_consumer_balance'],
        'migs' => $form['maker_migs'],
        'other_info' => $form['maker_other_info'],
    ),
);
for ($slot = 1; $slot <= 2; $slot++) {
    $person = isset($co_makers[$slot - 1]) ? $co_makers[$slot - 1] : null;
    $balance_rows[] = array(
        'label' => 'Co-Maker',
        'name' => $person ? $person['name'] : '',
        'cbu' => $form['comaker' . $slot . '_cbu'],
        'savings' => $form['comaker' . $slot . '_savings'],
        'collateral' => $form['comaker' . $slot . '_collateral'],
        'loan_balance' => $form['comaker' . $slot . '_loan_balance'],
        'consumer_balance' => $form['comaker' . $slot . '_consumer_balance'],
        'migs' => $form['comaker' . $slot . '_migs'],
        'other_info' => $form['comaker' . $slot . '_other_info'],
    );
}

$types_row_one = array(
    'salary' => 'Salary Loan',
    'supervise' => 'Supervise Loan',
    'bonus' => 'Bonus Loan',
    'cashadvance' => 'Cash Advance',
    'emergency' => 'Emergency Loan',
);
$types_row_two = array(
    'gadget' => 'Gadget Loan',
    'car' => 'Car Loan',
);
?>

<div class="text-center my-6">
    <h2 class="text-lg font-extrabold tracking-wide uppercase border-b-2 border-gray-800 inline-block pb-0.5">Application for Loan</h2>
</div>

<div class="mb-6">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-sm">
        <?php foreach ($types_row_one as $type_key => $type_label) { ?>
            <label class="flex items-center space-x-2">
                <?php echo $checkbox(!empty($form['loan_type_' . $type_key])); ?>
                <span><?php echo htmlspecialchars($type_label, ENT_QUOTES, 'UTF-8'); ?></span>
            </label>
        <?php } ?>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm mt-3 items-center">
        <?php foreach ($types_row_two as $type_key => $type_label) { ?>
            <label class="flex items-center space-x-2">
                <?php echo $checkbox(!empty($form['loan_type_' . $type_key])); ?>
                <span><?php echo htmlspecialchars($type_label, ENT_QUOTES, 'UTF-8'); ?></span>
            </label>
        <?php } ?>
        <div class="flex items-center space-x-2 md:col-span-1">
            <?php echo $checkbox(!empty($form['loan_type_others'])); ?>
            <span class="whitespace-nowrap">Others pls. specify:</span>
            <?php echo $input($form['loan_type_others'], 'flex-1 px-1 text-sm'); ?>
        </div>
    </div>
</div>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center my-6 gap-4 text-sm font-medium">
    <div class="flex items-center w-full md:w-auto">
        <span class="mr-2">PASSBOOK No.</span><?php echo $input($form['passbook_no'], 'w-48 px-2'); ?>
    </div>
    <div class="flex items-center w-full md:w-auto">
        <span class="mr-2">Date:</span><?php echo $input($form['date'], 'w-48 px-2'); ?>
    </div>
</div>

<div class="space-y-4 text-sm leading-relaxed my-6">
    <div class="flex flex-wrap items-baseline gap-y-2">
        <span>I hereby apply for a loan of &#8369;</span>
        <?php echo $input($form['amount'], 'w-40 px-2 mx-1'); ?>
        <span>for a period of</span>
        <?php echo $input($form['period'], 'w-24 px-2 mx-1 text-center'); ?>
        <span>months to be repaid in monthly installments of &#8369;</span>
        <?php echo $input($form['monthly_installment'], 'w-36 px-2 mx-1'); ?>
        <span>each plus interest. I prefer the first payment to fall a month after the loan is granted.</span>
    </div>

    <div>
        <p class="mb-1">I desire this loan for the following provident/productive purpose:</p>
        <p class="text-xs text-gray-500 italic mb-1">(Please explain fully)</p>
        <?php echo $input($form['purpose'], 'w-full px-2 py-1 mb-2'); ?>
        <?php echo $input('', 'w-full px-2 py-1'); ?>
    </div>

    <div class="flex flex-wrap items-baseline gap-2 pt-2">
        <span>Co-Makers security offered:</span>
        <?php echo $input($form['co_makers_security'], 'flex-1 min-w-[250px] px-2 py-1'); ?>
    </div>
</div>

<p class="text-xs text-justify leading-relaxed my-6">
    I/We Hereby certify that my/our treasury warrant is not confined in any loaning agencies of the province and that all statements made including those on the reverse side herein are true and complete and submitted for the purpose of obtaining credit.
</p>

<div class="overflow-x-auto my-6">
    <table class="w-full text-xs text-center border-collapse">
        <thead>
            <tr class="border-b-2 border-gray-400">
                <th class="p-2 font-bold w-1/4 text-left">Name</th>
                <th class="p-2 font-bold">CBU</th>
                <th class="p-2 font-bold">Savings Deposit</th>
                <th class="p-2 font-bold">Collateral</th>
                <th class="p-2 font-bold">Loan Balance</th>
                <th class="p-2 font-bold">Consumer Balance</th>
                <th class="p-2 font-bold">MIGS</th>
                <th class="p-2 font-bold">Other Info.</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php foreach ($balance_rows as $row) { ?>
                <tr>
                    <td class="p-2 font-semibold text-left">
                        <?php echo htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php if ($row['name'] !== '') { ?>
                            <span class="block text-[10px] font-normal text-gray-500"><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php } ?>
                    </td>
                    <td class="p-1"><?php echo $input($row['cbu'], 'w-full text-center'); ?></td>
                    <td class="p-1"><?php echo $input($row['savings'], 'w-full text-center'); ?></td>
                    <td class="p-1"><?php echo $input($row['collateral'], 'w-full text-center'); ?></td>
                    <td class="p-1"><?php echo $input($row['loan_balance'], 'w-full text-center'); ?></td>
                    <td class="p-1"><?php echo $input($row['consumer_balance'], 'w-full text-center'); ?></td>
                    <td class="p-1"><?php echo $input($row['migs'], 'w-full text-center'); ?></td>
                    <td class="p-1"><?php echo $input($row['other_info'], 'w-full text-center'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div class="border-t-2 border-b-2 border-gray-800 h-1 my-6"></div>

<p class="text-xs text-justify leading-relaxed my-4">
    We hereby certify that the Maker and Co-Makers of this application for loan have the following savings and obligations with the TAPSTEMCO and we approved the loan amount and the terms and conditions by the applicant.
</p>

<div class="mt-8 space-y-8">
    <div>
        <span class="text-xs font-medium italic block mb-6">Recommending Approval:</span>
        <div class="flex justify-center">
            <div class="w-64 text-center">
                <?php echo $input($form['sign_loan_officer'], 'w-full text-center mb-1'); ?>
                <span class="text-xs font-semibold italic">Loan Officer</span>
            </div>
        </div>
    </div>

    <div>
        <span class="text-xs font-medium italic block mb-6">Approved:</span>
        <div class="grid grid-cols-2 gap-x-12 gap-y-8 max-w-2xl mx-auto">
            <div class="text-center">
                <?php echo $input($form['sign_treasurer'], 'w-full text-center mb-1'); ?>
                <span class="text-xs font-semibold italic">Treasurer</span>
            </div>
            <div class="text-center">
                <?php echo $input($form['sign_crecom_chairman'], 'w-full text-center mb-1'); ?>
                <span class="text-xs font-semibold italic">CRECOM Chairman</span>
            </div>
            <div class="text-center">
                <?php echo $input($form['sign_manager'], 'w-full text-center mb-1'); ?>
                <span class="text-xs font-semibold italic">Manager</span>
            </div>
            <div class="text-center">
                <?php echo $input($form['sign_chairman'], 'w-full text-center mb-1'); ?>
                <span class="text-xs font-semibold italic">Chairman</span>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('loan/print/partials/loan_form_close'); ?>
