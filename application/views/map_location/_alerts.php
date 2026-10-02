<?php
if (isset($message) && !empty($message)) {
    echo '<div class="alert alert-success displaymessage">' . $message . '</div>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div class="alert alert-success displaymessage">' . $this->session->flashdata('message') . '</div>';
}
if (isset($warning) && !empty($warning)) {
    echo '<div class="alert alert-danger displaymessage">' . htmlspecialchars($warning, ENT_QUOTES, 'UTF-8') . '</div>';
} else if ($this->session->flashdata('warning') != '') {
    echo '<div class="alert alert-danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
}
