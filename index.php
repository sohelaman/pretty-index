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
// if( isset($_POST['submit']) and $_POST['submit'] == 'Run' ) {
//   if( isset($_POST['code']) ) {
//     $code = trim($_POST['code']);
//     ob_start();
//     eval($code);
//     $eval_output = ob_get_contents();
//     ob_end_clean();
//   }
// }
# php eval alternative
# request sent using HTTP_X_REQUESTED_WITH
if( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ){
  if(isset($_POST['eval_code'])){
    $name = trim($_POST['eval_code']);
    ob_start();
    eval($name);
    $eval_output = ob_get_contents();
    ob_end_clean();
    echo $eval_output;
    return;
  }else{
    return;
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
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script type="text/javascript">
      window.onload = function() {
        if (window.jQuery) {
          // jQuery is loaded
          $('#eval_output').hide();
          $(document).ready(function() {
            var form = $('#eval_form'); // contact form
            var submit = $('#submit');  // submit button
            var eval_text = $('#eval_output'); // eval_output div for show alert message
            // form submit event
            form.on('submit', function(e) {
              e.preventDefault(); // prevent default form submit
              // sending ajax request through jQuery
              $.ajax({
                url: '', // form action url
                type: 'POST', // form submit method get/post
                dataType: 'html', // request type html/json/xml
                data: form.serialize(), // serialize form data
                beforeSend: function() {
                  eval_text.fadeOut();
                  submit.html('Processing...'); // change submit button text
                },
                success: function(data) {
                  eval_text.show(); // show the eval box
                  eval_text.html(data).fadeIn(); // fade in response data
                  form.trigger('reset'); // reset form
                  submit.html('Run'); // reset submit button text
                },
                error: function(e) {
                  console.log(e); //show error on console
                }
              });
            });
          });
        } else {
          // jQuery is not loaded
          <?php
            if(isset($_POST['eval_code'])){
              $name = trim($_POST['eval_code']);
              ob_start();
              eval($name);
              $eval_output = ob_get_contents();
              ob_end_clean();
            }
          ?>
        }
      }
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
      <div id="eval_output" class="block eval_output">
        <p><?php echo $eval_output; ?></p>
      </div>
      <div class="block">
        <form id="eval_form" method="post" action="">
          <textarea class="code" id="eval_code" name="eval_code" rows="4" placeholder="PHP code here..."></textarea>
          <br/><button name="submit" type="submit" id="submit">Run</button>
        </form>
      </div>
    </div>
  </body>
</html>
