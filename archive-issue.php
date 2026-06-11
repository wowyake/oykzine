<?php
/**
 * archive-issue.php — 号の一覧ページ（「今までのOYKZINE」）
 * --------------------------------------------------------------------
 * 表紙（印刷版＝アイキャッチ）をグリッドで並べる。各表紙はその号へリンク。
 * デザインはここを書き換えれば自由に変えられる（独立したテンプレート）。
 * --------------------------------------------------------------------
 */
get_header();
?>

<header class="oyk-archive__head">
	<h1 class="oyk-archive__title">今までのOYKZINE</h1>
</header>

<?php if ( have_posts() ) : ?>
	<div class="oyk-grid">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<a class="oyk-grid__item" href="<?php the_permalink(); ?>">
				<span class="oyk-grid__cover">
					<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } ?>
				</span>
				<span class="oyk-grid__title"><?php the_title(); ?></span>
			</a>
			<?php
		endwhile;
		?>
	</div>

	<?php
	// もっと号が増えたときのためのページ送り
	the_posts_pagination( array(
		'mid_size'  => 1,
		'prev_text' => '前へ',
		'next_text' => '次へ',
	) );
	?>

<?php else : ?>
	<p class="oyk-archive__empty">まだ号がありません。</p>
<?php endif; ?>

<?php get_footer(); ?>
