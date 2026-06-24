<?php
/** taxonomy.php — タグ/著者/連載ページ。共通フィルターを使う */
get_header();
$oyk_term = get_queried_object();
$oyk_tax  = $oyk_term->taxonomy;
$oyk_kicker = ''; $oyk_heading = $oyk_term->name;

if ( 'topic' === $oyk_tax ) {
    $oyk_kicker = 'TAG'; $oyk_heading = '#' . $oyk_term->name;
    set_query_var( 'oyk_preset_tag', $oyk_term->slug );
} elseif ( 'writer' === $oyk_tax ) {
    $oyk_kicker = '著者';
    set_query_var( 'oyk_preset_author', $oyk_term->name );
} elseif ( 'series' === $oyk_tax ) {
    $oyk_kicker = 'SERIES'; $oyk_heading = '連載「' . $oyk_term->name . '」';
    set_query_var( 'oyk_base_args', array( 'post_type' => array( 'note', 'article' ), 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC', 'tax_query' => array( array( 'taxonomy' => 'series', 'field' => 'term_id', 'terms' => $oyk_term->term_id ) ) ) );
} elseif ( 'section' === $oyk_tax ) {
    $oyk_kicker = 'SECTION';
    set_query_var( 'oyk_base_args', array( 'post_type' => array( 'article' ), 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC', 'tax_query' => array( array( 'taxonomy' => 'section', 'field' => 'term_id', 'terms' => $oyk_term->term_id ) ) ) );
}
?>
<header class="oyk-fhead">
    <?php if ( $oyk_kicker ) : ?><div class="oyk-arch__kicker"><span class="oyk-arthead__bar"></span><?php echo esc_html( $oyk_kicker ); ?></div><?php endif; ?>
    <h1 class="oyk-fhead__title"><?php echo esc_html( $oyk_heading ); ?></h1>
</header>
<?php
get_template_part( 'template-parts/filter-results' );
get_footer();
