<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Article;
use App\Inward;
use App\Outward;
use App\OutwardNumber;
use App\Purchasereturns;
use App\Salesreturn;
use App\SO;
use App\PO;
use App\Stockshortage;
use App\Stocktransfer;
use Dotenv\Regex\Success;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BugsController extends Controller
{
    public function getBugs()
    {
        /* Check Duplication record in so and outward table */
        $duplicateSoRecords = DB::select("SELECT a.* FROM so a JOIN (SELECT SoNumberId  , ArticleId , COUNT(*) FROM so GROUP BY SoNumberId , ArticleId HAVING count(*) > 1 ) b ON a.SoNumberId  = b.SoNumberId  AND a.ArticleId = b.ArticleId ORDER BY a.SoNumberId");

        $duplicateOutwardRecords = DB::select("SELECT a.* FROM outward a JOIN (SELECT OutwardNumberId , ArticleId , COUNT(*) FROM outward GROUP BY OutwardNumberId, ArticleId HAVING count(*) > 1 ) b ON a.OutwardNumberId = b.OutwardNumberId AND a.ArticleId = b.ArticleId ORDER BY a.OutwardNumberId");
        
        $duplicateSalesReturns =  DB::select("SELECT a.* FROM salesreturn a JOIN (SELECT SalesReturnNumber , OutwardId , ArticleId , CreatedDate , COUNT(*) FROM salesreturn GROUP BY SalesReturnNumber , OutwardId , ArticleId , CreatedDate HAVING count(*) > 1 ) b ON a.SalesReturnNumber= b.SalesReturnNumber AND a.OutwardId =b.OutwardId  AND a.ArticleId = b.ArticleId AND a.CreatedDate = b.CreatedDate ORDER BY a.CreatedDate");
        $duplicateStockTranfers =  DB::select("SELECT a.* FROM stocktransfer a JOIN (SELECT StocktransferNumberId , ConsumedArticleId , TransferArticleId , COUNT(*) FROM stocktransfer GROUP BY StocktransferNumberId, ConsumedArticleId , TransferArticleId HAVING count(*) > 1) b ON a.StocktransferNumberId = b.StocktransferNumberId AND a.ConsumedArticleId = b.ConsumedArticleId AND a.TransferArticleId = b.TransferArticleId ORDER BY a.StocktransferNumberId");
        // print_r(["duplicateSoRecords" => $duplicateSoRecords, 'duplicateOutwardRecords' => $duplicateOutwardRecords, "duplicateSalesReturns" => $duplicateSalesReturns ,"duplicateStockTranfers"=>$duplicateStockTranfers]);

        /* Check OutwardSalesRemaining in so table */
        $allSOrecords  = SO::get();
        // return $allSOrecords;
        $AllArticlesSoOutwardCheck = [];
        // return $AllArticlesSoOutwardCheck;
        foreach ($allSOrecords as $allSOrecord) {
            $outwardNumbers = OutwardNumber::where('SoId', $allSOrecord->SoNumberId)->get();
            // return $outwardNumbers;
            if (count($outwardNumbers) != 0) {
                $OutwardRemainingPacksNew = explode(",",  $allSOrecord->NoPacks);
                // return $OutwardRemainingPacksNew;
                $OutwardNoPacksNew = $allSOrecord->NoPacks;
                // return $OutwardNoPacksNew;
                foreach ($outwardNumbers as $outwardNumber) {
                    $allOutwards = Outward::where(['OutwardNumberId' => $outwardNumber->Id, 'ArticleId' => $allSOrecord->ArticleId])->get();
                    // return ($allOutwards);
                    if (count($allOutwards) != 0) {
                        if (strpos($allSOrecord->NoPacks, ",") != false) {
                            foreach ($allOutwards as $allOutward) {
                                $outwardnoPacks =  explode(",", $allOutward->NoPacks);
                                for ($i = 0; $i < count(explode(",",  $allSOrecord->NoPacks)); $i++) {
                                    $OutwardRemainingPacksNew[$i] = $OutwardRemainingPacksNew[$i] - (int)$outwardnoPacks[$i];
                                      
                                }

                            }
                        } else {
                            foreach ($allOutwards as $allOutward) {
                                $OutwardNoPacksNew = $OutwardNoPacksNew - $allOutward->NoPacks;
                            }
                        }
                    }
                }
                if (strpos($allSOrecord->NoPacks, ",") != false) {
                    $OutwardNoPacksNewImplode = implode(",", $OutwardRemainingPacksNew);
                    // return gettype($OutwardNoPacksNewImplode);
                    if ($OutwardNoPacksNewImplode != $allSOrecord->OutwardNoPacks) {
                        // SO::where('Id', $allSOrecord->Id)->update(['OutwardNoPacks' => $OutwardNoPacksNewImplode]);
                        array_push($AllArticlesSoOutwardCheck, json_encode(["SOId" => $allSOrecord->Id, 'oRemPacks' => $OutwardNoPacksNewImplode]));
                        // return $AllArticlesSoOutwardCheck;
                    }
                } else {
                    if ($OutwardNoPacksNew != $allSOrecord->OutwardNoPacks) {
                        // SO::where('Id', $allSOrecord->Id)->update(['OutwardNoPacks' => $OutwardNoPacksNew]);
                        array_push($AllArticlesSoOutwardCheck, json_encode(["SOId" => $allSOrecord->Id, 'oRemPacks' => $OutwardNoPacksNew]));
                    }
                }
            }
            // return ($AllArticlesSoOutwardCheck); 
        }
        // print_r($AllArticlesSoOutwardCheck);

        /* Check SalesRemainingPacks in inward and remaining stocks of articles and fix */

        $outerinwards  = Inward::get();
        $allArticlesIds = [];
        foreach ($outerinwards as $outerinward) {
            $ArticleId =  $outerinward->ArticleId;
            $inwards  = Inward::where('ArticleId', $ArticleId)->get();
            $outwards  = Outward::where('ArticleId', $ArticleId)->get();
            $salesreturns = Salesreturn::where('ArticleId', $ArticleId)->get();
            $purchasereturns = Purchasereturns::where('ArticleId', $ArticleId)->get();
            $consumestocktransfers = Stocktransfer::where('ConsumedArticleId', $ArticleId)->get();
            $transferstocktransfers = Stocktransfer::where('TransferArticleId', $ArticleId)->get();
            $shortedStocks = Stockshortage::where('ArticleId', $ArticleId)->get();
            $sorecords  = SO::where('ArticleId', $ArticleId)->where('Status', 0)->get();
            if (strpos($outerinward->NoPacks, ',') !== false) {
                $SalesNoPacks = [];
                foreach (explode(",", $outerinward->NoPacks) as $makearray) {
                    array_push($SalesNoPacks, 0);
                }
                for ($i = 0; $i < count(explode(",", $outerinward->NoPacks)); $i++) {
                    foreach ($inwards  as $inward) {
                        $noPacks = explode(",", $inward->NoPacks);
                        $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
                    }
                    foreach ($salesreturns  as $salesreturn) {
                        $noPacks = explode(",", $salesreturn->NoPacks);
                        $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
                    }
                    foreach ($outwards  as $outward) {
                        $noPacks = explode(",", $outward->NoPacks);
                        $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                    }
                    foreach ($purchasereturns  as $purchasereturn) {
                        $noPacks = explode(",", $purchasereturn->ReturnNoPacks);
                        $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                    }
                    foreach ($consumestocktransfers  as $stocktransfer) {
                        $noPacks = explode(",", $stocktransfer->ConsumedNoPacks);

                        $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                    }
                    foreach ($transferstocktransfers  as $stocktransfer) {
                        $noPacks = explode(",", $stocktransfer->TransferNoPacks);
                        $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
                    }
                    foreach ($shortedStocks  as $shortedStock) {
                        $noPacks = explode(",", $shortedStock->NoPacks);
                        $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                    }
                    if (!empty($sorecords)) {
                        foreach ($sorecords  as $sorecord) {
                            $OutwardNoPacks = explode(",", $sorecord->OutwardNoPacks);
                            $SalesNoPacks[$i] = $SalesNoPacks[$i] - $OutwardNoPacks[$i];
                        }
                    }
                }
                $newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
                $inward = Inward::where('Id', $outerinward->Id)->first();
                if ($newimplodeSalesNoPacks !== $inward->SalesNoPacks || $newimplodeSalesNoPacks < 0) {
                    array_push($allArticlesIds, $inward->ArticleId);
                }
                //  Inward::where('Id', $outerinward->Id)->update(['SalesNoPacks' => $newimplodeSalesNoPacks]);
            } else {
                $articlerecord = Article::where('Id', $outerinward->ArticleId)->first();
                if ($articlerecord) {
                    if ($articlerecord->ArticleOpenFlag == 0) {
                        $SalesNoPacks = 0;
                        foreach ($inwards  as $inward) {
                            $SalesNoPacks = $SalesNoPacks + $inward->NoPacks;
                        }
                        foreach ($salesreturns  as $salesreturn) {
                            $SalesNoPacks = $SalesNoPacks + $salesreturn->NoPacks;
                        }
                        foreach ($outwards  as $outward) {
                            $SalesNoPacks = $SalesNoPacks - $outward->NoPacks;
                        }
                        foreach ($purchasereturns  as $purchasereturn) {
                            $SalesNoPacks = $SalesNoPacks - $purchasereturn->ReturnNoPacks;
                        }
                        foreach ($consumestocktransfers  as $stocktransfer) {
                            $SalesNoPacks = $SalesNoPacks - $stocktransfer->ConsumedNoPacks;
                        }
                        foreach ($transferstocktransfers  as $stocktransfer) {
                            $SalesNoPacks = $SalesNoPacks + $stocktransfer->TransferNoPacks;
                        }
                        foreach ($shortedStocks  as $shortedStock) {
                            $SalesNoPacks = $SalesNoPacks - $shortedStock->NoPacks;
                        }
                        if (!empty($sorecords)) {
                            foreach ($sorecords  as $sorecord) {
                                $SalesNoPacks = $SalesNoPacks - $sorecord->OutwardNoPacks;
                            }
                        }
                        $inward = Inward::where('Id', $outerinward->Id)->first();
                        if ($SalesNoPacks !== (int)$inward->SalesNoPacks || $SalesNoPacks < 0) {
                            array_push($allArticlesIds, $inward->ArticleId);
                        }
                        //  Inward::where('Id', $outerinward->Id)->update(['SalesNoPacks' => $SalesNoPacks]);
                    }
                }
            }
        }
        //  print_r($allArticlesIds);
        return ["allArticlesIds" => $allArticlesIds, "AllArticlesSoOutwardCheck" => $AllArticlesSoOutwardCheck, "DuplicatedRecords" => ["duplicateSoRecords" => $duplicateSoRecords, 'duplicateOutwardRecords' => $duplicateOutwardRecords, "duplicateSalesReturns" => $duplicateSalesReturns, "duplicateStockTranfers" => $duplicateStockTranfers]];
    }

    public function salesReturnDuplication()
    {
        $salesreturnduplications =   DB::select("SELECT a.Id as SalesReturnId ,a.ArticleId, a.NoPacks ,DATE_FORMAT(a.CreatedDate , '%d-%m-%Y') as CreatedDate, DATE_FORMAT(a.CreatedDate , '%Y-%m-%d') as DeleteCreatedDate,srn.Id as SalesReturnNumberId,article.ArticleNumber, concat(srn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SalesReturnNumber  FROM salesreturn a JOIN (SELECT SalesReturnNumber , OutwardId , ArticleId , CreatedDate , COUNT(*) FROM salesreturn GROUP BY SalesReturnNumber , OutwardId , ArticleId , CreatedDate HAVING count(*) > 1 ) b ON a.SalesReturnNumber= b.SalesReturnNumber inner join salesreturnnumber srn on a.SalesReturnNumber=srn.Id inner join article on  a.ArticleId=article.Id inner join financialyear fn on fn.Id=srn.FinancialYearId AND a.OutwardId =b.OutwardId  AND a.ArticleId = b.ArticleId AND a.CreatedDate = b.CreatedDate ORDER BY a.CreatedDate");
        // return $salesreturnduplications;
        if (count($salesreturnduplications)) {
            $arrayCreateDuplicate = [];
            //Key will be sonumberid and value will be article
            foreach ($salesreturnduplications as $key => $salesreturnduplication) {
                if (array_key_exists($salesreturnduplication->SalesReturnNumber, $arrayCreateDuplicate)) {
                    if ($arrayCreateDuplicate[$salesreturnduplication->SalesReturnNumber] != $salesreturnduplication->ArticleId) {
                        $arrayCreateDuplicate[$salesreturnduplication->SalesReturnNumber] = $salesreturnduplication->ArticleId;
                    } else {
                        unset($salesreturnduplications[$key]);
                    }
                } else {
                    $arrayCreateDuplicate[$salesreturnduplication->SalesReturnNumber] = $salesreturnduplication->ArticleId;
                }
            }
            return response()->json(['status' => "success", "salesreturnDuplication" => array_values($salesreturnduplications)], 200);
        } else {
            return response()->json(['status' => "failed"], 200);
        }
    }

    public function soDuplication()
    {
        $soDuplications =  DB::select("SELECT a.Id as SoId,a.ArticleId as ArticleId ,a.SoNumberId , a.NoPacks as NoPacks, a.OutwardNoPacks ,a.ArticleRate,a.Status ,DATE_FORMAT(a.created_at , '%d-%m-%Y') as CreatedDate, concat(IFNULL(partyuser.Name,u.Name),son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber,article.ArticleNumber  FROM so a JOIN (SELECT SoNumberId  , ArticleId , COUNT(*) FROM so GROUP BY SoNumberId , ArticleId HAVING count(*) > 1 ) b ON a.SoNumberId  = b.SoNumberId inner join sonumber son on son.Id=a.SoNumberId  inner join party p on p.Id=son.PartyId  left join users partyuser on partyuser.Id=p.UserId inner join users u on u.Id=son.UserId inner join financialyear fn on fn.Id=son.FinancialYearId inner join article on  article.Id=a.ArticleId AND a.ArticleId = b.ArticleId ORDER BY a.SoNumberId");
        if (count($soDuplications)) {
            $arrayCreateDuplicate = [];
            //Key will be sonumberid and value will be article
            foreach ($soDuplications as $key => $soDuplication) {
                if (array_key_exists($soDuplication->SoNumberId, $arrayCreateDuplicate)) {
                    if ($arrayCreateDuplicate[$soDuplication->SoNumberId] != $soDuplication->ArticleId) {
                        $arrayCreateDuplicate[$soDuplication->SoNumberId] = $soDuplication->ArticleId;
                    } else {
                        unset($soDuplications[$key]);
                    }
                } else {
                    $arrayCreateDuplicate[$soDuplication->SoNumberId] = $soDuplication->ArticleId;
                }
            }
            return response()->json(['status' => "success", "soDuplication" => array_values($soDuplications)], 200);
        } else {
            return response()->json(['status' => "failed"], 200);
        }
    }
    public function outwardDuplication()
    {
        $outwardDuplications =  DB::select("SELECT a.Id as OutwardId , a.ArticleId , a.OutwardNumberId , article.ArticleNumber ,own.Id as OutwardNumberId , concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber ,a.NoPacks ,a.OutwardRate,DATE_FORMAT(a.created_at , '%d-%m-%Y') as CreatedDate FROM outward a JOIN (SELECT OutwardNumberId , ArticleId , COUNT(*) FROM outward GROUP BY OutwardNumberId, ArticleId HAVING count(*) > 1 ) b ON a.OutwardNumberId = b.OutwardNumberId  inner join article on article.Id=a.ArticleId inner join outwardnumber own on own.Id=a.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId  AND a.ArticleId = b.ArticleId ORDER BY a.OutwardNumberId");
        if (count($outwardDuplications)) {
            $arrayCreateDuplicate = [];
            //Key will be outwardnumberid and value will be article
            foreach ($outwardDuplications as $key => $outwardDuplication) {
                if (array_key_exists($outwardDuplication->OutwardNumberId, $arrayCreateDuplicate)) {
                    if ($arrayCreateDuplicate[$outwardDuplication->OutwardNumberId] != $outwardDuplication->ArticleId) {
                        $arrayCreateDuplicate[$outwardDuplication->OutwardNumberId] = $outwardDuplication->ArticleId;
                    } else {
                        unset($outwardDuplications[$key]);
                    }
                } else {
                    $arrayCreateDuplicate[$outwardDuplication->OutwardNumberId] = $outwardDuplication->ArticleId;
                }
            }
            return response()->json(['status' => "success", "outwardDuplication" => array_values($outwardDuplications)], 200);
        } else {
            return response()->json(['status' => "failed"], 200);
        }
    }
    public function deleteSoDuplication($sonumberid, $articleid)
    {
        $sorecord = SO::where('SoNumberId', $sonumberid)->where('ArticleId', $articleid)->first();
        $sorecords = SO::where('Id', '!=',  $sorecord->Id)->where('SoNumberId', $sonumberid)->where('ArticleId', $articleid)->get();
        foreach ($sorecords as $sorecordinner) {
            SO::where('Id', $sorecordinner->Id)->delete();
        }
        return response()->json(['status' => "success"], 200);
    }
    public function deleteOutwardDuplication($outwardnumberid, $articleid)
    {
        $outwardrecord = Outward::where('OutwardNumberId', $outwardnumberid)->where('ArticleId', $articleid)->first();
        $outwardrecords = Outward::where('Id', '!=',  $outwardrecord->Id)->where('OutwardNumberId', $outwardnumberid)->where('ArticleId', $articleid)->get();
        foreach ($outwardrecords as $outwardrecinner) {
            Outward::where('Id', $outwardrecinner->Id)->delete();
        }
        return response()->json(['status' => "success"], 200);
    }
    public function deleteSalesreturnDuplication($salesreturnnumberid, $articleid, $CreatedDate)
    {
        $salesreturnrecord = Salesreturn::where('SalesReturnNumber', $salesreturnnumberid)->where('ArticleId', $articleid)->where('CreatedDate', 'like', '%' . $CreatedDate . '%')->first();
        $salesreturnrecords = Salesreturn::where('Id', '!=',  $salesreturnrecord->Id)->where('SalesReturnNumber', $salesreturnnumberid)->where('ArticleId', $articleid)->where('CreatedDate', 'like', '%' . $CreatedDate . '%')->get();
        foreach ($salesreturnrecords as $salesreturnuinner) {
            Salesreturn::where('Id', $salesreturnuinner->Id)->delete();
        }
        return response()->json(['status' => "success"], 200);
    }
    public function outwardSoRemaining()
    {
        $allSOrecords =  DB::select('select Id , Status , DATE_FORMAT(created_at, "%d/%m/%Y") as created_at , NoPacks , OutwardNoPacks ,ArticleId ,SoNumberId from so');
        // $allSOrecords  = SO::get();
        $AllArticlesSoOutwardCheck = [];
        foreach ($allSOrecords as $key => $allSOrecord) {
            $outwardNumbers =  DB::select("select Id from outwardnumber where SoId=" . $allSOrecord->SoNumberId);
            // return $allSOrecord->NoPacks;
            // $outwardNumbers = OutwardNumber::where('SoId', $allSOrecord->SoNumberId)->get();
            if (count($outwardNumbers) != 0) {
                $OutwardRemainingPacksNew = explode(",",  $allSOrecord->NoPacks);
                $OutwardNoPacksNew = $allSOrecord->NoPacks;
                foreach ($outwardNumbers as $outwardNumber) {
                    // return $outwardNumber->Id;
                    $allOutwards =  DB::select("select NoPacks from outward where OutwardNumberId=" . $outwardNumber->Id . " and ArticleId=" . $allSOrecord->ArticleId);
                    // $allOutwards = Outward::where(['OutwardNumberId' => $outwardNumber->Id, 'ArticleId' => $allSOrecord->ArticleId])->get();
                    if (count($allOutwards) != 0) {
                        if (strpos($allSOrecord->NoPacks, ",") != false) {
                            foreach ($allOutwards as $allOutward) {
                                $outwardnoPacks =  explode(",", $allOutward->NoPacks);
                                for ($i = 0; $i < count(explode(",",  $allSOrecord->NoPacks)); $i++) {
                                    $OutwardRemainingPacksNew[$i] = $OutwardRemainingPacksNew[$i] - (int)$outwardnoPacks[$i];
                                }
                            }
                        } else {
                            foreach ($allOutwards as $allOutward) {
                                $OutwardNoPacksNew = $OutwardNoPacksNew - $allOutward->NoPacks;
                            }
                        }
                    }
                }
                if (strpos($allSOrecord->NoPacks, ",") != false) {
                    $OutwardNoPacksNewImplode = implode(",", $OutwardRemainingPacksNew);
                    // return ["1st" => $OutwardNoPacksNewImplode ,'2nd' => (int)$allSOrecord->OutwardNoPacks];
                    if ($OutwardNoPacksNewImplode != (int)$allSOrecord->OutwardNoPacks) {
                        $sodetails =  DB::select("select article.ArticleNumber, concat(IFNULL(partyuser.Name,u.Name),son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber from  so s left join sonumber son on s.SoNumberId=son.Id left join financialyear fn on fn.Id=son.FinancialYearId  inner join party p on p.Id=son.PartyId  left join users partyuser on partyuser.Id=p.UserId left join users u on u.Id=son.UserId  left join article on  article.Id=s.ArticleId where s.Id =$allSOrecord->Id");
                        array_push($AllArticlesSoOutwardCheck, ["SoNumber" => $sodetails[0]->SoNumber, 'ArticleNumber' => $sodetails[0]->ArticleNumber,  "SoId" => $allSOrecord->Id, "SoNumberId" => $allSOrecord->SoNumberId, 'ArticleId' => $allSOrecord->ArticleId, 'OutwardNoPacks' => $allSOrecord->OutwardNoPacks, 'Status' => $allSOrecord->Status, 'OutwardNoPacksActual' => $OutwardNoPacksNewImplode, 'CreatedAt' => $allSOrecord->created_at]);
                    }
                } else {
                    // return ["1stt" => $OutwardNoPacksNew ,'2ndt' => $allSOrecord->OutwardNoPacks];
                    if ((int)$OutwardNoPacksNew != (int)$allSOrecord->OutwardNoPacks) {
                        $sodetails =  DB::select("select article.ArticleNumber, concat(IFNULL(partyuser.Name,u.Name),son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber from  so s left join sonumber son on s.SoNumberId=son.Id left join financialyear fn on fn.Id=son.FinancialYearId  inner join party p on p.Id=son.PartyId  left join users partyuser on partyuser.Id=p.UserId left join users u on u.Id=son.UserId  left join article on  article.Id=s.ArticleId where s.Id =$allSOrecord->Id");
                        array_push($AllArticlesSoOutwardCheck, ["SoNumber" => $sodetails[0]->SoNumber, 'ArticleNumber' => $sodetails[0]->ArticleNumber, "SoId" => $allSOrecord->Id, "SoNumberId" => $allSOrecord->SoNumberId, 'ArticleId' => $allSOrecord->ArticleId, 'OutwardNoPacks' => $allSOrecord->OutwardNoPacks, 'Status' => $allSOrecord->Status, 'OutwardNoPacksActual' => $OutwardNoPacksNew, 'CreatedAt' => $allSOrecord->created_at]);
                    }
                }
            }
            else{
                if($allSOrecord->NoPacks != $allSOrecord->OutwardNoPacks){
                    $sodetails =  DB::select("select article.ArticleNumber, concat(IFNULL(partyuser.Name,u.Name),son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber from  so s left join sonumber son on s.SoNumberId=son.Id left join financialyear fn on fn.Id=son.FinancialYearId  inner join party p on p.Id=son.PartyId  left join users partyuser on partyuser.Id=p.UserId left join users u on u.Id=son.UserId  left join article on  article.Id=s.ArticleId where s.Id =$allSOrecord->Id");
                    array_push($AllArticlesSoOutwardCheck, ["SoNumber" => $sodetails[0]->SoNumber, 'ArticleNumber' => $sodetails[0]->ArticleNumber, "SoId" => $allSOrecord->Id, "SoNumberId" => $allSOrecord->SoNumberId, 'ArticleId' => $allSOrecord->ArticleId, 'OutwardNoPacks' => $allSOrecord->OutwardNoPacks, 'Status' => $allSOrecord->Status, 'OutwardNoPacksActual' => $allSOrecord->NoPacks, 'CreatedAt' => $allSOrecord->created_at]);
                }
            }
        }
        if (count($AllArticlesSoOutwardCheck)) {
            return response()->json(['status' => "success", "outwardSalesRemaning" => $AllArticlesSoOutwardCheck], 200);
        } else {
            return response()->json(['status' => "failed"], 200);
        }
    }
    public function fixOutwardSalesremaining($soid, $OutwardNoPacksActual)
    {
        SO::where('Id', $soid)->update(['OutwardNoPacks' => $OutwardNoPacksActual]);
        return response()->json(['status' => "success"], 200);
    }


    public function soRemaining()
    {
        $outerinwards  =  DB::select("select i.Id , i.ArticleId , i.NoPacks , i.SalesNoPacks , concat(ig.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number from inward i inner join inwardgrn ig on ig.Id=i.GRN  left join financialyear fn on fn.Id=ig.FinancialYearId");
        $allArticlesIds = [];
        foreach ($outerinwards as $outerinward) {
            $ArticleId =  $outerinward->ArticleId;
            $articleRecord = DB::select("select Id , ArticleOpenFlag , ArticleNumber from article where Id=" . $ArticleId . " limit 1");
            if (count($articleRecord) != 0) {
                $allRecords =  DB::select("select dd.* from ((select so.Id , 8 as type, OutwardNoPacks as Packs ,so.ArticleId ,so.SoNumberId as numberid from so where ArticleId =" . $ArticleId . " and Status = 0) union (select inward.Id , 1 as type, NoPacks as Packs , inward.ArticleId , inward.GRN as numberid from inward where ArticleId = " . $ArticleId . ") union (select outward.Id , 2 as type, NoPacks as Packs ,outward.ArticleId ,outward.OutwardNumberId as numberid from outward where ArticleId = " . $ArticleId . ") union (select salesreturn.Id , 3 as type, NoPacks as Packs ,salesreturn.ArticleId , salesreturn.SalesReturnNumber as numberid from salesreturn where ArticleId = " . $ArticleId . ") union (select purchasereturn.Id , 4 as type, ReturnNoPacks as Packs ,purchasereturn.ArticleId ,purchasereturn.PurchaseReturnNumber as numberid from purchasereturn where ArticleId = " . $ArticleId . ") union (select stocktransfer.Id , 5 as type, ConsumedNoPacks as Packs , stocktransfer.ConsumedArticleId as ArticleId ,stocktransfer.StocktransferNumberId as numberid from stocktransfer where ConsumedArticleId = " . $ArticleId . ") union (select stocktransfer.Id , 6 as type, TransferNoPacks as Packs ,stocktransfer.TransferArticleId as ArticleId ,stocktransfer.StocktransferNumberId as numberid from stocktransfer where TransferArticleId = " . $ArticleId . ") union (select stockshortage.Id , 7 as type, NoPacks as Packs ,stockshortage.ArticleId ,stockshortage.StocktransferNumberId as numberid from stockshortage where ArticleId = " . $ArticleId . ")) as dd;");
                if (strpos($outerinward->NoPacks, ',') !== false) {
                    $SalesNoPacks = [];
                    foreach (explode(",", $outerinward->NoPacks) as $makearray) {
                        array_push($SalesNoPacks, 0);
                    }
                    for ($i = 0; $i < count(explode(",", $outerinward->NoPacks)); $i++) {
                        foreach ($allRecords as $allRecord) {
                            if ($allRecord->type == 1) {
                                $noPacks = explode(",", $allRecord->Packs);
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
                            } else if ($allRecord->type == 2) {
                                $noPacks = explode(",", $allRecord->Packs);
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                            } else if ($allRecord->type == 3) {
                                $noPacks = explode(",", $allRecord->Packs);
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
                            } else if ($allRecord->type == 4) {
                                $noPacks = explode(",", $allRecord->Packs);
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                            } else if ($allRecord->type == 5) {
                                $noPacks = explode(",", $allRecord->Packs);
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                            } else if ($allRecord->type == 6) {
                                $noPacks = explode(",", $allRecord->Packs);
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
                            } else if ($allRecord->type == 7) {
                                $noPacks = explode(",", $allRecord->Packs);
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                            } else if ($allRecord->type == 8) {
                                $noPacks = explode(",", $allRecord->Packs);
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                            }
                        }
                    }
                    $newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
                    if ($newimplodeSalesNoPacks !== $outerinward->SalesNoPacks || $newimplodeSalesNoPacks < 0) {
                        array_push($allArticlesIds, ['inwardgrn' => $outerinward->GRN_Number,  'ArticleNumber' => $articleRecord[0]->ArticleNumber, 'InwardId' => $outerinward->Id, 'ArticleId' => $ArticleId, 'SalesNoPacks' => $outerinward->SalesNoPacks, "ShoulSalesNoPacks" => $newimplodeSalesNoPacks]);
                    }
                } else {
                    if ($articleRecord[0]->ArticleOpenFlag == 0) {
                        $SalesNoPacks = 0;
                        foreach ($allRecords as $allRecord) {
                            if ($allRecord->type == 1) {
                                $SalesNoPacks = $SalesNoPacks + (int)$allRecord->Packs;
                            } else if ($allRecord->type == 2) {
                                $SalesNoPacks = $SalesNoPacks - (int)$allRecord->Packs;
                            } else if ($allRecord->type == 3) {
                                $SalesNoPacks = $SalesNoPacks + (int)$allRecord->Packs;
                            } else if ($allRecord->type == 4) {
                                $SalesNoPacks = $SalesNoPacks - (int)$allRecord->Packs;
                            } else if ($allRecord->type == 5) {
                                $SalesNoPacks = $SalesNoPacks - (int)$allRecord->Packs;
                            } else if ($allRecord->type == 6) {
                                $SalesNoPacks = $SalesNoPacks + (int)$allRecord->Packs;
                            } else if ($allRecord->type == 7) {
                                $SalesNoPacks = $SalesNoPacks - (int)$allRecord->Packs;
                            } else if ($allRecord->type == 8) {
                                $SalesNoPacks = $SalesNoPacks - (int)$allRecord->Packs;
                            }
                        }
                        if ((int)$SalesNoPacks !== (int)$outerinward->SalesNoPacks || $SalesNoPacks < 0) {
                            array_push($allArticlesIds, ['InwardId' => $outerinward->Id, 'inwardgrn' => $outerinward->GRN_Number,  'ArticleNumber' => $articleRecord[0]->ArticleNumber,  'ArticleId' => $ArticleId, 'SalesNoPacks' => $outerinward->SalesNoPacks, "ShoulSalesNoPacks" => $SalesNoPacks]);
                        }
                    }
                }
            }
        }
        if (count($allArticlesIds)) {
            return response()->json(['status' => "success", "soRemaning" => $allArticlesIds], 200);
        } else {
            return response()->json(['status' => "failed"], 200);
        }
    }

    public function fixSoremaining($inwardId, $newsalespacks)
    {
        Inward::where('Id', $inwardId)->update(['SalesNoPacks' => $newsalespacks]);
        return response()->json(['status' => "success"], 200);
    }

    public function allRemaining()
    {
        $mixArticlesMisMatch = [];
        $mixnopacksrecords =  DB::table('mixnopacks')->get();
        foreach ($mixnopacksrecords as $mixnopacksrecord) {
            $totalInward = 0;
            $totalOutwards = 0;
            $inwards  = Inward::where('ArticleId', $mixnopacksrecord->ArticleId)->get();
            $outwards  = Outward::where('ArticleId', $mixnopacksrecord->ArticleId)->get();
            $salesreturns = Salesreturn::where('ArticleId', $mixnopacksrecord->ArticleId)->get();
            $purchasereturns = Purchasereturns::where('ArticleId', $mixnopacksrecord->ArticleId)->get();
            $consumestocktransfers = Stocktransfer::where('ConsumedArticleId', $mixnopacksrecord->ArticleId)->get();
            $transferstocktransfers = Stocktransfer::where('TransferArticleId', $mixnopacksrecord->ArticleId)->get();
            $shortedStocks = Stockshortage::where('ArticleId', $mixnopacksrecord->ArticleId)->get();
            $sorecords  = SO::where('ArticleId', $mixnopacksrecord->ArticleId)->where('Status', 0)->get();
            foreach ($inwards  as $inward) {
                if (strpos($inward->NoPacks, ',') !== false) {
                    $totalInward = $totalInward + array_sum(explode(",", $inward->NoPacks));
                } else {
                    $totalInward = $totalInward + $inward->NoPacks;
                }
            }
            foreach ($salesreturns  as $salesreturn) {
                if (strpos($salesreturn->NoPacks, ',') !== false) {
                    $totalInward = $totalInward + array_sum(explode(",", $salesreturn->NoPacks));
                } else {
                    $totalInward = $totalInward + $salesreturn->NoPacks;
                }
            }
            foreach ($outwards  as $outward) {
                if (strpos($outward->NoPacks, ',') !== false) {
                    $totalOutwards = $totalOutwards + array_sum(explode(",", $outward->NoPacks));
                } else {
                    $totalOutwards = $totalOutwards + $outward->NoPacks;
                }
            }
            foreach ($purchasereturns  as $purchasereturn) {
                if (strpos($purchasereturn->ReturnNoPacks, ',') !== false) {
                    $totalOutwards = $totalOutwards + array_sum(explode(",", $purchasereturn->ReturnNoPacks));
                } else {
                    $totalOutwards = $totalOutwards + $purchasereturn->ReturnNoPacks;
                }
            }
            foreach ($consumestocktransfers  as $stocktransfer) {
                if (strpos($stocktransfer->ConsumedNoPacks, ',') !== false) {
                    $totalOutwards = $totalOutwards + array_sum(explode(",", $stocktransfer->ConsumedNoPacks));
                } else {
                    $totalOutwards = $totalOutwards + $stocktransfer->ConsumedNoPacks;
                }
            }
            foreach ($transferstocktransfers  as $stocktransfer) {
                if (strpos($stocktransfer->TransferNoPacks, ',') !== false) {
                    $totalInward = $totalInward + array_sum(explode(",", $stocktransfer->TransferNoPacks));
                } else {
                    $totalInward = $totalInward + $stocktransfer->TransferNoPacks;
                }
            }
            foreach ($shortedStocks  as $shortedStock) {
                if (strpos($shortedStock->NoPacks, ',') !== false) {
                    $totalOutwards = $totalOutwards + array_sum(explode(",", $shortedStock->NoPacks));
                } else {
                    $totalOutwards = $totalOutwards + $shortedStock->NoPacks;
                }
            }
            if (!empty($sorecords)) {
                foreach ($sorecords  as $sorecord) {
                    if (strpos($sorecord->NoPacks, ',') !== false) {
                        $totalOutwards = $totalInward + array_sum(explode(",", $sorecord->NoPacks));
                    } else {
                        $totalOutwards = $totalOutwards + $sorecord->NoPacks;
                    }
                }
            }
            $TotalRemaining =  $totalInward - $totalOutwards;
            if ($mixnopacksrecord->NoPacks != $TotalRemaining) {
                $articleMix = Article::where('Id', $mixnopacksrecord->ArticleId)->first();
                array_push($mixArticlesMisMatch, ['MixId' => $mixnopacksrecord->Id, 'ArticleId' => $mixnopacksrecord->ArticleId, 'ArticleNumber' => $articleMix->ArticleNumber, 'NoPacks' => $mixnopacksrecord->NoPacks, 'NoPacksShould' => $TotalRemaining, 'ArticleOpenFlag' => "Yes"]);
            }
        }
        if (count($mixArticlesMisMatch) != 0) {
            return response()->json(['mixArticlesMisMatch' => $mixArticlesMisMatch, 'status' => 'success'], 200);
        } else {
            return response()->json(['status' => 'failed'], 200);
        }
    }

    public function fixAllremaining($mixid, $nopacks)
    {
        DB::table('mixnopacks')->where('Id', $mixid)->update(['NoPacks' => $nopacks]);
        return response()->json(['status' => "success"], 200);
    }


    public function fixAllremainingByOnce()
    {
        $mixnopacksrecords =  DB::table('mixnopacks')->get();
        foreach ($mixnopacksrecords as $mixnopacksrecord) {
            $totalInward = 0;
            $totalOutwards = 0;
            $inwards  = Inward::where('ArticleId', $mixnopacksrecord->ArticleId)->get();
            $outwards  = Outward::where('ArticleId', $mixnopacksrecord->ArticleId)->get();
            $salesreturns = Salesreturn::where('ArticleId', $mixnopacksrecord->ArticleId)->get();
            $purchasereturns = Purchasereturns::where('ArticleId', $mixnopacksrecord->ArticleId)->get();
            $consumestocktransfers = Stocktransfer::where('ConsumedArticleId', $mixnopacksrecord->ArticleId)->get();
            $transferstocktransfers = Stocktransfer::where('TransferArticleId', $mixnopacksrecord->ArticleId)->get();
            $shortedStocks = Stockshortage::where('ArticleId', $mixnopacksrecord->ArticleId)->get();
            $sorecords  = SO::where('ArticleId', $mixnopacksrecord->ArticleId)->where('Status', 0)->get();
            foreach ($inwards  as $inward) {
                if (strpos($inward->NoPacks, ',') !== false) {
                    $totalInward = $totalInward + array_sum(explode(",", $inward->NoPacks));
                } else {
                    $totalInward = $totalInward + $inward->NoPacks;
                }
            }
            foreach ($salesreturns  as $salesreturn) {
                if (strpos($salesreturn->NoPacks, ',') !== false) {
                    $totalInward = $totalInward + array_sum(explode(",", $salesreturn->NoPacks));
                } else {
                    $totalInward = $totalInward + $salesreturn->NoPacks;
                }
            }
            foreach ($outwards  as $outward) {
                if (strpos($outward->NoPacks, ',') !== false) {
                    $totalOutwards = $totalOutwards + array_sum(explode(",", $outward->NoPacks));
                } else {
                    $totalOutwards = $totalOutwards + $outward->NoPacks;
                }
            }
            foreach ($purchasereturns  as $purchasereturn) {
                if (strpos($purchasereturn->ReturnNoPacks, ',') !== false) {
                    $totalOutwards = $totalOutwards + array_sum(explode(",", $purchasereturn->ReturnNoPacks));
                } else {
                    $totalOutwards = $totalOutwards + $purchasereturn->ReturnNoPacks;
                }
            }
            foreach ($consumestocktransfers  as $stocktransfer) {
                if (strpos($stocktransfer->ConsumedNoPacks, ',') !== false) {
                    $totalOutwards = $totalOutwards + array_sum(explode(",", $stocktransfer->ConsumedNoPacks));
                } else {
                    $totalOutwards = $totalOutwards + $stocktransfer->ConsumedNoPacks;
                }
            }
            foreach ($transferstocktransfers  as $stocktransfer) {
                if (strpos($stocktransfer->TransferNoPacks, ',') !== false) {
                    $totalInward = $totalInward + array_sum(explode(",", $stocktransfer->TransferNoPacks));
                } else {
                    $totalInward = $totalInward + $stocktransfer->TransferNoPacks;
                }
            }
            foreach ($shortedStocks  as $shortedStock) {
                if (strpos($shortedStock->NoPacks, ',') !== false) {
                    $totalOutwards = $totalOutwards + array_sum(explode(",", $shortedStock->NoPacks));
                } else {
                    $totalOutwards = $totalOutwards + $shortedStock->NoPacks;
                }
            }
            if (!empty($sorecords)) {
                foreach ($sorecords  as $sorecord) {
                    if (strpos($sorecord->NoPacks, ',') !== false) {
                        $totalOutwards = $totalInward + array_sum(explode(",", $sorecord->NoPacks));
                    } else {
                        $totalOutwards = $totalOutwards + $sorecord->NoPacks;
                    }
                }
            }
            $TotalRemaining =  $totalInward - $totalOutwards;
            if ($mixnopacksrecord->NoPacks != $TotalRemaining) {
                DB::table('mixnopacks')->where('ArticleId', $mixnopacksrecord->ArticleId)->update(['NoPacks' => $TotalRemaining]);
            }
        }

        return response()->json(['status' => "success"], 200);
    }

    public function fixSoRemainingByOnce()
    {
        $outerinwards  =  DB::select("select i.Id , i.ArticleId , i.NoPacks , i.SalesNoPacks , concat(ig.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number from inward i inner join inwardgrn ig on ig.Id=i.GRN  left join financialyear fn on fn.Id=ig.FinancialYearId");
        foreach ($outerinwards as $outerinward) {
            $ArticleId =  $outerinward->ArticleId;
            $articleRecord = DB::select("select Id , ArticleOpenFlag , ArticleNumber from article where Id=" . $ArticleId . " limit 1");
            if (count($articleRecord) != 0) {
                $allRecords =  DB::select("select dd.* from ((select so.Id , 8 as type, OutwardNoPacks as Packs ,so.ArticleId ,so.SoNumberId as numberid from so where ArticleId =" . $ArticleId . " and Status = 0) union (select inward.Id , 1 as type, NoPacks as Packs , inward.ArticleId , inward.GRN as numberid from inward where ArticleId = " . $ArticleId . ") union (select outward.Id , 2 as type, NoPacks as Packs ,outward.ArticleId ,outward.OutwardNumberId as numberid from outward where ArticleId = " . $ArticleId . ") union (select salesreturn.Id , 3 as type, NoPacks as Packs ,salesreturn.ArticleId , salesreturn.SalesReturnNumber as numberid from salesreturn where ArticleId = " . $ArticleId . ") union (select purchasereturn.Id , 4 as type, ReturnNoPacks as Packs ,purchasereturn.ArticleId ,purchasereturn.PurchaseReturnNumber as numberid from purchasereturn where ArticleId = " . $ArticleId . ") union (select stocktransfer.Id , 5 as type, ConsumedNoPacks as Packs , stocktransfer.ConsumedArticleId as ArticleId ,stocktransfer.StocktransferNumberId as numberid from stocktransfer where ConsumedArticleId = " . $ArticleId . ") union (select stocktransfer.Id , 6 as type, TransferNoPacks as Packs ,stocktransfer.TransferArticleId as ArticleId ,stocktransfer.StocktransferNumberId as numberid from stocktransfer where TransferArticleId = " . $ArticleId . ") union (select stockshortage.Id , 7 as type, NoPacks as Packs ,stockshortage.ArticleId ,stockshortage.StocktransferNumberId as numberid from stockshortage where ArticleId = " . $ArticleId . ")) as dd;");
                if (strpos($outerinward->NoPacks, ',') !== false) {
                    $SalesNoPacks = [];
                    foreach (explode(",", $outerinward->NoPacks) as $makearray) {
                        array_push($SalesNoPacks, 0);
                    }
                    for ($i = 0; $i < count(explode(",", $outerinward->NoPacks)); $i++) {
                        foreach ($allRecords as $allRecord) {
                            if ($allRecord->type == 1) {
                                $noPacks = explode(",", $allRecord->Packs);
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
                            } else if ($allRecord->type == 2) {
                                $noPacks = explode(",", $allRecord->Packs);
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                            } else if ($allRecord->type == 3) {
                                $noPacks = explode(",", $allRecord->Packs);
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
                            } else if ($allRecord->type == 4) {
                                $noPacks = explode(",", $allRecord->Packs);
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                            } else if ($allRecord->type == 5) {
                                $noPacks = explode(",", $allRecord->Packs);
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                            } else if ($allRecord->type == 6) {
                                $noPacks = explode(",", $allRecord->Packs);
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
                            } else if ($allRecord->type == 7) {
                                $noPacks = explode(",", $allRecord->Packs);
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                            } else if ($allRecord->type == 8) {
                                $noPacks = explode(",", $allRecord->Packs);
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                            }
                        }
                    }
                    $newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
                    if ($newimplodeSalesNoPacks !== $outerinward->SalesNoPacks || $newimplodeSalesNoPacks < 0) {
                        Inward::where('Id', $outerinward->Id)->update(['SalesNoPacks' => $newimplodeSalesNoPacks]);
                    }
                } else {
                    if ($articleRecord[0]->ArticleOpenFlag == 0) {
                        $SalesNoPacks = 0;
                        foreach ($allRecords as $allRecord) {
                            if ($allRecord->type == 1) {
                                $SalesNoPacks = $SalesNoPacks + (int)$allRecord->Packs;
                            } else if ($allRecord->type == 2) {
                                $SalesNoPacks = $SalesNoPacks - (int)$allRecord->Packs;
                            } else if ($allRecord->type == 3) {
                                $SalesNoPacks = $SalesNoPacks + (int)$allRecord->Packs;
                            } else if ($allRecord->type == 4) {
                                $SalesNoPacks = $SalesNoPacks - (int)$allRecord->Packs;
                            } else if ($allRecord->type == 5) {
                                $SalesNoPacks = $SalesNoPacks - (int)$allRecord->Packs;
                            } else if ($allRecord->type == 6) {
                                $SalesNoPacks = $SalesNoPacks + (int)$allRecord->Packs;
                            } else if ($allRecord->type == 7) {
                                $SalesNoPacks = $SalesNoPacks - (int)$allRecord->Packs;
                            } else if ($allRecord->type == 8) {
                                $SalesNoPacks = $SalesNoPacks - (int)$allRecord->Packs;
                            }
                        }
                        if ((int)$SalesNoPacks !== (int)$outerinward->SalesNoPacks || $SalesNoPacks < 0) {
                            Inward::where('Id', $outerinward->Id)->update(['SalesNoPacks' => $SalesNoPacks]);
                        }
                    }
                }
            }
        }
        return response()->json(['status' => "success"], 200);
    }

    public function fixOutwardSalesremainingByOnce()
    {
        $allSOrecords =  DB::select('select Id , Status , DATE_FORMAT(created_at, "%d/%m/%Y") as created_at , NoPacks , OutwardNoPacks ,ArticleId ,SoNumberId from so');
        foreach ($allSOrecords as $allSOrecord) {
            $outwardNumbers =  DB::select("select Id from outwardnumber where SoId=" . $allSOrecord->SoNumberId);
            if (count($outwardNumbers) != 0) {
                $OutwardRemainingPacksNew = explode(",",  $allSOrecord->NoPacks);
                $OutwardNoPacksNew = $allSOrecord->NoPacks;
                foreach ($outwardNumbers as $outwardNumber) {
                    $allOutwards =  DB::select("select NoPacks from outward where OutwardNumberId=" . $outwardNumber->Id . " and ArticleId=" . $allSOrecord->ArticleId);
                    // if($allSOrecord->SoNumberId == 3871){
                    //     return $allOutwards;
                    // }
                    if (count($allOutwards) != 0) {
                        if (strpos($allSOrecord->NoPacks, ",") != false) {
                            foreach ($allOutwards as $allOutward) {
                                $outwardnoPacks =  explode(",", $allOutward->NoPacks);
                                for ($i = 0; $i < count(explode(",",  $allSOrecord->NoPacks)); $i++) {
                                    $OutwardRemainingPacksNew[$i] = $OutwardRemainingPacksNew[$i] - (int)$outwardnoPacks[$i];
                                }
                            }
                        } else {
                            foreach ($allOutwards as $allOutward) {
                                $OutwardNoPacksNew = $OutwardNoPacksNew - $allOutward->NoPacks;
                            }
                        }
                    }
                }
                if (strpos($allSOrecord->NoPacks, ",") != false) {
                    $OutwardNoPacksNewImplode = implode(",", $OutwardRemainingPacksNew);
                    if ($OutwardNoPacksNewImplode != $allSOrecord->OutwardNoPacks) {
                        SO::where('Id', $allSOrecord->Id)->update(['OutwardNoPacks' => $OutwardNoPacksNewImplode]);
                    }
                } else {
                    if ((int)$OutwardNoPacksNew != (int)$allSOrecord->OutwardNoPacks) {
                        SO::where('Id', $allSOrecord->Id)->update(['OutwardNoPacks' => $OutwardNoPacksNew]);
                    }
                }
            }
            else{
                if($allSOrecord->NoPacks != $allSOrecord->OutwardNoPacks){
                    SO::where('Id', $allSOrecord->Id)->update(['OutwardNoPacks' =>$allSOrecord->NoPacks]);
                }
            }
        }
        return response()->json(['status' => "success"], 200);

        // $allSOrecords  = SO::get();
        // foreach ($allSOrecords as $allSOrecord) {
        //     $outwardNumbers = OutwardNumber::where('SoId', $allSOrecord->SoNumberId)->get();
        //     if (count($outwardNumbers) != 0) {
        //         $OutwardRemainingPacksNew = explode(",",  $allSOrecord->NoPacks);
        //         $OutwardNoPacksNew = $allSOrecord->NoPacks;
        //         foreach ($outwardNumbers as $outwardNumber) {
        //             $allOutwards = Outward::where(['OutwardNumberId' => $outwardNumber->Id, 'ArticleId' => $allSOrecord->ArticleId])->get();
        //             if (count($allOutwards) != 0) {
        //                 if (strpos($allSOrecord->NoPacks, ",") != false) {
        //                     foreach ($allOutwards as $allOutward) {
        //                         $outwardnoPacks =  explode(",", $allOutward->NoPacks);
        //                         for ($i = 0; $i < count(explode(",",  $allSOrecord->NoPacks)); $i++) {
        //                             $OutwardRemainingPacksNew[$i] = $OutwardRemainingPacksNew[$i] - (int)$outwardnoPacks[$i];
        //                         }
        //                     }
        //                 } else {
        //                     foreach ($allOutwards as $allOutward) {
        //                         $OutwardNoPacksNew = $OutwardNoPacksNew - $allOutward->NoPacks;
        //                     }
        //                 }
        //             }
        //         }
        //         if (strpos($allSOrecord->NoPacks, ",") != false) {
        //             $OutwardNoPacksNewImplode = implode(",", $OutwardRemainingPacksNew);
        //             if ($OutwardNoPacksNewImplode != $allSOrecord->OutwardNoPacks) {
        //                 SO::where('Id', $allSOrecord->Id)->update(['OutwardNoPacks' => $OutwardNoPacksNewImplode]);
        //                 // array_push($AllArticlesSoOutwardCheck, json_encode(["SOId" => $allSOrecord->Id, 'oRemPacks' => $OutwardNoPacksNewImplode]));
        //             }
        //         } else {
        //             if ($OutwardNoPacksNew != $allSOrecord->OutwardNoPacks) {
        //                 SO::where('Id', $allSOrecord->Id)->update(['OutwardNoPacks' => $OutwardNoPacksNew]);
        //                 // array_push($AllArticlesSoOutwardCheck, json_encode(["SOId" => $allSOrecord->Id, 'oRemPacks' => $OutwardNoPacksNew]));
        //             }
        //         }
        //     }
        // }
        // return response()->json(['status' => "success"], 200);
    }

    public function stocktransferDuplication()
    {
        $duplicateStockTranfers =  DB::select("SELECT a.* , artcon.ArticleNumber as ConsumeArticleNumber , arttra.ArticleNumber as TransferArticleNumber , DATE_FORMAT(a.created_at , '%d-%m-%Y') as CreatedDate,concat(stnum.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as StocktransferNumber FROM stocktransfer a JOIN (SELECT StocktransferNumberId , ConsumedArticleId , TransferArticleId , COUNT(*) FROM stocktransfer GROUP BY StocktransferNumberId, ConsumedArticleId , TransferArticleId HAVING count(*) > 1) b ON a.StocktransferNumberId = b.StocktransferNumberId AND a.ConsumedArticleId = b.ConsumedArticleId AND a.TransferArticleId = b.TransferArticleId inner join stocktransfernumber stnum on a.StocktransferNumberId=stnum.Id inner join article artcon on artcon.Id=a.ConsumedArticleId inner join article arttra on arttra.Id=a.TransferArticleId inner join financialyear fn on fn.Id=stnum.FinancialYearId  ORDER BY a.StocktransferNumberId");
        if (count($duplicateStockTranfers)) {
            $arrayCreateDuplicate = [];
            foreach ($duplicateStockTranfers as $key => $duplicateStockTranfer) {
                if (array_key_exists($duplicateStockTranfer->StocktransferNumberId, $arrayCreateDuplicate)) {
                    if ($arrayCreateDuplicate[$duplicateStockTranfer->StocktransferNumberId] != $duplicateStockTranfer->ConsumedArticleId . ',' . $duplicateStockTranfer->TransferArticleId) {
                        $arrayCreateDuplicate[$duplicateStockTranfer->StocktransferNumberId] = $duplicateStockTranfer->ConsumedArticleId;
                    } else {
                        unset($duplicateStockTranfers[$key]);
                    }
                } else {
                    $arrayCreateDuplicate[$duplicateStockTranfer->StocktransferNumberId] = $duplicateStockTranfer->ConsumedArticleId . ',' . $duplicateStockTranfer->TransferArticleId;
                }
            }
            return response()->json(['status' => "success", "stockTransferDuplications" => array_values($duplicateStockTranfers)], 200);
        } else {
            return response()->json(['status' => "failed"], 200);
        }
    }
    public function deleteStockTraDuplication($stocktransfernumberid, $consumedarticleid, $transferarticleid)
    {
        $stocktrarecord = Stocktransfer::where('StocktransferNumberId', $stocktransfernumberid)->where('ConsumedArticleId', $consumedarticleid)->where('TransferArticleId', $transferarticleid)->first();
        $stockTraRecords = Stocktransfer::where('Id', '!=',  $stocktrarecord->Id)->where('StocktransferNumberId', $stocktransfernumberid)->where('ConsumedArticleId', $consumedarticleid)->where('TransferArticleId', $transferarticleid)->get();
        foreach ($stockTraRecords as $stockTraRecord) {
            Stocktransfer::where('Id', $stockTraRecord->Id)->delete();
        }
        return response()->json(['status' => "success"], 200);
    }
    public function deletePODuplication($ponumberid, $articleid)
    {
        $porecord = PO::where('PO_Number', $ponumberid)->where('ArticleId', $articleid)->first();
        $porecords = PO::where('Id', '!=',  $porecord->Id)->where('PO_Number', $ponumberid)->where('ArticleId', $articleid)->get();
        foreach ($porecords as $porecordinner) {
            PO::where('Id', $porecordinner->Id)->delete();
        }
        return response()->json(['status' => "success"], 200);
    }
    
    public function deleteInwardDuplication($GRNId, $articleid)
    {
        $inwardrecord = Inward::where('GRN', $GRNId)->where('ArticleId', $articleid)->first();
        $inwardrecords = Inward::where('Id', '!=',  $inwardrecord->Id)->where('GRN', $GRNId)->where('ArticleId', $articleid)->get();
        foreach ($inwardrecords as $inwardrecordinner) {
            Inward::where('Id', $inwardrecordinner->Id)->delete();
        }
        return response()->json(['status' => "success"], 200);
    }

    public function poDuplication()
    {
        $poDuplications = DB::select("SELECT a.Id as POId , a.ArticleId , a.PO_Number , article.ArticleNumber ,pn.Id as PONumberId , concat(pn.PurchaseNumber , '/',fn.StartYear,'-',fn.EndYear) as PONumber ,a.NumPacks as NoPacks ,DATE_FORMAT(a.created_at , '%d-%m-%Y') as CreatedDate FROM po a JOIN (SELECT PO_Number , ArticleId  , COUNT(*) FROM po GROUP BY PO_Number, ArticleId HAVING count(*) > 1 ) b ON a.PO_Number = b.PO_Number inner join article on article.Id=a.ArticleId inner join purchasenumber pn on pn.Id=a.PO_Number inner join financialyear fn on fn.Id=pn.FinancialYearId  AND a.ArticleId = b.ArticleId ORDER BY a.PO_Number;");
        
        if (count($poDuplications)) {
            $arrayCreateDuplicate = [];
            //Key will be outwardnumberid and value will be article
            foreach ($poDuplications as $key => $poDuplication) {
                if (array_key_exists($poDuplication->PONumberId, $arrayCreateDuplicate)) {
                    if ($arrayCreateDuplicate[$poDuplication->PONumberId] != $poDuplication->ArticleId) {
                        $arrayCreateDuplicate[$poDuplication->PONumberId] = $poDuplication->ArticleId;
                    } else {
                        unset($poDuplications[$key]);
                    }
                } else {
                    $arrayCreateDuplicate[$poDuplication->PONumberId] = $poDuplication->ArticleId;
                }
            }
            return response()->json(['status' => "success", "poDuplication" => array_values($poDuplications)], 200);
        } else {
            return response()->json(['status' => "failed"], 200);
        }
    }
    public function inwardDuplication()
    {
              $inwardDuplications = DB::select("SELECT a.ArticleOpenFlag, i.Id as InwardId, ig.Id as GRNId, i.ArticleId, i.GRN, a.ArticleNumber, concat(ig.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRNNumber, i.NoPacks, DATE_FORMAT(i.InwardDate, '%d-%m-%Y') as InwardDate FROM inward i JOIN (SELECT GRN, ArticleId, COUNT(*) FROM inward GROUP BY GRN, ArticleId HAVING count(*) > 1) b ON i.GRN=b.GRN inner join article a on a.Id=i.ArticleId inner join inwardgrn ig on ig.Id=i.GRN inner join financialyear fn on fn.Id=ig.FinancialYearId AND i.ArticleId=b.ArticleId  WHERE a.ArticleOpenFlag = 0 and NOT EXISTS
              (SELECT *
               FROM purchasereturn
               WHERE purchasereturn.ArticleId = i.ArticleId ORDER BY i.GRN)");
        if (count($inwardDuplications)) {
            $arrayCreateDuplicate = [];
            //Key will be outwardnumberid and value will be article
            foreach ($inwardDuplications as $key => $inwardDuplication) {
                if (array_key_exists($inwardDuplication->GRNId, $arrayCreateDuplicate)) {
                    if ($arrayCreateDuplicate[$inwardDuplication->GRNId] != $inwardDuplication->ArticleId) {
                        $arrayCreateDuplicate[$inwardDuplication->GRNId] = $inwardDuplication->ArticleId;
                    } else {
                        unset($inwardDuplications[$key]);
                    }
                } else {
                    $arrayCreateDuplicate[$inwardDuplication->GRNId] = $inwardDuplication->ArticleId;
                }
            }
            return response()->json(['status' => "success", "inwardDuplication" => array_values($inwardDuplications)], 200);
        } else {
            return response()->json(['status' => "failed"], 200);
        }
    }
}
