<?php
/**
 * TPSTEMPC-13 - Co-Makers Statement & Promissory Note: browser preview / print page.
 *
 * Markup and print CSS follow the source document
 * (TPSTEMPC-13_CoMakers_PromissoryNote.html); values come from
 * Loan_model::loan_form_prefill().
 *
 * Expects: $form, $maker, $co_makers, $company, $reference, $pdf_url, $forms_url.
 */
$this->load->view('loan/print/partials/loan_form_open', array(
    'page_title' => isset($page_title) ? $page_title : 'TAPSTEMCO - Form TPSTEMPC-13 Co-Makers Statement & Promissory Note',
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

$plain_fields = array(
    array('label' => 'Name', 'key' => 'name'),
    array('label' => 'Address', 'key' => 'address'),
    array('label' => 'Employer', 'key' => 'employer'),
    array('label' => 'Station/School', 'key' => 'station'),
    array('label' => 'Position', 'key' => 'position'),
);
$tail_fields = array(
    array('label' => 'Name of Spouse', 'key' => 'spouse'),
    array('label' => 'Occupation', 'key' => 'spouse_occupation'),
);
?>

<div class="flex justify-between items-center my-4 text-xs font-semibold">
    <div>Form No. TPSTEMPC-13</div>
    <div class="flex items-center">
        <span class="mr-2">Date:</span><?php echo $input($form['date'], 'w-40 text-center px-1'); ?>
    </div>
</div>

<div class="text-center my-4">
    <h2 class="text-sm font-extrabold tracking-wider uppercase">CO-MAKERS STATEMENT</h2>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-xs mb-6">
    <?php for ($slot = 1; $slot <= 2; $slot++) { ?>
        <div class="space-y-2">
            <?php foreach ($plain_fields as $field) { ?>
                <div class="flex items-baseline">
                    <span class="w-28 flex-shrink-0"><?php echo htmlspecialchars($field['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php echo $input($form['comaker' . $slot . '_' . $field['key']], 'flex-1'); ?>
                </div>
            <?php } ?>

            <div class="flex items-baseline gap-1">
                <span class="w-28 flex-shrink-0">Salary</span>
                <?php echo $input($form['comaker' . $slot . '_salary'], 'w-20'); ?>
                <span class="ml-2">Net Pay</span>
                <?php echo $input($form['comaker' . $slot . '_net_pay'], 'flex-1'); ?>
            </div>

            <div>
                <span class="font-semibold block my-1">
                    <?php echo $slot === 1 ? 'Obligations with TPSTEMPC:' : 'Obligation with TSTEMPC:'; ?>
                </span>
                <div class="flex items-baseline gap-1 pl-2">
                    <span>Loan</span>
                    <?php echo $input($form['comaker' . $slot . '_obligation_loan'], 'w-24'); ?>
                    <span class="ml-2">Consumer</span>
                    <?php echo $input($form['comaker' . $slot . '_obligation_consumer'], 'flex-1'); ?>
                </div>
            </div>

            <?php foreach ($tail_fields as $field) { ?>
                <div class="flex items-baseline">
                    <span class="w-28 flex-shrink-0"><?php echo htmlspecialchars($field['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php echo $input($form['comaker' . $slot . '_' . $field['key']], 'flex-1'); ?>
                </div>
            <?php } ?>

            <div class="flex items-baseline">
                <span class="w-28 flex-shrink-0">No. of Dependents<?php echo $slot === 1 ? ':' : ''; ?></span>
                <?php echo $input($form['comaker' . $slot . '_dependents'], 'flex-1'); ?>
            </div>

            <div class="pt-8 text-center">
                <?php echo $input($form['comaker' . $slot . '_name'], 'w-full text-center py-1'); ?>
                <span class="text-[11px] font-semibold text-gray-700 block mt-1">Signature of Co-Maker</span>
            </div>
        </div>
    <?php } ?>
</div>

<div class="text-center text-xs tracking-tighter overflow-hidden text-gray-500 my-4 select-none">
    ***********************************************************************************************************************************************
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-4 text-xs font-semibold">
    <div class="flex items-center">
        <span class="text-sm">(P</span><?php echo $input($form['amount'], 'flex-1 mx-1 text-center font-bold'); ?><span class="text-sm">)</span>
    </div>
    <div class="space-y-2">
        <div class="flex items-baseline justify-end">
            <span class="mr-2">Date Released</span><?php echo $input($form['date_released'], 'w-48 text-center'); ?>
        </div>
        <div class="flex items-baseline justify-end">
            <span class="mr-2">Date Due:</span><?php echo $input($form['date_due'], 'w-48 text-center'); ?>
        </div>
        <div class="flex items-baseline justify-end pt-1">
            <span class="mr-2">PN NO.</span><?php echo $input($form['pn_no'], 'w-48 text-center'); ?>
        </div>
    </div>
</div>

<div class="text-center my-4">
    <h2 class="text-sm font-extrabold tracking-wider uppercase">PROMISSORY NOTE</h2>
</div>

<div class="space-y-3 text-xs text-justify leading-relaxed">
    <p>
        For value received, I/We jointly and severally promise to pay the <strong class="font-bold uppercase">TALIBON PUBLIC SCHOOL TEACHERS &amp; EMPLOYEES MULTIPURPOSE COOPERATIVE, TALIBON, BOHOL</strong> the sum of P
        <?php echo $input($form['pn_amount'], 'w-44 text-center px-1'); ?>
        with interest of
        <?php echo $input($form['pn_interest_rate'], 'w-28 text-center px-1'); ?>
        payable in
        <?php echo $input($form['pn_installments'], 'w-16 text-center px-1'); ?>
        monthly equal installments and a like amount every month thereafter until the full amount has been paid and on unpaid balance at the rate of <span class="underline font-bold"><?php echo htmlspecialchars($form['pn_penalty_rate'], ENT_QUOTES, 'UTF-8'); ?>% per month.</span>
    </p>

    <div class="space-y-1 pl-4 pt-1">
        <div class="flex flex-wrap items-baseline gap-1">
            <span>Pay starts on</span><?php echo $input($form['pn_pay_start'], 'w-52 text-center'); ?><span>and ends on</span><?php echo $input($form['pn_pay_end'], 'flex-1 min-w-[150px] text-center'); ?>.
        </div>
        <div class="flex items-baseline gap-1">
            <span>At P</span><?php echo $input($form['pn_monthly'], 'w-44 text-center'); ?><span>monthly installments.</span>
        </div>
    </div>

    <p class="pt-2">In case of default in payments as herein agreed, the entire balance of this note shall become immediately due and payable at the option of the holder. Each party to this note whether as maker/co-maker/guarantor severally waives presentment of payments, demands, protests and notice of protest and dishonor of the same.</p>
    <p>It is agreed by party hereto, that in case payment shall not be made at maturity, he shall pay the cost of collections, and attorney's fee not be in the amount equal to twenty percent (20%) of the principal and interest due on this note but such in no event to be less than ten pesos (10.00).</p>
    <p>In case of judicial execution of this obligation in any part of it, the debtor waives all his rights under the provisions of Rule 3 Section 13 and Rule 39 Section 12 of the Rules of the Court.</p>
</div>

<?php if (trim((string) $form['remarks']) !== '') { ?>
    <p class="text-[10px] text-gray-500 mt-4">Remarks: <?php echo htmlspecialchars($form['remarks'], ENT_QUOTES, 'UTF-8'); ?></p>
<?php } ?>

<div class="mt-8 space-y-4">
    <div class="grid grid-cols-2 gap-x-8 gap-y-4">
        <div class="text-center">
            <?php echo $input($form['maker_name'], 'w-full text-center py-1'); ?>
            <span class="text-[11px] font-semibold text-gray-700 block mt-1">Signature of Maker</span>
        </div>
        <div class="text-center">
            <?php echo $input($form['maker_address'], 'w-full text-center py-1'); ?>
            <span class="text-[11px] font-semibold text-gray-700 block mt-1">Address</span>
        </div>
        <div class="text-center">
            <?php echo $input($form['comaker1_name'], 'w-full text-center py-1'); ?>
            <span class="text-[11px] font-semibold text-gray-700 block mt-1">Signature of Co-Maker</span>
        </div>
        <div class="text-center">
            <?php echo $input($form['comaker1_address'], 'w-full text-center py-1'); ?>
            <span class="text-[11px] font-semibold text-gray-700 block mt-1">Address</span>
        </div>
        <div class="text-center">
            <?php echo $input($form['comaker2_name'], 'w-full text-center py-1'); ?>
            <span class="text-[11px] font-semibold text-gray-700 block mt-1">Signature of Co-Maker</span>
        </div>
        <div class="text-center">
            <?php echo $input($form['comaker2_address'], 'w-full text-center py-1'); ?>
            <span class="text-[11px] font-semibold text-gray-700 block mt-1">Address</span>
        </div>
    </div>
</div>

<?php $this->load->view('loan/print/partials/loan_form_close'); ?>
