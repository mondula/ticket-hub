<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <header>
        <?php block_template_part( 'header' ) ?>
    </header>
    <main>
        <div class="wrapper">
            <?php echo do_shortcode('[ticket id="' . get_the_ID() . '"]'); ?>
        </div>
    </main>
    <footer>
        <?php block_template_part( 'footer' ) ?>
    </footer>

    <?php wp_footer(); ?>
</body>
</html>
