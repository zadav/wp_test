<div class="sidebar">
<ul>
	<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
	<li id="search"><?php get_template_part(TEMPLATEPATH . '/searchform.php'); ?></li>
	<li id="calendar"><h2>Calendrier</h2>
	<?php get_calendar(); ?>
	</li>
	<li><h2>Categories</h2>
		<ul>
		<?php wp_list_categories('sort_column=name&optioncount=1&hierarchical=0'); ?>
		</ul> 
	</li>
	<?php wp_list_pages('title_li=<h2>Pages</h2>'); ?>
	<li><h2>Archives</h2>
 
		<ul>
		<?php wp_get_archives('type=monthly'); ?>
 
		</ul>
	</li>
	<?php wp_list_bookmarks(); ?>
	<li><h2>Infos Meta</h2> 
		<ul>
		<?php wp_register(); ?>
				<li><?php wp_loginout(); ?></li>
				<li><a href="http://validator.w3.org/check/referer" title="This page validates as XHTML 1.0 Transitional"><abbr title="eXtensible HyperText Markup Language">XHTML valide</abbr></a></li>
				<li><a href="http://gmpg.org/xfn/"><abbr title="XHTML Friends Network">XFN</abbr></a></li>
				<li><a href="http://wordpress.org/" title="Powered by WordPress, state-of-the-art semantic personal publishing platform.">WordPress</a></li>
			<li><a href="http://wordpress-fr.net/" title="CommunautÚ franšaise de WordPress et WPmu.">WordPress Francophone</a></li>
				<?php wp_meta(); ?>
		 
		</ul>
	</li>
	<li><h2>Abonnez-vous au blog !</h2>
		<ul>		 
		<li><a href="<?php bloginfo('rss2_url'); ?>" title="Flux RSS des articles">Flux RSS des articles</a></li>
		<li><a href="<?php bloginfo('comments_rss2_url'); ?>" title="Flux RSS des commentaires">Flux RSS des commentaires</a></li>
		</ul>
	</li>
	<?php endif; ?>
</ul>
</div>