<?php
/**
 * TPSTEMPC-13 - Pledge & Authority: browser preview / print page.
 *
 * Markup and print CSS follow the source document
 * (TPSTEMPC-13_Pledge_Authority.html); values come from
 * Loan_model::loan_form_prefill().
 *
 * Expects: $form, $maker, $company, $reference, $pdf_url, $forms_url.
 */
$this->load->view('loan/print/partials/loan_form_open', array(
    'page_title' => isset($page_title) ? $page_title : 'TAPSTEMCO - Form TPSTEMPC-13 Pledge & Authority',
    'reference' => isset($reference) ? $reference : '',
    'pdf_url' => isset($pdf_url) ? $pdf_url : '',
    'forms_url' => isset($forms_url) ? $forms_url : '',
    'company' => isset($company) ? $company : null,
    'form_no_header' => '',
));

$input = function ($value, $class = '') {
    return '<input type="text" readonly="readonly" class="underline-input ' . $class . '" value="'
        . htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8') . '">';
};
?>

<div class="flex justify-between items-center my-8 text-sm font-semibold">
    <div>Form No. TPSTEMPC-13</div>
    <div class="flex items-center">
        <span class="mr-2">Date</span><?php echo $input($form['date'], 'w-44 text-center px-1'); ?>
    </div>
</div>

<div class="space-y-10">
    <div class="text-sm leading-relaxed text-justify">
        <p>
            I/We the undersigned hereby pledge all deposits and payments on deposits which I/We now have or hereafter may have in the Multipurpose Cooperative as security for the loan as evidenced by a note dated
            <?php echo $input($form['note_date'], 'w-36 text-center mx-1 px-1'); ?>, 20<?php echo $input($form['note_year'], 'w-12 text-center mx-1 px-1'); ?>
            in the amount of P
            <?php echo $input($form['note_amount'], 'w-36 text-center mx-1 px-1'); ?>
            executed by us payable to the <strong class="font-semibold uppercase">TALIBON PUBLIC SCHOOL TEACHERS AND EMPLOYEES MULTIPURPOSE COOPERATIVE, TALIBON, BOHOL</strong>, this pledge is given to secure the payments of the above described loan and interests, fines, costs or expenses that may accrue thereon, and I/We hereby authorize this Multi-Purpose Cooperative to apply any or all such deposits and payments of said loan interests, fines and cost or expenses.
        </p>
    </div>

    <div class="pt-6 space-y-12">
        <div class="flex justify-center">
            <div class="w-80 text-center">
                <?php echo $input($form['maker_name'], 'w-full text-center py-1 mb-1'); ?>
                <span class="text-xs font-semibold text-gray-700">Signature of Maker</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 pt-4">
            <div class="text-center">
                <?php echo $input($form['comaker1_name'], 'w-full max-w-xs text-center py-1 mb-1'); ?>
                <span class="text-xs font-semibold text-gray-700 block">Signature of Co-Maker</span>
            </div>
            <div class="text-center">
                <?php echo $input($form['comaker2_name'], 'w-full max-w-xs text-center py-1 mb-1'); ?>
                <span class="text-xs font-semibold text-gray-700 block">Signature of Co-Maker</span>
            </div>
        </div>
    </div>

    <div class="pt-8 space-y-6">
        <div class="text-center">
            <h2 class="text-base font-extrabold tracking-widest uppercase">A U T H O R I T Y</h2>
        </div>
        <div class="text-sm leading-relaxed text-justify">
            <p>
                In consideration for the Loan granted to me by the <strong class="font-semibold uppercase">TALIBON PUBLIC SCHOOL TEACHERS &amp; EMPLOYEES MULTIPURPOSE COOPERATIVE, Talibon, Bohol</strong> in the amount of P
                <?php echo $input($form['authority_amount'], 'w-48 text-center mx-1 px-1'); ?>
                in Philippine Currency, I authorize the Multipurpose Cooperative to withhold my Treasury Warrant / ATM CARD corresponding to my salary for the period to redeem to by paying the amount at the Multi-Purpose Cooperative.
            </p>
        </div>
    </div>

    <div class="pt-6 space-y-8">
        <div class="text-xs font-medium text-gray-700">With Spouse consent:</div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 pt-4">
            <div class="text-center">
                <?php echo $input($form['spouse_name'], 'w-full max-w-xs text-center py-1 mb-1'); ?>
                <span class="text-xs font-semibold text-gray-700 block">(Signature of Spouse over printed name)</span>
            </div>
            <div class="text-center">
                <?php echo $input($form['maker_name'], 'w-full max-w-xs text-center py-1 mb-1'); ?>
                <span class="text-xs font-semibold text-gray-700 block">Signature of Maker</span>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('loan/print/partials/loan_form_close'); ?>
