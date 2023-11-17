<?php

namespace App\Http\Controllers;

use App\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Stockshortage;
use App\Stocktransfer;
use App\SO;
use App\Inward;
use App\Outward;
use App\Salesreturn;
use App\Purchasereturns;
use App\UserLogs;
use App\Users;

class StocktransferController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //
    }

    public function GenerateSTNumber($UserId)
    {
        $array = array();
        $fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
        $stnumberdata = DB::select('SELECT Id, FinancialYearId, StocktransferNumber From stocktransfernumber order by Id desc limit 0,1');
        if (count($stnumberdata) > 0) {
            if ($fin_yr[0]->Id > $stnumberdata[0]->FinancialYearId) {
                $array["ST_Number"] = 1;
                $array["ST_Number_Financial_Id"] = $fin_yr[0]->Id;
                $array["ST_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
                return $array;
            } else {
                $array["ST_Number"] = ($stnumberdata[0]->StocktransferNumber) + 1;
                $array["ST_Number_Financial_Id"] = $fin_yr[0]->Id;
                $array["ST_Number_Financial"] = ($stnumberdata[0]->StocktransferNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
                return $array;
            }
        } else {
            $array["ST_Number"] = 1;
            $array["ST_Number_Financial_Id"] = $fin_yr[0]->Id;
            $array["ST_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
            return $array;
        }
    }

    public function AddStocktransfer(Request $request)
    {   
    
        $data = $request->all();
        $ConsumeArt = Article::where('Id', $data['ArticleId'])->first();
        
        $ProductionArt = Article::where('Id', $data['ProductionArticleId'])->first();


        $userRec = Users::where('Id', $data['UserId'])->first();
        if ($data['StocktransferNumberId'] == "Add") {
            $generate_STNumber = $this->GenerateSTNumber($data['UserId']);
            $ST_Number = $generate_STNumber['ST_Number'];
            $ST_Number_Financial_Id = $generate_STNumber['ST_Number_Financial_Id'];

            $stocktransfernumberId = DB::table('stocktransfernumber')->insertGetId(
                ['StocktransferNumber' =>  $ST_Number, 'Remarks' => $data['Remarks'],  "FinancialYearId" => $ST_Number_Financial_Id, 'StocktransferDate' =>  $data['StocktransferDate'], 'UserId' => $data['UserId'], 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
            );
            $outwardRec = DB::select("select concat($ST_Number,'/', fn.StartYear,'-',fn.EndYear) as STnumber from stocktransfernumber stn inner join financialyear fn on fn.Id=stn.FinancialYearId where stn.Id= '" . $stocktransfernumberId . "'");
            if ($data['TransferType'] == '1') {
                UserLogs::create([
                    'Module' => 'Stock Transfer',
                    'ModuleNumberId' => $stocktransfernumberId,
                    'LogType' => 'Created',
                    'LogDescription' => $userRec['Name'] . ' added consumed article ' . $ConsumeArt['ArticleNumber'] . '  and transfer article ' . $ProductionArt['ArticleNumber'] . '  with stock transfer number ' . " " . $outwardRec[0]->STnumber,
                    'UserId' => $userRec['Id'],
                    'updated_at' => null
                ]);
            }
        } else {
            $checksonumber = DB::select("SELECT StocktransferNumber FROM `stocktransfernumber` where Id ='" . $data['StocktransferNumberId'] . "'");
            if (!empty($checksonumber)) {
                $ST_Number = $checksonumber[0]->StocktransferNumber;
                $stocktransfernumberId = $data['StocktransferNumberId'];
                DB::table('stocktransfernumber')
                    ->where('Id', $stocktransfernumberId)
                    ->update(['StocktransferDate' =>  $data['StocktransferDate'], 'Remarks' => $data['Remarks'],  'UserId' => $data['UserId'], 'updated_at' => date('Y-m-d H:i:s')]);
                $outwardRec = DB::select("select concat($ST_Number,'/', fn.StartYear,'-',fn.EndYear) as STnumber from stocktransfernumber stn inner join financialyear fn on fn.Id=stn.FinancialYearId where stn.Id= '" . $stocktransfernumberId . "'");
                if ($data['TransferType'] == '1') {
                    UserLogs::create([
                        'Module' => 'Stock Transfer',
                        'ModuleNumberId' => $stocktransfernumberId,
                        'LogType' => 'Updated',
                        'LogDescription' => $userRec['Name'] . ' added consumed article ' . $ConsumeArt['ArticleNumber'] . '  and transfer article ' . $ProductionArt['ArticleNumber'] . '  with stock transfer number ' . " " . $outwardRec[0]->STnumber,
                        'UserId' => $userRec['Id'],
                        'updated_at' => null
                    ]);
                }
            }
        }
        if ($data['TransferType'] == "1") {
            $articleflag = DB::select("SELECT c.Colorflag, c.ArticleOpenFlag FROM `article` a inner join category c on c.Id=a.CategoryId where a.Id='" . $data['ArticleId'] . "'");
            if ($articleflag[0]->ArticleOpenFlag == 1) {
                $mixnopacks = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='" . $data['ArticleId'] . "'");
                $totalInward = 0;
                $totalOutwards = 0;
                $inwards  = Inward::where('ArticleId', $data['ArticleId'])->get();
                $outwards  = Outward::where('ArticleId', $data['ArticleId'])->get();
                $salesreturns = Salesreturn::where('ArticleId', $data['ArticleId'])->get();
                $purchasereturns = Purchasereturns::where('ArticleId', $data['ArticleId'])->get();
                $consumestocktransfers = Stocktransfer::where('ConsumedArticleId', $data['ArticleId'])->get();
                $transferstocktransfers = Stocktransfer::where('TransferArticleId', $data['ArticleId'])->get();
                $shortedStocks = Stockshortage::where('ArticleId', $data['ArticleId'])->get();
                $sorecords  = SO::where('ArticleId', $data['ArticleId'])->where('Status', 0)->get();
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
                if ($mixnopacks[0]->total > 0) {
                    if ($TotalRemaining >= $data['NoPacksNew']) {
                        $NoPacks = $data['NoPacksNew'];
                        $Consumption_Source = "";
                        $totalnopacks = ($mixnopacks[0]->NoPacks - $NoPacks);
                        DB::table('mixnopacks')
                            ->where('Id', $mixnopacks[0]->Id)
                            ->update(['NoPacks' => $totalnopacks, 'UpdatedDate' => date("Y-m-d H:i:s")]);
                    } else {
                        return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                    }
                }
                
            } else {
                $dataresult = DB::select('SELECT c.Colorflag, i.SalesNoPacks FROM `article` a inner join inward i on i.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where a.Id="' . $data['ArticleId'] . '"');
                $Colorflag = $dataresult[0]->Colorflag;
                $search = $dataresult[0]->SalesNoPacks;
                $searchString = ',';
                if (strpos($search, $searchString) !== false) {
                    $string = explode(',', $search);
                    $stringcomma = 1;
                } else {
                    $search;
                    $stringcomma = 0;
                }
                $NoPacks = "";
                $Consumption_Source = "";
                if ($Colorflag == 1) {
                    foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                        $numberofpacks = $vl["Id"];

                         //yashvi factory art
                        $newnopacks = $data["NoPacksNew_" . $numberofpacks];

                            $currentSalesNoPacks = DB::table('artstockstatus')
                            ->where(['outletId' =>  0])
                            ->where(['ArticleId' => $data["ArticleId"]])
                            ->value('SalesNoPacks');
                          // Convert comma-separated values to arrays
                            $currentSalesNoPacksArray = explode(',', $currentSalesNoPacks);
                            $dataNoPacksNewArray = explode(',', $newnopacks);

                            
                                 $artD = DB::table('article')
                                ->join('category', 'article.CategoryId', '=', 'category.Id')
                                ->where('article.Id', $data["ArticleId"])
                                ->first();

                                // Perform element-wise addition
                            $newSalesNoPacksArray = [];

                            for ($i = 0; $i < count($dataNoPacksNewArray); $i++) {
                                $newSalesNoPacksArray[$i] = (int)$currentSalesNoPacksArray[$i] - (int)$dataNoPacksNewArray[$i];
                            }
                             // Convert back to comma-separated string
                             $newSalesNoPacks = implode(',', $newSalesNoPacksArray);

                                // Perform the updateOrInsert operation with the new SalesNoPacks value
                
                                    $packes = $newSalesNoPacks;
                                    $packesArray = explode(',', $packes);
                                    $sum = array_sum($packesArray);

                                
                                // Perform the updateOrInsert operation with the new SalesNoPacks value
                                DB::table('artstockstatus')->updateOrInsert(
                                    [
                                        'outletId' => 0,
                                        'ArticleId' => $data['ArticleId']
                                    ],
                                    [
                                        'Title' => $artD->Title,
                                        'ArticleNumber' => $artD->ArticleNumber,
                                        'SalesNoPacks' => $packesArray,
                                        'TotalPieces' => $sum
                                    ]
                                );
                                
                            //close

                        if ($data["NoPacksNew_" . $numberofpacks] != "") {
                            if ($stringcomma == 1) {
                                if ($string[$key] < $data["NoPacksNew_" . $numberofpacks]) {
                                    return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                                }
                                $Consumption_Source .= ($string[$key] - $data["NoPacksNew_" . $numberofpacks]) . ",";
                            } else {
                                if ($search < $data["NoPacksNew_" . $numberofpacks]) {
                                    return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                                }
                                $Consumption_Source .= ($search - $data["NoPacksNew_" . $numberofpacks]) . ",";
                            }
                            $NoPacks .= $data["NoPacksNew_" . $numberofpacks] . ",";
                        } else {
                            $NoPacks .= "0,";
                            $Consumption_Source .= $string[$key] . ",";
                        }
                    }
                } else {
                        //yashvi factory art

                             $currentSalesNoPacks = DB::table('artstockstatus')
                             ->where(['outletId' =>  0])
                             ->where(['ArticleId' => $data["ArticleId"]])
                             ->value('SalesNoPacks');
                                             
                             $artD = DB::table('article')
                            ->join('category', 'article.CategoryId', '=', 'category.Id')
                            ->where('article.Id', $data["ArticleId"])
                            ->first();
                            // Calculate the new SalesNoPacks value by adding the new value to the current value                        
                            $newSalesNoPacks = $currentSalesNoPacks - $data['NoPacksNew'];
                                                 
                           // Perform the updateOrInsert operation with the new SalesNoPacks value
                           DB::table('artstockstatus')->updateOrInsert(
                           [
                            'outletId' => 0,
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
                 
                    if (isset($data['NoPacksNew'])) {
                        $NoPacks = $data['NoPacksNew'];
                        if ($search < $data['NoPacksNew']) {
                            return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                        }
                        $Consumption_Source = ($search - $data['NoPacksNew']);
                    } else {
                        return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
                    }
                }

                $CheckSalesNoPacks = explode(',', $NoPacks);
                $Consumption_Source = rtrim($Consumption_Source, ',');
                $tmp = array_filter($CheckSalesNoPacks);
                if (empty($tmp)) {
                    return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
                }
                DB::table('inward')
                    ->where('ArticleId', $data['ArticleId'])
                    ->update(['SalesNoPacks' => $Consumption_Source, 'updated_at' => date('Y-m-d H:i:s')]);
            }
            $articleflagpro = DB::select("SELECT c.Colorflag, c.ArticleOpenFlag FROM `article` a inner join category c on c.Id=a.CategoryId where a.Id='" . $data['ProductionArticleId'] . "'");
            if ($articleflagpro[0]->ArticleOpenFlag == 1) {
                $mixnopacksprod = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='" . $data['ProductionArticleId'] . "'");
                if ($mixnopacksprod[0]->total > 0) {
                    $ProductionNoPacks = $data['ProductionNoPacksNew'];
                    $Production_Destination = "";
                    $totalnopacksprod = ($mixnopacksprod[0]->NoPacks + $ProductionNoPacks);
                    DB::table('mixnopacks')
                        ->where('Id', $mixnopacksprod[0]->Id)
                        ->update(['NoPacks' => $totalnopacksprod, 'UpdatedDate' => date("Y-m-d H:i:s")]);
                }
            } else {
              
                $productiondataresult = DB::select('SELECT c.Colorflag, i.SalesNoPacks FROM `article` a inner join inward i on i.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where a.Id="' . $data['ProductionArticleId'] . '"');
                $productionColorflag = $productiondataresult[0]->Colorflag;
                $productionsearch = $productiondataresult[0]->SalesNoPacks;
                $searchStringpro = ',';
                if (strpos($productionsearch, $searchStringpro) !== false) {
                    $productionstring = explode(',', $productionsearch);
                    $productionstringcomma = 1;
                } else {
                    $productionsearch;
                    $productionstringcomma = 0;
                }
                $ProductionNoPacks = "";
                $Production_Destination = "";
                if ($productionColorflag == 1) {
                    foreach ($data['ProductionArticleSelectedColor'] as $key => $vl) {
                        $production_numberofpacks = $vl["Id"];

                        $newnopacks = $data["ProductionNoPacksNew_" . $production_numberofpacks];

                        $currentSalesNoPacks = DB::table('artstockstatus')
                        ->where(['outletId' =>  0])
                        ->where(['ArticleId' => $data["ProductionArticleId"]])
                        ->value('SalesNoPacks');

                        $artD = DB::table('article')
                        ->join('category', 'article.CategoryId', '=', 'category.Id')
                        ->where('article.Id', $data["ProductionArticleId"])
                        ->first();
                      
                      // Convert comma-separated values to arrays
                        $currentSalesNoPacksArray = explode(',', $currentSalesNoPacks);
                        $dataNoPacksNewArray = explode(',', $newnopacks);

                            // Perform element-wise addition
                        $newSalesNoPacksArray = [];

                        for ($i = 0; $i < count($dataNoPacksNewArray); $i++) {
                            $newSalesNoPacksArray[$i] = (int)$currentSalesNoPacksArray[$i] + (int)$dataNoPacksNewArray[$i];
                        }
                         // Convert back to comma-separated string
                         $newSalesNoPacks = implode(',', $newSalesNoPacksArray);

                            // Perform the updateOrInsert operation with the new SalesNoPacks value
            
                                $packes = $newSalesNoPacks;
                                $packesArray = explode(',', $packes);
                                $sum = array_sum($packesArray);

                            
                            // Perform the updateOrInsert operation with the new SalesNoPacks value
                            DB::table('artstockstatus')->updateOrInsert(
                                [
                                    'outletId' => 0,
                                    'ArticleId' => $data['ProductionArticleId']
                                ],
                                [ 
                                    'Title' => $artD->Title,
                                    'ArticleNumber' => $artD->ArticleNumber,
                                    'SalesNoPacks' => $packesArray,
                                    'TotalPieces' => $sum
                                ]
                            );

                        if ($data["ProductionNoPacksNew_" . $production_numberofpacks] != "") {
                            if ($productionstringcomma == 1) {
                                $Production_Destination .= ($productionstring[$key] + $data["ProductionNoPacksNew_" . $production_numberofpacks]) . ",";
                            } else {
                                $Production_Destination .= ($productionsearch + $data["ProductionNoPacksNew_" . $production_numberofpacks]) . ",";
                            }
                            $ProductionNoPacks .= $data["ProductionNoPacksNew_" . $production_numberofpacks] . ",";
                        } else {
                            $ProductionNoPacks .= "0,";
                            $Production_Destination .= $productionstring[$key] . ",";
                        }
                    }
                } else {
                    $currentSalesNoPacks = DB::table('artstockstatus')
                    ->where(['outletId' =>  0])
                    ->where(['ArticleId' => $data["ProductionArticleId"]])
                    ->value('SalesNoPacks');

                    
                    $artD = DB::table('article')
                        ->join('category', 'article.CategoryId', '=', 'category.Id')
                        ->where('article.Id', $data["ProductionArticleId"])
                        ->first();
                        // Calculate the new SalesNoPacks value by adding the new value to the current value                        
                        $newSalesNoPacks = $currentSalesNoPacks +  $data['ProductionNoPacksNew'];
                        
                        // Perform the updateOrInsert operation with the new SalesNoPacks value
                        DB::table('artstockstatus')->updateOrInsert(
                            [
                                'outletId' => 0,
                                'ArticleId' => $data['ProductionArticleId']
                            ],
                            [ 
                                'Title' => $artD->Title,
                                'ArticleNumber' => $artD->ArticleNumber,
                                'SalesNoPacks' => $newSalesNoPacks,
                                'TotalPieces' => $newSalesNoPacks
                            ]
                        );
                    if (isset($data['ProductionNoPacksNew'])) {
                        $ProductionNoPacks = $data['ProductionNoPacksNew'];
                        $Production_Destination = ($productionsearch + $data['ProductionNoPacksNew']);
                    } else {
                        return response()->json(array("id" => "", "as" => "", "ProductionZeroNotAllow" => "true"), 200);
                    }
                }
                $CheckProductionSalesNoPacks = explode(',', $ProductionNoPacks);
                $Production_Destination = rtrim($Production_Destination, ',');
                $tmp = array_filter($CheckProductionSalesNoPacks);
                if (empty($tmp)) {
                    return response()->json(array("id" => "", "as111" => "", "ProductionZeroNotAllow" => "true"), 200);
                }
                DB::table('inward')
                    ->where('ArticleId', $data['ProductionArticleId'])
                    ->update(['SalesNoPacks' => $Production_Destination, 'updated_at' => date('Y-m-d H:i:s')]);
            }
            $stocktransferPre = Stocktransfer::where(['StocktransferNumberId' => $stocktransfernumberId, 'ConsumedArticleId' => $data['ArticleId'], 'TransferArticleId' => $data['ProductionArticleId']])->first();
            if ($stocktransferPre) {
                if (strpos($stocktransferPre->ConsumedNoPacks, ',') != false) {
                    $preStocktransfer = explode(',', $stocktransferPre->ConsumedNoPacks);
                    $arrayConsumedNoPacks = explode(',', rtrim($NoPacks, ','));
                    $newConsumedNoPacks = $preStocktransfer;
                    for ($i = 0; $i < count($arrayConsumedNoPacks); $i++) {
                        $newConsumedNoPacks[$i] =  $newConsumedNoPacks[$i] + $arrayConsumedNoPacks[$i];
                    }
                    $finalConsumedNoPacks = implode(",", $newConsumedNoPacks);
                } else {
                    $finalConsumedNoPacks = (int)$stocktransferPre->ConsumedNoPacks + (int)rtrim($NoPacks, ',');
                }
                if (strpos($stocktransferPre->TransferNoPacks, ',') != false) {
                    $preStocktransferGain = explode(',', $stocktransferPre->TransferNoPacks);
                    $arrayTransferNoPacks = explode(',', rtrim($ProductionNoPacks, ','));
                    $newTransferNoPacks = $preStocktransferGain;
                    for ($i = 0; $i < count($arrayTransferNoPacks); $i++) {
                        $newTransferNoPacks[$i] =  $newTransferNoPacks[$i] + $arrayTransferNoPacks[$i];
                    }
                    $finalTransferNoPacks = implode(",", $newTransferNoPacks);
                } else {
                    $finalTransferNoPacks = (int)$stocktransferPre->TransferNoPacks + (int)rtrim($ProductionNoPacks, ',');
                }
                Stocktransfer::where(['StocktransferNumberId' => $stocktransfernumberId, 'ConsumedArticleId' => $data['ArticleId'], 'TransferArticleId' => $data['ProductionArticleId']])
                    ->update(['ConsumedNoPacks' => $finalConsumedNoPacks, 'TotalConsumedNoPacks' => $Consumption_Source, 'TransferNoPacks' => $finalTransferNoPacks, 'TotalTransferNoPacks' => $Production_Destination]);
            } else {
                $stocktransferadd = array();
                $stocktransferadd['StocktransferNumberId'] = $stocktransfernumberId;
                $stocktransferadd["ConsumedArticleId"] = $data['ArticleId'];
                $stocktransferadd["ConsumedNoPacks"] = rtrim($NoPacks, ',');
                $stocktransferadd["TotalConsumedNoPacks"] = $Consumption_Source;
                $stocktransferadd["TransferArticleId"] = $data['ProductionArticleId'];
                $stocktransferadd['TransferNoPacks'] = rtrim($ProductionNoPacks, ',');
                $stocktransferadd['TotalTransferNoPacks'] = $Production_Destination;
                Stocktransfer::create($stocktransferadd);
            }
            return response()->json(array("StocktransferNumberId" => $stocktransfernumberId, "ST_Number" => $ST_Number), 200);
        } else {
            $articleflag = DB::select("SELECT c.Colorflag, c.ArticleOpenFlag FROM `article` a inner join category c on c.Id=a.CategoryId where a.Id='" . $data['ArticleId'] . "'");
            if ($articleflag[0]->ArticleOpenFlag == 1) {
                $mixnopacks = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='" . $data['ArticleId'] . "'");
                if ($mixnopacks[0]->total > 0) {
                    $NoPacks = $data['NoPacksNew'];
                    $Consumption_Source = "";
                    $totalnopacks = ($mixnopacks[0]->NoPacks - $NoPacks);
                    DB::table('mixnopacks')
                        ->where('Id', $mixnopacks[0]->Id)
                        ->update(['NoPacks' => $totalnopacks, 'UpdatedDate' => date("Y-m-d H:i:s")]);
                }
            } else {
                $dataresult = DB::select('SELECT c.Colorflag, i.SalesNoPacks FROM `article` a inner join inward i on i.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where a.Id="' . $data['ArticleId'] . '"');
                $Colorflag = $dataresult[0]->Colorflag;
                $search = $dataresult[0]->SalesNoPacks;
                $stocktransferadd = array();
                $searchString = ',';
                if (strpos($search, $searchString) !== false) {
                    $string = explode(',', $search);
                    $stringcomma = 1;
                } else {
                    $search;
                    $stringcomma = 0;
                }
                $NoPacks = "";
                $Consumption_Source = "";
                if ($Colorflag == 1) {
                    foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                        $numberofpacks = $vl["Id"];
                        if ($data["NoPacksNew_" . $numberofpacks] != "") {
                            if ($stringcomma == 1) {
                                if ($string[$key] < $data["NoPacksNew_" . $numberofpacks]) {
                                    return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                                }
                                $Consumption_Source .= ($string[$key] - $data["NoPacksNew_" . $numberofpacks]) . ",";
                            } else {
                                if ($search < $data["NoPacksNew_" . $numberofpacks]) {
                                    return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                                }
                                $Consumption_Source .= ($search - $data["NoPacksNew_" . $numberofpacks]) . ",";
                            }
                            $NoPacks .= $data["NoPacksNew_" . $numberofpacks] . ",";
                        } else {
                            $NoPacks .= "0,";
                            $Consumption_Source .= $string[$key] . ",";
                        }
                    }
                } else {
                    if (isset($data['NoPacksNew'])) {
                        $NoPacks = $data['NoPacksNew'];
                        if ($search < $data['NoPacksNew']) {
                            return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                        }
                        $Consumption_Source = ($search - $data['NoPacksNew']);
                    } else {
                        return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
                    }
                }
                $CheckSalesNoPacks = explode(',', $NoPacks);
                $Consumption_Source = rtrim($Consumption_Source, ',');
                $tmp = array_filter($CheckSalesNoPacks);
                if (empty($tmp)) {
                    return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
                }
                DB::table('inward')
                    ->where('ArticleId', $data['ArticleId'])
                    ->update(['SalesNoPacks' => $Consumption_Source, 'updated_at' => date('Y-m-d H:i:s')]);
            }
            $stockrecordpre = Stockshortage::where(['StocktransferNumberId' => $stocktransfernumberId, 'ArticleId' => $data['ArticleId']])->first();
            if ($stockrecordpre) {
                if (strpos($stockrecordpre->NoPacks, ',' != false)) {
                    $preStockShortage = explode(',', $stockrecordpre->NoPacks);
                    $gotStockShortage = explode(',', rtrim($NoPacks, ','));
                    $newStockShortage = $preStockShortage;
                    for ($i = 0; $i < count($preStockShortage); $i++) {
                        $newStockShortage[$i] = $newStockShortage[$i] + $gotStockShortage[$i];
                    }
                    $finalStockShortage = implode(',', $newStockShortage);
                    Stockshortage::where(['StocktransferNumberId' => $stocktransfernumberId, 'ArticleId' => $data['ArticleId']])->update(['NoPacks' => $finalStockShortage, 'TotalNoPacks' => $Consumption_Source]);
                } else {
                    Stockshortage::where(['StocktransferNumberId' => $stocktransfernumberId, 'ArticleId' => $data['ArticleId']])->update(['NoPacks' => (int)$stockrecordpre->NoPacks + (int)rtrim($NoPacks), 'TotalNoPacks' => $Consumption_Source]);
                }
            } else {
                $stocktransferadd['StocktransferNumberId'] = $stocktransfernumberId;
                $stocktransferadd["ArticleId"] = $data['ArticleId'];
                $stocktransferadd["NoPacks"] = rtrim($NoPacks, ',');
                $stocktransferadd["TotalNoPacks"] = $Consumption_Source;
                Stockshortage::create($stocktransferadd);
            }
            return response()->json(array("StocktransferNumberId" => $stocktransfernumberId, "ST_Number" => $ST_Number), 200);
        }
    }



    // public function StockshortageListFromSTNO($STNO, Request $request)
    // {
        
        
    //      $data = $request->all();
    //     $search = $data["search"];
    //     $startnumber = $data["start"];
    //     $vnddataTotal = DB::select("select count(*) as Total from (SELECT s.Id , s.ArticleId, s.NoPacks, stn.StocktransferNumber , stn.StocktransferDate, a.ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear FROM `stockshortage` s inner join stocktransfernumber stn on stn.Id=s.StocktransferNumberId inner join article a on a.Id=s.ArticleId inner join financialyear fn on fn.Id=stn.FinancialYearId where s.StocktransferNumberId =  '" . $STNO . "') as d");
    //     $vnTotal = $vnddataTotal[0]->Total;
    //     $length = $data["length"];
    //     if ($search['value'] != null && strlen($search['value']) > 2) {
    //         $searchstring = "where d.ConsumedArticle like '%" . $search['value'] . "%' OR d.TransferArticle like '%" . $search['value'] . "%' OR d.TransferCategory like '%" . $search['value'] . "%' OR d.ConsumeCategory like '%" . $search['value'] . "%' ";
    //         $vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT s.Id , s.ArticleId, s.NoPacks, stn.StocktransferNumber , stn.StocktransferDate, a.ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear FROM `stockshortage` s inner join stocktransfernumber stn on stn.Id=s.StocktransferNumberId inner join article a on a.Id=s.ArticleId inner join financialyear fn on fn.Id=stn.FinancialYearId where s.StocktransferNumberId =  '" . $STNO . "') as d "  . $searchstring );
    //         $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
    //     } else {
    //         $searchstring = "";
    //         $vnddataTotalFilterValue = $vnTotal;
    //     }
    //     $column = $data["order"][0]["column"];
    //     switch ($column) {
    //         case 1:
    //             $ordercolumn = "d.ConsumedArticle";
    //             break;
    //         case 2:
    //             $ordercolumn = "d.ConsumeCategory";
    //             break;
    //         case 3:
    //             $ordercolumn = "d.ConsumedNoPacks";
    //             break;
    //         case 4:
    //             $ordercolumn = "d.TransferArticle";
    //         break;
            
    //         case 5:
    //             $ordercolumn = "d.TransferCategory";
    //         break;
            
    //          case 6:
    //             $ordercolumn = "d.PartyDiscount";
    //         break;
            
    //         default:
    //             $ordercolumn = "d.OutwardDate";
    //             break;
    //     }
    //     $order = "";
    //     if ($data["order"][0]["dir"]) {
    //         $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
    //     }
    //     $vnddata = DB::select("select d.* from (SELECT s.Id , s.ArticleId, s.NoPacks, stn.StocktransferNumber , stn.StocktransferDate, a.ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear FROM `stockshortage` s inner join stocktransfernumber stn on stn.Id=s.StocktransferNumberId inner join article a on a.Id=s.ArticleId inner join financialyear fn on fn.Id=stn.FinancialYearId where s.StocktransferNumberId =  '" . $STNO . "') as d " );
    
    

    //     return array(
    //         'datadraw' => $data["draw"],
    //         'recordsTotal' => $vnTotal,
    //         'recordsFiltered' => $vnddataTotalFilterValue,
    //         'response' => 'success',
    //         'startnumber' => $startnumber,
    //         'search' => count($vnddata),
    //         'data' => $vnddata,
    //     );
        
        
        
        
    //     // return DB::select("SELECT s.Id , s.ArticleId, s.NoPacks, stn.StocktransferNumber , stn.StocktransferDate, a.ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear FROM `stockshortage` s inner join stocktransfernumber stn on stn.Id=s.StocktransferNumberId inner join article a on a.Id=s.ArticleId inner join financialyear fn on fn.Id=stn.FinancialYearId where s.StocktransferNumberId =  '" . $STNO . "'");
    // }


    public function StockshortageListFromSTNO($STNO)
    {
        
        return DB::select("SELECT s.Id , s.ArticleId, s.NoPacks, stn.StocktransferNumber , stn.StocktransferDate, a.ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear FROM `stockshortage` s inner join stocktransfernumber stn on stn.Id=s.StocktransferNumberId inner join article a on a.Id=s.ArticleId inner join financialyear fn on fn.Id=stn.FinancialYearId where s.StocktransferNumberId =  '" . $STNO . "'");
    }








    public function StocktransferListFromSTNO($STNO, Request $request)
    {
        
        
        $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select("select count(*) as Total from (SELECT s.Id , cc.Title as ConsumeCategory, tc.Title as TransferCategory,  s.ConsumedArticleId, s.ConsumedNoPacks, s.TransferArticleId , s.TransferNoPacks, stn.StocktransferNumber , stn.StocktransferDate, a.ArticleNumber as ConsumedArticle, aa.ArticleNumber as TransferArticle, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear FROM `stocktransfer` s inner join stocktransfernumber stn on stn.Id=s.StocktransferNumberId inner join article a on a.Id=s.ConsumedArticleId left join category cc on cc.Id=a.CategoryId  inner join article aa on aa.Id=s.TransferArticleId  left join category tc on tc.Id=aa.CategoryId inner join financialyear fn on fn.Id=stn.FinancialYearId where s.StocktransferNumberId = '" . $STNO . "') as d");
        $vnTotal = $vnddataTotal[0]->Total;
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "where d.ConsumedArticle like '%" . $search['value'] . "%' OR d.TransferArticle like '%" . $search['value'] . "%' OR d.TransferCategory like '%" . $search['value'] . "%' OR d.ConsumeCategory like '%" . $search['value'] . "%' ";
            $vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT s.Id , cc.Title as ConsumeCategory, tc.Title as TransferCategory,  s.ConsumedArticleId, s.ConsumedNoPacks, s.TransferArticleId , s.TransferNoPacks, stn.StocktransferNumber , stn.StocktransferDate, a.ArticleNumber as ConsumedArticle, aa.ArticleNumber as TransferArticle, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear FROM `stocktransfer` s inner join stocktransfernumber stn on stn.Id=s.StocktransferNumberId inner join article a on a.Id=s.ConsumedArticleId left join category cc on cc.Id=a.CategoryId  inner join article aa on aa.Id=s.TransferArticleId  left join category tc on tc.Id=aa.CategoryId inner join financialyear fn on fn.Id=stn.FinancialYearId where s.StocktransferNumberId = '" . $STNO . "') as d "  . $searchstring );
            $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
        } else {
            $searchstring = "";
            $vnddataTotalFilterValue = $vnTotal;
        }
        $column = $data["order"][0]["column"];
        switch ($column) {
            case 1:
                $ordercolumn = "d.ConsumedArticle";
                break;
            case 2:
                $ordercolumn = "d.ConsumeCategory";
                break;
            case 3:
                $ordercolumn = "d.ConsumedNoPacks";
                break;
            case 4:
                $ordercolumn = "d.TransferArticle";
            break;
            
            case 5:
                $ordercolumn = "d.TransferCategory";
            break;
            
             case 6:
                $ordercolumn = "d.PartyDiscount";
            break;
            
            default:
                $ordercolumn = "d.OutwardDate";
                break;
        }
        $order = "";
        if ($data["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
        }
        $vnddata = DB::select("select d.* from (SELECT s.Id , cc.Title as ConsumeCategory, tc.Title as TransferCategory,  s.ConsumedArticleId, s.ConsumedNoPacks, s.TransferArticleId , s.TransferNoPacks, stn.StocktransferNumber , stn.StocktransferDate, a.ArticleNumber as ConsumedArticle, aa.ArticleNumber as TransferArticle, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear FROM `stocktransfer` s inner join stocktransfernumber stn on stn.Id=s.StocktransferNumberId inner join article a on a.Id=s.ConsumedArticleId left join category cc on cc.Id=a.CategoryId  inner join article aa on aa.Id=s.TransferArticleId  left join category tc on tc.Id=aa.CategoryId inner join financialyear fn on fn.Id=stn.FinancialYearId where s.StocktransferNumberId = '" . $STNO . "') as d " . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length );
    
    

        return array(
            'datadraw' => $data["draw"],
            'recordsTotal' => $vnTotal,
            'recordsFiltered' => $vnddataTotalFilterValue,
            'response' => 'success',
            'startnumber' => $startnumber,
            'search' => count($vnddata),
            'data' => $vnddata,
        );
        
        
        
        // return ($STNO);
        // return DB::select("SELECT s.Id , cc.Title as ConsumeCategory, tc.Title as TransferCategory,  s.ConsumedArticleId, s.ConsumedNoPacks, s.TransferArticleId , s.TransferNoPacks, stn.StocktransferNumber , stn.StocktransferDate, a.ArticleNumber as ConsumedArticle, aa.ArticleNumber as TransferArticle, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear FROM `stocktransfer` s inner join stocktransfernumber stn on stn.Id=s.StocktransferNumberId inner join article a on a.Id=s.ConsumedArticleId left join category cc on cc.Id=a.CategoryId  inner join article aa on aa.Id=s.TransferArticleId  left join category tc on tc.Id=aa.CategoryId inner join financialyear fn on fn.Id=stn.FinancialYearId where s.StocktransferNumberId = '" . $STNO . "'");
    }
    // public function StocktransferListFromSTNO($STNO)
    // {
    //     // return ($STNO);
    //     return DB::select("SELECT s.Id , cc.Title as ConsumeCategory, tc.Title as TransferCategory,  s.ConsumedArticleId, s.ConsumedNoPacks, s.TransferArticleId , s.TransferNoPacks, stn.StocktransferNumber , stn.StocktransferDate, a.ArticleNumber as ConsumedArticle, aa.ArticleNumber as TransferArticle, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear FROM `stocktransfer` s inner join stocktransfernumber stn on stn.Id=s.StocktransferNumberId inner join article a on a.Id=s.ConsumedArticleId left join category cc on cc.Id=a.CategoryId  inner join article aa on aa.Id=s.TransferArticleId  left join category tc on tc.Id=aa.CategoryId inner join financialyear fn on fn.Id=stn.FinancialYearId where s.StocktransferNumberId = '" . $STNO . "'");
    // }




    // public function StocktransferDateFromSTNO($id, Request $request)
    // {
        
    //     // (SELECT stn.StocktransferNumber, stn.StocktransferDate, stn.Remarks, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear from stocktransfernumber stn inner join financialyear fn on fn.Id=stn.FinancialYearId  where stn.Id = '" . $id . "')
        
    //     $data = $request->all();
    //     $search = $data["search"];
    //     $startnumber = $data["start"];
    //     $vnddataTotal = DB::select("select count(*) as Total from( SELECT stn.StocktransferNumber, stn.StocktransferDate, stn.Remarks, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear from stocktransfernumber stn inner join financialyear fn on fn.Id=stn.FinancialYearId  where stn.Id = '" . $id . "') as d");
        
        
        
    //     $vnTotal = $vnddataTotal[0]->Total;
    //     $length = $data["length"];
    //     if ($search['value'] != null && strlen($search['value']) > 2) {
    //         $searchstring = "where d.ConsumedArticle like '%" . $search['value'] . "%' OR d.TransferArticle like '%" . $search['value'] . "%' OR d.TransferCategory like '%" . $search['value'] . "%' OR d.ConsumeCategory like '%" . $search['value'] . "%' ";
    //         $vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT stn.StocktransferNumber, stn.StocktransferDate, stn.Remarks, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear from stocktransfernumber stn inner join financialyear fn on fn.Id=stn.FinancialYearId  where stn.Id = '" . $id . "') as d "  . $searchstring );
    //         $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
    //     } else {
    //         $searchstring = "";
    //         $vnddataTotalFilterValue = $vnTotal;
    //     }
    //     $column = $data["order"][0]["column"];
    //     switch ($column) {
    //         case 1:
    //             $ordercolumn = "d.StocktransferNumber";
    //             break;
    //         case 2:
    //             $ordercolumn = "d.StocktransferDate";
    //             break;
    //         case 3:
    //             $ordercolumn = "d.Remarks";
    //             break;
    //         case 4:
    //             $ordercolumn = "d.ST_Number_FinancialYear";
    //         break;
           
            
    //         default:
    //             $ordercolumn = "d.StocktransferDate";
    //             break;
    //     }
    //     $order = "";
    //     if ($data["order"][0]["dir"]) {
    //         $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
    //     }
    //     $vnddata = DB::select("select d.* from (SELECT stn.StocktransferNumber, stn.StocktransferDate, stn.Remarks, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear from stocktransfernumber stn inner join financialyear fn on fn.Id=stn.FinancialYearId  where stn.Id = '" . $id . "') as d " . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length );
    
    

    //     return array(
    //         'datadraw' => $data["draw"],
    //         'recordsTotal' => $vnTotal,
    //         'recordsFiltered' => $vnddataTotalFilterValue,
    //         'response' => 'success',
    //         'startnumber' => $startnumber,
    //         'search' => count($vnddata),
    //         'data' => $vnddata,
    //     );
    
        
    //     return DB::select("SELECT stn.StocktransferNumber, stn.StocktransferDate, stn.Remarks, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear from stocktransfernumber stn inner join financialyear fn on fn.Id=stn.FinancialYearId  where stn.Id = '" . $id . "'");
    // }
    
        public function StocktransferDateFromSTNO($id)
    {
        return DB::select("SELECT stn.StocktransferNumber, stn.StocktransferDate, stn.Remarks, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear from stocktransfernumber stn inner join financialyear fn on fn.Id=stn.FinancialYearId  where stn.Id = '" . $id . "'");
    }
    

    public function Deletestocktransfer($id, $type, $LoggedId)
    {
        if ($type == 2) {
            $dataresult = DB::select("SELECT c.Colorflag, c.ArticleOpenFlag, a.ArticleColor, i.SalesNoPacks, ss.Id, ss.ArticleId, ss.StocktransferNumberId, ss.NoPacks FROM `stockshortage` ss inner join article a on a.Id=ss.ArticleId inner join inward i on i.ArticleId=a.Id left join category c on c.Id=a.CategoryId where ss.Id='" . $id . "'");
            $Colorflag = $dataresult[0]->Colorflag;
            $ArticleColor = json_decode($dataresult[0]->ArticleColor);
            $string = $dataresult[0]->SalesNoPacks;
            $ShortageDataNoPacks = $dataresult[0]->NoPacks;
            $ArticleId = $dataresult[0]->ArticleId;
            if (strpos($string, ',') !== false) {
                $ShortageDataNoPacks = explode(',', $ShortageDataNoPacks);
                $string = explode(',', $string);
                $stringcomma = 1;
            } else {
                $stringcomma = 0;
            }
            $ShortageNoPacks = "";
            if ($Colorflag == 1) {
                foreach ($ArticleColor as $key => $vl) {
                    if ($stringcomma == 1) {
                        $ShortageNoPacks .= ($string[$key] + $ShortageDataNoPacks[$key]) . ",";
                    } else {
                        $ShortageNoPacks .= ($string + $ShortageDataNoPacks) . ",";
                    }
                }
            } else {
                $ShortageNoPacks .= ($string + $ShortageDataNoPacks) . ",";
            }
            $ShortageNoPacks = rtrim($ShortageNoPacks, ',');

            DB::beginTransaction();
            try {

                DB::table('stockshortage')
                    ->where('Id', '=', $id)
                    ->delete();
                DB::table('inward')
                    ->where('ArticleId', $ArticleId)
                    ->update(['SalesNoPacks' => $ShortageNoPacks]);
                DB::commit();
                return response()->json(array("id" => "SUCCESS"), 200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(array("Id" => ""), 200);
            }
        } else {
            $dataresult = DB::select("SELECT c.Colorflag as ConsumedColorflag, cc.Colorflag as TransferColorflag, c.ArticleOpenFlag as ConsumedArticleOpenFlag, cc.ArticleOpenFlag as TransferArticleOpenFlag, a.ArticleColor as ConsumedArticleColor, aa.ArticleColor as TransferArticleColor, ss.ConsumedArticleId, ss.ConsumedNoPacks, i.SalesNoPacks as ConsumedSalesNoPacks, ss.TransferArticleId, ss.TransferNoPacks, ii.SalesNoPacks as TransferSalesNoPacks, ss.Id, ss.StocktransferNumberId FROM `stocktransfer` ss inner join article a on a.Id=ss.ConsumedArticleId inner join article aa on aa.Id=ss.TransferArticleId inner join inward i on i.ArticleId=a.Id inner join inward ii on ii.ArticleId=aa.Id left join category c on c.Id=a.CategoryId left join category cc on cc.Id=aa.CategoryId where ss.Id='" . $id . "'");
            $Colorflag = $dataresult[0]->ConsumedColorflag;
            $ArticleColor = json_decode($dataresult[0]->ConsumedArticleColor);
            $string = $dataresult[0]->ConsumedSalesNoPacks;
            $ConsumedDataNoPacks = $dataresult[0]->ConsumedNoPacks;
            $ConsumedArticleId = $dataresult[0]->ConsumedArticleId;
            if (strpos($string, ',') !== false) {
                $ConsumedDataNoPacks = explode(',', $ConsumedDataNoPacks);
                $string = explode(',', $string);
                $stringcomma = 1;
            } else {
                $stringcomma = 0;
            }
            $ConsumedNoPacks = "";
            if ($Colorflag == 1) {
                foreach ($ArticleColor as $key => $vl) {
                    if ($stringcomma == 1) {
                        $ConsumedNoPacks .= ($string[$key] + $ConsumedDataNoPacks[$key]) . ",";
                    } else {
                        $ConsumedNoPacks .= ($string + $ConsumedDataNoPacks) . ",";
                    }
                }
            } else {
                $ConsumedNoPacks .= ($string + $ConsumedDataNoPacks) . ",";
            }
            $ConsumedNoPacks = rtrim($ConsumedNoPacks, ',');
            $TransferColorflag = $dataresult[0]->TransferColorflag;
            $TransferArticleColor = json_decode($dataresult[0]->TransferArticleColor);
            $Transferstring = $dataresult[0]->TransferSalesNoPacks;
            $TransferDataNoPacks = $dataresult[0]->TransferNoPacks;
            $TransferArticleId = $dataresult[0]->TransferArticleId;

            if (strpos($Transferstring, ',') !== false) {
                $TransferDataNoPacks = explode(',', $TransferDataNoPacks);
                $Transferstring = explode(',', $Transferstring);
                $Transferstringcomma = 1;
            } else {
                $Transferstringcomma = 0;
            }

            $TransferNoPacks = "";
            if ($TransferColorflag == 1) {
                foreach ($TransferArticleColor as $key => $vl) {
                    if ($Transferstringcomma == 1) {
                        $TransferNoPacks .= ($Transferstring[$key] - $TransferDataNoPacks[$key]) . ",";
                    } else {
                        $TransferNoPacks .= ($Transferstring - $TransferDataNoPacks) . ",";
                    }
                }
            } else {
                $TransferNoPacks .= ($Transferstring - $TransferDataNoPacks) . ",";
            }
            $TransferNoPacks = rtrim($TransferNoPacks, ',');

            DB::beginTransaction();
            try {
                $stocktransferRec = DB::select("select stn.Id as StockTransferNumberId, a.ArticleNumber as ConsumedArticleNumber, aa.ArticleNumber as TransferArticleNumber, concat(stn.StocktransferNumber ,'/', fn.StartYear,'-',fn.EndYear) as StockTransferNumber from stocktransfer st inner join stocktransfernumber stn on stn.Id=st.StocktransferNumberId inner join article a on a.Id=st.ConsumedArticleId inner join article aa on aa.Id=st.TransferArticleId inner join financialyear fn on fn.Id=stn.FinancialYearId where st.Id = '" . $id . "'");
                $loggedUser = Users::where('Id', $LoggedId)->first();

                DB::table('stocktransfer')
                    ->where('Id', '=', $id)
                    ->delete();

                DB::table('inward')
                    ->where('ArticleId', $ConsumedArticleId)
                    ->update(['SalesNoPacks' => $ConsumedNoPacks]);

                DB::table('inward')
                    ->where('ArticleId', $TransferArticleId)
                    ->update(['SalesNoPacks' => $TransferNoPacks]);
                UserLogs::create([
                    'Module' => 'Stock Transfer',
                    'ModuleNumberId' => $stocktransferRec[0]->StockTransferNumberId,
                    'LogType' => 'Deleted',
                    'LogDescription' => $loggedUser->Name . ' deleted consumed article ' . $stocktransferRec[0]->ConsumedArticleNumber . ' and transfer article ' . $stocktransferRec[0]->TransferArticleNumber . ' from StockTransfer Number ' . $stocktransferRec[0]->StockTransferNumber,
                    'UserId' => $loggedUser->Id,
                    'updated_at' => null
                ]);
                DB::commit();
                return response()->json(array("id" => "SUCCESS"), 200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(array("Id" => ""), 200);
            }
        }
    }

    public function PostStocktransfer(Request $request)
    {
        $data = $request->all();
        $search = $data['dataTablesParameters']["search"];
        $UserId = $data['UserID'];
        $startnumber = $data['dataTablesParameters']["start"];
        $vnddataTotal = DB::select("select count(*) as Total from (SELECT stn.UserId, stn.Id, st.ConsumedArticleId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY stn.Id SEPARATOR ',') as ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as StocktransferNumber,  DATE_FORMAT(stn.StocktransferDate, '%d/%m/%Y') as StocktransferDate FROM `stocktransfer` st inner join article a on a.Id=st.ConsumedArticleId left join stocktransfernumber stn on st.StocktransferNumberId=stn.Id inner join users u on u.Id=stn.UserId inner join financialyear fn on fn.Id=stn.FinancialYearId group by st.StocktransferNumberId  order by stn.Id desc) as d");
        $vntotal = $vnddataTotal[0]->Total;
        $length = $data['dataTablesParameters']["length"];
        $wherecustom = "";
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "where d.StocktransferNumber like '%" . $search['value'] . "%' OR cast(d.StocktransferDate as char) like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%' OR d.Remarks like '%" . $search['value'] . "%' OR d.TransferNoPacks like '%" . $search['value'] . "%'";
            $vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT stn.UserId, stn.Id, st.ConsumedArticleId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY stn.Id SEPARATOR ',') as ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as StocktransferNumber,  DATE_FORMAT(stn.StocktransferDate, '%d/%m/%Y') as StocktransferDate ,CountNoPacks(GROUP_CONCAT(CONCAT(st.TransferNoPacks) ORDER BY a.Id SEPARATOR ',')) as TransferNoPacks , stn.Remarks   FROM `stocktransfer` st inner join article a on a.Id=st.ConsumedArticleId left join stocktransfernumber stn on st.StocktransferNumberId=stn.Id inner join users u on u.Id=stn.UserId inner join financialyear fn on fn.Id=stn.FinancialYearId group by st.StocktransferNumberId  order by stn.Id desc) as d " . $searchstring);
            $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
        } else {
            $searchstring = "";
            $vnddataTotalFilterValue = $vntotal;
        }
        $column = $data['dataTablesParameters']["order"][0]["column"];
        switch ($column) {
            case 1:
                $ordercolumn = "d.Id";
                break;
            case 2:
                $ordercolumn = "d.Remarks";
                break;
            case 3:
                $ordercolumn = "d.TransferNoPacks";
                break;
            case 4:
                $ordercolumn = "date(d.StocktransferDate)";
                break;
            default:
                $ordercolumn = "d.Id";
                break;
        }

        $order = "";
        if ($data['dataTablesParameters']["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data['dataTablesParameters']["order"][0]["dir"];
        }
        $vnddata = DB::select("select d.* from (select dd.* from (SELECT stn.UserId, stn.Id, st.ConsumedArticleId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY stn.Id SEPARATOR ',') as ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as StocktransferNumber, DATE_FORMAT(stn.StocktransferDate, '%d/%m/%Y') as StocktransferDate  ,CountNoPacks(GROUP_CONCAT(CONCAT(st.TransferNoPacks) ORDER BY a.Id SEPARATOR ',')) as TransferNoPacks , stn.Remarks  FROM `stocktransfer` st inner join article a on a.Id=st.ConsumedArticleId left join stocktransfernumber stn on st.StocktransferNumberId=stn.Id inner join users u on u.Id=stn.UserId inner join financialyear fn on fn.Id=stn.FinancialYearId group by st.StocktransferNumberId UNION All SELECT stn.UserId, stn.Id, st.ArticleId as ConsumedArticleId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY stn.Id SEPARATOR ',') as ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as StocktransferNumber, DATE_FORMAT(stn.StocktransferDate, '%d/%m/%Y') as StocktransferDate ,CountNoPacks(GROUP_CONCAT(CONCAT(st.NoPacks) ORDER BY a.Id SEPARATOR ',')) as TransferNoPacks , stn.Remarks  FROM `stockshortage` st inner join article a on a.Id=st.ArticleId left join stocktransfernumber stn on st.StocktransferNumberId=stn.Id inner join users u on u.Id=stn.UserId inner join financialyear fn on fn.Id=stn.FinancialYearId group by st.StocktransferNumberId) as dd  group by dd.StocktransferNumber) as d " . $wherecustom . " " . $searchstring . " " . $order . " limit " . $data['dataTablesParameters']["start"] . "," . $length);
        // return ['sql'=>"select d.* from (select dd.* from (SELECT stn.UserId, stn.Id, st.ConsumedArticleId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY stn.Id SEPARATOR ',') as ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as StocktransferNumber, DATE_FORMAT(stn.StocktransferDate, '%d/%m/%Y') as StocktransferDate  ,CountNoPacks(GROUP_CONCAT(CONCAT(st.TransferNoPacks) ORDER BY a.Id SEPARATOR ',')) as TransferNoPacks , stn.Remarks  FROM `stocktransfer` st inner join article a on a.Id=st.ConsumedArticleId left join stocktransfernumber stn on st.StocktransferNumberId=stn.Id inner join users u on u.Id=stn.UserId inner join financialyear fn on fn.Id=stn.FinancialYearId group by st.StocktransferNumberId UNION All SELECT stn.UserId, stn.Id, st.ArticleId as ConsumedArticleId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY stn.Id SEPARATOR ',') as ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as StocktransferNumber, DATE_FORMAT(stn.StocktransferDate, '%d/%m/%Y') as StocktransferDate ,CountNoPacks(GROUP_CONCAT(CONCAT(st.NoPacks) ORDER BY a.Id SEPARATOR ',')) as TransferNoPacks , stn.Remarks  FROM `stockshortage` st inner join article a on a.Id=st.ArticleId left join stocktransfernumber stn on st.StocktransferNumberId=stn.Id inner join users u on u.Id=stn.UserId inner join financialyear fn on fn.Id=stn.FinancialYearId group by st.StocktransferNumberId) as dd  group by dd.StocktransferNumber) as d " . $wherecustom . " " . $searchstring . " " . $order . " limit " . $data['dataTablesParameters']["start"] . "," . $length];
        return array(
            'datadraw' => $data['dataTablesParameters']["draw"],
            'recordsTotal' => $vntotal,
            'recordsFiltered' => $vnddataTotalFilterValue,
            'response' => 'success',
            'startnumber' => $startnumber,
            'search' => count($vnddata),
            'data' => $vnddata,
        );
    }

    public function StockTransferDataCheckUserWise($UserId, $STNO)
    {
        $getdatauser = DB::select("SELECT u.Email, r.* FROM `users` u inner join userrights r on r.RoleId=u.Role where u.Id = '" . $UserId . "' and r.PageId =34");
        $userrole = DB::select("SELECT Role FROM `users`where Id='" . $UserId . "'");
        if ($getdatauser[0]->ListRights == 1) {
            $wherecustom = "";
        } else {
            if ($userrole[0]->Role == 2) {
                $wherecustom = "";
            } else {
                $wherecustom = "UserId=" . $UserId . " and ";
            }
        }

        $checkdata = DB::select("SELECT count(*) as TotalRow FROM stocktransfernumber where " . $wherecustom . " Id=" . $STNO);
        $array = array();
        if ($checkdata[0]->TotalRow > 0) {
            $array["Rights"] = true;
        } else {
            $array["Rights"] = false;
        }
        return response()->json($array, 200);
    }
    public function GetStockTransferChallen($id)
    {
        $getstchallan = DB::select("select coart.ArticleOpenFlag as ConsumedArticleOpenFlag, trart.ArticleOpenFlag as TransferArticleOpenFlag, st.Id, u.Name as UserName, coart.ArticleOpenFlag as CunsumedArticleOpenFlag, trart.ArticleOpenFlag as TransferArticleOpenFlag , coart.ArticleNumber as ConsumedArticleNumber, trart.ArticleNumber as TransferArticleNumber, st.ConsumedNoPacks, st.TransferNoPacks, stn.StocktransferDate as STDate, concat( stn.StocktransferNumber , '/',fn.StartYear,'-',fn.EndYear) as STNumber, stn.Remarks, ccat.Title as ConsumedTitle, tcat.Title as TransferTitle, coart.ArticleColor as ConsumedArticleColor, coart.ArticleSize as ConsumedArticleSize, trart.ArticleColor as TransferArticleColor, trart.ArticleSize as TransferArticleSize from stocktransfer st inner join stocktransfernumber stn on stn.Id=st.StocktransferNumberId inner join users u on u.Id=stn.UserId inner join article coart on coart.Id=st.ConsumedArticleId inner join article trart on trart.Id=st.TransferArticleId inner join financialyear fn on fn.Id=stn.FinancialYearId inner join category ccat on ccat.Id=coart.CategoryId inner join category tcat on tcat.Id=trart.CategoryId where st.StocktransferNumberId = '" . $id . "' ");
        $TotalConsumedNoPacks = 0;
        $TotalTransferNoPacks = 0;
        foreach ($getstchallan as $vl) {
            $ConsumedNoPacks = $vl->ConsumedNoPacks;
            $TransferNoPacks = $vl->TransferNoPacks;
            if ($vl->CunsumedArticleOpenFlag == 0) {
                $PreConsumedArticleColor = json_decode($vl->ConsumedArticleColor);
                $PreConsumedArticleSize = json_decode($vl->ConsumedArticleSize);
                $ConsumedArticleColor = "";
                foreach ($PreConsumedArticleColor as $vl1) {
                    $ConsumedArticleColor .= $vl1->Name . ",";
                }
                $ConsumedArticleColor = rtrim($ConsumedArticleColor, ',');
                $ConsumedArticleSize = "";
                foreach ($PreConsumedArticleSize as $vl2) {
                    $ConsumedArticleSize .= $vl2->Name . ",";
                }
                $ConsumedArticleSize = rtrim($ConsumedArticleSize, ',');
                if (strpos($ConsumedNoPacks, ',') != false) {
                    $TotalConsumedNoPacks =  $TotalConsumedNoPacks + array_sum(explode(",", $ConsumedNoPacks));
                } else {
                    $TotalConsumedNoPacks = $TotalConsumedNoPacks + (int)$ConsumedNoPacks;
                }
            } else {
                $TotalConsumedNoPacks = $TotalConsumedNoPacks + (int)$ConsumedNoPacks;
                $ConsumedArticleColor = "";
                $ConsumedArticleSize = "";
            }
            if ($vl->TransferArticleOpenFlag == 0) {
                $PreTransferArticleColor = json_decode($vl->TransferArticleColor);
                $PreTransferArticleSize = json_decode($vl->TransferArticleSize);
                $TransferArticleColor = "";
                foreach ($PreTransferArticleColor as $vl3) {
                    $TransferArticleColor .= $vl3->Name . ",";
                }
                $TransferArticleColor = rtrim($TransferArticleColor, ',');
                $TransferArticleSize = "";
                foreach ($PreTransferArticleSize as $vl4) {
                    $TransferArticleSize .= $vl4->Name . ",";
                }
                $TransferArticleSize = rtrim($TransferArticleSize, ',');
                if (strpos($TransferNoPacks, ',') != false) {
                    $TotalTransferNoPacks = $TotalTransferNoPacks +   array_sum(explode(",", $TransferNoPacks));
                } else {
                    $TotalTransferNoPacks = $TotalTransferNoPacks +  (int)$TransferNoPacks;
                }
            } else {
                $TotalTransferNoPacks = $TotalTransferNoPacks +  (int)$TransferNoPacks;
                $TransferArticleColor = "";
                $TransferArticleSize = "";
            }
            $vl->ConsumedArticleColor = $ConsumedArticleColor;
            $vl->ConsumedArticleSize = $ConsumedArticleSize;
            $vl->TransferArticleColor = $TransferArticleColor;
            $vl->TransferArticleSize = $TransferArticleSize;
        }
        return ['data' => $getstchallan,  'TotalConsumedNoPacks' => $TotalConsumedNoPacks, "TotalTransferNoPacks" => $TotalTransferNoPacks];
    }

    public function GetStocktransferIdWise($id)
    {
        $getStockTransferData = DB::select("select st.ConsumedNoPacks , st.TransferNoPacks , coart.ArticleOpenFlag as ConsumedArticleOpenFlag,   trart.ArticleOpenFlag as TransferArticleOpenFlag, st.Id, u.Name as UserName, coart.ArticleNumber as ConsumedArticleNumber,coart.Id as ConsumedArticleId , coart.ArticleRatio as ConsumeArticleRatio ,trart.ArticleRatio as TransferArticleRatio   , trart.Id as  TransferArticleId  ,  trart.ArticleNumber as TransferArticleNumber, st.ConsumedNoPacks, st.TransferNoPacks, stn.StocktransferDate as STDate, concat( stn.StocktransferNumber , '/',fn.StartYear,'-',fn.EndYear) as STNumber, stn.Remarks, ccat.Title as ConsumedTitle, tcat.Title as TransferTitle, ccat.Colorflag as ConsumeColorflag , tcat.Colorflag as TransferColorflag , coart.ArticleColor as ConsumedArticleColor, coart.ArticleSize as ConsumedArticleSize, trart.ArticleColor as TransferArticleColor, trart.ArticleSize as TransferArticleSize from stocktransfer st inner join stocktransfernumber stn on stn.Id=st.StocktransferNumberId inner join users u on u.Id=stn.UserId inner join article coart on coart.Id=st.ConsumedArticleId inner join article trart on trart.Id=st.TransferArticleId inner join financialyear fn on fn.Id=stn.FinancialYearId inner join category ccat on ccat.Id=coart.CategoryId inner join category tcat on tcat.Id=trart.CategoryId where st.Id = '" . $id . "' ");
        // return ($getStockTransferData);
        // $stkdata = DB::select("SELECT * FROM `outlet` o INNER JOIN outletnumber oltn ON o.OutletNumberId = oltn.Id INNER JOIN Article a ON o.ArticleId = a.Id WHERE `PartyId` = '" . $id . "' ");
        // return ($stkdata);

        if ($getStockTransferData[0]->TransferArticleOpenFlag == 0) {
            $inwardpro = Inward::where('ArticleId', $getStockTransferData[0]->TransferArticleId)->first();
            $getStockTransferData[0]->ProSalesNoPacks = $inwardpro->SalesNoPacks;
        } else {
            $inwards  = Inward::where('ArticleId', $getStockTransferData[0]->TransferArticleId)->get();
            $outwards  = Outward::where('ArticleId', $getStockTransferData[0]->TransferArticleId)->get();
            $salesreturns = Salesreturn::where('ArticleId', $getStockTransferData[0]->TransferArticleId)->get();
            $purchasereturns = Purchasereturns::where('ArticleId', $getStockTransferData[0]->TransferArticleId)->get();
            $consumestocktransfers = Stocktransfer::where('ConsumedArticleId', $getStockTransferData[0]->TransferArticleId)->get();
            $transferstocktransfers = Stocktransfer::where('TransferArticleId', $getStockTransferData[0]->TransferArticleId)->get();
            $shortedStocks = Stockshortage::where('ArticleId', $getStockTransferData[0]->TransferArticleId)->get();
            $sorecords  = SO::where('ArticleId', $getStockTransferData[0]->TransferArticleId)->where('Status', 0)->get();
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
            $getStockTransferData[0]->ProSalesNoPacks = $SalesNoPacks;
        }
        if ($getStockTransferData[0]->ConsumedArticleOpenFlag == 0) {
            $inwardtra = Inward::where('ArticleId', $getStockTransferData[0]->ConsumedArticleId)->first();
            $getStockTransferData[0]->ConNoPacks = $inwardtra->SalesNoPacks;
        } else {
            $inwards  = Inward::where('ArticleId', $getStockTransferData[0]->ConsumedArticleId)->get();
            $outwards  = Outward::where('ArticleId', $getStockTransferData[0]->ConsumedArticleId)->get();
            $salesreturns = Salesreturn::where('ArticleId', $getStockTransferData[0]->ConsumedArticleId)->get();
            $purchasereturns = Purchasereturns::where('ArticleId', $getStockTransferData[0]->ConsumedArticleId)->get();
            $consumestocktransfers = Stocktransfer::where('ConsumedArticleId', $getStockTransferData[0]->ConsumedArticleId)->get();
            $transferstocktransfers = Stocktransfer::where('TransferArticleId', $getStockTransferData[0]->ConsumedArticleId)->get();
            $shortedStocks = Stockshortage::where('ArticleId', $getStockTransferData[0]->ConsumedArticleId)->get();
            $sorecords  = SO::where('ArticleId', $getStockTransferData[0]->ConsumedArticleId)->where('Status', 0)->get();
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
            $getStockTransferData[0]->ConNoPacks = $SalesNoPacks;
        }
        return $getStockTransferData;
    }

    public function updateStockTransfer(Request $request)
    {
        $data = $request->all();
        if ($data['TransferType'] == 1) {
            $ConsumeArticleId = $data['ArticleId'];
            $ProductionArticleId = $data['ProductionArticleId'];
            $stockTransferId = $data['id'];
            $stRecord  = DB::select("select st.Id, trart.Id as  TransferArticleId  , coart.Id as ConsumedArticleId ,  st.ConsumedNoPacks , st.TransferNoPacks , coart.ArticleOpenFlag as ConsumedArticleOpenFlag,   trart.ArticleOpenFlag as TransferArticleOpenFlag,   coart.ArticleRatio as ConsumeArticleRatio ,trart.ArticleRatio as TransferArticleRatio  , coart.ArticleNumber as ConsumeArticleNumber , trart.ArticleNumber as TransferArticleNumber, st.ConsumedNoPacks, st.TransferNoPacks, stn.StocktransferDate as STDate, stn.Id as StNumberId ,  concat( stn.StocktransferNumber , '/',fn.StartYear,'-',fn.EndYear) as STNumber, stn.Remarks, ccat.Title as ConsumedTitle, tcat.Title as TransferTitle, ccat.Colorflag as ConsumeColorflag , tcat.Colorflag as TransferColorflag , coart.ArticleColor as ConsumedArticleColor, coart.ArticleSize as ConsumedArticleSize, trart.ArticleColor as TransferArticleColor, trart.ArticleSize as TransferArticleSize from stocktransfer st inner join stocktransfernumber stn on stn.Id=st.StocktransferNumberId inner join users u on u.Id=stn.UserId inner join article coart on coart.Id=st.ConsumedArticleId inner join article trart on trart.Id=st.TransferArticleId inner join financialyear fn on fn.Id=stn.FinancialYearId inner join category ccat on ccat.Id=coart.CategoryId inner join category tcat on tcat.Id=trart.CategoryId where st.Id = '" . $stockTransferId . "' ");
            //Calculation of Consume
            $conInwardRec  = Inward::where('ArticleId',  $ConsumeArticleId)->first();
            if ($stRecord[0]->ConsumedArticleOpenFlag == 0) {
                $conArticleSelectedColors = $data['ArticleSelectedColor'];
                $oldConNopacks = explode(',', $stRecord[0]->ConsumedNoPacks);
                $oldSalesNoPacks = explode(',', $conInwardRec->SalesNoPacks);
                $newSalesNoPacks  = $oldSalesNoPacks;
                $conCount = 0;
                $newConsumeNoPacks = "";


                foreach ($conArticleSelectedColors as $conArticleSelectedColor) {

                    $preNoPacksNew_ = $data["NoPacksNew_" . $conArticleSelectedColor['Id']];
                    //yashvi factory art

                    $currentSalesNoPacks = DB::table('artstockstatus')
                    ->where(['outletId' =>  0])
                    ->where(['ArticleId' => $data["ArticleId"]])
                    ->value('SalesNoPacks');
                  
                    $artD = DB::table('article')->join('category', 'article.CategoryId', '=', 'category.Id')
                    ->where('article.Id', $data["ArticleId"])
                    ->first();

                    // Convert comma-separated values to arrays

                    if (is_array($currentSalesNoPacks)) {
                        $currentSalesNoPacks = implode(',', $currentSalesNoPacks);
                    }                    
                $currentSalesNoPacksArray = explode(',', $currentSalesNoPacks);
                $oldConNopacksArray = explode(',', $oldConNopacks);
                $preNoPacksNew_Array = explode(',', $preNoPacksNew_);

                  // Perform element-wise addition
                  $newSalesNoPacksArray = [];

                  for ($i = 0; $i < count($preNoPacksNew_Array); $i++) {
                    $newSalesNoPacksArray[$i] = (int)$currentSalesNoPacksArray[$i] + (int)$oldConNopacksArray[$i] - (int)$preNoPacksNew_Array[$i];
                }
            
                // Convert back to comma-separated string
                $newSalesNoPacks = implode(',', $newSalesNoPacksArray);

                // Perform the updateOrInsert operation with the new SalesNoPacks value
                
                $packes = $newSalesNoPacks;
                $packesArray = explode(',', $packes);
                $sum = array_sum($packesArray); 

                     // Perform the updateOrInsert operation with the new SalesNoPacks value
                    DB::table('artstockstatus')->updateOrInsert(
                    ['outletId' => 0,'ArticleId' => $data['ArticleId']],
                    ['Title' => $artD->Title,'ArticleNumber' => $artD->ArticleNumber,'SalesNoPacks' => $packesArray,
                    'TotalPieces' => $sum]
                    );
                    //CLOSE

                         
                    if ($data["NoPacksNew_" . $conArticleSelectedColor['Id']] <= ($data["NoPacks_" . $conArticleSelectedColor['Id']] + $oldConNopacks[$conCount])) {
                        $newSalesNoPacks[$conCount] =  ($newSalesNoPacks[$conCount] +  $oldConNopacks[$conCount]) - $data["NoPacksNew_" . $conArticleSelectedColor['Id']];
                    } else {
                        return response()->json(["status" => "failed", "articleType" => 0], 200);
                    }
                    if (count($conArticleSelectedColors) == $conCount + 1) {
                        $newConsumeNoPacks = $newConsumeNoPacks . $data["NoPacksNew_" . $conArticleSelectedColor['Id']];
                    } else {
                        $newConsumeNoPacks = $newConsumeNoPacks . $data["NoPacksNew_" . $conArticleSelectedColor['Id']] . ",";
                    }
                    $conCount  = $conCount + 1;

                    
                }
                $newSalesNoPacksGot = implode(',', $newSalesNoPacks);
                $newConsumeNoPacksGot =  $newConsumeNoPacks;
            } else {
                $oldConNopacks = explode(',', $stRecord[0]->ConsumedNoPacks);
                  //yashvi factory art

                  $currentSalesNoPacks = DB::table('artstockstatus')
                  ->where(['outletId' =>  0])
                  ->where(['ArticleId' => $data["ArticleId"]])
                  ->value('SalesNoPacks');
                  
                                       
                  $artD = DB::table('article')->join('category', 'article.CategoryId', '=', 'category.Id')
                  ->where('article.Id', $data["ArticleId"])
                  ->first();
                  
                  // Calculate the new SalesNoPacks value by adding the new value to the current value                        
                   $newSalesNoPacks = $currentSalesNoPacks + (int)$oldConNopacks- (int)$data["NoPacksNew"];
                                           
                   // Perform the updateOrInsert operation with the new SalesNoPacks value
                  DB::table('artstockstatus')->updateOrInsert(
                  ['outletId' => 0,'ArticleId' => $data['ArticleId']],
                  ['Title' => $artD->Title,'ArticleNumber' => $artD->ArticleNumber,'SalesNoPacks' => $newSalesNoPacks,
                  'TotalPieces' => $newSalesNoPacks]
                  );
                  //CLOSE
                if ((int)$data["NoPacksNew"] <= (int)$data["NoPacks"] + (int)$stRecord[0]->ConsumedNoPacks) {
                    $newSalesNoPacksGot =  ((int)$stRecord[0]->ConsumedNoPacks +  (int)$data["NoPacks"]) - (int)$data["NoPacksNew"];
                    $newConsumeNoPacksGot = (int)$data["NoPacksNew"];
                } else {
                    return response()->json(["status" => "failed", "articleType" => 0], 200);
                }
            }
            //Calculation of Production
            $proInwardRec  = Inward::where('ArticleId',  $ProductionArticleId)->first();
            if ($stRecord[0]->TransferArticleOpenFlag == 0) {
                $proArticleSelectedColors = $data['ProductionArticleSelectedColor'];
                $oldProNopacks = explode(',', $stRecord[0]->TransferNoPacks);
                $oldProSalesNoPacks = explode(',', $proInwardRec->SalesNoPacks);
                $newProSalesNoPacks  = $oldProSalesNoPacks;
                $proCount = 0;
                $newProductionNoPacks = "";
                foreach ($proArticleSelectedColors as $proArticleSelectedColor) {
                    if ((($data["ProductionNoPacks_" . $proArticleSelectedColor['Id']] - $oldProNopacks[$proCount]) + $data["ProductionNoPacksNew_" . $proArticleSelectedColor['Id']]) >= 0) {
                        $newProSalesNoPacks[$proCount] =  ($newProSalesNoPacks[$proCount] -  $oldProSalesNoPacks[$proCount]) + $data["ProductionNoPacksNew_" . $proArticleSelectedColor['Id']];
                    } else {
                        return response()->json(["status" => "failed", "articleType" => 1], 200);
                    }
                    if (count($proArticleSelectedColors) == $proCount + 1) {
                        $newProductionNoPacks = $newProductionNoPacks . $data["ProductionNoPacksNew_" . $proArticleSelectedColor['Id']];
                    } else {
                        $newProductionNoPacks = $newProductionNoPacks . $data["ProductionNoPacksNew_" . $proArticleSelectedColor['Id']] . ",";
                    }
                    $proCount  = $proCount + 1;

                    //YASHVI


                    $preNoPacksNew_ = $data["ProductionNoPacksNew_" . $proArticleSelectedColor['Id']];
                
                    $currentSalesNoPacks = DB::table('artstockstatus')
                    ->where(['outletId' =>  0])
                    ->where(['ArticleId' => $data["ProductionArticleId"]])
                    ->value('SalesNoPacks');

                    $artD = DB::table('article')
                    ->join('category', 'article.CategoryId', '=', 'category.Id')
                    ->where('article.Id', $data["ProductionArticleId"])
                    ->first();

                    // Convert comma-separated values to arrays
                $currentSalesNoPacksArray = explode(',', $currentSalesNoPacks);
                $oldProNopacksArray = explode(',', $oldProNopacks);
                $preNoPacksNew_Array = explode(',', $preNoPacksNew_);

                  // Perform element-wise addition
                  $newSalesNoPacksArray = [];

                  for ($i = 0; $i < count($preNoPacksNew_Array); $i++) {
                    $newSalesNoPacksArray[$i] = (int)$currentSalesNoPacksArray[$i] - (int)$oldProNopacksArray[$i] + (int)$preNoPacksNew_Array[$i];
                }
            
                // Convert back to comma-separated string
                $newSalesNoPacks = implode(',', $newSalesNoPacksArray);

                // Perform the updateOrInsert operation with the new SalesNoPacks value
                
                $packes = $newSalesNoPacks;
                $packesArray = explode(',', $packes);
                $sum = array_sum($packesArray); 
     
                        // Perform the updateOrInsert operation with the new SalesNoPacks value
                        DB::table('artstockstatus')->updateOrInsert(
                            [
                                'outletId' => 0,
                                'ArticleId' => $data['ProductionArticleId']
                            ],
                            [ 
                                'Title' => $artD->Title,
                                'ArticleNumber' => $artD->ArticleNumber,
                                'SalesNoPacks' => $packesArray,
                                'TotalPieces' => $sum
                            ]
                        );

                        //CLOSE
                }
                $newProSalesNoPacksGot = implode(',', $newProSalesNoPacks);
                $newProductionNoPacksGot =  $newProductionNoPacks;
            } else {
                //YASHVI

                $currentSalesNoPacks = DB::table('artstockstatus')
                ->where(['outletId' =>  0])
                ->where(['ArticleId' => $data["ProductionArticleId"]])
                ->value('SalesNoPacks');

                
                $artD = DB::table('article')
                    ->join('category', 'article.CategoryId', '=', 'category.Id')
                    ->where('article.Id', $data["ProductionArticleId"])
                    ->first();
                    // Calculate the new SalesNoPacks value by adding the new value to the current value                        
                    $newSalesNoPacks = $currentSalesNoPacks - (int)$oldConNopacks +  (int)$data["ProductionNoPacksNew"];
                    
                    // Perform the updateOrInsert operation with the new SalesNoPacks value
                    DB::table('artstockstatus')->updateOrInsert(
                        [
                            'outletId' => 0,
                            'ArticleId' => $data['ProductionArticleId']
                        ],
                        [ 
                            'Title' => $artD->Title,
                            'ArticleNumber' => $artD->ArticleNumber,
                            'SalesNoPacks' => $newSalesNoPacks,
                            'TotalPieces' => $newSalesNoPacks
                        ]
                    );

                    //CLOSE
                if (((int)$data["ProductionNoPacks"] - (int)$stRecord[0]->TransferNoPacks + (int)$data["ProductionNoPacksNew"]) >= 0) {
                    $newProSalesNoPacksGot =  ((int)$stRecord[0]->TransferNoPacks -  (int)$data["ProductionNoPacks"]) + (int)$data["ProductionNoPacksNew"];
                    $newProductionNoPacksGot = (int)$data["ProductionNoPacksNew"];
                } else {
                    return response()->json(["status" => "failed", "articleType" => 1], 200);
                }
            }
            //Updation
            if ($stRecord[0]->ConsumedArticleOpenFlag == 0) {
                
                Inward::where('ArticleId',  $ConsumeArticleId)->update([
                    'SalesNoPacks' =>  $newSalesNoPacksGot
                ]);
            } else {
                DB::table('mixnopacks')->where('ArticleId', $ConsumeArticleId)->update([
                    "NoPacks" => $newSalesNoPacksGot
                ]);
            }
            if ($stRecord[0]->TransferArticleOpenFlag == 0) {
                $proInwardRec  = Inward::where('ArticleId',  $ProductionArticleId)->update([
                    'SalesNoPacks' =>  $newProSalesNoPacksGot
                ]);
            } else {
                DB::table('mixnopacks')->where('ArticleId', $ProductionArticleId)->update([
                    "NoPacks" => $newProSalesNoPacksGot
                ]);
            }
            $logDesc = "";
            $ActiveStockTransfer = Stocktransfer::where('id', $stockTransferId)->first();
            if ($ActiveStockTransfer->ConsumedNoPacks  != $newConsumeNoPacksGot) {
                $logDesc = $logDesc . 'Consumed Pieces,';
            }
            if ($ActiveStockTransfer->TransferNoPacks  != $newProductionNoPacksGot) {
                $logDesc = $logDesc . 'Transfer Pieces,';
            }
            $newLogDesc = rtrim($logDesc, ',');
            $userRec = Users::where('Id', $data['UserId'])->first();
            $stocktransferRec = DB::select("select stn.Id as StockTransferNumberId, a.ArticleNumber as ConsumedArticleNumber, aa.ArticleNumber as TransferArticleNumber, concat(stn.StocktransferNumber ,'/', fn.StartYear,'-',fn.EndYear) as StockTransferNumber from stocktransfer st inner join stocktransfernumber stn on stn.Id=st.StocktransferNumberId inner join article a on a.Id=st.ConsumedArticleId inner join article aa on aa.Id=st.TransferArticleId inner join financialyear fn on fn.Id=stn.FinancialYearId where st.Id = '" . $stockTransferId . "'");
            // $outletRec = DB::select("select concat('un.OutletNumber','/', fn.StartYear,'-',fn.EndYear) as OutletNumber from outletnumber un inner join financialyear fn on fn.Id=un.FinancialYearId where un.Id= '" . $data['OutletNumberId'] . "'");
            UserLogs::create([
                'Module' => 'Stock Transfer',
                'ModuleNumberId' => $stocktransferRec[0]->StockTransferNumberId,
                'LogType' => 'Updated',
                'LogDescription' => $userRec->Name . ' updated ' . $newLogDesc . ' of article ' . $stocktransferRec[0]->ConsumedArticleNumber . ' and ' . $stocktransferRec[0]->TransferArticleNumber . ' in StockTransfer number ' . $stocktransferRec[0]->StockTransferNumber,
                'UserId' => $userRec->Id,
                'updated_at' => null
            ]);
            Stocktransfer::where('Id', $stockTransferId)->update([
                'ConsumedNoPacks' => $newConsumeNoPacksGot,
                'TransferNoPacks' => $newProductionNoPacksGot
            ]);
            DB::table('stocktransfernumber')->where('Id', $stRecord[0]->StNumberId)->update([
                'Remarks' => $data['Remarks']
            ]);

            return response()->json(["status" => "success", "id" => $stRecord[0]->StNumberId], 200);
        }
    }

    public function DeletestocktransferNumber($id, $LoggedId)
    {
        $identifySTRecords = DB::select("select st.*, concat( stn.StocktransferNumber , '/',fn.StartYear,'-',fn.EndYear) as STNumber, trart.ArticleColor as TraArticleColor , conar.ArticleColor as ConArticleColor , trart.ArticleOpenFlag as TraArticleOpenFlag , conar.ArticleOpenFlag as ConArticleOpenFlag from stocktransfer st inner join stocktransfernumber stn on stn.Id=st.StocktransferNumberId inner join financialyear fn on fn.Id=stn.FinancialYearId inner join article conar on conar.Id=st.ConsumedArticleId inner join article trart on trart.Id=st.TransferArticleId where st.StockTransferNumberId='" . $id . "'");
        //Check Packs
        foreach ($identifySTRecords as $identifySTRecord) {
            $TransferArticleId =  $identifySTRecord->TransferArticleId;
            if ($identifySTRecord->TraArticleOpenFlag == 0) {
                $traInwardRec = Inward::where('ArticleId', $TransferArticleId)->first();
                $TraSalesNoPacks = explode(',', $traInwardRec->SalesNoPacks);
                $TransferNoPacks =  explode(',', $identifySTRecord->TransferNoPacks);
                $traCount = 0;
                foreach ($TraSalesNoPacks as $TraSalesNoPack) {
                    $TraSalesNoPacks[$traCount] = $TraSalesNoPacks[$traCount] - $TransferNoPacks[$traCount];
                    if ($TraSalesNoPacks[$traCount] < 0) {
                        return response()->json(['status' => 'failed', 'GoingMinus' => true], 200);
                    }
                    $traCount  = $traCount + 1;
                }
            } else {
                $inwards  = Inward::where('ArticleId', $TransferArticleId)->get();
                $outwards  = Outward::where('ArticleId', $TransferArticleId)->get();
                $salesreturns = Salesreturn::where('ArticleId', $TransferArticleId)->get();
                $purchasereturns = Purchasereturns::where('ArticleId', $TransferArticleId)->get();
                $consumestocktransfers = Stocktransfer::where('ConsumedArticleId', $TransferArticleId)->get();
                $transferstocktransfers = Stocktransfer::where('TransferArticleId', $TransferArticleId)->get();
                $shortedStocks = Stockshortage::where('ArticleId', $TransferArticleId)->get();
                $sorecords  = SO::where('ArticleId', $TransferArticleId)->where('Status', 0)->get();
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
                if ($SalesNoPacks - $identifySTRecord->TransferNoPacks < 0) {
                    return response()->json(['status' => 'failed', 'GoingMinus' => true], 200);
                }
            }
        }
        //Production & Consumption deletion
        foreach ($identifySTRecords as $identifySTRecord) {
            $TransferArticleId =  $identifySTRecord->TransferArticleId;
            if ($identifySTRecord->TraArticleOpenFlag == 0) {
                $traInwardRec = Inward::where('ArticleId', $TransferArticleId)->first();
                $TraSalesNoPacks = explode(',', $traInwardRec->SalesNoPacks);
                $TransferNoPacks =  explode(',', $identifySTRecord->TransferNoPacks);
                $traCount = 0;
                $newTraSalesNoPacks =  $TraSalesNoPacks;
                foreach ($TraSalesNoPacks as $TraSalesNoPack) {
                    $newTraSalesNoPacks[$traCount] = $newTraSalesNoPacks[$traCount] - $TransferNoPacks[$traCount];
                    $traCount  = $traCount + 1;
                }
                $newTraSalesNoPacks =     implode(",", $newTraSalesNoPacks);
            } else {
                $inwards  = Inward::where('ArticleId', $TransferArticleId)->get();
                $outwards  = Outward::where('ArticleId', $TransferArticleId)->get();
                $salesreturns = Salesreturn::where('ArticleId', $TransferArticleId)->get();
                $purchasereturns = Purchasereturns::where('ArticleId', $TransferArticleId)->get();
                $consumestocktransfers = Stocktransfer::where('ConsumedArticleId', $TransferArticleId)->get();
                $transferstocktransfers = Stocktransfer::where('TransferArticleId', $TransferArticleId)->get();
                $shortedStocks = Stockshortage::where('ArticleId', $TransferArticleId)->get();
                $sorecords  = SO::where('ArticleId', $TransferArticleId)->where('Status', 0)->get();
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
                $newTraSalesNoPacks =  $SalesNoPacks;
            }


            $ConsumedArticleId  =  $identifySTRecord->ConsumedArticleId;
            if ($identifySTRecord->ConArticleOpenFlag == 0) {
                $conInwardRec = Inward::where('ArticleId', $ConsumedArticleId)->first();
                $ConSalesNoPacks = explode(',', $conInwardRec->SalesNoPacks);
                $ConsumeNoPacks =  explode(',', $identifySTRecord->ConsumedNoPacks);
                $conCount = 0;
                $newConSalesNoPacks =  $ConSalesNoPacks;
                foreach ($ConSalesNoPacks as $ConSalesNoPack) {
                    $newConSalesNoPacks[$conCount] = $newConSalesNoPacks[$conCount] + $ConsumeNoPacks[$conCount];
                    $conCount  = $conCount + 1;
                }
                $newConSalesNoPacks =     implode(",", $newConSalesNoPacks);
            } else {
                $coninwards  = Inward::where('ArticleId', $ConsumedArticleId)->get();
                $conoutwards  = Outward::where('ArticleId', $ConsumedArticleId)->get();
                $consalesreturns = Salesreturn::where('ArticleId', $ConsumedArticleId)->get();
                $conpurchasereturns = Purchasereturns::where('ArticleId', $ConsumedArticleId)->get();
                $conconsumestocktransfers = Stocktransfer::where('ConsumedArticleId', $ConsumedArticleId)->get();
                $contransferstocktransfers = Stocktransfer::where('TransferArticleId', $ConsumedArticleId)->get();
                $conshortedStocks = Stockshortage::where('ArticleId', $ConsumedArticleId)->get();
                $consorecords  = SO::where('ArticleId', $ConsumedArticleId)->where('Status', 0)->get();
                $conSalesNoPacks = 0;
                foreach ($coninwards  as $inward) {
                    $conSalesNoPacks = $conSalesNoPacks + $inward->NoPacks;
                }
                foreach ($consalesreturns  as $salesreturn) {
                    $conSalesNoPacks = $conSalesNoPacks + $salesreturn->NoPacks;
                }
                foreach ($conoutwards  as $outward) {
                    $conSalesNoPacks = $conSalesNoPacks - $outward->NoPacks;
                }
                foreach ($conpurchasereturns  as $purchasereturn) {
                    $conSalesNoPacks = $conSalesNoPacks - $purchasereturn->ReturnNoPacks;
                }
                foreach ($conconsumestocktransfers  as $stocktransfer) {
                    $conSalesNoPacks = $conSalesNoPacks - $stocktransfer->ConsumedNoPacks;
                }
                foreach ($contransferstocktransfers  as $stocktransfer) {
                    $conSalesNoPacks = $conSalesNoPacks + $stocktransfer->TransferNoPacks;
                }
                foreach ($conshortedStocks  as $shortedStock) {
                    $conSalesNoPacks = $conSalesNoPacks - $shortedStock->NoPacks;
                }
                if (!empty($consorecords)) {
                    foreach ($consorecords  as $sorecord) {
                        $conSalesNoPacks = $conSalesNoPacks - $sorecord->OutwardNoPacks;
                    }
                }
                $newConSalesNoPacks =  $conSalesNoPacks;
            }
            if ($identifySTRecord->ConArticleOpenFlag == 0) {
                Inward::where('ArticleId', $ConsumedArticleId)->update([
                    'SalesNoPacks' => $newConSalesNoPacks
                ]);
            } else {
                DB::table('mixnopacks')->where('ArticleId', $ConsumedArticleId)->update([
                    "NoPacks" => (int)$newConSalesNoPacks + (int)$identifySTRecord->ConsumedNoPacks
                ]);
            }
            if ($identifySTRecord->TraArticleOpenFlag == 0) {
                Inward::where('ArticleId', $TransferArticleId)->update([
                    'SalesNoPacks' => $newTraSalesNoPacks
                ]);
            } else {
                DB::table('mixnopacks')->where('ArticleId', $TransferArticleId)->update([
                    "NoPacks" => (int)$newTraSalesNoPacks - (int)$identifySTRecord->TransferNoPacks
                ]);
            }
            Stocktransfer::where('Id', $identifySTRecord->Id)->delete();
        }
        $loggedUser = Users::where('Id', $LoggedId)->first();
        UserLogs::create([
            'Module' => 'Stock Transfer',
            'ModuleNumberId' => $id,
            'LogType' => 'Deleted',
            'LogDescription' => $loggedUser->Name . " " . 'deleted Stock Transfer Number' . " " . $identifySTRecords[0]->STNumber,
            'UserId' => $LoggedId,
            'updated_at' => null
        ]);
        return response()->json(["status" => "success"], 200);
    }
    public function StocktransferLogs($STNOId)
    {
        return DB::select("select * from (select ul.Id as UserLogsId, ul.Module, ul.LogType, ul.ModuleNumberId, ul.LogDescription, DATE_FORMAT(ul.created_at ,'%d-%m-%Y') as CreatedDate, DATE_FORMAT(ul.created_at ,'%H:%i:%s') as CreatedTime, u.Name, u.Status, ur.Role from userlogs ul inner join users u on u.Id=ul.UserId inner join userrole ur on ur.RoleType=u.Role) as dd where dd.ModuleNumberId= '" . $STNOId . "' and dd.Module='Stock Transfer' order by dd.UserLogsId desc ");
    }
}
