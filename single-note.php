<?php
/**
 * single-note.php — Note の個別ページ（OYKZINEヘッダー版）
 * ラベル(NOTE/連載名)＋大見出し＋リード＋罫線＋CREDIT/日付＋タグ＋全面写真。
 */
get_header();

while ( have_posts() ) :
    the_post();

    $oyk_series = get_the_terms( get_the_ID(), 'series' );
    $oyk_series = ( $oyk_series && ! is_wp_error( $oyk_series ) ) ? $oyk_series[0] : null;

    $oyk_topics = get_the_terms( get_the_ID(), 'topic' );
    $oyk_topics = ( $oyk_topics && ! is_wp_error( $oyk_topics ) ) ? $oyk_topics : array();

    $oyk_c_photo = function_exists( 'get_field' ) ? get_field( 'credit_photo' ) : '';
    $oyk_c_text  = function_exists( 'get_field' ) ? get_field( 'credit_text' )  : '';
    $oyk_c_edit  = function_exists( 'get_field' ) ? get_field( 'credit_edit' )  : '';
    $oyk_has_credit = ( $oyk_c_photo || $oyk_c_text || $oyk_c_edit );
    ?>

    <article class="oyk-article">

        <header class="oyk-arthead">
            <?php if ( $oyk_series ) : ?>
                <a class="oyk-arthead__label" href="<?php echo esc_url( get_term_link( $oyk_series ) ); ?>">
                    <span class="oyk-arthead__bar"></span>連載「<?php echo esc_html( $oyk_series->name ); ?>」
                </a>
            <?php else : ?>
                <span class="oyk-arthead__label"><span class="oyk-arthead__bar"></span>NOTE</span>
            <?php endif; ?>

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
                        <?php if ( $oyk_c_text ) : ?><span>文・<?php echo esc_html( $oyk_c_text ); ?></span><?php endif; ?>
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
        $oyk_next_note = $oyk_series ? get_previous_post( true, '', 'series' ) : get_previous_post();
        $oyk_archive   = get_post_type_archive_link( 'note' );
        ?>
        <div class="oyk-article__nav">
            <?php if ( $oyk_archive ) : ?>
                <a class="oyk-article__nav-back" href="<?php echo esc_url( $oyk_archive ); ?>">
                    <span class="oyk-article__nav-cap">一覧へ</span>
                    <span class="oyk-article__nav-ttl">Notes</span>
                </a>
            <?php else : ?>
                <span></span>
            <?php endif; ?>
            <?php if ( $oyk_next_note ) : ?>
                <a class="oyk-article__nav-next" href="<?php echo esc_url( get_permalink( $oyk_next_note->ID ) ); ?>">
                    <span class="oyk-article__nav-cap">次のNote &rarr;</span>
                    <span class="oyk-article__nav-ttl"><?php echo esc_html( get_the_title( $oyk_next_note->ID ) ); ?></span>
                </a>
            <?php endif; ?>
        </div>

    </article>

    <?php
endwhile;

get_footer();
