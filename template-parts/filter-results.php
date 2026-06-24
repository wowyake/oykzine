<?php
/**
 * 絞り込み付き結果リスト（検索・タグ・著者・連載で共用）
 * クエリ変数: oyk_base_args / oyk_preset_tag / oyk_preset_author / oyk_kw
 */
$oyk_base = get_query_var( 'oyk_base_args' );
if ( ! is_array( $oyk_base ) || empty( $oyk_base ) ) {
    $oyk_base = array( 'post_type' => array( 'note', 'article' ), 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC' );
}
$oyk_preset_tag    = get_query_var( 'oyk_preset_tag' );
$oyk_url_tags = isset( $_GET['tag'] ) ? array_filter( array_map( 'sanitize_title', explode( ',', wp_unslash( $_GET['tag'] ) ) ) ) : array();
if ( $oyk_preset_tag ) { $oyk_url_tags[] = $oyk_preset_tag; }
$oyk_url_tags = array_values( array_unique( $oyk_url_tags ) );
$oyk_preset_author = get_query_var( 'oyk_preset_author' );
$oyk_kw            = get_query_var( 'oyk_kw' );

$oyk_q = new WP_Query( $oyk_base );
$oyk_all_topics = array(); $oyk_all_writers = array(); $oyk_cards = array();
if ( $oyk_q->have_posts() ) :
    while ( $oyk_q->have_posts() ) : $oyk_q->the_post();
        $w  = get_the_terms( get_the_ID(), 'writer' ); $w = ( $w && ! is_wp_error( $w ) ) ? $w : array();
        $pt = get_the_terms( get_the_ID(), 'topic' );  $pt = ( $pt && ! is_wp_error( $pt ) ) ? $pt : array();
        foreach ( $w as $x )  { $oyk_all_writers[ $x->name ] = $x->name; }
        foreach ( $pt as $x ) { $oyk_all_topics[ $x->slug ] = $x->name; }
        $oyk_cards[] = array(
            'permalink' => get_permalink(), 'title' => get_the_title(),
            'date_iso' => get_the_date( 'Y-m-d' ), 'date_disp' => get_the_date(),
            'thumb' => has_post_thumbnail() ? get_the_post_thumbnail( get_the_ID(), 'large' ) : '',
            'excerpt' => has_excerpt() ? wp_trim_words( get_the_excerpt(), 32 ) : '',
            'authors' => wp_list_pluck( $w, 'name' ), 'tagslugs' => wp_list_pluck( $pt, 'slug' ), 'type' => get_post_type(),
            'searchstr' => mb_strtolower( get_the_title() . ' ' . get_the_excerpt() . ' ' . implode( ' ', wp_list_pluck( $w, 'name' ) ) . ' ' . implode( ' ', wp_list_pluck( $pt, 'name' ) ) ),
        );
    endwhile; wp_reset_postdata();
endif;
asort( $oyk_all_writers ); asort( $oyk_all_topics );
?>
<div class="oyk-filter" data-filter>
    <input type="search" class="oyk-filter__kw" placeholder="キーワード" data-kw value="<?php echo esc_attr( $oyk_kw ); ?>">
    <select class="oyk-filter__sort" data-author>
        <option value="">著者：すべて</option>
        <?php foreach ( $oyk_all_writers as $wn ) : ?><option value="<?php echo esc_attr( $wn ); ?>" <?php selected( $oyk_preset_author, $wn ); ?>><?php echo esc_html( $wn ); ?></option><?php endforeach; ?>
    </select>
    <select class="oyk-filter__sort" data-sort>
        <option value="new">新しい順</option><option value="old">古い順</option><option value="title">タイトル順</option>
    </select>
</div>
<?php if ( $oyk_all_topics ) : ?>
<div class="oyk-chips">
    <span class="oyk-chips__label">タグ</span>
<?php foreach ( $oyk_all_topics as $slug => $name ) : ?><button type="button" class="oyk-chip<?php echo in_array( $slug, $oyk_url_tags, true ) ? ' is-on' : ''; ?>" data-chip="<?php echo esc_attr( $slug ); ?>">#<?php echo esc_html( $name ); ?></button><?php endforeach; ?>
</div>
<?php endif; ?>
<p class="oyk-filter__count" data-count></p>
<div class="oyk-notes__list oyk-notes__list--page" data-list>
    <?php foreach ( $oyk_cards as $c ) : ?>
        <a class="oyk-note" href="<?php echo esc_url( $c['permalink'] ); ?>" data-card
           data-date="<?php echo esc_attr( $c['date_iso'] ); ?>" data-author="<?php echo esc_attr( implode( ' ', $c['authors'] ) ); ?>"
           data-title="<?php echo esc_attr( $c['title'] ); ?>" data-tags="<?php echo esc_attr( implode( ' ', $c['tagslugs'] ) ); ?>"
           data-search="<?php echo esc_attr( $c['searchstr'] ); ?>">
            <span class="oyk-note__thumb"><?php echo $c['thumb']; ?></span>
            <span class="oyk-note__body">
                <span class="oyk-note__cat"><?php echo esc_html( $c['authors'] ? implode( '、', $c['authors'] ) : ( 'article' === $c['type'] ? '記事' : 'NOTE' ) ); ?></span>
                <span class="oyk-note__title"><?php echo esc_html( $c['title'] ); ?></span>
                <?php if ( $c['excerpt'] ) : ?><span class="oyk-note__excerpt"><?php echo esc_html( $c['excerpt'] ); ?></span><?php endif; ?>
                <span class="oyk-note__date"><?php echo esc_html( $c['date_disp'] ); ?></span>
            </span>
        </a>
    <?php endforeach; ?>
</div>
<p class="oyk-filter__empty" data-empty hidden>条件に合う投稿がありません。</p>
<script>
(function () {
    var root = document.querySelector('[data-filter]'); if (!root) { return; }
    var list = document.querySelector('[data-list]');
    var cards = Array.prototype.slice.call(list.querySelectorAll('[data-card]'));
    var kw = root.querySelector('[data-kw]'), sort = root.querySelector('[data-sort]'), au = root.querySelector('[data-author]');
    var count = document.querySelector('[data-count]'), emptyMsg = document.querySelector('[data-empty]');
    var chips = Array.prototype.slice.call(document.querySelectorAll('[data-chip]'));
    var activeTags = chips.filter(function (c) { return c.classList.contains('is-on'); }).map(function (c) { return c.getAttribute('data-chip'); });
    chips.forEach(function (b) { b.addEventListener('click', function () {
        var s = b.getAttribute('data-chip'), i = activeTags.indexOf(s);
        if (i === -1) { activeTags.push(s); b.classList.add('is-on'); } else { activeTags.splice(i, 1); b.classList.remove('is-on'); }
        apply();
    }); });
    function apply() {
        var q = (kw.value || '').toLowerCase().trim(), a = au.value, shown = 0;
        cards.forEach(function (c) {
            var okKw = !q || (c.getAttribute('data-search') || '').indexOf(q) !== -1;
            var okAu = !a || (c.getAttribute('data-author') || '').indexOf(a) !== -1;
            var tags = (c.getAttribute('data-tags') || '').split(' ');
            var okTag = activeTags.every(function (t) { return tags.indexOf(t) !== -1; });
            var show = okKw && okAu && okTag; c.style.display = show ? '' : 'none'; if (show) { shown++; }
        });
        emptyMsg.hidden = shown !== 0; if (count) { count.textContent = shown + '件'; }
        var v = sort.value, vis = cards.filter(function (c) { return c.style.display !== 'none'; });
        vis.sort(function (x, y) {
            if (v === 'old') { return x.getAttribute('data-date').localeCompare(y.getAttribute('data-date')); }
            if (v === 'title') { return x.getAttribute('data-title').localeCompare(y.getAttribute('data-title'), 'ja'); }
            return y.getAttribute('data-date').localeCompare(x.getAttribute('data-date'));
        });
        vis.forEach(function (c) { list.appendChild(c); });
    }
    kw.addEventListener('input', apply); sort.addEventListener('change', apply); au.addEventListener('change', apply); apply();
})();
</script>
