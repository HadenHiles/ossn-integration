<?php
add_action( 'personal_options_update', 'user_profile_update', 10, 2 );

function user_profile_update( $user_id, $old_user_data ) {
    // Retrieve api key
    $options = get_option('ossn_options');
    $api_key = $options['ossn_field_api_key'];

    // Retrieve updated WP user email
    $user = get_user_by('id', $user_id);
    $new_email = $user->user_email;
    $old_email = $old_user_data->user_email;

    //get the ossn user
    $ossn_user = get_ossn_user($api_key, $old_email);

    //update the ossn user email
    $update_ossn_user_response = update_ossn_user_email($api_key, $ossn_user, $new_email);

    if ($update_ossn_user_response->code != '100') {
      $args = array(
        'ID'         => $user_id,
        'user_email' => $old_user_data->user_email
      );
      wp_update_user( $args );
      ?>
      <div class="mepr_error" id="mepr_jump">
        <ul>
            <li>
              <strong>Error</strong>: There was an error updating your email address! Please contact the <a href="mailto:haden@howtohockey.com">system administrator</a>.
              <?php
              if (!empty($update_ossn_user_response->message)) {
                ?>
                <small>Message: <?=$update_ossn_user_response->message?></small>
                <?php
              }
              ?>
            </li>
        </ul>
      </div>
      <?php
    }
}

function get_ossn_user($api_key, $email) {
  // get ossn user data based on email
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://thepond.howtohockey.com/squad/api/v1.0/user_details?api_key_token={$api_key}&email={$email}",
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
    CURLOPT_URL => "https://thepond.howtohockey.com/squad/api/v1.0/update_user_email",
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
