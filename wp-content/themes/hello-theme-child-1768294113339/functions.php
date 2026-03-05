<?php
/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0' );

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function hello_elementor_child_scripts_styles() {

	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		HELLO_ELEMENTOR_CHILD_VERSION
	);

}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );



function my_custom_scripts()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('manage-services-js', get_stylesheet_directory_uri() . '/js/custom.js?v=1.0');

}
add_action('wp_enqueue_scripts', 'my_custom_scripts');


function external_load() {

    // Custom style
  //  wp_enqueue_style('custom-style2', get_stylesheet_directory_uri() . '/style.css?v=1.0');

    // jQuery UI
    wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', [], null);
    wp_enqueue_script('jquery-ui-js', 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js', ['jquery'], null, true);

}
add_action('wp_enqueue_scripts', 'external_load');



//OPTIMISATION FAITE PAR YOUSR ////
add_action('wp_head', function () {
    ?>
    <!-- Preload CSS thème enfant (version normale) -->
    <link rel="preload"
          href="/wp-content/themes/hello-theme-child-1768294113339/style.css"
          as="style"
          onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="/wp-content/themes/hello-theme-child-1768294113339/style.css">
    </noscript>

    <!-- Preload CSS minifié (WP Rocket / cache) -->
 	
	<link rel="preload"
          href="/wp-content/cache/min/1/wp-content/themes/hello-theme-child-1768294113339/style.css"
          as="style"
          onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="/wp-content/cache/min/1/wp-content/themes/hello-theme-child-1768294113339/style.css">
    </noscript>
	
	 <link rel="preload"
          href="/wp-content/cache/min/1/wp-content/themes/hello-elementor/assets/css/header-footer.css?ver=1768574184"
          as="style"
          onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="/wp-content/cache/min/1/wp-content/themes/hello-elementor/assets/css/header-footer.css?ver=1768574184">
    </noscript>

	
	 <link rel="preload"
          href="/wp-content/themes/hello-elementor/assets/css/header-footer.css"
          as="style"
          onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="/wp-content/themes/hello-elementor/assets/css/header-footer.css">
    </noscript>


    <?php
}, 1);


function lcp_desktop_img_shortcode() {
  return '<img
    src="https://n1i2026.dev.makedifferent.fr/wp-content/uploads/2026/01/immobilier-annonces-desktop.webp"
    width="1200"
    height="700"
    fetchpriority="high"
    decoding="async"
    style="display:block;width:100%;height:auto;"
  >';
}
add_shortcode('lcp_desktop_img', 'lcp_desktop_img_shortcode');
function lcp_mobile_img_shortcode() {
  $src = 'https://n1i2026.dev.makedifferent.fr/wp-content/uploads/2026/01/img_mobile_mai_2024.webp';

  return '<img
    class="lcp-hero-img"
    src="https://n1i2026.dev.makedifferent.fr/wp-content/uploads/2026/01/img_mobile_mai_2024.webp"
    loading="eager"
	fetchpriority="high"
	data-no-lazy|true
	width="412"
	height="474"
	decoding="sync"
    alt="N1IMMO"
    style="display:block;width:100%;height:auto;"
  >';
}
add_shortcode('lcp_mobile_img', 'lcp_mobile_img_shortcode');

add_action('wp_head', function () {
  echo '<link rel="preload" as="image" href="https://n1i2026.dev.makedifferent.fr/wp-content/uploads/2026/01/img_mobile_mai_2024.webp" media="(max-width: 767px)" fetchpriority="high">' . "\n";
  echo '<link rel="preload" as="image" href="https://n1i2026.dev.makedifferent.fr/wp-content/uploads/2026/01/immobilier-annonces-desktop.webp" media="(min-width: 768px)" fetchpriority="high">' . "\n";
}, 1);


add_action('wp_enqueue_scripts', function () {
  if (is_front_page()) {
    wp_dequeue_style('elementor-widget-nav-menu');
    wp_dequeue_style('elementor-widget-icon-list');
    wp_dequeue_style('elementor-widget-posts');
  }
}, 100);



 add_action('wp_head', function () {
  if (!is_front_page()) return;

  // Mets l’URL EXACTE du background desktop (celle réellement chargée)
  $bg = '/wp-content/uploads/2026/01/immobilier-annonces.webp';

  echo '<link rel="preload" as="image" href="'.esc_url($bg).'" fetchpriority="high" imagesizes="100vw">' . "\n";
}, 1);


add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment, $size) {

  // cible le logo via son ID (163616 d’après ta capture)
  if ((int)$attachment->ID === 163616) {
    $attr['loading'] = 'eager';
    $attr['fetchpriority'] = 'high';
    $attr['decoding'] = 'async';
  }

  return $attr;
}, 10, 3);

/**
 * Supprimer fetchpriority=high sur les images non LCP (logo Elementor)
 */
add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment) {

  if (!empty($attr['class']) && strpos($attr['class'], 'custom-logo') !== false) {
    unset($attr['fetchpriority']);
    $attr['loading'] = 'lazy';
  }

  return $attr;
}, 10, 2);


/**
 * Corrige sizes pour le logo (évite 100vw inutile)
 */
add_filter('wp_get_attachment_image_attributes', function ($attr) {

  if (!empty($attr['class']) && strpos($attr['class'], 'custom-logo') !== false) {
    $attr['sizes'] = '(max-width: 768px) 200px, 300px';
  }

  return $attr;
}, 11);


add_action('wp_enqueue_scripts', function () {

  wp_enqueue_style(
    'fe-fonts',
    get_stylesheet_directory_uri() . '/style.css',
    [],
    filemtime(get_stylesheet_directory() . '/style.css')
  );

}, 5); // priorité basse = chargé tôt


/**
 * Corrige l'image LCP (Elementor hero)
 */
add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment) {

  // détecte l'image hero LCP
  if (
    !empty($attr['src']) &&
    strpos($attr['src'], 'Agence.jpg') !== false
  ) {
    $attr['loading'] = 'eager';
    $attr['fetchpriority'] = 'high';
    $attr['decoding'] = 'async';

    // supprime toute trace de lazy
    unset($attr['data-lazy-src'], $attr['data-lazy-srcset']);
  }

  return $attr;
}, 20, 2);

add_filter('rocket_lazyload_excluded_attributes', function ($attrs) {
  $attrs[] = 'fetchpriority';
  return $attrs;
});


add_action('wp_head', function () {
  echo '<link rel="preload" as="image" href="https://www.n1immo.com/wp-content/uploads/2023/02/Agence.jpg" fetchpriority="high">';
}, 1);

 /**
 * Disable Elementor post CSS (post-xxx.css)
 */
add_filter('elementor/frontend/print_css', '__return_false');
add_action('wp_head', function () {
  echo '<link rel="preload" href="/wp-content/cache/min/1/elementor/frontend.min.css" as="style" />';
}, 1);





/**
 * Ne charger Elementor que sur les pages qui l'utilisent
 */
add_action('wp_enqueue_scripts', function () {

  // Ne rien faire dans l'admin
  if (is_admin()) return;

  // Si Elementor n'est pas actif, stop
  if (!did_action('elementor/loaded')) return;

  // Détecter si la page courante utilise Elementor
  $post_id = get_queried_object_id();
  $uses_elementor = false;

  if ($post_id) {
    // Méthode officielle
    if (class_exists('\Elementor\Plugin')) {
      $uses_elementor = \Elementor\Plugin::$instance->documents->get($post_id)
        ? \Elementor\Plugin::$instance->documents->get($post_id)->is_built_with_elementor()
        : false;
    }

    // Fallback : meta Elementor
    if (!$uses_elementor) {
      $uses_elementor = (bool) get_post_meta($post_id, '_elementor_edit_mode', true);
    }
  }

  // Si la page n'utilise pas Elementor => on enlève ses assets
  if (!$uses_elementor) {
    // CSS
    wp_dequeue_style('elementor-frontend');
    wp_dequeue_style('elementor-post-' . $post_id);
    wp_dequeue_style('elementor-icons');
    wp_dequeue_style('elementor-animations');

    // JS
    wp_dequeue_script('elementor-frontend');
    wp_dequeue_script('elementor-webpack-runtime');
    wp_dequeue_script('elementor-frontend-modules');
    wp_dequeue_script('swiper');
  }

}, 100);

add_action('wp_enqueue_scripts', function () {
  if (is_admin()) return;
  if (!did_action('elementor/loaded')) return;

  $post_id = get_queried_object_id();

  // 1) page built with Elementor ?
  $uses_elementor = false;
  if ($post_id && class_exists('\Elementor\Plugin')) {
    $doc = \Elementor\Plugin::$instance->documents->get($post_id);
    $uses_elementor = $doc ? $doc->is_built_with_elementor() : false;
  }

  // 2) Header/Footer Elementor (Theme Builder) ?
  $uses_theme_builder = false;
  if (class_exists('\ElementorPro\Plugin')) {
    // Si Elementor Pro est actif et a des templates pour cette page
    $uses_theme_builder = true; // par défaut on suppose oui si Pro est là (safe)
  }

  // Si ni page Elementor ni Theme Builder => on coupe Elementor
  if (!$uses_elementor && !$uses_theme_builder) {

    // Déqueue "front" Elementor (handles classiques)
    foreach ([
      'elementor-frontend',
      'elementor-frontend-css',
      'elementor-icons',
      'elementor-icons-fa-solid',
      'elementor-icons-fa-regular',
      'elementor-icons-fa-brands',
      'elementor-animations',
      'swiper',
      'swiper-css',
    ] as $h) {
      wp_dequeue_style($h);
      wp_deregister_style($h);
      wp_dequeue_script($h);
      wp_deregister_script($h);
    }

    // Déqueue toutes les feuilles Elementor générées "post-XXX.css"
    global $wp_styles;
    if ($wp_styles && !empty($wp_styles->queue)) {
      foreach ($wp_styles->queue as $handle) {
        if (strpos($handle, 'elementor-post-') === 0) {
          wp_dequeue_style($handle);
          wp_deregister_style($handle);
        }
      }
    }
  }

}, 9999);

add_action('wp_enqueue_scripts', function () {
  if (is_admin()) return;

  // Si on n'est pas sur une page qui contient un carousel/slider, on enlève swiper
  if (!is_front_page() && !is_page_template() ) {
    wp_dequeue_style('swiper');
    wp_dequeue_style('e-swiper');
    wp_dequeue_script('swiper');
    wp_dequeue_script('e-swiper');
  }
}, 9999);


/**
 * Elementor Pro: couper Swiper/Carousel partout
 * et ne les garder QUE sur les pages qui en ont besoin.
 */
add_action('wp_enqueue_scripts', function () {
  if (is_admin()) return;

  // Whitelist pages qui utilisent vraiment un slider/carousel
  $allow_swiper = is_front_page() || is_page([8, 30]); // <-- Mets ici TES IDs

  if (!$allow_swiper) {
    // CSS
    wp_dequeue_style('swiper');
    wp_dequeue_style('e-swiper');
    wp_dequeue_style('elementor-swiper'); // selon versions
    wp_dequeue_style('widget-carousel-module-base');
    wp_dequeue_style('widget-testimonial-carousel');

    // JS
    wp_dequeue_script('swiper');
    wp_dequeue_script('e-swiper');
    wp_dequeue_script('elementor-swiper');
  }
}, 9999);

/**
 * Rendre non-bloquants les CSS Elementor / Elementor Pro (render-blocking -> async)
 * - elementor/assets/css/*.css
 * - uploads/elementor/css/post-*.css
 */
add_filter('style_loader_tag', function ($html, $handle, $href, $media) {

  if (is_admin()) return $html;

  // Cible: CSS Elementor + CSS générés post-xx + conditionals (e-swiper etc.)
  $is_elementor_css =
    (strpos($href, '/wp-content/plugins/elementor/') !== false) ||
    (strpos($href, '/wp-content/plugins/elementor-pro/') !== false) ||
    (strpos($href, '/wp-content/uploads/elementor/css/') !== false);

  if (!$is_elementor_css) return $html;

  // Ne touche pas si déjà "print/onload"
  if (strpos($html, "media='print'") !== false || strpos($html, 'media="print"') !== false) {
    return $html;
  }

  // Construit un link non-bloquant + fallback noscript
  $href_esc = esc_url($href);

  $out  = "<link rel='preload' as='style' href='{$href_esc}'>\n";
  $out .= "<link rel='stylesheet' href='{$href_esc}' media='print' onload=\"this.media='all'\">\n";
  $out .= "<noscript><link rel='stylesheet' href='{$href_esc}'></noscript>\n";

  return $out;

}, 10, 4);
 
add_filter('script_loader_tag', function ($tag, $handle, $src) {

  if (in_array($handle, ['jquery', 'jquery-core', 'jquery-migrate'], true)) {
    $tag = preg_replace('/\sdefer(="defer")?/i', '', $tag);
    $tag = preg_replace('/\sasync(="async")?/i', '', $tag);
  }

  return $tag;

}, 9999, 3);

 
add_action('wp_enqueue_scripts', function () {
  if (is_admin()) return;

  // Gutenberg CSS (front)
  wp_dequeue_style('wp-block-library');
  wp_dequeue_style('wp-block-library-theme');

  // Certains sites ont aussi ça
  wp_dequeue_style('global-styles');

}, 100);

add_filter('style_loader_tag', function ($html, $handle, $href, $media) {
  if (is_admin()) return $html;

  // hello-elementor theme.css (direct ou minifié)
  $is_hello_theme_css =
    (strpos($href, '/themes/hello-elementor/assets/css/theme.css') !== false) ||
    (strpos($href, '/cache/min/') !== false && strpos($href, 'hello-elementor') !== false && strpos($href, 'theme.css') !== false);

  if (!$is_hello_theme_css) return $html;

  $href_esc = esc_url($href);

  return "<link rel='preload' as='style' href='{$href_esc}'>\n"
    . "<link rel='stylesheet' href='{$href_esc}' media='print' onload=\"this.media='all'\">\n"
    . "<noscript><link rel='stylesheet' href='{$href_esc}'></noscript>\n";

}, 10, 4);
add_filter('style_loader_tag', function ($html, $handle, $href, $media) {
  if (is_admin()) return $html;

  $is_jquery_ui_css =
    (strpos($href, '/wp-includes/css/jquery-ui/') !== false) ||
    (strpos($href, 'jquery-ui.css') !== false);

  if (!$is_jquery_ui_css) return $html;

  $href_esc = esc_url($href);

  return "<link rel='preload' as='style' href='{$href_esc}'>\n"
    . "<link rel='stylesheet' href='{$href_esc}' media='print' onload=\"this.media='all'\">\n"
    . "<noscript><link rel='stylesheet' href='{$href_esc}'></noscript>\n";

}, 11, 4);


add_action('wp_enqueue_scripts', function () {
  if (is_admin()) return;

  // Elementor / Elementor Pro - Font Awesome
  foreach ([
    'elementor-icons-fa-solid',
    'elementor-icons-fa-regular',
    'elementor-icons-fa-brands',
    'elementor-icons',
  ] as $h) {
    wp_dequeue_style($h);
    wp_deregister_style($h);
  }
}, 9999);


add_filter('style_loader_tag', function ($html, $handle, $href, $media) {
  if (is_admin()) return $html;

  // Cible reset.css Hello Elementor (direct ou minifié WP Rocket)
  $is_reset =
    (strpos($href, '/themes/hello-elementor/assets/css/reset.css') !== false) ||
    (strpos($href, '/cache/min/') !== false && strpos($href, 'hello-elementor') !== false && strpos($href, 'reset.css') !== false);

  if (!$is_reset) return $html;

  $href = esc_url($href);

  return "<link rel='preload' as='style' href='{$href}'>\n"
    . "<link rel='stylesheet' href='{$href}' media='print' onload=\"this.media='all'\">\n"
    . "<noscript><link rel='stylesheet' href='{$href}'></noscript>\n";

}, 10, 4);


add_action('wp_head', function () {
  ?>
  <style>
    *,*::before,*::after{box-sizing:border-box}
    body{margin:0}
    img,svg{display:block;max-width:100%;height:auto}
  </style>
  <?php
}, 1);
  
/**
 * PERF CLEAN PACK (WordPress) - SAFE VERSION
 * - LCP image preload (front page)
 * - Dashicons off for visitors
 * - Optional: remove Gutenberg styles (commented)
 * - Preload fonts (kept, but guarded)
 * - Defer CSS by handles + by URL patterns (merged into ONE filter)
 */

/** 1) Preload LCP image (front page only) */
add_action('wp_head', function () {
  if (!is_front_page()) return;

  // URL EXACTE de l'image LCP (OK)
  $lcp = home_url('/wp-content/uploads/2026/02/img_mobile.webp');

  // Important: ajoute imagesrcset/imagesizes si ton thème sert plusieurs tailles (sinon laisse comme ça)
  echo '<link rel="preload" as="image" href="' . esc_url($lcp) . '" fetchpriority="high" imagesizes="100vw">' . "\n";
}, 1);


add_action('wp_head', function () {
  if (!is_front_page()) return;

  $href = home_url('/wp-content/uploads/2026/02/img_mobile.webp');

  // Mets ICI le srcset exact généré par WP (copié depuis le <img>)
  $srcset = home_url('/wp-content/uploads/2026/02/img_mobile.webp') . ' 659w, '
          . home_url('/wp-content/uploads/2026/02/img_mobile-768x?.webp') . ' 768w';

  echo '<link rel="preload" as="image" href="'.esc_url($href).'" imagesrcset="'.esc_attr($srcset).'" imagesizes="100vw" fetchpriority="high">'."\n";
}, 1);
 
/** 2) Remove dashicons for non-logged visitors */
add_action('wp_enqueue_scripts', function () {
  if (!is_user_logged_in()) {
    wp_dequeue_style('dashicons');
    wp_deregister_style('dashicons');
  }
}, 100);

 

/**
 * 4) Preload fonts (kept) - guarded to avoid duplicated output & keep stable behavior
 * Note: preload n'améliore que si la font est vraiment utilisée above-the-fold.
 */
 
/**
 * FONTS PERFORMANCE SAFE PACK
 * - preload ONLY 1 font used by LCP (Open Sans for your H2)
 * - add font-display: swap for all fonts to prevent blocking
 * - DO NOT preload FontAwesome / Rubik / Prata (keep them, but non-critical)
 */
add_action('wp_head', function () {
  echo "<style>
/* Prata */
@font-face{
  font-family:'Prata';
  font-style:normal;
  font-weight:400;
  font-display:swap;
  src:url('/wp-content/themes/hello-theme-child-1768294113339/css/fonts/Prata-Regular.woff2') format('woff2');
}

/* Rubik */
@font-face{
  font-family:'Rubik';
  font-style:normal;
  font-weight:500;
  font-display:swap;
  src:url('/wp-content/themes/hello-theme-child-1768294113339/css/fonts/rubik/Rubik-Medium.woff2') format('woff2');
}
@font-face{
  font-family:'Rubik';
  font-style:normal;
  font-weight:700;
  font-display:swap;
  src:url('/wp-content/themes/hello-theme-child-1768294113339/css/fonts/rubik/Rubik-Bold.woff2') format('woff2');
}

/* Limite le poids de Rubik au-dessus de la ligne de flottaison */
header, .elementor-location-header,
.elementor-top-section:first-of-type,
.elementor-section:first-of-type{
  font-weight: 500 !important; /* évite le Bold en initial */
} 

/* Mobile: évite le chargement de 2 variantes Rubik au-dessus du fold */
@media (max-width: 1024px){
  header, .elementor-location-header,
  .elementor-top-section:first-of-type,
  .elementor-section:first-of-type,
  .elementor-container, .e-con, .e-con-inner{
    font-weight: 500 !important;
  }
}
@media (max-width: 1024px){
  header,
  .elementor-top-section:first-of-type{
    font-family: 'Rubik', system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif !important;
  }
}
</style>\n";
}, 1); 

/** B) Force font-display: swap to avoid blocking (all 4 families) */

 
/**
 * 5) Defer / async CSS
 * IMPORTANT: on fusionne TOUT dans UN SEUL filtre pour éviter des collisions.
 * On ne touche pas au print CSS du core, uniquement à la balise de style concernée.
 */
add_filter('style_loader_tag', function ($html, $handle, $href, $media) {

  // 1) NE JAMAIS différer les styles critiques (sinon LCP texte se décale)
  $never_defer_handles = [
    'elementor-frontend',
    'elementor-pro',
    'hello-elementor',
    'global-styles',
    'wp-block-library',
    'wp-block-library-theme',
  ];
  foreach ($never_defer_handles as $h) {
    if ($handle === $h) return $html;
  }

  // 2) Defer par handles (si tu les actives)
  $defer_handles = [
    // 'popup-maker-site',
    // 'cute-alert',
  ];

  // 3) Defer par patterns URL (avis etc.)
  $defer_url_patterns = [
    '/g-business-reviews-rating/',
    '/wp-ultimate-review/',
  ];

  $should_defer = in_array($handle, $defer_handles, true);

  if (!$should_defer) {
    foreach ($defer_url_patterns as $t) {
      if (strpos($href, $t) !== false) { $should_defer = true; break; }
    }
  }

  if ($should_defer) {
    if (empty($href)) return $html;
    if (stripos($html, "rel='preload'") !== false || stripos($html, 'rel="preload"') !== false) {
      return $html;
    }
    $href_esc = esc_url($href);

    return "<link rel='preload' as='style' href='{$href_esc}' onload=\"this.onload=null;this.rel='stylesheet'\">"
         . "<noscript><link rel='stylesheet' href='{$href_esc}'></noscript>";
  }

  return $html;

}, 20, 4);

 
/**
 * Avis uniquement sur /livre-dor/
 * - g-business-reviews-rating
 * - wp-ultimate-review
 */
add_action('wp_enqueue_scripts', function () {

  // Page unique autorisée (slug)
  $is_livre_dor = is_page('livre-dor');

  if ($is_livre_dor) return;

  // STYLES: retire tout ce qui vient de ces plugins
  global $wp_styles;
  if (!empty($wp_styles->registered)) {
    foreach ($wp_styles->registered as $handle => $style) {
      $src = isset($style->src) ? $style->src : '';
      if (!$src) continue;

      if (
        strpos($src, 'g-business-reviews-rating') !== false ||
        strpos($src, 'wp-ultimate-review') !== false
      ) {
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
      }
    }
  }

  // SCRIPTS: retire les JS des plugins d’avis (si présents)
  global $wp_scripts;
  if (!empty($wp_scripts->registered)) {
    foreach ($wp_scripts->registered as $handle => $script) {
      $src = isset($script->src) ? $script->src : '';
      if (!$src) continue;

      if (
        strpos($src, 'g-business-reviews-rating') !== false ||
        strpos($src, 'wp-ultimate-review') !== false
      ) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
      }
    }
  }

}, 999);
 
 
 