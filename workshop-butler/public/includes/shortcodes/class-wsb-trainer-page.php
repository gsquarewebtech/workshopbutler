<?php
/**
 * The file that defines the trainer page class
 * @link       https://workshopbutler.com
 * @since      0.2.0
 *
 * @package    WSB_Integration
 */
require_once plugin_dir_path(dirname(__FILE__) ) . 'class-wsb-page.php';

/**
 * Trainer Page class which handles the rendering and logic for the profile of trainer
 *
 * @since      0.2.0
 * @package    WSB_Integration
 * @author     Sergey Kotlov <sergey@workshopbutler.com>
 */
class WSB_Trainer_Page extends WSB_Page {
    
    private $requests;
    
    /**
     * Initialize the class and set its properties.
     *
     * @since    0.2.0
     */
    public function __construct() {
        parent::__construct();
        $this->load_dependencies();
        $this->requests = new WSB_Requests();
    }
    
    /**
     * Load the required dependencies for this class.
     *
     * @since    0.2.0
     * @access   private
     */
    private function load_dependencies() {
        require_once plugin_dir_path( dirname(__FILE__ ) ) . 'class-wsb-requests.php';
        require_once plugin_dir_path(dirname(__FILE__ ) ) . 'models/class-trainer.php';
    }
    
    /**
     * Renders the trainer page
     *
     * @param array $attrs Shortcode attributes
     * @param string $content Shortcode content
     *
     * @since  0.2.0
     *
     * @return string
     */
    public function render( $attrs = [], $content = null ) {
        if (empty( $_GET['id'])) {
            return $this->format_error('empty trainer ID');
        }
        $id = $_GET['id'];
        $may_be_trainer = $this->dict->get_trainer();
        if (is_null($may_be_trainer)) {
            $may_be_trainer = $this->requests->retrieve_trainer($id);
        }
        if (is_wp_error($may_be_trainer)) {
            return $this->format_error($may_be_trainer->get_error_message());
        }
    
        wp_enqueue_script( "wsb-single-trainer-scripts" );
        $this->add_theme_fonts();
        $wsb_nonce = wp_create_nonce( 'wsb-nonce' );
    
        wp_localize_script( 'wsb-single-trainer-scripts', 'wsb_single_trainer', array(
            'ajax_url'   => admin_url( 'admin-ajax.php' ),
            'nonce'      => $wsb_nonce,
            'trainer_id' => $id,
        ) );
        
        return $this->render_profile($may_be_trainer);
    }
    
    /**
     * Renders the profile of trainer
     *
     * @param Trainer $trainer Current trainer
     *
     * @return string
     */
    private function render_profile( $trainer ) {
        $template_data = array('trainer' => $trainer,
                               'theme' => $this->get_theme());
    
        $custom_template = $this->settings->get(WSB_Options::TRAINER_TEMPLATE);
        $template = $this->get_template('trainer-page', $custom_template);
        
        $processed_template = do_shortcode($template);
        $content = $this->compile_string($processed_template, $template_data);
        
        return $this->add_custom_styles($content);
    }
    
    /**
     * Handles 'wsb_trainer' shortcode
     *
     * @param $attrs   array  Shortcode attributes
     * @param $content string Shortcode content
     * @since  0.2.0
     * @return string
     */
    static public function page( $attrs = [], $content = null ) {
        $page = new WSB_Trainer_Page();
        
        return $page->render( $attrs, $content );
    }
    
    /**
     * Renders a simple shortcode with no additional logic
     * @param string       $name Name of the shortcode (like 'title', 'register'
     * @param array        $attrs  Attributes
     * @param null|string  $content Replaceable content
     *
     * @return string
     */
    protected function render_simple_shortcode($name, $attrs = [], $content = null) {
        $trainer = $this->dict->get_trainer();
        if (!is_a($trainer, 'Trainer')) {
            return '';
        }
        $template = $this->get_template('trainer/' . $name, null);
        if (!$template) {
            return '[wsb_trainer_' . $name . ']';
        }
        $attrs['trainer'] = $trainer;
        return $this->compile_string($template, $attrs);
    }
    
    
}