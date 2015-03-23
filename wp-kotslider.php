<?php
/*
Plugin Name: WP-Kotslider
Plugin URI: https://github.com/ejkot/wp-kotslider/blob/
Description: Image gallery slider with rich-media captions by ejkot
Version: 0.02
Author: ejkot
Author URI: https://github.com/ejkot/
Text Domain: wp-kotslider
*/
?>
<?php
class KotSliderPlugin {
	   private $plugin_name;
	   private $setup=Array("width"=>"950","height"=>"650","type"=>1);
		public static function init() {
        $kotslider = new self();	
		}
		public function __construct() {
			$this->defineconstants();
			$this->registerActions();
			
			
		 $this->plugin_name = plugin_basename(__FILE__);	

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
		// Функция которая исполняется при активации плагина
		register_activation_hook( $this->plugin_name, array(&$this, 'activate') );
 
		// Функция которая исполняется при деактивации плагина
		register_deactivation_hook( $this->plugin_name, array(&$this, 'deactivate') );
 
		//  Функция которая исполняется удалении плагина
		register_uninstall_hook( $this->plugin_name, array(&$this, 'uninstall') );
		add_shortcode('wks',array($this,'display_wks'));
		add_action('admin_print_footer_scripts', array($this,'add_editor_button') );
		add_action('media_buttons',array($this,'add_wks_button'),11);
		add_action('admin_footer',Array($this,'add_wks_popup'));
		add_action('admin_print_styles',Array($this,'add_wks_css_admin'));
		add_action('wp_print_styles',Array($this,'add_wks_css_wp'));
		add_action('admin_enqueue_scripts',Array($this,'add_wks_js_admin'));
		}
		
		
		
		public function register_admin_menu(){
			$capability ='edit_others_posts';
			$page=add_menu_page('Koslider','Kotslider',$capability,'kotslider', array($this, 'render_admin_page'),'dashicons-admin-generic');
		}
		
		public function add_wks_js_admin() {
		wp_enqueue_script('wks_admin_js',KOTSLIDER_BASE_URL.'js/kotslider-admin.js',false);
		}
		
		public function add_wks_js_wp() {
		wp_enqueue_script('jquery_event_move',KOTSLIDER_BASE_URL.'js/jquery.event.move.js',false);
		wp_enqueue_script('jquery_event_swipe',KOTSLIDER_BASE_URL.'js/jquery.event.swipe.js',false);
		wp_enqueue_script('wks_jquery_plugin',KOTSLIDER_BASE_URL.'js/jquery.kotslider.js',false);
		wp_enqueue_script('wks_start_plugin',KOTSLIDER_BASE_URL.'js/plugin_start.js',false);
		}
		
		public function add_wks_css_admin() {
		wp_enqueue_style( 'kotslider-admin-styles', KOTSLIDER_BASE_URL . 'css/admin.css', false);
		}
		
		public function add_wks_css_wp() {
		wp_enqueue_style( 'kotslider-admin-styles', KOTSLIDER_BASE_URL . 'css/kotslider.css', false);
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
		elseif ($action=="moveup") {
			$sliderid=intval($_GET['sliderid']);
			$id=intval($_GET['id']);
			$this->moveUp($id,$sliderid);
			$vars=$this->getSliderData($sliderid);
			include(KOTSLIDER_PATH.'html/editform.html');
			}
		elseif ($action=="movedown") {
			$sliderid=intval($_GET['sliderid']);
			$id=intval($_GET['id']);
			$this->moveDown($id,$sliderid);
			$vars=$this->getSliderData($sliderid);
			include(KOTSLIDER_PATH.'html/editform.html');
			}
		//echo 'SHOPIZDEDC:'.KOTSLIDER_PATH;
		}
		
		private function moveDown($id,$sliderid) {
		
			$args = array( 
						'post_parent' => $sliderid,
						'post_type' => 'kot-slide',
						'numberposts' => -1,
						'orderby'=>'menu_order',
						'order'=>'ASC'
						);
			$posts=get_children($args);
			$curorder=$posts[$id]->menu_order;
			if (is_array($posts) && count($posts) > 1) {
				$keys=array_keys($posts);
				$fi=array_search($id,$keys);
				$next=$posts[$keys[$fi+1]];	
				if (!empty($next)) {
						$nextorder=$next->menu_order;
						$nextid=$next->ID;
						$this->exchange($id,$curorder,$nextid,$nextorder);
						}
			
			}

		}
		
		private function  moveUp ($id,$sliderid) {
			$args = array( 
						'post_parent' => $sliderid,
						'post_type' => 'kot-slide',
						'numberposts' => -1,
						'orderby'=>'menu_order',
						'order'=>'ASC'
						);
			$posts=get_children($args);
			$curorder=$posts[$id]->menu_order;
			if (is_array($posts) && count($posts) > 1) {
				$keys=array_keys($posts);
				$fi=array_search($id,$keys);
				$prev=$posts[$keys[$fi-1]];
				if (!empty($prev)) {
					$prevorder=$prev->menu_order;
					$previd=$prev->ID;
					$this->exchange($id,$curorder,$previd,$prevorder);
					}
				}

			}
		
		private function exchange($id1,$order1,$id2,$order2) {
		if ($id2 && $id1) {
				$args=Array(
							"ID"=>$id1,
							"post_type"=>"kot-slide",
							"post_status"=>"inherit",
							"menu_order"=>$order2
							);
				wp_update_post($args);
			
				$args=Array(
							"ID"=>$id2,
							"post_type"=>"kot-slide",
							"post_status"=>"inherit",
							"menu_order"=>$order1
							);
				wp_update_post($args);
				}
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
						"post_title"=>utf8_encode($sli['image']),
                                                "post_excerpt"=>$sli['title'],
						"post_content"=>$sli['content'],
						"post_name"=>"content".$sli['id'],
						"menu_order"=>intval($sli['menu_order']),
						'filter' => true
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
						$menu_order=$arr['o'.$sid];
                                                $title=$arr['c'.$sid];
						$slides[$sid]=Array("id"=>$sid,"image"=>$img,"content"=>$txt,"menu_order"=>$menu_order,"title"=>$title);
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
			$order=$this->getNextOrder($sliderid);
			$settings = array(
				'post_status' => 'inherit',
				'post_type' => 'kot-slide',
				'post_author' => $user_ID,
				'ping_status' => get_option('default_ping_status'),
				'post_parent' => $sliderid,
				'menu_order' => $order,
				'guid' => '',
				);
				$id=wp_insert_post( $settings );
				return $id;
		}
		
		private function getNextOrder($sliderid) {
			$settings= array(
				'numberposts' => -1,
				'post_parent' =>$sliderid,
				'post_type'=>'kot-slide',
				);
			$slides_posts=get_children($settings);
			$next=0;
			if ($slides_posts) 
				{
				foreach ($slides_posts as $sp) {
					if ($sp->menu_order>$next) $next=$sp->menu_order;
					}
				$next=$next+10;
				}
			return $next;
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
			'orderby'=>'menu_order',
			'order'=>'ASC'
			);
			$slides_posts=get_children($settings);
			$slides=Array();
			if ($slides_posts) {
					foreach ($slides_posts as $sp) {
							$slides[$sp->ID]['id']=$sp->ID;
							$slides[$sp->ID]['content']=$sp->post_content;
                                                        $slides[$sp->ID]['title']=$sp->post_excerpt;
							if (!empty($sp->post_title) && preg_match("#^http:\/\/#Ui",$sp->post_title)) 
									{
									$img=$sp->post_title;
									$slides[$sp->ID]['embeded']=$sp->post_title;
									} else 
									{
									$img=KOTSLIDER_BASE_URL . 'css/addimage.png';
									$slides[$sp->ID]['embeded']=$sp->post_title;
									}
							$slides[$sp->ID]['img']=$img;
							$slides[$sp->ID]['menu_order']=$sp->menu_order;
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
		
		public function display_wks($atts) {
		$slider_id=$atts['id'];
		if ($slider_id>0) {
				$slider=$this->getSliderData($slider_id);
				if (!empty($slider) && is_array($slider)){
				 $this->add_wks_js_wp();
                                 if ($slider['slider']['width']) $width="width: ".$slider['slider']['width']."px;"; else $width="";
                                 if ($slider['slider']['height']) $height="height: ".$slider['slider']['height']."px;"; else $height="";
				 //$jsdata=Array('width'=>$slider['slider']['width'],'height'=>$slider['slider']['height']);
				// wp_localize_script( 'wks_start_plugin', 'jsdata', $jsdata);
				 ob_start();
?>
<?php if (!is_feed()) { ?>
					
					<div data-id="<?php echo $slider['slider']['id'] ?>" class="kotwrapper">
<?php } ?>
					<ul<?php if (is_feed()) {?> id="is_kotslider"<?php } ?>>
<?php				
						if (!empty($slider['slides'])) {
					$cnt=0;
							foreach ($slider['slides'] as $k=>$s)
								{
?>
	<li>
		<?php /*<div class="kotslider-img" style="width: <?php echo $slider['slider']['width']; ?>px; height: <?php echo $slider['slider']['height']; ?>px;"><img src="<?php echo $s['img']; ?>" alt="<?php echo $s['content']; ?>"/></div> */ ?>
		<?php 
		if (is_feed()) $imalt=""; else 
                    {
                    if ($s['title']) $imalt=strip_tags(str_replace("'","&#39",$s['title'])); else
                            $imalt=strip_tags(str_replace("'","&#39",$s['content']));
                    }
               
		if (preg_match("#^http:\/\/#Ui",$s['embeded'])) $img='<img src="'.$s['img'].'" alt="'.$imalt.'"/>'; else $img='<center>'.$s['embeded'].'</center>';
		?>
		<?php if (!is_feed()) { ?>
		<div class="kotslider-embeded" style="<?php #echo $width." ".$height; ?>">
		<?php } ?>
		<?php echo $img ?>
		<?php if (!is_feed()) { ?>
		</div>
		<?php } ?>
                <?php if ($s['title']) { ?>
                <div class="kotslider-imgtitle">
                    <?php echo $s['title']; ?>
                </div>
                <?php } ?>
		<?php if (!is_feed()) { ?>
		<div class="kotslider-content desc">
		<?php echo $s['content']; ?>
		<?php } else { ?>
		<?php echo strip_tags($s['content'],"<h1><h2><h3><h4><h5><h6><p><strong><b><a>"); ?>
		<?php } ?>
		<?php if (!is_feed()) { ?>
		</div>
		<?php } else { ?><?php } ?>
		<?php /*<div class="kotslider-content"><?php echo $s['content']; ?></div>*/ ?>
	</li>
<?php
		$cnt++;
		if (is_feed() && $cnt>2) break;
								}
						}
?>
					</ul>
<?php if (!is_feed()) { ?>
					</div>
<?php } ?>
<?php
				$res=ob_get_contents();
				ob_clean();
					}
				}
		return $res;
		}
		
		public function add_editor_button() {
			if ( wp_script_is('quicktags') ){ ?>
<script type="text/javascript">
QTags.addButton( 'my_id', 'my button', my_callback );
function my_callback() { alert('Ура!'); } 
</script>
<?php			}
		}
		
		public function add_wks_button() {
			echo '<a href="#TB_inline?width=400&inlineId=wks_popup" class="thickbox button" title="Select slider"><span class="kot-icon"></span>Add Kotslider</a>';
			}
			
		public function add_wks_popup() {
?>
			<div id="wks_popup">
				<div class="wks-popup-form">
					<h3 style="margin-bottom:30px;">Insert kot slider</h3>
					<select name="wks-select" id="wks-select">
<?php
		$sliders=$this->getSlidersList();
		if (!empty($sliders) && is_array($sliders)) {
					foreach ($sliders as $s)
						{
						echo '<option value="'.$s['id'].'">'.$s['title'].'</option>';
						}
				}
?>						
					</select> <button class="button primary" id="wks_insert_button">Insert Slider</button>
				</div>
			</div>
<?php
			}
		


}
?>
<?php
#Подключение плагина
add_action( 'plugins_loaded', array( 'KotSliderPlugin', 'init' ), 10 );

?>