<?php
/*
Plugin Name: WP-Kotslider
Plugin URI: http://github.com
Description: Content slider by ejkot
Version: 0.01
Author: ejkot
Author URI: http://github.com
Text Domain: wp-kotslider
*/
?>
<?php
class KotSliderPlugin {
	   private $setup=Array("width"=>"950","height"=>"650","type"=>1);
		public static function init() {
        $kotslider = new self();	
		}
		public function __construct() {
			$this->defineconstants();
			$this->registerActions();
			
			wp_enqueue_style( 'kotslider-admin-styles', KOTSLIDER_BASE_URL . 'css/admin.css', false);

		}
		
		public function activate() {
		return true;
		}
		
		public function deactivate() {
		return true;
		}
		
		public function uninstall() {
		return true;
		}
		
		private function getAdminPage() {
		echo 'ADMIN PAGE';
		}
		
		private function defineconstants() {
		define ("KOTSLIDER_BASE_URL", trailingslashit( plugins_url( 'wp-kotslider' ) ));
		define( 'KOTSLIDER_PATH',       plugin_dir_path( __FILE__ ) );
		}
		
		private function registerActions() {
		add_action('admin_menu',array($this,'register_admin_menu'),9999);
		}
		
		public function register_admin_menu(){
			$capability ='edit_others_posts';
			$page=add_menu_page('Koslider','Kotslider',$capability,'kotslider', array($this, 'render_admin_page'),'dashicons-admin-generic');
		}
		
		public function render_admin_page() {
		$action=$_GET['action'];
		$message="";
		if (!$action || $action=="sliders")	{
			$vars=$this->getSlidersList();
			include(KOTSLIDER_PATH.'html/main.html');
			}
		elseif ($action=="addform") include(KOTSLIDER_PATH.'html/addform.html'); 
		elseif ($action=="add") {
			$post_id=$this->addNewSlider();
			$vars=$this->getSliderData($post_id);
			include(KOTSLIDER_PATH.'html/editform.html');
			} 
		elseif ($action=="editform") {
			$post_id=intval($_GET['sliderid']);
			$vars=$this->getSliderData($post_id);
			include(KOTSLIDER_PATH.'html/editform.html');
			}
		elseif ($action=="addslide") {
			$post_id=intval($_GET['sliderid']);
			$getid=$this->addNewSlide($post_id);
			$vars=$this->getSliderData($post_id);
			include(KOTSLIDER_PATH.'html/editform.html');
			}
		elseif ($action=="save") {
			$vars=$this->parseSliderForm($_POST);
			$this->updateSlider($vars);
			$post_id=$vars['slider']['id'];
			$vars=$this->getSliderData($post_id);
			$message="Changes Saved";
			include(KOTSLIDER_PATH.'html/editform.html');
			}
		elseif ($action=="deleteslide") {
			$sliderid=intval($_GET['sliderid']);
			$post_id=intval($_GET['id']);
			$this->deletePost($post_id);
			$vars=$this->getSliderData($sliderid);
			$message="Slide #".$post_id." deleted.";
			include(KOTSLIDER_PATH.'html/editform.html');
			}
		elseif ($action=="deleteslider") {
			$post_id=intval($_GET['id']);
			$this->deleteChilds($post_id);
			$this->deletePost($post_id);
			$vars=$this->getSlidersList();
			include(KOTSLIDER_PATH.'html/main.html');
			}
		//echo 'SHOPIZDEDC:'.KOTSLIDER_PATH;
		}
		
		private function deleteChilds($id)
			{
			$args = array( 
				'post_parent' => $id,
				'post_type' => 'kot-slide',
				'numberposts' => -1
				);
			$posts=get_children($args);
				if (is_array($posts) && count($posts) > 0) {
					foreach ($posts as $p)
						{
						wp_delete_post($p->ID,true);
						}
				}
			}
		
		private function deletePost($id)
			{
			wp_delete_post($id,true);
			}
		
		private function updateSlider($vars) {
			$args=Array(
				"ID"=>$vars['slider']['id'],
				"post_title"=>$vars['slider']['title'],
				"post_type"=>"kot-slider",
				"post_status"=>"publish"
				);
			wp_insert_post($args);
			
			update_post_meta($vars['slider']['id'],'kot-slider_settings',Array("width"=>$vars['slider']['width'],"height"=>$vars['slider']['height'],"type"=>1));
				foreach ($vars['slides'] as $sli)
					{
						$args=Array(
						"ID"=>$sli["id"],
						"post_type"=>"kot-slide",
						"post_status"=>"inherit",
						"post_parent"=>$vars['slider']['id'],
						"post_title"=>$sli['image'],
						"post_content"=>$sli['content'],
						"post_name"=>"content".$sli['id']
						);
					wp_insert_post($args);
					}
			}
		
		private function parseSliderForm($arr) {
			$slider=Array();
			$slides=Array();
			foreach ($arr as $ak=>$av) {
					if ($ak=="slider_name") $slider['title']=$av;
					if ($ak=="width") $slider['width']=$av;
					if ($ak=="height") $slider['height']=$av;
					if ($ak=="sliderid") $slider['id']=$av;
					if ($ak[0]=='i') {
						$sid=substr($ak,1);
						$img=$av;
						$txt=$arr['t'.$sid];
						$slides[$sid]=Array("id"=>$sid,"image"=>$img,"content"=>$txt);
						}
				}
			return Array("slider"=>$slider,"slides"=>$slides);
			}
		
		private function addNewSlider() {
		$slidername=$_POST['slider_name'];
		$id = wp_insert_post( array(
                'post_title' => __( $slidername, "kotslider" ),
                'post_status' => 'publish',
                'post_type' => 'kot-slider'
            ));
		add_post_meta( $id, 'kot-slider_settings', $this->setup, true );

        wp_insert_term( $id, 'kot-slider' );

        return $id;
		}
		
		private function addNewSlide($sliderid) {
			$settings = array(
				'post_status' => 'inherit',
				'post_type' => 'kot-slide',
				'post_author' => $user_ID,
				'ping_status' => get_option('default_ping_status'),
				'post_parent' => $sliderid,
				'menu_order' => 0,
				'guid' => '',
				);
				$id=wp_insert_post( $settings );
				return $id;
		}
		
		private function getSliderData($id) {
		$post=get_post($id);
		if ($post) {
			$slider=array(
				"id"=>$post->ID,
				"title"=>$post->post_title
			);
		$meta=get_post_meta($id, $key = 'kot-slider_settings', true);
		$slider['width']=$meta['width'];
		$slider['height']=$meta['height'];
			} else return false;
# ������� �������� ����� � ���������� ��������			
			$settings=Array (
			'numberposts' => -1,
			'post_parent' =>$id,
			'post_type'=>'kot-slide',
			'order'=>'ASC'
			);
			$slides_posts=get_children($settings);
			$slides=Array();
			if ($slides_posts) {
					foreach ($slides_posts as $sp) {
							$slides[$sp->ID]['id']=$sp->ID;
							$slides[$sp->ID]['content']=$sp->post_content;
							if (!empty($sp->post_title)) $img=$sp->post_title; else $img=KOTSLIDER_BASE_URL . 'css/addimage.png';
							$slides[$sp->ID]['img']=$img;
							}
					}
			return Array("slider"=>$slider,'slides'=>$slides);

		}
		
		private function getSlidersList() {
		  $args = array(
            'post_type' => 'kot-slider',
            'post_status' => 'publish',
            'orderby' => 'date',
            'suppress_filters' => 1, // wpml, ignore language filter
            'order' => 'DESC',
            'posts_per_page' => -1
			);
		  $posts=get_posts($args);
		  
		  foreach ($posts as $pi) {
		  $meta=get_post_meta($pi->ID,"kot-slider_settings",true);
				$sliders[]=array(
					"id"=>$pi->ID,
					"title"=>$pi->post_title,
					"width"=>$meta["width"],
					"height"=>$meta["height"],
					"type"=>$meta["type"]
					);
				}
		return $sliders;
		  
		}

}
?>
<?php
#Подключение планина
add_action( 'plugins_loaded', array( 'KotSliderPlugin', 'init' ), 10 );

?>