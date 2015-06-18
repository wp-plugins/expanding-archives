<?php

/**
 * Creates the Expanding Archives widget.
 *
 * @package   expanding-archives
 * @copyright Copyright (c) 2015, Ashley Evans
 * @license   GPL2+
 */
class NG_Expanding_Archives_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 *
	 * @return void
	 */
	function __construct() {
		parent::__construct(
			'ng_expanding_archives', // Base ID
			__( 'Expanding Archives', 'expanding-archives' ), // Name
			array( 'description' => __( 'Adds expandable archives of your old posts.', 'expanding-archives' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		echo $args['before_widget'];

		$title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );

		//If title section is filled out, display title.  If not, display nothing.
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		?>
        <div class="ng-expanding-archives-wrap">
        <?php
		global $wpdb;
		$date_current_year  = date( 'Y' );
		$date_current_month = date( 'm' );

		$year_prev = null;
		$months    = $wpdb->get_results( "SELECT DISTINCT MONTH( post_date ) AS month , YEAR( post_date ) AS year, COUNT( id ) as post_count FROM $wpdb->posts WHERE post_status = 'publish' and post_date <= now( ) and post_type = 'post' GROUP BY month , year ORDER BY post_date DESC" );

		foreach ( $months as $month ) {

			$year_current = $month->year;
			if ( $year_current != $year_prev ) {
				if ( $year_prev != null ) {
					?>
                    </ul>
                    </div>
                    </div>
                <?php } ?>

            <div class="expanding-archives-section">

                <h3 class="expanding-archives-title">
                    <a data-toggle="collapse" href="#collapse<?php echo $month->year; ?>"><?php echo $month->year; ?></a>
                </h3>

                <div id="collapse<?php echo $month->year; ?>" class="expanding-archives-collapse-section<?php echo ( $month->year == $date_current_year ) ? ' expanding-archives-expanded' : ''; ?>">
                <ul>
            <?php } ?>

            <li>
                <a href="<?php bloginfo( 'url' ) ?>/<?php echo $month->year; ?>/<?php echo date( "m", mktime( 0, 0, 0, $month->month, 1, $month->year ) ) ?>" data-month="<?php echo date( "m", mktime( 0, 0, 0, $month->month, 1, $month->year ) ); ?>" data-year="<?php echo $month->year; ?>" class="clear expanding-archives-clickable-month <?php echo ( $month->year == $date_current_year && date( "m", mktime( 0, 0, 0, $month->month, 1, $month->year ) ) == $date_current_month ) ? 'expandable-archive-rendered-true' : ''; ?>">

                    <span class="expanding-archive-month">
                    <span class="expand-collapse<?php echo ( $month->year == $date_current_year && date( "m", mktime( 0, 0, 0, $month->month, 1, $month->year ) ) == $date_current_month ) ? ' archive-expanded' : ''; ?>">
                    <?php echo ( $month->year == $date_current_year && date( "m", mktime( 0, 0, 0, $month->month, 1, $month->year ) ) == $date_current_month ) ? '&ndash;' : '+'; ?></span> <?php echo date( "F", mktime( 0, 0, 0, $month->month, 1, $month->year ) ) ?>
                    </span>
                    <i class="fa"></i>
                    <span class="expanding-archive-count">(<?php echo $month->post_count; ?>)</span>

                </a>
                <?php if ( $month->year == $date_current_year && date( "m", mktime( 0, 0, 0, $month->month, 1, $month->year ) ) == $date_current_month ) { ?>
				<div class="expanding-archive-month-results"><?php echo NG_Expanding_Archives()->get_current_month_posts(); ?></div>
			<?php } else { ?>
				<div class="expanding-archive-month-results" style="display:none;"></div>
			<?php } ?>
            </li>
            <?php $year_prev = $year_current;

		}
		?>
        </ul>
        </div><!-- .expanding-archives-collapse-section -->
        </div><!-- .expanding-archives-section -->

        </div>

        <?php

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
	<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

}

add_action( 'widgets_init', function () {
	register_widget( 'NG_Expanding_Archives_Widget' );
} );