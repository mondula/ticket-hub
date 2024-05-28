<!DOCTYPE html>
<html <?php echo esc_attr(language_attributes()); ?>>

<head>
	<meta charset="<?php echo esc_attr(get_bloginfo('charset')); ?>">
	<?php
	$header = do_blocks('<!-- wp:template-part {"slug":"header","tagName":"header"} /-->');
	$footer = do_blocks('<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->')
	?>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div class="wp-site-blocks">
		<?php echo wp_kses_post($header); ?>
		<main>
			<div class="wrapper">
				<?php echo do_shortcode('[th_ticket id="' . esc_attr(get_the_ID()) . '"]'); ?>
			</div>
		</main>
		<?php echo wp_kses_post($footer); ?>
	</div>
	<?php wp_footer(); ?>
</body>

</html>
