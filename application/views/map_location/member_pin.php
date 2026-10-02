<?php
$e = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$base = site_url(current_lang() . '/map_location');
$has_own_pin = ($member->map_lat !== null && $member->map_lng !== null);
$addr_pin = $member->address_pin;
$addr_ok = $addr_pin && $addr_pin->geocode_status === 'ok' && $addr_pin->lat !== null;
$name = trim($member->firstname . ' ' . $member->lastname);
?>
<div class="col-lg-12">
    <?php $this->load->view('map_location/_alerts'); ?>

    <p>
        <a href="<?php echo site_url(current_lang() . '/member/membercontact/' . $encoded_id); ?>"><i class="fa fa-arrow-left"></i> Back to contact info</a>
    </p>

    <h3 style="margin-top:0;"><?php echo $e($member->member_id . ' — ' . $name); ?></h3>
    <p class="text-muted">Address: <strong><?php echo $member->address !== '' ? $e($member->address) : '—'; ?></strong></p>

    <div class="alert alert-info">
        <?php if ($has_own_pin) { ?>
            This member has their <strong>own pin</strong>, so the maps show them here instead of at the address pin.
            <?php if (!empty($member->map_updated_at)) { ?>(Set <?php echo $e($member->map_updated_at); ?>.)<?php } ?>
        <?php } elseif ($addr_ok) { ?>
            No own pin yet. The maps use the <strong>address pin</strong> (<?php echo $e($addr_pin->source ?: 'automatic'); ?>),
            shared with everyone at the same address. Save a pin here to place only this member.
        <?php } else { ?>
            This member is <strong>not plotted</strong>: there is no own pin and the address has no usable location.
        <?php } ?>
        <?php if ($addr_pin) { ?>
            <a href="<?php echo $base . '/edit_address/' . intval($addr_pin->id); ?>">Edit the shared address pin</a>.
        <?php } ?>
    </div>

    <form method="post" action="<?php echo $base . '/member_pin/' . $e($encoded_id); ?>">
        <?php $this->load->view('map_location/_picker'); ?>
        <button type="submit" name="action" value="save" class="btn btn-primary"><i class="fa fa-save"></i> Save member pin</button>
        <?php if ($has_own_pin) { ?>
            <button type="submit" name="action" value="clear" class="btn btn-default"
                    onclick="return confirm('Remove this member\'s own pin and use the address pin again?');">
                <i class="fa fa-eraser"></i> Clear member pin
            </button>
        <?php } ?>
    </form>
</div>
