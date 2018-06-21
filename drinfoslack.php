<?php

$whereami = dirname(__FILE__);

$config = file_get_contents($whereami . '/config.json');
$config = json_decode($config, TRUE);

$updates_available = [];

foreach ($config['sites'] as $site_name => $site_path) {
  
  $packages = [];
  
  chdir($site_path);
  $drushjson = shell_exec('drush ups --format=json');
  
  // if this comes back empty if means there are no updates available
  if ($drushjson == '') {
    $updates_available[$site_name] = $packages;
    continue;
  }
  
  $drushsays = json_decode($drushjson, TRUE);
  
  foreach ($drushsays as $package => $info) {
    
    $current_version = $info['info']['version'];
    if (array_key_exists('updateable', $info) && $info['updateable']) {
      $recommended = $info['recommended'];
    }
    $packages[$package] = [
      'current' => $current_version,
      'recommended' => $recommended
    ];
    
  }
  
  $updates_available[$site_name] = $packages;
  
}


$msg = '';

//
// okay if i don't have updates, let me know
if (count($updates_available) == 0) {
  $msg = count($config['sites']) . ' sites. No updates available. :smile:';
}
else {
  // if we DO have updates available, make something nice out of them
  foreach ($updates_available as $site => $packages) {
    if (count($packages) == 0) {
      $msg .= $site . ' has no updates available';
    }
    else if (count($packages) == 1) {
      $msg .= $site . ' has an update available for ';
      $msg .= current(array_keys($packages));
    }
    else {
      $msg .= $site . ' has ' . count($packages) . ' update(s) available';
    }
    if (array_key_exists('drupal', $packages)) {
      $msg .= ' *DRUPAL CORE*';
    }
    $msg .= chr(10);
  }
}


// send to Slack
if ($msg) {
  slack($msg, $config['servername'], $config['slack']);
}



function slack($message, $from, $cfg) {
  $channel = ($cfg['channel']) ? $cfg['channel'] : "drupal";
  $data = "payload=" . json_encode(array(
          "channel"       =>  "#{$cfg['channel']}",
          "text"          =>  $message,
          "username"      =>  $from,
          "icon_emoji"    =>  $cfg['icon']
      ));

  // You can get your webhook endpoint from your Slack settings
  $ch = curl_init($cfg['webhook']);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $result = curl_exec($ch);
  curl_close($ch);

  return $result;
}

  
  
  