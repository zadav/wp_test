	<?php get_header(); // ouvrir header,php?>
<div id="content">
<?php if(have_posts()) : ?><?php while(have_posts()) : the_post(); ?>
<div class="post" id="post-<?php the_ID(); ?>">
<h2><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
<p class="postmetadata">
		<?php the_time(get_option('date_format')) ?> par <?php the_author() ?> | 
		Cat&eacute;gorie: <?php the_category(', ') ?>
</p>
<div class="post_content">
<?php the_content(); ?>
</div>
</div>
<div class="comments-template">
<?php comments_template(); ?>
</div>
<?php endwhile; ?>
<?php else : ?>
		<p>D�sol�, aucun article ne correspond � vos crit�res.</p>
<?php previous_post_link() ?> <?php next_post_link() ?>
<?php endif; ?>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
</div>
</body>
</html>
