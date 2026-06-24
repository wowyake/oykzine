<?php
/**
 * OYKZINE テーマの機能ファイル
 * --------------------------------------------------------------------
 * このファイルはテーマの「土台」です。
 * ここでやること:
 *   1) コンテンツの「型」を登録する（号 / Notes / 作品）
 *   2) 連載などの分類（タクソノミー）を登録する
 *   3) メニューの置き場所を登録する（フッターナビ / Menuの中身）
 *
 * 見た目（デザイン）はこのファイルには書きません。
 * front-page.php や single-issue.php などのテンプレートで作ります。
 * --------------------------------------------------------------------
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // 直接アクセスは禁止
}

/**
 * テーマの基本サポート設定
 */
function oykzine_setup() {
	// <title> の出力をWordPressに任せる
	add_theme_support( 'title-tag' );

	// アイキャッチ画像を有効化（= 号の表紙、Notesのサムネ等に使う）
	add_theme_support( 'post-thumbnails' );

	// モダンなHTML出力
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
    
    // ロゴ画像を管理画面から設定できるようにする
        add_theme_support( 'custom-logo', array(
            'height'      => 200,
            'width'       => 600,
            'flex-height' => true,
            'flex-width'  => true,
        ) );
    // ブロックエディタ（記事の部品）対応
        add_theme_support( 'align-wide' );        // 画像などを全幅・幅広にできるようにする
        add_theme_support( 'responsive-embeds' ); // YouTube等の埋め込みを画面幅に追従させる
	// メニューの「置き場所」を2つ登録する。
	// 実際に何を入れるかは 外観 > メニュー から触れる（コード不要）。
    // 記事ブロックのカラーパレット（OYKZINEでよく使う色を登録）
        add_theme_support( 'editor-color-palette', array(
            array( 'name' => 'インク（黒）', 'slug' => 'oyk-ink',   'color' => '#0a0a0a' ),
            array( 'name' => 'ペーパー（白）', 'slug' => 'oyk-paper', 'color' => '#ffffff' ),
            array( 'name' => 'グレー',        'slug' => 'oyk-muted', 'color' => '#6b6b6b' ),
            array( 'name' => 'ブルー',        'slug' => 'oyk-blue',  'color' => '#2342d6' ),
        ) );

        // 自由なカスタムカラー（任意のHEX）も選べるようにする
        add_theme_support( 'custom-color' );
	register_nav_menus( array(
		'bottom_nav'  => 'フッターナビ（Home / Search / Menu）',
		'drawer_menu' => 'Menuの中身（今までのOYKZINE / Notes / About / Instagram など）',
	) );
}
add_action( 'after_setup_theme', 'oykzine_setup' );

/**
 * カスタム投稿タイプ（コンテンツの型）の登録
 *   issue = 号
 *   note  = 雑多・コラム（Notes）
 *   work  = 作品（ギャラリー / いずれ販売）
 */
function oykzine_register_post_types() {

	// ---- 号（issue）----
	register_post_type( 'issue', array(
		'labels'        => array(
			'name'          => '号（Issues）',
			'singular_name' => '号',
			'add_new_item'  => '号を追加',
			'edit_item'     => '号を編集',
			'all_items'     => '号の一覧',
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-book',
		'menu_position' => 5,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'rewrite'       => array( 'slug' => 'issues' ),
		'show_in_rest'  => true, // ブロックエディタを使えるようにする
	) );

	// ---- Notes（note）----
	register_post_type( 'note', array(
		'labels'        => array(
			'name'          => 'Notes',
			'singular_name' => 'Note',
			'add_new_item'  => 'Noteを追加',
			'edit_item'     => 'Noteを編集',
			'all_items'     => 'Notesの一覧',
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-edit',
		'menu_position' => 6,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'rewrite'       => array( 'slug' => 'notes' ),
		'show_in_rest'  => true,
	) );

	// ---- 作品（work）----
	register_post_type( 'work', array(
		'labels'        => array(
			'name'          => '作品（Works）',
			'singular_name' => '作品',
			'add_new_item'  => '作品を追加',
			'edit_item'     => '作品を編集',
			'all_items'     => '作品の一覧',
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-format-image',
		'menu_position' => 7,
		'supports'      => array( 'title', 'editor', 'thumbnail' ),
		'rewrite'       => array( 'slug' => 'works' ),
		'show_in_rest'  => true,
	) );

	// ---- 記事（article）= 号に属する個別記事 ----
	// has_archive は false。記事は号の目次から辿るため、記事だけの一覧URLは持たない。
	// page-attributes を有効にすると「順序」欄が出て、目次の並び順を数字で決められる。
	register_post_type( 'article', array(
		'labels'        => array(
			'name'          => '記事（Articles）',
			'singular_name' => '記事',
			'add_new_item'  => '記事を追加',
			'edit_item'     => '記事を編集',
			'all_items'     => '記事の一覧',
		),
		'public'        => true,
		'has_archive'   => false,
		'menu_icon'     => 'dashicons-media-text',
		'menu_position' => 6,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'rewrite'       => array( 'slug' => 'articles' ),
		'show_in_rest'  => true,
	) );
}
add_action( 'init', 'oykzine_register_post_types' );

/**
 * タクソノミー（分類）の登録
 *   series = 連載。今すぐ使わなくても「箱」だけ用意しておく。
 *   note と issue の両方に付けられるようにしておく。
 */
function oykzine_register_taxonomies() {

    // 連載（号をまたぐシリーズ）。あとから連載としてまとめるカテゴリ分け。
    register_taxonomy( 'series', array( 'note', 'issue', 'article' ), array(
        'labels'       => array( 'name' => '連載', 'singular_name' => '連載', 'add_new_item' => '連載を追加' ),
        'public'       => true, 'hierarchical' => true, 'show_in_rest' => true,
        'rewrite'      => array( 'slug' => 'series' ),
    ) );

    // セクション（号の中の枠：THEME / TREND / COLUMN）。
    register_taxonomy( 'section', array( 'article' ), array(
        'labels'       => array( 'name' => 'セクション', 'singular_name' => 'セクション', 'add_new_item' => 'セクションを追加' ),
        'public'       => true, 'hierarchical' => true, 'show_in_rest' => true,
        'rewrite'      => array( 'slug' => 'section' ),
    ) );

    // タグ（#フード などの自由なテーマタグ）。
    register_taxonomy( 'topic', array( 'note', 'article' ), array(
        'labels'       => array( 'name' => 'タグ', 'singular_name' => 'タグ', 'add_new_item' => 'タグを追加' ),
        'public'       => true, 'hierarchical' => false, 'show_in_rest' => true,
        'rewrite'      => array( 'slug' => 'topic' ),
    ) );

    // 著者（寄稿者）。著者ページ＆並び替え用。'author'は予約語なので 'writer' で登録。
    register_taxonomy( 'writer', array( 'note', 'article' ), array(
        'labels'       => array( 'name' => '著者', 'singular_name' => '著者', 'add_new_item' => '著者を追加' ),
        'public'       => true, 'hierarchical' => true, 'show_in_rest' => true,
        'rewrite'      => array( 'slug' => 'writer' ),
    ) );

    // THEME / TREND / COLUMN を最初から用意する（無ければ作る）。
    $oyk_sections = array( 'theme' => 'THEME', 'trend' => 'TREND', 'column' => 'COLUMN' );
    foreach ( $oyk_sections as $oyk_slug => $oyk_name ) {
        if ( ! term_exists( $oyk_slug, 'section' ) ) {
            wp_insert_term( $oyk_name, 'section', array( 'slug' => $oyk_slug ) );
        }
    }
}
add_action( 'init', 'oykzine_register_taxonomies' );

/**
 * スタイルシートの読み込み
 */
function oykzine_enqueue_assets() {
	wp_enqueue_style( 'oykzine-style', get_stylesheet_uri(), array(), '0.1.0' );
}
add_action( 'wp_enqueue_scripts', 'oykzine_enqueue_assets' );

/**
 * ブロックエディタに「OYKZINE囲み（黒地に白）」スタイルを追加する。
 * 記事でグループブロックを挿入し、スタイルでこれを選ぶと黒い囲みになる。
 */
function oykzine_register_block_styles() {
    register_block_style( 'core/group', array(
        'name'  => 'oyk-callout-dark',
        'label' => 'OYKZINE囲み（黒地に白）',
    ) );
}
add_action( 'init', 'oykzine_register_block_styles' );
