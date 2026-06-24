<?php
/**
 * single-article.php — 記事の個別ページ（OYKZINEヘッダー版）
 * 号のセクション(THEME 01)ラベル＋大見出し＋リード＋罫線＋CREDIT/著者/日付＋タグ＋写真。
 * 号への戻り／次の記事ナビは従来どおり。
 */
get_header();

while ( have_posts() ) :
    the_post();

    $oyk_issue = function_exists( 'get_field' ) ? get_field( 'belongs_to_issue' ) : null;

    // セクション（THEME/TREND/COLUMN）とその号内ナンバー
    $oyk_sec = get_the_terms( get_the_ID(), 'section' );
    $oyk_sec = ( $oyk_sec && ! is_wp_error( $oyk_sec ) ) ? $oyk_sec[0] : null;

    // 著者・タグ・クレジット
    $oyk_writers = get_the_terms( get_the_ID(), 'writer' );
    $oyk_writers = ( $oyk_writers && ! is_wp_error( $oyk_writers ) ) ? $oyk_writers : array();
    $oyk_topics  = get_the_terms( get_the_ID(), 'topic' );
    $oyk_topics  = ( $oyk_topics && ! is_wp_error( $oyk_topics ) ) ? $oyk_topics : array();
    $oyk_c_photo = function_exists( 'get_field' ) ? get_field( 'credit_photo' ) : '';
    $oyk_c_text  = function_exists( 'get_field' ) ? get_field( 'credit_text' )  : '';
    $oyk_c_edit  = function_exists( 'get_field' ) ? get_field( 'credit_edit' )  : '';
    $oyk_has_credit = ( $oyk_c_photo || $oyk_c_text || $oyk_c_edit || $oyk_writers );

    // THEME内のナンバー（同じ号・同じセクションでの順番）
    $oyk_num = '';
    if ( $oyk_issue && $oyk_sec && 'theme' === $oyk_sec->slug ) {
        $oyk_theme_q = new WP_Query( array(
            'post_type'      => 'article',
            'posts_per_page' => -1,
            'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
            'fields'         => 'ids',
            'meta_query'     => array( array( 'key' => 'belongs_to_issue', 'value' => $oyk_issue->ID, 'compare' => '=' ) ),
            'tax_query'      => array( array( 'taxonomy' => 'section', 'field' => 'slug', 'terms' => 'theme' ) ),
        ) );
        $oyk_pos = array_search( get_the_ID(), $oyk_theme_q->posts, true );
        wp_reset_postdata();
        if ( false !== $oyk_pos ) { $oyk_num = sprintf( '%02d', $oyk_pos + 1 ); }
    }
    ?>

    <article class="oyk-article">

        <?php if ( $oyk_issue ) : ?>
            <a class="oyk-article__issue" href="<?php echo esc_url( get_permalink( $oyk_issue->ID ) ); ?>">&laquo; <?php echo esc_html( get_the_title( $oyk_issue->ID ) ); ?> の目次へ</a>
        <?php endif; ?>

        <header class="oyk-arthead">
            <div class="oyk-arthead__label">
                <span class="oyk-arthead__bar"></span>
                <?php echo $oyk_sec ? esc_html( strtoupper( $oyk_sec->name ) ) : 'ARTICLE'; ?>
                <?php if ( $oyk_num ) : ?> <span class="oyk-arthead__num"><?php echo esc_html( $oyk_num ); ?></span><?php endif; ?>
            </div>

            <h1 class="oyk-arthead__title"><?php the_title(); ?></h1>

            <?php if ( has_excerpt() ) : ?>
                <p class="oyk-arthead__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
            <?php endif; ?>

            <div class="oyk-arthead__rule"></div>

            <div class="oyk-arthead__meta">
                <?php if ( $oyk_has_credit ) : ?>
                    <div class="oyk-credit">
                        <span class="oyk-credit__label">CREDIT</span>
                        <?php if ( $oyk_c_photo ) : ?><span>写真・<?php echo esc_html( $oyk_c_photo ); ?></span><?php endif; ?>
                        <?php if ( $oyk_writers ) : ?>
                            <span>文・<?php
                                $oyk_wl = array();
                                foreach ( $oyk_writers as $w ) { $oyk_wl[] = '<a href="' . esc_url( get_term_link( $w ) ) . '">' . esc_html( $w->name ) . '</a>'; }
                                echo implode( '、', $oyk_wl );
                            ?></span>
                        <?php elseif ( $oyk_c_text ) : ?>
                            <span>文・<?php echo esc_html( $oyk_c_text ); ?></span>
                        <?php endif; ?>
                        <?php if ( $oyk_c_edit ) : ?><span>編集・<?php echo esc_html( $oyk_c_edit ); ?></span><?php endif; ?>
                    </div>
                <?php else : ?>
                    <span></span>
                <?php endif; ?>
                <time class="oyk-arthead__date"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></time>
            </div>

            <?php if ( $oyk_topics ) : ?>
                <div class="oyk-tags">
                    <?php foreach ( $oyk_topics as $oyk_t ) : ?>
                        <a class="oyk-tag" href="<?php echo esc_url( get_term_link( $oyk_t ) ); ?>">#<?php echo esc_html( $oyk_t->name ); ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </header>

        <?php if ( has_post_thumbnail() ) : ?>
            <figure class="oyk-arthead__hero"><?php the_post_thumbnail( 'full' ); ?></figure>
        <?php endif; ?>

        <div class="oyk-article__body">
            <?php the_content(); ?>
        </div>

        <?php
        // 次の記事＝同じ号で順序が後の1件
        $oyk_next = null;
        if ( $oyk_issue ) {
            $oyk_all = new WP_Query( array(
                'post_type'      => 'article',
                'posts_per_page' => -1,
                'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
                'fields'         => 'ids',
                'meta_query'     => array( array( 'key' => 'belongs_to_issue', 'value' => $oyk_issue->ID, 'compare' => '=' ) ),
            ) );
            $oyk_ids = $oyk_all->posts;
            wp_reset_postdata();
            $oyk_pos2 = array_search( get_the_ID(), $oyk_ids, true );
            if ( false !== $oyk_pos2 && isset( $oyk_ids[ $oyk_pos2 + 1 ] ) ) { $oyk_next = $oyk_ids[ $oyk_pos2 + 1 ]; }
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
                        <span class="oyk-article__nav-cap">次の記事 &rarr;</span>
                        <span class="oyk-article__nav-ttl"><?php echo esc_html( get_the_title( $oyk_next ) ); ?></span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </article>

    <?php
endwhile;

get_footer();
