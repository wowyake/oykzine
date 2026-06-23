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
        <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } ?>
    </span>
    <span class="oyk-note__body">
        <?php
        $oyk_note_series = get_the_terms( get_the_ID(), 'series' );
        $oyk_note_label  = ( $oyk_note_series && ! is_wp_error( $oyk_note_series ) ) ? $oyk_note_series[0]->name : 'NOTE';
        ?>
        <span class="oyk-note__cat"><?php echo esc_html( $oyk_note_label ); ?></span>
        <span class="oyk-note__title"><?php the_title(); ?></span>
        <?php if ( has_excerpt() ) : ?>
            <span class="oyk-note__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 40 ) ); ?></span>
        <?php endif; ?>
        <span class="oyk-note__date"><?php echo esc_html( get_the_date() ); ?></span>
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
