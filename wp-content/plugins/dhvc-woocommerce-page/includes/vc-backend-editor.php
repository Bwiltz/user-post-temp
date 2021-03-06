<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('Vc_Backend_Editor'))
	require_once vc_path_dir( 'EDITORS_DIR', 'class-vc-backend-editor.php' );

class DHVC_Woo_Page_Vc_Backend_Editor extends Vc_Backend_Editor {
	
	protected static $post_type = 'dhwc_template';
	protected $templates_editor = false;
	protected static $predefined_templates = false;
	
	public function addHooksSettings(){
		parent::addHooksSettings();
	}
	

	public function render( $post_type ) {
		if ( $this->isValidPostType( $post_type ) ) {
			// Disbale Frontend
			vc_disable_frontend();
			
			$this->registerBackendJavascript();
			$this->registerBackendCss();
			// B.C:
			visual_composer()->registerAdminCss();
			visual_composer()->registerAdminJavascript();

			// meta box to render
			add_meta_box( 'wpb_visual_composer', __( 'Template Builder', DHVC_WOO_PAGE ), array(
				$this,
				'renderEditor',
			), $post_type, 'normal', 'high' );
		}
	}

	public function editorEnabled() {
		return true;
	}
	
	public function isValidPostType( $type = '' ) {
		$type = ! empty( $type ) ? $type : get_post_type();
		if('dhwc_page_template'===$type)
			return true;
		return $this->editorEnabled() && $this->postType() === $type;
	}


	public static function postType() {
		return self::$post_type;
	}
	
	public function renderEditorFooter() {
		vc_include_template( 'editors/partials/backend_editor_footer.tpl.php', array(
			'editor' => $this,
			'post' => $this->post,
		) );
		do_action( 'vc_backend_editor_footer_render' );
	}
}
