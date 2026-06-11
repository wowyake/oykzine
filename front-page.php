<?php
/**
 * front-page.php — トップページ
 * --------------------------------------------------------------------
 * 構成:
 *   1) ロゴ＋流れる表紙   … 1画面まるごと。ロゴ上・表紙下、間を大きく開ける
 *   2) 最新号ヒーロー      … 1画面まるごと
 *   （1と2はスクロールスナップで「パチンと」吸い付く）
 *   3) Notes フィード      … ここから下は普通のスクロール
 *
 * スナップは .oyk-snap という箱の中だけで効かせる（他ページに影響しない）。
 * --------------------------------------------------------------------
 */
get_header();
?>

<div class="oyk-snap">

    <?php /* ===== 1画面目：ロゴ＋流れる表紙 ===== */ ?>
    <section class="oyk-snap__screen oyk-firstview">

        <header class="oyk-masthead">
            <?php if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) : ?>
                <h1 class="oyk-masthead__logo"><?php the_custom_logo(); ?></h1>
            <?php else : ?>
                <h1 class="oyk-masthead__logo">OYKZINE</h1>
            <?php endif; ?>
        </header>

        <?php
        /* 流れる表紙：号を新しい順に取得 */
        $oyk_covers = new WP_Query( array(
            'post_type'      => 'issue',
            'posts_per_page' => 12,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );
        if ( $oyk_covers->have_posts() ) : ?>
            <section class="oyk-strip" aria-label="これまでの号">
                <div class="oyk-strip__track" data-cover-track>
                    <?php
                    while ( $oyk_covers->have_posts() ) :
                        $oyk_covers->the_post();
                        ?>
                        <a class="oyk-strip__item" href="<?php the_permalink(); ?>">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'large' ); ?>
                            <?php else : ?>
                                <span class="oyk-strip__placeholder"><?php the_title(); ?></span>
                            <?php endif; ?>
                        </a>
                        <?php
                    endwhile;
                    ?>
                </div>
            </section>
        <?php endif;
        wp_reset_postdata();
        ?>

    </section>

    <?php
    /* ===== 2画面目：最新号ヒーロー ===== */
    $oyk_latest = new WP_Query( array(
        'post_type'      => 'issue',
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );
    if ( $oyk_latest->have_posts() ) :
        $oyk_latest->the_post();
        // まずWeb用背景(hero_bg)を見る。無ければ印刷版の表紙(アイキャッチ)にフォールバック。
        $oyk_hero_bg   = function_exists( 'get_field' ) ? get_field( 'hero_bg' ) : '';
        $oyk_cover_url = $oyk_hero_bg
            ? $oyk_hero_bg
            : ( has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'full' ) : '' );
        // VOL番号・リード文（ACF）。未入力なら自動で隠す。
        $oyk_vol  = function_exists( 'get_field' ) ? get_field( 'vol_number' ) : '';
        $oyk_lead = function_exists( 'get_field' ) ? get_field( 'lead_text' ) : '';
        ?>
        <section class="oyk-snap__screen oyk-hero"<?php if ( $oyk_cover_url ) : ?> style="background-image:url('<?php echo esc_url( $oyk_cover_url ); ?>');"<?php endif; ?>>
<?php
$oyk_logo_id    = get_theme_mod( 'custom_logo' );
$oyk_logo_url   = $oyk_logo_id ? wp_get_attachment_image_url( $oyk_logo_id, 'full' ) : '';
$oyk_logo_color = ( function_exists( 'get_field' ) && get_field( 'hero_logo_color' ) ) ? get_field( 'hero_logo_color' ) : '#ffffff';
?>
<?php if ( $oyk_logo_url ) : ?>
    <span class="oyk-hero__logo" aria-hidden="true" style="--logo-src:url('<?php echo esc_url( $oyk_logo_url ); ?>');--logo-color:<?php echo esc_attr( $oyk_logo_color ); ?>;"></span>
<?php endif; ?>
            <div class="oyk-hero__inner">
                <?php if ( $oyk_vol !== '' && $oyk_vol !== null ) : ?>
                    <p class="oyk-hero__vol"><span class="oyk-hero__vol-label">VOL.</span><span class="oyk-hero__vol-num"><?php echo esc_html( $oyk_vol ); ?></span></p>
                <?php else : ?>
                    <p class="oyk-hero__label">最新号</p>
                <?php endif; ?>
                <h2 class="oyk-hero__title"><?php the_title(); ?></h2>
                <?php if ( $oyk_lead ) : ?>
                    <p class="oyk-hero__lead"><?php echo nl2br( esc_html( $oyk_lead ) ); ?></p>
                <?php endif; ?>
                <a class="oyk-hero__btn" href="<?php the_permalink(); ?>">最新号を読む &gt;&gt;&gt;</a>
            </div>
        </section>
        <?php
    endif;
    wp_reset_postdata();
    ?>

    <?php
    /* ===== 3画面目以降：Notes（ここから普通のスクロール） ===== */
    $oyk_notes = new WP_Query( array(
        'post_type'      => 'note',
        'posts_per_page' => 6,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );
    if ( $oyk_notes->have_posts() ) : ?>
        <section class="oyk-notes" aria-label="Notes">
            <h2 class="oyk-notes__heading">NOTES</h2>
            <div class="oyk-notes__list">
                <?php
                while ( $oyk_notes->have_posts() ) :
                    $oyk_notes->the_post();
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
            <a class="oyk-notes__more" href="<?php echo esc_url( get_post_type_archive_link( 'note' ) ); ?>">もっと見る &gt;&gt;&gt;</a>
        </section>
    <?php endif;
    wp_reset_postdata();
    ?>

</div><!-- /.oyk-snap -->

<script>
/* 流れる表紙：自動で右→左にドリフト。触れると止まる。指/マウスでドラッグ可。
   「動きを減らす」OS設定の人には自動を止める。
   ループ用に、トラックの中身が画面幅を超えるまで複製する（号が少なくても流れる）。 */
(function () {
    var track = document.querySelector('[data-cover-track]');
    if (!track) { return; }
    var strip = track.parentElement;
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // 元の中身を覚えておく
    var original = track.innerHTML;
    if (!original.trim()) { return; }

    // 画面幅の2倍を超えるまで複製を足す（最低でも2セットは並ぶ）
    var guard = 0;
    while ( track.scrollWidth < strip.clientWidth * 2 && guard < 20 ) {
        track.innerHTML += original;
        guard++;
    }
    // ループ用に、さらにもう1セット足して前半＝後半の関係を作る
    track.innerHTML += track.innerHTML;

    var pos = 0;
    var paused = false, isDown = false, startX = 0, startScroll = 0;

    strip.addEventListener('pointerdown', function (e) {
        isDown = true; paused = true;
        startX = e.pageX; startScroll = strip.scrollLeft;
    });
    window.addEventListener('pointerup', function () {
        if (!isDown) { return; }
        isDown = false;
        setTimeout(function () { paused = false; }, 1500);
    });
    strip.addEventListener('pointermove', function (e) {
        if (!isDown) { return; }
        strip.scrollLeft = startScroll - (e.pageX - startX);
    });
    strip.addEventListener('mouseenter', function () { paused = true; });
    strip.addEventListener('mouseleave', function () { if (!isDown) { paused = false; } });

    function tick() {
        var half = track.scrollWidth / 2;
        if (!reduce && !paused && half > 0) {
            pos += 0.4;
            if (pos >= half) { pos -= half; }
            strip.scrollLeft = pos;
        } else {
            pos = strip.scrollLeft; // ユーザー操作中は位置を同期
        }
        requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
})();
</script>
<script>
/* 最初の画面で少しでも下にスクロールしたら、最新号(ヒーロー)まで一気に移動する。
   ヒーロー上端から少し上に戻すと最初の画面へ。Notes以降は普通のスクロール。
   「動きを減らす」設定の人には何もしない。 */
(function () {
    var first = document.querySelector('.oyk-firstview');
    var hero  = document.querySelector('.oyk-hero.oyk-snap__screen');
    if (!first || !hero) { return; }
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }

    var locked = false;
    function lock() { locked = true; setTimeout(function () { locked = false; }, 900); }
    function onFirst() { return window.scrollY < hero.offsetTop - 5; }
    function nearHeroTop() { return window.scrollY <= hero.offsetTop + 5; }
    function go(target) {
        if (locked) { return; }
        lock();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // マウスホイール / トラックパッド
    window.addEventListener('wheel', function (e) {
        if (locked) { return; }
        if (e.deltaY > 0 && onFirst()) { go(hero); }
        else if (e.deltaY < 0 && !onFirst() && nearHeroTop()) { go(first); }
    }, { passive: true });

    // スマホのスワイプ
    var startY = null;
    window.addEventListener('touchstart', function (e) { startY = e.touches[0].clientY; }, { passive: true });
    window.addEventListener('touchmove', function (e) {
        if (locked || startY === null) { return; }
        var dy = startY - e.touches[0].clientY; // プラス＝下方向へスワイプ
        if (Math.abs(dy) < 80) { return; }
        if (dy > 0 && onFirst()) { go(hero); startY = null; }
        else if (dy < 0 && !onFirst() && nearHeroTop()) { go(first); startY = null; }
    }, { passive: true });
})();
</script>

<?php get_footer(); ?>
