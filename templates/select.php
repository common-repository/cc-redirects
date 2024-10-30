<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php if ( ! empty( $before ) ) : ?><?= $before; ?><?php endif; ?>
<select<?php if ( ! empty( $name ) ) : ?> name="<?= $name; ?>"<?php endif; ?>>
	<?php if ( $options ) foreach( $options as $value => $name ) : ?>
		<option <?php  if ( '' !== $value ) : ?>value="<?= $value; ?>"<?php endif; ?><?php if ( $selected === $value ) : ?> selected="selected"<?php endif; ?>><?php if ( ! empty( $name ) ) : ?><?= $name; ?><?php endif; ?></option>
	<?php endforeach; ?>
</select>
<?php if ( ! empty( $after ) ) : ?><?= $after; ?><?php endif; ?>
<?php if ( ! empty( $desc ) ) : ?>
	<p class="description">
		<?= $desc; ?>
	</p>
<?php endif; ?>