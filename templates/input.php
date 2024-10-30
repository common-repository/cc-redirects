<?php defined( 'ABSPATH' ) or exit; ?>
<?php if ( ! empty( $before ) or ! empty( $after ) ) : ?><label><?php endif; ?>
	<?php if ( ! empty( $before ) ) : ?><?= $before; ?><?php endif; ?>
	<input <?php if ( ! empty( $atts ) ) : ?><?= $atts; ?><?php endif; ?><?php if ( ! empty( $checked ) ) : ?> <?= $checked; ?><?php endif; ?>/>
	<?php if ( ! empty( $after ) ) : ?><?= $after; ?><?php endif; ?>
<?php if ( ! empty( $before ) or ! empty( $after ) ) : ?></label><?php endif; ?>
<?php if ( ! empty( $desc ) ) : ?>
	<p class="description">
		<?= $desc; ?>
	</p>
<?php endif; ?>