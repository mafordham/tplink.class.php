# tplink.class.php
Control TP-Link Plugs on LAN

pseudo code / usage

require_once "tplink.class.php";

// replace with the ip address of your device here
$tp = new TpLink("192.168.0.101");

if (($t = $tp->getStatus()) !== FALSE) {

  $isRunning = ((!empty($t->system->get_sysinfo->relay_state))?TRUE:FALSE);
  
  if (!$isRunning) {
    $tp->turnOn();
  }
  else {
    $tp->turnOff();
  }

}
