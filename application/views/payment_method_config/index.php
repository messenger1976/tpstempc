<?php
if (isset($message) && !empty($message)) {
    echo '<div class="alert alert-info">' . $message . '</div>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div class="alert alert-info">' . $this->session->flashdata('message') . '</div>';
}
if (isset($warning) && !empty($warning)) {
    echo '<div class="alert alert-warning">' . $warning . '</div>';
} else if ($this->session->flashdata('warning') != '') {
    echo '<div class="alert alert-warning">' . $this->session->flashdata('warning') . '</div>';
}
?>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Payment Method GL Account Configuration</h5>
                    <p class="text-muted small">Configure which COA account each payment method should use in journal entries</p>
                </div>
                <div class="ibox-content">
                    
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <strong>Payment Methods and GL Account Mapping</strong>
                                </div>
                                <div class="panel-body">
                                    <?php if (!empty($payment_methods)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th width="20%">Payment Method</th>
                                                        <th width="40%">Description</th>
                                                        <th width="20%">GL Account Code</th>
                                                        <th width="20%">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($payment_methods as $method): ?>
                                                        <tr>
                                                            <td><strong><?php echo htmlspecialchars($method->name); ?></strong></td>
                                                            <td><?php echo htmlspecialchars($method->description); ?></td>
                                                            <td>
                                                                <?php if ($method->gl_account_code): ?>
                                                                    <span class="label label-success"><?php echo htmlspecialchars($method->gl_account_code); ?></span>
                                                                <?php else: ?>
                                                                    <span class="label label-warning">Not Configured</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editModal_<?php echo $method->id; ?>">
                                                                    <i class="fa fa-edit"></i> Configure
                                                                </button>
                                                            </td>
                                                        </tr>

                                                        <!-- Edit Modal for each payment method -->
                                                        <div class="modal fade" id="editModal_<?php echo $method->id; ?>" tabindex="-1" role="dialog">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                                        <h4 class="modal-title">Configure GL Account for <?php echo htmlspecialchars($method->name); ?></h4>
                                                                    </div>
                                                                    <form method="POST" action="<?php echo site_url('payment_method_config/save'); ?>">
                                                                        <div class="modal-body">
                                                                            <div class="form-group">
                                                                                <label>Select GL Account:</label>
                                                                                <input type="hidden" name="payment_method_id" value="<?php echo $method->id; ?>">
                                                                                <select name="gl_account_code" class="form-control" required>
                                                                                    <option value="">-- Select Account --</option>
                                                                                    <?php 
                                                                                    if (!empty($account_list)) {
                                                                                        foreach ($account_list as $account_type => $accounts) {
                                                                                            echo '<optgroup label="' . htmlspecialchars($account_type) . '">';
                                                                                            foreach ($accounts as $account) {
                                                                                                $selected = ($method->gl_account_code == $account->account) ? 'selected' : '';
                                                                                                echo '<option value="' . $account->account . '" ' . $selected . '>' . $account->account . ' - ' . htmlspecialchars($account->name) . '</option>';
                                                                                            }
                                                                                            echo '</optgroup>';
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No payment methods available.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="alert alert-info">
                                <h4><i class="fa fa-info-circle"></i> How It Works</h4>
                                <ul>
                                    <li>Each payment method can be mapped to a specific GL account in your Chart of Accounts</li>
                                    <li>For example: "CASH" → Account 11110 (Cash in Hand)</li>
                                    <li>When you create a cash receipt with "CASH" as the payment method, the journal entry will automatically debit the configured GL account</li>
                                    <li>This ensures consistent accounting and accurate financial reporting</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

