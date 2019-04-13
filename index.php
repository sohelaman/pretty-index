<?php

$pi = new Pi();
$current_dir_url = rtrim("//" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']), '/');
list($directories, $files) = $pi->getDirContents();
$local_addr = $pi->getLocalAddress();

function colStyleGen($prefix = "col") {
  $temp = '';
  for ($i = 1; $i < 13; $i++) {$w = round($i * (100 / 12), 2);
    $temp .= '.' . $prefix . "-{$i} {width: {$w}%;}\n";}
  return $temp;
} // end of colStyleGen()

?>

<!DOCTYPE html>
<html>
<head>
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Pretty Index</title>
  <style type="text/css">
    * { box-sizing: border-box; }
    html { font-family: "Lato", sans-serif; }
    .row::after { content: ""; clear: both; display: table; }
    [class*="col-"] { float: left; padding: 10px; }
    .header { background-color: teal; color: white; padding: 15px; }
    .menu ul { list-style-type: none; margin: 0; padding: 0; }
    .menu li {
      padding: 8px;
      margin-bottom: 7px;
      background-color: purple;
      color: white;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
    }
    .menu li:hover { background-color: skyblue; }
    .aside {
      background-color: purple;
      padding: 15px;
      color: white;
      font-size: 14px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
    }
    .footer {
      background-color: teal;
      color: white;
      text-align: center;
      font-size: 12px;
      padding: 15px;
    }
    .btn {
      background-color: darkslategray;
      border: none;
      color: white;
      padding: 5px;
      margin: 3px;
      text-align: center;
      text-decoration: none;
      display: inline-block;
      cursor: pointer;
    }
    input[type=text], textarea {
      border: 1px solid darkslategray;
      padding: 4px;
      box-sizing: border-box;
    }
    select { padding: 4px; border: none; }
    .spinner { display: inline; }
    .spinner:after {
      content: " ";
      display: block;
      width: 18px;
      height: 18px;
      margin: 1px;
      border-radius: 50%;
      border: 3px solid darkslategray;
      border-color: darkslategray transparent darkslategray transparent;
      animation: spinner 1.2s linear infinite;
    }
    @keyframes spinner {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .disappear {
      animation: cssAnimDisappear 0s 3s forwards;
      visibility: visible;
    }
    @keyframes cssAnimDisappear {
      to { visibility: hidden; }
    }

    /* For mobile phones: */
    [class*="col-"] { width: 100%; }

    @media only screen and (min-width: 600px) {
      /* For tablets: */
      <?php print colStyleGen('col-s');?>
    }

    @media only screen and (min-width: 768px) {
      /* For desktop: */
      <?php print colStyleGen();?>
    }
  </style>
  <style type="text/css">
    @media only screen and (min-width: 600px) {
      /* For tablets: */
      .center { width: 80%; }
    }
    @media only screen and (min-width: 768px) {
      /* For desktop: */
      .center { width: 60%; }
    }

    .content a { text-decoration: none; color: royalblue; }
    .content a:hover { color: crimson; }
    .center { text-align: center; margin: 0 auto; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    .right { float: right; }
    .left { float: left; }
    .pad-right { padding-right: 10px; }
    .pad-left { padding-left: 10px; }
    .pad { padding: 4px; }
    .wide { width: 100%; }
    .hidden { display: none; }
    .dark-matter { visibility: hidden; }
    .blurry { opacity: 0.4; }
    .callout { border: 1px solid black; margin: 4px; padding: 4px; }
    .spacer { height: 10px; }
    .current-dir:hover { background-color: whitesmoke; }
    .content textarea { width: 100%; resize: vertical; }
    .content textarea#code-box, #code-result { font-family: monospace, sans-serif; background-color: whitesmoke; }
    .content #search-query { min-width: 50%; }
    .content #code-result, #todo-list { overflow-x: auto; }
    .error { color: red; }
    .warn { color: orange; }
    .spinner { display: none; }
    .content .exterminate a.bookmark { color: red; }
    .content .exterminate a.bookmark:hover { text-decoration: line-through; }
    /*.sidepane { padding-left: 0; }*/
    .delete-todo { font-weight: bold; }
  </style>
  </meta>
</head>

<body>

  <div class="spacer"></div>

  <div class="content center">
    <div class="row">

      <div class="main col-9 col-s-12">
        <div class="row" id="host-wrapper">
          <div class="callout">
            <a href="javascript:location.reload();"><h1>localhost | <?php echo getHostByName(getHostName()); ?></h1></a>
          </div>
        </div>

        <div class="row" id="time-wrapper">
          <div class="callout">
            <h3 id="datetime"><?php echo date('l, F j, H:i:s'); ?></h3>
          </div>
        </div>

        <div class="row" id="infobox-wrapper">
          <div class="row callout">
            <div class="col-6 col-s-6 text-right">
              <div>Public IP : <b><span id="public-ip">N/A</span></b></div>
              <div>LAN IP : <b><?php echo $local_addr; ?></b></div>
              <div>Host IP : <b><?php echo getHostByName(getHostName()); ?></b></div>
              <div>Remote IP : <b><?php echo $_SERVER['REMOTE_ADDR']; ?></b></div>
            </div>
            <div class="col-6 col-s-6 text-left">
              <div>Document Root : <b><?php echo $_SERVER['DOCUMENT_ROOT']; ?></b></div>
              <div>PHP : <a href="#" id="phpinfo"><b><?php echo phpversion(); ?></b></a></div>
              <div>INI : <b><?php echo php_ini_loaded_file(); ?></b></div>
              <div>Timezone : <b><?php echo date_default_timezone_get(); ?></b></div>
            </div>
          </div>
        </div>

        <div class="row" id="search-wrapper">
          <div class="callout">
            <div class="pad">
              <input type="text" id="search-query" maxlength="255" placeholder="Search" />
              <button class="btn search-button" data-uri="https://www.google.com/search?q=">Google</button>
            </div>
            <div class="pad">
              <button class="btn search-button" data-uri="https://duckduckgo.com/?q=">DuckDuckGo</button>
              <button class="btn search-button" data-uri="https://stackoverflow.com/search?q=">StackOverFlow</button>
              <button class="btn search-button" data-uri="https://github.com/search?q=">Github</button>
              <button class="btn search-button" data-uri="https://packagist.org/search/?q=">Packagist</button>
              <button class="btn search-button" data-uri="https://www.npmjs.com/search?q=">NPM</button>
            </div>
          </div>
        </div>

        <div class="row" id="bookmark-wrapper">
          <div class="callout text-left">
            <button id="bookmark-add">&#43;</button>&nbsp;
            <span class="right">&nbsp;<button id="bookmark-delete">&#215;</button></span>
            <span id="bookmark-list"></span>
          </div>
        </div>
        <div class="row hidden" id="bookmark-detail-wrapper">
          <div class="callout">
            <div class="row">
              <div class="col-3 col-s-3">
                <input class="wide" type="text" name="bookmark-name" id="bookmark-name" placeholder="Name">
              </div>
              <div class="col-7 col-s-7">
                <input class="wide" type="text" name="bookmark-url" id="bookmark-url" placeholder="URL">
              </div>
              <div class="col-2 col-s-2">
                <button class="btn" id="bookmark-save">Save</button>
              </div>
            </div>
          </div>
        </div>

        <div class="row" id="current-dir-wrapper">
          <div class="callout current-dir">
            <a href="#" id="current-dir"><div><strong><?php echo __DIR__; ?></strong></div></a>
          </div>
        </div>

        <div class="row hidden" id="dir-listing">
          <div class="row callout">
            <div class="col-6 col-s-6 text-left">
              <div>Directories</div><hr>
              <?php foreach ($directories as $folder) {
                echo '<b><a href="' . $current_dir_url . '/' . $folder . '">' . $folder . '</a></b><br/>';
              } ?>
            </div>
            <div class="col-6 col-s-6 text-right">
              <div>Files</div><hr>
              <?php foreach ($files as $file) {
                echo '<i><a href="' . $current_dir_url . '/' . $file . '">' . $file . '</a></i><br/>';
              } ?>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="callout text-left">
            <textarea class="code-box" id="code-box" name="code-box" rows="10" placeholder="Code..." autofocus></textarea><br>
            <select name="operation" id="operation">
              <option value="eval">PHP</option>
              <option value="jsonlint">JSON Lint</option>
              <option value="base64encode">Base64 Encode</option>
              <option value="base64decode">Base64 Decode</option>
              <option value="serialize">Serialize</option>
              <option value="unserialize">Unserialize</option>
            </select>
            <button class="btn" id="code-submit">Execute</button>
            <button class="btn" id="copy-code">Copy</button>
            <button class="btn hidden" id="copy-result">Copy Result</button>
            <em class="pad hidden" id="code-msg"></em>
            <div class="spinner right" id="spinner"></div>
          </div>
        </div>
        <div class="row hidden" id="code-result-wrapper">
          <div class="callout text-left" id="code-result"></div>
        </div>

        <div class="row">
          <div class="blurry text-right pad-right"><em><a href="https://github.com/sohelaman/pretty-index" target="_blank">Pretty Index</a></em></div>
        </div>
      </div><!-- main -->

      <div class="sidepane col-3 col-s-12 text-left">
        <div class="callout">
          <div class="pad">
            <div><strong>Todos</strong></div>
            <div class="spacer"></div>
            <textarea id="todo-box" placeholder="What to do?"></textarea>
            <div id="todo-list"></div>
          </div>
        </div>
      </div><!-- sidepane -->

    </div><!-- row -->

  </div><!-- content -->
  

</body>

<script type="text/javascript">
  class Pi {
    constructor() {
      this._prefix = '_pretty_index__';
      this.init();
    }

    init() {
      this.registerEvents();
      this.listBookmarks();
      this.listTodos();
      this.ipfy('https://api.ipify.org?format=json').then(response => {
        if (response) { document.getElementById('public-ip').innerHTML = JSON.parse(response).ip; }
      }, error => { console.log(error); });
      
    } // end of init()

    ipfy(url) {
      return new Promise(function(resolve, reject) {
        var request = new XMLHttpRequest();
        request.open('GET', url);
        request.onload = () => {
          if(request.status === 200) resolve( request.response );
          else reject(Error('Failed to load IP. Error code: ' + request.statusText));
        };
        request.onerror = () => { reject(Error('Request failed.')); };
        request.send();
      });
    } // end of ipfy()

    phpinfo() {
      let xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = () => {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
          let x = window.open('', '', 'location=no, toolbar=0');
          x.document.body.innerHTML = `${xhttp.responseText}`;
        }
      };
      xhttp.open("POST", window.location.href, true);
      xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhttp.send("phpinfo=1");
    } // end of phpinfo()

    codeSubmit() {
      let code = document.getElementById('code-box').value;
      code = typeof code === 'string' ? code.trim() : false;
      let op = document.getElementById('operation').value;
      if (!code || !op) { this.showMsg('Execute Order 66'); return; }
      this.loaderSpinner();
      let xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = () => {
        if (xhttp.readyState == 4) {
          let result = xhttp.status === 200 ? `${xhttp.responseText}` : '<em class="error">[Error ' + xhttp.status + ' - ' + xhttp.statusText + ']</em>';
          document.getElementById('code-result').innerHTML = result ? '<pre>' + result + '</pre>' : '<em class="warn">[No output]<em>';
          document.getElementById('code-result-wrapper').classList.remove('hidden');
          document.getElementById('copy-result').classList.remove('hidden');
          this.hideOtherBoxes();
          this.loaderSpinner(true);
        }
      };
      xhttp.open("POST", window.location.href, true);
      xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhttp.send('code-box=' + code + '&operation=' + op);
    } // end of codeSubmit()

    showMsg(msg) {
      let codeMsg = document.getElementById('code-msg');
      codeMsg.innerHTML = msg;
      codeMsg.classList.remove('hidden');
      setTimeout(() => { codeMsg.classList.add('hidden'); codeMsg.innerHTML = ''; }, 3000);
    } // end of showMsg()

    copyCode() {
      let codeBox = document.getElementById('code-box');
      if (!codeBox.value) { this.showMsg('Such empty!'); return; }
      codeBox.select();
      document.execCommand("copy");
      this.showMsg('Code copied');
    }

    copyResult() {
      let result = document.querySelector('#code-result pre');
      if (!result || !result.textContent) { this.showMsg('Such empty!'); return; }
      let str = result.textContent; // result.textContent or result.innerHTML
      let resultCopyListener = e => {
        e.clipboardData.setData("text/html", str);
        e.clipboardData.setData("text/plain", str);
        e.preventDefault();
      };
      document.addEventListener("copy", resultCopyListener);
      document.execCommand("copy");
      document.removeEventListener("copy", resultCopyListener);
      this.showMsg('Result copied');
    } // end of copyResult()

    hideOtherBoxes() {
      let boxes = ['infobox-wrapper', 'search-wrapper', 'current-dir-wrapper', 'time-wrapper', 'bookmark-wrapper', 'bookmark-detail-wrapper'];
      boxes.forEach(v => { document.getElementById(v).classList.add('hidden'); });
    }

    loaderSpinner(hide) {
      let disp = hide ? 'none' : 'inline';
      document.getElementById('spinner').style.display = disp;
    }

    getItems(type) {
      if (!localStorage) return false;
      let items = [], keyPrefix = type === 'todo' ? this._prefix + 'todo-' : this._prefix + 'bookmark-';
      let keys = Object.keys(localStorage);
      keys.forEach(v => {
        if (v.includes(keyPrefix)) items.push(JSON.parse(localStorage[v]));
      });
      return items;
    } // end of getItems();

    listBookmarks() {
      let bms = this.getItems('bookmark');
      let list = document.getElementById('bookmark-list');
      while (list.firstChild) {
        list.removeChild(list.firstChild);
      }
      bms.forEach(v => {
        let a = document.createElement('a');
        a.innerHTML = v.name;
        a.href = v.url;
        a.classList.add('bookmark');
        a.classList.add('pad-right');
        a.setAttribute('data-id', v.id);
        a.setAttribute('target', '_blank');
        a.addEventListener('click', e => {
          if (document.getElementById('bookmark-wrapper').classList.contains('exterminate')) {
            e.preventDefault();
            this.deleteBookmark(e.target.getAttribute('data-id'));
          }
        });
        list.appendChild(a);
      });
      if (!list.firstChild) list.innerHTML = '<em>Silence is golden.</em>';
    } // end of listBookmarks()

    addBookmark() {
      let index = localStorage.getItem(this._prefix + 'bookmark_index');
      index = index ? parseInt(index) : 0;
      index++;
      let name = document.getElementById('bookmark-name');
      let url = document.getElementById('bookmark-url');
      if (!name.value || !url.value || !name.value.trim() || !url.value.trim()) { alert('Such empty!'); return; }
      if (!this.isValidURL(url.value)) { alert('Invalid URL.'); return; }
      let key = this._prefix + 'bookmark-' + index;
      let bm = { id: index, name: name.value.trim(), url: url.value.trim() };
      localStorage.setItem(key, JSON.stringify(bm));
      localStorage.setItem(this._prefix + 'bookmark_index', index);
      name.value = null;
      url.value = null;
      document.getElementById('bookmark-detail-wrapper').classList.add('hidden');
      this.listBookmarks();
    } // end of addBookmark()

    deleteBookmark(index) {
      if (!confirm('Delete bookmark?')) return;
      let key = this._prefix + 'bookmark-' + index;
      localStorage.removeItem(key);
      this.listBookmarks();
    }

    isValidURL(str) {
      try {
        new URL(str);
        return true;
      } catch (_) {
        return false;  
      }
    }

    listTodos() {
      let todos = this.getItems('todo');
      let list = document.getElementById('todo-list');
      while (list.firstChild) {
        list.removeChild(list.firstChild);
      }
      if (todos.length > 0) list.appendChild(document.createElement('hr'));
      todos.forEach(v => {
        let del = document.createElement('a');
        del.innerHTML = '&#215;';
        del.href = '#';
        del.classList.add('delete-todo');
        del.setAttribute('data-id', v.id);
        del.setAttribute('title', 'Delete');
        del.addEventListener('click', e => {
          e.preventDefault();
          this.deleteTodo(e.target.getAttribute('data-id'));
        });
        list.appendChild(del);
        let elm = document.createElement('span');
        elm.innerHTML = v.body;
        elm.classList.add('todo');
        elm.classList.add('pad');
        elm.setAttribute('data-id', v.id);
        list.appendChild(elm);
        list.appendChild(document.createElement('hr'));
      });
      // if (!list.firstChild) list.innerHTML = '<em>Silence is golden.</em>';
    } // end of listTodos()

    addTodo() {
      let index = localStorage.getItem(this._prefix + 'todo_index');
      index = index ? parseInt(index) : 0;
      index++;
      let body = document.getElementById('todo-box');
      if (!body.value || !body.value.trim()) { alert('Such empty!'); return; }
      let key = this._prefix + 'todo-' + index;
      let todo = { id: index, body: body.value.trim() };
      localStorage.setItem(key, JSON.stringify(todo));
      localStorage.setItem(this._prefix + 'todo_index', index);
      body.value = null;
      this.listTodos();
    } // end of addTodo()

    deleteTodo(index) {
      if (!confirm('Delete todo?')) return;
      let key = this._prefix + 'todo-' + index;
      localStorage.removeItem(key);
      this.listTodos();
    } // end of deleteTodo()

    registerEvents() {
      document.getElementById('phpinfo').addEventListener('click', e => {
        e.preventDefault();
        this.phpinfo();
      });
      document.getElementById('search-query').addEventListener('keyup', e => {
        if (e.keyCode === 13) window.open('https://www.google.com/search?q=' + e.target.value, '_blank').focus();
      });
      // search buttons click
      document.querySelectorAll('.search-button').forEach((v) => {
        v.addEventListener('click', e => {
          let uri = e.target.getAttribute('data-uri'), query = document.getElementById('search-query').value;
          window.open(uri + query, '_blank').focus();
        });
      });
      document.getElementById('current-dir').addEventListener('click', e => {
        e.preventDefault();
        document.getElementById('dir-listing').classList.toggle('hidden');
      });
      document.getElementById('code-submit').addEventListener('click', e => {
        this.codeSubmit();
      });
      document.getElementById('code-box').addEventListener('keydown', e => {
        if (e.ctrlKey && e.keyCode === 13) this.codeSubmit();
      });
      document.getElementById('bookmark-add').addEventListener('click', e => {
        if (!localStorage) { alert('Your browser does not seem to support localStorage!'); return; }
        document.getElementById('bookmark-detail-wrapper').classList.toggle('hidden');
      });
      document.getElementById('bookmark-save').addEventListener('click', e => {
        if (!localStorage) { alert('Your browser does not seem to support localStorage!'); return; }
        this.addBookmark();
      });
      document.getElementById('bookmark-delete').addEventListener('click', e => {
        document.getElementById('bookmark-wrapper').classList.toggle('exterminate');
      });
      document.getElementById('copy-code').addEventListener('click', e => {
        this.copyCode();
      });
      document.getElementById('copy-result').addEventListener('click', e => {
        this.copyResult();
      });
      document.getElementById('todo-box').addEventListener('keydown', e => {
        if (e.ctrlKey && e.keyCode === 13) this.addTodo();
      });
    } // end of registerEvents()

  } // end of class Pi

  // let's have a pie
  let pi = new Pi();
</script>

</html>

<?php

class Pi {

  function __construct() {
    $this->_handleRequests();
  }

  private function _handleRequests() {
    if (!empty($_REQUEST['phpinfo']) && $_REQUEST['phpinfo'] == 1) {
      phpinfo();
      exit;
    } else if(!empty($_POST['code-box']) && isset($_POST['operation'])) {
      $code = trim($_POST['code-box']);
      $operation = $_POST['operation'];
      ob_start();
      switch ($operation) {
        case 'base64encode':
          echo base64_encode($code);
          break;
        case 'base64decode':
          echo base64_decode($code);
          break;
        case 'serialize':
          print serialize($code);
          break;
        case 'unserialize':
          print_r(unserialize($code));
          break;
        case 'jsonlint':
          $data = json_decode($code);
          $jout = json_last_error() === JSON_ERROR_NONE ? json_encode($data, JSON_PRETTY_PRINT) : '<em class="warn">[Invalid JSON]</em>';
          print $jout;
          break;
        default:
          eval($code);
          break;
      }
      $output = ob_get_contents();
      ob_end_clean();
      echo $output;
      exit;
    }
  } // end of _handleRequests()

  public function getLocalAddress() {
    if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
      exec("ipconfig /all", $output);
      foreach($output as $line) {
        if(preg_match("/(.*)IPv4 Address(.*)/", $line)) {
          if(preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $line, $match)) {
            if(filter_var($match[0], FILTER_VALIDATE_IP)) { return trim($match[0]); }
          }
        }
      } // endforeach
    } else if(strtoupper(PHP_OS) == 'LINUX') {
      $methods = array();
      $interfaces = array_diff(explode(PHP_EOL, `ls -1 /sys/class/net`), ['', 'lo', 'docker0', 'virbr0']);
      $methods[] = function() { return `hostname -I | awk '{print $1}'`; };
      foreach($interfaces as $interface) {
        $methods[] = function() { return exec("ip a $interface | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'"); };
        $methods[] = function() { return exec("ifconfig $interface | sed -En 's/127.0.0.1//;s/.*inet (addr:)?(([0-9]*\.){3}[0-9]*).*/\2/p'"); };
      }
      foreach($methods as $method) {
        $ip = trim($method());
        if(filter_var($ip, FILTER_VALIDATE_IP)) { return $ip; }
      } // endforeach
    }
    return "N/A";
  } // end of getLocalAddress()

  public function getDirContents($dir = __DIR__) {
    $directories = array();
    $files_list  = array();
    $files = scandir($dir);
    foreach($files as $file) {
       if(($file != '.') && ($file != '..')) {
          if (is_dir($dir . '/' . $file)) $directories[] = $file;
          else $files_list[] = $file;
       }
    }
    return array($directories, $files_list);
  } // end of getDirContents()

} // end of class Pi

?>
