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
$buf=glob('*.xls');
$files='';
foreach ($buf as $f) {
    $files.="<option>$f</option>";
}
?>
<form action="index.php" method="post">
<p>File: <select name="file"><?php echo $files; ?></select></p>
<p>
    Importa:
    <button name="import" type="submit">Importa</button> 
</p>
<p>
    Query: <textarea name="query" style="vertical-align: top"><?php echo $_POST['query']?? 'select * from sqlite_master;'; ?></textarea>
    <button name="search" type="submit">Cerca</button>
</p>
</form>
<?php
function toISO($x)
{
    if (preg_match('#^(\d{2})/(\d{2})/(\d{4})#',$x,$found)) {
        return $found[3].'-'.$found[2].'-'.$found[1];
    } else return '';
}
require_once 'autoload.php';
try {
    $db=new PDO('sqlite:statistics.sqlite');
} catch (PDOException $e) {
    die('<p class="err">Errore PDO: '.$e->getMessage()."</p>\n");
}
if (isset($_POST['import'])) {
    $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($_POST['file']);
    $sheet->setActiveSheetIndexByName('Elenco');
    $buf=$sheet->getActiveSheet()->toArray(null, true, true, true);
    $ins=$db->prepare('INSERT INTO proc VALUES(?,?,?,?,?,?)');
    $r=$db->exec('DELETE FROM proc');
    if ($r===false) die('<p class="err">Errore PDO: '.$ins->errorInfo()[2]."</p>\n");
    echo <<<HTML
<div id="tabs">
<ul>
    <li><a href="#sub1">Procedimenti</a></li>
    <li><a href="#sub2">Reati</a></li>
</ul>
<table id="sub1">
HTML;
    foreach ($buf as $rowNo=>$row) {
        if ($rowNo==1) $tag='th';
        else {
            $tag='td';
            $ins->bindValue( 1,$row['A'],PDO::PARAM_STR);
            $ins->bindValue( 2,$row['E'],PDO::PARAM_STR);
            $ins->bindValue( 3,toISO($row['I']),PDO::PARAM_STR);
            $ins->bindValue( 4,toISO($row['K']),PDO::PARAM_STR);
            $ins->bindValue( 5,$row['M'],PDO::PARAM_STR);
            $ins->bindValue( 6,$row['R'],PDO::PARAM_STR);
            $ins->execute() or die('<p class="err">Errore PDO: '.$ins->errorInfo()[2]."</p>\n");
        }
        echo "<tr><$tag>".implode("</$tag><$tag>",$row)."</$tag></tr>\n";
    }
    echo "</table>\n";
    $sheet->setActiveSheetIndexByName('Elenco Reati');
    $buf=$sheet->getActiveSheet()->toArray(null, true, true, true);
    $ins=$db->prepare('INSERT INTO reati VALUES(?,?,?,?,?,?,?,?,?,?,?)');
    $r=$db->exec('DELETE FROM reati');
    if ($r===false) die('<p class="err">Errore PDO: '.$ins->errorInfo()[2]."</p>\n");
    echo "<table id=\"sub2\">\n";
    foreach ($buf as $rowNo=>$row) {
        if ($rowNo==1) $tag='th';
        else {
            $tag='td';
            $ins->bindValue( 1,$row['A'],PDO::PARAM_STR);
            $ins->bindValue( 2,$row['B'],PDO::PARAM_STR);
            $ins->bindValue( 3,$row['C'],PDO::PARAM_INT);
            $ins->bindValue( 4,$row['D'],PDO::PARAM_INT);
            $ins->bindValue( 5,$row['E'],PDO::PARAM_INT);
            $ins->bindValue( 6,$row['F'],PDO::PARAM_STR);
            $ins->bindValue( 7,$row['G'],PDO::PARAM_STR);
            $ins->bindValue( 8,$row['H'],PDO::PARAM_STR);
            $ins->bindValue( 9,$row['I'],PDO::PARAM_STR);
            $ins->bindValue(10,$row['J'],PDO::PARAM_INT);
            $ins->bindValue(11,$row['K'],PDO::PARAM_STR);
            $ins->execute() or die('<p class="err">Errore PDO: '.$ins->errorInfo()[2]."</p>\n");
        }
        echo "<tr><$tag>".implode("</$tag><$tag>",$row)."</$tag></tr>\n";
        //echo '<tr><td>'.(real)$row['A'].'</td></tr>';
    }
    echo "</table>\n</div>\n";
} elseif (isset($_POST['search'])) {
    $r=$db->query($_POST['query']);
    if ($r===false) die('<p class="err">Errore PDO: '.$db->errorInfo()[2]."</p>\n");
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