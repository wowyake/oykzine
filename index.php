<?php
/**
 * 予備（フォールバック）テンプレート。
 * front-page.php や single-issue.php を作るまでの間、
 * とりあえず投稿が表示されることを確認するためのものです。
 */
get_header();
?>

<main style="max-width:680px;margin:0 auto;padding:24px;font-family:sans-serif;">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
			<article style="margin-bottom:32px;">
				<h2 style="margin:0 0 8px;">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
				<?php the_excerpt(); ?>
			</article>
			<?php
		endwhile;
	else :
		echo '<p>まだ投稿がありません。号やNoteを追加してみてください。</p>';
	endif;
	?>
</main>

<?php get_footer(); ?>
