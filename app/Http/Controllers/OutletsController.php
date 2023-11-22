<?php

namespace App\Http\Controllers;

use App\Outlet;
use App\Transportoutlet;
use App\Party;
use App\OutletNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\OutletSalesreturn;
use App\Article;
use App\TransportOutwardpacks;
use App\OutletSalesReturnPacks;
use App\Outletimport;
use App\Outward;
use App\UserLogs;
use App\OutwardNumber;
use App\Users;
use App\Artstockstatus;

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


	//yashvi

	// public function GetArticleofOutlet($PartyId)
// {
//     // $articlesArray = DB::select('
//     //     SELECT `Artstockstatus`.`ArticleId`, `Artstockstatus`.`ArticleNumber`
//     //     FROM `Artstockstatus`
//     //     WHERE `Artstockstatus`.`outletId` = ? 
//     // ', [$PartyId]);

	// 	$articlesArray = DB::table('Artstockstatus')
//     ->select('ArticleId', 'ArticleNumber')
//     ->where('outletId', $PartyId)
//     ->get();


	//     // $jsonData = array_values($articlesArray);
// 	$jsonData = $articlesArray->toArray();


	//     return $jsonData;
// }





	// public function GetArticleofOutlet($PartyId)
	// {
	// 	$articlesArray = DB::select('(select `article`.`ArticleNumber`, `article`.`Id` as `ArticleId` from `transportoutlet` right join `outward` on `transportoutlet`.`OutwardNumberId` = `outward`.`OutwardNumberId` inner join `article` on `article`.`Id` = `outward`.`ArticleId`  where `transportoutlet`.`TransportStatus` = 1 )  union (select `article`.`ArticleNumber`, `article`.`Id` as `ArticleId` from `transportoutwardpacks` inner join `article` on `article`.`Id` = `transportoutwardpacks`.`ArticleId` where `transportoutwardpacks`.`OutwardId` = 0 )   order by `ArticleId` asc');
	// 	$collectionArticles = collect($articlesArray);
	// 	$articles = $collectionArticles->unique()->values()->all();
	// 	foreach ($articles as $key => $article) {
	// 		$objectArticle = $article;
	// 		$articleArray = (array) $article;
	// 		//$allRecords = DB::select('(select `outletsalesreturn`.`NoPacks` as `NoPacks`, 1 as type, `outletsalesreturnnumber`.`CreatedDate` as `SortDate` from `outletsalesreturn` inner join `outletsalesreturnnumber` on `outletsalesreturn`.`SalesReturnNumber` = `outletsalesreturnnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletsalesreturn`.`OutletPartyId` = ' . $PartyId . ')) union (select `outlet`.`NoPacks` as `NoPacks`, 1 as type, `outletnumber`.`CreatedDate` as `SortDate` from `outlet` inner join `outletnumber` on `outlet`.`OutletNumberId` = `outletnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletnumber`.`PartyId` = ' . $PartyId . ')) union (select `outward`.`NoPacks` as `NoPacks`, 0 as type, `outwardnumber`.`created_at` as `SortDate` from `outward` inner join `transportoutlet` on `outward`.`OutwardNumberId` = `transportoutlet`.`OutwardNumberId` inner join `outwardnumber` on `outward`.`OutwardNumberId` = `outwardnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `transportoutlet`.`TransportStatus` = 1 and `outward`.`PartyId` = ' . $PartyId . ')) order by `SortDate` asc');		    
	// 		$getdata = $this->GetOutletSingleArticle($PartyId, $articleArray['ArticleId'], '$outletId');
	// 		if (!empty($getdata)) {
	// 			$SalesNoPacks = $getdata[0]->SalesNoPacks;
	// 			$numbersArray = array_map('intval', explode(',', $SalesNoPacks));
	// 			$totalSales = array_sum($numbersArray);
	// 			$objectArticle->COUNTVL2 = $totalSales;
	// 		}
	// 	}
	// 	$jsonData = array_values($articles);
	// 	$filteredData = array_filter($jsonData, function ($item) {
	// 		return isset($item->COUNTVL2) && $item->COUNTVL2 !== 0;
	// 	});
	// 	// If you want the filtered data to be reindexed, use array_values
	// 	$filteredData = array_values($filteredData);
	// 	return $filteredData;
	// }


	//New

	public function GetArticleofOutlet($PartyId)
	{
		$articlesArray = DB::select('
        SELECT `artstockstatus`.`ArticleId`, `artstockstatus`.`ArticleNumber`, `artstockstatus`.`TotalPieces`
        FROM `artstockstatus`
        WHERE `artstockstatus`.`outletId` = ? 
    	', [$PartyId]);


		$jsonData = array_values($articlesArray);
		$filteredData = array_filter($jsonData, function ($item) {
			return isset($item->TotalPieces) && $item->TotalPieces !== "0";
		});
		$jsonData = array_values($filteredData);
		return $jsonData;
	
	}

	public function GetOutletSingleArticle($PartyId, $ArtId, $OutletPartyId)
	{
		$data = DB::select("select ar.ArticleNumber, CASE WHEN (oi.ArticleColor IS NULL) THEN ar.ArticleColor ELSE oi.ArticleColor END AS `ArticleColor`, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, CASE WHEN (art.ArticleRate IS NULL) THEN ar.ArticleRate ELSE art.ArticleRate END  as ArticleRate , p.OutletArticleRate as OutletArticleRate, c.Colorflag, c.Title as Category ,  ddd.ArticleId, ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks + d.OutletSalesReturn_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks + d.OutletSalesReturn_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN D.OutletSalesReturn_NoPacks IS NULL THEN '0' ELSE D.OutletSalesReturn_NoPacks END) as OutletSalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `transportoutwardpacks` onp inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='" . $ArtId . "' and onp.PartyId='" . $PartyId . "' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='" . $ArtId . "' and onp.PartyId='" . $PartyId . "' group by onp.ColorId) AS B ON A.ColorId=B.ColorId LEFT JOIN ( SELECT sum(srp.NoPacks) as SalesReturn_NoPacks, srp.ColorId, srp.ArticleId, srp.Id, c.Colorflag  FROM `salesreturnpacks` srp inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='" . $ArtId . "' and srp.PartyId='" . $PartyId . "' group by srp.ColorId) AS C ON A.ColorId=C.ColorId LEFT JOIN ( select sum(f.NoPacks) as OutletSalesReturn_NoPacks, f.ColorId, f.ArticleId, f.OutletId, f.Id, f.Colorflag from (SELECT srp.NoPacks, srp.ColorId, srp.ArticleId, srp.OutletId, srp.Id, c.Colorflag FROM `outletsalesreturnpacks` srp inner join outletsalesreturn osr on osr.OutletId=srp.OutletId inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='" . $ArtId . "' and osr.OutletPartyId='" . $PartyId . "' group by srp.Id) as f group by f.ColorId) AS D ON A.ColorId=D.ColorId) as d group by d.ArticleId) as ddd inner join article ar on ar.Id=ddd.ArticleId inner join category c on c.Id=ar.CategoryId left join articlerate art on art.ArticleId=ar.Id left join party p on p.Id='" . $PartyId . "' left join outletimport oi on oi.ArticleId='" . $ArtId . "' and oi.PartyId = '" . $PartyId . "'");


		if (!empty($data)) {
			$artucleratedata = DB::table('articlerate')->where('ArticleId', $ArtId)->first();
			if ($artucleratedata) {
				$data[0]->ArticleRate = (int) $artucleratedata->ArticleRate + (int) $data[0]->OutletArticleRate;
			} else {
				// Handle the case when $artucleratedata is empty
			}
		} else {
			$data = [];
		}

		$allRecords = DB::select('(select `outletsalesreturn`.`NoPacks` as `NoPacks`, 2 as type, `outletsalesreturnnumber`.`CreatedDate` as `SortDate` from `outletsalesreturn` inner join `outletsalesreturnnumber` on `outletsalesreturn`.`SalesReturnNumber` = `outletsalesreturnnumber`.`Id` where (`ArticleId` = ' . $ArtId . ' and `outletsalesreturn`.`OutletPartyId` = ' . $PartyId . ')) union (select `outlet`.`NoPacks` as `NoPacks`, 1 as type, `outletnumber`.`CreatedDate` as `SortDate` from `outlet` inner join `outletnumber` on `outlet`.`OutletNumberId` = `outletnumber`.`Id` where (`ArticleId` = ' . $ArtId . ' and `outletnumber`.`PartyId` = ' . $PartyId . ')) union (select `outward`.`NoPacks` as `NoPacks`, 0 as type, `outwardnumber`.`created_at` as `SortDate` from `outward` inner join `transportoutlet` on `outward`.`OutwardNumberId` = `transportoutlet`.`OutwardNumberId` inner join `outwardnumber` on `outward`.`OutwardNumberId` = `outwardnumber`.`Id` where (`ArticleId` = ' . $ArtId . ' and `transportoutlet`.`TransportStatus` = 1 and `outward`.`PartyId` = ' . $PartyId . ')) union (select `salesreturn`.`NoPacks` as `NoPacks`, 3 as type ,`salesreturnnumber`.`CreatedDate` as `SortDate` from `outward` inner join `salesreturn` on `salesreturn`.`OutwardId` = `outward`.`Id` inner join `salesreturnnumber` on `salesreturnnumber`.`Id` = `salesreturn`.`SalesReturnNumber` where (`outward`.`PartyId` = ' . $PartyId . ' and `outward`.`ArticleId` = ' . $ArtId . ')) order by `SortDate` asc');
		if (!isset($allRecords[0])) {
			$outletArticle = Outletimport::where('ArticleId', $ArtId)->where('PartyId', $PartyId)->first();
			if ($outletArticle) {
				$outletArticleColors = json_decode($outletArticle->ArticleColor);
			} else {
				$articleRecord = Article::where('Id', $ArtId)->first();
				$outletArticleColors = json_decode($articleRecord->ArticleColor);
			}
			$outletArticleColors = (array) $outletArticleColors;

			if (!empty($data)) {
				if ($data[0]->ArticleColor) {
					$SalesNoPacks = [];
					foreach ($outletArticleColors as $makearray) {
						array_push($SalesNoPacks, 0);
					}
					$transportOutwardpacks = TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $ArtId)->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
					$TotalTransportOutwardpacks = 0;
					if (count($transportOutwardpacks) != 0) {
						// $collectionTransportOutwardpacks = collect($transportOutwardpacks);
						// $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
						foreach ($transportOutwardpacks as $getTransportOutwardpack) {
							$outletArticle = Outletimport::where('ArticleId', $ArtId)->where('PartyId', $PartyId)->first();
							if ($outletArticle) {
								$articleColors = json_decode($outletArticle->ArticleColor);
							} else {
								$article = Article::select('ArticleColor')->where('Id', $ArtId)->first();
								$articleColors = json_decode($article['ArticleColor']);
							}
							$count = 0;
							foreach ($articleColors as $articlecolor) {
								if ($articlecolor->Id == $getTransportOutwardpack->ColorId) {
									if (!isset($SalesNoPacks[$count])) {
										array_push($SalesNoPacks, 0);
									}
									$SalesNoPacks[$count] = $SalesNoPacks[$count] + $getTransportOutwardpack->NoPacks;
								}
								$count = $count + 1;
							}
						}
					}
					$newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
					$data[0]->SalesNoPacks = $newimplodeSalesNoPacks;
				} else {
					$transportOutwardpacks = TransportOutwardpacks::select('NoPacks')->where('ArticleId', $ArtId)->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
					$TotalTransportOutwardpacks = 0;
					if (count($transportOutwardpacks) != 0) {
						// $collectionTransportOutwardpacks = collect($transportOutwardpacks);
						// $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
						foreach ($transportOutwardpacks as $getTransportOutwardpack) {
							$TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
						}
					}
					$data[0]->SalesNoPacks = $TotalTransportOutwardpacks;
				}
			}

		} else {
			if (!empty($data)) {
				if ($data[0]->ArticleColor) {
					$SalesNoPacks = [];
					foreach (explode(",", $allRecords[0]->NoPacks) as $makearray) {
						array_push($SalesNoPacks, 0);
					}
					$transportOutwardpacks = TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $ArtId)->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
					$TotalTransportOutwardpacks = 0;
					if (count($transportOutwardpacks) != 0) {
						// $collectionTransportOutwardpacks = collect($transportOutwardpacks);
						// $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
						foreach ($transportOutwardpacks as $getTransportOutwardpack) {
							$outletArticle = Outletimport::where('ArticleId', $ArtId)->where('PartyId', $PartyId)->first();
							if ($outletArticle) {
								$articleColors = json_decode($outletArticle->ArticleColor);
							} else {
								$article = Article::select('ArticleColor')->where('Id', $ArtId)->first();
								$articleColors = json_decode($article['ArticleColor']);
							}
							$count = 0;
							foreach ($articleColors as $articlecolor) {
								if ($articlecolor->Id == $getTransportOutwardpack->ColorId) {
									if (!isset($SalesNoPacks[$count])) {
										array_push($SalesNoPacks, 0);
									}
									$SalesNoPacks[$count] = $SalesNoPacks[$count] + $getTransportOutwardpack->NoPacks;
								}
								$count = $count + 1;
							}
						}
					}
					foreach ($allRecords as $allRecord) {
						for ($i = 0; $i < count(explode(",", $allRecord->NoPacks)); $i++) {
							if (!isset($SalesNoPacks[$i])) {
								array_push($SalesNoPacks, 0);
							}
							$noPacks = explode(",", $allRecord->NoPacks);
							if ($allRecord->type == 0) {
								$SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
							} else if ($allRecord->type == 1) {
								$SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
							} else if ($allRecord->type == 2) {
								$SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
							} else if ($allRecord->type == 3) {
								$SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
							}
						}
					}
					$newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
					$data[0]->SalesNoPacks = $newimplodeSalesNoPacks;
				} else {
					$transportOutwardpacks = TransportOutwardpacks::select('NoPacks')->where('ArticleId', $ArtId)->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
					$TotalTransportOutwardpacks = 0;
					if (count($transportOutwardpacks) != 0) {
						// $collectionTransportOutwardpacks = collect($transportOutwardpacks);
						// $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
						foreach ($transportOutwardpacks as $getTransportOutwardpack) {
							$TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
						}
					}
					$TotalInwardPacks = $TotalTransportOutwardpacks;
					$TotalOutwardPacks = 0;
					foreach ($allRecords as $allRecord) {
						if ($allRecord->type == 0) {
							$TotalInwardPacks = $TotalInwardPacks + $allRecord->NoPacks;
						} else if ($allRecord->type == 1) {
							$TotalOutwardPacks = $TotalOutwardPacks + $allRecord->NoPacks;
						} else if ($allRecord->type == 2) {
							$TotalInwardPacks = $TotalInwardPacks + $allRecord->NoPacks;
						} else if ($allRecord->type == 3) {
							$TotalOutwardPacks = $TotalOutwardPacks + $allRecord->NoPacks;
						}
					}
					$data[0]->SalesNoPacks = $TotalInwardPacks - $TotalOutwardPacks;
				}
			}
		}
		return $data;
	}


	//NEW

	//WORKED
// 	public function GetArticleofOutlet($PartyId)
// 	{
// 		$articlesArray = DB::select('(select `article`.`ArticleNumber`, `article`.`Id` as `ArticleId` from `transportoutlet` right join `outward` on `transportoutlet`.`OutwardNumberId` = `outward`.`OutwardNumberId` inner join `article` on `article`.`Id` = `outward`.`ArticleId`  where `transportoutlet`.`TransportStatus` = 1 )  union (select `article`.`ArticleNumber`, `article`.`Id` as `ArticleId` from `transportoutwardpacks` inner join `article` on `article`.`Id` = `transportoutwardpacks`.`ArticleId` where `transportoutwardpacks`.`OutwardId` = 0 )   order by `ArticleId` asc');

	// 		$collectionArticles = collect($articlesArray);
// 		$articles  = $collectionArticles->unique()->values()->all();
// 		foreach ($articles as $key => $article) {



	// 			$objectArticle = $article;
// 			$articleArray = (array)$article;



	// 			$allRecords = DB::select('(select `outletsalesreturn`.`NoPacks` as `NoPacks`, 1 as type, `outletsalesreturnnumber`.`CreatedDate` as `SortDate` from `outletsalesreturn` inner join `outletsalesreturnnumber` on `outletsalesreturn`.`SalesReturnNumber` = `outletsalesreturnnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletsalesreturn`.`OutletPartyId` = ' . $PartyId . ')) union (select `outlet`.`NoPacks` as `NoPacks`, 1 as type, `outletnumber`.`CreatedDate` as `SortDate` from `outlet` inner join `outletnumber` on `outlet`.`OutletNumberId` = `outletnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletnumber`.`PartyId` = ' . $PartyId . ')) union (select `outward`.`NoPacks` as `NoPacks`, 0 as type, `outwardnumber`.`created_at` as `SortDate` from `outward` inner join `transportoutlet` on `outward`.`OutwardNumberId` = `transportoutlet`.`OutwardNumberId` inner join `outwardnumber` on `outward`.`OutwardNumberId` = `outwardnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `transportoutlet`.`TransportStatus` = 1 and `outward`.`PartyId` = ' . $PartyId . ')) order by `SortDate` asc');

	// 			// $allRecords = DB::select('(select `outletsalesreturn`.`NoPacks` AS `NoPacks`,1 AS TYPE,`outletsalesreturnnumber`.`CreatedDate` AS `SortDate` from `outletsalesreturn` inner join `outletsalesreturnnumber` on `outletsalesreturn`.`SalesReturnNumber` = `outletsalesreturnnumber`.`Id`)union(select `outlet`.`NoPacks` AS `NoPacks`,1 AS TYPE,`outletnumber`.`CreatedDate` AS `SortDate` from `outlet`inner join `outletnumber` ON `outlet`.`OutletNumberId` = `outletnumber`.`Id`)union(select `outward`.`NoPacks` AS `NoPacks`, 0 AS TYPE,`outwardnumber`.`created_at` AS `SortDate`from `outward` inner join `transportoutlet` ON `outward`.`OutwardNumberId` = `transportoutlet`.`OutwardNumberId`inner join `outwardnumber` ON `outward`.`OutwardNumberId` = `outwardnumber`.`Id` where(`transportoutlet`.`TransportStatus` = 1)order by`SortDate` asc )');

	// 			if (!isset($allRecords[0])) {
// 				$outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->where('PartyId', $PartyId)->first();
// 				if ($outletArticle) {
// 					$outletArticleColors = json_decode($outletArticle->ArticleColor);
// 				} else {
// 					$articleRecord = Article::where('Id', $articleArray['ArticleId'])->first();

	// 					$outletArticleColors  = json_decode($articleRecord->ArticleColor);
// 				}
// 				$outletArticleColors =  (array)$outletArticleColors;
// 				if (count($outletArticleColors) > 0) {
// 					$SalesNoPacks = [];
// 					foreach ($outletArticleColors as $makearray) {
// 						array_push($SalesNoPacks, 0);
// 					}
// 					$transportOutwardpacks =  TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
// 					if (count($transportOutwardpacks) != 0) {
// 						$collectionTransportOutwardpacks = collect($transportOutwardpacks);
// 						$getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
// 						foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
// 							$outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->where('PartyId', $PartyId)->first();
// 							if ($outletArticle) {
// 								$articleColors = json_decode($outletArticle->ArticleColor);
// 							} else {
// 								$article = Article::select('ArticleColor')->where('Id', $articleArray['ArticleId'])->first();
// 								$articleColors = json_decode($article['ArticleColor']);
// 							}
// 							$count = 0;
// 							foreach ($articleColors as $articlecolor) {
// 								if ($articlecolor->Id == $getTransportOutwardpack->ColorId) {
// 									if (!isset($SalesNoPacks[$count])) {
// 										array_push($SalesNoPacks, 0);
// 									}
// 									$SalesNoPacks[$count] = $SalesNoPacks[$count] + $getTransportOutwardpack->NoPacks;
// 								}
// 								$count = $count + 1;
// 							}
// 						}
// 					}
// 					if (array_sum($SalesNoPacks) > 0) {
// 						$newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
// 						$objectArticle->STOCKS =  $newimplodeSalesNoPacks;
// 						$objectArticle->COUNTVL =  $newimplodeSalesNoPacks;
// 					} else {
// 						unset($articles[$key]);
// 					}
// 				} else {
// 					$transportOutwardpacks =  TransportOutwardpacks::select('NoPacks')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
// 					$TotalTransportOutwardpacks = 0;
// 					if (count($transportOutwardpacks) != 0) {
// 						$collectionTransportOutwardpacks = collect($transportOutwardpacks);
// 						$getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
// 						foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
// 							$TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
// 						}
// 					}
// 					$TotalInwardPacks = $TotalTransportOutwardpacks;
// 					$TotalOutwardPacks = 0;
// 					if (($TotalInwardPacks - $TotalOutwardPacks) > 0) {
// 						$objectArticle->STOCKS =  $TotalInwardPacks - $TotalOutwardPacks;
// 						$objectArticle->COUNTVL =   $TotalInwardPacks - $TotalOutwardPacks;
// 					} else {
// 						unset($articles[$key]);
// 					}
// 				}
// 			} else {
// 				if (strpos($allRecords[0]->NoPacks, ',')) {
// 					$SalesNoPacks = [];
// 					foreach (explode(",", $allRecords[0]->NoPacks) as $makearray) {
// 						array_push($SalesNoPacks, 0);
// 					}
// 					$transportOutwardpacks =  TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
// 					$TotalTransportOutwardpacks = 0;
// 					if (count($transportOutwardpacks) != 0) {
// 						$collectionTransportOutwardpacks = collect($transportOutwardpacks);
// 						$getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
// 						foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
// 							$outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->where('PartyId', $PartyId)->first();
// 							if ($outletArticle) {
// 								$articleColors = json_decode($outletArticle->ArticleColor);
// 							} else {
// 								$article = Article::select('ArticleColor')->where('Id', $articleArray['ArticleId'])->first();
// 								$articleColors = json_decode($article['ArticleColor']);
// 							}
// 							$count = 0;
// 							foreach ($articleColors as $articlecolor) {
// 								if ($articlecolor->Id == $getTransportOutwardpack->ColorId) {
// 									if (!isset($SalesNoPacks[$count])) {
// 										array_push($SalesNoPacks, 0);
// 									}
// 									$SalesNoPacks[$count] = $SalesNoPacks[$count] + $getTransportOutwardpack->NoPacks;
// 								}
// 								$count = $count + 1;
// 							}
// 						}
// 					}
// 					foreach ($allRecords as  $allRecord) {
// 						for ($i = 0; $i < count(explode(",", $allRecord->NoPacks)); $i++) {
// 							$noPacks = explode(",", $allRecord->NoPacks);
// 							if ($allRecord->type == 0) {
// 								$SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
// 							} else if ($allRecord->type == 1) {
// 								$SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
// 							} else if ($allRecord->type == 2) {
// 								$SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
// 							}
// 						}
// 					}
// 					if (array_sum($SalesNoPacks) > 0) {
// 						$newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
// 						$objectArticle->STOCKS =  $newimplodeSalesNoPacks;
// 						$objectArticle->COUNTVL =  $newimplodeSalesNoPacks;
// 					} else {
// 						unset($articles[$key]);
// 					}
// 				} else {
// 					$transportOutwardpacks =  TransportOutwardpacks::select('NoPacks')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
// 					$TotalTransportOutwardpacks = 0;
// 					if (count($transportOutwardpacks) != 0) {
// 						$collectionTransportOutwardpacks = collect($transportOutwardpacks);
// 						$getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
// 						foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
// 							$TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
// 						}
// 					}
// 					$TotalInwardPacks = $TotalTransportOutwardpacks;
// 					$TotalOutwardPacks = 0;
// 					foreach ($allRecords as  $allRecord) {
// 						if ($allRecord->type == 0) {
// 							$TotalInwardPacks = $TotalInwardPacks + (int)$allRecord->NoPacks;
// 						} else if ($allRecord->type == 1) {
// 							$TotalOutwardPacks = $TotalOutwardPacks + (int)$allRecord->NoPacks;
// 						} else if ($allRecord->type == 2) {
// 							$TotalInwardPacks = $TotalInwardPacks + (int)$allRecord->NoPacks;
// 						}
// 					}
// 					if (($TotalInwardPacks - $TotalOutwardPacks) > 0) {
// 						$objectArticle->STOCKS =  $TotalInwardPacks - $TotalOutwardPacks + 1;
// 						$objectArticle->COUNTVL =   $TotalInwardPacks - $TotalOutwardPacks;
// 					} else {
// 						unset($articles[$key]);
// 					}

	// 				// 	$TotalInwardPacks = $TotalTransportOutwardpacks;
// 				// $TotalOutwardPacks = 0;
// 				// foreach ($allRecords as  $allRecord) {
// 				// 	if ($allRecord->type == 0) {
// 				// 		$TotalInwardPacks = $TotalInwardPacks + $allRecord->NoPacks;
// 				// 	} else if ($allRecord->type == 1) {
// 				// 		$TotalOutwardPacks = $TotalOutwardPacks + $allRecord->NoPacks;
// 				// 	} else if ($allRecord->type == 2) {
// 				// 		$TotalInwardPacks = $TotalInwardPacks + $allRecord->NoPacks;
// 				// 	} else if ($allRecord->type == 3) {
// 				// 		$TotalOutwardPacks = $TotalOutwardPacks + $allRecord->NoPacks;
// 				// 	}
// 				// }
// 				// $objectArticle->STOCKS =  $TotalInwardPacks - $TotalOutwardPacks;
// 				}
// 			}
// 		}
// 		return array_values($articles);
// 	}


	// 	public function GetOutletSingleArticle($PartyId, $ArtId, $OutletPartyId)
// 	{
// 		// $updatedartucleratedata = DB::select("SELECT o.Id , o.PartyDiscount, o.OutwardNumberId, o.OutwardBox, o.OutwardRate,o.OutwardWeight, owdn.OutwardNumber, owdn.OutwardDate, owdn.GSTAmount, owdn.GSTPercentage, owdn.GSTType, o.NoPacks, a.ArticleNumber, a.Id as ArticleId,c.Title as Category, concat(owdn.OutwardNumber) as OW_Number_FinancialYear FROM `outward` o inner join outwardnumber owdn on o.OutwardNumberId=owdn.Id inner join article a on a.Id=o.ArticleId inner join category c on c.Id=a.CategoryId inner join financialyear fn on fn.Id=owdn.FinancialYearId where o.OutwardNumberId='5100'");
// 		// return $updatedartucleratedata;

	// 		$data = DB::select("select ar.ArticleNumber, CASE WHEN (oi.ArticleColor IS NULL) THEN ar.ArticleColor ELSE oi.ArticleColor END AS `ArticleColor`, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, CASE WHEN (art.ArticleRate IS NULL) THEN ar.ArticleRate ELSE art.ArticleRate END  as ArticleRate , p.OutletArticleRate as OutletArticleRate, c.Colorflag, c.Title as Category ,  ddd.ArticleId, ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks + d.OutletSalesReturn_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks + d.OutletSalesReturn_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN D.OutletSalesReturn_NoPacks IS NULL THEN '0' ELSE D.OutletSalesReturn_NoPacks END) as OutletSalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `transportoutwardpacks` onp inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='" . $ArtId . "' and onp.PartyId='" . $PartyId . "' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='" . $ArtId . "' and onp.PartyId='" . $PartyId . "' group by onp.ColorId) AS B ON A.ColorId=B.ColorId LEFT JOIN ( SELECT sum(srp.NoPacks) as SalesReturn_NoPacks, srp.ColorId, srp.ArticleId, srp.Id, c.Colorflag  FROM `salesreturnpacks` srp inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='" . $ArtId . "' and srp.PartyId='" . $PartyId . "' group by srp.ColorId) AS C ON A.ColorId=C.ColorId LEFT JOIN ( select sum(f.NoPacks) as OutletSalesReturn_NoPacks, f.ColorId, f.ArticleId, f.OutletId, f.Id, f.Colorflag from (SELECT srp.NoPacks, srp.ColorId, srp.ArticleId, srp.OutletId, srp.Id, c.Colorflag FROM `outletsalesreturnpacks` srp inner join outletsalesreturn osr on osr.OutletId=srp.OutletId inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='" . $ArtId . "' and osr.OutletPartyId='" . $PartyId . "' group by srp.Id) as f group by f.ColorId) AS D ON A.ColorId=D.ColorId) as d group by d.ArticleId) as ddd inner join article ar on ar.Id=ddd.ArticleId inner join category c on c.Id=ar.CategoryId left join articlerate art on art.ArticleId=ar.Id left join party p on p.Id='" . $PartyId . "' left join outletimport oi on oi.ArticleId='" . $ArtId . "' and oi.PartyId = '" . $PartyId . "'");

	// 		// return $data;
// 		$artucleratedata = DB::table('articlerate')->where('ArticleId', $ArtId)->first();
// 		$data[0]->ArticleRate = $artucleratedata->ArticleRate + $data[0]->OutletArticleRate;
// 		$allRecords = DB::select('(select `outletsalesreturn`.`NoPacks` as `NoPacks`, 2 as type, `outletsalesreturnnumber`.`CreatedDate` as `SortDate` from `outletsalesreturn` inner join `outletsalesreturnnumber` on `outletsalesreturn`.`SalesReturnNumber` = `outletsalesreturnnumber`.`Id` where (`ArticleId` = ' . $ArtId . ' and `outletsalesreturn`.`OutletPartyId` = ' . $PartyId . ')) union (select `outlet`.`NoPacks` as `NoPacks`, 1 as type, `outletnumber`.`CreatedDate` as `SortDate` from `outlet` inner join `outletnumber` on `outlet`.`OutletNumberId` = `outletnumber`.`Id` where (`ArticleId` = ' . $ArtId . ' and `outletnumber`.`PartyId` = ' . $PartyId . ')) union (select `outward`.`NoPacks` as `NoPacks`, 0 as type, `outwardnumber`.`created_at` as `SortDate` from `outward` inner join `transportoutlet` on `outward`.`OutwardNumberId` = `transportoutlet`.`OutwardNumberId` inner join `outwardnumber` on `outward`.`OutwardNumberId` = `outwardnumber`.`Id` where (`ArticleId` = ' . $ArtId . ' and `transportoutlet`.`TransportStatus` = 1 and `outward`.`PartyId` = ' . $PartyId . ')) union (select `salesreturn`.`NoPacks` as `NoPacks`, 3 as type ,`salesreturnnumber`.`CreatedDate` as `SortDate` from `outward` inner join `salesreturn` on `salesreturn`.`OutwardId` = `outward`.`Id` inner join `salesreturnnumber` on `salesreturnnumber`.`Id` = `salesreturn`.`SalesReturnNumber` where (`outward`.`PartyId` = ' . $PartyId . ' and `outward`.`ArticleId` = ' . $ArtId . ')) order by `SortDate` asc');
// 		if (!isset($allRecords[0])) {
// 			$outletArticle = Outletimport::where('ArticleId', $ArtId)->where('PartyId', $PartyId)->first();
// 			if ($outletArticle) {
// 				$outletArticleColors = json_decode($outletArticle->ArticleColor);
// 			} else {
// 				$articleRecord = Article::where('Id', $ArtId)->first();
// 				$outletArticleColors  = json_decode($articleRecord->ArticleColor);
// 			}
// 			$outletArticleColors =  (array)$outletArticleColors;
// 			if ($data[0]->ArticleColor) {
// 				$SalesNoPacks = [];	
// 				foreach ($outletArticleColors as $makearray) {
// 					array_push($SalesNoPacks, 0);
// 				}
// 				$transportOutwardpacks =  TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $ArtId)->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
// 				$TotalTransportOutwardpacks = 0;
// 				if (count($transportOutwardpacks) != 0) {
// 					// $collectionTransportOutwardpacks = collect($transportOutwardpacks);
// 					// $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
// 					foreach ($transportOutwardpacks as $getTransportOutwardpack) {
// 						$outletArticle = Outletimport::where('ArticleId', $ArtId)->where('PartyId', $PartyId)->first();
// 						if ($outletArticle) {
// 							$articleColors = json_decode($outletArticle->ArticleColor);
// 						} else {
// 							$article = Article::select('ArticleColor')->where('Id', $ArtId)->first();
// 							$articleColors = json_decode($article['ArticleColor']);
// 						}
// 						$count = 0;
// 						foreach ($articleColors as $articlecolor) {
// 							if ($articlecolor->Id == $getTransportOutwardpack->ColorId) {
// 								if (!isset($SalesNoPacks[$count])) {
// 									array_push($SalesNoPacks, 0);
// 								}
// 								$SalesNoPacks[$count] = $SalesNoPacks[$count] + $getTransportOutwardpack->NoPacks;
// 							}
// 							$count = $count + 1;
// 						}
// 					}
// 				}
// 				$newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
// 				$data[0]->SalesNoPacks =  $newimplodeSalesNoPacks;
// 			} else {
// 				$transportOutwardpacks =  TransportOutwardpacks::select('NoPacks')->where('ArticleId', $ArtId)->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
// 				$TotalTransportOutwardpacks = 0;
// 				if (count($transportOutwardpacks) != 0) {
// 					// $collectionTransportOutwardpacks = collect($transportOutwardpacks);
// 					// $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
// 					foreach ($transportOutwardpacks as $getTransportOutwardpack) {
// 						$TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
// 					}
// 				}
// 				$data[0]->SalesNoPacks =  $TotalTransportOutwardpacks;
// 			}
// 		} else {

	// 			if ($data[0]->ArticleColor) {
// 				$SalesNoPacks = [];
// 				foreach (explode(",", $allRecords[0]->NoPacks) as $makearray) {
// 					array_push($SalesNoPacks, 0);
// 				}
// 				$transportOutwardpacks =  TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $ArtId)->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
// 				$TotalTransportOutwardpacks = 0;
// 				if (count($transportOutwardpacks) != 0) {
// 					// $collectionTransportOutwardpacks = collect($transportOutwardpacks);
// 					// $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
// 					foreach ($transportOutwardpacks as $getTransportOutwardpack) {
// 						$outletArticle = Outletimport::where('ArticleId', $ArtId)->where('PartyId', $PartyId)->first();
// 						if ($outletArticle) {
// 							$articleColors = json_decode($outletArticle->ArticleColor);
// 						} else {
// 							$article = Article::select('ArticleColor')->where('Id', $ArtId)->first();
// 							$articleColors = json_decode($article['ArticleColor']);
// 						}
// 						$count = 0;
// 						foreach ($articleColors as $articlecolor) {
// 							if ($articlecolor->Id == $getTransportOutwardpack->ColorId) {
// 								if (!isset($SalesNoPacks[$count])) {
// 									array_push($SalesNoPacks, 0);
// 								}
// 								$SalesNoPacks[$count] = $SalesNoPacks[$count] + $getTransportOutwardpack->NoPacks;
// 							}
// 							$count = $count + 1;
// 						}
// 					}
// 				}
// 				foreach ($allRecords as  $allRecord) {
// 					for ($i = 0; $i < count(explode(",", $allRecord->NoPacks)); $i++) {
// 						if (!isset($SalesNoPacks[$i])) {
// 							array_push($SalesNoPacks, 0);
// 						}
// 						$noPacks = explode(",", $allRecord->NoPacks);
// 						if ($allRecord->type == 0) {
// 							$SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
// 						} else if ($allRecord->type == 1) {
// 							$SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
// 						} else if ($allRecord->type == 2) {
// 							$SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
// 						} else if ($allRecord->type == 3) {
// 							$SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
// 						}
// 					}
// 				}
// 				$newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
// 				$data[0]->SalesNoPacks =  $newimplodeSalesNoPacks;
// 			} else {
// 				$transportOutwardpacks =  TransportOutwardpacks::select('NoPacks')->where('ArticleId', $ArtId)->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
// 				$TotalTransportOutwardpacks = 0;
// 				if (count($transportOutwardpacks) != 0) {
// 					// $collectionTransportOutwardpacks = collect($transportOutwardpacks);
// 					// $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
// 					foreach ($transportOutwardpacks as $getTransportOutwardpack) {
// 						$TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
// 					}
// 				}
// 				$TotalInwardPacks = $TotalTransportOutwardpacks;
// 				$TotalOutwardPacks = 0;
// 				foreach ($allRecords as  $allRecord) {
// 					if ($allRecord->type == 0) {
// 						$TotalInwardPacks = $TotalInwardPacks + $allRecord->NoPacks;
// 					} else if ($allRecord->type == 1) {
// 						$TotalOutwardPacks = $TotalOutwardPacks + $allRecord->NoPacks;
// 					} else if ($allRecord->type == 2) {
// 						$TotalInwardPacks = $TotalInwardPacks + $allRecord->NoPacks;
// 					} else if ($allRecord->type == 3) {
// 						$TotalOutwardPacks = $TotalOutwardPacks + $allRecord->NoPacks;
// 					}
// 				}
// 				$data[0]->SalesNoPacks =  $TotalInwardPacks - $TotalOutwardPacks;
// 			}
// 		}
// 		return $data;
// 	}
//WORKED


	public function GenerateOutletNumber($PartyId)
	{
		$array = array();
		$fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
		$outletnumberdata = DB::select('SELECT Id, FinancialYearId, OutletNumber From outletnumber where PartyId="' . $PartyId . '" order by Id desc limit 0,1');
		// $outletnumberdata = DB::select('SELECT Id, FinancialYearId, OutletNumber From outletnumber order by Id desc limit 0,1');
		if (count($outletnumberdata) > 0) {
			if ($fin_yr[0]->Id > $outletnumberdata[0]->FinancialYearId) {
				$array["Outlet_Number"] = 1;
				$array["Outlet_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["Outlet_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			} else {
				$array["Outlet_Number"] = ($outletnumberdata[0]->OutletNumber) + 1;
				$array["Outlet_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["Outlet_Number_Financial"] = ($outletnumberdata[0]->OutletNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			}
		} else {
			$array["Outlet_Number"] = 1;
			$array["Outlet_Number_Financial_Id"] = $fin_yr[0]->Id;
			$array["Outlet_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
			return $array;
		}
	}

	public function AddOutlet(Request $request)
	{

		$data = $request->all();

		$outletName = $data['OutletPartyId'];


		if (isset($outletName['Id'])) {
			// Case: $outletName is an array with 'Id' key
			$id = $outletName['Id'];
			$outletName = $outletName['Name'];
		} elseif (is_string($outletName)) {
			// Case: $outletName is a string
			$outletName = $outletName;
		} else {
			// Case: $outletName is neither an array with 'Id' key nor a string
			// Handle the case as per your requirements
		}



		$partyoutlet = Party::where('Name', $outletName)->first();

		$outletId = $partyoutlet->Id;

		$soadd = array();


		$getdata = $this->GetOutletSingleArticle($data['PartyId'], $data['ArticleId'], $outletId);

		$ArticleRate = $data['ArticleRate'];

		DB::beginTransaction();

		try {

			if ($data['Discount'] == "") {

				$Discount = 0;

			} else {

				$Discount = $data['Discount'];

			}

			$userRec = Users::where('Id', $data['UserId'])->first();

			if ($data['OutletNumberId'] == "Add") {

				$generate_OutletNumber = $this->GenerateOutletNumber($data['PartyId']);

				$Outlet_Number = $generate_OutletNumber['Outlet_Number'];

				$Outlet_Number_Financial_Id = $generate_OutletNumber['Outlet_Number_Financial_Id'];

				$OutletNumberId = DB::table('outletnumber')->insertGetId(

					['OutletNumber' => $Outlet_Number, "FinancialYearId" => $Outlet_Number_Financial_Id, 'UserId' => $data['UserId'], 'OutletPartyId' => $outletId, 'PartyId' => $data['PartyId'], 'OutletDate' => $data['Date'], 'GSTAmount' => $data['GSTAmount'], 'GSTPercentage' => $data['GSTPercentage'], 'Remarks' => $data['Remarks'], 'Discount' => $Discount, 'Address' => $data['Address'], 'Contact' => $data['Contact'], 'CreatedDate' => date('Y-m-d H:i:s')]

				);

				$outletRec = DB::select("select concat($Outlet_Number,'/', fn.StartYear,'-',fn.EndYear) as OutletNumber from outletnumber un inner join financialyear fn on fn.Id=un.FinancialYearId where un.Id= '" . $OutletNumberId . "'");

				UserLogs::create([

					'Module' => 'Outlet',

					'ModuleNumberId' => $OutletNumberId,

					'LogType' => 'Created',

					'LogDescription' => $userRec['Name'] . " " . 'created outlet with Outlet number' . " " . $outletRec[0]->OutletNumber,

					'UserId' => $userRec['Id'],

					'updated_at' => null

				]);

			} else {

				$checkoutlet_number = DB::select("SELECT OutletNumber FROM `outletnumber` where Id ='" . $data['OutletNumberId'] . "'");

				if (!empty($checkoutlet_number)) {

					$OutletNumberId = $data['OutletNumberId'];

					$OutletNumber = $checkoutlet_number[0]->OutletNumber;

					$OutletNumberId = $data['OutletNumberId'];

					DB::table('outletnumber')

						->where('Id', $OutletNumberId)

						->update(['UserId' => $data['UserId'], 'PartyId' => $data['PartyId'], 'OutletPartyId' => $outletId, 'OutletDate' => $data['Date'], 'GSTAmount' => $data['GSTAmount'], 'GSTPercentage' => $data['GSTPercentage'], 'Discount' => $Discount, 'Remarks' => $data['Remarks'], 'Address' => $data['Address'], 'Contact' => $data['Contact']]);

				}

				$artRateRecord = Article::where('Id', $data['ArticleId'])->first();

				$outletRec = DB::select("select concat($OutletNumber,'/', fn.StartYear,'-',fn.EndYear) as OutletNumber from outletnumber un inner join financialyear fn on fn.Id=un.FinancialYearId where un.Id= '" . $OutletNumberId . "'");

				UserLogs::create([

					'Module' => 'Outlet',

					'ModuleNumberId' => $OutletNumberId,

					'LogType' => 'updated',

					'LogDescription' => $userRec['Name'] . " " . 'added article ' . $artRateRecord->ArticleNumber . ' in outlet with Outlet number' . " " . $outletRec[0]->OutletNumber,

					'UserId' => $userRec['Id'],

					'updated_at' => null

				]);

			}

			if ($data["ArticleOpenFlag"] == 1) {

				//using for outletrepot yashvi
				$dataupdate = $data['NoPacks'] - $data['NoPacksNew'];
				DB::table('artstockstatus')->where(['outletId' => $data['PartyId']])->where(['ArticleId' => $data['ArticleId']])->update(['SalesNoPacks' => $dataupdate, 'TotalPieces' => $dataupdate]);
				//close

				$NoPacks = "";
				// $newSalesNoPacks = $article->STOCKS - $NoPacks; // Adjust this logic as needed
				// $article->update(['SalesNoPacks' => $newSalesNoPacks]);		


				if (isset($data['NoPacksNew'])) {

					$NoPacks .= $data['NoPacksNew'];

					if ($data['NoPacks'] < $data['NoPacksNew']) {

						return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);

					}

					if ($data['NoPacksNew'] == 0) {

						return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);

					}

				} else {

					return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);

				}

				$sonumberdata = DB::select('SELECT count(*) as total, (case when NoPacks IS NULL then "0" ELSE NoPacks End) as NoPacks FROM `outlet` where OutletNumberId="' . $OutletNumberId . '" and ArticleId="' . $data['ArticleId'] . '"');

				if ($sonumberdata[0]->total > 0) {

					$getnppacks = $sonumberdata[0]->NoPacks;

					$nopacksadded = $getnppacks + $NoPacks;

					DB::table('outlet')

						->where('OutletNumberId', $OutletNumberId)

						->where('ArticleId', $data['ArticleId'])

						->update(['NoPacks' => $nopacksadded]);

					if ($data['ArticleOpenFlag'] == 0) {




						if (strpos($nopacksadded, ',') !== false) {

							$nopacksadded = explode(',', $nopacksadded);

							foreach ($data['ArticleSelectedColor'] as $key => $vl) {

								$numberofpacks = $vl["Id"];

								$res = DB::select("select Id from outletnopacks where ColorId='" . $numberofpacks . "' and OutletId='" . $OutletNumberId . "' and ArticleId='" . $data['ArticleId'] . "'");

								DB::table('outletnopacks')

									->where('Id', $res[0]->Id)

									->update(['NoPacks' => $nopacksadded[$key], 'UpdatedDate' => date('Y-m-d H:i:s')]);

							}

						} else {

							foreach ($data['ArticleSelectedColor'] as $key => $vl) {

								$numberofpacks = $vl["Id"];

								$res = DB::select("select Id from outletnopacks where ColorId='" . $numberofpacks . "' and OutletId='" . $OutletNumberId . "' and ArticleId='" . $data['ArticleId'] . "'");

								DB::table('outletnopacks')

									->where('Id', $res[0]->Id)

									->update(['NoPacks' => $nopacksadded, 'UpdatedDate' => date('Y-m-d H:i:s')]);

							}

						}

					} else {

						$datavl = DB::select('select Id from outlet where OutletNumberId = "' . $OutletNumberId . '" and ArticleId = "' . $data['ArticleId'] . '"');



						DB::table('outletnopacks')

							->where('Id', $datavl[0]->Id)

							->update(['NoPacks' => $nopacksadded, 'UpdatedDate' => date('Y-m-d H:i:s')]);

					}

				} else {

					$soadd1["OutletNumberId"] = $OutletNumberId;

					$soadd1["ArticleId"] = $data['ArticleId'];

					$soadd1["NoPacks"] = $NoPacks;

					$soadd1["ArticleRate"] = $ArticleRate;

					$field = Outlet::create($soadd1);

					$outlet_insertedid = $field->id;

					if ($data['ArticleOpenFlag'] == 0) {


						if (strpos($NoPacks, ',') !== false) {


							$NoPacks = explode(',', $NoPacks);

							foreach ($data['ArticleSelectedColor'] as $key => $vl) {

								$numberofpacks = $vl["Id"];

								DB::table('outletnopacks')->insertGetId(

									['ArticleId' => $data['ArticleId'], 'ColorId' => $numberofpacks, 'OutletId' => $outlet_insertedid, 'NoPacks' => $NoPacks[$key], 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]

								);

							}

						} else {

							foreach ($data['ArticleSelectedColor'] as $key => $vl) {

								$numberofpacks = $vl["Id"];

								DB::table('outletnopacks')->insertGetId(

									['ArticleId' => $data['ArticleId'], 'ColorId' => $numberofpacks, 'OutletId' => $outlet_insertedid, 'NoPacks' => $NoPacks, 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]

								);

							}

						}

					} else {

						$datavl = DB::select('select Id from outlet where OutletNumberId = "' . $OutletNumberId . '" and ArticleId = "' . $data['ArticleId'] . '"');



						DB::table('outletnopacks')->insertGetId(

							['ArticleId' => $data['ArticleId'], 'ColorId' => 0, 'OutletId' => $outlet_insertedid, 'NoPacks' => $NoPacks, 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]

						);

					}

				}

			} else {

				$search = $getdata[0]->SalesNoPacks;

				$searchString = ',';

				if (strpos($search, $searchString) !== false) {

					$string = explode(',', $search);

					$stringcomma = 1;

				} else {

					$search;

					$stringcomma = 0;

				}

				$NoPacks = "";

				if ($data['Colorflag'] == 1) {
					//update artstockstatus yashvi
					$salesNoPacksData = [];
					$totalPieces = 0;

					foreach ($data['ArticleSelectedColor'] as $key => $vl) {
						$numberofpacks = $vl["Id"];
						$articleId = $data['ArticleId'];
						$noPacksNewKey = 'NoPacksNew_' . $numberofpacks;
						$noPacksKey = 'NoPacks_' . $numberofpacks;

						if (isset($data[$noPacksNewKey]) && isset($data[$noPacksKey])) {
							$noPacksNewValue = (int) $data[$noPacksNewKey];
							$noPacksValue = (int) $data[$noPacksKey];
							$salesNoPacksData[] = abs($noPacksValue - $noPacksNewValue);
						} else {
							$salesNoPacksData[] = 0;
						}

					$totalPieces = array_sum($salesNoPacksData);
					$salesNoPacksDataString = implode(',', $salesNoPacksData);

					DB::table('artstockstatus')
						->where(['outletId' => $data['PartyId'], 'ArticleId' => $articleId])
						->update(['SalesNoPacks' => $salesNoPacksDataString, 'TotalPieces' => $totalPieces]);

					//close

						if ($data["NoPacksNew_" . $numberofpacks] != "") {

							if ($stringcomma == 1) {

								if ($string[$key] < $data["NoPacksNew_" . $numberofpacks]) {

									return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);

								}

							} else {

								if ($search < $data["NoPacksNew_" . $numberofpacks]) {

									return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);

								}

							}

							$NoPacks .= $data["NoPacksNew_" . $numberofpacks] . ",";

						} else {

							$NoPacks .= "0,";

						}

					}

				} else {

					if (isset($data['NoPacksNew'])) {

						$NoPacks .= $data['NoPacksNew'];

						if ($search < $data['NoPacksNew']) {

							return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);

						}

					} else {

						return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);

					}

				}




				//For non openflag
				$NoPacks = rtrim($NoPacks, ',');

				$CheckSalesNoPacks = explode(',', $NoPacks);

				$tmp = array_filter($CheckSalesNoPacks);

				if (empty($tmp)) {

					return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);

				}

				$sonumberdata = DB::select('SELECT count(*) as total, NoPacks  FROM `outlet` where OutletNumberId="' . $OutletNumberId . '" and ArticleId="' . $data['ArticleId'] . '"');

				$getnppacks = $sonumberdata[0]->NoPacks;

				if ($sonumberdata[0]->total > 0) {

					$nopacksadded = "";

					if (strpos($NoPacks, ',') !== false) {

						$NoPacks1 = explode(',', $NoPacks);

						$getnppacks = explode(',', $getnppacks);

						foreach ($getnppacks as $key => $vl) {

							$nopacksadded .= $NoPacks1[$key] + $vl . ",";
							$nopacksaddeds .= $NoPacks1[$key] - $vl . ",";

						}

					} else {

						$nopacksadded .= $getnppacks + $NoPacks . ",";
						$nopacksaddeds .= $getnppacks - $NoPacks . ",";

					}

					$nopacksadded = rtrim($nopacksadded, ',');
					$nopacksaddeds = rtrim($nopacksaddeds, ',');

					DB::table('outlet')

						->where('OutletNumberId', $OutletNumberId)

						->where('ArticleId', $data['ArticleId'])

						->update(['NoPacks' => $nopacksadded]);

					//For non openflag colse


					$IdData = DB::select("select Id from outlet where OutletNumberId='" . $OutletNumberId . "' and ArticleId='" . $data['ArticleId'] . "'");

					if ($data['ArticleOpenFlag'] == 0) {

						DB::table('artstockstatus')
							->where(['outletId' => $data['PartyId']])
							->where(['ArticleId' => $data['ArticleId']])
							->update(['SalesNoPacks' => $nopacksadded[$key]]);


						if (strpos($nopacksadded, ',') !== false) {

							$nopacksadded = explode(',', $nopacksadded);



							foreach ($data['ArticleSelectedColor'] as $key => $vl) {

								$numberofpacks = $vl["Id"];

								$res = DB::select("select Id from outletnopacks where ColorId='" . $numberofpacks . "' and OutletId='" . $IdData[0]->Id . "' and ArticleId='" . $data['ArticleId'] . "'");

								DB::table('outletnopacks')

									->where('Id', $res[0]->Id)

									->update(['NoPacks' => $nopacksadded[$key], 'UpdatedDate' => date('Y-m-d H:i:s')]);

							}

						} else {

							foreach ($data['ArticleSelectedColor'] as $key => $vl) {

								$numberofpacks = $vl["Id"];

								$res = DB::select("select Id from outletnopacks where ColorId='" . $numberofpacks . "' and OutletId='" . $IdData[0]->Id . "' and ArticleId='" . $data['ArticleId'] . "'");

								DB::table('outletnopacks')

									->where('Id', $res[0]->Id)

									->update(['NoPacks' => $nopacksadded, 'UpdatedDate' => date('Y-m-d H:i:s')]);

							}

						}

					} else {

						$datavl = DB::select('select Id from outlet where OutletNumberId = "' . $OutletNumberId . '" and ArticleId = "' . $data['ArticleId'] . '"');

						DB::table('outletnopacks')

							->where('OutletId', $datavl[0]->Id)

							->where('ArticleId', $data['ArticleId'])

							->update(['NoPacks' => $nopacksadded, 'UpdatedDate' => date('Y-m-d H:i:s')]);

					}

				} else {

					$soadd['OutletNumberId'] = $OutletNumberId;

					$soadd["ArticleId"] = $data['ArticleId'];

					$soadd["NoPacks"] = $NoPacks;

					$soadd["ArticleRate"] = $ArticleRate;

					$field = Outlet::create($soadd);

					$outlet_insertedid = $field->id;

					if ($data['ArticleOpenFlag'] == 0) {

						if (strpos($NoPacks, ',') !== false) {

							$NoPacks = explode(',', $NoPacks);

							foreach ($data['ArticleSelectedColor'] as $key => $vl) {

								$numberofpacks = $vl["Id"];

								DB::table('outletnopacks')->insertGetId(

									['ArticleId' => $data['ArticleId'], 'ColorId' => $numberofpacks, 'OutletId' => $outlet_insertedid, 'NoPacks' => $NoPacks[$key], 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]

								);

							}

						} else {

							foreach ($data['ArticleSelectedColor'] as $key => $vl) {

								$numberofpacks = $vl["Id"];

								DB::table('outletnopacks')->insertGetId(

									['ArticleId' => $data['ArticleId'], 'ColorId' => $numberofpacks, 'OutletId' => $outlet_insertedid, 'NoPacks' => $NoPacks, 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]

								);

							}

						}

					} else {

						$datavl = DB::select('select Id from outlet where OutletNumberId = "' . $OutletNumberId . '" and ArticleId = "' . $data['ArticleId'] . '"');



						DB::table('outletnopacks')->insertGetId(

							['ArticleId' => $data['ArticleId'], 'ColorId' => 0, 'OutletId' => $outlet_insertedid, 'NoPacks' => $NoPacks, 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]

						);

					}

				}

			}

			DB::commit();

			return response()->json(array("OutletNumberId" => $OutletNumberId), 200);

		} catch (\Exception $e) {

			DB::rollback();

			return response()->json("", 200);

		}

	}

	public function UpdateOutlet(Request $request, $field)
	{
		//articleId yashvi
		$result = DB::select("SELECT ArticleId FROM `outlet` WHERE outlet.Id = '" . $field . "'");
        $articleId = $result[0]->ArticleId;
		//close
		$data = $request->all();

		$outletName = $data['OutletPartyId'];
		$partyoutlet = Party::where('Name', $outletName)->first();
		$outletId = $partyoutlet->Id;
		$getresult = DB::select("SELECT NoPacks as GetNoPacks FROM `outlet` where Id = '" . $data["id"] . "'");
		$GetNoPacks = $getresult[0]->GetNoPacks;
		// if ($data['ArticleRate'] > 0) {
		$ArticleRate = $data['ArticleRate'];
		// } 
		// else {
		// 	$artratedata = DB::select("select * from articlerate where ArticleId='" . $data['ArticleId'] . "'");
		// 	$ArticleRate = $artratedata[0]->ArticleRate;
		// }
		if ($data['Discount'] == "") {
			$Discount = 0;
		} else {
			$Discount = $data['Discount'];
		}
		if ($data["ArticleOpenFlag"] == 1) {
				//using for outletrepot yashvi
				$dataupdatenew =$data['NoPacks'] + $GetNoPacks;
				$dataupdate =  $dataupdatenew - $data['NoPacksNew'];
				DB::table('artstockstatus')->where(['outletId' => $data['PartyId']])->where(['ArticleId' => $articleId])->update(['SalesNoPacks' => $dataupdate, 'TotalPieces' => $dataupdate]);
				//close
			$UpdateInwardNoPacks = "";
			$updateNoPacks = $data['NoPacksNew'];
			$remainignopacks = $data['NoPacks'];
			$total = $remainignopacks + $GetNoPacks;
			if ($total < $data["NoPacksNew"]) {
				return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
			}
			if (empty($updateNoPacks)) {
				return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
			}
			DB::beginTransaction();
			try {
				$logDesc = "";
				$ActiveOutlet = Outlet::where('id', $data['id'])->first();
				if ($ActiveOutlet->NoPacks != $updateNoPacks) {
					$logDesc = $logDesc . 'Pieces,';
				}
				if ($ActiveOutlet->ArticleRate != $ArticleRate) {
					$logDesc = $logDesc . 'ArticleRate,';
				}
				$newLogDesc = rtrim($logDesc, ',');
				$userRec = Users::where('Id', $data['UserId'])->first();
				$artRateRecord = Article::where('Id', $articleId)->first();
				$outletRec = DB::select("select concat(un.OutletNumber,'/', fn.StartYear,'-',fn.EndYear) as OutletNumber from outletnumber un inner join financialyear fn on fn.Id=un.FinancialYearId where un.Id= '" . $data['OutletNumberId'] . "'");
				UserLogs::create([
					'Module' => 'Outlet',
					'ModuleNumberId' => $data['OutletNumberId'],
					'LogType' => 'updated',
					'LogDescription' => $userRec->Name . ' updated ' . $newLogDesc . ' of article ' . $artRateRecord->ArticleNumber . ' in Outlet number ' . $outletRec[0]->OutletNumber,
					'UserId' => $userRec['Id'],
					'updated_at' => null
				]);
				DB::table('outletnumber')
					->where('Id', $data['OutletNumberId'])
					->update(['OutletDate' => $data['Date'], 'OutletPartyId' => $outletId, 'PartyId' => $data['PartyId'], 'Remarks' => $data['Remarks'], 'GSTAmount' => $data['GSTAmount'], 'GSTPercentage' => $data['GSTPercentage'], 'Discount' => $Discount, 'Address' => $data['Address'], 'Contact' => $data['Contact']]);
				Outlet::where('id', $data['id'])->update(
					array(
						'NoPacks' => $updateNoPacks,
						'ArticleRate' => $ArticleRate
					)
				);
				DB::table('outletnopacks')
					->where('OutletId', $data['id'])
					->where('ArticleId', $articleId)
					->where('PartyId', $data['PartyId'])
					->update(['NoPacks' => $updateNoPacks, 'UpdatedDate' => date('Y-m-d H:i:s')]);
				DB::commit();
				return response()->json("SUCCESS", 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}
		} else {
			$OutletNoPacks = $data["NoPacks"];
			if (strpos($data["NoPacks"], ',') !== false) {
				$OutletNoPacks = explode(',', $data["NoPacks"]);
				$stringcomma = 1;
			} else {
				$stringcomma = 0;
			}
			$updateNoPacks = "";
			if ($data["Colorflag"] == 1) {
					//update artstockstatus yashvi
					$getresult = DB::select("SELECT NoPacks as GetNoPacks FROM `outlet` where Id = '" . $data["id"] . "'");
$GetNoPacksString = $getresult[0]->GetNoPacks;
$GetNoPacksArray = explode(',', $GetNoPacksString);

$salesNoPacksData = [];
$totalPieces = 0;

foreach ($data['ArticleSelectedColor'] as $key => $vl) {
    $numberofpacks = $vl["Id"];
    $noPacksNewKey = 'NoPacksNew_' . $numberofpacks;
    $noPacksKey = 'NoPacks_' . $numberofpacks;

    if (isset($data[$noPacksNewKey]) && isset($data[$noPacksKey])) {
        $noPacksNewValue = (int) $data[$noPacksNewKey];
        $noPacksValue = (int) $data[$noPacksKey];
        
        $salesNoPacksDataupdate = abs($GetNoPacksArray[$key] + $noPacksValue);
        $salesNoPacksData[] = abs($salesNoPacksDataupdate - $noPacksNewValue);
    } else {
        $salesNoPacksData[] = 0;
    }


$salesNoPacksDataString = implode(',', $salesNoPacksData);
$totalPieces = array_sum($salesNoPacksData);

DB::table('artstockstatus')
    ->where(['outletId' => $data['PartyId'], 'ArticleId' => $articleId])
    ->update(['SalesNoPacks' => $salesNoPacksDataString, 'TotalPieces' => $totalPieces]);
//close

					$outletsale = $OutletNoPacks[$key];
					$remaingnopacks = $data["NoPacks_" . $numberofpacks];
					$getnopacks1 = $data["NoPacksNew_" . $numberofpacks];
					if ($data["NoPacksNew_" . $numberofpacks] != "") {
						if ($stringcomma == 1) {
							$total = ($remaingnopacks + $outletsale);
							if ($total < $getnopacks1 && $total > $outletsale) {
								return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
							}

							if ($total < $getnopacks1) {
								return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
							}
							$updateNoPacks .= $getnopacks1 . ",";
						} else {
							$total = $remaingnopacks + $OutletNoPacks;
							if ($total < $getnopacks1) {
								return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
							}
							$updateNoPacks .= $getnopacks1 . ",";
						}
					} else {
						$updateNoPacks .= "0,";
					}
				}
			} else {
				$updateNoPacks .= $data['NoPacksNew'] . ",";
				$total = $data['NoPacks'] + $GetNoPacks;
				if ($total < $data["NoPacksNew"]) {
					return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
				}
			}
			$updateNoPacks = rtrim($updateNoPacks, ',');
			$CheckupdateNoPacks = explode(',', $updateNoPacks);
			$tmp = array_filter($CheckupdateNoPacks);
			if (empty($tmp)) {
				return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
			}
			DB::beginTransaction();
			try {
				$logDesc = "";
				$ActiveOutlet = Outlet::where('id', $data['id'])->first();
				if ($ActiveOutlet->NoPacks != $updateNoPacks) {
					$logDesc = $logDesc . 'Pieces,';
				}
				if ($ActiveOutlet->ArticleRate != $ArticleRate) {
					$logDesc = $logDesc . 'ArticleRate,';
				}
				$newLogDesc = rtrim($logDesc, ',');
				$userRec = Users::where('Id', $data['UserId'])->first();
				$artRateRecord = Article::where('Id', $articleId)->first();
				$outletRec = DB::select("select concat(un.OutletNumber,'/', fn.StartYear,'-',fn.EndYear) as OutletNumber from outletnumber un inner join financialyear fn on fn.Id=un.FinancialYearId where un.Id= '" . $data['OutletNumberId'] . "'");
				UserLogs::create([
					'Module' => 'Outlet',
					'ModuleNumberId' => $data['OutletNumberId'],
					'LogType' => 'updated',
					'LogDescription' => $userRec->Name . ' updated ' . $newLogDesc . ' of article ' . $artRateRecord->ArticleNumber . ' in Outlet number ' . $outletRec[0]->OutletNumber,
					'UserId' => $userRec['Id'],
					'updated_at' => null
				]);
				DB::table('outletnumber')
					->where('Id', $data['OutletNumberId'])
					->update(['OutletDate' => $data['Date'], 'OutletPartyId' => $outletId, 'PartyId' => $data['PartyId'], 'Remarks' => $data['Remarks'], 'GSTAmount' => $data['GSTAmount'], 'GSTPercentage' => $data['GSTPercentage'], 'Discount' => $Discount, 'Address' => $data['Address'], 'Contact' => $data['Contact']]);
				Outlet::where('id', $data['id'])->update(
					array(
						'NoPacks' => $updateNoPacks,
						'ArticleRate' => $ArticleRate
					)
				);
				if ($data['ArticleOpenFlag'] == 0) {
					if (strpos($updateNoPacks, ',') !== false) {
						$updateNoPacks = explode(',', $updateNoPacks);
						foreach ($data['ArticleSelectedColor'] as $key => $vl) {
							$numberofpacks = $vl["Id"];
							$res = DB::select("select Id from outletnopacks where ColorId='" . $numberofpacks . "' and OutletId='" . $data['id'] . "' and ArticleId='" . $articleId . "'");
							DB::table('outletnopacks')
								->where('Id', $res[0]->Id)
								->update(['NoPacks' => $updateNoPacks[$key], 'UpdatedDate' => date('Y-m-d H:i:s')]);
						}
					} else {
						foreach ($data['ArticleSelectedColor'] as $key => $vl) {
							$numberofpacks = $vl["Id"];
							$res = DB::select("select Id from outletnopacks where ColorId='" . $numberofpacks . "' and OutletId='" . $data['id'] . "' and ArticleId='" . $articleId . "'");
							DB::table('outletnopacks')
								->where('Id', $res[0]->Id)
								->update(['NoPacks' => $updateNoPacks, 'UpdatedDate' => date('Y-m-d H:i:s')]);
						}
					}
				} else {
					$datavl = DB::select('select Id from outlet where OutletNumberId = "' . $data['OutletNumberId'] . '" and ArticleId = "' . $articleId . '"');
					DB::table('outletnopacks')
						->where('OutletId', $datavl[0]->Id)
						->where('ArticleId', $articleId)
						->update(['NoPacks' => $updateNoPacks, 'UpdatedDate' => date('Y-m-d H:i:s')]);
				}

				DB::commit();
				return response()->json("SUCCESS", 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}
		}
	}

	public function DeleteOutlet($id, $ArticleOpenFlag, $LoggedId)
	{
		
		$data = DB::select("SELECT outlet.* , otn.PartyId , a.ArticleColor , a.ArticleOpenFlag  FROM `outlet` inner join outletnumber otn On otn.Id = outlet.OutletNumberId INNER JOIN article a ON a.Id = outlet.ArticleId where outlet.Id = '" . $id . "'");
		
		//update artstockstatus yashvi
		if (!empty($data) && $data[0]->ArticleOpenFlag == 0) {
			$salesNoPacksData = []; // Initialize outside the loop
			$data1 = DB::select("SELECT * FROM `artstockstatus` WHERE ArticleId = {$data[0]->ArticleId} AND outletId = {$data[0]->PartyId}");
			$totalPieces = 0;
			
			if (!empty($data1) && isset($data1[0]->SalesNoPacks)) {
				$articleColors = json_decode($data[0]->ArticleColor, true);
			
				foreach ($articleColors as $key => $vl) {
					$numberofpacks = $vl["Id"];
					$articleId = $data[0]->ArticleId;
					$noPacksNewKey = $data1[0]->SalesNoPacks;
					$noPacksKey = $data[0]->NoPacks;
			
					$noPacksNewKey = explode(',', $noPacksNewKey);
					$noPacksKey = explode(',', $noPacksKey);
			
					if (count($noPacksNewKey) === count($noPacksKey)) {
						$salesNoPacksData = [];
						for ($i = 0; $i < count($noPacksNewKey); $i++) {
							$salesNoPacksData[] = $noPacksNewKey[$i] + $noPacksKey[$i];
						}
					} else {
						$salesNoPacksData[] = 0;
					}
			
					$salesNoPacksDataString = implode(',', $salesNoPacksData);
					$totalPieces = array_sum($salesNoPacksData);
			
					DB::table('artstockstatus')
						->where(['outletId' => $data[0]->PartyId, 'ArticleId' => $data[0]->ArticleId])
						->update(['SalesNoPacks' => $salesNoPacksDataString, 'TotalPieces' => $totalPieces]);
				}
			} 
		}else
		{
		$data1 = DB::select("SELECT * FROM `artstockstatus` WHERE ArticleId = {$data[0]->ArticleId} AND outletId = {$data[0]->PartyId}");
		$dataupdate = $data[0]->NoPacks + $data1[0]->SalesNoPacks ;	
		DB::table('artstockstatus')->where(['outletId' => $data[0]->PartyId, 'ArticleId' => $data[0]->ArticleId])->update(['SalesNoPacks' =>  $dataupdate,  'TotalPieces' => $dataupdate]);
		}		
		//close
		DB::beginTransaction();
		try {
			$outletRec = DB::select("select un.Id as OutletNumberId, a.ArticleNumber, concat(un.OutletNumber,'/', fn.StartYear,'-',fn.EndYear) as Outletnumber from outlet o inner join outletnumber un on un.Id=o.OutletNumberId inner join article a on a.Id=o.ArticleId inner join financialyear fn on fn.Id=un.FinancialYearId where o.Id= '" . $id . "'");
			$userName = Users::where('Id', $LoggedId)->first();
			UserLogs::create([
				'Module' => 'Outlet',
				'ModuleNumberId' => $outletRec[0]->OutletNumberId,
				'LogType' => 'Deleted',
				'LogDescription' => $userName->Name . ' deleted article ' . $outletRec[0]->ArticleNumber . ' from Outlet Number ' . $outletRec[0]->Outletnumber,
				'UserId' => $userName['Id'],
				'updated_at' => null
			]);
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

	public function DeleteOutleNumber($OTLNO, $LoggedId)
	{


		$data = DB::select("SELECT outlet.* , otn.PartyId , a.ArticleColor , a.ArticleOpenFlag  FROM `outlet` inner join outletnumber otn On otn.Id = outlet.OutletNumberId INNER JOIN article a ON a.Id = outlet.ArticleId where OutletNumberId = '" . $OTLNO . "'");
		//update artstockstatus yashvi
		if (!empty($data) && $data[0]->ArticleOpenFlag == 0) {
			$salesNoPacksData = []; // Initialize outside the loop
foreach ($data as $d) {
    $data1 = DB::select("SELECT * FROM `artstockstatus` where ArticleId =  $d->ArticleId and outletId =   $d->PartyId ");
    $totalPieces = 0;

    if (!empty($data1) && isset($data1[0]->SalesNoPacks)) {
    $salesNoPacksData = []; // Initialize outside the loop
    $data1 = DB::select("SELECT * FROM `artstockstatus` where ArticleId =  $d->ArticleId and outletId =   $d->PartyId ");
    $totalPieces = 0;
        $articleColors = json_decode($d->ArticleColor, true);

      
            $articleId = $d->ArticleId;
            $noPacksNewKey = $data1[0]->SalesNoPacks;
            $noPacksKey = $d->NoPacks;

            $noPacksNewKey = explode(',', $noPacksNewKey);
            $noPacksKey = explode(',', $noPacksKey);

            if (count($noPacksNewKey) === count($noPacksKey)) {
                for ($i = 0; $i < count($noPacksNewKey); $i++) {
                    $salesNoPacksData[] = $noPacksNewKey[$i] + $noPacksKey[$i];
                }
            } else {
                $salesNoPacksData[] = 0;
            }
        

        $salesNoPacksDataString = implode(',', $salesNoPacksData);
        $totalPieces = array_sum($salesNoPacksData);

        DB::table('artstockstatus')
            ->where(['outletId' => $d->PartyId, 'ArticleId' => $articleId])
            ->update(['SalesNoPacks' => $salesNoPacksDataString, 'TotalPieces' => $totalPieces]);
	}
}

		}else{
			foreach ($data as $d) {
				$data1 = DB::select("SELECT * FROM `artstockstatus` where ArticleId = $d->ArticleId and outletId =  $d->PartyId ");
				$dataupdate = $d->NoPacks + $data1[0]->SalesNoPacks ;
				DB::table('artstockstatus')->where(['outletId' => $d->PartyId])->where(['ArticleId' => $d->ArticleId])->update(['SalesNoPacks' =>  $dataupdate,  'TotalPieces' => $dataupdate]);
			}
		
		}	
		// $salesNoPacksData = []; // Initialize outside the loop
		// foreach ($data as $d) {
		// 	$data1 = DB::select("SELECT * FROM `artstockstatus` where ArticleId =  $d->ArticleId and outletId =   $d->PartyId ");
		// 	$totalPieces = 0;
		// 	$salesNoPacksData = [];

		// 	$articleColors = json_decode($d->ArticleColor, true);

		// 	foreach ($articleColors as $key => $vl) {
		// 		$numberofpacks = $vl["Id"];
		// 		$articleId = $d->ArticleId;
		// 		$noPacksNewKey = $data1[0]->SalesNoPacks;
		// 		$noPacksKey = $d->NoPacks;

		// 		$noPacksNewKey = explode(',', $noPacksNewKey);
		// 		$noPacksKey = explode(',', $noPacksKey);

		// 		if (count($noPacksNewKey) === count($noPacksKey)) {
		// 			$salesNoPacksData = [];
		// 			for ($i = 0; $i < count($noPacksNewKey); $i++) {
		// 				$salesNoPacksData[] = $noPacksNewKey[$i] + $noPacksKey[$i];
		// 			}
		// 		} else {
		// 			$salesNoPacksData[] = 0;
		// 		}

		// 		$salesNoPacksDataString = implode(',', $salesNoPacksData);
		// 		$totalPieces = array_sum($salesNoPacksData);
				

		// 		DB::table('artstockstatus')
		// 			->where(['outletId' => $d->PartyId, 'ArticleId' => $articleId])
		// 			->update(['SalesNoPacks' => $salesNoPacksDataString, 'TotalPieces' => $totalPieces]);
		// 	}
		// }


	
		// foreach ($data as $d) {
		// 		$data1 = DB::select("SELECT * FROM `artstockstatus` where ArticleId = $d->ArticleId and outletId =  $d->PartyId ");
		// 		$dataupdate = $d->NoPacks + $data1[0]->SalesNoPacks ;
		// 		DB::table('artstockstatus')->where(['outletId' => $d->PartyId])->where(['ArticleId' => $d->ArticleId])->update(['SalesNoPacks' =>  $dataupdate,  'TotalPieces' => $dataupdate]);

		// 	}
		//close 	
		if (!empty($data)) {
			DB::beginTransaction();
			try {
				$outletRec = DB::select("select concat(un.OutletNumber,'/', fn.StartYear,'-',fn.EndYear) as Outletnumber from outletnumber un inner join financialyear fn on fn.Id=un.FinancialYearId where un.Id= '" . $OTLNO . "'");
				DB::table('outlet')
					->where('OutletNumberId', '=', $OTLNO)
					->delete();

				foreach ($data as $key => $val) {
					DB::table('outletnopacks')
						->where('OutletId', '=', $val->Id)
						->delete();
				}
				$userName = Users::where('Id', $LoggedId)->first();
				UserLogs::create([
					'Module' => 'Outlet',
					'ModuleNumberId' => $OTLNO,
					'LogType' => 'Deleted',
					'LogDescription' => $userName['Name'] . " " . 'deleted outlet with Outlet number' . " " . $outletRec[0]->Outletnumber,
					'UserId' => $userName['Id'],
					'updated_at' => null
				]);
				DB::commit();
				return response()->json("SUCCESS", 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}
		}
	}

	public function GetOutletChallen($OTLNO)
	{
		$outletsData = OutletNumber::where('Id', $OTLNO)->first();
		if ($outletsData->OutwardnumberId == null) {
			$getoutletchallen = DB::select("SELECT oln.Address as OutletAddress,  o.Id as OID,pr.UserId as SalesUserId , a.Id, a.ArticleNumber, CASE WHEN (oi.ArticleColor IS NULL) THEN a.ArticleColor ELSE oi.ArticleColor END AS `ArticleColor`, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, o.NoPacks, concat(Replace(partyuser.Name,' ',''), oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber, oln.OutletDate, oln.GSTAmount, oln.GSTPercentage, oln.Discount, oln.Remarks, pr.Address, pr.PhoneNumber as Contact, pr.City , pr.State , pr.Country ,pr.PinCode ,  u.Name as UserName, c.Title, c.Colorflag, o.ArticleRate, pr.Name as PartyName , pr.GSTNumber as GST FROM `outlet` o inner join outletnumber oln on oln.Id=o.OutletNumberId inner join article a on a.Id=o.ArticleId inner join users u on u.Id=oln.UserId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party pr on pr.Id=oln.OutletPartyId left join users partyuser on oln.PartyId=partyuser.PartyId  left join outletimport oi on oi.ArticleId=a.Id and oi.PartyId = oln.PartyId where o.OutletNumberId = '" . $OTLNO . "' order by c.Title asc");
		} else {
			$getoutletchallen = DB::select("SELECT otn.Address as OutletAddress, o.Id as OID, pr.UserId as SalesUserId , a.Id, a.ArticleNumber, a.ArticleColor ,a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, o.NoPacks, concat(Replace(partyuser.Name,' ',''), otn.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber , otn.Remarks, otn.OutletDate, own.GSTAmount, own.GSTPercentage, own.Discount , pr.Address, pr.PhoneNumber as Contact, pr.City , pr.State , pr.Country ,pr.PinCode ,  u.Name as UserName, c.Title, c.Colorflag, o.OutwardRate as ArticleRate, pr.Name as PartyName , pr.GSTNumber as GST   from outletnumber otn left join outwardnumber own on otn.OutwardnumberId=own.Id left join outward o on own.Id=o.OutwardNumberId inner join article a on a.Id=o.ArticleId inner join category c on c.Id=a.CategoryId  inner join users u on u.Id=otn.UserId left join party pr on pr.Id=otn.OutletPartyId  left join users partyuser on otn.PartyId=partyuser.PartyId inner join financialyear fn on fn.Id=otn.FinancialYearId  where otn.Id= '" . $OTLNO . "' ");
		}

		$challendata = [];
		$TotalNoPacks = 0;
		$TotalAmount = 0;
		foreach ($getoutletchallen as $vl) {
			if (!is_null($vl->OutletAddress)) {
				$fullAddress = $vl->OutletAddress;
			} else {
				if (!is_null($vl->City)) {
					$fullAddress = $vl->Address . ', ' . $vl->City . ', ' . $vl->State . ', ' . $vl->Country . ' - ' . $vl->PinCode;
				} else {
					$fullAddress = $vl->Address;
				}
			}

			if (!is_null($vl->SalesUserId)) {
				$usersales = Users::where('Id', $vl->SalesUserId)->first();
				$SalesPerson = $usersales->Name;
			} else {
				$SalesPerson = $vl->UserName;
			}
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
			$Address = $fullAddress;
			$Remarks = $vl->Remarks;
			$Contact = $vl->Contact;
			$GST = $vl->GST;
			$GSTPercentage = $vl->GSTPercentage;
			$GSTAmount = $vl->GSTAmount;
			$Discount = number_format($vl->Discount, 2);
			if ($Colorflag == 0) {
				if ($ArticleOpenFlag == 0) {
					$task_array = json_decode($vl->ArticleColor);
					$ArticleRatio = $vl->ArticleRatio;
					$TotalNoPacks += $NoPacks;
				} else {
					$TotalNoPacks += $NoPacks;
				}
			} else {
				if ($ArticleOpenFlag == 0) {
					$task_array = json_decode($vl->ArticleColor);
					$ArticleRatio = $vl->ArticleRatio;
					$countNoSet = array_sum(explode(",", $NoPacks));
					$TotalNoPacks += array_sum(explode(",", $NoPacks));
				} else {
					$TotalNoPacks += $NoPacks;
				}
			}
			if ($ArticleOpenFlag == 0) {
				$ArticleRatio = $vl->ArticleRatio;
				if (strpos($NoPacks, ',') !== false) {
					$countNoSet = array_sum(explode(",", $NoPacks));
				} else {
					if (strpos($ArticleRatio, ',') !== false) {
						$ArticleRatio = array_sum(explode(",", $ArticleRatio));
					}
					$countNoSet = $NoPacks;
				}
				$Amount = $countNoSet * $ArticleRate;
				$TotalAmount += $Amount;
				$getcolor = json_decode($vl->ArticleColor);
				$getsize = json_decode($vl->ArticleSize);




				//COLOR
				$colorsv = $vl->ArticleColor;
				$decodedData = json_decode($colorsv, true);
				$results = [];
				foreach ($decodedData as $item) {
					$results[] = $item['Name'];
				}
				$jsonResult = $results;
				//COLOR

				//PACKS
				$data = $NoPacks;
				$results = explode(",", $data);

				// Convert the results to JSON if needed
				$jsonResultP = $results;

				//PACKS


				$C = array_combine($jsonResult, $jsonResultP);

				$jsonData = json_encode($C);

				$arrayData = json_decode($jsonData, true);
				$TotalCQty = "";
				$allkey = "";
				$allvalue = "";
				foreach ($arrayData as $key => $value) {
					if (!empty($key)) {
						$TotalCQty .= '<b>' . $key . " : " . '</b>' . $value . ", ";
					} else {
						$TotalCQty .= '<b>' . '--' . " : " . '</b>' . $value . ', ';
					}
				}


				$ArticleColor = "";
				foreach ($getcolor as $vl) {
					$ArticleColor .= $vl->Name . ",";
				}
				$ArticleColor = rtrim($ArticleColor, ',');

				$ArticleSize = "";
				foreach ($getsize as $vl) {
					$ArticleSize .= $vl->Name . ",";
				}
				$ArticleSize = rtrim($ArticleSize, ',');
			} else {
				$countNoSet = $NoPacks;
				$Amount = $countNoSet * $ArticleRate;
				$ArticleRatio = "";
				$TotalCQty = "";
				$ArticleColor = "";
				$TotalAmount += $Amount;
				$ArticleSize = "";
			}

			$numbers_array = explode(",", $NoPacks);
			$sum = array_sum($numbers_array);
			$TotalQty = $sum;
			$challendata[] = json_decode(json_encode(array("SalesPerson" => $SalesPerson, "TotalCQty" => $TotalCQty, "TotalQty" => $TotalQty, "Address" => $Address, "Remarks" => $Remarks, "Contact" => $Contact, "GST" => $GST, "PartyName" => $PartyName, "UserName" => $UserName, "OutletDate" => $OutletDate, "OutletNumber" => $OutletNumber, "ArticleNumber" => $ArticleNumber, "Title" => $Title, "ArticleRatio" => $ArticleRatio, "QuantityInSet" => $NoPacks, "ArticleColor" => $ArticleColor, "ArticleSize" => $ArticleSize, "ArticleRate" => number_format($ArticleRate, 2), "Amount" => number_format($Amount, 2))), false);
		}
		$TotalFinalAmount = 0;
		$GSTLabel = "";
		$GSTValue = 0;

		if (($GSTPercentage != "" && !is_null($GSTPercentage)) || $GSTAmount != "") {
			if ($GSTPercentage > 0) {
				$GSTLabel = "GST " . $GSTPercentage . "%";
				$GSTValue = number_format((($TotalAmount * $GSTPercentage) / 100), 2);
				$GSTValue1 = (($TotalAmount * $GSTPercentage) / 100);
				$TotalFinalAmount = ($TotalAmount + $GSTValue1);
			} else {
				$GSTValue = number_format($GSTAmount, 2);
				$GSTValue1 = $GSTAmount;
				$TotalFinalAmount = ($TotalAmount + $GSTValue1);
				$GSTLabel = "GST Amount";
			}
		}
		if ($Discount > 0 || $Discount != "") {
			if ($TotalFinalAmount > 0) {
				$TotalFinalAmount = ($TotalFinalAmount - $Discount);
			} else {
				$TotalFinalAmount = ($TotalAmount - $Discount);
			}
		} else {
			if ($TotalFinalAmount == 0) {
				$TotalFinalAmount = $TotalAmount;
			}
		}
		$as = array($challendata, array("RoundOff" => $this->splitter(number_format($TotalFinalAmount, 2, '.', '')), "TotalQuantityPic" => $TotalNoPacks, "TotalAmount" => number_format($TotalAmount, 2), "Discount" => number_format($Discount, 2), "TotalFinalAmount" => number_format($TotalFinalAmount, 2), "GSTLabel" => $GSTLabel, "GSTValue" => $GSTValue));
		return $as;
	}

	function splitter($val)
	{
		$totalroundamount = $val;
		$str = (string) $val;
		$splitted = explode(".", $str);
		$num = (int) $splitted[1];
		if ($num < 10) {
			$num = $num . '0';
		}
		$number_var = "";
		$adjust_amount = "";
		if ($num != 0) {
			$roundoff_check = true;
			if ($num >= 50) {
				$number_var = "Up";
				$adjust_amount = "0." . (100 - $num);
			} else {
				$number_var = "Down";
				$adjust_amount = "0." . $num;
			}
			$totalroundamount = number_format(round($totalroundamount), 2);
		} else {
			$number_var = "Zero";
			$roundoff_check = false;
			$adjust_amount = 0;
		}
		return array("Roundoff" => $roundoff_check, 'RoundValueSign' => $number_var, 'TotalRoundAmount' => $totalroundamount, 'AdjustAmount' => number_format($adjust_amount, 2));
	}

	public function OutletList($UserId)
	{
		$userrole = DB::select("SELECT Role FROM `users`where Id='" . $UserId . "'");
		if ($userrole[0]->Role == 2) {
			$wherecustom = "";
		} else {
			$wherecustom = "where oln.UserId='" . $UserId . "'";
		}
		return DB::select("SELECT oln.Id, oln.OutletDate, concat(FirstCharacterConcat(u.Name), oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber  FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId inner join users u on u.Id=oln.UserId " . $wherecustom . " group by oln.Id");
	}

	public function PostOutletList(Request $request)
	{
		$data = $request->all();
		$search = $data['dataTablesParameters']["search"];
		$startnumber = $data['dataTablesParameters']["start"];
		$UserId = $data['UserID'];
		$userrole = DB::select("SELECT Role, PartyId FROM `users`where Id='" . $UserId . "'");
		if ($userrole[0]->Role == 2) {
			$vnddataTotal = DB::select("select count(*) as Total from (select d.* from (SELECT oln.Id FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party p on p.Id=oln.PartyId left join party pp on pp.Id=oln.OutletPartyId inner join users u on u.Id=oln.UserId) as d group by d.Id) as dd");
			$vntotal = $vnddataTotal[0]->Total;
			$length = $data['dataTablesParameters']["length"];
			if ($search['value'] != null && strlen($search['value']) > 2) {
				$searchstring = "where d.OutletNumber like '%" . $search['value'] . "%' OR cast(d.cdate as char) like '%" . $search['value'] . "%' OR d.OutletName like '%" . $search['value'] . "%' OR d.PartyName like '%" . $search['value'] . "%'  OR d.ArticleNumber like '%" . $search['value'] . "%'";
				$vnddataTotalFilter = DB::select("select count(*) as Total from (select d.*, GROUP_CONCAT(DISTINCT CONCAT(d.ArticleNumber1) ORDER BY d.Id SEPARATOR ',') as ArticleNumber from (SELECT oln.Id, a.ArticleNumber as ArticleNumber1, p.Name as PartyName, pp.Name as OutletName, DATE_FORMAT(oln.OutletDate, '%d/%m/%Y') as cdate, concat(Replace(partyuser.Name,' ',''),oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party p on p.Id=oln.PartyId left join users partyuser on oln.PartyId=partyuser.PartyId  left join party pp on pp.Id=oln.OutletPartyId inner join users u on u.Id=oln.UserId) as d group by d.Id) as d " . $searchstring);
				$vntotal = $vnddataTotalFilter[0]->Total;
				$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
			} else {
				$searchstring = "";
				$vnddataTotalFilterValue = $vntotal;
			}
		} else {
			$vnddataTotal = DB::select("select count(*) as Total from (select d.*, GROUP_CONCAT(DISTINCT CONCAT(d.ArticleNumber1) ORDER BY d.Id SEPARATOR ',') as ArticleNumber from (SELECT oln.Id, p.Name as PartyName, p.Id as PartyId,  pp.Name as OutletName, DATE_FORMAT(oln.OutletDate, '%d/%m/%Y') as cdate, concat(Replace(partyuser.Name,' ',''),oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party p on p.Id=oln.PartyId left join users partyuser on oln.PartyId=partyuser.PartyId  left join party pp on pp.Id=oln.OutletPartyId inner join users u on u.Id=oln.UserId) as d group by d.Id) as d where PartyId='" . $userrole[0]->PartyId . "'");
			$vntotal = $vnddataTotal[0]->Total;
			$length = $data['dataTablesParameters']["length"];
			if ($search['value'] != null && strlen($search['value']) > 2) {
				if ($userrole[0]->PartyId != 0) {
					$searchstring = "where d.PartyId='" . $userrole[0]->PartyId . "' and (d.OutletNumber like '%" . $search['value'] . "%' OR cast(d.cdate as char) like '%" . $search['value'] . "%' OR d.OutletName like '%" . $search['value'] . "%' OR d.PartyName like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%')";
				} else {
					$searchstring = "where d.OutletNumber like '%" . $search['value'] . "%' OR cast(d.cdate as char) like '%" . $search['value'] . "%' OR d.OutletName like '%" . $search['value'] . "%' OR d.PartyName like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%'";
				}
				$vnddataTotalFilter = DB::select("select count(*) as Total from (select d.*, GROUP_CONCAT(DISTINCT CONCAT(d.ArticleNumber1) ORDER BY d.Id SEPARATOR ',') as ArticleNumber from (SELECT oln.Id, p.Name as PartyName, a.ArticleNumber as ArticleNumber1, p.Id as PartyId, pp.Name as OutletName, DATE_FORMAT(oln.OutletDate, '%d/%m/%Y') as cdate, concat(Replace(partyuser.Name,' ',''),oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party p on p.Id=oln.PartyId left join users partyuser on oln.PartyId=partyuser.PartyId  left join party pp on pp.Id=oln.OutletPartyId inner join users u on u.Id=oln.UserId) as d group by d.Id) as d " . $searchstring);
				$vntotal = $vnddataTotalFilter[0]->Total;
				$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
			} else {
				$searchstring = "where d.PartyId='" . $userrole[0]->PartyId . "'";
				$vnddataTotalFilterValue = $vntotal;
			}
		}
		$column = $data['dataTablesParameters']["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "d.OutletName";
				break;
			case 2:
				$ordercolumn = "d.PartyName";
				break;
			case 4:
				$ordercolumn = "d.OutletDate";
				break;
			case 6:
				$ordercolumn = "d.OutletNumber";
				break;
			default:
				$ordercolumn = "d.Id";
				break;
		}
		$order = "";
		if ($data['dataTablesParameters']["order"][0]["dir"]) {
			$order = "order by " . $ordercolumn . " " . $data['dataTablesParameters']["order"][0]["dir"];
		}
		$vnddata = DB::select("select d.*, GROUP_CONCAT(DISTINCT CONCAT(d.ArticleNumber) ORDER BY d.Id SEPARATOR ',') as ArticleNumber,  sum(d.TotalNoPacks) as TotalPieces from ((SELECT oln.OutwardnumberId, oln.Id, oln.UserId, p.Id as PartyId, p.Name as PartyName, pp.Name as OutletName, a.ArticleNumber, CountNoPacks(o.NoPacks) as TotalNoPacks, o.OutletNumberId , oln.OutletDate,DATE_FORMAT(oln.OutletDate, '%d/%m/%Y') as cdate, concat(Replace(partyuser.Name,' ',''),oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber  FROM `outlet` o inner join article a on a.Id=o.ArticleId inner join outletnumber oln on oln.Id=o.OutletNumberId inner join financialyear fn on fn.Id=oln.FinancialYearId left join party p on p.Id=oln.PartyId left join users partyuser on oln.PartyId=partyuser.PartyId left join party pp on pp.Id=oln.OutletPartyId inner join users u on u.Id=oln.UserId) UNION (SELECT oln.OutwardnumberId,oln.Id, oln.UserId , p.Id as PartyId, p.Name as PartyName, pp.Name as OutletName,a.ArticleNumber ,CountNoPacks(outward.NoPacks) as TotalNoPacks,oln.Id as OutletNumberId , oln.OutletDate , DATE_FORMAT(oln.OutletDate, '%d/%m/%Y') as cdate, concat(Replace(partyuser.Name,' ',''),oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber FROM `outletnumber` oln left join outwardnumber on oln.OutwardnumberId=outwardnumber.Id left join outward on outwardnumber.Id=outward.OutwardNumberId inner join article a on a.Id=outward.ArticleId  left join party p on p.Id=oln.PartyId left join users partyuser on oln.PartyId=partyuser.PartyId left join party pp on pp.Id=oln.OutletPartyId inner join users u on u.Id=oln.UserId inner join financialyear fn on fn.Id=oln.FinancialYearId where oln.OutwardnumberId IS NOT NULL)) as d " . $searchstring . " group by d.Id " . $order . " limit " . $data['dataTablesParameters']["start"] . "," . $length);
		$totalNoPacks = 0;
		foreach ($vnddata as $vnd) {
			$getOutlets = Outlet::where('OutletNumberId', $vnd->Id)->get();
			foreach ($getOutlets as $getOutlet) {
				if (strpos($getOutlet->NoPacks, ',') !== false) {
					$totalNoPacks += array_sum(explode(",", $getOutlet->NoPacks));
				} else {
					$singlecountNoPacks = $getOutlet->NoPacks;
					$totalNoPacks += $getOutlet->NoPacks;
				}
				$vnd->TotalPieces = $totalNoPacks;
			}
			$totalNoPacks = 0;
		}
		$TotalAmount = 0;
		foreach ($vnddata as $vnd) {
			$outlets = Outlet::where('OutletNumberId', $vnd->OutletNumberId)->get();
			$outletsData = OutletNumber::where('Id', $vnd->OutletNumberId)->first();
			if ($vnd->OutwardnumberId == null) {
				foreach ($outlets as $outlet) {
					if (strpos($outlet->NoPacks, ',') !== false) {
						foreach (explode(",", $outlet->NoPacks) as $pack) {
							$TotalAmount = $TotalAmount + ($pack * $outlet->ArticleRate);
						}
					} else {
						$TotalAmount = $TotalAmount + ($outlet->NoPacks * $outlet->ArticleRate);
					}
				}
				if (!is_null($outletsData->GSTPercentage)) {
					$GSTValue = (($TotalAmount * $outletsData->GSTPercentage) / 100);
					$TotalAmount = $TotalAmount + $GSTValue;
				}
				if ($outletsData->Discount != 0) {
					$TotalAmount = $TotalAmount - $outletsData->Discount;
				}
				$totalRoundoffAmmount = $this->splitter(number_format($TotalAmount, 2, '.', ''));
				$vnd->TotalAmount = $totalRoundoffAmmount['TotalRoundAmount'];
				$TotalAmount = 0;
			} else {
				$outwardNumberId = DB::select("select Id as OutwardNumberId from outwardnumber where Id=" . $vnd->OutwardnumberId . "");
				$outwards = Outward::where('OutwardNumberId', $outwardNumberId[0]->OutwardNumberId)->get();

				if (count($outwards) != 0) {
					foreach ($outwards as $outward) {
						if (strpos($outward->NoPacks, ',') !== false) {
							foreach (explode(",", $outward->NoPacks) as $pack) {
								if ($outward->PartyDiscount) {
									$partyDiscountAmount = (($pack * $outward->OutwardRate * $outward->PartyDiscount) / 100);
									$AmountWithPartyDiscount = $pack * $outward->OutwardRate - $partyDiscountAmount;
									$TotalAmount = $TotalAmount + $AmountWithPartyDiscount;
								} else {
									$TotalAmount = $TotalAmount + ($pack * $outward->OutwardRate);
								}
							}
						} else {
							if ($outward->PartyDiscount) {
								$partyDiscountAmount = ((($outward->NoPacks * $outward->OutwardRate) * $outward->PartyDiscount) / 100);
								$AmountWithPartyDiscount = $outward->NoPacks * $outward->OutwardRate - $partyDiscountAmount;
								$TotalAmount = $TotalAmount + $AmountWithPartyDiscount;
							} else {
								$TotalAmount = $TotalAmount + ($outward->NoPacks * $outward->OutwardRate);
							}
						}
					}
					$outwardData = OutwardNumber::where('Id', $outwardNumberId[0]->OutwardNumberId)->first();
					if (!is_null($outwardData->GSTPercentage)) {
						$GSTValue = (($TotalAmount * $outwardData->GSTPercentage) / 100);
						$TotalAmount = $TotalAmount + $GSTValue;
					}
					if ($outwardData->Discount != 0) {
						$discountValue = (($TotalAmount * $outwardData->Discount) / 100);
						$TotalAmount = $TotalAmount - $discountValue;
					}
					if (!is_null($outwardData->GSTAmount)) {
						$TotalAmount = $TotalAmount + $outwardData->GSTAmount;
					}
					$totalRoundoffAmmount = $this->splitter(number_format($TotalAmount, 2, '.', ''));
					$vnd->TotalAmount = $totalRoundoffAmmount['TotalRoundAmount'];
					$TotalAmount = 0;
				}
			}
		}
		return array(
			'datadraw' => $data['dataTablesParameters']["draw"],
			'recordsTotal' => $vntotal,
			'recordsFiltered' => $vnddataTotalFilterValue,
			'startnumber' => $startnumber,
			'response' => 'success',
			'search' => count($vnddata),
			'data' => $vnddata,
		);
	}

	public function OutletListFromOTLNO($Id, Request $request)
	{
		$data = $request->all();
		$search = $data["search"];
		$startnumber = $data["start"];
		$vnddataTotal = DB::select('select count(*) as Total from (SELECT o.ArticleRate as Rate ,  concat(oln.OutletNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as Outlet_Number_FinancialYear, o.Id , o.OutletNumberId, oln.OutletNumber,  oln.OutletDate, o.NoPacks, a.ArticleNumber, a.ArticleOpenFlag FROM `outlet` o inner join outletnumber oln on o.OutletNumberId=oln.Id inner join financialyear fn on fn.Id=oln.FinancialYearId inner join article a on a.Id=o.ArticleId where o.OutletNumberId="' . $Id . '") as d');
		$vnTotal = $vnddataTotal[0]->Total;
		$length = $data["length"];
		if ($search['value'] != null && strlen($search['value']) > 2) {
			$searchstring = "where d.NoPacks like '%" . $search['value'] . "%' OR d.Rate like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%' ";
			$vnddataTotalFilter = DB::select('select count(*) as Total from (SELECT o.ArticleRate as Rate ,  concat(oln.OutletNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as Outlet_Number_FinancialYear, o.Id , o.OutletNumberId, oln.OutletNumber,  oln.OutletDate, o.NoPacks, a.ArticleNumber, a.ArticleOpenFlag FROM `outlet` o inner join outletnumber oln on o.OutletNumberId=oln.Id inner join financialyear fn on fn.Id=oln.FinancialYearId inner join article a on a.Id=o.ArticleId where o.OutletNumberId="' . $Id . '") as d ' . $searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		} else {
			$searchstring = "";
			$vnddataTotalFilterValue = $vnTotal;
		}
		$column = $data["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "d.ArticleNumber";
				break;
			case 2:
				$ordercolumn = "d.NoPacks";
				break;
			case 3:
				$ordercolumn = "d.Rate";
				break;


			default:
				$ordercolumn = "d.OutletDate";
				break;
		}
		$order = "";
		if ($data["order"][0]["dir"]) {
			$order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
		}
		$vnddata = DB::select('select d.* from (SELECT o.ArticleRate as Rate ,  concat(oln.OutletNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as Outlet_Number_FinancialYear, o.Id , o.OutletNumberId, oln.OutletNumber,  oln.OutletDate, o.NoPacks, a.ArticleNumber, a.ArticleOpenFlag FROM `outlet` o inner join outletnumber oln on o.OutletNumberId=oln.Id inner join financialyear fn on fn.Id=oln.FinancialYearId inner join article a on a.Id=o.ArticleId where o.OutletNumberId="' . $Id . '") as d ' . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length);



		return array(
			'datadraw' => $data["draw"],
			'recordsTotal' => $vnTotal,
			'recordsFiltered' => $vnddataTotalFilterValue,
			'response' => 'success',
			'startnumber' => $startnumber,
			'search' => count($vnddata),
			'data' => $vnddata,
		);


		// 		return DB::select('SELECT o.ArticleRate as Rate ,  concat(oln.OutletNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as Outlet_Number_FinancialYear, o.Id , o.OutletNumberId, oln.OutletNumber,  oln.OutletDate, o.NoPacks, a.ArticleNumber, a.ArticleOpenFlag FROM `outlet` o inner join outletnumber oln on o.OutletNumberId=oln.Id inner join financialyear fn on fn.Id=oln.FinancialYearId inner join article a on a.Id=o.ArticleId where o.OutletNumberId="' . $Id . '"');
	}

	public function OutletDatePartyfromOTLNO($Id)
	{
		return DB::select("SELECT oln.*, p.Name as PartyName ,  concat(Replace(partyuser.Name,' ',''), oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as Outlet_Number_FinancialYear FROM `outletnumber`oln inner join financialyear fn on fn.Id=oln.FinancialYearId inner join users u on u.Id=oln.UserId left join users partyuser on oln.PartyId=partyuser.PartyId inner join party p on oln.OutletPartyId=p.Id where oln.Id='" . $Id . "'");
	}

	public function GetOutletIdWise($id)
	{
		return DB::select("SELECT o.*, concat(Replace(partyuser.Name,' ',''), oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber, oln.OutletDate From outlet o inner join outletnumber oln on oln.Id=o.OutletNumberId left join users partyuser on oln.PartyId=partyuser.PartyId inner join financialyear fn on fn.Id=oln.FinancialYearId WHERE o.Id =$id");
	}

	public function UpdateTransportStatus(Request $request)
	{
		$data = $request->all();
		Transportoutlet::where('OutwardNumberId', $data['OutwardId'])->update(
			array(
				'TransportStatus' => $data['TransportStatus'],
				'Remarks' => $data['Remarks'],
				'UserId' => $data['UserId'],
				'TotalPieces' => $data['TotalPieces'],
				'ReceivedDate' => $data['ReceivedDate']
			)
		);
		if ($data['TransportStatus'] == 1) {
			DB::select('INSERT INTO `transportoutwardpacks` (`ArticleId`, `ColorId`, `OutwardId`, `NoPacks`, `PartyId`, `CreatedDate`, `UpdatedDate`) select `ArticleId`, `ColorId`, `OutwardId`, `NoPacks`, `PartyId`, `CreatedDate`, `UpdatedDate` from outwardpacks where OutwardId in (SELECT Id FROM `outward` where OutwardNumberId = ' . $data['OutwardId'] . ')');
		}
		$outletTraRec = DB::select("select outn.Id, concat(outn.OutwardNumber,'/', fn.StartYear,'-',fn.EndYear) as OutletOutwardnumber from transportoutlet t inner join outwardnumber outn on outn.Id = t.OutwardNumberId inner join financialyear fn on fn.Id=outn.FinancialYearId where t.OutwardNumberId= '" . $data['OutwardId'] . "'");
		$userRec = Users::where('Id', $data['UserId'])->first();
		// $Transportoutlet = Transportoutlet::where('OutwardNumberId', $data['OutwardId'])->first();
		UserLogs::create([
			'Module' => 'Outlet Transport',
			'ModuleNumberId' => $data['OutwardId'],
			'LogType' => 'Created',
			'LogDescription' => $userRec['Name'] . " " . 'recieved good of Outward order number' . " " . $outletTraRec[0]->OutletOutwardnumber,
			'UserId' => $userRec['Id'],
			'updated_at' => null
		]);
		return response()->json("SUCCESS", 200);
	}

	public function getoutwardtransport($partyId)
	{
		return DB::select('SELECT outn.Id, concat(outn.OutwardNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as Name FROM `transportoutlet` t inner join `outwardnumber` outn on outn.Id = t.OutwardNumberId inner join financialyear fn on fn.Id=outn.FinancialYearId where t.TransportStatus=0 and t.PartyId = ' . $partyId);
	}

	public function getoutwardpieces($outwardid)
	{
		return DB::select("select sum(TotalNoPacks) as TotalPieces, OutwardDate from (SELECT own.OutwardDate, c.Colorflag, a.ArticleOpenFlag, CountNoPacks(o.NoPacks) as TotalNoPacks,(SELECT count(*) FROM `articlecolor` where ArticleId=a.Id) as TotalColor, (SELECT sum(ArticleRatio) FROM `articleratio` where ArticleId=a.Id) as TotalRation FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on own.Id=o.OutwardNumberId left join po po on po.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where own.Id = '" . $outwardid . "' group by o.Id) as dd");
	}

	public function intransitlist($partyId)
	{
		if ($partyId == 0) {
			return DB::select("SELECT t.*, p.Name as PartyName, o.Id as OutwardNumberId ,  o.OutwardDate, concat(o.OutwardNumber, '/',f.StartYear,'-',f.EndYear) as OutwardNo FROM `transportoutlet` t left join party p on p.Id=t.PartyId left join outwardnumber o on o.Id=t.OutwardNumberId left join financialyear f on f.Id=o.FinancialYearId ORDER BY t.Id DESC");
		} else {
			return DB::select("SELECT t.*, p.Name as PartyName, o.Id as OutwardNumberId , o.OutwardDate, concat(o.OutwardNumber, '/',f.StartYear,'-',f.EndYear) as OutwardNo FROM `transportoutlet` t left join party p on p.Id=t.PartyId left join outwardnumber o on o.Id=t.OutwardNumberId left join financialyear f on f.Id=o.FinancialYearId where t.PartyId = " . $partyId . " ORDER BY t.Id DESC");
		}
	}

	public function GenerateOSRNumber($UserId)
	{
		$array = array();
		$fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
		$srnumberdata = DB::select('SELECT Id, FinancialYearId, SalesReturnNumber From outletsalesreturnnumber order by Id desc limit 0,1');
		if (count($srnumberdata) > 0) {
			if ($fin_yr[0]->Id > $srnumberdata[0]->FinancialYearId) {
				$array["SR_Number"] = 1;
				$array["SR_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["SR_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			} else {
				$array["SR_Number"] = ($srnumberdata[0]->SalesReturnNumber) + 1;
				$array["SR_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["SR_Number_Financial"] = ($srnumberdata[0]->SalesReturnNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			}
		} else {
			$array["SR_Number"] = 1;
			$array["SR_Number_Financial_Id"] = $fin_yr[0]->Id;
			$array["SR_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
			return $array;
		}
	}

	public function AddOutletSalesReturn(Request $request)
{
	$data = $request->all();
	
	
	$userName = Users::where('Id', $data['UserId'])->first();
	$OutletPartyId = $data['OutletPartyId'];
	if ($data['SRNumberId'] == "Add") {
		$generate_SRNUMBER = $this->GenerateOSRNumber($data['UserId']);
		$SR_Number = $generate_SRNUMBER['SR_Number'];
		$SR_Number_Financial_Id = $generate_SRNUMBER['SR_Number_Financial_Id'];
		$SRNumberId = DB::table('outletsalesreturnnumber')->insertGetId(
			['SalesReturnNumber' => $SR_Number, "FinancialYearId" => $SR_Number_Financial_Id, 'PartyId' => $data['PartyId'], 'OutletPartyId' => $OutletPartyId, 'Remarks' => $data['Remark'], 'CreatedDate' => date('Y-m-d H:i:s')]
		);
		$salesRetRec = DB::select("select concat($SR_Number,'/', fn.StartYear,'-',fn.EndYear) as OutletSalesreturnnumber from outletsalesreturnnumber srn inner join financialyear fn on fn.Id=srn.FinancialYearId where srn.Id= '" . $SRNumberId . "'");
		UserLogs::create([
			'Module' => 'Outlet Sales Return',
			'ModuleNumberId' => $SRNumberId,
			'LogType' => 'Created',
			'LogDescription' => $userName->Name . " " . 'created outlet sales return with OutletSalesReturn Number' . " " . $salesRetRec[0]->OutletSalesreturnnumber,
			'UserId' => $userName->Id,
			'updated_at' => null
		]);
		$artRateRecord = Article::where('Id', $data["ArticleId"])->first();
		UserLogs::create([
			'Module' => 'Outlet Sales Return',
			'ModuleNumberId' => $SRNumberId,
			'LogType' => 'Updated',
			'LogDescription' => $userName->Name . " " . 'added article ' . $artRateRecord->ArticleNumber . ' in outlet sales return with OutletSalesReturn Number ' . $salesRetRec[0]->OutletSalesreturnnumber,
			'UserId' => $userName->Id,
			'updated_at' => null
		]);
	} else {
		$checksonumber = DB::select("SELECT SalesReturnNumber FROM `outletsalesreturnnumber` where Id ='" . $data['SRNumberId'] . "'");
		if (!empty($checksonumber)) {
			$SR_Number = $checksonumber[0]->SalesReturnNumber;
			$SRNumberId = $data['SRNumberId'];
			DB::table('outletsalesreturnnumber')
				->where('Id', $SRNumberId)
				->update(['PartyId' => $data['PartyId'], 'OutletPartyId' => $OutletPartyId, 'Remarks' => $data['Remark']]);
			$artRateRecord = Article::where('Id', $data["ArticleId"])->first();
			$salesRetRec = DB::select("select concat($SR_Number,'/', fn.StartYear,'-',fn.EndYear) as OutletSalesreturnnumber from outletsalesreturnnumber srn inner join financialyear fn on fn.Id=srn.FinancialYearId where srn.Id= '" . $SRNumberId . "'");
			UserLogs::create([
				'Module' => 'Outlet Sales Return',
				'ModuleNumberId' => $SRNumberId,
				'LogType' => 'Updated',
				'LogDescription' => $userName->Name . " " . 'added article ' . $artRateRecord->ArticleNumber . ' in outlet sales return with OutletSalesReturn Number ' . $salesRetRec[0]->OutletSalesreturnnumber,
				'UserId' => $userName->Id,
				'updated_at' => null
			]);
		}
	}
	if ($data['ArticleOpenFlag'] == 1) {
		if ($data['NoPacksNew'] == 0) {
			return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
		}
		$getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutletId, o.NoPacks as OutwardNoPacks FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId inner join category c on c.Id=a.CategoryId where o.PartyId='" . $data["PartyId"] . "' and o.ArticleId = '" . $data["ArticleId"] . "' and o.OutwardNumberId='" . $data["OutwardNumberId"] . "' group by OutwardNumber");
		if ($data['NoPacksNew'] <= $data['NoPacks']) {
			$NoPacks = $data["NoPacksNew"];
			DB::beginTransaction();
			try {
				
			////Nitin Art Stock Status
			
			
					// Fetch the current SalesNoPacks value
						$currentSalesNoPacks = DB::table('artstockstatus')
							->where(['outletId' => $data['OutletPartyId']])
							->where(['ArticleId' => $data['ArticleId']])
							->value('SalesNoPacks');
						
							
							$artD = DB::table('article')
								->join('category', 'article.CategoryId', '=', 'category.Id')
								->where('article.Id', $data['ArticleId'])
								->first();

						
						// Calculate the new SalesNoPacks value by adding the new value to the current value
						$newSalesNoPacks = $currentSalesNoPacks + $data['NoPacksNew'];
						
						// Perform the updateOrInsert operation with the new SalesNoPacks value
						DB::table('artstockstatus')->updateOrInsert(
							[
								'outletId' => $data['OutletPartyId'],
								'ArticleId' => $data['ArticleId']
							],
							[
								'Title' => $artD->Title,
								'ArticleNumber' => $artD->ArticleNumber,
								'SalesNoPacks' => $newSalesNoPacks,
								'TotalPieces' => $newSalesNoPacks
							]
						);
			
			//close
				
				$outletSalesreturnrecord = OutletSalesreturn::where('SalesReturnNumber', $SRNumberId)->where('OutletId', $data['OutletId'])->where('ArticleId', $data['ArticleId'])->first();
				if ($outletSalesreturnrecord) {
					OutletSalesreturn::where('SalesReturnNumber', $SRNumberId)->where('OutletId', $data['OutletId'])->where('ArticleId', $data['ArticleId'])->update(['NoPacks' => $NoPacks + $outletSalesreturnrecord->NoPacks]);
					$transportOutwardpacks = OutletSalesReturnPacks::where('SalesReturnId', $outletSalesreturnrecord->Id)->where('ColorId', 0)->where('OutletId', $data['OutletId'])->first();
					OutletSalesReturnPacks::where('SalesReturnId', $outletSalesreturnrecord->Id)->where('ColorId', 0)->where('OutletId', $data['OutletId'])->update(['NoPacks' => $NoPacks + $outletSalesreturnrecord->NoPacks]);
				} else {
					$salesreturnId = DB::table('outletsalesreturn')->insertGetId(
						["SalesReturnNumber" => $SRNumberId, 'OutletId' => $data['OutletId'], 'OutletPartyId' => $data['OutletPartyId'], 'PartyId' => $data["PartyId"], 'ArticleId' => $data['ArticleId'], 'NoPacks' => $NoPacks, 'UserId' => $data['UserId'], 'CreatedDate' => date('Y-m-d H:i:s')]
					);
					DB::table('outletsalesreturnpacks')->insertGetId(
						['SalesReturnId' => $salesreturnId, 'ArticleId' => $data['ArticleId'], 'ColorId' => 0, 'OutletId' => $data['OutletId'], 'NoPacks' => $NoPacks, 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]
					);
				}
				DB::commit();
				return response()->json(array("SRNumberId" => $SRNumberId, "id" => "SUCCESS"), 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}
		} else {
			return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
		}
	} else {

		$getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutletId, o.NoPacks as OutletNoPacks FROM article a inner join outlet o on o.ArticleId=a.Id left join outletnumber own on own.Id=o.OutletNumberId inner join financialyear fn on fn.Id=own.FinancialYearId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where own.Id='" . $data["OutwardNumberId"] . "' and o.ArticleId = '" . $data["ArticleId"] . "' and o.OutletNumberId='" . $data["OutwardNumberId"] . "' group by OutletNumber");
		if ($getalldata[0]->ArticleOpenFlag == 0 && $getalldata[0]->Colorflag == 0) {
			if ($data['NoPacksNew'] == "") {
				return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
			}
			$as = DB::select("select sum(ddd.NoPacks) as TotalNoPacks from (select NoPacks FROM `outletsalesreturnpacks` where OutletId= '" . $data['OutletId'] . "' group by NoPacks) as ddd");
			$spacks = "";
			if (isset($as[0]->TotalNoPacks) != "") {
				$npacks = $getalldata[0]->OutletNoPacks;
				$spacks = $as[0]->TotalNoPacks;
				$newdata = $npacks - $spacks;
				$NoPacksNew = $data['NoPacksNew'];
				if ($newdata < $NoPacksNew) {
					return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
				}
			} else {
				$newdata = $getalldata[0]->OutletNoPacks;
			}
		} else {
			$as = DB::select("select GROUP_CONCAT(CONCAT(ddd.NoPacks) SEPARATOR ',') as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `outletsalesreturnpacks` where OutletId= '" . $getalldata[0]->OutletId . "' group by ColorId) as ddd");
			if ($as[0]->SalesReturnNoPacks != "") {
				$npacks = explode(",", $getalldata[0]->OutletNoPacks);
				$spacks = explode(",", $as[0]->SalesReturnNoPacks);
				$newdata = "";
				foreach ($npacks as $key => $vl) {
					$newdata .= ($vl - $spacks[$key]) . ',';
				}
				$newdata = rtrim($newdata, ',');
			} else {
				$newdata = $getalldata[0]->OutletNoPacks;
			}
		}
		$search = $getalldata[0]->OutletNoPacks;
		$OutletId = $getalldata[0]->OutletId;
		$NoPacks = "";
		$SalesNoPacks = "";
		$UpdateInwardNoPacks = "";
		$searchString = ',';
		if (strpos($search, $searchString) !== false) {
			$stringcomma = 1;
		} else {
			$search;
			$stringcomma = 0;
		}
		if ($data['ArticleColorFlag'] == "Yes") {
			foreach ($data['ArticleSelectedColor'] as $key => $vl) {
				$numberofpacks = $vl["Id"];
				if ($data["NoPacksNew_" . $numberofpacks] != "") {
					if ($stringcomma == 1) {
						if ($data["NoPacks_" . $numberofpacks] < $data["NoPacksNew_" . $numberofpacks]) {
							return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
						}
						$SalesNoPacks .= ($data["NoPacks_" . $numberofpacks] - $data["NoPacksNew_" . $numberofpacks]) . ",";
					} else {
						if ($data["NoPacks_" . $numberofpacks] < $data["NoPacksNew_" . $numberofpacks]) {
							return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
						}
						$SalesNoPacks .= ($data["NoPacks_" . $numberofpacks] - $data["NoPacksNew_" . $numberofpacks]) . ",";
					}
					$NoPacks .= $data["NoPacksNew_" . $numberofpacks] . ",";
				} else {
					$NoPacks .= "0,";
					$SalesNoPacks .= $data["NoPacks_" . $numberofpacks] . ",";
				}
			}
			$NoPacks = rtrim($NoPacks, ',');
			
			
			//Nitin Art Stock Status
			$currentSalesNoPacks = DB::table('artstockstatus')
					->where(['outletId' => $data['OutletPartyId']])
					->where(['ArticleId' => $data['ArticleId']])
					->value('SalesNoPacks');
				
				// Check if $currentSalesNoPacks is not empty
				if ($currentSalesNoPacks !== null) {
					// Convert comma-separated values to arrays
					$currentSalesNoPacksArray = explode(',', $currentSalesNoPacks);
					$dataNoPacksNewArray = explode(',', $NoPacks);
				
					// Perform element-wise addition
					$newSalesNoPacksArray = [];

					for ($i = 0; $i < count($dataNoPacksNewArray); $i++) {
						$newSalesNoPacksArray[$i] = (int)$currentSalesNoPacksArray[$i] + (int)$dataNoPacksNewArray[$i];
					}
				
					// Convert back to comma-separated string
					$newSalesNoPacks = implode(',', $newSalesNoPacksArray);
					
					$packes = $newSalesNoPacks;
					$packesArray = explode(',', $packes);
					$sum = array_sum($packesArray);
					
					 $artD = DB::table('article')
								->join('category', 'article.CategoryId', '=', 'category.Id')
								->where('article.Id', $data['ArticleId'])
								->first();

					// Perform the updateOrInsert operation with the new SalesNoPacks value
					DB::table('artstockstatus')->updateOrInsert(
						[
							'outletId' => $data['OutletPartyId'],
							'ArticleId' => $data['ArticleId']
						],
						[
							'Title' => $artD->Title,
							'ArticleNumber' => $artD->ArticleNumber,
							'SalesNoPacks' => $newSalesNoPacks,
							'TotalPieces' => $sum
						]
					);
				} else {
					$dataNoPacksNewArray = explode(',', $NoPacks);
					// Convert back to comma-separated string
					$newSalesNoPacks = implode(',', $dataNoPacksNewArray);
					$packes = $newSalesNoPacks;
					$packesArray = explode(',', $packes);
					$sum = array_sum($packesArray);
					 $artD = DB::table('article')
								->join('category', 'article.CategoryId', '=', 'category.Id')
								->where('article.Id', $data['ArticleId'])
								->first();

					// Perform the updateOrInsert operation with the new SalesNoPacks value
					DB::table('artstockstatus')->updateOrInsert(
						[
							'outletId' => $data['OutletPartyId'],
							'ArticleId' => $data['ArticleId']
						],
						[   'Title' => $artD->Title,
							'ArticleNumber' => $artD->ArticleNumber,
							'SalesNoPacks' => $newSalesNoPacks,
							'TotalPieces' => $sum
						]
					);
				}

				//Close

			
			
			
		} else {
			if (isset($data['NoPacksNew'])) {
				$NoPacks = $data['NoPacksNew'];
				$SalesNoPacks .= ($data["NoPacks"] - $data['NoPacksNew']);
			} else {
				return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
			}
		}
		$SalesNoPacks = rtrim($SalesNoPacks, ',');
		$UpdateInwardNoPacks = rtrim($UpdateInwardNoPacks, ',');
		$CheckSalesNoPacks = explode(',', $NoPacks);
		$tmp = array_filter($CheckSalesNoPacks);
		if (empty($tmp)) {
			return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
		}
		DB::beginTransaction();
		try {
			$outletSalesreturnrecord = OutletSalesreturn::where('SalesReturnNumber', $SRNumberId)->where('OutletId', $OutletId)->where('ArticleId', $data['ArticleId'])->first();
			if ($outletSalesreturnrecord) {
				$outletSalesreturnrecordPacks = explode(',', $outletSalesreturnrecord->NoPacks);
				$newPacksImport = [];
				$NoPacksGot = explode(',', $NoPacks);
				foreach ($NoPacksGot as $makearray) {
					array_push($newPacksImport, 0);
				}
				$count = 0;
				foreach ($NoPacksGot as $loop) {
					$newPacksImport[$count] = $outletSalesreturnrecordPacks[$count] + $NoPacksGot[$count];
					$count = $count + 1;
				}
				$newPacksImportstring = implode(',', $newPacksImport);
				OutletSalesreturn::where('SalesReturnNumber', $SRNumberId)->where('OutletId', $OutletId)->where('ArticleId', $data['ArticleId'])->update(['NoPacks' => $newPacksImportstring]);
				if ($data['ArticleOpenFlag'] == 0) {
					if (strpos($NoPacks, ',') !== false) {
						$NoPacks = explode(',', $NoPacks);
						foreach ($data['ArticleSelectedColor'] as $key => $vl) {
							$numberofpacks = $vl["Id"];
							$transportOutwardpacks = OutletSalesReturnPacks::where('SalesReturnId', $outletSalesreturnrecord->Id)->where('ColorId', $numberofpacks)->where('ArticleId', $data['ArticleId'])->where('OutletId', $OutletId)->first();
							OutletSalesReturnPacks::where('SalesReturnId', $outletSalesreturnrecord->Id)->where('ColorId', $numberofpacks)->where('ArticleId', $data['ArticleId'])->where('OutletId', $OutletId)->update(['NoPacks' => $transportOutwardpacks->NoPacks + $NoPacks[$key]]);
						}
					} else {
						foreach ($data['ArticleSelectedColor'] as $key => $vl) {
							$numberofpacks = $vl["Id"];
							$transportOutwardpacks = OutletSalesReturnPacks::where('SalesReturnId', $outletSalesreturnrecord->Id)->where('ColorId', $numberofpacks)->where('ArticleId', $data['ArticleId'])->where('OutletId', $OutletId)->first();
							OutletSalesReturnPacks::where('SalesReturnId', $outletSalesreturnrecord->Id)->where('ColorId', $numberofpacks)->where('ArticleId', $data['ArticleId'])->where('OutletId', $OutletId)->update(['NoPacks' => $transportOutwardpacks->NoPacks + $NoPacks]);
						}
					}
				} else {
					$transportOutwardpacks = OutletSalesReturnPacks::where('SalesReturnId', $outletSalesreturnrecord->Id)->where('ColorId', 0)->where('ArticleId', $data['ArticleId'])->where('OutletId', $OutletId)->first();
					OutletSalesReturnPacks::where('SalesReturnId', $outletSalesreturnrecord->Id)->where('ColorId', 0)->where('ArticleId', $data['ArticleId'])->where('OutletId', $OutletId)->update(['NoPacks' => $transportOutwardpacks->NoPacks + $NoPacks]);
				}
			} else {
				$salesreturnId = DB::table('outletsalesreturn')->insertGetId(
					["SalesReturnNumber" => $SRNumberId, 'OutletId' => $OutletId, 'PartyId' => $data["PartyId"], 'OutletPartyId' => $data["OutletPartyId"], 'Remark' => $data['Remark'], 'ArticleId' => $data['ArticleId'], 'NoPacks' => $NoPacks, 'UserId' => $data['UserId'], 'OutletRate' => $data['ArticleRate'], 'CreatedDate' => date('Y-m-d H:i:s')]
				);
				if ($data['ArticleOpenFlag'] == 0) {
					if (strpos($NoPacks, ',') !== false) {
						$NoPacks = explode(',', $NoPacks);
						foreach ($data['ArticleSelectedColor'] as $key => $vl) {
							$numberofpacks = $vl["Id"];
							DB::table('outletsalesreturnpacks')->insertGetId(
								['SalesReturnId' => $salesreturnId, 'ArticleId' => $data['ArticleId'], 'ColorId' => $numberofpacks, 'OutletId' => $OutletId, 'NoPacks' => $NoPacks[$key], 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]
							);
						}
					} else {
						foreach ($data['ArticleSelectedColor'] as $key => $vl) {
							$numberofpacks = $vl["Id"];
							DB::table('outletsalesreturnpacks')->insertGetId(
								['SalesReturnId' => $salesreturnId, 'ArticleId' => $data['ArticleId'], 'ColorId' => $numberofpacks, 'OutletId' => $OutletId, 'NoPacks' => $NoPacks, 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]
							);
						}
					}
				} else {
					DB::table('outletsalesreturnpacks')->insertGetId(
						['SalesReturnId' => $salesreturnId, 'ArticleId' => $data['ArticleId'], 'ColorId' => 0, 'OutletId' => $OutletId, 'NoPacks' => $NoPacks, 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]
					);
				}
			}
			DB::commit();
			return response()->json(array("SRNumberId" => $SRNumberId, "id" => "SUCCESS"), 200);
		} catch (\Exception $e) {
			DB::rollback();
			return response()->json(array("id" => ""), 200);
		}
	}
}

	public function GetOutletSalesReturn($id)
	{
		// return($id);
		$userrole = DB::select("SELECT Role FROM `users`where Id='" . $id . "'");
		// return($userrole);

		if ($userrole[0]->Role == 2) {
			$wherecustom = "";
		} else {
			$wherecustom = "where otn.UserId='" . $id . "'";
		}
		return DB::select("SELECT concat(otn.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, slr.Id, p.Name, a.ArticleNumber, FirstCharacterConcat(u.Name),pp.Name as OutletPartyName slr.NoPacks, slr.CreatedDate FROM `salesreturn` slr inner join party p on p.Id=slr.PartyId inner join article a on a.Id=slr.ArticleId  inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=otn.FinancialYearId inner join users u on u.Id=oln.UserId " . $wherecustom . " group by oln.Id");
	}

	public function PostOutletSalesReturn(Request $request)
	{

		// var_dump($request);
		// print_r($request);
		$data = $request->all();
		// $UserId = $data['UserID'];
		$search = $data["search"];
		$startnumber = $data["start"];



		$role = DB::select("SELECT `Role` FROM `users` where `Id` = " . $request->pid);


		if ($role[0]->Role != 2) {
			$vnddataTotal = DB::select("select count(*) as Total from (SELECT slr.Id FROM `outletsalesreturn` slr inner join outletsalesreturnnumber sln on sln.Id=slr.SalesReturnNumber inner join party p on p.Id=sln.PartyId inner join article a on a.Id=slr.ArticleId inner join users u on u.Id=slr.UserId inner join outlet o on o.Id=slr.OutletId inner join outletnumber otn on otn.Id=o.OutletNumberId inner join financialyear fn on fn.Id=otn.FinancialYearId where slr.UserId = " . $request->pid . "  group by slr.SalesReturnNumber ) as d");
		} else {
			$vnddataTotal = DB::select("select count(*) as Total from (SELECT slr.Id FROM `outletsalesreturn` slr inner join outletsalesreturnnumber  sln on sln.Id=slr.SalesReturnNumber inner join party p on p.Id=sln.PartyId inner join article a on a.Id=slr.ArticleId inner join users u on u.Id=slr.UserId inner join outlet o on o.Id=slr.OutletId inner join outletnumber otn on otn.Id=o.OutletNumberId inner join financialyear fn on fn.Id=otn.FinancialYearId group by slr.SalesReturnNumber) as d");
		}


		$vnTotal = $vnddataTotal[0]->Total;
		$length = $data["length"];


		if ($search['value'] != null && strlen($search['value']) > 2) {
			$searchstring = "WHERE d.SalesReturnNumber like '%" . $search['value'] . "%' OR d.Name like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%' OR cast(d.CreatedDate as char) like '%" . $search['value'] . "%'";

			if ($role[0]->Role != 2) {
				$vnddataTotalFilter = DB::select("select count(*) as Total from (select * from (SELECT GetTotalOSROrderPieces(son.Id) as TotalSRPieces, s.UserId, DATE_FORMAT(son.CreatedDate, '%d/%m/%Y') as CreatedDate, son.Id, p.Name, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, concat(son.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SalesReturnNumber FROM `outletsalesreturn` s inner join article a on a.Id=s.ArticleId left join outletsalesreturnnumber son on s.SalesReturnNumber=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=s.UserId inner join outlet o on o.Id=s.OutletId group by s.SalesReturnNumber) as ddd where ddd.UserId = " . $request->pid . " order by ddd.Id desc) as d " . $searchstring);
			} else {
				$vnddataTotalFilter = DB::select("select count(*) as Total from (select * from (SELECT GetTotalOSROrderPieces(son.Id) as TotalSRPieces, DATE_FORMAT(son.CreatedDate, '%d/%m/%Y') as CreatedDate, son.Id, p.Name, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, concat(son.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SalesReturnNumber FROM `outletsalesreturn` s inner join article a on a.Id=s.ArticleId left join outletsalesreturnnumber son on s.SalesReturnNumber=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=s.UserId inner join outlet o on o.Id=s.OutletId group by s.SalesReturnNumber) as ddd order by ddd.Id desc) as d " . $searchstring);
			}


			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		} else {
			$searchstring = "";
			$vnddataTotalFilterValue = $vnTotal;
		}
		$column = $data["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "son.SalesReturnNumber";
				break;
			case 2:
				$ordercolumn = "p.Name";
				break;
			case 3:
				$ordercolumn = "son.ArticleNumber";
				break;
			case 4:
				$ordercolumn = "son.CreatedDate";
				break;
			default:
				$ordercolumn = "son.SalesReturnNumber";
				break;
		}
		$order = "";
		if ($data["order"][0]["dir"]) {
			$order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
		}


		if ($role[0]->Role != 2) {
			$vnddata = DB::select("select d.* FROM ( SELECT * FROM ( SELECT GetTotalOSROrderPieces(son.Id) AS TotalSRPieces, DATE_FORMAT(son.CreatedDate, '%d/%m/%Y') AS CreatedDate, son.Id, p.UserId, p.Name, GROUP_CONCAT( DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',' ) AS ArticleNumber, CONCAT( son.SalesReturnNumber, '/', fn.StartYear, '-', fn.EndYear ) AS SalesReturnNumber FROM `outletsalesreturn` s LEFT JOIN article a ON a.Id = s.ArticleId LEFT JOIN outletsalesreturnnumber son ON s.SalesReturnNumber = son.Id LEFT JOIN party p ON p.Id = son.PartyId LEFT JOIN financialyear fn ON fn.Id = son.FinancialYearId LEFT JOIN users u ON u.Id = s.UserId LEFT JOIN outward o ON o.Id = s.OutletId GROUP BY s.SalesReturnNumber " . $order . " ) AS ddd WHERE ddd.UserId = " . $request->pid . "  ) AS d" . $searchstring . " limit " . $data["start"] . "," . $length);
		} else {
			$vnddata = DB::select("select d.* from (select * from (SELECT GetTotalOSROrderPieces(son.Id) as TotalSRPieces, DATE_FORMAT(son.CreatedDate, '%d/%m/%Y') as CreatedDate, son.Id, p.Name, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, concat(son.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SalesReturnNumber FROM `outletsalesreturn` s left join article a on a.Id=s.ArticleId left join outletsalesreturnnumber son on s.SalesReturnNumber=son.Id left join party p on p.Id=son.PartyId left join financialyear fn on fn.Id=son.FinancialYearId left join users u on u.Id=s.UserId left join outward o on o.Id=s.OutletId group by s.SalesReturnNumber " . $order . " ) as ddd ) as d  " . $searchstring . "limit " . $data["start"] . "," . $length);
		}



		$totalNoPacks = 0;
		foreach ($vnddata as $vnd) {
			$getOutletsalesreturns = OutletSalesreturn::where('SalesReturnNumber', $vnd->Id)->get();
			foreach ($getOutletsalesreturns as $getOutletsalesreturn) {
				if (strpos($getOutletsalesreturn->NoPacks, ',') !== false) {
					$totalNoPacks += array_sum(explode(",", $getOutletsalesreturn->NoPacks));
				} else {
					$singlecountNoPacks = $getOutletsalesreturn->NoPacks;
					$totalNoPacks += $getOutletsalesreturn->NoPacks;
				}
				$vnd->TotalSRPieces = $totalNoPacks;
			}
			$totalNoPacks = 0;
		}
		return array(
			'datadraw' => $data["draw"],
			'recordsTotal' => $vnTotal,
			'recordsFiltered' => $vnddataTotalFilterValue,
			'response' => 'success',
			'startnumber' => $startnumber,
			'search' => count($vnddata),
			'data' => $vnddata,
		);
	}


	public function DeleteOutletSalesReturn($id, $LoggedId)
	{
		$articledata = DB::select("SELECT slr.SalesReturnNumber, slr.ArticleId, slr.NoPacks, a.ArticleNumber, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, c.Colorflag FROM `outletsalesreturn` slr inner join article a on a.Id=slr.ArticleId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where slr.Id ='" . $id . "'");
		$SalesReturnNoPacks = $articledata[0]->NoPacks;
		$SalesReturnNumber = $articledata[0]->SalesReturnNumber;
		$userName = Users::where('Id', $LoggedId)->first();
		$sodRec = DB::select("select a.ArticleNumber, sn.Id as OutletSalesReturnNumberId, concat(sn.SalesReturnNumber,'/', fn.StartYear,'-',fn.EndYear) as OutletSalesReturnnumber from outletsalesreturn s inner join outletsalesreturnnumber sn on sn.Id=s.SalesReturnNumber inner join article a on a.Id=s.ArticleId inner join financialyear fn on fn.Id=sn.FinancialYearId where s.Id= '" . $id . "'");
		
		if ($articledata[0]->ArticleOpenFlag == 1) {
			
			$getdata = DB::select("SELECT NoPacks FROM `mixnopacks` where ArticleId='" . $articledata[0]->ArticleId . "'");
			$InwardNoPacks = $getdata[0]->NoPacks;
			$NoPacks = $SalesReturnNoPacks;
			if ($InwardNoPacks < $NoPacks) {
				return response()->json(array("Alreadyexist" => "true"), 200);
			} else {
				$totalnopacks = ($InwardNoPacks - $NoPacks);
				DB::beginTransaction();
				try {
					
					
					
				
				//Nitin Art Stock Status
			
						$r = DB::table('outletsalesreturn')->where('Id', '=', $id)->first();
						$DeleteNoPackes = $r->NoPacks;
						$ArticleId = $r->ArticleId;
						$OutletPartyId = $r->OutletPartyId;
						
						
								// Fetch the current SalesNoPacks value
						$currentSalesNoPacks = DB::table('artstockstatus')
							->where(['outletId' => $OutletPartyId])
							->where(['ArticleId' => $ArticleId])
							->value('SalesNoPacks');
						
						 $artD = DB::table('article')
							->where('Id', $ArticleId)
							->first();
						
						// Calculate the new SalesNoPacks value by adding the new value to the current value
						$newSalesNoPacks = $currentSalesNoPacks - $DeleteNoPackes;
						
						// Perform the updateOrInsert operation with the new SalesNoPacks value
						DB::table('artstockstatus')->updateOrInsert(
							[
								'outletId' => $OutletPartyId,
								'ArticleId' => $ArticleId
							],
							[
								'SalesNoPacks' => $newSalesNoPacks,
								'TotalPieces' => $newSalesNoPacks
							]
						);
					//Colse
					
					
					
					
					UserLogs::create([
						'Module' => 'Outlet Sales Return',
						'ModuleNumberId' => $sodRec[0]->OutletSalesReturnNumberId,
						'LogType' => 'Deleted',
						'LogDescription' => $userName->Name . " " . 'deleted article ' . $sodRec[0]->ArticleNumber . ' from OutletSalesReturn Number ' . $sodRec[0]->OutletSalesReturnnumber,
						'UserId' => $userName->Id,
						'updated_at' => null
					]);
					DB::table('mixnopacks')
						->where('ArticleId', $articledata[0]->ArticleId)
						->update(['NoPacks' => $totalnopacks]);
					DB::table('outletsalesreturn')
						->where('Id', '=', $id)
						->delete();
					DB::table('outletsalesreturnpacks')
						->where('SalesReturnId', '=', $id)
						->delete();
					DB::commit();
					return response()->json(array("id" => "SUCCESS"), 200);
				} catch (\Exception $e) {
					DB::rollback();
					return response()->json(array("id" => ""), 200);
				}
			}
		} else {
			DB::beginTransaction();
			try {
				
				
				
				
				//Nitin Art Stock Status
					$r = DB::table('outletsalesreturn')->where('Id', '=', $id)->first();
					
						$NoPacks = $r->NoPacks;
						$data['ArticleId'] = $r->ArticleId;
						$data['OutletPartyId'] = $r->OutletPartyId;
				
				
				
				$currentSalesNoPacks = DB::table('artstockstatus')
						->where(['outletId' => $data['OutletPartyId']])
						->where(['ArticleId' => $data['ArticleId']])
						->value('SalesNoPacks');
					
					// Check if $currentSalesNoPacks is not empty
					if ($currentSalesNoPacks !== null) {
						// Convert comma-separated values to arrays
						$currentSalesNoPacksArray = explode(',', $currentSalesNoPacks);
						$dataNoPacksNewArray = explode(',', $NoPacks);
					
						// Perform element-wise addition
						$newSalesNoPacksArray = [];
	
						for ($i = 0; $i < count($dataNoPacksNewArray); $i++) {
							$newSalesNoPacksArray[$i] = (int)$currentSalesNoPacksArray[$i] - (int)$dataNoPacksNewArray[$i];
						}
					
						// Convert back to comma-separated string
						$newSalesNoPacks = implode(',', $newSalesNoPacksArray);
						$artD = DB::table('article')
							->where('Id', $data['ArticleId'])
							->first();
						// Perform the updateOrInsert operation with the new SalesNoPacks value
						
						$packes = $newSalesNoPacks;
						$packesArray = explode(',', $packes);
						$sum = array_sum($packesArray);
						DB::table('artstockstatus')->updateOrInsert(
							[
								'outletId' => $data['OutletPartyId'],
								'ArticleId' => $data['ArticleId']
							],
							[
								'Title' => $artD->Category,
								'ArticleNumber' => $artD->ArticleNumber,
								'SalesNoPacks' => $newSalesNoPacks,
								'TotalPieces' => $sum
							   
							]
						);
					} else {
						$dataNoPacksNewArray = explode(',', $NoPacks);
						// Convert back to comma-separated string
						$newSalesNoPacks = implode(',', $dataNoPacksNewArray);
						$artD = DB::table('article')
							->where('Id', $data['ArticleId'])
							->first();
						// Perform the updateOrInsert operation with the new SalesNoPacks value
						
						
						$packes = $newSalesNoPacks;
						$packesArray = explode(',', $packes);
						$sum = array_sum($packesArray);
	
						DB::table('artstockstatus')->updateOrInsert(
							[
								'outletId' => $data['OutletPartyId'],
								'ArticleId' => $data['ArticleId']
							],
							[
								'Title' => $artD->Category,
								'ArticleNumber' => $artD->ArticleNumber,
								'SalesNoPacks' => $newSalesNoPacks,
								'TotalPieces' => $sum
							]
						);
					}
	
					//Close
				
				
				
				
				UserLogs::create([
					'Module' => 'Outlet Sales Return',
					'ModuleNumberId' => $sodRec[0]->OutletSalesReturnNumberId,
					'LogType' => 'Deleted',
					'LogDescription' => $userName->Name . " " . 'deleted article ' . $sodRec[0]->ArticleNumber . ' from OutletSalesReturn Number ' . $sodRec[0]->OutletSalesReturnnumber,
					'UserId' => $userName->Id,
					'updated_at' => null
				]);
				DB::table('outletsalesreturnpacks')
					->where('SalesReturnId', '=', $id)
					->delete();
				DB::table('outletsalesreturn')
					->where('Id', '=', $id)
					->delete();
				DB::commit();
				return response()->json(array("SRNumberId" => $SalesReturnNumber, "id" => "SUCCESS"), 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json(array("id" => ""), 200);
			}
		}
	}


	public function GetOutletSalesReturnChallan($Id)
	{
		$getdata = DB::select("SELECT srn.OutletPartyId FROM `outletsalesreturn` s inner join outletsalesreturnnumber srn on srn.Id=s.SalesReturnNumber where s.SalesReturnNumber ='" . $Id . "'");
		if ($getdata[0]->OutletPartyId == 0) {
			$sl = 0;
			$getsrchallen = DB::select("SELECT concat(otn.OutletNumber,'/' ,fn2.StartYear,'-',fn2.EndYear) as OutletNumber, concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, '%Y-%m-%d') as SRDate, srn.Remarks, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate11, slr.OutletRate as SRArticleRate  FROM `outletsalesreturn` slr inner join article a on a.Id=slr.ArticleId inner join category c on c.Id=a.CategoryId inner join users u on u.Id=slr.UserId inner join outletsalesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join party p on p.Id = srn.PartyId  inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join outlet o on o.Id=slr.OutletId inner join outletnumber otn on otn.Id=o.OutletNumberId inner join financialyear fn2 on fn2.Id=otn.FinancialYearId where slr.SalesReturnNumber='" . $Id . "'");
		} else {
			$sl = 1;
			$getsrchallen = DB::select("SELECT concat(otn.OutletNumber,'/' ,fn2.StartYear,'-',fn2.EndYear) as OutletNumber,concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, '%Y-%m-%d') as SRDate, srn.Remarks, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, pp.Name as OutletPartyName, pp.Address as OutletPartyAddress, pp.GSTNumber as OutletPartyGSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate11, slr.OutletRate as SRArticleRate  FROM `outletsalesreturn` slr inner join article a on a.Id=slr.ArticleId inner join category c on c.Id=a.CategoryId inner join users u on u.Id=slr.UserId inner join outletsalesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join party p on p.Id = srn.PartyId inner join party pp on pp.Id=srn.OutletPartyId inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join outlet o on o.Id=slr.OutletId inner join outletnumber otn on otn.Id=o.OutletNumberId inner join financialyear fn2 on fn2.Id=otn.FinancialYearId where slr.SalesReturnNumber='" . $Id . "'ORDER BY c.Title ASC");
		}
		$challendata = [];
		$TotalNoPacks = 0;
		$TotalAmount = 0;
		foreach ($getsrchallen as $vl) {
			$Name = $vl->Name;
			$UserName = $vl->UserName;
			$Address = $vl->Address;
			$GSTNumber = $vl->GSTNumber;
			if ($sl == 1) {
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
			if ($ArticleOpenFlag == 0) {
				$ArticleRatio = $vl->ArticleRatio;
				if (strpos($NoPacks, ',') !== true) {
					$countNoSet = array_sum(explode(",", $NoPacks));
					$TotalNoPacks += array_sum(explode(",", $NoPacks));
				} else {
					$countNoSet = $NoPacks;
					$TotalNoPacks += $NoPacks;
				}
				$ArticleRate = $vl->SRArticleRate;
				$Amount = $countNoSet * $ArticleRate;
				$TotalAmount += $Amount;
				$getcolor = json_decode($vl->ArticleColor);
				$getsize = json_decode($vl->ArticleSize);
				$ArticleColor = "";
				foreach ($getcolor as $vl) {
					$ArticleColor .= $vl->Name . ",";
				}
				$ArticleColor = rtrim($ArticleColor, ',');
				$ArticleSize = "";
				foreach ($getsize as $vl) {
					$ArticleSize .= $vl->Name . ",";
				}
				$ArticleSize = rtrim($ArticleSize, ',');
			} else {
				$countNoSet = $NoPacks;
				$TotalNoPacks += $NoPacks;
				$TotalCQty = "";
				$ArticleRatio = "";
				$ArticleRate = $vl->SRArticleRate;
				$Amount = $countNoSet * $ArticleRate;
				$TotalAmount += $Amount;
				$ArticleColor = "";
				$ArticleSize = "";
			}


			$numbers_array = explode(",", $NoPacks);
			$sum = array_sum($numbers_array);
			$TotalQty = $sum;

			$numbers_colorQty = explode(",", $ArticleColor);

			$numbers_packQty = explode(",", $NoPacks);

			$C = array_combine($numbers_colorQty, $numbers_packQty);

			$jsonData = json_encode($C);

			$arrayData = json_decode($jsonData, true);
			$TotalCQty = "";
			$allkey = "";
			$allvalue = "";
			foreach ($arrayData as $key => $value) {
				if (!empty($key)) {
					$TotalCQty .= '<b>' . $key . " : " . '</b>' . $value . ", ";
				} else {
					$TotalCQty .= '<b>' . '--' . " : " . '</b>' . $value . ', ';
				}
			}

			$challendata[] = json_decode(json_encode(array("ColorWiseQty" => $TotalCQty, "OutletNumber" => $OutletNumber, "TotalQty" => $TotalQty, "SalesReturnNumber" => $SalesReturnNumber, "UserName" => $UserName, "SRDate" => $SRDate, "Id" => $Id, "Name" => $Name, "Address" => $Address, "Outletdata" => $outletdata, "OutletPartyName" => $OutletPartyName, "OutletPartyAddress" => $OutletPartyAddress, "OutletPartyGSTNumber" => $OutletPartyGSTNumber, "GSTNumber" => $GSTNumber, "Remark" => $Remark, "ArticleNumber" => $ArticleNumber, "Title" => $Title, "ArticleRatio" => $ArticleRatio, "QuantityInSet" => $NoPacks, "ArticleRate" => number_format($ArticleRate, 2), "Amount" => number_format($Amount, 2), "ArticleColor" => $ArticleColor, "ArticleSize" => $ArticleSize)), false);
		}
		$as = array($challendata, array("TotalNoPacks" => $TotalNoPacks, "TotalAmount" => number_format($TotalAmount, 2)));
		return $as;
	}

	public function intransitlistpost(Request $request)
	{
		$partyId = $request->PartyId;
		return [1, 2, 3];
		if ($partyId == 0) {
			return DB::select("SELECT t.*, p.Name as PartyName, o.Id as OutwardNumberId ,  o.OutwardDate, concat(o.OutwardNumber, '/',f.StartYear,'-',f.EndYear) as OutwardNo FROM `transportoutlet` t left join party p on p.Id=t.PartyId left join outwardnumber o on o.Id=t.OutwardNumberId left join financialyear f on f.Id=o.FinancialYearId");
		} else {
			return DB::select("SELECT t.*, p.Name as PartyName, o.Id as OutwardNumberId , o.OutwardDate, concat(o.OutwardNumber, '/',f.StartYear,'-',f.EndYear) as OutwardNo FROM `transportoutlet` t left join party p on p.Id=t.PartyId left join outwardnumber o on o.Id=t.OutwardNumberId left join financialyear f on f.Id=o.FinancialYearId where t.PartyId = " . $partyId);
		}
	}
	public function OutletSalesReturnLogs($OSRONOId)
	{
		return DB::select("select * from (select ul.Id as UserLogsId, ul.Module, ul.LogType, ul.ModuleNumberId, ul.LogDescription, DATE_FORMAT(ul.created_at ,'%d-%m-%Y') as CreatedDate, DATE_FORMAT(ul.created_at ,'%H:%i:%s') as CreatedTime, u.Name, u.Status, ur.Role from userlogs ul inner join users u on u.Id=ul.UserId inner join userrole ur on ur.RoleType=u.Role) as dd where dd.ModuleNumberId= '" . $OSRONOId . "' and dd.Module='Outlet Sales Return' order by dd.UserLogsId desc ");
	}
	public function OutletLogs($OTLNOId)
	{
		return DB::select("select * from (select ul.Id as UserLogsId, ul.Module, ul.LogType, ul.ModuleNumberId, ul.LogDescription, DATE_FORMAT(ul.created_at ,'%d-%m-%Y') as CreatedDate, DATE_FORMAT(ul.created_at ,'%H:%i:%s') as CreatedTime, u.Name, u.Status, ur.Role from userlogs ul inner join users u on u.Id=ul.UserId inner join userrole ur on ur.RoleType=u.Role) as dd where dd.ModuleNumberId= '" . $OTLNOId . "' and dd.Module='Outlet' order by dd.UserLogsId desc ");
	}
	public function OutletTransportLogs($id)
	{
		return DB::select("select * from (select ul.Id as UserLogsId, ul.Module, ul.LogType, ul.ModuleNumberId, ul.LogDescription, DATE_FORMAT(ul.created_at ,'%d-%m-%Y') as CreatedDate, DATE_FORMAT(ul.created_at ,'%H:%i:%s') as CreatedTime, u.Name, u.Status, ur.Role from userlogs ul inner join users u on u.Id=ul.UserId inner join userrole ur on ur.RoleType=u.Role) as dd where dd.ModuleNumberId= '" . $id . "' and dd.Module='Outlet Transport' order by dd.UserLogsId desc ");
	}
}