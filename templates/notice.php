<?php defined( 'ABSPATH' ) or exit; ?>
<div class="notice notice-<?= $class; ?> <?php if ( $is_dismissible ) { echo ' is-dismissible'; } ?>">
	<p><?= $content; ?></p>
</div>
