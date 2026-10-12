<?php
$flash_message = isset($message) && $message !== '' ? $message : $this->session->flashdata('message');
$flash_warning = isset($warning) && $warning !== '' ? $warning : $this->session->flashdata('warning');
if ($flash_message) {
    echo '<div class="alert alert-info">' . htmlspecialchars($flash_message, ENT_QUOTES, 'UTF-8') . '</div>';
}
if ($flash_warning) {
    echo '<div class="alert alert-warning">' . htmlspecialchars($flash_warning, ENT_QUOTES, 'UTF-8') . '</div>';
}
