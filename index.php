<?php

$pi = new Pi();
$current_dir_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
list( $directories, $files ) = $pi->getDirContents();
$local_addr = $pi->getLocalAddress();
$isBookmarksEnabled = !empty($pi->storage);
$bookmarks = $pi->getBookmarks();
$errors = $pi->errors;

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Localhost</title>
    <style type="text/css">
      <?php for( $i=1; $i<13; $i++ ) { $w=$i*8.33; print ".col-{$i} {width: {$w}%;}\n";} ?>
      [class*="col-"] {float: left;}
      @media only screen and (max-width: 768px) {
        [class*="col-"] { width: 100%; }
      }
      .clearfix:after { visibility: hidden; display: block; font-size: 0; content: " "; clear: both; height: 0; }
      * html .clearfix { zoom: 1; }
      *:first-child+html .clearfix { zoom: 1; }
      body { background-color: #666666; }
      a { text-decoration: none !important; background: none !important; }
      a:hover { color: red; }
      p { margin: 0; padding: 2px 0; }
      svg { padding-top: 2px; }
      .errorbox p { color: red; }
      .container { font-family: monospace; max-width: 768px; background: #DDD; padding: 10px; margin: auto; }
      .center { text-align: center; }
      .text-left { text-align: left; }
      .text-right { text-align: right; }
      .left { float: left; }
      .right { float: right; }
      .hidden { display: none; }
      div.block { border: 1px solid #000; padding: 4px; margin: 8px 4px; overflow: auto; }
      div.eval_output{ max-height: 250px; overflow-wrap: break-word; overflow: auto; }
      .menu { padding: 0 4px 0 0; font-size: 14px; font-weight: bold; }
      input { padding-left: 2px; padding-right: 2px; }
      textarea.code { width:100%; height:100%; max-width: 100%; box-sizing: border-box; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; }
      .stretch { display: inline-block; margin: 0 1vw; }
      .current-dir:hover { background-color: lightblue; }
      .bm-list-wrapper { overflow: hidden; }
      ul { padding: 0; margin: 0; }
      .bookmarks { display: inline-block; padding: 1px 4px 2px 4px;}
      .search_engines li { padding: 2px 0;}
      .exterminate a { color: red; }
      .exterminate .bookmarks:hover { text-decoration: line-through; }
      #bm-form-wrapper { padding-top: 2px; padding-bottom: 4px; }
      #bm-list{ max-width: 700px; margin-left: 10px; }
      ::-webkit-scrollbar { width: 6px; height: 6px; } /* Track */
      ::-webkit-scrollbar-track { -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3); -webkit-border-radius: 10px; border-radius: 10px; }
       /* Handle */
      ::-webkit-scrollbar-thumb { -webkit-border-radius: 10px; border-radius: 10px; background: rgba(0,0,0,0.8); -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.5); }
      ::-webkit-scrollbar-thumb:window-inactive { background: rgba(0,0,0,0.4); }
    </style>
    <script>
      (function() {
        if(navigator.onLine){
          var addJquery = document.createElement("script");
          addJquery.async = false;
          addJquery.type = "text/javascript";
          addJquery.src = "//cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js";
          var node = document.getElementsByTagName("script")[0];
          node.parentNode.insertBefore(addJquery, node);
        }
      })();
    </script>
    <script type="text/javascript">
      window.onload = function() {
        function bookmark_delete(){
          var bm_list = document.getElementById('bookmarks-list');
          if( bm_list == null ) return;
          bm_list.addEventListener('click', function(event) {
            if (bm_list.className == "exterminate") {
              var clickedEl = event.target;
              if(clickedEl.tagName === 'A') {
                event.preventDefault();
                if( confirm('Delete bookmark "' + clickedEl.innerHTML + '"?') ) {
                  var listItem = clickedEl.parentNode;
                  listItem.parentNode.removeChild(listItem);
                  var bm_id = clickedEl.getAttribute('data-bookmark-id');
                  var xhttp = new XMLHttpRequest();
                  xhttp.open("POST", window.location.href, true);
                  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                  xhttp.send("delete=" + bm_id);
                }
              }
            }
          });
        }
        bookmark_delete();
        jstime('datetime');
        document.getElementById('eval_code').focus();
        document.getElementById('eval_output').style.display='none';
        
        // Ajax load ip from ipfy.org
        ipfy('https://api.ipify.org?format=json').then( function( response ) {
          if(response) { document.getElementById('public_ip').innerHTML = JSON.parse(response).ip; }
        }, function( Error ) {
          console.log(Error);
        });

        if (window.jQuery) {
          // jQuery is loaded
          $(document).ready(function() {
            var form = $('#eval_form'); // contact form
            var submit = $('#runBtnSubmit');  // submit button
            var eval_text = $('#eval_output'); // eval_output div for show alert message
            var info_wrapper = $('#info_wrapper'); // information wrapper
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
                  $('#evalErrMsg').html('');
                  $('#evalErrWrapper').hide();
                },
                success: function(data) {
                  eval_text.show(); // show the eval box
                  eval_text.html(data).fadeIn(); // fade in response data
                  submit.html('Run'); // reset submit button text
                  info_wrapper.hide(); // hide information fields
                },
                error: function(e) {
                  console.log(e); //show error on console
                  $('#evalErrMsg').html('Error ' + e.status + ' : ' + e.statusText);
                  $('#evalErrWrapper').show();
                }
              });
            });
          });
        } else {
          // jQuery is not loaded
          <?php
            if( isset($_POST['eval_code']) && isset($_POST['modeSelect']) ){
            ?>
            document.getElementById('eval_output').style.display='block';
            document.getElementById('mode_select').value="<?php echo $_POST['modeSelect']; ?>";
            <?php
              $eval_data = trim($_POST['eval_code']);
              ob_start();
              switch ($_POST['modeSelect']) {
                case 'encode':
                  echo base64_encode($eval_data);
                  break;
                case 'decode':
                  echo base64_decode($eval_data);
                  break;
                case 'serialize':
                  eval ( "echo serialize($eval_data);" );
                  break;
                case 'unserialize':
                  // var_dump( unserialize($eval_data) );
                  print_r( unserialize($eval_data) );
                  break;
                default:
                  eval($eval_data);
                  break;
              }
              $eval_output = ob_get_contents();
              ob_end_clean();
            }
          ?>
        }
      }
    </script>
    <script type="text/javascript">
      function jstime(elem) {
        var dateObj = new Date();
        var datePart = dateObj.toLocaleString("en", { weekday: "long"  })
                          + ', ' + dateObj.toLocaleString("en", { month: "long" })
                          + ' ' + dateObj.toLocaleString("en", { day: "numeric" })
                          + ', ' + dateObj.toLocaleString("en", { year: "numeric"});
        var h = dateObj.getHours();
        var m = dateObj.getMinutes();
        var s = dateObj.getSeconds();
        h = (h < 10) ? "0" + h : h; m = (m < 10) ? "0" + m : m; s = (s < 10) ? "0" + s : s;
        var timePart = h + ":" + m + ":" + s;
        document.getElementById(elem).innerHTML = timePart + ' - ' + datePart;
        var timePart = setTimeout(function() {
          jstime(elem);
        }, 900);
      }
      function phpi() {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
            var x = window.open('', '', 'location=no, toolbar=0');
            x.document.body.innerHTML = `${this.responseText}`;
          }
        };
        xhttp.open("POST", window.location.href, true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("phpinfo=1");
      }
      function reset_text() {
        clearInputValue('eval_code');
        document.getElementById("eval_output").innerHTML='';
        document.getElementById("runBtnSubmit").innerHTML='Run';
        document.getElementById("eval_output").style.display='none';
        document.getElementById("info_wrapper").style.display='block';
      }
      function toggleElementView(elementID) {
        var elem = document.getElementById(elementID);
        elem.style.display = elem.style.display === 'none' ? 'block' : 'none';
      }
      function webSearch(elem) {
        var uri = elem.getAttribute('data-uri');
        var query = document.getElementById('search_query').value;
        var win = window.open(uri + query, '_blank');
        win.focus();
      }
      function clearInputValue(elementID) {
        document.getElementById(elementID).value = '';
      }
      function defaultSearch(e, q) {
        if(e.keyCode === 13) { // 13 === enter/return key
          e.preventDefault();
          var win = window.open('//www.google.com/search?q=' + q, '_blank');
          win.focus();
        }
      }
      function codeValidate(e, fieldSelector) {
        var code = document.getElementById(fieldSelector).value;
        if( code.trim() == "" ) {
          e.preventDefault();
        }
      }
      function codeEvalKey(e, elem) {
        if( elem.value.trim() != "" && (e.ctrlKey || e.metaKey) && (e.keyCode == 13 || e.keyCode == 10) ) { // 13 === enter/return key
          document.getElementById('runBtnSubmit').click();
        }
      }
      function remove_btn(){
        var bm_list = document.getElementById('bookmarks-list');
        bm_list.classList.toggle('exterminate');
      }
      function bookmarkSave() {
        var title = document.getElementById('bookmark_name').value;
        var url = document.getElementById('bookmark_url').value;
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
            location.reload();
          }
        };
        xhttp.open("POST", window.location.href, true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send( "addbookmark=1&bookmark_name=" + title + "&bookmark_url=" + encodeURI(url) );
      }
      function ipfy(url) {
        return new Promise( function( resolve, reject ) {
          var request = new XMLHttpRequest();
          request.open('GET', url);
          request.onload = function() {
            if( request.status === 200 )
              resolve( request.response );
            else
              reject( Error('Failed to load IP. Error code: ' + request.statusText) );
          };
          request.onerror = function() { reject(Error('Request failed.')); };
          request.send();
        });
      } // end of ipfy()
    </script>
  </head>
  <body>
    <div class="container">
      <?php if( !empty($errors) ) { ?>
        <div class="block center errorbox">
          <?php foreach( $errors as $error ) { ?>
            <p><?php print $error; ?></p>
          <?php } ?>
        </div>
      <?php } ?>
      <div class="block center">
        <a href="javascript:location.reload();"><h1>localhost | <?php echo getHostByName(getHostName()); ?></h1></a>
      </div>
      <div id="info_wrapper">
        <div class="block center">
          <h3 id="datetime"><?php echo date('l, F j, H:i:s'); ?></h3>
        </div>
        <div class="block center clearfix">
          <div class="col-12">
            <div class="col-6 first">
              <p>Public IP : <b><span id="public_ip">N/A</span></b></p>
              <p>LAN IP : <b><?php echo $local_addr; ?></b></p>
              <p>Host IP : <b><?php echo getHostByName( getHostName() ); ?></b></p>
              <p>Remote IP : <b><?php echo $_SERVER['REMOTE_ADDR']; ?></b></p>
            </div>
            <div class="col-6 last">
              <p>Document Root : <b><?php echo $_SERVER['DOCUMENT_ROOT']; ?></b></p>
              <p>PHP Version : <b><?php echo phpversion(); ?> (<a href="javascript:phpi();">phpinfo</a>)</b></p>
              <p>PHP Loaded INI : <b><?php echo php_ini_loaded_file(); ?></b></p>
              <p>PHP Timezone : <b><?php echo date_default_timezone_get(); ?></b></p>
            </div>
          </div>
        </div>

        <div class="block search clearfix center">
          <div class="col-12 clearfix">
            <ul class="search_engines">
              <li>
                <input type="text" id="search_query" size="50" maxlength="255" value="" onkeypress="defaultSearch(event, this.value);" placeholder="Search in the web"/>
                <input type="button" value=" X " onclick="clearInputValue('search_query');" />
                <input type="button" value="Google" data-uri="//www.google.com/search?q=" onclick="webSearch(this);" />
                <input type="button" value="Bing" data-uri="//www.bing.com/search?q=" onclick="webSearch(this);" />
                <input type="button" value="Dictionary" data-uri="//www.dictionary.com/browse/" onclick="webSearch(this);" />
              </li>
              <li>
                <!-- <input type="button" value="DuckDuckGo" data-uri="//duckduckgo.com/?q=" onclick="webSearch(this);" /> -->
                <input type="button" value="Github" data-uri="//github.com/search?q=" onclick="webSearch(this);" />
                <input type="button" value="StackOverFlow" data-uri="//stackoverflow.com/search?q=" onclick="webSearch(this);" />
                <input type="button" value="Packagist" data-uri="//packagist.org/search/?q=" onclick="webSearch(this);" />
                <input type="button" value="NPM" data-uri="//www.npmjs.com/search?q=" onclick="webSearch(this);" />
                <input type="button" value="Bower" data-uri="//bower.io/search/?q=" onclick="webSearch(this);" />
                <input type="button" value="RubyGems" data-uri="//rubygems.org/search?query=" onclick="webSearch(this);" />
                <input type="button" value="Libraries.io" data-uri="//libraries.io/search?q=" onclick="webSearch(this);" />
                <!-- <input type="button" value="NPMS" data-uri="//npms.io/search?q=" onclick="webSearch(this);" /> -->
                <input type="button" value="Explain Shell" data-uri="//explainshell.com/explain?cmd=" onclick="webSearch(this);" />
              </li>
            </ul>
          </div>
        </div>
        <?php if($isBookmarksEnabled) { ?>
        <div id="bm-form-wrapper" class="block center" style="display: none;">
          <label for="bookmark_name">Title: </label><input type="text" name="bookmark_name" id="bookmark_name" placeholder="Enter bookmark title">
          <label for="bookmark_url">URL: </label><input type="text" name="bookmark_url" id="bookmark_url" placeholder="Enter URL to bookmark">
          <input type="button" id="bookmark_save" name="bookmark_save" onclick="bookmarkSave();" value="Save Bookmark">
        </div>
        <div class="block" id="bookmark_wrapper">
          <div class="bm-list-wrapper">
            <div class="left">
              <a href="javascript:;" onclick="toggleElementView('bm-form-wrapper')" title="Add Bookmark"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="13" height="13" viewBox="0 0 32 32"><path d="M31 12h-11v-11c0-0.552-0.448-1-1-1h-6c-0.552 0-1 0.448-1 1v11h-11c-0.552 0-1 0.448-1 1v6c0 0.552 0.448 1 1 1h11v11c0 0.552 0.448 1 1 1h6c0.552 0 1-0.448 1-1v-11h11c0.552 0 1-0.448 1-1v-6c0-0.552-0.448-1-1-1z"></path></svg></a>
            </div>
            <div id="bm-list" style="display: inline-block;">
              <ul id="bookmarks-list">
                <?php if( count($bookmarks) > 0 ) { ?>
                  <?php foreach($bookmarks as $bookmark) { ?>
                    <li class="bookmarks"><a href="<?php echo $bookmark->url;?>" data-bookmark-id="<?php echo $bookmark->id;?>" target="_blank"><?php echo $bookmark->title;?></a></li>
                  <?php } ?>
                <?php } else { ?>
                  <li class="bookmarks bookmark-help">No bookmarks. Add bookmarks using the <b>+</b> button.</li>
                <?php } ?>
              </ul>
            </div>
            <div class="right">
              <a href="javascript:;" onclick="remove_btn()" title="Exterminator! Click me and then click on any bookmark to delete it"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="13" height="13" viewBox="0 0 32 32"><path d="M31.708 25.708c-0-0-0-0-0-0l-9.708-9.708 9.708-9.708c0-0 0-0 0-0 0.105-0.105 0.18-0.227 0.229-0.357 0.133-0.356 0.057-0.771-0.229-1.057l-4.586-4.586c-0.286-0.286-0.702-0.361-1.057-0.229-0.13 0.048-0.252 0.124-0.357 0.228 0 0-0 0-0 0l-9.708 9.708-9.708-9.708c-0-0-0-0-0-0-0.105-0.104-0.227-0.18-0.357-0.228-0.356-0.133-0.771-0.057-1.057 0.229l-4.586 4.586c-0.286 0.286-0.361 0.702-0.229 1.057 0.049 0.13 0.124 0.252 0.229 0.357 0 0 0 0 0 0l9.708 9.708-9.708 9.708c-0 0-0 0-0 0-0.104 0.105-0.18 0.227-0.229 0.357-0.133 0.355-0.057 0.771 0.229 1.057l4.586 4.586c0.286 0.286 0.702 0.361 1.057 0.229 0.13-0.049 0.252-0.124 0.357-0.229 0-0 0-0 0-0l9.708-9.708 9.708 9.708c0 0 0 0 0 0 0.105 0.105 0.227 0.18 0.357 0.229 0.356 0.133 0.771 0.057 1.057-0.229l4.586-4.586c0.286-0.286 0.362-0.702 0.229-1.057-0.049-0.13-0.124-0.252-0.229-0.357z"></path></svg></a>
            </div>
          </div>
        </div>
        <?php } ?>
        <a onclick="toggleElementView('directory_index');" href="javascript:;">
          <div class="block clearfix center current-dir">Current directory: <?php echo __DIR__; ?></div>
        </a>
        <div id="directory_index" class="block directory_index clearfix" style="display: none;">
          <div id="dir-contents" class="col-12">
            <div class="col-6 text-left" >
              <p>Directories</p><hr>
              <?php
              foreach( $directories as $folder ) {
                echo '<b><a href="' . $current_dir_url . '/' . $folder . '">' . $folder . '</a></b><br/>';
              }
              ?>
            </div>
            <div class="col-6 text-right">
              <p>Files</p><hr>
              <?php
              foreach( $files as $file ) {
                echo '<i><a href="' . $current_dir_url . '/' . $file . '">' . $file . '</a></i><br/>';
              }
              ?>
            </div>
          </div>
        </div>
      </div>
      <div class="block">
        <form id="eval_form" method="post" action="" >
          <textarea class="code" id="eval_code" name="eval_code" rows="6" onkeypress="codeEvalKey(event, this);" placeholder="PHP code here..."><?php echo empty($eval_data) ? '' : $eval_data; ?></textarea>
          <br/><button name="submit" type="submit" id="runBtnSubmit" onclick="codeValidate(event, 'eval_code');">Run</button>
          <button name="clearText" type="button" id="clearText" onclick="clearInputValue('eval_code');">Clear Text</button>
          <button name="resetText" type="button" id="resetText" onclick="reset_text()">Reset</button>
          <select name="modeSelect" id="mode_select">
            <option value="eval">PHP Eval</option>
            <option value="encode">Base64 Encode</option>
            <option value="decode">Base64 Decode</option>
            <option value="serialize">Serialize</option>
            <option value="unserialize">Unserialize</option>
          </select>
        </form>
      </div>
      <div id="eval_output" class="block eval_output">
        <p><?php if(isset($eval_output)) { echo $eval_output; } ?></p>
      </div>
      <div id="evalErrWrapper" class="block hidden errorbox">
        <p id="evalErrMsg"></p>
      </div>
      <footer><div class="text-right"><a href="https://github.com/sohelamankhan/pretty_index" target="_blank"><i>Pretty Index</i></a></div></footer>
    </div>
  </body>
</html>


<?php

class Pi {
  const DB = 'pretty_index.sqlite';
  const FS = 'pretty_index.json';
  private $_pdo = null;
  private $_jsonData = null;
  public $storage = null;
  public $errors = array();

  function __construct() {
    $this->_setupStorage();
    $this->_handleRequests();
  }

  private function _handleRequests() {
    if ( !empty($_REQUEST['phpinfo']) and $_REQUEST['phpinfo'] == 1 ) {
      phpinfo();
      exit;
    } else if( !empty($_REQUEST['addbookmark']) and !empty($_REQUEST['bookmark_name']) and !empty($_REQUEST['bookmark_url']) ) {
      $this->saveBookmark( $_REQUEST['bookmark_name'], $_REQUEST['bookmark_url'] );
      exit;
    } else if ( !empty($_REQUEST['delete']) ) {
      $this->deleteBookmark($_REQUEST['delete']);
    } else if( !empty($_POST['eval_code']) and isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and isset($_POST['modeSelect']) ) {
      $eval_data = trim($_POST['eval_code']);
      $modeSelect = $_POST['modeSelect'];
      ob_start();
      switch ($modeSelect) {
        case 'encode':
          echo base64_encode($eval_data);
          break;
        case 'decode':
          echo base64_decode($eval_data);
          break;
        case 'serialize':
          eval ( "echo serialize($eval_data);" );
          break;
        case 'unserialize':
          // var_dump( unserialize($eval_data) );
          print_r( unserialize($eval_data) );
          break;
        default:
          eval($eval_data);
          break;
      }
      $eval_output = ob_get_contents();
      ob_end_clean();
      echo $eval_output;
      exit;
    }
  } // end of _handleRequests()

  private function _setupStorage() {
    try {
      if( is_writable(__DIR__) and in_array('sqlite', PDO::getAvailableDrivers()) ) {
        $this->_pdo = new PDO( "sqlite:" . self::DB );
        $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_pdo->exec("CREATE TABLE IF NOT EXISTS bookmarks (id INTEGER PRIMARY KEY, title TEXT NOT NULL UNIQUE, url TEXT NOT NULL UNIQUE)");
        $this->storage = self::DB;
      } else if( touch(self::FS) and is_writable(self::FS) ) {
        $json = json_decode( file_get_contents(self::FS) );
        $jsonDB = new stdClass();
        $indices = new stdClass();
        $indices->bookmarksIndex = 0;
        $jsonDB->indices = $indices;
        $jsonDB->bookmarks = array();
        $this->_jsonData = !empty($json) ? $json : $jsonDB;
        $this->storage = self::FS;
      } else {
        $this->errors[] = "Bookmark feature requires pdo-sqlite extension and write permission on " . __DIR__ . " directory.";
        $this->errors[] = "Alternatively, a writable JSON file named " . self::FS . " will do.";
      }
    } catch( PDOException $e ) {
      $this->errors[] = $e->getMessage();
    }
  } // end of _setupStorage()

  private function _query( $sql ) {
    try {
      return !empty($this->_pdo) ? $this->_pdo->query($sql, PDO::FETCH_CLASS, "stdClass") : false;
    } catch (Exception $e) {
      $this->errors[] = $e->getMessage();
    }
  }

  private function _writeJSON( $option = JSON_PRETTY_PRINT ) {
    if( !empty($this->_jsonData) )
      return @file_put_contents( self::FS, json_encode($this->_jsonData, $option) );
    return false;
  }

  public function getLocalAddress() {
    if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
      exec("ipconfig /all", $output);
      foreach($output as $line) {
        if(preg_match("/(.*)IPv4 Address(.*)/", $line)) {
          if( preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $line, $match) ) {
            if( filter_var($match[0], FILTER_VALIDATE_IP) ) { return trim($match[0]); }
          }
        }
      } // endforeach
    } else if(strtoupper(PHP_OS) == 'LINUX') {
      $methods = array();
      $methods[] = function() { return `ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'`; };
      $methods[] = function() { return `ifconfig | sed -En 's/127.0.0.1//;s/.*inet (addr:)?(([0-9]*\.){3}[0-9]*).*/\2/p'`; };
      $methods[] = function() { return `ip route get 1 | awk '{print $NF;exit}'`; };
      foreach( $methods as $method ) {
        $ip = trim($method());
        if( filter_var($ip, FILTER_VALIDATE_IP) ) { return $ip; }
      } // endforeach
    }
    return "N/A";
  } // end of getLocalAddress()

  public function getDirContents( $dir = __DIR__ ) {
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

  public function saveBookmark($title, $url) {
    if( empty($this->storage) ) { return; }
    if( $this->storage === self::DB ) {
      $title = $this->_pdo->quote($title);
      $url = $this->_pdo->quote($url);
      $this->_query("INSERT INTO bookmarks (id, title, url) VALUES (NULL, $title, $url)");
    } else {
      $newBookmark = new stdClass();
      $newBookmark->id = ++$this->_jsonData->indices->bookmarksIndex;
      $newBookmark->title = $title;
      $newBookmark->url = $url;
      $this->_jsonData->bookmarks[] = $newBookmark;
      $this->_writeJSON(0);
    }
  }

  public function deleteBookmark($id) {
    if( empty($this->storage) ) { return; }
    if( $this->storage === self::DB ) {
      $this->_query("DELETE FROM bookmarks WHERE id = $id");
    } else {
      foreach( $this->_jsonData->bookmarks as $index => $bookmark ) {
        if( $bookmark->id == $id ) {
          unset( $this->_jsonData->bookmarks[$index] );
          $this->_jsonData->bookmarks = array_values($this->_jsonData->bookmarks); // important to reindex
          $this->_writeJSON(0);
          break;
        }
      } // end foreach
    }
  }

  public function getBookmarks() {
    if( empty($this->storage) ) { return array(); }
    $bm = ( $this->storage === self::DB ) ? $this->_query("SELECT * FROM bookmarks")->fetchAll() : $this->_jsonData->bookmarks;
    return count($bm) > 0 ? $bm : array();
  }

} // end of class Pi

?>
