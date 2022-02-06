<?php

class TpLink
{
  public $ip = '';
  public $port = 9999;

  private $commands = array('info'      => '{"system":{"get_sysinfo":{}}}'
                           ,'on'        => '{"system":{"set_relay_state":{"state":1}}}'
                           ,'off'       => '{"system":{"set_relay_state":{"state":0}}}'
                           ,'cloudinfo' => '{"cnCloud":{"get_info":{}}}'
                           ,'wlanscan'  => '{"netif":{"get_scaninfo":{"refresh":0}}}'
                           ,'time'      => '{"time":{"get_time":{}}}'
                           ,'schedule'  => '{"schedule":{"get_rules":{}}}'
                           ,'countdown' => '{"count_down":{"get_rules":{}}}'
                           ,'antitheft' => '{"anti_theft":{"get_rules":{}}}'
                           ,'reboot'    => '{"system":{"reboot":{"delay":1}}}'
                           ,'reset'     => '{"system":{"reset":{"delay":1}}}'
                           ,'energy'    => '{"emeter":{"get_realtime":{}}}'
                           ,'setalias'  => '{"system":{"set_dev_alias":{"alias":"Exhaust Fan"}}}'
                           );
  public function __construct($ip,$port=9999) {
    $this->ip = $ip;
    $this->port = $port;
  }
  public function __destruct() {
  }

  public function getStatus() //{{{
  {
    return($this->runCommand('info'));
  } //}}}
  public function getMeter() //{{{
  {
    return($this->runCommand('energy'));
  } //}}}
  public function turnOn() //{{{
  {
    return($this->runCommand('on'));
  } //}}}
  public function turnOff() //{{{
  {
    return($this->runCommand('off'));
  } //}}}
  public function reboot() //{{{
  {
    return($this->runCommand('reboot'));
  } //}}}
  public function setAlias($descr) //{{{
  {
    $this->commands['setalias'] = '{"system":{"set_dev_alias":{"alias":"'.addslashes($descr).'"}'.'}'.'}';
    return($this->runCommand('setalias'));
  } //}}}

  private function runCommand($cmd,$ret=FALSE) //{{{
  {
    if (isset($this->commands[$cmd]) && ($ret = $this->callSwitch($this->commands[$cmd])) !== FALSE) {
      if (($json = @json_decode($ret)) !== NULL) {
        $ret = $json;
      }
    }
    return($ret);
  } //}}}

  private function callSwitch($data,$ret=FALSE) //{{{
  {
    if (($cmd = $this->encrypt($data)) !== FALSE) {
      if (($socket = @socket_create(AF_INET,SOCK_STREAM,SOL_TCP)) !== FALSE) {
        socket_set_option($socket,SOL_SOCKET,SO_SNDTIMEO,array('sec'=>10,'usec'=>0));
        if (($result = @socket_connect($socket,$this->ip,$this->port)) !== FALSE) {
          @socket_write($socket,$cmd,strlen($cmd));

          if (($bytes = @socket_recv($socket,$buf,2048,MSG_PEEK)) !== FALSE) {
            $ret = $this->decrypt($buf);
          }
          else {
            $ret = FALSE;
          }
        }
        @socket_close($socket);
      }
    }
    return($ret);
  } //}}}
  private function encrypt($buf) //{{{
  {
    $ret = pack("N",strlen($buf));

    $l = strlen($buf);
    $i = 171;

    for ($i2 = 0; $i2 < $l; $i2++) {
      $b = $i ^ ord(substr($buf,$i2,1));
      $i = $b;
      $ret .= chr($b);
    }

    return($ret);
  } //}}}
  private function decrypt($buf) //{{{
  {
    $ret = "";

    $l = strlen($buf);
    $i = 171;

    for ($i2 = 4; $i2 < $l; $i2++) {
      $b = $i ^ ord(substr($buf,$i2,1));
      $i = ord(substr($buf,$i2,1));
      $ret .= chr($b);
    }

    return($ret);
  } //}}}
}

?>
