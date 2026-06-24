<?php
/**
 * taxonomy.php — タグ/著者/連載 などターム一覧（絞り込み・並び替え付き）
 * note と article を一緒に並べる。絞り込み・並び替えはJSで即時。
 */
get_header();

$oyk_term = get_queried_object();
$oyk_tax  = $oyk_term->taxonomy;

$oyk_heading = $oyk_term->name;
$oyk_kicker  = '';
if ( 'topic' === $oyk_tax )       { $oyk_heading = '#' . $oyk_term->name; $oyk_kicker = 'TAG'; }
elseif ( 'writer' === $oyk_tax )  { $oyk_kicker = '著者'; }
elseif ( 'series' === $oyk_tax )  { $oyk_heading = '連載「' . $oyk_term->name . '」'; $oyk_kicker = 'SERIES'; }
elseif ( 'section' === $oyk_tax )  { $oyk_kicker = 'SECTION'; }

$oyk_q = new WP_Query( array(
    'post_type'      => array( 'note', 'article' ),
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'tax_query'      => array( array(
        'taxonomy' => $oyk_tax,
        'field'    => 'term_id',
        'terms'    => $oyk_term->term_id,
    ) ),
) );
$oyk_chip_terms = array();
?>

<header class="oyk-archive__head">
    <?php if ( $oyk_kicker ) : ?>
        <div class="oyk-arch__kicker"><span class="oyk-arthead__bar"></span><?php echo esc_html( $oyk_kicker ); ?></div>
    <?php endif; ?>
    <h1 class="oyk-archive__title"><?php echo esc_html( $oyk_heading ); ?></h1>
</header>

<?php if ( $oyk_q->have_posts() ) : ?>

    <div class="oyk-filter" data-filter>
        <input type="search" class="oyk-filter__kw" placeholder="キーワードで絞り込み" data-kw>
        <select class="oyk-filter__sort" data-sort>
            <option value="new">新しい順</option>
            <option value="old">古い順</option>
            <option value="author">著者順</option>
            <option value="title">タイトル順</option>
        </select>
    </div>
    <div class="oyk-chips" data-chips></div>

    <div class="oyk-notes__list oyk-notes__list--page" data-list>
        <?php
        while ( $oyk_q->have_posts() ) :
            $oyk_q->the_post();
            $oyk_writers = get_the_terms( get_the_ID(), 'writer' );
            $oyk_writers = ( $oyk_writers && ! is_wp_error( $oyk_writers ) ) ? wp_list_pluck( $oyk_writers, 'name' ) : array();
            $oyk_ptopics = get_the_terms( get_the_ID(), 'topic' );
            $oyk_ptopics = ( $oyk_ptopics && ! is_wp_error( $oyk_ptopics ) ) ? $oyk_ptopics : array();
            $oyk_tslugs  = wp_list_pluck( $oyk_ptopics, 'slug' );
            foreach ( $oyk_ptopics as $pt ) {
                if ( 'topic' === $oyk_tax && $pt->term_id === $oyk_term->term_id ) { continue; }
                $oyk_chip_terms[ $pt->slug ] = $pt->name;
            }
            $oyk_author_str = implode( ' ', $oyk_writers );
            $oyk_label = $oyk_writers ? $oyk_author_str : ( 'article' === get_post_type() ? '記事' : 'NOTE' );
            $oyk_search = mb_strtolower( get_the_title() . ' ' . get_the_excerpt() . ' ' . $oyk_author_str . ' ' . implode( ' ', wp_list_pluck( $oyk_ptopics, 'name' ) ) );
            ?>
            <a class="oyk-note" href="<?php the_permalink(); ?>" data-card
               data-date="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"
               data-author="<?php echo esc_attr( $oyk_author_str ); ?>"
               data-title="<?php echo esc_attr( get_the_title() ); ?>"
               data-tags="<?php echo esc_attr( implode( ' ', $oyk_tslugs ) ); ?>"
               data-search="<?php echo esc_attr( $oyk_search ); ?>">
                <span class="oyk-note__thumb">
                    <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } ?>
                </span>
                <span class="oyk-note__body">
                    <span class="oyk-note__cat"><?php echo esc_html( $oyk_label ); ?></span>
                    <span class="oyk-note__title"><?php the_title(); ?></span>
                    <?php if ( has_excerpt() ) : ?><span class="oyk-note__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 32 ) ); ?></span><?php endif; ?>
                    <span class="oyk-note__date"><?php echo esc_html( get_the_date() ); ?></span>
                </span>
            </a>
            <?php
        endwhile;
        ?>
    </div>
    <p class="oyk-filter__empty" data-empty hidden>条件に合う投稿がありません。</p>

    <?php if ( $oyk_chip_terms ) : ?>
        <script type="application/json" data-chipdata><?php echo wp_json_encode( $oyk_chip_terms ); ?></script>
    <?php endif; ?>

    <script>
    (function () {
        var root = document.querySelector('[data-filter]');
        if (!root) { return; }
        var list = document.querySelector('[data-list]');
        var cards = Array.prototype.slice.call(list.querySelectorAll('[data-card]'));
        var kw = root.querySelector('[data-kw]');
        var sort = root.querySelector('[data-sort]');
        var chipsBox = document.querySelector('[data-chips]');
        var emptyMsg = document.querySelector('[data-empty]');
        var activeTags = [];
        var chipData = {};
        var raw = document.querySelector('[data-chipdata]');
        if (raw) { try { chipData = JSON.parse(raw.textContent); } catch (e) {} }
        Object.keys(chipData).forEach(function (slug) {
            var b = document.createElement('button');
            b.type = 'button'; b.className = 'oyk-chip'; b.textContent = '#' + chipData[slug];
            b.addEventListener('click', function () {
                var i = activeTags.indexOf(slug);
                if (i === -1) { activeTags.push(slug); b.classList.add('is-on'); }
                else { activeTags.splice(i, 1); b.classList.remove('is-on'); }
                apply();
            });
            chipsBox.appendChild(b);
        });
        function apply() {
            var q = (kw.value || '').toLowerCase().trim();
            var shown = 0;
            cards.forEach(function (c) {
                var okKw = !q || (c.getAttribute('data-search') || '').indexOf(q) !== -1;
                var tags = (c.getAttribute('data-tags') || '').split(' ');
                var okTag = activeTags.every(function (t) { return tags.indexOf(t) !== -1; });
                var show = okKw && okTag;
                c.style.display = show ? '' : 'none';
                if (show) { shown++; }
            });
            emptyMsg.hidden = shown !== 0;
            var v = sort.value;
            var vis = cards.filter(function (c) { return c.style.display !== 'none'; });
            vis.sort(function (a, b) {
                if (v === 'new') { return b.getAttribute('data-date').localeCompare(a.getAttribute('data-date')); }
                if (v === 'old') { return a.getAttribute('data-date').localeCompare(b.getAttribute('data-date')); }
                if (v === 'author') { return a.getAttribute('data-author').localeCompare(b.getAttribute('data-author'), 'ja'); }
                if (v === 'title') { return a.getAttribute('data-title').localeCompare(b.getAttribute('data-title'), 'ja'); }
                return 0;
            });
            vis.forEach(function (c) { list.appendChild(c); });
        }
        kw.addEventListener('input', apply);
        sort.addEventListener('change', apply);
        apply();
    })();
    </script>

<?php else : ?>
    <p class="oyk-archive__empty">この条件の投稿はまだありません。</p>
<?php endif; ?>

<?php
wp_reset_postdata();
get_footer();
