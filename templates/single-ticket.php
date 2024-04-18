<?php get_header(); ?>

<body>
    <main>
        <div class="wrapper">
            <?php echo do_shortcode('[ticket id="' . get_the_ID() . '"]'); ?>
        </div>
    </main>
</body>

<?php get_footer(); ?>
