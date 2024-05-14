<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php
	$header = do_blocks('<!-- wp:template-part {"slug":"header","tagName":"header"} /-->');
    $footer = do_blocks('<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->')
 	?>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wp-site-blocks">
<?php echo $header; ?>
<main>
    <div class="wrapper">
        <?php echo do_shortcode('[ticket id="' . get_the_ID() . '"]'); ?>
    </div>
</main>
<?php echo $footer; ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
