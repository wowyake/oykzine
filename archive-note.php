<?php
/**
 * archive-note.php — Notes の一覧ページ
 * --------------------------------------------------------------------
 * noteを更新順に並べる。front-pageのNotes部分と同じ見た目の行リスト。
 * --------------------------------------------------------------------
 */
get_header();
?>

<header class="oyk-archive__head">
	<h1 class="oyk-archive__title">Notes</h1>
</header>

<?php if ( have_posts() ) : ?>
	<div class="oyk-notes__list oyk-notes__list--page">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<a class="oyk-note" href="<?php the_permalink(); ?>">
				<span class="oyk-note__thumb">
					<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium' ); } ?>
				</span>
				<span class="oyk-note__body">
					<span class="oyk-note__title"><?php the_title(); ?></span>
					<span class="oyk-note__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 38 ) ); ?></span>
				</span>
			</a>
			<?php
		endwhile;
		?>
	</div>

	<?php
	the_posts_pagination( array(
		'mid_size'  => 1,
		'prev_text' => '前へ',
		'next_text' => '次へ',
	) );
	?>

<?php else : ?>
	<p class="oyk-archive__empty">まだNotesがありません。</p>
<?php endif; ?>

<?php get_footer(); ?>
