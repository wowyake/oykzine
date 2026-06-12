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

<?php
        // 次の記事＝同じ号の中で、自分より「順序」が後の記事を1件。
        $oyk_next = null;
        if ( $oyk_issue ) {
            $oyk_next_q = new WP_Query( array(
                'post_type'      => 'article',
                'posts_per_page' => 1,
                'post__not_in'   => array( get_the_ID() ),
                'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
                'meta_query'     => array( array(
                    'key'     => 'belongs_to_issue',
                    'value'   => $oyk_issue->ID,
                    'compare' => '=',
                ) ),
                'meta_key'       => 'menu_order',
            ) );
            // 自分より後ろの順序の記事を探す
            $oyk_all = new WP_Query( array(
                'post_type'      => 'article',
                'posts_per_page' => -1,
                'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
                'meta_query'     => array( array(
                    'key'     => 'belongs_to_issue',
                    'value'   => $oyk_issue->ID,
                    'compare' => '=',
                ) ),
                'fields'         => 'ids',
            ) );
            $oyk_ids = $oyk_all->posts;
            wp_reset_postdata();
            $oyk_pos = array_search( get_the_ID(), $oyk_ids, true );
            if ( false !== $oyk_pos && isset( $oyk_ids[ $oyk_pos + 1 ] ) ) {
                $oyk_next = $oyk_ids[ $oyk_pos + 1 ];
            }
        }
        ?>
        <?php if ( $oyk_issue || $oyk_next ) : ?>
            <div class="oyk-article__nav">
                <?php if ( $oyk_issue ) : ?>
                    <a class="oyk-article__nav-back" href="<?php echo esc_url( get_permalink( $oyk_issue->ID ) ); ?>">
                        <span class="oyk-article__nav-cap">目次へ</span>
                        <span class="oyk-article__nav-ttl"><?php echo esc_html( get_the_title( $oyk_issue->ID ) ); ?></span>
                    </a>
                <?php else : ?>
                    <span></span>
                <?php endif; ?>

                <?php if ( $oyk_next ) : ?>
                    <a class="oyk-article__nav-next" href="<?php echo esc_url( get_permalink( $oyk_next ) ); ?>">
                        <span class="oyk-article__nav-cap">次の記事 →</span>
                        <span class="oyk-article__nav-ttl"><?php echo esc_html( get_the_title( $oyk_next ) ); ?></span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

	</article>

	<?php
endwhile;

get_footer();
