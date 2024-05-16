<?php get_header(); ?>

<main>
    <div class="wrapper">
        <?php echo do_shortcode('[th_ticket id="' . get_the_ID() . '"]'); ?>
    </div>
</main>

<?php get_footer(); ?>