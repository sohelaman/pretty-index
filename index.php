<?php

// config
$localhost_url = "http://localhost";
$phpMyAdmin_Url = $localhost_url . "/pma";
$adminer_url = $localhost_url . "/adm";
$current_script_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
$current_dir_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);

// menu bookmarks
$bookmarks = array();
$bookmarks['phpMyAdmin'] = $phpMyAdmin_Url;
$bookmarks['Adminer'] = $adminer_url;

// booleans
$get_public_ip = true;

// core
$date_time = date('l, F j, H:i:s');
$host_name = getHostName();
$host_ip = getHostByName($host_name);
$remote_ip = $_SERVER['REMOTE_ADDR'];
list( $directories, $files ) = getDirContents();
$local_addr = getLocalAddress();
$public_addr = ( $get_public_ip ) ? getPublicAddress() : 'N/A';
$doc_root = $_SERVER['DOCUMENT_ROOT'];
$php_version = phpversion();
$php_ini = php_ini_loaded_file();
$php_tz = date_default_timezone_get();


// php eval
if( isset($_POST['submit']) and $_POST['submit'] == 'Run' ) {
  if( isset($_POST['code']) ) {
    $code = trim($_POST['code']);
    ob_start();
    eval($code);
    $eval_output = ob_get_contents();
    ob_end_clean();
  }
}

// functions

function getLocalAddress() {
  if(strtoupper(PHP_OS) == 'LINUX') {
    return trim(explode(' ',explode(':',explode('inet addr',explode('eth0',trim(`ifconfig`))[1])[1])[1])[0]);
  } else if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
    exec("ipconfig /all", $output);
    foreach($output as $line) {
      if(preg_match("/(.*)IPv4 Address(.*)/", $line)) {
        $ip = $line;
        $ip = str_replace("IPv4 Address. . . . . . . . . . . :","",$ip);
        $ip = str_replace("(Preferred)","",$ip);
      }
    }
    return trim($ip);
  }
  return null;
}


function getDirContents( $dir = __DIR__ ) {
  $directories = array();
  $files_list  = array();
  $files = scandir($dir);
  foreach($files as $file) {
     if(($file != '.') && ($file != '..')) {
        if(is_dir($dir . '/' . $file)) {
          $directories[] = $file;
        } else {
          $files_list[] = $file;
        }
     }
  }
  return array( $directories, $files_list );
}


function getPublicAddress() {
  $api_url = "https://api.ipify.org?format=json";
  $data = file_get_contents($api_url);
  $obj = json_decode($data);
  return $obj->ip;
}

?>


<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Localhost</title>
    <style type="text/css">
      a {
        text-decoration: none;
      }
      a:hover {
        color: red;
      }
      .container {
        font-family: monospace;
        width: 50%;
        background: #DDD;
        padding: 10px;
        margin: auto;
      }
      .center {
        text-align: center;
      }
      .left {
        text-align: left;
      }
      .right {
        text-align: right;
      }
      div.block {
        border: 1px solid #000;
        padding: 4px;
        margin: 8px 4px;
        overflow: auto;
      }
      div.footer {
        padding: 4px;
        margin: 4px 4px;
      }
      table.mid {
        margin: auto;
      }
      table.wide {
        width: 100%;
      }
      td.menu {
        padding: 0 4px;
        font-size: 14px;
      }
      textarea.code {
        width:100%;
        height:100%; 
        max-width: 100%;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
      }
    </style>
    <script type="text/javascript">
      // console.log('Hello, world!');
    </script>
  </head>
  <body>
    <div class="container">
      <div class="block center">
        <a href="<?php echo $localhost_url; ?>"><h1>localhost | <?php echo $host_ip; ?></h1></a>
      </div>
      
      <div class="block center">
        <h3><?php echo $date_time; ?></h3>
      </div>
      
      <div class="block">
        <table class="mid">
          <td>
            <table>
              <tr><td>Public IP</td><td>:</td><td><b><?php echo $public_addr; ?></b></td></tr>
              <tr><td>LAN IP</td><td>:</td><td><b><?php echo $local_addr; ?></b></td></tr>
              <tr><td>Host IP</td><td>:</td><td><b><?php echo $host_ip; ?></b></td></tr>
              <tr><td>Remote IP</td><td>:</td><td><b><?php echo $remote_ip; ?></b></td></tr>
            </table>
          </td>
          <td>&nbsp;&nbsp;</td>
          <td>
            <table>
              <tr><td>Document Root</td><td>:</td><td><b><?php echo $doc_root; ?></b></td></tr>
              <tr><td>PHP Version</td><td>:</td><td><b><?php echo $php_version; ?></b></td></tr>
              <tr><td>PHP Loaded INI</td><td>:</td><td><b><?php echo $php_ini; ?></b></td></tr>
              <tr><td>PHP Timezone</td><td>:</td><td><b><?php echo $php_tz; ?></b></td></tr>
            </table>
          </td>
        </table>
      </div>
      
      <div class="block">
        <table class="mid">
          <td>
            <form method="get" action="http://www.google.com/search">
              <input type="text" name="q" size="30" maxlength="255" value="" placeholder="Google search"/>
              <input type="submit" value="Google" />
            </form>
          </td>
          <td></td>
          <td>
            <form method="get" action="http://www.bing.com/search">
              <input type="text" name="q" size="30" maxlength="255" value="" placeholder="Bing search"/>
              <input type="submit" value="Bing" />
            </form>
          </td>
        </table>
      </div>
      
      <div class="block">
        <table class="mid">
          <?php
          foreach ($bookmarks as $key => $value) {
            echo '<td class="menu"><b><a href="' . $value . '">' . $key . '</a></b></td>';
          }
          ?>
        </table>
      </div> 

      <div class="block">
        <table class="wide">
          <td valign="top" class="left">
            <?php
            foreach( $directories as $folder ) {
              echo '<b><a href="' . $current_dir_url . '/' . $folder . '">' . $folder . '</a></b><br/>';
            }
            ?>
          </td>
          <td valign="top" class="right">
            <?php
            foreach( $files as $file ) {
              echo '<i><a href="' . $current_dir_url . '/' . $file . '">' . $file . '</a></i><br/>';
            }
            ?>
          </td>
        </table>
      </div>
      
      <?php if( isset( $eval_output ) and ! empty($eval_output) ) { ?>
      <div class="block">
        <p><?php echo $eval_output; ?></p>
      </div>
      <?php } ?>
      
      <div class="block">
        <form method="post">
          <textarea class="code" name="code" rows="4" placeholder="PHP code here..."></textarea>
          <br/><input type="submit" name="submit" value="Run">
        </form>
      </div>
    </div>
  </body>
</html>
