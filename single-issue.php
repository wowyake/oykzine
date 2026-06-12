<?php
/**
 * single-issue.php — 号ページ（目次）
 * ヒーロー（表紙＋ロゴ＋VOL＋タイトル）／ 号の導入文（read moreで開閉）／
 * セクション別の目次（THEMEのみ番号）。
 */
get_header();

$oyk_section_order = array(
    'theme'  => 'THEME',
    'trend'  => 'TREND',
    'column' => 'COLUMN',
);

while ( have_posts() ) :
    the_post();
    $oyk_issue_id   = get_the_ID();
    $oyk_hero_bg    = function_exists( 'get_field' ) ? get_field( 'hero_bg' ) : '';
    $oyk_cover_url  = $oyk_hero_bg ? $oyk_hero_bg : ( has_post_thumbnail() ? get_the_post_thumbnail_url( $oyk_issue_id, 'full' ) : '' );
    $oyk_vol        = function_exists( 'get_field' ) ? get_field( 'vol_number' ) : '';
    $oyk_logo_id    = get_theme_mod( 'custom_logo' );
    $oyk_logo_url   = $oyk_logo_id ? wp_get_attachment_image_url( $oyk_logo_id, 'full' ) : '';
    $oyk_logo_color = ( function_exists( 'get_field' ) && get_field( 'hero_logo_color' ) ) ? get_field( 'hero_logo_color' ) : '#ffffff';
    ?>
    <article class="oyk-issue">

        <section class="oyk-hero"<?php if ( $oyk_cover_url ) : ?> style="background-image:url('<?php echo esc_url( $oyk_cover_url ); ?>');"<?php endif; ?>>
            <?php if ( $oyk_logo_url ) : ?>
                <span class="oyk-hero__logo" aria-hidden="true" style="--logo-src:url('<?php echo esc_url( $oyk_logo_url ); ?>');--logo-color:<?php echo esc_attr( $oyk_logo_color ); ?>;"></span>
            <?php endif; ?>
            <div class="oyk-hero__inner">
                <?php if ( $oyk_vol !== '' && $oyk_vol !== null ) : ?>
                    <p class="oyk-hero__vol"><span class="oyk-hero__vol-label">VOL.</span><span class="oyk-hero__vol-num"><?php echo esc_html( $oyk_vol ); ?></span></p>
                <?php endif; ?>
                <h1 class="oyk-hero__title"><?php the_title(); ?></h1>
            </div>
        </section>

        <?php if ( trim( get_the_content() ) ) : ?>
            <div class="oyk-issue__intro" data-intro>
                <div class="oyk-issue__intro-body"><?php the_content(); ?></div>
                <button class="oyk-issue__more" type="button" data-intro-toggle aria-expanded="false">read more <i class="oyk-issue__more-ico" aria-hidden="true">▾</i></button>
            </div>
        <?php endif; ?>

        <nav class="oyk-toc" aria-label="この号の目次">
            <?php
            $oyk_found = false;
            foreach ( $oyk_section_order as $oyk_slug => $oyk_label ) :
                $oyk_q = new WP_Query( array(
                    'post_type'      => 'article',
                    'posts_per_page' => -1,
                    'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
                    'meta_query'     => array( array(
                        'key'     => 'belongs_to_issue',
                        'value'   => $oyk_issue_id,
                        'compare' => '=',
                    ) ),
                    'tax_query'      => array( array(
                        'taxonomy' => 'section',
                        'field'    => 'slug',
                        'terms'    => $oyk_slug,
                    ) ),
                ) );
                if ( ! $oyk_q->have_posts() ) { wp_reset_postdata(); continue; }
                $oyk_found    = true;
                $oyk_is_theme = ( 'theme' === $oyk_slug );
                ?>
                <div class="oyk-toc__group">
                    <div class="oyk-toc__label"><span class="oyk-toc__bar"></span><?php echo esc_html( $oyk_label ); ?></div>
                    <ol class="oyk-toc__items<?php echo $oyk_is_theme ? ' oyk-toc__items--num' : ''; ?>">
                        <?php
                        while ( $oyk_q->have_posts() ) :
                            $oyk_q->the_post();
                            $oyk_series = get_the_terms( get_the_ID(), 'series' );
                            ?>
                            <li class="oyk-toc__item">
                                <a class="oyk-toc__link" href="<?php the_permalink(); ?>">
                                    <span class="oyk-toc__text">
                                        <span class="oyk-toc__title"><?php the_title(); ?><?php if ( $oyk_series && ! is_wp_error( $oyk_series ) ) : ?> <span class="oyk-toc__badge">連載</span><?php endif; ?></span>
                                        <?php if ( has_excerpt() ) : ?><span class="oyk-toc__sub"><?php echo esc_html( get_the_excerpt() ); ?></span><?php endif; ?>
                                    </span>
                                    <i class="oyk-toc__arrow" aria-hidden="true"></i>
                                </a>
                            </li>
                            <?php
                        endwhile;
                        ?>
                    </ol>
                </div>
                <?php
                wp_reset_postdata();
            endforeach;

            if ( ! $oyk_found ) :
                ?>
                <p class="oyk-toc__empty">この号にはまだ記事がありません。記事に「セクション」と「この記事が属する号」を設定すると、ここに目次が出ます。</p>
                <?php
            endif;
            ?>
        </nav>

    </article>

    <script>
    (function () {
        var box = document.querySelector('[data-intro]');
        if (!box) { return; }
        var btn = box.querySelector('[data-intro-toggle]');
        btn.addEventListener('click', function () {
            var open = box.classList.toggle('is-open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.firstChild.textContent = open ? 'とじる ' : 'read more ';
        });
    })();
    </script>

    <?php
endwhile;

get_footer();
