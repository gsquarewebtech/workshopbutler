<?php
/**
 * The file that defines WSB_Sidebar class
 *
 * @link       https://workshopbutler.com
 * @since      2.0.0
 *
 * @package    WorkshopButler
 */

namespace WorkshopButler;

/**
 * Represents a sidebar events widget
 *
 * @since      2.0.0
 * @package    WorkshopButler
 * @author     Sergey Kotlov <sergey@workshopbutler.com>
 */
class Sidebar_Widget extends \WP_Widget {

	/**
	 * Requests entity
	 *
	 * @var WSB_Requests
	 */
	private $requests;

	/**
	 * Plugin settings
	 *
	 * @access  protected
	 * @since   2.0.0
	 * @var     WSB_Options $settings Plugin settings
	 */
	protected $settings;

	/**
	 * List of widget fields
	 *
	 * @var Sidebar_Field[] $fields
	 * @since 2.0.0
	 */
	private $fields;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->requests = new WSB_Requests();
		$options        = array(
			'description' => 'Display a list of workshops',
		);
		$this->init_fields();
		parent::__construct( 'wsb_workshop_widget', 'Workshop Butler Events', $options );
	}

	/**
	 * Load the required dependencies for this class.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( __FILE__ ) . '/../../admin/includes/class-sidebar-field.php';
		require_once plugin_dir_path( __FILE__ ) . 'view/class-formatter.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-wsb-requests.php';
		require_once plugin_dir_path( __FILE__ ) . 'models/class-event.php';

		/**
		 * The class responsible for all plugin-related options
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . '../includes/class-wsb-options.php';

		$this->settings = new WSB_Options();
	}

	/**
	 * Renders a widget's settings form
	 *
	 * @since 2.0.0
	 * @param array $instance Widget's instance.
	 */
	public function form( $instance ) {
		foreach ( $this->fields as $name => $field ) {
			switch ( $name  ) {

				
				case 'eventtype':
				?>
					<p>
						<label for="<?php echo $this->get_field_id( $name ); ?>"><?php echo $field->description; ?></label>
						<input class="widefat" id="<?php echo $this->get_field_id( $name ); ?>"
							name="<?php echo $this->get_field_name( $name ); ?>"
							type="<?php echo $this->get_field_name( $field->type ); ?>"
							value="<?php echo esc_attr( isset( $instance[ $name ] ) ? $instance[ $name ] : $field->default_value ); ?>"/>
					</p>
				<?php
				break;
				default:
				?>
					<p>
						<label for="<?php echo $this->get_field_id( $name ); ?>"><?php echo $field->description; ?></label>
						<input class="widefat" id="<?php echo $this->get_field_id( $name ); ?>"
							name="<?php echo $this->get_field_name( $name ); ?>"
							type="<?php echo $this->get_field_name( $field->type ); ?>"
							value="<?php echo esc_attr( isset( $instance[ $name ] ) ? $instance[ $name ] : $field->default_value ); ?>"/>
					</p>
					<?php
			}
		}
	}

	/**
	 * Renders a widget on the page
	 *
	 * @since 2.0.0
	 * @param array $args     Widget's arguments.
	 * @param array $instance Settings for the current instance of the widget.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		echo $this->render( $instance );
		echo $args['after_widget'];
	}

	/**
	 * Updating widget by replacing the old instance with new
	 *
	 * @since 2.0.0
	 * @param array $new_instance New widget instance.
	 * @param array $old_instance Old widget instance.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		foreach ( array_keys( $this->fields ) as $name ) {
			$instance[ $name ] = strip_tags( $new_instance[ $name ] );
		}
		return $instance;
	}


	/**
	 * Initialises a widget's settings fields
	 *
	 * @since 2.0.0
	 */
	private function init_fields() {
		if ( ! is_array( $this->fields ) ) {
			$this->fields = array();
		}
		$this->fields['title']  = new Sidebar_Field( 'text', 'Title', '' );
		$this->fields['length'] = new Sidebar_Field( 'number', 'Number of events', 3 );
		$this->fields['eventtype'] = new Sidebar_Field( 'text', 'Event type 	(Exp: 002,221)', '' );
	}

	/**
	 * Retrieves the sidebar data and renders it
	 *
	 * @param array $instance Settings for the current instance of the widget.
	 *
	 * @since  2.0.0
	 * @return string
	 */
	public function render( $instance ) {
		$method = 'events';
		$fields = 'type,title,location,hashed_id,schedule,free,spoken_languages';
		
		$multievent = explode(',',$instance['eventtype']);
		
		foreach($multievent as $mevent)
		{

			$query  = array(
			'future' => true,
			'public' => true,
			'eventType'   => $mevent,
			'fields' => $fields,
			'length'=> $instance['length']
		);

			
			$evettyperesult = $this->requests->get( $method, $query );

			if($evettyperesult->is_error())
			{

				$response['error'] = $evettyperesult->error;

			}
			else
			{
				for($i=0;$i<count($evettyperesult->body);$i++)
				{
					$response[] = $evettyperesult->body[$i];
				}	
			}	
			
		
		}	
		


		
		return $this->render_list( $response, $instance );
	}

	/**
	 * Renders the list of events
	 *
	 * @param WSB_Response $response Workshop Butler API response.
	 * @param array        $instance Settings for the current instance of the widget.
	 *
	 * @since  2.0.0
	 * @return string
	 */
	private function render_list( $response, $instance ) {

		if (isset($response['error'])) {
			$html  = '<h2>' . __( 'Workshop Butler API: Request failed', 'wsbintegration' ) . '</h2>';
			$html .= '<p>' . __( 'Reason : ', 'wsbintegration' ) . $response->error . '</p>';
			return $html;
		}
		else
		{	
		$events = '<ul>';

		$sliced = array_slice( $response, 0, $instance['length'] );


		
		foreach ( $sliced as $json_event ) {

				

				$event  = new Event(
				$json_event,
				$this->settings->get_event_page_url(),
				$this->settings->get_trainer_page_url(),
				$this->settings->get_registration_page_url()
			);
			$target = '';
			if ( $event->is_url_external() ) {
				$target = ' target="_blank" ';
			}
			$events .= '<li>' .
				Formatter::format( $event->schedule, 'full_short' ) . ', ' .
				Formatter::format( $event->location ) . '<br>' .
				'<a href="' . $event->url() . '" ' . $target . '>' .
				$event->title . '</a></li>';
		}
		$events .= '</ul>';
		return $events;
		}
	}

	/**
	 * Registers the widget
	 *
	 * @since 2.0.0
	 */
	public static function init() {
		register_widget( 'WorkshopButler\Sidebar_Widget' );
	}
}
