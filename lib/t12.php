<?php
use Utils\Database\OcDb;
?>
<div id='idGTC' ></div>

<script type="text/javascript">
    GCTLoad('ChartTable');
</script>

<?php
echo "<script type='text/javascript'>

    var gct = new GCT( 'idGTC' );

    gct.addColumn('number', '" . tr('ranking') . "', 'width:60px; text-align: left; ');
    gct.addColumn('number', '" . tr('caches') . "', 'width:60px; text-align: left;');
    gct.addColumn('string', '" . tr('user') . "', ' font-weight: bold; ' );

</script>";



require_once __DIR__.'/ClassPathDictionary.php';

$sRok = "";
$sMc = "";
$sCondition = "";
$nIsCondition = 0;

if (isset($_REQUEST['Rok']))
    $sRok = $_REQUEST['Rok'];

if (isset($_REQUEST['Mc']))
    $sMc = $_REQUEST['Mc'];



if ($sRok <> "" and $sMc <> "") {
    $sData_od = $sRok . '-' . $sMc . '-' . '01';

    $dDate = new DateTime($sData_od);
    $dDate->add(new DateInterval('P1M'));
    $nIsCondition = 1;
}

if ($sRok <> "" and $sMc == "") {
    $sData_od = $sRok . '-01-01';

    $dDate = new DateTime($sData_od);
    $dDate->add(new DateInterval('P1Y'));
    $nIsCondition = 1;
}


if ($nIsCondition) {
    $sData_do = $dDate->format('Y-m-d');
    $sCondition = "and date >='" . $sData_od . "' and date < '" . $sData_do . "'";
}

$dbc = OcDb::instance();

$query = "SELECT COUNT(*) count, u.username username, u.user_id user_id,
        u.date_created date_created, u.description description

        FROM
        cache_logs cl
        join caches c on c.cache_id = cl.cache_id
        join user u on cl.user_id = u.user_id

        WHERE cl.deleted=0 AND  cl.type=6 and c.user_id <> cl.user_id "
        . $sCondition .
        "GROUP BY u.user_id
        ORDER BY count DESC, u.username ASC";


$s = $dbc->multiVariableQuery($query);

echo "<script type='text/javascript'>";

$nRanking = 0;
$sOpis = "";
$nOldCount = -1;


while ($record = $dbc->dbResultFetch($s)) {
    if ($record["description"] <> "") {
        $sOpis = $record["description"];

        $sOpis = str_replace("\r\n", " ", $sOpis);
        $sOpis = str_replace("\n", " ", $sOpis);
        $sOpis = str_replace("'", "-", $sOpis);
        $sOpis = str_replace("\"", " ", $sOpis);
    } else
        $sOpis = "Niestety, brak opisu <img src=lib/tinymce4/plugins/emotions/img/smiley-surprised.gif />";


    $sProfil = "<b>Zarejestrowany od:</b> " . $record["date_created"]
            . " <br><b>Opis: </b> " . $sOpis;

    $nCount = $record["count"];
    $sUsername = '<a href="viewprofile.php?userid=' . $record["user_id"] . '" onmouseover="Tip(\\\'' . $sProfil . '\\\')" onmouseout="UnTip()"  >' . $record["username"] . '</a>';


    if ($nCount != $nOldCount) {
        $nRanking++;
        $nOldCount = $nCount;
    }

    echo "
    gct.addEmptyRow();
    gct.addToLastRow( 0, $nRanking );
    gct.addToLastRow( 1, $nCount );
    gct.addToLastRow( 2, '$sUsername' );
    ";
}

echo "gct.drawChart();";
echo "</script>";

