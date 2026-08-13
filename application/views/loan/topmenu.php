<style>
.loan-topmenu {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 0 0 18px;
    padding: 12px;
    background: #f8fafb;
    border: 1px solid #e7eaec;
    border-radius: 8px;
}
.loan-topmenu .btn {
    border-radius: 6px;
    font-weight: 600;
    box-shadow: none;
}
.loan-topmenu .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.loan-topmenu .btn-default {
    background: #fff;
    border: 1px solid #e1e5e8;
    color: #676a6c;
}
</style>
<?php
$current = $this->router->fetch_method();
$tabs = array(
    'loan_editing' => array('label' => lang('loan_basic_info'), 'icon' => 'fa-file-text-o'),
    'loan_security' => array('label' => lang('loan_security'), 'icon' => 'fa-shield'),
    'loan_guarantor' => array('label' => lang('loan_guarantors'), 'icon' => 'fa-users'),
);
?>
<div class="loan-topmenu">
    <?php foreach ($tabs as $method => $tab) {
        $is_active = ($current === $method);
        $class = $is_active ? 'btn btn-primary btn-sm' : 'btn btn-default btn-sm';
        ?>
        <a class="<?php echo $class; ?>" href="<?php echo site_url(current_lang() . '/loan/' . $method . '/' . $loanid); ?>">
            <i class="fa <?php echo $tab['icon']; ?>"></i> <?php echo $tab['label']; ?>
        </a>
    <?php } ?>
</div>
