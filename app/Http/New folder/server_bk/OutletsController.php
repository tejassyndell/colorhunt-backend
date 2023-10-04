<?php

namespace App\Http\Controllers;

use App\Outlet;
use App\Transportoutlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OutletsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Outlet  $outlet
     * @return \Illuminate\Http\Response
     */
    public function show(Outlet $outlet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Outlet  $outlet
     * @return \Illuminate\Http\Response
     */
    public function edit(Outlet $outlet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Outlet  $outlet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Outlet $outlet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Outlet  $outlet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Outlet $outlet)
    {
        //
    }
	
	public function GetArticleofOutlet($PartyId){
		//$db = DB::select("select ArticleId, GROUP_CONCAT(CONCAT(TotalNoPacks) SEPARATOR ',') as TotalNoPacks from (Select ArticleId, sum(cast(NoPacks as int)) as TotalNoPacks from ( SELECT outward.ArticleId, n as id, numbers.n as dd, SUBSTRING_INDEX(SUBSTRING_INDEX(outward.NoPacks, ',', numbers.n), ',', -1) NoPacks FROM (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24 UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL SELECT 30 UNION ALL SELECT 31 UNION ALL SELECT 32 UNION ALL SELECT 33 UNION ALL SELECT 34 UNION ALL SELECT 35 UNION ALL SELECT 36 UNION ALL SELECT 37 UNION ALL SELECT 38 UNION ALL SELECT 39 UNION ALL SELECT 40) numbers INNER JOIN outward ON CHAR_LENGTH(outward.NoPacks) -CHAR_LENGTH(REPLACE(outward.NoPacks, ',', ''))>=numbers.n-1 ORDER BY id, n )t group by ArticleId, id order by ArticleId) as ff group by ff.ArticleId");
		//SELECT o.*, a.ArticleNumber, own.SoId, son.PartyId FROM `outward` o inner join article a on a.Id=o.ArticleId inner join outwardnumber own on own.OutwardNumber=o.OutwardNumberId inner join sonumber son on son.Id=own.SoId
		
		//select (CASE c.Colorflag WHEN '0' THEN (dd.Outward_NoPacks - dd.Outlet_NoPacks) ELSE GROUP_CONCAT(CONCAT((dd.Outward_NoPacks - dd.Outlet_NoPacks)) ORDER BY dd.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id FROM `outwardpacks` onp where onp.ArticleId=22 and onp.PartyId=5 group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id FROM `outletnopacks` onp where onp.ArticleId=22 and onp.PartyId=5 group by onp.ColorId) AS B ON A.ColorId=B.ColorId) as dd inner join po p on p.ArticleId=dd.ArticleId inner join category c on c.Id=p.CategoryId
		//$data = DB::select("SELECT o.ArticleId, a.ArticleNumber, own.SoId FROM `outward` o inner join article a on a.Id=o.ArticleId inner join outwardnumber own on own.OutwardNumber=o.OutwardNumberId inner join sonumber son on son.Id=own.SoId where son.PartyId='".$PartyId."' group by o.ArticleId");
		
	
		//$data = DB::select("SELECT o.ArticleId, a.ArticleNumber, own.SoId, son.PartyId, GetOutletSingleArticle(son.PartyId,o.ArticleId) as STOCKS, CountNoPacks(GetOutletSingleArticle(son.PartyId,o.ArticleId)) as COUNTVL FROM `outward` o inner join article a on a.Id=o.ArticleId inner join outwardnumber own on own.Id=o.OutwardNumberId inner join sonumber son on son.Id=own.SoId where son.PartyId='".$PartyId."' group by o.ArticleId having COUNTVL>0");
		//$data = DB::select("SELECT o.ArticleId, a.ArticleNumber, own.SoId, son.PartyId, GetOutletSingleArticle(topp.PartyId,o.ArticleId) as STOCKS, CountNoPacks(GetOutletSingleArticle(topp.PartyId,o.ArticleId)) as COUNTVL FROM `outward` o inner join article a on a.Id=o.ArticleId inner join outwardnumber own on own.Id=o.OutwardNumberId inner join sonumber son on son.Id=own.SoId left join transportoutwardpacks topp on topp.ArticleId=a.Id where topp.PartyId='".$PartyId."' group by o.ArticleId having COUNTVL>0");
		//$data = DB::select("select f.*,CountNoPacks(STOCKS) as COUNTVL from (SELECT o.ArticleId, a.ArticleNumber, own.SoId, son.PartyId, GetOutletSingleArticle(topp.PartyId,o.ArticleId) as STOCKS FROM `outward` o inner join article a on a.Id=o.ArticleId inner join outwardnumber own on own.Id=o.OutwardNumberId inner join sonumber son on son.Id=own.SoId left join transportoutwardpacks topp on topp.ArticleId=a.Id where topp.PartyId='".$PartyId."' group by o.ArticleId) as f having COUNTVL>0");
		$data = DB::select("select f.*,CountNoPacks(STOCKS) as COUNTVL from (SELECT GetOutletSingleArticle(topp.PartyId,a.Id) as STOCKS, a.Id as ArticleId, a.ArticleNumber FROM transportoutwardpacks topp left join `article` a on topp.ArticleId=a.Id where topp.PartyId='".$PartyId."' group by topp.ArticleId) as f having COUNTVL>0");
		return $data;
	}
	
	public function GetOutletSingleArticle($PartyId , $ArtId, $OutletPartyId){
		//select ar.ArticleNumber, ar.ArticleColor, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, ArticleId, GROUP_CONCAT(CONCAT(TotalNoPacks) SEPARATOR ',') as SalesNoPacks from (Select ArticleId, sum(cast(NoPacks as int)) as TotalNoPacks from (SELECT outward.ArticleId, n as id, SUBSTRING_INDEX(SUBSTRING_INDEX(outward.NoPacks, ',', numbers.n), ',', -1) NoPacks FROM (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 ) numbers INNER JOIN (SELECT o.ArticleId, o.NoPacks FROM `outward` o inner join article a on a.Id=o.ArticleId inner join outwardnumber own on own.OutwardNumber=o.OutwardNumberId inner join sonumber son on son.Id=own.SoId where son.PartyId='4' and a.Id='3') as outward ON CHAR_LENGTH(outward.NoPacks) -CHAR_LENGTH(REPLACE(outward.NoPacks, ',', ''))>=numbers.n-1 ORDER BY id, n)t group by ArticleId, id order by ArticleId) as ff inner join article ar on ar.Id=ff.ArticleId group by ff.ArticleId
		//$data = DB::select("select ar.ArticleNumber, ar.ArticleColor, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, ArticleId, GROUP_CONCAT(CONCAT(TotalNoPacks) SEPARATOR ',') as SalesNoPacks from (Select ArticleId, sum(cast(NoPacks as int)) as TotalNoPacks from (SELECT outward.ArticleId, n as id, SUBSTRING_INDEX(SUBSTRING_INDEX(outward.NoPacks, ',', numbers.n), ',', -1) NoPacks FROM (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 ) numbers INNER JOIN (SELECT o.ArticleId, o.NoPacks FROM `outward` o inner join article a on a.Id=o.ArticleId inner join outwardnumber own on own.OutwardNumber=o.OutwardNumberId inner join sonumber son on son.Id=own.SoId where son.PartyId='".$PartyId."' and a.Id='".$ArtId."') as outward ON CHAR_LENGTH(outward.NoPacks) -CHAR_LENGTH(REPLACE(outward.NoPacks, ',', ''))>=numbers.n-1 ORDER BY id, n)t group by ArticleId, id order by ArticleId) as ff inner join article ar on ar.Id=ff.ArticleId group by ff.ArticleId");
		//$data = DB::select("select ar.ArticleNumber, ar.ArticleColor, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, c.Colorflag, ff.ArticleId, GROUP_CONCAT(CONCAT(ff.TotalNoPacks) order by ff.id SEPARATOR ',') as SalesNoPacks from (Select id,ArticleId, sum(cast(NoPacks as int)) as TotalNoPacks from (SELECT outward.ArticleId, n as id, SUBSTRING_INDEX(SUBSTRING_INDEX(outward.NoPacks, ',', numbers.n), ',', -1) NoPacks FROM (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 ) numbers INNER JOIN (SELECT o.ArticleId, o.NoPacks FROM `outward` o inner join article a on a.Id=o.ArticleId inner join outwardnumber own on own.OutwardNumber=o.OutwardNumberId inner join sonumber son on son.Id=own.SoId where son.PartyId='".$PartyId."' and a.Id='".$ArtId."') as outward ON CHAR_LENGTH(outward.NoPacks) -CHAR_LENGTH(REPLACE(outward.NoPacks, ',', ''))>=numbers.n-1 ORDER BY id, n)t group by ArticleId, id order by ArticleId) as ff inner join article ar on ar.Id=ff.ArticleId inner join po p on p.ArticleId=ar.Id inner join category c on c.Id=p.CategoryId group by ff.ArticleId");
		//$data = DB::select("select ar.ArticleNumber, ar.ArticleColor, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, c.Colorflag, ddd.ArticleId, GROUP_CONCAT(case ar.ArticleOpenFlag WHEN 0 THEN CONCAT(ddd.Total) ELSE ddd.Total END ORDER BY ddd.Id SEPARATOR ',') as SalesNoPacks  from (SELECT sum(NoPacks) as Total, Id, ArticleId FROM `outwardpacks` where PartyId='".$PartyId."' and ArticleId='".$ArtId."' group by ColorId) as ddd inner join article ar on ar.Id=ddd.ArticleId inner join po p on p.ArticleId=ar.Id inner join category c on c.Id=p.CategoryId  group by ddd.ArticleId");
		//$data = DB::select("select ar.ArticleNumber, ar.ArticleColor, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, c.Colorflag, ddd.ArticleId, GROUP_CONCAT(case ar.ArticleOpenFlag WHEN 0 THEN CONCAT(ddd.Total) ELSE ddd.Total END ORDER BY ddd.Id SEPARATOR ',') as SalesNoPacks  from (select * from (SELECT sum(owp.NoPacks) as Total, owp.Id, owp.ArticleId, c.Colorflag FROM `outwardpacks` owp inner join po p on p.ArticleId=owp.ArticleId inner join category c on c.Id=p.CategoryId where owp.PartyId='".$PartyId."' and owp.ArticleId='".$ArtId."' group by owp.ColorId) as dff group by case dff.Colorflag when 0 then \"GROUP BY dff.Colorflag\" else dff.Id end) as ddd inner join article ar on ar.Id=ddd.ArticleId inner join po p on p.ArticleId=ar.Id inner join category c on c.Id=p.CategoryId  group by ddd.ArticleId");
		
		//$data = DB::select("select ar.ArticleNumber, ar.ArticleColor, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, c.Colorflag, ddd.ArticleId, ddd.SalesNoPacks from (select d.ArticleId, GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.Outlet_NoPacks)) ORDER BY d.Id SEPARATOR ',') as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id FROM `outwardpacks` onp where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id FROM `outletnopacks` onp where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS B ON A.ColorId=B.ColorId) as d group by d.ArticleId) as ddd inner join article ar on ar.Id=ddd.ArticleId inner join po p on p.ArticleId=ar.Id inner join category c on c.Id=p.CategoryId");
		//$data = DB::select("select ar.ArticleNumber, ar.ArticleColor, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, c.Colorflag, ddd.ArticleId, ddd.SalesNoPacks from (select d.ArticleId, (CASE d.ArticleOpenFlag WHEN '0' THEN (d.Outward_NoPacks - d.Outlet_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.Outlet_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.ArticleOpenFlag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, a.ArticleOpenFlag FROM `outwardpacks` onp inner join article a on a.Id = onp.ArticleId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, a.ArticleOpenFlag  FROM `outletnopacks` onp inner join article a on a.Id = onp.ArticleId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS B ON A.ColorId=B.ColorId) as d group by d.ArticleId) as ddd inner join article ar on ar.Id=ddd.ArticleId inner join po p on p.ArticleId=ar.Id inner join category c on c.Id=p.CategoryId");
		
		
		//$data = DB::select("select ar.ArticleNumber, ar.ArticleColor, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, art.ArticleRate, c.Colorflag, ddd.ArticleId, ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.Outlet_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.Outlet_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `outwardpacks` onp inner join po p on p.ArticleId = onp.ArticleId inner join category c on c.Id=p.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp inner join po p on p.ArticleId = onp.ArticleId inner join category c on c.Id=p.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS B ON A.ColorId=B.ColorId) as d group by d.ArticleId) as ddd inner join article ar on ar.Id=ddd.ArticleId inner join po p on p.ArticleId=ar.Id inner join category c on c.Id=p.CategoryId inner join articlerate art on art.ArticleId=ar.Id");
		
		//$data = DB::select("select ar.ArticleNumber, ar.ArticleColor, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, art.ArticleRate, c.Colorflag, ddd.ArticleId, ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.Outlet_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.Outlet_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `transportoutwardpacks` onp inner join po p on p.ArticleId = onp.ArticleId inner join category c on c.Id=p.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp inner join po p on p.ArticleId = onp.ArticleId inner join category c on c.Id=p.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS B ON A.ColorId=B.ColorId) as d group by d.ArticleId) as ddd inner join article ar on ar.Id=ddd.ArticleId inner join po p on p.ArticleId=ar.Id inner join category c on c.Id=p.CategoryId inner join articlerate art on art.ArticleId=ar.Id");
		//$data = DB::select("select ar.ArticleNumber, ar.ArticleColor, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, art.ArticleRate, c.Colorflag, ddd.ArticleId, ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `transportoutwardpacks` onp inner join po p on p.ArticleId = onp.ArticleId inner join category c on c.Id=p.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp inner join po p on p.ArticleId = onp.ArticleId inner join category c on c.Id=p.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS B ON A.ColorId=B.ColorId LEFT JOIN ( SELECT sum(srp.NoPacks) as SalesReturn_NoPacks, srp.ColorId, srp.ArticleId, srp.Id, c.Colorflag  FROM `salesreturnpacks` srp inner join po p on p.ArticleId = srp.ArticleId inner join category c on c.Id=p.CategoryId where srp.ArticleId='".$ArtId."' and srp.PartyId='".$PartyId."' group by srp.ColorId) AS C ON A.ColorId=C.ColorId) as d group by d.ArticleId) as ddd inner join article ar on ar.Id=ddd.ArticleId inner join po p on p.ArticleId=ar.Id inner join category c on c.Id=p.CategoryId inner join articlerate art on art.ArticleId=ar.Id");
		
		
		//$data = DB::select("select ar.ArticleNumber, ar.ArticleColor, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, art.ArticleRate, c.Colorflag, ddd.ArticleId, ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `transportoutwardpacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS B ON A.ColorId=B.ColorId LEFT JOIN ( SELECT sum(srp.NoPacks) as SalesReturn_NoPacks, srp.ColorId, srp.ArticleId, srp.Id, c.Colorflag  FROM `salesreturnpacks` srp left join po p on p.ArticleId = srp.ArticleId inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='".$ArtId."' and srp.PartyId='".$PartyId."' group by srp.ColorId) AS C ON A.ColorId=C.ColorId) as d group by d.ArticleId) as ddd inner join article ar on ar.Id=ddd.ArticleId left join po p on p.ArticleId=ar.Id inner join category c on c.Id=ar.CategoryId inner join articlerate art on art.ArticleId=ar.Id");
		//$data = DB::select("select ar.ArticleNumber, CASE WHEN (oi.ArticleColor IS NULL) THEN ar.ArticleColor ELSE oi.ArticleColor END AS `ArticleColor`, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, art.ArticleRate, c.Colorflag, ddd.ArticleId, ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `transportoutwardpacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS B ON A.ColorId=B.ColorId LEFT JOIN ( SELECT sum(srp.NoPacks) as SalesReturn_NoPacks, srp.ColorId, srp.ArticleId, srp.Id, c.Colorflag  FROM `salesreturnpacks` srp left join po p on p.ArticleId = srp.ArticleId inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='".$ArtId."' and srp.PartyId='".$PartyId."' group by srp.ColorId) AS C ON A.ColorId=C.ColorId) as d group by d.ArticleId) as ddd inner join article ar on ar.Id=ddd.ArticleId left join po p on p.ArticleId=ar.Id inner join category c on c.Id=ar.CategoryId inner join articlerate art on art.ArticleId=ar.Id left join outletimport oi on oi.ArticleId='".$ArtId."' and oi.PartyId = '".$PartyId."'");
		//$data = DB::select("select ar.ArticleNumber, CASE WHEN (oi.ArticleColor IS NULL) THEN ar.ArticleColor ELSE oi.ArticleColor END AS `ArticleColor`, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, art.OutletArticleRate as ArticleRate, c.Colorflag, ddd.ArticleId, ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `transportoutwardpacks` onp inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS B ON A.ColorId=B.ColorId LEFT JOIN ( SELECT sum(srp.NoPacks) as SalesReturn_NoPacks, srp.ColorId, srp.ArticleId, srp.Id, c.Colorflag  FROM `salesreturnpacks` srp inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='".$ArtId."' and srp.PartyId='".$PartyId."' group by srp.ColorId) AS C ON A.ColorId=C.ColorId) as d group by d.ArticleId) as ddd inner join article ar on ar.Id=ddd.ArticleId inner join category c on c.Id=ar.CategoryId inner join articlerate art on art.ArticleId=ar.Id left join outletimport oi on oi.ArticleId='".$ArtId."' and oi.PartyId = '".$PartyId."'");
		//$data = DB::select("select ar.ArticleNumber, CASE WHEN (oi.ArticleColor IS NULL) THEN ar.ArticleColor ELSE oi.ArticleColor END AS `ArticleColor`, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, art.ArticleRate, p.OutletArticleRate as OutletArticleRate, c.Colorflag, ddd.ArticleId, ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `transportoutwardpacks` onp inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS B ON A.ColorId=B.ColorId LEFT JOIN ( SELECT sum(srp.NoPacks) as SalesReturn_NoPacks, srp.ColorId, srp.ArticleId, srp.Id, c.Colorflag  FROM `salesreturnpacks` srp inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='".$ArtId."' and srp.PartyId='".$PartyId."' group by srp.ColorId) AS C ON A.ColorId=C.ColorId) as d group by d.ArticleId) as ddd inner join article ar on ar.Id=ddd.ArticleId inner join category c on c.Id=ar.CategoryId inner join articlerate art on art.ArticleId=ar.Id left join party p on p.Id='".$PartyId."' left join outletimport oi on oi.ArticleId='".$ArtId."' and oi.PartyId = '".$PartyId."'");
		
		
		//$data = DB::select("select ar.ArticleNumber, CASE WHEN (oi.ArticleColor IS NULL) THEN ar.ArticleColor ELSE oi.ArticleColor END AS `ArticleColor`, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, art.ArticleRate, p.OutletArticleRate as OutletArticleRate, c.Colorflag, ddd.ArticleId, ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `transportoutwardpacks` onp inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS B ON A.ColorId=B.ColorId LEFT JOIN ( SELECT sum(srp.NoPacks) as SalesReturn_NoPacks, srp.ColorId, srp.ArticleId, srp.Id, c.Colorflag  FROM `salesreturnpacks` srp inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='".$ArtId."' and srp.PartyId='".$PartyId."' group by srp.ColorId) AS C ON A.ColorId=C.ColorId) as d group by d.ArticleId) as ddd inner join article ar on ar.Id=ddd.ArticleId inner join category c on c.Id=ar.CategoryId inner join articlerate art on art.ArticleId=ar.Id left join party p on p.Id='".$PartyId."' left join outletimport oi on oi.ArticleId='".$ArtId."' and oi.PartyId = '".$PartyId."'");
		//$data = DB::select("select ar.ArticleNumber, CASE WHEN (oi.ArticleColor IS NULL) THEN ar.ArticleColor ELSE oi.ArticleColor END AS `ArticleColor`, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, art.ArticleRate, p.OutletArticleRate as OutletArticleRate, c.Colorflag, ddd.ArticleId, ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks + d.OutletSalesReturn_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks + d.OutletSalesReturn_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN D.OutletSalesReturn_NoPacks IS NULL THEN '0' ELSE D.OutletSalesReturn_NoPacks END) as OutletSalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `transportoutwardpacks` onp inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS B ON A.ColorId=B.ColorId LEFT JOIN ( SELECT sum(srp.NoPacks) as SalesReturn_NoPacks, srp.ColorId, srp.ArticleId, srp.Id, c.Colorflag  FROM `salesreturnpacks` srp inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='".$ArtId."' and srp.PartyId='".$PartyId."' group by srp.ColorId) AS C ON A.ColorId=C.ColorId LEFT JOIN ( SELECT sum(srp.NoPacks) as OutletSalesReturn_NoPacks, srp.ColorId, srp.ArticleId, srp.Id, c.Colorflag  FROM `outletsalesreturnpacks` srp inner join outletsalesreturn osr on osr.OutletId=srp.OutletId inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='".$ArtId."' and srp.PartyId='".$OutletPartyId."' and osr.OutletPartyId='".$PartyId."'  group by srp.ColorId) AS D ON A.ColorId=D.ColorId) as d group by d.ArticleId) as ddd inner join article ar on ar.Id=ddd.ArticleId inner join category c on c.Id=ar.CategoryId inner join articlerate art on art.ArticleId=ar.Id left join party p on p.Id='".$PartyId."' left join outletimport oi on oi.ArticleId='".$ArtId."' and oi.PartyId = '".$PartyId."'");
		//$data = DB::select("select ar.ArticleNumber, CASE WHEN (oi.ArticleColor IS NULL) THEN ar.ArticleColor ELSE oi.ArticleColor END AS `ArticleColor`, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, art.ArticleRate, p.OutletArticleRate as OutletArticleRate, c.Colorflag, ddd.ArticleId, ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks + d.OutletSalesReturn_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks + d.OutletSalesReturn_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN D.OutletSalesReturn_NoPacks IS NULL THEN '0' ELSE D.OutletSalesReturn_NoPacks END) as OutletSalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `transportoutwardpacks` onp inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS B ON A.ColorId=B.ColorId LEFT JOIN ( SELECT sum(srp.NoPacks) as SalesReturn_NoPacks, srp.ColorId, srp.ArticleId, srp.Id, c.Colorflag  FROM `salesreturnpacks` srp inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='".$ArtId."' and srp.PartyId='".$PartyId."' group by srp.ColorId) AS C ON A.ColorId=C.ColorId LEFT JOIN ( select sum(f.NoPacks) as OutletSalesReturn_NoPacks, f.ColorId, f.ArticleId, f.OutletId, f.Id, f.Colorflag from (SELECT srp.NoPacks, srp.ColorId, srp.ArticleId, srp.OutletId, srp.Id, c.Colorflag FROM `outletsalesreturnpacks` srp inner join outletsalesreturn osr on osr.OutletId=srp.OutletId inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='".$ArtId."' and srp.PartyId='".$OutletPartyId."' and osr.OutletPartyId='".$PartyId."' group by srp.Id) as f group by f.ColorId) AS D ON A.ColorId=D.ColorId) as d group by d.ArticleId) as ddd inner join article ar on ar.Id=ddd.ArticleId inner join category c on c.Id=ar.CategoryId inner join articlerate art on art.ArticleId=ar.Id left join party p on p.Id='".$PartyId."' left join outletimport oi on oi.ArticleId='".$ArtId."' and oi.PartyId = '".$PartyId."'");
		$data = DB::select("select ar.ArticleNumber, CASE WHEN (oi.ArticleColor IS NULL) THEN ar.ArticleColor ELSE oi.ArticleColor END AS `ArticleColor`, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, art.ArticleRate, p.OutletArticleRate as OutletArticleRate, c.Colorflag, ddd.ArticleId, ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks + d.OutletSalesReturn_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks + d.OutletSalesReturn_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN D.OutletSalesReturn_NoPacks IS NULL THEN '0' ELSE D.OutletSalesReturn_NoPacks END) as OutletSalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `transportoutwardpacks` onp inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArtId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS B ON A.ColorId=B.ColorId LEFT JOIN ( SELECT sum(srp.NoPacks) as SalesReturn_NoPacks, srp.ColorId, srp.ArticleId, srp.Id, c.Colorflag  FROM `salesreturnpacks` srp inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='".$ArtId."' and srp.PartyId='".$PartyId."' group by srp.ColorId) AS C ON A.ColorId=C.ColorId LEFT JOIN ( select sum(f.NoPacks) as OutletSalesReturn_NoPacks, f.ColorId, f.ArticleId, f.OutletId, f.Id, f.Colorflag from (SELECT srp.NoPacks, srp.ColorId, srp.ArticleId, srp.OutletId, srp.Id, c.Colorflag FROM `outletsalesreturnpacks` srp inner join outletsalesreturn osr on osr.OutletId=srp.OutletId inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='".$ArtId."' and osr.OutletPartyId='".$PartyId."' group by srp.Id) as f group by f.ColorId) AS D ON A.ColorId=D.ColorId) as d group by d.ArticleId) as ddd inner join article ar on ar.Id=ddd.ArticleId inner join category c on c.Id=ar.CategoryId inner join articlerate art on art.ArticleId=ar.Id left join party p on p.Id='".$PartyId."' left join outletimport oi on oi.ArticleId='".$ArtId."' and oi.PartyId = '".$PartyId."'");
		//$partydata = DB::select('SELECT OutletArticleRate  FROM `party` WHERE `Id` = '.$OutletPartyId);
		foreach($data as $key => $val){
			$object = (object)$val;
			if($object->OutletArticleRate!="0"){
			//if($partydata[0]->OutletArticleRate!="0"){
				//$newdata = $object->ArticleRate + $partydata[0]->OutletArticleRate;
				$newdata = $object->ArticleRate + $object->OutletArticleRate;
				$object->ArticleRate = $newdata;
			}
			
		}
		return $data;
	}
	
	public function GenerateOutletNumber($UserId)
    {
		$array = array();
		$fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
        $outletnumberdata = DB::select('SELECT Id, FinancialYearId, OutletNumber From outletnumber order by Id desc limit 0,1');
		
		if(count($outletnumberdata)>0){
			if($fin_yr[0]->Id > $outletnumberdata[0]->FinancialYearId){
				$array["Outlet_Number"] = 1;
				$array["Outlet_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["Outlet_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			} else{
				$array["Outlet_Number"] = ($outletnumberdata[0]->OutletNumber) + 1;
				$array["Outlet_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["Outlet_Number_Financial"] = ($outletnumberdata[0]->OutletNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			}
		}
        else{
			$array["Outlet_Number"] = 1;
			$array["Outlet_Number_Financial_Id"] = $fin_yr[0]->Id;
			$array["Outlet_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
			return $array;
        }
    }
	
	public function AddOutlet(Request $request)
    {
		$data = $request->all();
		//echo "<pre>"; print_r($data); exit;
		$soadd = array();
		$getdata  = $this->GetOutletSingleArticle($data['PartyId'], $data['ArticleId'], $data['OutletPartyId']);
		//$artratedata = DB::select("select * from articlerate where ArticleId='".$data['ArticleId']."'");
		$ArticleRate = $data['ArticleRate'];
		
		DB::beginTransaction();
		try {
			if($data['Discount']==""){
				$Discount = 0;
			}else{
				$Discount = $data['Discount'];
			}
			
			if($data['OutletNumberId']=="Add"){
				$generate_OutletNumber = $this->GenerateOutletNumber($data['UserId']);
				$Outlet_Number = $generate_OutletNumber['Outlet_Number'];
				$Outlet_Number_Financial_Id = $generate_OutletNumber['Outlet_Number_Financial_Id'];
				//$generate_OutletNumber['Outlet_Number'];
				//$generate_OutletNumber['Outlet_Number_Financial'];
				//$generate_OutletNumber['Outlet_Number_Financial_Id'];
				
				$OutletNumberId = DB::table('outletnumber')->insertGetId(
					['OutletNumber' =>  $Outlet_Number,"FinancialYearId"=>$Outlet_Number_Financial_Id,'UserId'=>$data['UserId'], 'OutletPartyId' => $data['OutletPartyId'] ,'PartyId' =>  $data['PartyId'], 'OutletDate'=>$data['Date'], 'GSTAmount'=>$data['GSTAmount'], 'GSTPercentage'=>$data['GSTPercentage'], 'Discount'=>$Discount, 'Address'=>$data['Address'], 'Contact'=>$data['Contact'], 'CreatedDate' => date('Y-m-d H:i:s')]
				);
				
			}else{
				$checkoutlet_number = DB::select("SELECT OutletNumber FROM `outletnumber` where Id ='".$data['OutletNumberId']."'");
				//echo "SELECT OutletNumber FROM `outletnumber` where Id ='".$data['OutletNumberId']."'"; exit;
				if(!empty($checkoutlet_number)){
					$OutletNumber = $checkoutlet_number[0]->OutletNumber;
					$OutletNumberId = $data['OutletNumberId'];
					
					DB::table('outletnumber')
					->where('Id', $OutletNumberId)
					->update(['UserId'=>$data['UserId'],'PartyId' =>  $data['PartyId'], 'OutletPartyId' => $data['OutletPartyId'], 'OutletDate'=>$data['Date'], 'GSTAmount'=>$data['GSTAmount'], 'GSTPercentage'=>$data['GSTPercentage'], 'Discount'=>$Discount, 'Address'=>$data['Address'], 'Contact'=>$data['Contact']]);
				}
			}
				
				
			//echo "<pre>"; print_r($getdata); print_r($data);
			if($data["ArticleOpenFlag"]==1){
				$NoPacks = "";
				if(isset($data['NoPacksNew'])){
					$NoPacks .= $data['NoPacksNew'];
					if($data['NoPacks']<$data['NoPacksNew']){
						return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
					}
				}else{
					return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
				}
				
				$sonumberdata = DB::select('SELECT count(*) as total, (case when NoPacks IS NULL then "0" ELSE NoPacks End) as NoPacks FROM `outlet` where OutletNumberId="'.$OutletNumberId.'" and ArticleId="'.$data['ArticleId'].'"');
				
				if($sonumberdata[0]->total>0){
					$getnppacks = $sonumberdata[0]->NoPacks;
					$nopacksadded = $getnppacks + $NoPacks;
					
					DB::table('outlet')
						->where('OutletNumberId', $OutletNumberId)
						->where('ArticleId', $data['ArticleId'])
						->update(['NoPacks'=>$nopacksadded]);
						
					if($data['ArticleOpenFlag']==0){
						if( strpos($nopacksadded, ',') !== false ) {
							$nopacksadded = explode(',', $nopacksadded);
							foreach($data['ArticleSelectedColor'] as $key => $vl){
								$numberofpacks = $vl["Id"];
								$res = DB::select("select Id from outletnopacks where ColorId='".$numberofpacks."' and OutletId='".$OutletNumberId."' and ArticleId='".$data['ArticleId']."'");
								DB::table('outletnopacks')
								->where('Id', $res[0]->Id)
								->update(['NoPacks'=>$nopacksadded[$key], 'UpdatedDate'=>date('Y-m-d H:i:s')]);
							}
						}else{
							foreach($data['ArticleSelectedColor'] as $key => $vl){
								$numberofpacks = $vl["Id"];
								$res = DB::select("select Id from outletnopacks where ColorId='".$numberofpacks."' and OutletId='".$OutletNumberId."' and ArticleId='".$data['ArticleId']."'");
								DB::table('outletnopacks')
								->where('Id', $res[0]->Id)
								->update(['NoPacks'=>$nopacksadded, 'UpdatedDate'=>date('Y-m-d H:i:s')]);
							}
						}
					}else{
						$datavl = DB::select('select Id from outlet where OutletNumberId = "'.$OutletNumberId.'" and ArticleId = "'.$data['ArticleId'].'"');
						
						DB::table('outletnopacks')
						->where('Id', $datavl[0]->Id)
						->update(['NoPacks'=>$nopacksadded, 'UpdatedDate'=>date('Y-m-d H:i:s')]);
					}
					
				}else{
					//echo "<pre>"; print_r($data); echo $OutletNumberId; echo $NoPacks; exit;
				  
				   $soadd1["OutletNumberId"] = $OutletNumberId;
				   $soadd1["ArticleId"] = $data['ArticleId'];
				   $soadd1["NoPacks"] = $NoPacks;
				   $soadd1["ArticleRate"] = $ArticleRate;
				   $field = Outlet::create($soadd1);
				   
				   $outlet_insertedid =$field->id;
				   
					if($data['ArticleOpenFlag']==0){
						if( strpos($NoPacks, ',') !== false ) {
							$NoPacks = explode(',', $NoPacks);
							foreach($data['ArticleSelectedColor'] as $key => $vl){
								$numberofpacks = $vl["Id"];
								DB::table('outletnopacks')->insertGetId(
									['ArticleId' =>  $data['ArticleId'], 'ColorId'=>$numberofpacks, 'OutletId'=> $outlet_insertedid,'NoPacks'=>$NoPacks[$key], 'PartyId' =>$data["PartyId"], 'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
								);
							}
						}else{
							foreach($data['ArticleSelectedColor'] as $key => $vl){
								$numberofpacks = $vl["Id"];
								DB::table('outletnopacks')->insertGetId(
									['ArticleId' =>  $data['ArticleId'], 'ColorId'=>$numberofpacks, 'OutletId'=> $outlet_insertedid,'NoPacks'=>$NoPacks, 'PartyId' =>$data["PartyId"],'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
								);
							}
						}
					}else{
						$datavl = DB::select('select Id from outlet where OutletNumberId = "'.$OutletNumberId.'" and ArticleId = "'.$data['ArticleId'].'"');
						
						DB::table('outletnopacks')->insertGetId(
							['ArticleId' =>  $data['ArticleId'], 'ColorId'=>0, 'OutletId'=> $outlet_insertedid,'NoPacks'=>$NoPacks, 'PartyId' =>$data["PartyId"],'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
						);
					}
				}
			}else{	
				$search = $getdata[0]->SalesNoPacks;
				$searchString = ',';
				if( strpos($search, $searchString) !== false ) {
					$string = explode(',', $search);
					$stringcomma = 1;
				}else{
					$search;
					$stringcomma = 0;
				}
				
				$NoPacks = "";
				if($data['Colorflag']==1){
					foreach($data['ArticleSelectedColor'] as $key => $vl){
						$numberofpacks = $vl["Id"];
						if($data["NoPacksNew_".$numberofpacks]!=""){
							//echo "Inner";
							if($stringcomma==1){
								if($string[$key]<$data["NoPacksNew_".$numberofpacks]){
									return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
								}
							}else{
								if($search<$data["NoPacksNew_".$numberofpacks]){
									return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
								}
							}
							$NoPacks .= $data["NoPacksNew_".$numberofpacks].",";
						}
						else{
							//echo "Blank";
							$NoPacks .= "0,";
						}
					}
				} else{
					if(isset($data['NoPacksNew'])){
						$NoPacks .= $data['NoPacksNew'];
						if($search<$data['NoPacksNew']){
							return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
						}
					}else{
						return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
					}
					
				}
				//echo "asd111"; exit;
				$NoPacks = rtrim($NoPacks,',');
				$CheckSalesNoPacks = explode(',', $NoPacks);
				
				$tmp = array_filter($CheckSalesNoPacks);
				if (empty($tmp)) {
					//echo "All zeros!";
					return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
				}
				
				
				$sonumberdata = DB::select('SELECT count(*) as total, NoPacks  FROM `outlet` where OutletNumberId="'.$OutletNumberId.'" and ArticleId="'.$data['ArticleId'].'"');
				$getnppacks = $sonumberdata[0]->NoPacks;
					
				if($sonumberdata[0]->total>0){
					$nopacksadded = "";
					if( strpos($NoPacks, ',') !== false ) {
						$NoPacks1 = explode(',', $NoPacks);
						$getnppacks = explode(',', $getnppacks);
						//echo "<pre>"; print_r($NoPacks1);
						foreach($getnppacks as $key => $vl){
							$nopacksadded .= $NoPacks1[$key] + $vl.",";
						}
					}else{
						$nopacksadded .= $getnppacks + $NoPacks.",";
					}
					$nopacksadded = rtrim($nopacksadded,',');
					
					DB::table('outlet')
						->where('OutletNumberId', $OutletNumberId)
						->where('ArticleId', $data['ArticleId'])
						->update(['NoPacks'=>$nopacksadded]);
						
					$IdData = DB::select("select Id from outlet where OutletNumberId='".$OutletNumberId."' and ArticleId='".$data['ArticleId']."'");
					//echo "<pre>"; print_r($IdData); exit;
					
					if($data['ArticleOpenFlag']==0){
						if( strpos($nopacksadded, ',') !== false ) {
							$nopacksadded = explode(',', $nopacksadded);
							foreach($data['ArticleSelectedColor'] as $key => $vl){
								$numberofpacks = $vl["Id"];
								$res = DB::select("select Id from outletnopacks where ColorId='".$numberofpacks."' and OutletId='".$IdData[0]->Id."' and ArticleId='".$data['ArticleId']."'");
								DB::table('outletnopacks')
								->where('Id', $res[0]->Id)
								->update(['NoPacks'=>$nopacksadded[$key], 'UpdatedDate'=>date('Y-m-d H:i:s')]);
							}
						}else{
							foreach($data['ArticleSelectedColor'] as $key => $vl){
								$numberofpacks = $vl["Id"];
								$res = DB::select("select Id from outletnopacks where ColorId='".$numberofpacks."' and OutletId='".$IdData[0]->Id."' and ArticleId='".$data['ArticleId']."'");
								DB::table('outletnopacks')
								->where('Id', $res[0]->Id)
								->update(['NoPacks'=>$nopacksadded, 'UpdatedDate'=>date('Y-m-d H:i:s')]);
							}
						}
					}else{
						$datavl = DB::select('select Id from outlet where OutletNumberId = "'.$OutletNumberId.'" and ArticleId = "'.$data['ArticleId'].'"');
						DB::table('outletnopacks')
						->where('OutletId', $datavl[0]->Id)
						->where('ArticleId', $data['ArticleId'])
						->update(['NoPacks'=>$nopacksadded, 'UpdatedDate'=>date('Y-m-d H:i:s')]);
					}
					
				}else{
				   $soadd['OutletNumberId'] = $OutletNumberId;
				   $soadd["ArticleId"] = $data['ArticleId'];
				   $soadd["NoPacks"] = $NoPacks;
				   $soadd["ArticleRate"] = $ArticleRate;
				   $field = Outlet::create($soadd);
				   $outlet_insertedid =$field->id;
				   
					if($data['ArticleOpenFlag']==0){
						if( strpos($NoPacks, ',') !== false ) {
							$NoPacks = explode(',', $NoPacks);
							foreach($data['ArticleSelectedColor'] as $key => $vl){
								$numberofpacks = $vl["Id"];
								DB::table('outletnopacks')->insertGetId(
									['ArticleId' =>  $data['ArticleId'], 'ColorId'=>$numberofpacks, 'OutletId'=> $outlet_insertedid,'NoPacks'=>$NoPacks[$key], 'PartyId' =>$data["PartyId"], 'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
								);
							}
						}else{
							foreach($data['ArticleSelectedColor'] as $key => $vl){
								$numberofpacks = $vl["Id"];
								DB::table('outletnopacks')->insertGetId(
									['ArticleId' =>  $data['ArticleId'], 'ColorId'=>$numberofpacks, 'OutletId'=> $outlet_insertedid,'NoPacks'=>$NoPacks, 'PartyId' =>$data["PartyId"],'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
								);
							}
						}
					}else{
						$datavl = DB::select('select Id from outlet where OutletNumberId = "'.$OutletNumberId.'" and ArticleId = "'.$data['ArticleId'].'"');
						
						DB::table('outletnopacks')->insertGetId(
							['ArticleId' =>  $data['ArticleId'], 'ColorId'=>0, 'OutletId'=> $outlet_insertedid,'NoPacks'=>$NoPacks, 'PartyId' =>$data["PartyId"],'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
						);
					}
				}
				
			}
		
			DB::commit();
			return response()->json(array("OutletNumberId"=>$OutletNumberId), 200);
		}catch (\Exception $e) {
			
			DB::rollback();
			//echo "<pre>"; print_r(Exception); exit;
			return response()->json("", 200);
		}
	}
	
	public function UpdateOutlet(Request $request)
    {
        $data = $request->all();
		//$getdata  = $this->GetOutletSingleArticle($data['PartyId'], $data['ArticleId']);
		//echo "<pre>"; print_r($data); exit;
		
		$getresult = DB::select("SELECT NoPacks as GetNoPacks FROM `outlet` where Id = '".$data["id"]."'");
		$GetNoPacks = $getresult[0]->GetNoPacks;
		
		if($data['ArticleRate']>0){
			$ArticleRate = $data['ArticleRate'];
		}else{
			$artratedata = DB::select("select * from articlerate where ArticleId='".$data['ArticleId']."'");
			$ArticleRate = $artratedata[0]->ArticleRate;
		}
		
		if($data['Discount']==""){
			$Discount = 0;
		}else{
			$Discount = $data['Discount'];
		}
			
		//echo "<pre>"; print_r($data); echo $ArticleRate; exit;
		if($data["ArticleOpenFlag"]==1){
			$UpdateInwardNoPacks = "";
			$updateNoPacks = $data['NoPacksNew'];
			$remainignopacks = $data['NoPacks'];
			$total = $remainignopacks + $GetNoPacks;
			if($total<$data["NoPacksNew"]){
				return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
			}
			if (empty($updateNoPacks)) {
				//echo "All zeros!";
				return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
			}
			
			DB::beginTransaction();
			try {
				DB::table('outletnumber')
				->where('Id', $data['OutletNumberId'])
				->update(['OutletDate'=>$data['Date'], 'OutletPartyId' => $data['OutletPartyId'],  'PartyId' =>  $data['PartyId'], 'GSTAmount'=>$data['GSTAmount'], 'GSTPercentage'=>$data['GSTPercentage'], 'Discount'=>$Discount, 'Address'=>$data['Address'], 'Contact'=>$data['Contact']]);


				Outlet::where('id', $data['id'])->update(array(
					 'NoPacks' => $updateNoPacks, 'ArticleRate' => $ArticleRate
				));
				 	
				DB::table('outletnopacks')
				->where('OutletId', $data['id'])
				->where('ArticleId', $data['ArticleId'])
				->where('PartyId', $data['PartyId'])
				->update(['NoPacks'=>$updateNoPacks, 'UpdatedDate'=>date('Y-m-d H:i:s')]);
				
				DB::commit();
				return response()->json("SUCCESS", 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}
			
		}
		else{
			$OutletNoPacks = $data["NoPacks"];
			
			if( strpos($data["NoPacks"], ',') !== false ) {
				$OutletNoPacks = explode(',', $data["NoPacks"]);
				$stringcomma = 1;
			}else{
				$stringcomma = 0;
			}
			
			$updateNoPacks = "";
			$UpdateInwardNoPacks = "";
			if($data["Colorflag"]==1){
				foreach($data['ArticleSelectedColor'] as $key => $vl){
					$numberofpacks = $vl["Id"];
					$outletsale = $OutletNoPacks[$key];
					$remaingnopacks = $data["NoPacks_".$numberofpacks];
					$getnopacks1 = $data["NoPacksNew_".$numberofpacks];
					
					
					if($data["NoPacksNew_".$numberofpacks]!=""){
						if($stringcomma==1){
							$total = ($remaingnopacks + $outletsale);
							/* echo "Comma - ".$total; 
							echo "\n";
							echo "getnopacks1: ".$getnopacks1; 
							echo "\n";
							echo "outletsale: ".$outletsale;
							echo "\n";
							echo "total - ".$total; 
							echo "\n\n";  */
							/* if($total<$getnopacks1 && $total>=$outletsale){ */;
							if($total<$getnopacks1 && $total>$outletsale){
								return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
							}
							
							if($total<$getnopacks1){
								return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
							}
							$updateNoPacks .= $getnopacks1.",";
						}else{
							$total = $remaingnopacks + $OutletNoPacks;
							//echo "Comma2 - ".$total; //exit;
							if($total<$getnopacks1){
								return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
							}
							$updateNoPacks .= $getnopacks1.",";
						}
					}
					else{
						$updateNoPacks .= "0,";
					}
				}
			} else{
				$updateNoPacks .= $data['NoPacksNew'].",";
				$total = $data['NoPacks'] + $GetNoPacks;
				//echo "Colorflag false - ".$total; exit;
				if($total<$data["NoPacksNew"]){
					return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
				}
			}
			
			$updateNoPacks = rtrim($updateNoPacks,',');
			//echo "<pre>"; print_r($data);
			//echo $updateNoPacks; exit;
			
			$CheckupdateNoPacks = explode(',', $updateNoPacks);
			$tmp = array_filter($CheckupdateNoPacks);
			if (empty($tmp)) {
				//echo "All zeros!";
				return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
			}
			//echo $updateNoPacks;
			//exit;
			DB::beginTransaction();
			try {
				DB::table('outletnumber')
				->where('Id', $data['OutletNumberId'])
				->update(['OutletDate'=>$data['Date'], 'OutletPartyId' => $data['OutletPartyId'], 'PartyId' =>  $data['PartyId'], 'GSTAmount'=>$data['GSTAmount'], 'GSTPercentage'=>$data['GSTPercentage'], 'Discount'=>$Discount, 'Address'=>$data['Address'], 'Contact'=>$data['Contact']]);


				Outlet::where('id', $data['id'])->update(array(
					 'NoPacks' => $updateNoPacks, 'ArticleRate' => $ArticleRate
				 ));
				 
				 if($data['ArticleOpenFlag']==0){
						if( strpos($updateNoPacks, ',') !== false ) {
							$updateNoPacks = explode(',', $updateNoPacks);
							foreach($data['ArticleSelectedColor'] as $key => $vl){
								$numberofpacks = $vl["Id"];
								$res = DB::select("select Id from outletnopacks where ColorId='".$numberofpacks."' and OutletId='".$data['id']."' and ArticleId='".$data['ArticleId']."'");
								DB::table('outletnopacks')
								->where('Id', $res[0]->Id)
								->update(['NoPacks'=>$updateNoPacks[$key], 'UpdatedDate'=>date('Y-m-d H:i:s')]);
							}
						}else{
							foreach($data['ArticleSelectedColor'] as $key => $vl){
								$numberofpacks = $vl["Id"];
								$res = DB::select("select Id from outletnopacks where ColorId='".$numberofpacks."' and OutletId='".$data['id']."' and ArticleId='".$data['ArticleId']."'");
								DB::table('outletnopacks')
								->where('Id', $res[0]->Id)
								->update(['NoPacks'=>$updateNoPacks, 'UpdatedDate'=>date('Y-m-d H:i:s')]);
							}
						}
					}else{
						$datavl = DB::select('select Id from outlet where OutletNumberId = "'.$data['OutletNumberId'].'" and ArticleId = "'.$data['ArticleId'].'"');
						
						DB::table('outletnopacks')
						->where('OutletId', $datavl[0]->Id)
						->where('ArticleId', $data['ArticleId'])
						->update(['NoPacks'=>$updateNoPacks, 'UpdatedDate'=>date('Y-m-d H:i:s')]);
					}
				DB::commit();
				return response()->json("SUCCESS", 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}
		}
     }
     
	public function DeleteOutlet($id, $ArticleOpenFlag)
    {
		DB::beginTransaction();
		try {
			DB::table('outlet')
			->where('Id', '=', $id)
			->delete();
			
			DB::table('outletnopacks')
			->where('OutletId', '=', $id)
			->delete();
			
			DB::commit();
			
			return response()->json("SUCCESS", 200);
		} catch (\Exception $e) {
			DB::rollback();
			
			return response()->json("", 200);
		}
    }
	 
	public function DeleteOutleNumber($OTLNO)
	{
		$data = DB::select("SELECT * FROM `outlet` where OutletNumberId = '".$OTLNO."'");
		if(!empty($data)){
			DB::beginTransaction();
			try {
				DB::table('outlet')
				->where('OutletNumberId', '=', $OTLNO)
				->delete();
				
				foreach($data as $key => $val){
					DB::table('outletnopacks')
					->where('OutletId', '=', $val->Id)
					->delete();
				}
				DB::commit();
				return response()->json("SUCCESS", 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}
		}
	}
	
	public function GetOutletChallen($OTLNO){
		//$getoutletchallen = DB::select("SELECT o.Id as OID, a.Id, a.ArticleNumber, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, o.NoPacks, concat(FirstCharacterConcat(u.Name), oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber, oln.OutletDate, oln.GSTAmount, oln.GSTPercentage, oln.Discount, oln.Address, oln.Contact, u.Name as UserName, c.Title, c.Colorflag, o.ArticleRate, oln.PartyName FROM `outlet` o inner join outletnumber oln on oln.Id=o.OutletNumberId inner join article a on a.Id=o.ArticleId inner join users u on u.Id=oln.UserId inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId inner join financialyear fn on fn.Id=oln.FinancialYearId where o.OutletNumberId =  '".$OTLNO."' order by o.Id asc");
		//$getoutletchallen = DB::select("SELECT o.Id as OID, a.Id, a.ArticleNumber, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, o.NoPacks, concat(FirstCharacterConcat(u.Name), oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber, oln.OutletDate, oln.GSTAmount, oln.GSTPercentage, oln.Discount, oln.Address, oln.Contact, u.Name as UserName, c.Title, c.Colorflag, o.ArticleRate, pr.Name as PartyName FROM `outlet` o inner join outletnumber oln on oln.Id=o.OutletNumberId inner join article a on a.Id=o.ArticleId inner join users u on u.Id=oln.UserId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party pr on pr.Id=oln.OutletPartyId where o.OutletNumberId = '".$OTLNO."' order by o.Id asc");
		$getoutletchallen = DB::select("SELECT o.Id as OID, a.Id, a.ArticleNumber, CASE WHEN (oi.ArticleColor IS NULL) THEN a.ArticleColor ELSE oi.ArticleColor END AS `ArticleColor`, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, o.NoPacks, concat(FirstCharacterConcat(u.Name), oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber, oln.OutletDate, oln.GSTAmount, oln.GSTPercentage, oln.Discount, oln.Address, oln.Contact, u.Name as UserName, c.Title, c.Colorflag, o.ArticleRate, pr.Name as PartyName FROM `outlet` o inner join outletnumber oln on oln.Id=o.OutletNumberId inner join article a on a.Id=o.ArticleId inner join users u on u.Id=oln.UserId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party pr on pr.Id=oln.OutletPartyId left join outletimport oi on oi.ArticleId=a.Id and oi.PartyId = oln.PartyId where o.OutletNumberId = '".$OTLNO."' order by o.Id asc");
		//echo "<pre>"; print_r($getoutletchallen); exit;
		//return $getoutletchallen;
		
		$challendata = [];
		$TotalNoPacks = 0;
		$TotalAmount = 0;
		$TotalQuantityPic = 0;
		if(date('m') >= 06) {
		   $d = date('Y-m-d', strtotime('+1 years'));
		   $FinanceYear =  date('y') .'-'.date('y', strtotime($d));
		} else {
		  $d = date('Y-m-d', strtotime('-1 years'));
		  $FinanceYear =  date('y', strtotime($d)).'-'.date('y');
		}

		foreach($getoutletchallen as $vl){
			$UserName = $vl->UserName;
			$ArticleNumber = $vl->ArticleNumber;
			$ArticleOpenFlag = $vl->ArticleOpenFlag;
			$Title = $vl->Title;
			$OutletDate = $vl->OutletDate;
			$OutletNumber = $vl->OutletNumber;
			$NoPacks = $vl->NoPacks;
			$Colorflag = $vl->Colorflag;
			$ArticleRate = $vl->ArticleRate;
			$PartyName = $vl->PartyName;
			
			$Address = $vl->Address;
			$Contact = $vl->Contact;
			$GSTPercentage = $vl->GSTPercentage;
			$GSTAmount = $vl->GSTAmount;
			$Discount = number_format($vl->Discount, 2);
			
			if($Colorflag==0){
				if($ArticleOpenFlag==0){
					$task_array = json_decode($vl->ArticleColor);
					$countcolor = count($task_array);
					$ArticleRatio = $vl->ArticleRatio;
					if( strpos($ArticleRatio, ',') !== false ) {
						$TotalArticleRatio = array_sum(explode(",",$ArticleRatio));
					}else{
						$TotalArticleRatio = $ArticleRatio;
					}
					$TotalNoPacks+= $NoPacks;
					//$QuantityPic = $NoPacks * $countcolor * $TotalArticleRatio;
				}else{
					//$QuantityPic = $NoPacks;
					$TotalNoPacks+= $NoPacks;
				}
			} else{
				if($ArticleOpenFlag==0){
					$task_array = json_decode($vl->ArticleColor);
					$ArticleRatio = $vl->ArticleRatio;
					$countcolor = count($task_array);
					$TotalArticleRatio = array_sum(explode(",",$ArticleRatio));
					$countNoSet = array_sum(explode(",",$NoPacks));
					$TotalNoPacks+= array_sum(explode(",",$NoPacks));
					//$QuantityPic = $countNoSet * $TotalArticleRatio;
				}else{
					//$QuantityPic = $NoPacks;
					$TotalNoPacks+= $NoPacks;
				}
			}
			//$TotalQuantityPic += $QuantityPic;
			
			if($ArticleOpenFlag==0){
				$ArticleRatio = $vl->ArticleRatio;
				
				if( strpos($NoPacks, ',') !== false ) {
					$countNoSet = array_sum(explode(",",$NoPacks));
					$TotalArticleRatio = array_sum(explode(",",$ArticleRatio));
				}else{
					if( strpos($ArticleRatio, ',') !== false ) {
						$ArticleRatio = array_sum(explode(",",$ArticleRatio));
					}
					$countNoSet = $NoPacks;
					$TotalArticleRatio = $ArticleRatio;
				}
				
				$Amount = $countNoSet * $ArticleRate;
				$TotalAmount += $Amount;
				
				$getcolor = json_decode($vl->ArticleColor);
				$getsize = json_decode($vl->ArticleSize);
				
				$ArticleColor = "";
				foreach($getcolor as $vl){
					$ArticleColor .= $vl->Name.",";
				}
				$ArticleColor = rtrim($ArticleColor,',');
				
				$ArticleSize = "";
				foreach($getsize as $vl){
					$ArticleSize .= $vl->Name.",";
				}
				$ArticleSize = rtrim($ArticleSize,',');
			}else{
				$countNoSet = $NoPacks;
				$Amount = $countNoSet * $ArticleRate;
				$ArticleRatio = "";
				$ArticleColor = "";
				$ArticleSize = "";
			}
			$challendata[] = json_decode(json_encode(array("PartyName"=>$PartyName, "UserName"=>$UserName, "OutletDate"=>$OutletDate,"OutletNumber"=>$OutletNumber,"ArticleNumber"=>$ArticleNumber,"Title"=>$Title, "ArticleRatio"=>$ArticleRatio, "QuantityInSet"=>$NoPacks, "ArticleColor"=>$ArticleColor, "ArticleSize"=>$ArticleSize, "ArticleRate"=>number_format($ArticleRate, 2), "Amount"=>number_format($Amount, 2))), false);
		}
		
		$TotalFinalAmount = 0;
		$GSTLabel = "";
		$GSTValue = 0;
		if($GSTPercentage!="" || $GSTAmount!=""){
			if($GSTPercentage>0){
				$GSTLabel = "GST ".$GSTPercentage."%";
				$GSTValue = number_format((($TotalAmount * $GSTPercentage) / 100), 2);
				$GSTValue1 = (($TotalAmount * $GSTPercentage) / 100);
				$TotalFinalAmount = ($TotalAmount + $GSTValue1);
			} else{
				$GSTValue = number_format($GSTAmount, 2);
				$GSTValue1 = $GSTAmount;
				$TotalFinalAmount = ($TotalAmount + $GSTValue1);
				$GSTLabel = "GST Amount";
			}
		}
		
		//echo $TotalFinalAmount1."\n"; exit;
		//echo $TotalAmount."\n";
		//echo $Discount."\n";
		//echo $TotalFinalAmount1; exit;
		if($Discount>0 || $Discount!=""){
			if($TotalFinalAmount>0){
				$TotalFinalAmount = ($TotalFinalAmount - $Discount);
			}else{
				$TotalFinalAmount = ($TotalAmount - $Discount);
			}			
		}else{
			if($TotalFinalAmount==0){
				$TotalFinalAmount = $TotalAmount;
			}
		}
			
		//echo "<pre>"; print_r($challendata); exit;
		$as  = array($challendata, array("RoundOff"=>$this->splitter(number_format($TotalFinalAmount, 2, '.', '')),"TotalQuantityPic"=>$TotalNoPacks, "TotalAmount"=>number_format($TotalAmount, 2), "Discount"=>number_format($Discount, 2), "TotalFinalAmount"=>number_format($TotalFinalAmount, 2), "GSTLabel"=>$GSTLabel, "GSTValue"=>$GSTValue));
		//echo "<pre>"; print_r($as); exit;
		return $as;
	}
	
	function splitter($val)
	{
		$totalroundamount = $val; 
		$str = (string) $val;
		$splitted = explode(".",$str);
		$whole = (integer)$splitted[0] ;
		$num = (integer) $splitted[1];
		$lennum = strlen($num);
		if($num<10){
			$num = $num.'0';
		}
		//echo $num; exit;
		$den = (integer)  pow(10,strlen($splitted[1]));
		$number_var ="";
		$adjust_amount = "";
		if($num!=0){
			$roundoff_check = true;
			if($num>=50){
				$number_var = "Up";
				$adjust_amount = "0.". (100 - $num);
			}else{
				$number_var = "Down";
				$adjust_amount = "0.".$num;		  
			} 
			$totalroundamount = number_format(round($totalroundamount), 2);
		}else{
		  $number_var = "Zero";
		  $roundoff_check = false;
		  $adjust_amount = 0;
		  //round figure amount
		}
	  
	  return array("Roundoff"=>$roundoff_check,'RoundValueSign'=>$number_var,'TotalRoundAmount'=>$totalroundamount,'AdjustAmount'=>number_format($adjust_amount, 2));
	}
	
	public function OutletList($UserId)
    { 
		$userrole = DB::select("SELECT Role FROM `users`where Id='".$UserId."'");
		if($userrole[0]->Role==2){
			$wherecustom ="";
		}else{
			$wherecustom ="where oln.UserId='".$UserId."'";
		}
		return DB::select("SELECT oln.Id, oln.OutletDate, concat(FirstCharacterConcat(u.Name), oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber  FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId inner join users u on u.Id=oln.UserId ".$wherecustom." group by oln.Id");
	}
	
	public function PostOutletList(Request $request)
	{
		$data = $request->all();	
		$search = $data['dataTablesParameters']["search"];
		$startnumber = $data['dataTablesParameters']["start"];
		$UserId = $data['UserID'];
		$userrole = DB::select("SELECT Role, PartyId FROM `users`where Id='".$UserId."'");
		
		//return $userrole; exit;
		
		if($userrole[0]->Role==2){
				//$vnddataTotal = DB::select("select count(*) as Total from (SELECT oln.Id, oln.UserId, oln.OutletDate, concat(FirstCharacterConcat(u.Name), oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber  FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId inner join users u on u.Id=oln.UserId group by oln.Id) as d");
			$vnddataTotal = DB::select("select count(*) as Total from (select d.* from (SELECT oln.Id FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party p on p.Id=oln.PartyId left join party pp on pp.Id=oln.OutletPartyId inner join users u on u.Id=oln.UserId) as d group by d.Id) as dd");
			$vntotal=$vnddataTotal[0]->Total;		
			$length = $data['dataTablesParameters']["length"];
			
			$wherecustom ="";
			if($search['value'] != null && strlen($search['value']) > 2){
				//$searchstring = "and oln.OutletNumber like '%".$search['value']."%'";
				/* $vnddataTotal = DB::select("select count(*) as Total from (select d.* from (SELECT oln.Id FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party p on p.Id=oln.PartyId left join party pp on pp.Id=oln.OutletPartyId inner join users u on u.Id=oln.UserId) as d group by d.Id) as dd");
				$vntotal=$vnddataTotal[0]->Total;		
				$length = $data['dataTablesParameters']["length"]; */
				
				$searchstring = "where d.OutletNumber like '%".$search['value']."%' OR cast(d.cdate as char) like '%".$search['value']."%' OR d.OutletName like '%".$search['value']."%' OR d.PartyName like '%".$search['value']."%'  OR d.ArticleNumber like '%".$search['value']."%'";
				//$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT oln.Id, oln.UserId, oln.OutletDate, DATE_FORMAT(oln.OutletDate, \"%d/%m/%Y\") as cdate, concat(FirstCharacterConcat(u.Name), oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber  FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId inner join users u on u.Id=oln.UserId group by oln.Id) as d ".$searchstring);
				$vnddataTotalFilter = DB::select("select count(*) as Total from (select d.*, GROUP_CONCAT(DISTINCT CONCAT(d.ArticleNumber1) ORDER BY d.Id SEPARATOR ',') as ArticleNumber from (SELECT oln.Id, a.ArticleNumber as ArticleNumber1, p.Name as PartyName, pp.Name as OutletName, DATE_FORMAT(oln.OutletDate, '%d/%m/%Y') as cdate, concat(FirstCharacterConcat(u.Name),oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party p on p.Id=oln.PartyId left join party pp on pp.Id=oln.OutletPartyId inner join users u on u.Id=oln.UserId) as d group by d.Id) as d ".$searchstring);
				$vntotal=$vnddataTotalFilter[0]->Total;
				$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
			}else{
				$searchstring = "";
				$vnddataTotalFilterValue = $vntotal;
			}
			
		}else{
			//$vnddataTotal = DB::select("select count(*) as Total from (SELECT oln.Id, oln.UserId, oln.OutletDate, concat(FirstCharacterConcat(u.Name), oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber  FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId inner join users u on u.Id=oln.UserId where oln.UserId='".$UserId."' group by oln.Id) as d");
			$vnddataTotal = DB::select("select count(*) as Total from (select d.*, GROUP_CONCAT(DISTINCT CONCAT(d.ArticleNumber1) ORDER BY d.Id SEPARATOR ',') as ArticleNumber from (SELECT oln.Id, p.Name as PartyName, p.Id as PartyId,a.ArticleNumber as ArticleNumber1,  pp.Name as OutletName, DATE_FORMAT(oln.OutletDate, '%d/%m/%Y') as cdate, concat(FirstCharacterConcat(u.Name),oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party p on p.Id=oln.PartyId left join party pp on pp.Id=oln.OutletPartyId inner join users u on u.Id=oln.UserId) as d group by d.Id) as d where PartyId='".$userrole[0]->PartyId."'");
			
			$vntotal=$vnddataTotal[0]->Total;		
			$length = $data['dataTablesParameters']["length"];
			
			/* if($userrole[0]->PartyId!=0){
				$wherecustom ="where d.PartyId='".$userrole[0]->PartyId."' ";
			}else{
				$wherecustom ="";
			}
			 */
			//$wherecustom ="where d.UserId='".$UserId."'";
			if($search['value'] != null && strlen($search['value']) > 2){
				if($userrole[0]->PartyId!=0){
					//$searchstring = "and d.OutletNumber like '%".$search['value']."%' OR cast(d.cdate as char) like '%".$search['value']."%'";
					$searchstring = "where d.PartyId='".$userrole[0]->PartyId."' and d.OutletNumber like '%".$search['value']."%' OR cast(d.cdate as char) like '%".$search['value']."%' OR d.OutletName like '%".$search['value']."%' OR d.PartyName like '%".$search['value']."%' OR d.ArticleNumber like '%".$search['value']."%'";
				}
				else{
					//$searchstring = "where d.OutletNumber like '%".$search['value']."%' OR cast(d.cdate as char) like '%".$search['value']."%'";
					$searchstring = "where d.OutletNumber like '%".$search['value']."%' OR cast(d.cdate as char) like '%".$search['value']."%' OR d.OutletName like '%".$search['value']."%' OR d.PartyName like '%".$search['value']."%' OR d.ArticleNumber like '%".$search['value']."%'";
				}
				
				//$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT oln.Id, oln.UserId, oln.OutletDate, DATE_FORMAT(oln.OutletDate, \"%d/%m/%Y\") as cdate, concat(FirstCharacterConcat(u.Name), oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber  FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId inner join users u on u.Id=oln.UserId where oln.UserId='".$UserId."' group by oln.Id) as d ".$wherecustom.' '.$searchstring);
				
				/* if($userrole[0]->PartyId!=0){
					$wherequery = " where oln.PartyId = '".$userrole[0]->PartyId."'";
				} else{
					$wherequery = "";
				}
				$searchstring = "where d.OutletNumber like '%".$search['value']."%' OR cast(d.cdate as char) like '%".$search['value']."%'";
				 */
				 
				//$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT oln.Id, oln.UserId, oln.PartyId, oln.OutletDate, DATE_FORMAT(oln.OutletDate, '%d/%m/%Y') as cdate, concat(FirstCharacterConcat(u.Name), oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber  FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId inner join users u on u.Id=oln.UserId left join party p on p.Id=oln.PartyId ".$wherequery." group by oln.Id) as d ".$wherecustom.' '.$searchstring);
				$vnddataTotalFilter = DB::select("select count(*) as Total from (select d.*, GROUP_CONCAT(DISTINCT CONCAT(d.ArticleNumber1) ORDER BY d.Id SEPARATOR ',') as ArticleNumber from (SELECT oln.Id, p.Name as PartyName, a.ArticleNumber as ArticleNumber1, p.Id as PartyId, pp.Name as OutletName, DATE_FORMAT(oln.OutletDate, '%d/%m/%Y') as cdate, concat(FirstCharacterConcat(u.Name),oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party p on p.Id=oln.PartyId left join party pp on pp.Id=oln.OutletPartyId inner join users u on u.Id=oln.UserId) as d group by d.Id) as d ".$searchstring);
				$vntotal=$vnddataTotalFilter[0]->Total;
				$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
			}else{
				
				$searchstring = "where d.PartyId='".$userrole[0]->PartyId."'";
				$vnddataTotalFilterValue = $vntotal;
			}
		}
		//end
		$column = $data['dataTablesParameters']["order"][0]["column"];
		switch ($column) {
			case 4:
				$ordercolumn = "d.OutletDate";
				break;
			case 5:
				$ordercolumn = "d.OutletNumber";
				break;				
			default:
				$ordercolumn = "d.OutletDate";
				break;
		}
		
		$order = "";
		if($data['dataTablesParameters']["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data['dataTablesParameters']["order"][0]["dir"];
		}
		
		//return "select d.*, sum(d.TotalNoPacks) as TotalPieces from (SELECT oln.Id, oln.UserId, p.Id as PartyId, p.Name as PartyName, pp.Name as OutletName, CountNoPacks(o.NoPacks) as TotalNoPacks, oln.OutletDate,DATE_FORMAT(oln.OutletDate, '%d/%m/%Y') as cdate, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY oln.Id SEPARATOR ',') as ArticleNumber, concat(FirstCharacterConcat(u.Name),oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber  FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party p on p.Id=oln.PartyId left join party pp on pp.Id=oln.OutletPartyId inner join users u on u.Id=oln.UserId) as d ".$wherecustom." ".$searchstring." group by d.Id ".$order." limit ".$data['dataTablesParameters']["start"].",".$length; exit;
		//$vnddata = DB::select("select d.*, sum(d.TotalNoPacks) as TotalPieces from (SELECT oln.Id, oln.UserId, p.Id as PartyId, p.Name as PartyName, pp.Name as OutletName, CountNoPacks(o.NoPacks) as TotalNoPacks, oln.OutletDate,DATE_FORMAT(oln.OutletDate, '%d/%m/%Y') as cdate, concat(FirstCharacterConcat(u.Name),oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber  FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party p on p.Id=oln.PartyId left join party pp on pp.Id=oln.OutletPartyId inner join users u on u.Id=oln.UserId) as d ".$wherecustom." ".$searchstring." group by d.Id ".$order." limit ".$data['dataTablesParameters']["start"].",".$length);
		
		//return "select d.*, GROUP_CONCAT(DISTINCT CONCAT(d.ArticleNumber) ORDER BY d.Id SEPARATOR ',') as ArticleNumber,  sum(d.TotalNoPacks) as TotalPieces from (SELECT oln.Id, oln.UserId, p.Id as PartyId, p.Name as PartyName, pp.Name as OutletName, a.ArticleNumber, CountNoPacks(o.NoPacks) as TotalNoPacks, oln.OutletDate,DATE_FORMAT(oln.OutletDate, '%d/%m/%Y') as cdate, concat(FirstCharacterConcat(u.Name),oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber  FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party p on p.Id=oln.PartyId left join party pp on pp.Id=oln.OutletPartyId inner join users u on u.Id=oln.UserId) as d ".$wherecustom." ".$searchstring." group by d.Id ".$order." limit ".$data['dataTablesParameters']["start"].",".$length; exit;
		$vnddata = DB::select("select d.*, GROUP_CONCAT(DISTINCT CONCAT(d.ArticleNumber) ORDER BY d.Id SEPARATOR ',') as ArticleNumber,  sum(d.TotalNoPacks) as TotalPieces from (SELECT oln.Id, oln.UserId, p.Id as PartyId, p.Name as PartyName, pp.Name as OutletName, a.ArticleNumber, CountNoPacks(o.NoPacks) as TotalNoPacks, oln.OutletDate,DATE_FORMAT(oln.OutletDate, '%d/%m/%Y') as cdate, concat(FirstCharacterConcat(u.Name),oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber  FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party p on p.Id=oln.PartyId left join party pp on pp.Id=oln.OutletPartyId inner join users u on u.Id=oln.UserId) as d ".$searchstring." group by d.Id ".$order." limit ".$data['dataTablesParameters']["start"].",".$length);
		//return "select d.*, sum(d.TotalNoPacks) as TotalPieces from (SELECT oln.Id, oln.UserId, p.Name as PartyName, pp.Name as OutletName, CountNoPacks(o.NoPacks) as TotalNoPacks, oln.OutletDate,DATE_FORMAT(oln.OutletDate, '%d/%m/%Y') as cdate, concat(FirstCharacterConcat(u.Name),oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber  FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party p on p.Id=oln.PartyId left join party pp on pp.Id=oln.OutletPartyId inner join users u on u.Id=oln.UserId) as d ".$wherecustom." ".$searchstring." group by d.Id ".$order." limit ".$data['dataTablesParameters']["start"].",".$length;
		//select d.* from (SELECT oln.Id, oln.OutletDate, concat(FirstCharacterConcat(u.Name), oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber, oln.UserId  FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId inner join users u on u.Id=oln.UserId) as d where d.UserId='25' and d.OutletNumber like '%sub%' group by d.Id limit 0,10
		return array(
				'datadraw'=>$data['dataTablesParameters']["draw"],
				'recordsTotal'=>$vntotal,
				'recordsFiltered'=>$vnddataTotalFilterValue,
				'startnumber' => $startnumber,
				'response' => 'success',
				'search'=>count($vnddata),
				'data' => $vnddata,
			);
		
	}
	
	public function OutletListFromOTLNO($Id)
    { 
		return DB::select('SELECT concat(oln.OutletNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as Outlet_Number_FinancialYear, o.Id , o.OutletNumberId, oln.OutletNumber,  oln.OutletDate, o.NoPacks, a.ArticleNumber, a.ArticleOpenFlag FROM `outlet` o inner join outletnumber oln on o.OutletNumberId=oln.Id inner join financialyear fn on fn.Id=oln.FinancialYearId inner join article a on a.Id=o.ArticleId where o.OutletNumberId="'.$Id.'"');
	}
	
	public function OutletDatePartyfromOTLNO($Id)
    { 
		return DB::select("SELECT oln.*, concat(FirstCharacterConcat(u.Name), oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as Outlet_Number_FinancialYear FROM `outletnumber`oln inner join financialyear fn on fn.Id=oln.FinancialYearId inner join users u on u.Id=oln.UserId where oln.Id='". $Id ."'");
	}
	
	public function GetOutletIdWise($id)
    {
        return DB::select('SELECT o.*, concat(oln.OutletNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as OutletNumber, oln.OutletDate From outlet o inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId WHERE o.Id = ' . $id . '');
    }
	
	public function UpdateTransportStatus(Request $request)
    {
		$data = $request->all();
		//return print_r($data); exit;
		Transportoutlet::where('OutwardNumberId', $data['OutwardId'])->update(array(
            'TransportStatus' => $data['TransportStatus'],
            'Remarks' => $data['Remarks'],
            'UserId' => $data['UserId'],
			'TotalPieces' => $data['TotalPieces'],
			'ReceivedDate' => $data['ReceivedDate']
        ));
		
		if($data['TransportStatus']==1){
			DB::select('INSERT INTO `transportoutwardpacks` (`ArticleId`, `ColorId`, `OutwardId`, `NoPacks`, `PartyId`, `CreatedDate`, `UpdatedDate`) select `ArticleId`, `ColorId`, `OutwardId`, `NoPacks`, `PartyId`, `CreatedDate`, `UpdatedDate` from outwardpacks where OutwardId in (SELECT Id FROM `outward` where OutwardNumberId = '.$data['OutwardId'].')');
		}
        return response()->json("SUCCESS", 200);
	}
	
	public function getoutwardtransport($partyId){
		return DB::select('SELECT outn.Id, concat(outn.OutwardNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as Name FROM `transportoutlet` t inner join `outwardnumber` outn on outn.Id = t.OutwardNumberId inner join financialyear fn on fn.Id=outn.FinancialYearId where t.TransportStatus=0 and t.PartyId = '.$partyId);
	}
	
	public function getoutwardpieces($outwardid){
		//return DB::select("select sum(TotalNoPacks) as TotalPieces from (SELECT c.Colorflag, a.ArticleOpenFlag, CountNoPacks(o.NoPacks) as TotalNoPacks,(SELECT count(*) FROM `articlecolor` where ArticleId=a.Id) as TotalColor, (SELECT sum(ArticleRatio) FROM `articleratio` where ArticleId=a.Id) as TotalRation FROM `outward` o inner join article a on a.Id=o.ArticleId left join po po on po.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where o.OutwardNumberId = '".$outwardid."') as dd");
		
		//return DB::select("select sum(TotalNoPacks) as TotalPieces, OutwardDate from (SELECT own.OutwardDate, c.Colorflag, a.ArticleOpenFlag, CountNoPacks(o.NoPacks) as TotalNoPacks,(SELECT count(*) FROM `articlecolor` where ArticleId=a.Id) as TotalColor, (SELECT sum(ArticleRatio) FROM `articleratio` where ArticleId=a.Id) as TotalRation FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on own.OutwardNumber=o.OutwardNumberId left join po po on po.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where o.OutwardNumberId = '".$outwardid."') as dd");
		return DB::select("select sum(TotalNoPacks) as TotalPieces, OutwardDate from (SELECT own.OutwardDate, c.Colorflag, a.ArticleOpenFlag, CountNoPacks(o.NoPacks) as TotalNoPacks,(SELECT count(*) FROM `articlecolor` where ArticleId=a.Id) as TotalColor, (SELECT sum(ArticleRatio) FROM `articleratio` where ArticleId=a.Id) as TotalRation FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on own.Id=o.OutwardNumberId left join po po on po.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where own.Id = '".$outwardid."' group by o.Id) as dd");
	}
	
	public function intransitlist($partyId){
		if($partyId==0){
			return DB::select("SELECT t.*, p.Name as PartyName, o.OutwardDate, concat(o.OutwardNumber, '/',f.StartYear,'-',f.EndYear) as OutwardNo FROM `transportoutlet` t left join party p on p.Id=t.PartyId left join outwardnumber o on o.Id=t.OutwardNumberId left join financialyear f on f.Id=o.FinancialYearId");
		} else{
			return DB::select("SELECT t.*, p.Name as PartyName, o.OutwardDate, concat(o.OutwardNumber, '/',f.StartYear,'-',f.EndYear) as OutwardNo FROM `transportoutlet` t left join party p on p.Id=t.PartyId left join outwardnumber o on o.Id=t.OutwardNumberId left join financialyear f on f.Id=o.FinancialYearId where t.PartyId = ".$partyId);
		}
		
	}
	
	public function GenerateOSRNumber($UserId)
    { 
		$array = array();
		$fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
        $srnumberdata = DB::select('SELECT Id, FinancialYearId, SalesReturnNumber From outletsalesreturnnumber order by Id desc limit 0,1');
		
		if(count($srnumberdata)>0){
			if($fin_yr[0]->Id > $srnumberdata[0]->FinancialYearId){
				$array["SR_Number"] = 1;
				$array["SR_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["SR_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			} else{
				$array["SR_Number"] = ($srnumberdata[0]->SalesReturnNumber) + 1;
				$array["SR_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["SR_Number_Financial"] = ($srnumberdata[0]->SalesReturnNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			}
		}
        else{
			$array["SR_Number"] = 1;
			$array["SR_Number_Financial_Id"] = $fin_yr[0]->Id;
			$array["SR_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
			return $array;
        }
    }
	
	public function AddOutletSalesReturn(Request $request){
		$data = $request->all();
		$OutletPartyId = $data['OutletPartyId'];
		
		if($data['SRNumberId']=="Add"){
			$generate_SRNUMBER = $this->GenerateOSRNumber($data['UserId']);
			$SR_Number = $generate_SRNUMBER['SR_Number'];
			$SR_Number_Financial_Id = $generate_SRNUMBER['SR_Number_Financial_Id'];
			$SRNumberId = DB::table('outletsalesreturnnumber')->insertGetId(
				['SalesReturnNumber' =>  $SR_Number,"FinancialYearId"=>$SR_Number_Financial_Id,'PartyId' =>  $data['PartyId'],'OutletPartyId'=>$OutletPartyId,'Remarks'=>$data['Remark'],'CreatedDate' => date('Y-m-d H:i:s')]
			);
		}else{
			$checksonumber = DB::select("SELECT SalesReturnNumber FROM `outletsalesreturnnumber` where Id ='".$data['SRNumberId']."'");
			
			if(!empty($checksonumber)){
				$SR_Number = $checksonumber[0]->SalesReturnNumber;
				$SRNumberId = $data['SRNumberId'];
				
				DB::table('outletsalesreturnnumber')
				->where('Id', $SRNumberId)
				->update(['PartyId' =>  $data['PartyId'],'OutletPartyId'=>$OutletPartyId, 'Remarks'=>$data['Remark']]);
			}
		}
		
		
		if($data['ArticleOpenFlag']==1)
		{
			if(isset($data['NoPacksNew'])==""){
				return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
			}
			
			if($data['partyflag']==true){
				if($data['NoPacks_TotalOutlet'] < $data['NoPacksNew']){
					return response()->json(array("id"=>"", "OutletNoOfSetNotMatch"=>"true"), 200);
				}
			}
				
			if($data['NoPacks'] < $data['NoPacksNew']){
				return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
			}			
			//print_r($data); exit;
			$getdata = DB::select("SELECT * FROM `mixnopacks` where ArticleId='".$data["ArticleId"]."'");
			$getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutletId, o.NoPacks as OutwardNoPacks FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId inner join category c on c.Id=a.CategoryId where o.PartyId='".$data["PartyId"]."' and o.ArticleId = '".$data["ArticleId"]."' and o.OutwardNumberId='".$data["OutwardNumberId"]."' group by OutwardNumber");
			$OutletId = $getalldata[0]->OutletId;
			//print_r($getdata); print_r($data); print_r($getalldata); exit;
			if(!empty($getdata)){
				$InwardNoPacks = $getdata[0]->NoPacks;
				$NoPacks = $data["NoPacksNew"];
				
				$totalnopacks = ($InwardNoPacks+$NoPacks);
				//return $totalnopacks; exit;
				
				DB::beginTransaction();
				try {
					//$ddd= array("SalesReturnNumber"=>1,'OutwardId'=>$OutwardId,'PartyId' =>  $data['PartyId'],'OutletPartyId'=>$OutletPartyId,'ArticleId'=>$data['ArticleId'], 'NoPacks' =>  $NoPacks, 'UserId' =>  $data['UserId'], 'CreatedDate'=>date('Y-m-d H:i:s'));
					//print_r($ddd); exit;
					DB::table('mixnopacks')
					->where('ArticleId', $data['ArticleId'])
					->update(['NoPacks'=>$totalnopacks]);
					
					/* $salesreturn_numberid = DB::table('salesreturnnumber')->insertGetId(
					['SalesReturnNumber'=>$SR_Number,"FinancialYearId"=>$SR_Number_Financial_Id, 'CreatedDate' => date('Y-m-d H:i:s')]); */
			
			
					$salesreturnId = DB::table('outletsalesreturn')->insertGetId(
						["SalesReturnNumber"=>$SRNumberId,'OutletId'=>$OutletId,'ArticleId'=>$data['ArticleId'], 'NoPacks' =>  $NoPacks, 'UserId' =>  $data['UserId'], 'CreatedDate'=>date('Y-m-d H:i:s')]
					);
					
					DB::table('outletsalesreturnpacks')->insertGetId(
						['SalesReturnId'=>$salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId'=>0, 'OutletId'=> $OutletId,'NoPacks'=>$NoPacks, 'PartyId' =>$data["PartyId"],'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
					);
					
					DB::commit();
					return response()->json(array("SRNumberId"=>$SRNumberId, "id"=>"SUCCESS"), 200);
				} catch (\Exception $e) {
					DB::rollback();
					return response()->json("", 200);
				}
			}else{
				return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
			}
		} 
		else{
			
			//$getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutwardId, o.NoPacks as OutwardNoPacks FROM article a inner join outlet o on o.ArticleId=a.Id left join outletnumber own on own.Id=o.OutletNumberId inner join financialyear fn on fn.Id=own.FinancialYearId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where own.OutletPartyId='".$data["PartyId"]."' and o.ArticleId = '".$data["ArticleId"]."' and o.OutletNumberId='".$data["OutwardNumberId"]."' group by OutletNumber");
			$getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutletId, o.NoPacks as OutletNoPacks FROM article a inner join outlet o on o.ArticleId=a.Id left join outletnumber own on own.Id=o.OutletNumberId inner join financialyear fn on fn.Id=own.FinancialYearId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where own.Id='".$data["OutwardNumberId"]."' and o.ArticleId = '".$data["ArticleId"]."' and o.OutletNumberId='".$data["OutwardNumberId"]."' group by OutletNumber");
			
		
			if($getalldata[0]->ArticleOpenFlag==0 && $getalldata[0]->Colorflag==0){
				
				if($data['NoPacksNew']==""){
					return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
				}
				
				//return $data['NoPacks_TotalOutlet']; exit;
				$as = DB::select("select sum(ddd.NoPacks) as TotalNoPacks from (select NoPacks FROM `outletsalesreturnpacks` where OutletId= '".$data['OutletId']."' group by NoPacks) as ddd");
				//return $as; exit;
				$spacks = "";
				if(isset($as[0]->TotalNoPacks)!=""){
					$npacks = $getalldata[0]->OutletNoPacks;
					$spacks = $as[0]->TotalNoPacks;
					$newdata = $npacks - $spacks;
					$NoPacksNew = $data['NoPacksNew'];
					if($newdata < $NoPacksNew){
						return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
					}
				} else{
					$newdata = $getalldata[0]->OutletNoPacks;
				}
				
			} else{
				$as = DB::select("select GROUP_CONCAT(CONCAT(ddd.NoPacks) SEPARATOR ',') as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `outletsalesreturnpacks` where OutletId= '".$getalldata[0]->OutletId."' group by ColorId) as ddd");
				$SalesReturnNoPacks = "";
				if($as[0]->SalesReturnNoPacks!=""){
					$SalesReturnNoPacks = $as[0]->SalesReturnNoPacks;
					$npacks = explode(",", $getalldata[0]->OutletNoPacks);
					$spacks = explode(",", $as[0]->SalesReturnNoPacks);
					//print_r($npacks); exit;
					$newdata  = "";
					foreach($npacks as $key => $vl){
						$newdata .= ($vl - $spacks[$key]).',';
					}
					$newdata = rtrim($newdata, ',');
				}else{
					$newdata = $getalldata[0]->OutletNoPacks;
				}
			}
			
			
			//$getdata = DB::select("SELECT SalesNoPacks FROM `inward` where ArticleId='".$data['ArticleId']."'");
			//$InwardSalesNoPacks = $getdata[0]->SalesNoPacks;
				
			/* print_r($data);
			print_r($getdata);
			print_r($getalldata);
			print_r($as);
			exit; */
			$search = $getalldata[0]->OutletNoPacks;
			$OutletId = $getalldata[0]->OutletId;
			
			$NoPacks = "";
			$SalesNoPacks = "";
			$UpdateInwardNoPacks = "";
			$searchString = ',';
			if( strpos($search, $searchString) !== false ) {
				//echo "fffff";
				$string = explode(',', $search);
				//$InwardSalesNoPacks = explode(',', $InwardSalesNoPacks);
				$stringcomma = 1;
			} 
			else{
				//echo "333";
				$search;
				//$InwardSalesNoPacks = $InwardSalesNoPacks;
				$stringcomma = 0;
			}
			//echo $InwardSalesNoPacks;
			//exit;
			if($data['ArticleColorFlag']=="Yes"){
				foreach($data['ArticleSelectedColor'] as $key => $vl){
					$numberofpacks = $vl["Id"];
					//$InwardSalesNoPacks_VL = $InwardSalesNoPacks[$key];
					
					if($data["NoPacksNew_".$numberofpacks]!=""){
						if($stringcomma==1){
							if($data["NoPacks_".$numberofpacks]<$data["NoPacksNew_".$numberofpacks]){
								return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
							}
							
							$SalesNoPacks .= ($data["NoPacks_".$numberofpacks] - $data["NoPacksNew_".$numberofpacks]).",";
							//$UpdateInwardNoPacks .= ($InwardSalesNoPacks_VL + $data["NoPacksNew_".$numberofpacks]).",";
						}else{
							if($data["NoPacks_".$numberofpacks]<$data["NoPacksNew_".$numberofpacks]){
								return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
							}
							
							$SalesNoPacks .= ($data["NoPacks_".$numberofpacks] - $data["NoPacksNew_".$numberofpacks]).",";
							//$UpdateInwardNoPacks .= ($InwardSalesNoPacks + $data["NoPacksNew_".$numberofpacks]).",";
						}
						$NoPacks .= $data["NoPacksNew_".$numberofpacks].",";
					}
					else{
						$NoPacks .= "0,";
						$SalesNoPacks .= $data["NoPacks_".$numberofpacks].",";
						//$UpdateInwardNoPacks .= ($InwardSalesNoPacks_VL + $data["NoPacksNew_".$numberofpacks]).",";
					}
				}
				$NoPacks = rtrim($NoPacks,',');
			}else{
				if(isset($data['NoPacksNew'])){
					$NoPacks = $data['NoPacksNew'];
					$SalesNoPacks .= ($data["NoPacks"] - $data['NoPacksNew']);
					//$UpdateInwardNoPacks = ($InwardSalesNoPacks + $NoPacks).",";
					/* echo $InwardSalesNoPacks;
					echo "\n"; 
					echo $UpdateInwardNoPacks = ($InwardSalesNoPacks + $NoPacks);
					echo "\n";
					exit;*/
				} 
				else{
					return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
				}
			}
			
			
			$SalesNoPacks = rtrim($SalesNoPacks,',');
			$UpdateInwardNoPacks = rtrim($UpdateInwardNoPacks,',');
			/* return "SalesNoPacks - ".$SalesNoPacks."----"."UpdateInwardNoPacks - ".$UpdateInwardNoPacks;
			echo "UpdateInwardNoPacks - ".$UpdateInwardNoPacks;
			exit;  */
			
			
			$CheckSalesNoPacks = explode(',', $NoPacks);
			//return $CheckSalesNoPacks; exit;
			$tmp = array_filter($CheckSalesNoPacks);
			if (empty($tmp)) {
				//echo "All zeros!";
				return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
			}
			
			DB::beginTransaction();
			try {
				
				$salesreturnId = DB::table('outletsalesreturn')->insertGetId(
					["SalesReturnNumber"=>$SRNumberId,'OutletId'=>$OutletId, 'PartyId' =>$data["PartyId"], 'OutletPartyId' =>$data["OutletPartyId"], 'Remark'=>$data['Remark'], 'ArticleId'=>$data['ArticleId'], 'NoPacks'=>$NoPacks,'UserId'=>$data['UserId'],'OutletRate'=>$data['ArticleRate'],'CreatedDate'=>date('Y-m-d H:i:s')]
				);
				
				/* DB::table('inward')
				->where('ArticleId', $data['ArticleId'])
				->update(['SalesNoPacks'=>$UpdateInwardNoPacks]); */
				
				if($data['ArticleOpenFlag']==0){
					if( strpos($NoPacks, ',') !== false ) {
						$NoPacks = explode(',', $NoPacks);
						foreach($data['ArticleSelectedColor'] as $key => $vl){
							$numberofpacks = $vl["Id"];
							DB::table('outletsalesreturnpacks')->insertGetId(
								['SalesReturnId'=>$salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId'=>$numberofpacks, 'OutletId'=> $OutletId,'NoPacks'=>$NoPacks[$key], 'PartyId' =>$data["PartyId"], 'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
							);
						}
					}else{
						foreach($data['ArticleSelectedColor'] as $key => $vl){
							$numberofpacks = $vl["Id"];
							DB::table('outletsalesreturnpacks')->insertGetId(
								['SalesReturnId'=>$salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId'=>$numberofpacks, 'OutletId'=> $OutletId,'NoPacks'=>$NoPacks, 'PartyId' =>$data["PartyId"],'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
							);
						}
					}
				}else{
					DB::table('outletsalesreturnpacks')->insertGetId(
						['SalesReturnId'=>$salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId'=>0, 'OutletId'=> $OutletId,'NoPacks'=>$NoPacks, 'PartyId' =>$data["PartyId"],'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
					);
				}
					
				DB::commit();
				return response()->json(array("SRNumberId"=>$SRNumberId, "id"=>"SUCCESS"), 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json(array("id"=>""), 200);
			}
		}
		
	 }
	 
	public function GetOutletSalesReturn($id){
		return DB::select("SELECT concat(otn.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, slr.Id, p.Name, a.ArticleNumber, FirstCharacterConcat(u.Name), slr.NoPacks, slr.CreatedDate FROM `salesreturn` slr inner join party p on p.Id=slr.PartyId inner join article a on a.Id=slr.ArticleId inner join users u on u.Id=slr.UserId inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=otn.FinancialYearId");
		//return DB::select("SELECT slr.Id, p.Name, a.ArticleNumber, FirstCharacterConcat(u.Name), slr.NoPacks, slr.CreatedDate FROM `salesreturn` slr inner join party p on p.Id=slr.PartyId inner join article a on a.Id=slr.ArticleId inner join users u on u.Id=slr.UserId");
	}
	 
	public function PostOutletSalesReturn(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
		//$vnddataTotal = DB::select("SELECT count(*) as Total FROM `salesreturn` slr inner join party p on p.Id=slr.PartyId inner join article a on a.Id=slr.ArticleId inner join users u on u.Id=slr.UserId inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=otn.FinancialYearId");
		$vnddataTotal = DB::select("select count(*) as Total from (SELECT slr.Id FROM `outletsalesreturn` slr inner join outletsalesreturnnumber  sln on sln.Id=slr.SalesReturnNumber inner join party p on p.Id=sln.PartyId inner join article a on a.Id=slr.ArticleId inner join users u on u.Id=slr.UserId inner join outlet o on o.Id=slr.OutletId inner join outletnumber otn on otn.Id=o.OutletNumberId inner join financialyear fn on fn.Id=otn.FinancialYearId group by slr.SalesReturnNumber) as d");
		$vnTotal = $vnddataTotal[0]->Total;
		$length = $data["length"];
		
		if($search['value'] != null && strlen($search['value']) > 2){
			$searchstring = "WHERE d.SalesReturnNumber like '%".$search['value']."%' OR d.Name like '%".$search['value']."%' OR d.ArticleNumber like '%".$search['value']."%' OR cast(d.CreatedDate as char) like '%".$search['value']."%'";
			$vnddataTotalFilter = DB::select("select count(*) as Total from (select * from (SELECT GetTotalOSROrderPieces(son.Id) as TotalSRPieces, DATE_FORMAT(son.CreatedDate, '%d/%m/%Y') as CreatedDate, son.Id, p.Name, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, concat(son.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SalesReturnNumber FROM `outletsalesreturn` s inner join article a on a.Id=s.ArticleId left join outletsalesreturnnumber son on s.SalesReturnNumber=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=s.UserId inner join outlet o on o.Id=s.OutletId group by s.SalesReturnNumber) as ddd order by ddd.Id desc) as d ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "";
			$vnddataTotalFilterValue = $vnTotal;
		}
		
		$column = $data["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "d.SalesReturnNumber";
				break;
			case 2:
				$ordercolumn = "d.Name";
				break;
			case 3:
				$ordercolumn = "d.ArticleNumber";
				break;
			case 5:
				$ordercolumn = "d.CreatedDate";
				break;
			default:
				$ordercolumn = "d.SalesReturnNumber";
				break;
		}
		
		$order = "";
		if($data["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data["order"][0]["dir"];
		}
		
		$vnddata = DB::select("select d.* from (select * from (SELECT GetTotalOSROrderPieces(son.Id) as TotalSRPieces, DATE_FORMAT(son.CreatedDate, '%d/%m/%Y') as CreatedDate, son.Id, p.Name, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, concat(son.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SalesReturnNumber FROM `outletsalesreturn` s inner join article a on a.Id=s.ArticleId left join outletsalesreturnnumber son on s.SalesReturnNumber=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=s.UserId inner join outward o on o.Id=s.OutletId group by s.SalesReturnNumber) as ddd order by ddd.Id desc) as d ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		return array(
				'datadraw'=>$data["draw"],
				'recordsTotal'=>$vnTotal,
				'recordsFiltered'=>$vnddataTotalFilterValue,
				'response' => 'success',
				'startnumber' => $startnumber,
				'search'=>count($vnddata),
				'data' => $vnddata,
			);
	}
	 
	public function DeleteOutletSalesReturn($id){
		$articledata = DB::select("SELECT slr.SalesReturnNumber, slr.ArticleId, slr.NoPacks, a.ArticleNumber, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, c.Colorflag FROM `outletsalesreturn` slr inner join article a on a.Id=slr.ArticleId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where slr.Id ='".$id."'");
		$SalesReturnNoPacks = $articledata[0]->NoPacks;
		$SalesReturnNumber = $articledata[0]->SalesReturnNumber;
		$ArticleColor = json_decode($articledata[0]->ArticleColor);
		//echo $articledata[0]->Colorflag;
		//print_r($articledata); exit;
		if($articledata[0]->ArticleOpenFlag==1){
			$getdata = DB::select("SELECT NoPacks FROM `mixnopacks` where ArticleId='".$articledata[0]->ArticleId."'");
			
			$InwardNoPacks = $getdata[0]->NoPacks;
			$NoPacks = $SalesReturnNoPacks;
			
			if($InwardNoPacks<$NoPacks){
				return response()->json(array("Alreadyexist"=>"true"), 200);
			}else{
			
				$totalnopacks = ($InwardNoPacks - $NoPacks);
				
				/* echo $InwardNoPacks."\n";
				echo $NoPacks."\n";
				echo $totalnopacks."\n";
				print_r($getdata);
				exit; */
				
				
				DB::beginTransaction();
				try {
					DB::table('mixnopacks')
					->where('ArticleId', $articledata[0]->ArticleId)
					->update(['NoPacks'=>$totalnopacks]);
					
					/* DB::table('salesreturnpacks')
					->where('SalesReturnId', '=', $id)
					->delete(); */
					
					DB::table('outletsalesreturn')
					->where('Id', '=', $id)
					->delete();
					
					DB::table('outletsalesreturnpacks')
					->where('SalesReturnId', '=', $id)
					->delete();
					
					DB::commit();
					return response()->json(array("id"=>"SUCCESS"), 200);
				} catch (\Exception $e) {
					DB::rollback();
					return response()->json(array("id"=>""), 200);
				}
			}
		}else{
			/* $getdata = DB::select("SELECT SalesNoPacks FROM `inward` where ArticleId='".$articledata[0]->ArticleId."'");
			$SalesNoPacks = $getdata[0]->SalesNoPacks;
			//echo $SalesReturnNoPacks;
			//print_r($getdata); exit;
			if( strpos($SalesNoPacks, ',') !== false ) {
				$SalesNoPacks = explode(',', $SalesNoPacks);
				$SalesReturnNoPacks = explode(',', $SalesReturnNoPacks);
				$stringcomma = 1;
			}else{
				$stringcomma = 0;
			}
			
			$NoPacks = "";
			$UpdateInwardNoPacks = "";
			if($articledata[0]->Colorflag=="1"){
				foreach($ArticleColor as $key => $vl){
					$numberofpacks = $vl->Id;
					$inwardsale = $SalesNoPacks[$key];
					
					$SalesReturn = $SalesReturnNoPacks[$key];
					//exit;
					if($inwardsale<$SalesReturn){
						return response()->json(array("Alreadyexist"=>"true"), 200);
					}
							
					if($stringcomma==1){
						$NoPacks .= $SalesReturn.",";
						$UpdateInwardNoPacks .= ($inwardsale - $SalesReturn).",";
						
					}else{
						$NoPacks = $SalesReturn.",";
						$UpdateInwardNoPacks = ($SalesNoPacks - $SalesReturn).",";
					}
				}
				$NoPacks = rtrim($NoPacks,',');
				$UpdateInwardNoPacks = rtrim($UpdateInwardNoPacks,',');
			} else{
				if($SalesNoPacks<$SalesReturnNoPacks){
					return response()->json(array("Alreadyexist"=>"true"), 200);
				}
					
				$NoPacks = $SalesReturnNoPacks;
				$UpdateInwardNoPacks = ($SalesNoPacks - $NoPacks);
			} */
			
			/* echo "Flag false";
			echo $NoPacks;
			echo $UpdateInwardNoPacks;
			print_r($getdata); exit; */
			
			DB::beginTransaction();
			try {
				/* DB::table('inward')
				->where('ArticleId', $articledata[0]->ArticleId)
				->update(['SalesNoPacks'=>$UpdateInwardNoPacks]); */
				
				DB::table('outletsalesreturnpacks')
				->where('SalesReturnId', '=', $id)
				->delete();
				
				DB::table('outletsalesreturn')
					->where('Id', '=', $id)
					->delete();
				
				DB::commit();
				return response()->json(array("SRNumberId"=>$SalesReturnNumber,"id"=>"SUCCESS"), 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json(array("id"=>""), 200);
			}
		}	
		//return response()->json("SUCCESS", 200);
	}
	
	public function GetOutletSalesReturnChallan($Id){
		//$getdata = DB::select("select OutletPartyId from salesreturn where Id='".$Id."'");
		$getdata = DB::select("SELECT srn.OutletPartyId FROM `outletsalesreturn` s inner join outletsalesreturnnumber srn on srn.Id=s.SalesReturnNumber where s.SalesReturnNumber ='".$Id."'");
		//return $getdata; exit;
		if($getdata[0]->OutletPartyId==0){
			$sl = 0;
			//$getsrchallen = DB::select("SELECT concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, \"%Y-%m-%d\") as SRDate, slr.Remark, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate  FROM `salesreturn` slr inner join party p on p.Id = slr.PartyId inner join article a on a.Id=slr.ArticleId inner join po po on po.ArticleId=a.Id inner join category c on c.Id=po.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id where slr.Id='".$Id."'");
			//$getsrchallen = DB::select("SELECT concat(otn.OutwardNumber,'/' ,fn2.StartYear,'-',fn2.EndYear) as OutwardNumber, concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, '%Y-%m-%d') as SRDate, srn.Remarks, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate  FROM `salesreturn` slr inner join article a on a.Id=slr.ArticleId inner join category c on c.Id=a.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join party p on p.Id = srn.PartyId  inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn2 on fn2.Id=otn.FinancialYearId where slr.SalesReturnNumber='".$Id."'");
			$getsrchallen = DB::select("SELECT concat(otn.OutletNumber,'/' ,fn2.StartYear,'-',fn2.EndYear) as OutletNumber, concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, '%Y-%m-%d') as SRDate, srn.Remarks, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate11, slr.OutletRate as SRArticleRate  FROM `outletsalesreturn` slr inner join article a on a.Id=slr.ArticleId inner join category c on c.Id=a.CategoryId inner join users u on u.Id=slr.UserId inner join outletsalesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join party p on p.Id = srn.PartyId  inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join outlet o on o.Id=slr.OutletId inner join outletnumber otn on otn.Id=o.OutletNumberId inner join financialyear fn2 on fn2.Id=otn.FinancialYearId where slr.SalesReturnNumber='".$Id."'");
		}
		else{
			$sl = 1;
			//$getsrchallen = DB::select("SELECT concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, \"%Y-%m-%d\") as SRDate, slr.Remark, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, pp.Name as OutletPartyName, pp.Address as OutletPartyAddress, pp.GSTNumber as OutletPartyGSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate  FROM `salesreturn` slr inner join party p on p.Id = slr.PartyId inner join party pp on pp.Id=slr.PartyId inner join article a on a.Id=slr.ArticleId inner join po po on po.ArticleId=a.Id inner join category c on c.Id=po.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id where slr.Id='".$Id."'");
			//$getsrchallen = DB::select("SELECT concat(otn.OutwardNumber,'/' ,fn2.StartYear,'-',fn2.EndYear) as OutwardNumber,concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, '%Y-%m-%d') as SRDate, srn.Remarks, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, pp.Name as OutletPartyName, pp.Address as OutletPartyAddress, pp.GSTNumber as OutletPartyGSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate  FROM `salesreturn` slr inner join article a on a.Id=slr.ArticleId inner join category c on c.Id=a.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join party p on p.Id = srn.PartyId inner join party pp on pp.Id=srn.OutletPartyId inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn2 on fn2.Id=otn.FinancialYearId where slr.SalesReturnNumber='".$Id."'");
			$getsrchallen = DB::select("SELECT concat(otn.OutletNumber,'/' ,fn2.StartYear,'-',fn2.EndYear) as OutletNumber,concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, '%Y-%m-%d') as SRDate, srn.Remarks, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, pp.Name as OutletPartyName, pp.Address as OutletPartyAddress, pp.GSTNumber as OutletPartyGSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate11, slr.OutletRate as SRArticleRate  FROM `outletsalesreturn` slr inner join article a on a.Id=slr.ArticleId inner join category c on c.Id=a.CategoryId inner join users u on u.Id=slr.UserId inner join outletsalesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join party p on p.Id = srn.PartyId inner join party pp on pp.Id=srn.OutletPartyId inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join outlet o on o.Id=slr.OutletId inner join outletnumber otn on otn.Id=o.OutletNumberId inner join financialyear fn2 on fn2.Id=otn.FinancialYearId where slr.SalesReturnNumber='".$Id."'");
		}

/* $sl = 0;
			//$getsrchallen = DB::select("SELECT concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, \"%Y-%m-%d\") as SRDate, slr.Remark, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, pp.Name as OutletPartyName, pp.Address as OutletPartyAddress, pp.GSTNumber as OutletPartyGSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate  FROM `salesreturn` slr inner join party p on p.Id = slr.PartyId inner join party pp on pp.Id=slr.PartyId inner join article a on a.Id=slr.ArticleId inner join po po on po.ArticleId=a.Id inner join category c on c.Id=po.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id where slr.Id='".$Id."'");
			$getsrchallen = DB::select("SELECT concat(otn.OutwardNumber,'/' ,fn2.StartYear,'-',fn2.EndYear) as OutwardNumber, concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, \"%Y-%m-%d\") as SRDate, slr.Remark, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate  FROM `salesreturn` slr inner join party p on p.Id = slr.PartyId inner join article a on a.Id=slr.ArticleId inner join po po on po.ArticleId=a.Id inner join category c on c.Id=po.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn2 on fn2.Id=otn.FinancialYearId where slr.SalesReturnNumber='".$Id."'");
 */ 			
		
		//print_r($getsrchallen); exit;
		$challendata = [];
		$TotalNoPacks = 0;
		$TotalAmount = 0;
		$TotalQuantityPic = 0;
		

		foreach($getsrchallen as $vl){
			//echo $vl->InwardDate;
			$Name = $vl->Name;
			$UserName = $vl->UserName;
			$Address = $vl->Address;
			$GSTNumber = $vl->GSTNumber;
			if($sl==1){
				$outletdata = true;
				$OutletPartyName = $vl->OutletPartyName;
				$OutletPartyAddress = $vl->OutletPartyAddress;
				$OutletPartyGSTNumber = $vl->OutletPartyGSTNumber;
			} else {
				$outletdata = false;
				$OutletPartyName = "";
				$OutletPartyAddress = "";
				$OutletPartyGSTNumber = "";
			}
			
			$ArticleNumber = $vl->ArticleNumber;
			$ArticleOpenFlag = $vl->ArticleOpenFlag;
			$Title = $vl->Title;
			$SRDate = $vl->SRDate;
			$Id = $vl->Id;
			$Remark = $vl->Remarks;
			$SalesReturnNumber = $vl->SalesReturnNumber;
			$OutletNumber = $vl->OutletNumber;
			$NoPacks = $vl->NoPacks;
			
			if($ArticleOpenFlag==0){
				$ArticleRatio = $vl->ArticleRatio;
				
				if( strpos($NoPacks, ',') !== true ) {
					$countNoSet = array_sum(explode(",",$NoPacks));
					$TotalNoPacks+= array_sum(explode(",",$NoPacks));
					$TotalArticleRatio = array_sum(explode(",",$ArticleRatio));
				}else{
					$countNoSet = $NoPacks;
					$TotalNoPacks+= $NoPacks;
					$TotalArticleRatio = $ArticleRatio;
				}
				
				//$QuantityPic = $TotalArticleRatio * $countNoSet;
				//$TotalQuantityPic += $QuantityPic;
			
				$ArticleRate = $vl->SRArticleRate;
				$Amount = $countNoSet * $ArticleRate;
				$TotalAmount += $Amount;
				
				$getcolor = json_decode($vl->ArticleColor);
				$getsize = json_decode($vl->ArticleSize);
				
				$ArticleColor = "";
				foreach($getcolor as $vl){
					$ArticleColor .= $vl->Name.",";
				}
				$ArticleColor = rtrim($ArticleColor,',');
				
				$ArticleSize = "";
				foreach($getsize as $vl){
					$ArticleSize .= $vl->Name.",";
				}
				$ArticleSize = rtrim($ArticleSize,',');
			}else{
				$countNoSet = $NoPacks;
				//$QuantityPic = $countNoSet;
				//$TotalQuantityPic += $QuantityPic;
				$TotalNoPacks+= $NoPacks;
				
				$ArticleRatio = "";
				$ArticleRate = $vl->SRArticleRate;
				$Amount = $countNoSet * $ArticleRate;
				$TotalAmount += $Amount;
				$ArticleColor = "";
				$ArticleSize = "";
			}
			$challendata[] = json_decode(json_encode(array("OutletNumber"=>$OutletNumber,"SalesReturnNumber"=>$SalesReturnNumber,"UserName"=>$UserName, "SRDate"=>$SRDate,"Id"=>$Id,"Name"=>$Name,"Address"=>$Address,"Outletdata"=>$outletdata,"OutletPartyName"=>$OutletPartyName,"OutletPartyAddress"=>$OutletPartyAddress, "OutletPartyGSTNumber"=>$OutletPartyGSTNumber, "GSTNumber"=>$GSTNumber,"Remark"=>$Remark,"ArticleNumber"=>$ArticleNumber,"Title"=>$Title, "ArticleRatio"=>$ArticleRatio, "QuantityInSet"=>$NoPacks, "ArticleRate"=>number_format($ArticleRate, 2), "Amount"=>number_format($Amount, 2), "ArticleColor"=>$ArticleColor, "ArticleSize"=>$ArticleSize)), false);
		}
		
		//echo "<pre>"; print_r($challendata); exit;
		$as  = array($challendata, array("TotalNoPacks"=>$TotalNoPacks, "TotalAmount"=>number_format($TotalAmount, 2)));
		//echo "<pre>"; print_r($as); exit;
		return $as;
	 
	 }
	 
}
