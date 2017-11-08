<?php

function echo_acl_form($level) {
    ?>
    <form method="POST" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    	<?php wp_nonce_field( 'epfl-tequila-save-options' ); ?>
        <input type="hidden" name="acl_level" value="<?php echo $level ?>">
        <input class="button-primary" type="submit" value="<?php echo __("Sauvegarder", "epfl-tequila") ?>" />
    </form>
    <?php
}

?>

<div class="wrap">

    <div id="icon-themes" class="icon32"></div>
    <h2>Authentification Tequila</h2>
    <h3><?php echo __("Accès super-administrateur", "epfl-tequila") ?></h3>
    <?php echo_acl_form("superadmin") ?>
    <h3><?php echo __("Accès administrateur", "epfl-tequila") ?></h3>
    <?php echo_acl_form("admin") ?>
    <h3><?php echo __("Accès éditeur", "epfl-tequila") ?></h3>
    <?php echo_acl_form("editor") ?>
    <h3><?php echo __("Accès auteur", "epfl-tequila") ?></h3>
    <?php echo_acl_form("author") ?>
    <h3><?php echo __("Accès contributeur", "epfl-tequila") ?></h3>
    <?php echo_acl_form("contributor") ?>
    <h3><?php echo __("Accès abonné", "epfl-tequila") ?></h3>
    <?php echo_acl_form("subscriber") ?>
</div>

