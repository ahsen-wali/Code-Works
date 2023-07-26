<?php
/**
 * GeneratePress child theme functions and definitions.
 *
 * Add your custom PHP in this file. 
 * Only edit this file if you have direct access to it on your server (to fix errors if they happen).
 */
if( ! class_exists( 'Intersol_Extended_Cart' ) ){
    /**
     * @class Intersol_Extended_Cart
     * Controls the custom meta data of orders, attaching attendee information based on products and their quantities.
     * Sends emails based on attendees.
     * @function __construct
     * initializes action and filter hooks
     */
    class Intersol_Extended_Cart{
        public function __construct(){
            add_action( 'admin_init', array( $this, 'register_acf_email_body' ), 10, 0 );
            add_action( 'admin_init', array( $this, 'register_acf_reminder_email_body' ), 10, 0 );
            add_action( 'woocommerce_review_order_before_payment', array( $this, 'generate_forms' ), 10, 0 );
            add_action( 'woocommerce_checkout_process', array( $this, 'validate_custom_fields' ), 10, 0 );
            add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_custom_form_fields' ), 10, 1 );
            //add_action( 'woocommerce_order_details_after_order_table', array( $this, 'add_attendee_info_to_order_received' ), 10, 1 );
            add_action( 'woocommerce_thankyou_order_received_text', array( $this, 'add_to_confirmation_message' ), 10, 2 );
            add_action( 'woocommerce_email_order_meta', array( $this, 'add_attendee_info_to_email' ), 10, 4 );
            add_action( 'woocommerce_order_status_pending_to_processing', array( $this, 'send_emails_to_attendees' ), 10, 1 );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ), 10, 0 );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend_scripts' ), 10, 0 );
            add_action( 'add_meta_boxes', array( $this, 'add_meta_box_for_orders' ), 10, 0 );
			add_action( 'after_setup_theme', array( $this, 'set_cron_job' ) );
			add_filter( 'cron_schedules', array( $this, 'addCronMinutes' ) );
			add_action( 'set_wp_cron_notification', array( $this, 'execute_cron_job' ) );
			add_action( 'switch_theme', array( $this, 'changeTheme' ) );
			add_action('admin_menu', array( $this,'admin_menu') );
        }
		
		function admin_menu(){
			add_menu_page('Cron Setting Page', 'Cron Setting Page', 'manage_options', 'check_cron_job', array($this, 'cronsetting') );
		}
		
		function cronsetting(){
			if(isset($_POST['submit_run_cron'])){
				$this->fetchRecords();
			}
			
			if(isset($_POST['save_settings'])){
				$timeset = $_POST['time_interval'];
				
				update_option("cron_time_set",$timeset);
				
				
				echo '<h1>Your settings has been saved successfully.</h1>';
			}
			
			$crontime = get_option("cron_time_set");
			
			?>
			<h2 class="nav-tab-wrapper">
				<a href="?page=check_cron_job&tab=cron_setting" class="nav-tab ">Cron Setting</a>
				<form method="post" action=""><a href="" class="nav-tab"><input type="submit" name="submit_run_cron" value="Manually Run Notification"></a></form>
			</h2>
			<div><h3>Set Time interval for cron job and event credentials</h3></div>
			<table>	
				<tbody>	
					<form method="post" action="">
						<tr>
							<td><b>Select Interval</b></td>
							<td>
							<select name="time_interval" required>
								<option value="">select interval</option>
								<option <?php if( $crontime == 180 ){ echo "selected"; } ?> value="180">After 15 Minute</option>
								<option <?php if( $crontime == 1800 ){ echo "selected"; } ?> value="1800">After 30 Minute</option>
								<option  <?php if( $crontime == 3600 ){ echo "selected"; } ?> value="3600">After One Hour</option>
								<option  <?php if( $crontime == 43200 ){ echo "selected"; } ?> value="43200">Twise Daily</option>
								<option  <?php if( $crontime == 86400 ){ echo "selected"; } ?> value="86400">One Time Daily</option>
							</select>
							</td>
						</tr>
						
						<tr>
							<td><input type="submit" value="Save" name="save_settings" class="btn btn-primary"></td>
						</tr>
					</form>
				<tbody>	
			</table>
			<?php
		}
		
		function add_to_outlook($startTime , $endTime){
			$message = '';
			
			//Event setting
			$ical = 'BEGIN:VCALENDAR' . "\r\n" .
			'PRODID:-//Microsoft Corporation//Outlook 10.0 MIMEDIR//EN' . "\r\n" .
			'VERSION:2.0' . "\r\n" .
			'METHOD:REQUEST' . "\r\n" .
			'BEGIN:VTIMEZONE' . "\r\n" .
			'TZID:Eastern Time' . "\r\n" .
			'BEGIN:STANDARD' . "\r\n" .
			'DTSTART:20091101T020000' . "\r\n" .
			'RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=1SU;BYMONTH=11' . "\r\n" .
			'TZOFFSETFROM:-0400' . "\r\n" .
			'TZOFFSETTO:-0500' . "\r\n" .
			'TZNAME:EST' . "\r\n" .
			'END:STANDARD' . "\r\n" .
			'BEGIN:DAYLIGHT' . "\r\n" .
			'DTSTART:20090301T020000' . "\r\n" .
			'RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=2SU;BYMONTH=3' . "\r\n" .
			'TZOFFSETFROM:-0500' . "\r\n" .
			'TZOFFSETTO:-0400' . "\r\n" .
			'TZNAME:EDST' . "\r\n" .
			'END:DAYLIGHT' . "\r\n" .
			'END:VTIMEZONE' . "\r\n" .
			'BEGIN:VEVENT' . "\r\n" .
			'ORGANIZER;CN="'.$from_name.'":MAILTO:'.$from_address. "\r\n" .
			'ATTENDEE;CN="'.$to_name.'";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:'.$to_address. "\r\n" .
			'LAST-MODIFIED:' . date("Ymd\TGis") . "\r\n" .
			'UID:'.date("Ymd\TGis", strtotime($startTime)).rand()."@".$domain."\r\n" .
			'DTSTAMP:'.date("Ymd\TGis"). "\r\n" .
			'DTSTART;TZID="Pacific Daylight":'.date("Ymd\THis", strtotime($startTime)). "\r\n" .
			'DTEND;TZID="Pacific Daylight":'.date("Ymd\THis", strtotime($endTime)). "\r\n" .
			'TRANSP:OPAQUE'. "\r\n" .
			'SEQUENCE:1'. "\r\n" .
			'SUMMARY:' . $subject . "\r\n" .
			'LOCATION:' . $location . "\r\n" .
			'CLASS:PUBLIC'. "\r\n" .
			'PRIORITY:5'. "\r\n" .
			'BEGIN:VALARM' . "\r\n" .
			'TRIGGER:-PT15M' . "\r\n" .
			'ACTION:DISPLAY' . "\r\n" .
			'DESCRIPTION:Reminder' . "\r\n" .
			'END:VALARM' . "\r\n" .
			'END:VEVENT'. "\r\n" .
			'END:VCALENDAR'. "\r\n";
			//$message .= 'Content-Type: text/calendar;name="meeting.ics";method=REQUEST'."\n";
			//$message .= "Content-Transfer-Encoding: 8bit\n\n";
			$message .= $ical;
			
			$uploads = wp_upload_dir();
			$path = $uploads['path'];

			file_put_contents( $path . '/reservation.ics', $message );

			$attachments = array( $path . '/reservation.ics' );
			
			return $attachments;
			
			
			
		}
		
		function fetchRecords(){
			global  $wpdb ;
			
			$current_date = date('Y-m-d h:i:s');
			
			$sql = "SELECT ID  FROM {$wpdb->prefix}posts  WHERE post_type = 'shop_order'  ORDER BY ID DESC " ; 
			$allPosts = $wpdb->get_results($sql);
		
			if($allPosts){ 
				foreach($allPosts as $pid){ 
					
					$checknotific = get_post_meta($pid->ID,'order_status_notification_check',true);
				
					
					if(empty($checknotific)){
					
						$attendedata = get_post_meta($pid->ID,'attendees',true);
						
						$attendedata = json_decode( $attendedata , true );
			
						if($attendedata){
							foreach($attendedata as $key=>$product_data){
								$product_id = $key;
								
								$title = $product_data['name'];
								$subject = 'Course Details for the upcoming Workshop "'.$title.'"';
								
								$atendedata = $product_data['attendees'];
								$first_name = $atendedata[1]['first_name'];
								$last_name = $atendedata[1]['last_name'];
								$position = $atendedata[1]['position'];
								$desc = '';
								
								$last_name = $product_data['attendees'][1]['last_name'];
								$email = $product_data['attendees']['1']['email'];
								
								$s_date = get_post_meta($product_id,'start_date',true);
								$end_date = get_post_meta($product_id,'end_date',true);
								$location = get_post_meta($product_id,'location',true);
								
								$s_time = get_post_meta($product_id,'start_time',true);
								$end_time = get_post_meta($product_id,'end_time',true);
								
								$e_start_date = date('Y-m-d h:i:s', strtotime(  $s_date .' '. $s_time ));
								$e_end_date = date('Y-m-d h:i:s', strtotime( $end_date .' '. $end_time )); 
								
								//$event_started = date('Ymdhis', strtotime( $e_start_date ));
								$event_started = $s_date."T". str_replace( ':', '', $s_time );
								
								//$event_ended = date('Ymdhis', strtotime( $e_end_date ));
								$event_ended = $end_date."T". str_replace( ':', '', $end_time );
								
								$diff = strtotime($e_start_date) - strtotime($current_date); 
								// 1 day = 24 hours 
								// 24 * 60 * 60 = 86400 seconds 
								$days = round( $diff / 86400 ); 
								
								if( $days == 1 ){
									//$message = get_field( 'email_body', intval($product_id) );
									$message = get_post_meta( $product_id, 'reminder_email_body', true );	
								
									$message .= "<p>";
									$message .= '<a href="http://www.google.com/calendar/event?action=TEMPLATE&text='.$title.'&dates='.$event_started.'/'.$event_ended.'&details='.$desc.'&location='.$location.'">Add to google calendar</a>';
									$message .= "</p>";
									
									if( isset( $message ) && strlen( $message ) > 0 ){
										$message = str_replace( '%first_name%', $first_name, $message );
										$message = str_replace( '%last_name%', $last_name, $message );
										$message = wpautop($message);
										$from = get_bloginfo('admin_email');
										$fromName = get_bloginfo('name');
										//$to = $first_name . ' ' . $last_name . ' <' . $email . '>';
										//$to = $email;

										$headers = array();
										$headers[] = "From: $fromName <$from>";
										$headers[] = "X-Sender: $fromName <$from>";
										$headers[] = "Return-Path: $from";
										$headers[] = "Content-Type: text/html; charset=iso-8859-1";
										
										$startTime = $event_started;
										$endTime = $event_ended;
										$attachments = $this->add_to_outlook( $startTime ,  $endTime );
										$attachments_url = home_url().'/'.$attachments[0];
										$attachments_path = explode("uploads",$attachments[0] );
										
										$updated_url = home_url().'/wp-content/uploads'.$attachments_path[1];
										
										$message .= "<p>";
										$message .= '<a  class="addeventatc" data-id="Qe4853956" href="'.$updated_url.'" target="_blank" rel="nofollow">Download .ics</a> ';
										$message .= "</p>";
										
										
										
										add_filter( 'wp_mail_content_type',array($this,'wp_set_content_type' ));
										//wp_mail($to,$subject,$message,$headers); 
										
										wp_mail('wordpress@marsworks.com',$subject,$message,$headers , $updated_url); 
										
										remove_filter( 'wp_mail_content_type', array($this,'wp_set_content_type' ));
									}
									
									update_post_meta( $pid->ID , 'order_status_notification_check', 'done' );
									
								}
							}
						}
					}
					//break;
				}
			}
		}
		
		function wp_set_content_type(){
			return "text/html";
		}
		
		function changeTheme( $new_theme ) {
			//wp_clear_scheduled_hook('set_wp_cron_notification');
		}
		
		function execute_cron_job() {
			
			$this->fetchRecords();
			// do something every hour
		}
		
		function addCronMinutes($array) {
			$crontime = get_option("cron_time_set");
			
			if(empty($crontime)){
				$interval_time = 86400;
			}else{
				$interval_time = $crontime;
			}
			
			$schedules['every_day_once'] = array(
				'interval' => $interval_time,
				'display' => __('Cron-Job-Run')
			);
			return $schedules;
		}
				
		function set_cron_job() {
			
			if ( !wp_next_scheduled( 'set_wp_cron_notification' ) ) {
				wp_schedule_event(time(), 'every_day_once', 'set_wp_cron_notification');
			}
		}

        public function add_meta_box_for_orders(){
            if( isset( $_GET['post'] ) ){
                add_meta_box( 'woocommerce_order_attendees', 'Order Attendees', function (){ echo self::get_attendees($_GET['post'],false); }, 'shop_order', 'normal', 'low' );
            }
        }

        /**
         * @param $_order WC_Order
         * @param $_sentToAdmin boolean
         * @param $_plainText string
         * @param $_email string
         */
        public function add_attendee_info_to_email( $_order, $_sentToAdmin, $_plainText, $_email ){
            echo self::get_attendees( $_order->get_id(), false );
        }

        /**
         * @param $_order WC_Order
         */
        public function add_attendee_info_to_order_received( $_order ){
            echo '<style>body section.woocommerce-order-details{display: block !important;}</style>';
            echo self::get_attendees( $_order->get_id(), false );
        }

        /**
         * @param $_message string
         * @param $_order WC_Order
         */
        public function add_to_confirmation_message( $_message, $_order ){
            $styles = '<style>';
            $styles .= 'section.woocommerce-order-details{ display: none !important; }';
            $styles .= 'section.woocommerce-customer-details{ display: none !important; }';
            $styles .= 'div.woocommerce-order > p.woocommerce-thankyou-order-received{font-weight: 600;font-size: 34px;margin-bottom: 20px;line-height: 1.2em;text-transform: none;}';
            $styles .= '</style>';
            return $styles . $_message . '<span class="intersol-order-extended-message"> Please check your email for further instructions.</span>';
        }

        /**
         * @param $_orderID string
         * @var $orderAttendeeObj array
         */
        public function send_emails_to_attendees( $_orderID ){
            $orderAttendeeObj = self::get_attendees( $_orderID );
            foreach( $orderAttendeeObj as $_productID => $_productAttendeeObj ){
                foreach ( $_productAttendeeObj->attendees as $_attendeeNum => $_attendeeObj ){
                    $this->send_email( $_productID, $_productAttendeeObj->name, $_attendeeObj->email, $_attendeeObj->first_name, $_attendeeObj->last_name );
                }
            }
        }

        /**
         * @param $_productID string
         * @param $_productTitle string
         * @param $_attendeeEmail string
         * @param $_attendeeFirstName string
         * @param $_attendeeLastName string
         */
        private function send_email( $_productID, $_productTitle, $_attendeeEmail, $_attendeeFirstName, $_attendeeLastName ){
            $subject = 'Course Details for the upcoming Workshop "'.$_productTitle.'"';
            $message = get_field( 'email_body', intval($_productID) );
            if( isset( $message ) && strlen( $message ) > 0 ){
                $message = str_replace( '%first_name%', $_attendeeFirstName, $message );
                $message = str_replace( '%last_name%', $_attendeeLastName, $message );
                $message = wpautop($message);
				
				$s_date = get_post_meta($_productID,'start_date',true);
				$end_date = get_post_meta($_productID,'end_date',true);
				$location = get_post_meta($_productID,'location',true);
				
				$s_time = get_post_meta($_productID,'start_time',true);
				$end_time = get_post_meta($_productID,'end_time',true);
				
				$event_started = $s_date."T". str_replace( ':', '', $s_time );
				$event_ended = $end_date."T". str_replace( ':', '', $end_time );
				
				$attachments = $this->add_to_outlook( $event_started ,  $event_ended );
				$attachments_url = home_url().'/'.$attachments[0];
				$attachments_path = explode("uploads",$attachments[0] );
				
				$updated_url = home_url().'/wp-content/uploads'.$attachments_path[1];
				
				$message .= "<p>";
				$message .= '<a href="http://www.google.com/calendar/event?action=TEMPLATE&text='.$_productTitle.'&dates='.$event_started.'/'.$event_ended.'&details='.$_productTitle.'&location='.$location.'">Add to google calendar</a>';
				$message .= "</p>";
				
				$message .= "<p>";
				$message .= '<a  class="addeventatc" data-id="Qe4853956" href="'.$updated_url.'" target="_blank" rel="nofollow">Download .ics</a> ';
				$message .= "</p>";
										
                $from = get_bloginfo('admin_email');
                $fromName = get_bloginfo('name');
                $to = $_attendeeFirstName . ' ' . $_attendeeLastName . ' <' . $_attendeeEmail . '>';

                $headers = array();
                $headers[] = "From: $fromName <$from>";
                $headers[] = "X-Sender: $fromName <$from>";
                $headers[] = "Return-Path: $from";
                $headers[] = "Content-Type: text/html; charset=iso-8859-1";
                $mail = wp_mail($to,$subject,$message,$headers);
            }
        }

        /**
         * @param $_orderID string
         * @param $_returnObject boolean
         * @return string|array
         */
        public static function get_attendees( $_orderID, $_returnObject = true ){
            $attendeesObj = get_post_meta( $_orderID, 'attendees', true );
            while ( strpos( $attendeesObj, '\\' ) !== false ){
                $attendeesObj = stripslashes($attendeesObj);
            }
            $attendeesObj = json_decode( $attendeesObj );
            if( $_returnObject ){
                return $attendeesObj;
            }
            $html = '<div class="intersol-order-attendee-information-wrapper">';
                $html .= '<h3>Attendee Information</h3>';
                foreach ( $attendeesObj as $_product ){
                    $html .= '<div>';
                        $html .= '<h4>'.$_product->name.'</h4>';
                        $html .= '<div>';
                            foreach ( $_product->attendees as $_attendeeNum => $_attendeeInfo ){
                                $html .= '<table>';
                                    $html .= '<tbody>';
                                        $html .= '<tr>';
                                            $html .= '<th colspan="2">Attendee '.$_attendeeNum.'</th>';
                                        $html .= '</tr>';
                                        $html .= '<tr>';
                                            $html .= '<td>First Name</td>';
                                            $html .= '<td>'.$_attendeeInfo->first_name.'</td>';
                                        $html .= '</tr>';
                                        $html .= '<tr>';
                                            $html .= '<td>Last Name</td>';
                                            $html .= '<td>'.$_attendeeInfo->last_name.'</td>';
                                        $html .= '</tr>';
                                        $html .= '<tr>';
                                            $html .= '<td>Position</td>';
                                            $html .= '<td>'.$_attendeeInfo->position.'</td>';
                                        $html .= '</tr>';
//                                        $html .= '<tr>';
//                                            $html .= '<td>Telephone</td>';
//                                            $html .= '<td>'.$_attendeeInfo->telephone.'</td>';
//                                        $html .= '</tr>';
                                        $html .= '<tr>';
                                            $html .= '<td>Email</td>';
                                            $html .= '<td>'.$_attendeeInfo->email.'</td>';
                                        $html .= '</tr>';
                                    $html .= '</tbody>';
                                $html .= '</table>';
                            }
                        $html .= '</div>';
                    $html .= '</div>';
                }
            $html .= '</div>';
            return $html;
        }

        /**
         * @param $_orderID string
         */
        public function save_custom_form_fields( $_orderID ){
            $attendees = array();
            $orderObj = wc_get_order( $_orderID );
            foreach ( $orderObj->get_items() as $_sku => $_product ){
                $productID = $_product["product_id"];
                $productTitle = wc_get_product($productID)->get_title();
                $attendees[$productID]["name"] = $productTitle;
                for( $_i = 0; $_i < $_product["quantity"]; $_i ++ ){
                    $attendeeNum = strval( $_i + 1 );
                    if( isset( $_POST[$productID."_".$attendeeNum."_attendee_first_name"] ) ){
                        $attendees[$productID]["attendees"][$attendeeNum]["first_name"] = $_POST[$productID."_".$attendeeNum."_attendee_first_name"];
                    }else{
                        $attendees[$productID]["attendees"][$attendeeNum]["first_name"] = "No First Name Provided";
                    }
                    if( isset( $_POST[$productID."_".$attendeeNum."_attendee_last_name"] ) ){
                        $attendees[$productID]["attendees"][$attendeeNum]["last_name"] = $_POST[$productID."_".$attendeeNum."_attendee_last_name"];
                    }else{
                        $attendees[$productID]["attendees"][$attendeeNum]["last_name"] = "No Last Name Provided";
                    }
                    if( isset( $_POST[$productID."_".$attendeeNum."_attendee_position"] ) ){
                        $attendees[$productID]["attendees"][$attendeeNum]["position"] = $_POST[$productID."_".$attendeeNum."_attendee_position"];
                    }else{
                        $attendees[$productID]["attendees"][$attendeeNum]["position"] = "No Position Provided";
                    }
//                    if( isset( $_POST[$productID."_".$attendeeNum."_attendee_telephone"] ) ){
//                        $attendees[$productID]["attendees"][$attendeeNum]["telephone"] = $_POST[$productID."_".$attendeeNum."_attendee_telephone"];
//                    }else{
//                        $attendees[$productID]["attendees"][$attendeeNum]["telephone"] = "No Telephone Provided";
//                    }
                    if( isset( $_POST[$productID."_".$attendeeNum."_attendee_email"] ) ){
                        $attendees[$productID]["attendees"][$attendeeNum]["email"] = $_POST[$productID."_".$attendeeNum."_attendee_email"];
                    }else{
                        $attendees[$productID]["attendees"][$attendeeNum]["email"] = "No Email Provided";
                    }
                }
            }
            update_post_meta( $_orderID, 'attendees', json_encode($attendees) );
        }

        public function validate_custom_fields(){
            $didNotice = false;
            $didEmailNotice = false;
            $didTelNotice = false;
            foreach ( $_POST as $_key => $_value ){
                if( strpos( $_key, '_attendee_' ) > 0 && ! strpos( $_key, '_attendee_position' ) > 0 ){
                    if ( ! strlen($_value) > 0 && $didNotice === false ){
                        wc_add_notice( __( '<span class="intersol-attendee-error"><b>You are missing some Attendee Information.</b> Please fill in the required information for each attendee.</span>' ), 'error' );
                        $didNotice = true;
                    }else{
//                        if( strpos( $_key, '_attendee_telephone' ) > 0 && strlen( $_value ) > 0 ){
//                            if( ! WC_Validation::is_phone($_value) && $didTelNotice === false ){
//                                wc_add_notice( __( '<span class="intersol-attendee-error"><b>Invalid Attendee Telephone.</b> Please fill in a valid telephone number for each attendee.</span>' ), 'error' );
//                                $didTelNotice = true;
//                            }
//                        }
                        if( strpos( $_key, '_attendee_email' ) > 0 && strlen( $_value ) > 0 ){
                            if( ! WC_Validation::is_email($_value) && $didEmailNotice === false ){
                                wc_add_notice( __( '<span class="intersol-attendee-error"><b>Invalid Attendee Email.</b> Please fill in a valid email address for each attendee.</span>' ), 'error' );
                                $didEmailNotice = true;
                            }
                        }
                    }
                }
            }
        }

        public function generate_forms(){
            ?>
            <div class="intersol-product-attendee-wrapper">
                <h3>Attendee Information</h3>
                <div>
                    <?php
                    foreach ( WC()->cart->get_cart() as $_sku => $_product ) {
                        $productID = $_product["product_id"];
                        $productTitle = wc_get_product($productID)->get_title();
                        ?>
                        <div class="attendee-accordion open">
                            <h4><span></span><?php echo $productTitle; ?></h4>
                            <div class="attendee-accordion-content">
                                <div class="attendee-accordion-row">
                                    <?php
                                    for( $_i = 0; $_i < $_product["quantity"]; $_i ++){
                                        $attendeeNum = strval( $_i + 1 );
                                        ?>
                                        <div class="intersol-attendee-form">
                                            <h5>Attendee Information <?php echo $attendeeNum; ?></h5>
                                            <div class="attendee-accordion-row">
                                            <?php
                                            woocommerce_form_field($productID."_".$attendeeNum."_attendee_first_name", array(
                                                'type' => 'text',
                                                'class' => array('intersol-attendee-first-name', 'intersol-attendee-input'),
                                                'label' => _x("First Name", "Form field for Attendees in checkout.", "intersol_theme"),
                                                'placeholder' => _x("First Name", "Form placeholder for Attendees in checkout.", "intersol_theme"),
                                                'required' => true,
                                            ));
                                            woocommerce_form_field($productID."_".$attendeeNum."_attendee_last_name", array(
                                                'type' => 'text',
                                                'class' => array('intersol-attendee-last-name', 'intersol-attendee-input'),
                                                'label' => _x("Last Name", "Form field for Attendees in checkout.", "intersol_theme"),
                                                'placeholder' => _x("Last Name", "Form placeholder for Attendees in checkout.", "intersol_theme"),
                                                'required' => true,
                                            ));
                                            ?>
                                            </div>
                                            <div class="attendee-accordion-row">
                                            <?php
                                            woocommerce_form_field($productID."_".$attendeeNum."_attendee_position", array(
                                                'type' => 'text',
                                                'class' => array('intersol-attendee-position', 'intersol-attendee-input'),
                                                'label' => _x("Position", "Form field for Attendees in checkout.", "intersol_theme"),
                                                'placeholder' => _x("Position", "Form placeholder for Attendees in checkout.", "intersol_theme"),
                                                'required' => false,
                                            ));
//                                            woocommerce_form_field($productID."_".$attendeeNum."_attendee_telephone", array(
//                                                'type' => 'tel',
//                                                'class' => array('intersol-attendee-telephone', 'intersol-attendee-input'),
//                                                'label' => _x("Telephone", "Form field for Attendees in checkout.", "intersol_theme"),
//                                                'placeholder' => _x("Telephone", "Form placeholder for Attendees in checkout.", "intersol_theme"),
//                                                'required' => true,
//                                            ));
                                            woocommerce_form_field($productID."_".$attendeeNum."_attendee_email", array(
                                                'type' => 'email',
                                                'class' => array('intersol-attendee-email', 'intersol-attendee-input'),
                                                'label' => _x("Email", "Form field for Attendees in checkout.", "intersol_theme"),
                                                'placeholder' => _x("Email", "Form placeholder for Attendees in checkout.", "intersol_theme"),
                                                'required' => true,
                                            ));
                                            ?>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php
        }

        public function register_acf_email_body(){
            if( function_exists('acf_add_local_field_group') ) {
                acf_add_local_field_group(array(
                    'key' => 'group_5e31a7d1b6012',
                    'title' => 'Email Body',
                    'fields' => array(
                        array(
                            'key' => 'field_5e31a7dce452d',
                            'label' => 'Email Body',
                            'name' => 'email_body',
                            'type' => 'wysiwyg',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                        ),
                    ),
                    'location' => array(
                        array(
                            array(
                                'param' => 'post_type',
                                'operator' => '==',
                                'value' => 'product',
                            ),
                        ),
                    ),
                    'menu_order' => 0,
                    'position' => 'normal',
                    'style' => 'default',
                    'label_placement' => 'top',
                    'instruction_placement' => 'label',
                    'hide_on_screen' => '',
                    'active' => true,
                    'description' => '',
                ));
            }
        }

        public function register_acf_reminder_email_body(){
            if( function_exists('acf_add_local_field_group') ):

                acf_add_local_field_group(array(
                    'key' => 'group_5ec6e244aa98a',
                    'title' => 'Reminder Email Body',
                    'fields' => array(
                        array(
                            'key' => 'field_5ec6e24bb24a9',
                            'label' => 'Reminder Email Body',
                            'name' => 'reminder_email_body',
                            'type' => 'wysiwyg',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '',
                            'tabs' => 'all',
                            'toolbar' => 'full',
                            'media_upload' => 1,
                            'delay' => 0,
                        ),
                    ),
                    'location' => array(
                        array(
                            array(
                                'param' => 'post_type',
                                'operator' => '==',
                                'value' => 'product',
                            ),
                        ),
                    ),
                    'menu_order' => 0,
                    'position' => 'normal',
                    'style' => 'default',
                    'label_placement' => 'top',
                    'instruction_placement' => 'label',
                    'hide_on_screen' => '',
                    'active' => true,
                    'description' => '',
                ));
                
                endif;
        }

        public function enqueue_frontend_scripts(){
            wp_register_script( 'attendee-accordion-script', get_stylesheet_directory_uri() . '/assets/js/attendee-accordion.js', array('jquery'), '1.0', true );
            wp_enqueue_script( 'attendee-accordion-script' );
            wp_register_style( 'attendee-accordion-styles', get_stylesheet_directory_uri() . '/assets/styles/attendee-accordion.css', array(), '1.0', 'all' );
            wp_enqueue_style( 'attendee-accordion-styles' );
        }

        public function enqueue_backend_scripts(){
            wp_register_style( 'admin-styles', get_stylesheet_directory_uri() . '/assets/styles/admin/admin.css', array(), '1.0', 'all' );
            wp_enqueue_style( 'admin-styles' );
        }

        private function do_log($message, $shouldNotDie = false){
            error_log(print_r($message, true));
            if ($shouldNotDie) {
                exit;
            }
        }
    }
    new Intersol_Extended_Cart();
}

function generatepress_child_enqueue_scripts() {
	if ( is_rtl() ) {
		wp_enqueue_style( 'generatepress-rtl', trailingslashit( get_template_directory_uri() ) . 'rtl.css' );
	}
}
add_action( 'wp_enqueue_scripts', 'generatepress_child_enqueue_scripts', 100 );

/**
 * @function set_acf_google_maps_api_key
 * Sets the Google Maps API key for Advanced Custom Fields
 * @param $_api
 * @return mixed
 */
function set_acf_google_maps_api_key( $_api ){
    $_api['key'] = 'AIzaSyBnJgHdj9aFeiIKNZeTEQInQuxW8yEhB2c';
    return $_api;
}
add_filter( 'acf/fields/google_map/api', 'set_acf_google_maps_api_key', 10, 1 );

/**
 * @function redirect_product_archive_to_training
 * Redirects the Woocommerce product archive page to the /training url
 */
function redirect_product_archive_to_training(){
    if( is_post_type_archive( 'product' ) ){
        wp_redirect( '/training/', '301' );
        die();
    }
}
add_action( 'template_redirect', 'redirect_product_archive_to_training', 1, 0 );

function register_all_post_types(){
    register_post_type('instructors', [
        'label' => 'Instructors',
        'labels' => generate_post_type_labels('Instructor','Instructors', 'intersol_theme'),
        'has_archive' => false,
        'public' => true,
        'menu_icon' => 'dashicons-businesswoman',
        'show_in_rest' => true,
        'supports' => [
            'title',
            'editor',
            'excerpt',
            'revisions',
            'author',
            'page-attributes',
            'thumbnail',
        ],
        'rewrite' => [
            'slug' => 'team',
            'with_front' => false
        ]
    ]);
    register_post_type('team-training', [
        'label' => 'Team Training',
        'labels' => generate_post_type_labels('Team Training','Team Training', 'intersol_theme'),
        'has_archive' => false,
        'public' => true,
        'menu_icon' => 'dashicons-groups',
        'show_in_rest' => true,
        'supports' => [
            'title',
            'editor',
            'excerpt',
            'revisions',
            'author',
            'page-attributes',
            'thumbnail',
        ],
        'rewrite' => [
            'slug' => 'training-for-my-team',
            'with_front' => false
        ]
    ]);
}
add_action( 'init', 'register_all_post_types' );

function create_team_training_taxonomies() {
    register_taxonomy(
        'team-training-category',
        'team-training',
        array(
            'labels' => array(
                'name' => 'Category',
                'add_new_item' => 'Add New Category',
                'new_item_name' => "New Category"
            ),
            'show_ui' => true,
            'show_tagcloud' => false,
            'hierarchical' => true
        )
    );
}
add_action( 'init', 'create_team_training_taxonomies');

function intersol_register_scripts_and_stylesheets(){
    wp_register_script( 'intersol_main_script', get_stylesheet_directory_uri() . '/assets/js/script.js', array('jquery'), '1.0', true );
    wp_enqueue_script( 'intersol_main_script' );
}
add_action( 'wp_enqueue_scripts', 'intersol_register_scripts_and_stylesheets', 10, 0 );

function intersol_workshops_query( WP_Query $_query ){
    $metaQuery = array(
        'relation' => 'AND',
        array(
            'key' => 'start_date',
            'value' => date('Ymd'),
            'compare' => '>',
            'type' => 'date',
        ),
    );
    $_query->set('meta_query',$metaQuery);
    $_query->set('meta_key','start_date');
    $_query->set('orderby','meta_value');
    $_query->set('order','ASC');
    if( isset( $_COOKIE["WORKSHOP_SEARCH"] ) && strlen($_COOKIE["WORKSHOP_SEARCH"]) > 0 ){
        $searchQuery = $_COOKIE["WORKSHOP_SEARCH"];
        $_query->set('s',$searchQuery);
        relevanssi_do_query($_query);
    }
}
add_action( 'elementor/query/workshops', 'intersol_workshops_query', 10, 1 );

function generate_post_type_labels( $_single, $_plural, $_textDomain ){
    $labels = array(
        'name' => _x($_plural, 'Post type general name', $_textDomain),
        'singular_name' => _x($_single, 'Post type singular name', $_textDomain),
        'menu_name' => _x($_plural, 'Admin Menu text', $_textDomain),
        'name_admin_bar' => _x($_single, 'Add New on Toolbar', $_textDomain),
        'add_new' => __("Add New $_single", $_textDomain),
        'add_new_item' => __("Add New $_single", $_textDomain),
        'new_item' => __("New $_single", $_textDomain),
        'edit_item' => __("Edit $_single", $_textDomain),
        'view_item' => __("View $_single", $_textDomain),
        'all_items' => __("All $_plural", $_textDomain),
        'search_items' => __("Search $_plural", $_textDomain),
        'parent_item_colon' => __("Parent $_single:", $_textDomain),
        'not_found' => __("No $_plural found.", $_textDomain),
        'not_found_in_trash' => __("No $_plural found in Trash.", $_textDomain),
        'featured_image' => _x("$_single Cover Image", 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', $_textDomain),
        'set_featured_image' => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', $_textDomain),
        'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', $_textDomain),
        'use_featured_image' => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', $_textDomain),
        'archives' => _x("$_plural archives", 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', $_textDomain),
        'insert_into_item' => _x("Insert into $_single", 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', $_textDomain),
        'uploaded_to_this_item' => _x("Uploaded to this $_single", 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', $_textDomain),
        'filter_items_list' => _x("Filter $_plural list", 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', $_textDomain),
        'items_list_navigation' => _x("$_plural list navigation", 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', $_textDomain),
        'items_list' => _x("$_plural list", 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', $_textDomain),
        'item_published' => __("$_single published", $_textDomain),
        'item_published_privately' => __("$_single published privately", $_textDomain),
        'item_reverted_to_draft' => __("$_single reverted to draft", $_textDomain),
        'item_scheduled' => __("$_single scheduled", $_textDomain),
        'item_updated' => __("$_single updated", $_textDomain),
    );
    return $labels;
}

// Use woocommerce featured posts
add_action( 'elementor/query/woo_featured', function( $query ) {
    // Modify the posts query here
    $tax_query[] = array(
        'taxonomy' => 'product_visibility',
        'field'    => 'name',
        'terms'    => 'featured',
        'operator' => 'IN', // or 'NOT IN' to exclude feature products
    );
    $meta_query[] = [
      'key'         => 'start_date',
      'value'       => date('Ymd'),
      'compare'     => '>=',
      'type'        => 'date'
      ];
    $query->set( 'tax_query', $tax_query );
    $query->set( 'meta_key', 'start_date' );
    $query->set( 'orderby', 'meta_value' );
    $query->set( 'order', 'ASC' );
    $query->set( 'meta_query', $meta_query );
  } );

