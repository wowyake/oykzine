<?php
/** search.php — 探す（共通フィルターを使う。見出しは小さく） */
get_header();
?>
<header class="oyk-fhead">
    <div class="oyk-arch__kicker"><span class="oyk-arthead__bar"></span>SEARCH</div>
</header>
<?php
set_query_var( 'oyk_kw', get_search_query() );
get_template_part( 'template-parts/filter-results' );
get_footer();
