<?php 
    /*
    Plugin Name: CTFS - User Entries
    Description: Download Fuel your fun user entries in CSV format.  
    Author: C4pt4inM
    Version: 1.0.0
    Author URI: https://github.com/C4pt4inM
    */

		if ( ! defined( 'ABSPATH' ) ) exit;

		add_action('admin_menu', 'ctfs_csv_menu');

		function ctfs_csv_menu() {
			add_menu_page('Fuel Your Fun CSV user entries', 'CSV User Entries', 'manage_options', 'ctfs-csv', 'ctfs_csv_init');

		}

		function ctfs_csv_init() 
		{?>
			<style>
				.center_div{
					margin: 5px auto;
				}

			</style>
		
			<div class="container center_div">
			<div class="col-sm-10 col-sm-offset-2">
			
			<?php  echo "<h2 style='font-size: 32px;'>" . __( 'Download CSV', 'ctfs_csv_text' ) . "</h2>"; ?>
			<?php  echo "<p>" . __( 'Download complete list of User Entries in CSV format.', 'ctfs_csv_text' ) . "</p>"; ?>
			<hr>
			<?php  echo "<p style='color: #f00; font-size: 18px; '>" . __( 'You are about to download 1000s of entries. Be Patient!', 'ctfs_csv_text' ) . "</p>"; ?>
			<?php 
				echo '1) <a class="dwn_csv" target="_blank" href="' . wp_nonce_url( admin_url( 'admin-ajax.php?action=ctfs_csv_dl'), 'ctfs_csv_dl' ) . '">' . __( 'Download User Entries CSV', 'ctfs_csv_text' ) . '</a>';
			?><br><br>
			<?php 
				 echo '2) <a class="dwn_csv" target="_blank" href="' . wp_nonce_url( admin_url( 'admin-ajax.php?action=ctfs_csv_dl_pg'), 'ctfs_csv_dl_pg' ) . '">' . __( 'Download Photo Gallery CSV', 'ctfs_csv_text' ) . '</a>';
			?>
			</div>
			</div>
   
		<?php }

		add_action('wp_ajax_ctfs_csv_dl', 'ctfs_csv_dl');
		add_action('wp_ajax_nopriv_ctfs_csv_dl', 'ctfs_csv_dl');
		
		function ctfs_csv_dl()
		{
			global $wpdb;
			$table_name = 'ctfsuser_entries';
			$querystr = "SELECT * FROM $table_name ";
			
			$usr_entries = $wpdb->get_results($querystr, ARRAY_A);

			if(sizeof($usr_entries ) < 1 )
			{			
				die( __( 'Nothing to export!!' ) );
			} 
			else 
			{
			$csvheads = array("Name","Email","Phone Number", "Number of Entries"); 

			foreach($usr_entries as $single_array_entry ) {

				$db_usr_email = $single_array_entry['u_email'];
				$db_usr_nument = $single_array_entry['num_entries'];
				$db_usrname = "";
				$db_usrphnum = "";
				
				$getuser = get_user_by('email', $db_usr_email);
				$db_usrname = $getuser->display_name;
				$db_usrphnum = get_the_author_meta( 'phnum', $getuser->ID );
				
				if($db_usr_nument > 1){
					$counter = $db_usr_nument;
					while($counter > 0)
					{
						$csvbody[] = array($db_usrname,$db_usr_email,$db_usrphnum,"1","\n");
						$counter--;
					}
				}
				else
				{
					$csvbody[] = array($db_usrname,$db_usr_email,$db_usrphnum,$db_usr_nument,"\n");
				} 
			}

			/*======  trigger downloading ===============*/	 
			echo array2csv($csvheads, $csvbody);
			download_send_headers("user-entries");

			exit();
				
			}//end else
			
		}//end function

		 add_action('wp_ajax_ctfs_csv_dl_pg', 'ctfs_csv_dl_pg');
		 add_action('wp_ajax_nopriv_ctfs_csv_dl_pg', 'ctfs_csv_dl_pg');

		 function ctfs_csv_dl_pg()
		 {
		 	global $wpdb;
		 	$querystr = "SELECT * FROM ctfsfsq_data INNER JOIN ctfsuser_entries ON ctfsfsq_data.email = ctfsuser_entries.u_email WHERE star = 1 ";

		 	$pg_entries = $wpdb->get_results($querystr, ARRAY_A);

		 	if(sizeof($pg_entries ) < 1 )
		 	{			
		 		die( __( 'Nothing to export!!' ) );			
		 	} 
		 	else 
		 	{
		 		$csvheads = array("Name","Email","Phone Number", "Number of Entries","Caption","Image URL"); 

		 		foreach($pg_entries as $single_pg_entries) {

		 			$ph_entry_usr_email = $single_pg_entries['u_email'];
		 			$ph_entry_usr_nument = $single_pg_entries['num_entries'];
					
		 			$var1 = unserialize($single_pg_entries[pinfo]);
					
		 			$ph_entry_image_url = $var1[10][value];
		 			$ph_entry_caption = $var1[0][value];
		 			$ph_entry_usrname = "";
		 			$ph_entry_usrphnum = "";
					
		 			$getuser = get_user_by('email', $ph_entry_usr_email);
					
		 			$ph_entry_usrname = $getuser->display_name;
		 			$ph_entry_usrphnum = get_the_author_meta( 'phnum', $getuser->ID );
					
		 			$csvbody[] = array($ph_entry_usrname,$ph_entry_usr_email,$ph_entry_usrphnum,$ph_entry_usr_nument,$ph_entry_caption,$ph_entry_image_url);	
		 		}

		 	/*======  trigger downloading ===============*/	 
		 	echo array2csv($csvheads, $csvbody);
		 	download_send_headers("photo-gallery");

		 	exit();
				
		 	}//end else
			
		 }//end function

		function array2csv(array $arrayh, array $arrayd )
		{
		   ob_start();
		   $df = fopen("php://output", 'w');
		   fputcsv($df,  $arrayh);
		   foreach($arrayd as $arrayf){
		   fputcsv($df, $arrayf); 
		   }
		   fclose($df);
		   return ob_get_clean();
		}


		
		function download_send_headers($usr_ent) {
			// disable caching
		$now = gmdate("D, d M Y H:i:s");
		$fnma = date("Y-m-d");
			header('Content-Type: text/csv');
			header( 'Content-Description: File Transfer' );
			header("Expires: Wed, 21 Oct 2016 06:00:00 GMT");
			header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
			header("Last-Modified: {$now} GMT");
		
			// force download  
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
		
			// disposition / encoding on response body
			header("Content-Disposition: attachment;filename=csv-".$usr_ent."-".$fnma.".csv");
			header("Content-Transfer-Encoding: binary");
		   
		}


?>