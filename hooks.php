<?php
add_action( 'profile_update', 'user_profile_update', 10, 2 );

function user_profile_update( $user_id, $old_user_data ) {
    // Retrieve api key
    $options = get_option('ossn_options');
    $api_key = $options['ossn_field_api_key'];

    // Retrieve updated WP user email
    $user = get_user_by('id', $user_id);
    $new_email = $user->user_email;
    $username = $old_user_data->user_login;

    //get the ossn user
    $ossn_user = get_ossn_user($api_key, $username);

    //update the ossn user email
    $update_ossn_user_response = update_ossn_user_email($api_key, $ossn_user, $new_email);

    if ($update_ossn_user_response->code != '100') {
      global $wpdb;
      $sql = "UPDATE hth_users SET user_email='{$old_user_data->user_email}' WHERE ID={$user_id}";
      $results = $wpdb->get_results($sql);

      $error_message = $update_ossn_user_response->message;
      ?>
      <script type="text/javascript">
        jQuery(window).load(function() {
          jQuery('#mepr-account-welcome-message').nextAll('.mp_wrapper').first().children('.mepr_updated').remove();
          jQuery('#mepr-account-welcome-message').remove();
        });
      </script>
      <div class="mepr_error" id="mepr_jump">
        <ul>
            <li>
              <strong>Error</strong>: There was an error updating your email address! Please contact the <a href="mailto:haden@howtohockey.com?subject=There%20was%20an%20error%20while%20updating%20my%20email%20address&body=The%20error%20message%20was%3A%20<?=$error_message?>">system administrator</a>.
              <?php
              if (!empty($update_ossn_user_response->message)) {
                ?>
                <br />
                <small style="font-size: 10px;">Error message: <?=$update_ossn_user_response->message?></small>
                <?php
              }
              ?>
            </li>
        </ul>
      </div>
      <?php
    }
}

function get_ossn_user($api_key, $username) {
  // get ossn user data based on email
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://thepond.howtohockey.com/squad/api/v1.0/user_details?api_key_token={$api_key}&username={$username}",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET"
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  $ossn_user = json_decode($response);

  return $ossn_user->payload;
}

function update_ossn_user_email($api_key, $user, $new_email) {
  if (empty($user) || empty($api_key) || empty($new_email)) {
    return false;
  }

  // Update the ossn email address for that user
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://thepond.howtohockey.com/squad/api/v1.0/user_update_email",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => array(
      'api_key_token' => $api_key,
      'guid' => $user->guid,
      'new_email' => $new_email)
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  $response = json_decode($response);

  return $response;
}
?>
