<?php
$e = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$base = site_url(current_lang() . '/map_location');
?>
<div class="col-lg-12">
    <?php $this->load->view('map_location/_alerts'); ?>

    <p><a href="<?php echo $base . '/index'; ?>"><i class="fa fa-arrow-left"></i> Back to Map Locations</a></p>

    <h3 style="margin-top:0;"><?php echo $e($company->name); ?></h3>
    <p class="text-muted">Address: <strong><?php echo !empty($company->address) ? $e($company->address) : '—'; ?></strong></p>

    <div class="alert alert-info">
        <?php if ($has_office_pin) { ?>
            The dashboard and collector maps use this office pin, and routes start here when GPS is off.
            <?php if (!empty($company->map_updated_at)) { ?>(Set <?php echo $e($company->map_updated_at); ?>.)<?php } ?>
        <?php } else { ?>
            No office pin set. The maps currently guess the office from the address geocode cache, or a built-in default.
        <?php } ?>
    </div>

    <form method="post" action="<?php echo $base . '/office'; ?>">
        <?php $this->load->view('map_location/_picker'); ?>
        <button type="submit" name="action" value="save" class="btn btn-primary"><i class="fa fa-save"></i> Save office pin</button>
        <?php if ($has_office_pin) { ?>
            <button type="submit" name="action" value="clear" class="btn btn-default"
                    onclick="return confirm('Remove the office pin?');">
                <i class="fa fa-eraser"></i> Clear office pin
            </button>
        <?php } ?>
    </form>
</div>
