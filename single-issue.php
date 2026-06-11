<?php
/**
 * single-issue.php — 号の個別ページ（＝その号の「目次」）
 * --------------------------------------------------------------------
 * 構成:
 *   1) 表紙/背景のヒーロー（front-pageと同じ見た目）
 *   2) この号に属する記事の目次（記事ページへのリンク）
 *   3) 号本体に説明文を書いていれば、その下に表示
 *
 * 「この号に属する記事」は、記事(article)側の ACFフィールド
 *   belongs_to_issue（= この記事が属する号）
 * を頼りに集めています。並び順は記事の「順序」→ 日付の順。
 * --------------------------------------------------------------------
 */
get_header();

while ( have_posts() ) :
	the_post();
	$oyk_issue_id  = get_the_ID();
	$oyk_hero_bg   = function_exists( 'get_field' ) ? get_field( 'hero_bg' ) : '';
	$oyk_cover_url = $oyk_hero_bg
		? $oyk_hero_bg
		: ( has_post_thumbnail() ? get_the_post_thumbnail_url( $oyk_issue_id, 'full' ) : '' );
	?>

	<article class="oyk-issue">

		<section class="oyk-hero"<?php if ( $oyk_cover_url ) : ?> style="background-image:url('<?php echo esc_url( $oyk_cover_url ); ?>');"<?php endif; ?>>
			<div class="oyk-hero__inner">
				<p class="oyk-hero__label">OYKZINE</p>
				<h1 class="oyk-hero__title"><?php the_title(); ?></h1>
			</div>
		</section>

		<?php
		// この号に属する記事を集める
		$oyk_articles = new WP_Query( array(
			'post_type'      => 'article',
			'posts_per_page' => -1,
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
			'meta_query'     => array(
				array(
					'key'     => 'belongs_to_issue',
					'value'   => $oyk_issue_id,
					'compare' => '=',
				),
			),
		) );
		?>

		<?php if ( $oyk_articles->have_posts() ) : ?>
			<section class="oyk-toc" aria-label="この号の目次">
				<h2 class="oyk-toc__heading">目次</h2>
				<ol class="oyk-toc__list">
					<?php
					while ( $oyk_articles->have_posts() ) :
						$oyk_articles->the_post();
						?>
						<li class="oyk-toc__item">
							<a class="oyk-toc__link" href="<?php the_permalink(); ?>">
								<span class="oyk-toc__text">
									<span class="oyk-toc__title"><?php the_title(); ?></span>
									<?php if ( has_excerpt() ) : ?>
										<span class="oyk-toc__sub"><?php echo esc_html( get_the_excerpt() ); ?></span>
									<?php endif; ?>
								</span>
							</a>
						</li>
						<?php
					endwhile;
					?>
				</ol>
			</section>
		<?php else : ?>
			<section class="oyk-toc">
				<p class="oyk-toc__empty">
					この号にはまだ記事が登録されていません。<br>
					記事を作って「この記事が属する号」にこの号を指定すると、ここに目次が出ます。
				</p>
			</section>
		<?php endif;
		wp_reset_postdata();
		?>

		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="oyk-issue__body">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>

	</article>

	<?php
endwhile;

get_footer();
