<?php
$e = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$base = site_url(current_lang() . '/map_location');
?>
<div class="col-lg-12">
    <?php $this->load->view('map_location/_alerts'); ?>

    <p><a href="<?php echo $base . '/index'; ?>"><i class="fa fa-arrow-left"></i> Back to Map Locations</a></p>

    <div class="row">
        <div class="col-lg-8">
            <h3 style="margin-top:0;"><?php echo $e($row->address_raw); ?></h3>
            <p class="text-muted">
                Status: <strong><?php echo $e($row->geocode_status); ?></strong>
                &middot; Source: <strong><?php echo $e($row->source ?: '—'); ?></strong>
                <?php if (!empty($row->updated_at)) { ?>&middot; Updated <?php echo $e($row->updated_at); ?><?php } ?>
            </p>
            <div class="alert alert-info">
                This pin is shared by every member whose address is exactly this text, in every organisation using this database.
                Saved pins are marked <strong>manual</strong> and are never overwritten by the geocode script.
            </div>

            <form method="post" action="<?php echo $base . '/edit_address/' . intval($row->id); ?>">
                <?php $this->load->view('map_location/_picker'); ?>
                <button type="submit" name="action" value="save" class="btn btn-primary"><i class="fa fa-save"></i> Save address pin</button>
            </form>

            <?php if ($row->source === 'manual' || $row->geocode_status !== 'pending') { ?>
                <form method="post" action="<?php echo $base . '/reset_address/' . intval($row->id); ?>" style="margin-top:10px;"
                      onsubmit="return confirm('Clear this pin and let the geocode script look the address up again?');">
                    <button type="submit" class="btn btn-default"><i class="fa fa-undo"></i> Reset to automatic</button>
                </form>
            <?php } ?>
        </div>

        <div class="col-lg-4">
            <h4>Your members at this address</h4>
            <?php if (empty($members)) { ?>
                <p class="text-muted">None of your active members use this address.</p>
            <?php } else { ?>
                <ul class="list-unstyled">
                    <?php foreach ($members as $m) { ?>
                        <li>
                            <?php echo $e($m->member_id . ' — ' . $m->name); ?>
                            <a href="<?php echo $base . '/member_pin/' . encode_id($m->id); ?>" class="small">(own pin)</a>
                        </li>
                    <?php } ?>
                </ul>
                <?php if (count($members) >= 20) { ?><p class="text-muted small">Showing the first 20.</p><?php } ?>
            <?php } ?>
        </div>
    </div>
</div>
