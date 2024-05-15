<?php get_header(); ?>

<main>
    <div class="wrapper">
        <?php echo do_shortcode('[ticket id="' . get_the_ID() . '"]'); ?>
    </div>
</main>

<?php get_footer(); ?>