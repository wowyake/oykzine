<?php
/**
 * footer.php — 全ページ共通の下部
 * --------------------------------------------------------------------
 * 下部固定ナビ（Home / Search / Menu）をここに置く。
 *   - Home   … トップへのリンク
 *   - Search … 押すと検索欄がせり上がる（サイト内検索）
 *   - Menu   … 押すとパネルがせり上がる。中身は 外観 > メニュー の
 *              「Menuの中身（drawer_menu）」を表示するので、項目は
 *              管理画面から追加・並べ替えできる（コード変更不要）。
 * --------------------------------------------------------------------
 */
?>

<nav class="oyk-bottomnav" aria-label="サイトナビ">
	<a class="oyk-bottomnav__btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11l9-7 9 7"/><path d="M5 10v10h14V10"/></svg>
		<span>Home</span>
	</a>
	<button class="oyk-bottomnav__btn" type="button" data-oyk-toggle="search" aria-expanded="false" aria-controls="oyk-search">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.2-4.2"/></svg>
		<span>Search</span>
	</button>
	<button class="oyk-bottomnav__btn" type="button" data-oyk-toggle="drawer" aria-expanded="false" aria-controls="oyk-drawer">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
		<span>Menu</span>
	</button>
</nav>

<div class="oyk-panel oyk-search" id="oyk-search" aria-hidden="true">
	<div class="oyk-panel__head">
		<p class="oyk-panel__title">Search</p>
		<button class="oyk-close" type="button" data-oyk-close aria-label="閉じる">&times;</button>
	</div>
	<form class="oyk-search__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<input class="oyk-search__input" type="search" name="s" placeholder="記事・号・Notesを検索" />
	</form>
</div>

<div class="oyk-panel oyk-drawer" id="oyk-drawer" aria-hidden="true">
	<div class="oyk-panel__head">
		<p class="oyk-panel__title">Menu</p>
		<button class="oyk-close" type="button" data-oyk-close aria-label="閉じる">&times;</button>
	</div>

	<?php /* 上段：号一覧へのリンクをコードで自動表示（ドメイン手打ちなし＝移行しても自動で正しいURL） */ ?>
	<ul class="oyk-drawer__menu oyk-drawer__menu--fixed">
		<li>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'issue' ) ); ?>">今までのOYKZINE</a>
		</li>
	</ul>

	<?php
	/* 下段：管理画面（外観 > メニュー の drawer_menu）で足した項目。
	   Notes / About / Instagram などはここで自由に追加・並べ替えできる。 */
	wp_nav_menu( array(
		'theme_location' => 'drawer_menu',
		'container'      => false,
		'menu_class'     => 'oyk-drawer__menu',
		'fallback_cb'    => false,
		'depth'          => 1,
	) );
	?>
</div>

<script>
/* 下部ナビ：Search / Menu パネルの開閉。
   ・1つ開くと他は閉じる ・×かEscで閉じる ・開いたら入力欄にフォーカス */
(function () {
	var panels = {
		search: document.getElementById('oyk-search'),
		drawer: document.getElementById('oyk-drawer')
	};
	var toggles = document.querySelectorAll('[data-oyk-toggle]');

	function closeAll() {
		Object.keys(panels).forEach(function (k) {
			if (!panels[k]) { return; }
			panels[k].classList.remove('is-open');
			panels[k].setAttribute('aria-hidden', 'true');
		});
		toggles.forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
	}

	toggles.forEach(function (btn) {
		btn.addEventListener('click', function () {
			var p = panels[btn.getAttribute('data-oyk-toggle')];
			if (!p) { return; }
			var willOpen = !p.classList.contains('is-open');
			closeAll();
			if (willOpen) {
				p.classList.add('is-open');
				p.setAttribute('aria-hidden', 'false');
				btn.setAttribute('aria-expanded', 'true');
				var input = p.querySelector('input');
				if (input) { setTimeout(function () { input.focus(); }, 80); }
			}
		});
	});

	document.querySelectorAll('[data-oyk-close]').forEach(function (b) {
		b.addEventListener('click', closeAll);
	});
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') { closeAll(); }
	});
})();
</script>

	<?php wp_footer(); ?>
</body>
</html>
