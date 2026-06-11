<?php
/**
 * single-article.php — 記事の個別ページ
 * --------------------------------------------------------------------
 * 目次から飛んでくる、1記事ぶんのページ。
 * 上下に「この号の目次へ戻る」リンクを出す（belongs_to_issue を利用）。
 *
 * ※ belongs_to_issue は ACFで「返り値の形式 = 投稿オブジェクト(Post Object)」
 *    で作る前提。get_field() が WP_Post を返すので $oyk_issue->ID で使える。
 * --------------------------------------------------------------------
 */
get_header();

while ( have_posts() ) :
	the_post();
	$oyk_issue = function_exists( 'get_field' ) ? get_field( 'belongs_to_issue' ) : null;
	?>

	<article class="oyk-article">

		<header class="oyk-article__head">
			<?php if ( $oyk_issue ) : ?>
				<a class="oyk-article__issue" href="<?php echo esc_url( get_permalink( $oyk_issue->ID ) ); ?>">
					&laquo; <?php echo esc_html( get_the_title( $oyk_issue->ID ) ); ?> の目次へ
				</a>
			<?php endif; ?>
<h1 class="oyk-article__title"><?php the_title(); ?></h1>
            <?php if ( has_excerpt() ) : ?>
                <p class="oyk-article__lead-text"><?php echo esc_html( get_the_excerpt() ); ?></p>
            <?php endif; ?>
        </header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="oyk-article__lead"><?php the_post_thumbnail( 'full' ); ?></figure>
		<?php endif; ?>

		<div class="oyk-article__body">
			<?php the_content(); ?>
		</div>

		<?php if ( $oyk_issue ) : ?>
			<a class="oyk-article__back" href="<?php echo esc_url( get_permalink( $oyk_issue->ID ) ); ?>">
				この号の目次へ戻る
			</a>
		<?php endif; ?>

	</article>

	<?php
endwhile;

get_footer();
