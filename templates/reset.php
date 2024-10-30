<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <form method="post" action="<?= $url; ?>">
        <h1 class="wp-heading-inline"><?= $header; ?></h1>
        <h2 class="nav-tab-wrapper">
            <?php foreach( $tabs as $key => $value ) : ?>
                <a href="<?= $url; ?>&tab=<?= $key; ?>" class="nav-tab<?= $key === $tab ? ' nav-tab-active' : ''; ?>"><?= $value; ?></a>
            <?php endforeach; ?>
        </h2>
        <p><?= $desc; ?></p>
        <?php
            echo $input;
            wp_nonce_field( $action );
            submit_button(  $submit, 'primary', '' );
        ?>
    </form>
</div>