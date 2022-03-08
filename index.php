<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Importazione file Excel</title>
<link rel="stylesheet" href="/my_reset.css">
<style>
body { margin: 4px; font: 16px sans-serif; }
th { font-size: 10px; }
td { border: 1px dotted black; font-size: 14px; }
tr:hover td { background: yellow; }
.ui-button-text, .ui-selectmenu-menu, .ui-selectmenu-text { font-size: 14px; }
.ui-selectmenu-button { vertical-align: middle; }
.CodeMirror { width: 800px; height: 200px !important; margin-bottom: 4px; border: 1px solid black; }
</style>
<script src="/jquery-ui/external/jquery/jquery.min.js"></script>
<script src="/jquery-ui/jquery-ui.min.js"></script>
<link rel="stylesheet" href="/jquery-ui/themes/redmond/jquery-ui.min.css">
<script src="/codemirror/lib/codemirror.js"></script>
<script src="/codemirror/mode/sql/sql.js"></script>
<script src="/codemirror/addon/edit/matchbrackets.js"></script>
<link rel="stylesheet" href="/codemirror/lib/codemirror.css">
<link rel="stylesheet" href="/codemirror/theme/material-ivan.css">
<script>
$(function(){
    $('select').selectmenu();
    $('button').button();
    $('#tabs').tabs();
    CodeMirror.fromTextArea(
        document.forms[0].query,
        { theme: 'material', matchBrackets: true, lineWrapping: true }
    );
});
</script>
</head>
<body>
<?php
const ALL=<<<SQL
SELECT num,mag,DATE_FORMAT(iscr,'%d-%m-%Y') AS iscriz,DATE_FORMAT(defin,'%d-%m-%Y') AS definiz,tipo_def,fonte,num_fonte,anno_fonte,art,dupl,sub
FROM proc JOIN reati ON proc.num=reati.proc
SQL;
class myDB extends PDO
{
    public function __construct()
    {
        try {
            parent::__construct('mysql:dbname=from_excel;charset=utf8','script','tpircs');
        } catch (PDOException $e) {
            die('<p class="err">Errore PDO: '.$e->getMessage()."</p>\n");
        }
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS,['myDbStat']);
    }
    public function myErr()
    {
        $inf=debug_backtrace();
        printf(
            "<pre class=\"err\">Errore PDO alla riga %d: %s\n\n",
            $inf[0]['line'],$this->errorInfo()[2]
        );
        //$x->debugDumpParams();
        echo "</pre>\n";
    }
}
class myDbStat extends PDOStatement
{
    public function bindValueOrNull($p,$value,$originalType)
    {
        if (empty($value)) $type=PDO::PARAM_NULL;
        else $type=$originalType;
        return parent::bindValue($p,$value,$type);
    }
    public function myErr()
    {
        $inf=debug_backtrace();
        printf(
            "<pre class=\"err\">Errore PDO alla riga %d: %s\n\n",
            $inf[0]['line'],$this->errorInfo()[2]
        );
        //$x->debugDumpParams();
        echo "</pre>\n";
    }
}
function toISO($x)
{
    if (preg_match('#^(\d{2})/(\d{2})/(\d{4})#',$x,$found)) {
        return $found[3].'-'.$found[2].'-'.$found[1];
    } else return null;
}
if (isset($_POST['goTo']) && is_dir($_POST['dataSource'])) $_SESSION['cd']=$_POST['dataSource'];
elseif (!isset($_SESSION['cd'])) $_SESSION['cd']=__DIR__;
$d=dir($_SESSION['cd']);
$files='';
while ($f=$d->read()) {
    if ($f=='.') continue;
    $full=realpath($_SESSION['cd'].DIRECTORY_SEPARATOR.$f);
    $files.=sprintf(
        '<option value="%s">%s</option>',
        $full,
        is_dir($full)? '&#x1F4C2; '.$f: $f
    );
}
$query = $_POST['query']?? ALL;
?>
<form action="index.php" method="post" enctype="multipart/form-data">
<p>
    File locale: <select name="dataSource"><?php echo $files; ?></select>
    <button name="goTo" type="submit" title="Vai alla directory selezionata">&#8631;</button>
    <button name="open" type="submit">Apri</button>
</p>
<p>
    Upload: <input name="dataSource" type="file">
    <button name="upload" type="submit">Carica</button>
</p>
<p>
    Query: <textarea name="query" style="vertical-align: top"><?php echo $query; ?></textarea>
</p>
<p><button name="search" type="submit">Cerca</button></p>
</form>
<?php
require_once 'autoload.php';
$db=new myDB();
if (isset($_POST['open'])) {
    $dataSource=$_POST['dataSource'];
} elseif (isset($_POST['import']) && $_FILES['dataSource']['error']==UPLOAD_ERR_OK) {
    $dataSource=$_FILES['dataSource']['tmp_name'];
} else $dataSource=false;
if ($dataSource) {
    $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($dataSource);
    $sheet->setActiveSheetIndexByName('Elenco');
    $buf=$sheet->getActiveSheet()->toArray(null, true, true, true);
    $r=$db->exec('DELETE FROM proc');
    if ($r===false) die($ins->myErr());
    echo <<<HTML
<div id="tabs">
<ul>
    <li><a href="#sub1">Procedimenti</a></li>
    <li><a href="#sub2">Reati</a></li>
</ul>
<table id="sub1">
HTML;
    $ins=$db->prepare('INSERT INTO proc VALUES(?,?,?,?,?,?)');
    foreach ($buf as $rowNo=>$row) {
        set_time_limit(10);
        if ($rowNo==1) $tag='th';
        else {
            $tag='td';
            $ins->bindValue( 1,$row['A'],PDO::PARAM_STR); // num
            $ins->bindValue( 2,$row['E'],PDO::PARAM_STR); // mag
            $ins->bindValue( 3,toISO($row['I']),PDO::PARAM_STR); // iscr
            $ins->bindValueOrNull( 4,toISO($row['K']),PDO::PARAM_STR); // defin
            $ins->bindValue( 5,$row['M'],PDO::PARAM_STR); // tipo_def
            $ins->bindValue( 6,$row['R'],PDO::PARAM_STR); //chiave
            $ins->execute() or die($ins->myErr());
        }
        echo "<tr><$tag>".implode("</$tag><$tag>",$row)."</$tag></tr>\n";
    }
    echo "</table>\n";
    $sheet->setActiveSheetIndexByName('Elenco Reati');
    $buf=$sheet->getActiveSheet()->toArray(null, true, true, true);
    file_put_contents('debug.log','count(): '.count($buf),FILE_APPEND);
    $ins=$db->prepare('INSERT INTO reati VALUES(?,?,?,?,?,?,?,?,?,?,?)');
    $r=$db->exec('DELETE FROM reati');
    if ($r===false) die($ins->myErr());
    echo "<table id=\"sub2\">\n";
    foreach ($buf as $rowNo=>$row) {
        set_time_limit(10);
        if ($rowNo==1) $tag='th';
        else {
            $tag='td';
            $ins->bindValue( 1,$row['A'],PDO::PARAM_STR); // proc
            $ins->bindValue( 2,$row['B'],PDO::PARAM_STR); // fonte
            $ins->bindValue( 3,$row['C'],PDO::PARAM_INT); // anno_fonte
            $ins->bindValue( 4,$row['D'],PDO::PARAM_INT); // num_fonte
            $ins->bindValue( 5,$row['E'],PDO::PARAM_INT); // art
            $ins->bindValue( 6,$row['F'],PDO::PARAM_STR); // dupl
            $ins->bindValue( 7,$row['G'],PDO::PARAM_STR); // sub
            $ins->bindValue( 8,$row['H'],PDO::PARAM_STR); // tipo
            $ins->bindValue( 9,$row['I'],PDO::PARAM_STR); // aggr
            $ins->bindValue(10,$row['J'],PDO::PARAM_INT); // iter
            $ins->bindValue(11,$row['K'],PDO::PARAM_STR); // chiave
            $ins->execute() or die($ins->myErr());
        }
        echo "<tr><$tag>".implode("</$tag><$tag>",$row)."</$tag></tr>\n";
    }
    echo "</table>\n</div>\n";
} elseif (isset($_POST['search'])) {
    $r=$db->query($query);
    if ($r===false) die($db->myErr());
    echo "<table>\n<tr>\n";
    $tot=$r->columnCount();
    for ($n=0; $n<$tot; $n++) {
        $inf=$r->getColumnMeta($n);
        echo "<th>{$inf['name']}</th>";
    }
    echo "</tr>\n";
    while ($row=$r->fetch(PDO::FETCH_NUM)) {
        echo "<tr><td>".implode("</td><td>",$row)."</td></tr>\n";
    }
    echo "</table>\n";
}
$db=null;
?>
</body>
</html>
