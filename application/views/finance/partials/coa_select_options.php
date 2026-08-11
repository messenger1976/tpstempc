<?php
/**
 * Hierarchical Chart of Accounts <option> list (matches finance_account_list_print layout).
 *
 * Expected vars:
 *   $account_list     - from finance_model->account_chart_by_accounttype()
 *   $selected_account - optional account code to mark selected
 */
if (!isset($account_list) || !is_array($account_list) || empty($account_list)) {
    return;
}
if (!isset($selected_account)) {
    $selected_account = '';
}

$nbsp = "\xC2\xA0"; // UTF-8 non-breaking space (survives htmlspecialchars)

/**
 * Build map of account codes that have at least one child in $accounts.
 */
$find_parent_codes = function ($accounts) {
    $parent_codes = array();
    $codes = array();
    foreach ($accounts as $a) {
        $codes[] = (string)$a->account;
    }

    foreach ($accounts as $a) {
        $code = (string)$a->account;

        // Explicit parent link
        foreach ($accounts as $other) {
            if (isset($other->account_parent)
                && (string)$other->account_parent !== ''
                && (string)$other->account_parent !== '0'
                && (string)$other->account_parent === $code
                && (string)$other->account !== $code
            ) {
                $parent_codes[$code] = true;
                break;
            }
        }
        if (isset($parent_codes[$code])) {
            continue;
        }

        // Infer from account-number hierarchy (e.g. 11100 → 11110/11130, 11130 → 11131)
        if (substr($code, -1) === '0') {
            $stem = rtrim($code, '0');
            if ($stem !== '') {
                foreach ($codes as $other_code) {
                    if ($other_code !== $code && strpos($other_code, $stem) === 0) {
                        $parent_codes[$code] = true;
                        break;
                    }
                }
            }
        }
    }

    return $parent_codes;
};

foreach ($account_list as $type_data) {
    if (empty($type_data['data'])) {
        continue;
    }

    $account_type_info = $type_data['info'];
    $accounts = $type_data['data'];

    $optgroup_label = strtoupper($account_type_info->name) . ' (Type: ' . $account_type_info->account . ')';
    echo '<optgroup label="' . htmlspecialchars($optgroup_label, ENT_QUOTES) . '">';

    // Group accounts by sub_account_type
    $accounts_by_subtype = array();
    $sub_type_info_map = array();
    foreach ($accounts as $account) {
        $sub_type_key = (isset($account->sub_account_type) && $account->sub_account_type !== '' && $account->sub_account_type !== null)
            ? $account->sub_account_type
            : 'no_subtype';
        if (!isset($accounts_by_subtype[$sub_type_key])) {
            $accounts_by_subtype[$sub_type_key] = array();
            if ($sub_type_key != 'no_subtype') {
                $sub_type_result = $this->finance_model->account_type_sub(null, $account_type_info->account, $sub_type_key);
                if ($sub_type_result && $sub_type_result->num_rows() > 0) {
                    $sub_type_info_map[$sub_type_key] = $sub_type_result->row();
                }
            }
        }
        $accounts_by_subtype[$sub_type_key][] = $account;
    }

    // Sort sub types by sub_account code (ASC); no_subtype last
    uksort($accounts_by_subtype, function ($a, $b) use ($sub_type_info_map) {
        if ($a == 'no_subtype') {
            return 1;
        }
        if ($b == 'no_subtype') {
            return -1;
        }
        $sub_account_a = isset($sub_type_info_map[$a]->sub_account) ? (int)$sub_type_info_map[$a]->sub_account : 0;
        $sub_account_b = isset($sub_type_info_map[$b]->sub_account) ? (int)$sub_type_info_map[$b]->sub_account : 0;
        return $sub_account_a - $sub_account_b;
    });

    foreach ($accounts_by_subtype as $sub_type_key => $subtype_accounts) {
        if ($sub_type_key != 'no_subtype' && isset($sub_type_info_map[$sub_type_key])) {
            $sub_type = $sub_type_info_map[$sub_type_key];
            $sub_label = str_repeat($nbsp, 2) . $sub_type->name . ' (Sub Type: ' . $sub_type->sub_account . ')';
            echo '<option value="" disabled="disabled" data-coa-header="1">' . htmlspecialchars($sub_label) . '</option>';
        }

        $parent_codes = $find_parent_codes($subtype_accounts);

        foreach ($subtype_accounts as $account) {
            $account_str = (string)$account->account;
            $level = 0;

            if (strlen($account_str) >= 4) {
                $last_4 = substr($account_str, -4);
                $last_2 = substr($account_str, -2);
                $last_1 = substr($account_str, -1);

                if ($last_4 == '0000') {
                    $level = 0;
                } else if ($last_2 == '00') {
                    $level = 1;
                } else if ($last_1 == '0') {
                    $level = 2;
                } else {
                    $level = 3;
                }
            }

            if ($sub_type_key != 'no_subtype') {
                $level = max(1, $level + 1);
            }

            $is_parent = isset($parent_codes[$account_str]);
            $indent = str_repeat($nbsp, $level * 2);
            $label = $indent . $account->account . ' - ' . $account->name;

            $attrs = 'value="' . htmlspecialchars($account->account, ENT_QUOTES) . '"';
            if ($is_parent) {
                $attrs .= ' disabled="disabled" data-is-parent="1" class="coa-parent-account" style="font-weight:bold;"';
            } else if ((string)$selected_account !== '' && (string)$selected_account === $account_str) {
                $attrs .= ' selected="selected"';
            }

            echo '<option ' . $attrs . '>' . htmlspecialchars($label) . '</option>';
        }
    }

    echo '</optgroup>';
}
