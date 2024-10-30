<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <form method="post" action="<?= $url; ?>">
        <h1 class="wp-heading-inline"><?= $header; ?></h1>
        <?php if ( ! empty( $s ) ) : ?>
            <span class="subtitle">
                <?= $result; ?> <code><?= $s; ?></code>
            </span>
        <?php endif; ?>
        <h2 class="nav-tab-wrapper">
            <?php foreach( $tabs as $key => $value ) : ?>
                <a href="<?= $url; ?>&tab=<?= $key; ?>" class="nav-tab<?= $key === $tab ? ' nav-tab-active' : ''; ?>"><?= $value; ?></a>
            <?php endforeach; ?>
        </h2>
        <div id="poststuff">
            <div id="post-body" class="metabox-holder">
                <?= $desc; ?>
                <?php $table->search_box( $search, $id ); ?>
                <div id="post-body-content">
                    <div class="meta-box-sortables ui-sortable">
                        <?php $table->display(); ?>
                    </div>
                </div>
            </div>
            <br class="clear" />
        </div>
        <?php
            echo $input;
            wp_nonce_field( $action );
            submit_button(  $submit, 'primary', '' );
        ?>
    </form>
</div>