<html>
<body>
<h3>Test serialize object</h3>

<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 28;
$runid = 2;


include_once('./config.php');
// clear out mode runs from containers whose VPDES withdrawals have cjhanged substantially

// connect to Vpdes databas
// create a linkage to the deq2 database in order to use the plR stats package
$dbip2 = 'deq1.bse.vt.edu';
$dbname2 = 'vpdes';
$dbconn = pg_connect("host=$dbip2 port=5432 dbname=$dbname2 user=$dbuser password=$dbpass");
$vpdes = new pgsql_QueryObject;
$vpdes->dbconn = $dbconn;


$vpdes->querystring = "  select a.riverseg, sum(c.new1) as new1, sum(c.old1) as old1  ";
$vpdes->querystring .= " from sc_cbp53 as a, icprb_withdrawals_bak as b, ";
$vpdes->querystring .= "    ( ";
$vpdes->querystring .= "     select * from ( ";
$vpdes->querystring .= "        select b.wd_pt,  ";
$vpdes->querystring .= "           CASE WHEN a.totalwd is null then 0.0  ";
$vpdes->querystring .= "           ELSE a.totalwd  ";
$vpdes->querystring .= "           END as new1,  ";
$vpdes->querystring .= "           CASE WHEN b.totalwd is null then 0.0  ";
$vpdes->querystring .= "           ELSE b.totalwd  ";
$vpdes->querystring .= "           END as old1  ";
$vpdes->querystring .= "        from  ";
$vpdes->querystring .= "        ( select wd_pt, avg(divr + diva) as totalwd ";
$vpdes->querystring .= "          from icprb_wd_data_bak ";
$vpdes->querystring .= "          where (divr + diva) > 0 ";
$vpdes->querystring .= "          group by wd_pt ";
$vpdes->querystring .= "        ) as b left outer join  ";
$vpdes->querystring .= "        ( select wd_pt, avg(divr + diva) as totalwd ";
$vpdes->querystring .= "          from icprb_wd_data  ";
$vpdes->querystring .= "          where (divr + diva) > 0 ";
$vpdes->querystring .= "          group by wd_pt ";
$vpdes->querystring .= "        ) as a on (a.wd_pt = b.wd_pt)  ";
$vpdes->querystring .= "     ) as foo  ";
$vpdes->querystring .= "     where ( abs(old1 - new1)/old1) > 0.01  ";
$vpdes->querystring .= " ) as c  ";
$vpdes->querystring .= " where b.wd_pt = c.wd_pt  ";
$vpdes->querystring .= " and contains(transform(a.the_geom,4326), b.geom_dd) ";
$vpdes->querystring .= " group by a.riverseg  ";
$vpdes->querystring .= " order by a.riverseg  ";
$vpdes->performQuery();

#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/

$elrecs = $vpdes->queryrecords;
$debug = 0;
$i = 0;
$serializer = new XML_Serializer();
foreach ($elrecs as $thisrec) {
   $elname = $thisrec['riverseg'];
   $elid = getElementID($listobject, $scid, $elname);
   print("Clearing Run ID $runid from $elname ($elid) <br>\n");
   $listobject->querystring = "  delete from scen_model_run_elements where elementid in (";
   $listobject->querystring .= "    select elementid from scen_model_element ";
   $listobject->querystring .= "    where elemname ilike '$elname%' and scenarioid = $scid ";
   $listobject->querystring .= "    and objectclass = 'modelContainer' ";
   $listobject->querystring .= " ) ";
   $listobject->querystring .= " and runid = $runid ";
   print("$listobject->querystring \n");
   $listobject->performQuery();
   
   $i++;
   //break;
}

print("Finished.  Cleared $i items.<br>");

?>
</body>

</html>
