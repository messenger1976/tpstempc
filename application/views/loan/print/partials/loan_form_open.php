<?php
/**
 * Opening part of a printable loan form: head, preview toolbar and letterhead.
 * The markup matches the source documents (Tailwind + underline-input), so the
 * browser preview and the printed sheet look like the designed form.
 *
 * Expects: $page_title, $reference, $pdf_url, $forms_url, $company,
 *          $form_no_header ('' hides the header form number).
 */
$h = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$coop_name = (defined('TAPSTEMCO_FORM_COOP_NAME'))
    ? TAPSTEMCO_FORM_COOP_NAME
    : (($company && !empty($company->name)) ? $company->name : 'Multipurpose Cooperative');
$coop_address = (defined('TAPSTEMCO_FORM_COOP_ADDRESS'))
    ? TAPSTEMCO_FORM_COOP_ADDRESS
    : (($company && !empty($company->address)) ? $company->address : '');
$coop_reg = defined('TAPSTEMCO_FORM_REG_NO') ? TAPSTEMCO_FORM_REG_NO : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $h(isset($page_title) ? $page_title : 'TAPSTEMCO Loan Form'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background-color: #ffffff; padding: 0; }
            .form-container { box-shadow: none !important; border: none !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
        }
        .underline-input { border-bottom: 1px solid #374151; outline: none; background: transparent; }
        .underline-input:focus { border-bottom-color: #2563eb; }
        .print-exact { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8 px-4 font-sans text-gray-800">

<div class="max-w-4xl mx-auto mb-4 flex flex-wrap items-center justify-end gap-2 no-print">
    <span class="mr-auto text-xs text-gray-500"><?php echo $h(isset($reference) ? $reference : ''); ?></span>
    <a href="<?php echo $h(isset($forms_url) ? $forms_url : '#'); ?>"
       class="bg-white hover:bg-gray-50 text-gray-700 font-semibold py-2 px-4 rounded border border-gray-300 shadow-sm transition text-sm">
        Loan Forms
    </a>
    <a href="<?php echo $h(isset($pdf_url) ? $pdf_url : '#'); ?>" target="_blank"
       class="bg-white hover:bg-gray-50 text-gray-700 font-semibold py-2 px-4 rounded border border-gray-300 shadow-sm transition text-sm">
        Download PDF
    </a>
    <button type="button" onclick="window.print()"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition text-sm flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
        Print Form
    </button>
</div>

<div class="form-container bg-white max-w-4xl mx-auto p-8 md:p-12 shadow-lg border border-gray-200 rounded-sm">
    <div class="relative flex flex-col md:flex-row items-center justify-between pb-4 border-b border-gray-300 gap-4">
        <div class="w-24 h-24 flex-shrink-0 flex items-center justify-center">
            <div class="w-20 h-20 rounded-full border-2 border-green-700 p-1 flex items-center justify-center text-center text-[8px] font-bold text-green-800 relative print-exact">
                <div class="absolute inset-0 rounded-full border border-yellow-500"></div>
                <div>
                    <div class="text-[9px] font-extrabold text-red-600">TAPSTEMCO</div>
                    <div class="leading-tight">TEACHERS &amp; EMPLOYEES</div>
                </div>
            </div>
        </div>

        <div class="text-center flex-1">
            <h1 class="text-base font-bold tracking-tight text-gray-900 leading-snug uppercase"><?php echo $h($coop_name); ?></h1>
            <p class="text-xs text-gray-700 mt-1"><?php echo $h($coop_address); ?></p>
            <p class="text-xs text-gray-700">CDA Registration/Confirmation No. <?php echo $h($coop_reg); ?></p>
        </div>

        <div class="w-24 flex-shrink-0 text-right self-start text-xs font-semibold text-gray-700">
            <?php if (!empty($form_no_header)) { echo 'Form No. ' . $h($form_no_header); } ?>
        </div>
    </div>
