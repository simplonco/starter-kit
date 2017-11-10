
/**
 * Enqueue scripts and styles.
 */
function custom_scripts() {
	$dir = '/build';
	$style = "/css/style.css";
	$js = '/js/';
	$manifest = $js . 'manifest.js';
	$vendor = $js . 'vendor.js';
	$jsApp = $js . 'app.js';
	$version = time();
	$mix = ABSPATH . '/build/mix-manifest.json';
    if (file_exists($mix)) {
		$mix = json_decode(file_get_contents($mix), true);
		$manifest = $mix[$manifest];
		$vendor = $mix[$vendor];
		$jsApp = $mix[$jsApp];
		$version = str_replace($style . "?id=", '', $mix[$style]);
    }
	wp_enqueue_style( 'starter-kit-main-style', $dir . $style, array(), $version );

    wp_enqueue_script( 'starter-kit-script-manifest', $dir . $manifest, array(), $version, true );
    wp_enqueue_script( 'starter-kit-script-vendor', $dir . $vendor, array(), $version, true );
    wp_enqueue_script( 'starter-kit-script-app', $dir . $jsApp, array(), $version, true );

}
add_action( 'wp_enqueue_scripts', 'custom_scripts' );