<?php
$e = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};
$base = site_url(current_lang() . '/map_location');
$source_labels = array(
    'nominatim' => 'OpenStreetMap lookup',
    'talibon_fallback' => 'Barangay centre (guess)',
    'talibon_center' => 'Town centre (guess)',
    'manual' => 'Manual',
);
$total_pages = $per_page > 0 ? max(1, (int) ceil($total / $per_page)) : 1;
$page_url = function ($p) use ($base, $filters) {
    return $base . '/index?' . http_build_query(array_merge($filters, array('page' => $p)));
};
?>
<style type="text/css">
    .ml-page .ml-toolbar { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; margin-bottom: 14px; }
    .ml-page .ml-toolbar .form-group { margin: 0; }
    .ml-page .ml-shortcuts { margin-bottom: 14px; }
    .ml-page tr.ml-rough td { background: #fff8e6; }
    .ml-page tr.ml-missing td { background: #fdeceb; }
    .ml-page .label-manual { background: #1ab394; }
</style>

<div class="col-lg-12 ml-page">
    <?php $this->load->view('map_location/_alerts'); ?>

    <?php if (!$member_pin_ready || !$office_pin_ready) { ?>
        <div class="alert alert-warning">
            Member and office pins are not installed yet. Run <code>tools/install_map_locations.php</code>.
        </div>
    <?php } ?>

    <div class="ml-shortcuts">
        <?php if ($office_pin_ready) { ?>
            <a href="<?php echo $base . '/office'; ?>" class="btn btn-primary"><i class="fa fa-building"></i> Set office pin</a>
        <?php } ?>
        <span class="text-muted" style="margin-left:8px;">
            To pin one member, open the member's Contact Info page and choose <strong>Set map pin</strong>.
        </span>
    </div>

    <?php if (!$table_ready) { ?>
        <div class="alert alert-warning">
            The address geocode cache does not exist yet. Run <code>tools/install_member_address_geocode.php</code> first.
        </div>
    <?php } else { ?>
        <form method="get" action="<?php echo $base . '/index'; ?>" class="ml-toolbar">
            <div class="form-group">
                <label>Search address</label>
                <input type="text" name="q" value="<?php echo $e($filters['q']); ?>" class="form-control"/>
            </div>
            <div class="form-group">
                <label>Source</label>
                <select name="source" class="form-control">
                    <option value="">All</option>
                    <?php foreach ($source_labels as $k => $label) { ?>
                        <option value="<?php echo $k; ?>" <?php echo $filters['source'] === $k ? 'selected' : ''; ?>><?php echo $e($label); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">All</option>
                    <?php foreach (array('ok' => 'OK', 'failed' => 'Failed', 'pending' => 'Pending') as $k => $label) { ?>
                        <option value="<?php echo $k; ?>" <?php echo $filters['status'] === $k ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label>Show</label>
                <select name="scope" class="form-control">
                    <option value="mine" <?php echo $filters['scope'] === 'mine' ? 'selected' : ''; ?>>Addresses my members use</option>
                    <option value="all" <?php echo $filters['scope'] === 'all' ? 'selected' : ''; ?>>All cached addresses</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-default"><i class="fa fa-filter"></i> Filter</button>
            </div>
        </form>

        <p class="text-muted">
            <?php echo number_format($total); ?> address(es). Rows shaded yellow are rough guesses, red are not plotted.
            An address pin applies to every member with exactly that address text.
        </p>

        <div class="table-responsive">
            <table class="table table-bordered table-condensed">
                <thead>
                    <tr>
                        <th>Address</th>
                        <th style="width:90px;">Members</th>
                        <th style="width:190px;">Lat, Lng</th>
                        <th style="width:80px;">Status</th>
                        <th style="width:170px;">Source</th>
                        <th style="width:140px;">Updated</th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($addresses)) { ?>
                        <tr><td colspan="7" class="text-center text-muted">No addresses match.</td></tr>
                    <?php } ?>
                    <?php foreach ($addresses as $row) {
                        $missing = ($row->geocode_status !== 'ok' || $row->lat === null || $row->lng === null);
                        $rough = !$missing && in_array($row->source, array('talibon_fallback', 'talibon_center'), true);
                        ?>
                        <tr class="<?php echo $missing ? 'ml-missing' : ($rough ? 'ml-rough' : ''); ?>">
                            <td><?php echo $e($row->address_raw); ?></td>
                            <td class="text-right"><?php echo intval($row->member_count); ?></td>
                            <td><?php echo $missing ? '&mdash;' : $e($row->lat . ', ' . $row->lng); ?></td>
                            <td><?php echo $e($row->geocode_status); ?></td>
                            <td>
                                <?php if ($row->source === 'manual') { ?>
                                    <span class="label label-manual">Manual</span>
                                <?php } else {
                                    echo $e(isset($source_labels[$row->source]) ? $source_labels[$row->source] : ($row->source ?: '—'));
                                } ?>
                            </td>
                            <td><?php echo $e($row->updated_at); ?></td>
                            <td>
                                <a href="<?php echo $base . '/edit_address/' . intval($row->id); ?>" class="btn btn-xs btn-primary">
                                    <i class="fa fa-map-marker"></i> Edit
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1) { ?>
            <ul class="pagination">
                <?php if ($page > 1) { ?>
                    <li><a href="<?php echo $e($page_url($page - 1)); ?>">&laquo; Prev</a></li>
                <?php } ?>
                <li class="active"><span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span></li>
                <?php if ($page < $total_pages) { ?>
                    <li><a href="<?php echo $e($page_url($page + 1)); ?>">Next &raquo;</a></li>
                <?php } ?>
            </ul>
        <?php } ?>
    <?php } ?>
</div>
