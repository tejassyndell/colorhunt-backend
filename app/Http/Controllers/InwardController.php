<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Inward;
use App\Article;
use App\UserLogs;
use App\Users;



class InwardController extends Controller
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
    public function AddInward(Request $request)
    {
        $data = $request->all();
        if ($data['GRN_Number'] == "Add") {

            $generate_GRN = $this->GetGRNNumber();
        }
        if ($data["ArticleStatus"] == true) {
            $ArticleStatus = 1;
        } else {
            $ArticleStatus = 0;
        }
        $dataresult = DB::select('SELECT p.*, a.ArticleOpenFlag, c.Colorflag FROM `po` p left join category c on c.Id = p.CategoryId inner join article a on a.Id=p.ArticleId where p.Id="' . $data['PoId'] . '"');
        $Colorflag = $dataresult[0]->Colorflag;
        $ArticleId = $dataresult[0]->ArticleId;
        $ArticleOpenFlag = $dataresult[0]->ArticleOpenFlag;
        if ($data['ColorId']) {
            $ArticleColor = json_encode($data['ColorId']);
        } else {
            $ArticleColor = "";
        }
        if ($data['SizeId']) {
            $ArticleSize = json_encode($data['SizeId']);
        } else {
            $ArticleSize = "";
        }

        if (isset($data['RatioId'])) {
            $ArticleRatio = $data['RatioId'];
        } else {
            $ArticleRatio = 1;
        }

        if (!isset($data['RatioId'])) {
            $data['RatioId'] = 1;
        }
        $NoPacks = "";
        $rejections = [];
        if (isset($data['RejectionId'])) {
            $TotalNoPacks = 0;
            foreach ($data['RejectionId'] as $rejection) {
                if ($data["Rej_" . $rejection['Id']] != 0) {
                    array_push($rejections, ['Id' => $rejection['Id'], 'RejectionType' =>  $rejection['RejectionType'], 'RejectionPacks' => $data["Rej_" . $rejection['Id']]]);
                    $TotalNoPacks = $TotalNoPacks + (int)$data["Rej_" . $rejection['Id']];
                }
            }
            if ($TotalNoPacks == 0) {
                return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
            } else {
                $data['NoPacks'] = $TotalNoPacks;
            }
            if (count($rejections) == 0) {
                $rejections = null;
            } else {
                $rejections = json_encode($rejections);
            }
        } else {
            $rejections = null;
        }
        if ($Colorflag == 1) {
            foreach ($data['ColorId'] as $vl) {
                $numberofpacks = $vl["Id"];
                if ($data["NoPacks_" . $numberofpacks] == 0) {
                    return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                }
                $NoPacks .= $data["NoPacks_" . $numberofpacks] . ",";
            }
        } else {
            if ($data['NoPacks'] == 0) {
                return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
            }
            $NoPacks .= $data['NoPacks'];
        }
        $NoPacks = rtrim($NoPacks, ',');
        if ($data['ColorId']) {
            $countcolor = count($data['ColorId']);
        } else {
            $countcolor = 1;
        }

        $countration = array_sum(explode(",", $data['RatioId']));
        $countNoPacks = array_sum(explode(",", $NoPacks));
        // return $NoPacks;
        
        
        
        ////Nitin Art Stock Status
			
					// Fetch the current SalesNoPacks value
						$currentSalesNoPacks = DB::table('artstockstatus')
							->where(['outletId' => 0])
							->where(['ArticleId' => $dataresult[0]->ArticleId])
							->value('SalesNoPacks');
						
							
						$artD = DB::table('article')
							->join('category', 'article.CategoryId', '=', 'category.Id')
							->where('article.Id', $dataresult[0]->ArticleId)
							->first();

						
						// Calculate the new SalesNoPacks value by adding the new value to the current value
						if($currentSalesNoPacks == '' || $currentSalesNoPacks == null){
						    $newSalesNoPacks = $NoPacks;
						}else{
						    $newSalesNoPacks = $currentSalesNoPacks + $NoPacks;
						}
						
						$packes = $newSalesNoPacks;
    					$packesArray = explode(',', $packes);
    					$sum = array_sum($packesArray);
						
						// Perform the updateOrInsert operation with the new SalesNoPacks value
						DB::table('artstockstatus')->updateOrInsert(
							[
								'outletId' => 0,
								'ArticleId' => $dataresult[0]->ArticleId
							],
							[
								'Title' => $artD->Title,
								'ArticleNumber' => $artD->ArticleNumber,
								'SalesNoPacks' => $newSalesNoPacks,
								'TotalPieces' => $sum 
							] 
						);
			
			//close
        
        
        
        
        if ($Colorflag == 1) { 
            $TotalSetQuantity = ($countNoPacks * $countration);
        } else {
            $TotalSetQuantity = ($countNoPacks * ($countration * $countcolor));
        }
        if ($ArticleOpenFlag == 0) {
            $articledata = DB::select("select count(*) as Total, ArticleRate from articlerate where ArticleId = '" . $ArticleId . "'");
            if ($articledata[0]->Total > 0) {
                $ArticleRate = $articledata[0]->ArticleRate;
            } else {
                if (isset($data['Rate'])) {
                    $ArticleRate = $data['Rate'];
                }
            }
            DB::table('article')
                ->where('Id', $ArticleId)
                ->update(['ArticleRate' => $ArticleRate, 'ArticleColor' => $ArticleColor, 'ArticleSize' => $ArticleSize, 'ArticleRatio' => $ArticleRatio, 'ArticleStatus' => $ArticleStatus, 'UpdatedDate' => date("Y-m-d H:i:s")]);
            $getratio = explode(",", $data['RatioId']);
            foreach ($data['ColorId'] as $vl) {
                DB::table('articlecolor')->insertGetId(
                    ['ArticleId' => $ArticleId, 'ArticleColorId' => $vl["Id"], 'ArticleColorName' => $vl["Name"], 'CreatedDate' => date("Y-m-d H:i:s")]
                );
            }
            foreach ($data['SizeId'] as $key => $vl) {
                DB::table('articlesize')->insertGetId(
                    ['ArticleId' => $ArticleId, 'ArticleSize' => $vl["Id"], 'ArticleSizeName' => $vl["Name"], 'CreatedDate' => date("Y-m-d H:i:s")]
                );
                DB::table('articleratio')->insertGetId(
                    ['ArticleId' => $ArticleId, 'ArticleSizeId' => $vl["Id"], 'ArticleRatio' => $getratio[$key], 'CreatedDate' => date("Y-m-d H:i:s")]
                );
            }
            if ($articledata[0]->Total > 0) {
                $ArticleRate = $articledata[0]->ArticleRate;
            } else {
                if (isset($data['Rate'])) {
                    $ArticleRate = $data['Rate'];
                    DB::table('articlerate')->insertGetId(
                        ['ArticleId' => $ArticleId, 'ArticleRate' => $ArticleRate, 'CreatedDate' => date("Y-m-d H:i:s")]
                    );
                }
            }
        } else {
            $TotalSetQuantity = $countNoPacks;
        }
        if ($data['VendorId'] == "") {
            $VendorId = 0;
        } else {
            $VendorId = $data['VendorId'];
        }
        if ($data['GRN_Number'] == "Add") {
            $GRN_Number = $generate_GRN['GRN_Number'];
            $GRN_Number_Financial_Id = $generate_GRN['GRN_Number_Financial_Id'];
            $inwardgrnid = DB::table('inwardgrn')->insertGetId(
                ['GRN' => $GRN_Number, "FinancialYearId" => $GRN_Number_Financial_Id, 'InwardDate' => $data['InwardDate'], 'Remark' => $data['Remark'], 'VendorId' => $VendorId, 'UserId' => $data['UserId'], 'CreatedDate' => date('Y-m-d H:i:s')]
            );
            $userName = Users::where('Id', $data['LoggedId'])->first();
            $inwardRec = DB::select("select concat($GRN_Number,'/', fn.StartYear,'-',fn.EndYear) as GRNnumber from inwardgrn igrn inner join financialyear fn on fn.Id=igrn.FinancialYearId where igrn.Id= '" . $inwardgrnid . "'");
            // return $inwardRec;
            UserLogs::create([
                'Module' => 'Inward',
                'ModuleNumberId' => $inwardgrnid,
                'LogType' => 'Created',
                'LogDescription' => $userName['Name'] . " " . 'created inward with GRN number' . " " . $inwardRec[0]->GRNnumber,
                'UserId' => $data['LoggedId'],
                'updated_at' => null
            ]);
            $artRecor = Article::where('Id', $ArticleId)->first();
            UserLogs::create([
                'Module' => 'Inward',
                'ModuleNumberId' => $inwardgrnid,
                'LogType' => 'Updated',
                'LogDescription' => $userName['Name'] . " " . 'added article ' . $artRecor->ArticleNumber . ' in inward with GRN number' . " " . $inwardRec[0]->GRNnumber,
                'UserId' => $data['LoggedId'],
                'updated_at' => null
            ]);
        } else {
            $inwardgrn = DB::select("select GRN from inwardgrn where Id = " . $data['GRN_Number']);
            $GRN_Number = $inwardgrn[0]->GRN;
            $inwardgrnid = $data['GRN_Number'];
            DB::table('inwardgrn')
                ->where('Id', $inwardgrnid)
                ->update(['InwardDate' => $data['InwardDate'], 'Remark' => $data['Remark'], 'VendorId' => $VendorId]);
            $userName = Users::where('Id', $data['LoggedId'])->first();
            $inwardRec = DB::select("select concat($GRN_Number,'/',fn.StartYear,'-',fn.EndYear) as GRNnumber from inwardgrn igrn inner join financialyear fn on fn.Id=igrn.FinancialYearId where igrn.Id= '" . $data['GRN_Number'] . "'");
            $artRecor = Article::where('Id', $ArticleId)->first();
            UserLogs::create([
                'Module' => 'Inward',
                'ModuleNumberId' => $inwardgrnid,
                'LogType' => 'Updated',
                'LogDescription' => $userName['Name'] . " " . 'added article ' . $artRecor->ArticleNumber . ' in inward with GRN number' . " " . $inwardRec[0]->GRNnumber,
                'UserId' => $data['LoggedId'],
                'updated_at' => null
            ]);
        }
        $inwardid = DB::table('inward')->insertGetId(
            ['ArticleId' => $ArticleId, 'rejections' => $rejections,   'NoPacks' => $NoPacks, 'SalesNoPacks' => $NoPacks, "InwardDate" => $data['InwardDate'], 'GRN' => $inwardgrnid, "Weight" => $data['Weight'], 'TotalSetQuantity' => $TotalSetQuantity, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
        );
        if ($ArticleOpenFlag == 1) {
            DB::select("SELECT count(*) as InwardArticleTotal, Id FROM `inwardarticle` where ArticleId ='" . $ArticleId . "' limit 0,1");
            $articledata = DB::select("select count(*) as Total, ArticleRate from articlerate where ArticleId = '" . $ArticleId . "'");
            if ($articledata[0]->Total > 0) {
                $ArticleRate = $articledata[0]->ArticleRate;
            } else {
                if (isset($data['Rate'])) {
                    $ArticleRate = $data['Rate'];
                    DB::table('articlerate')->insertGetId(
                        ['ArticleId' => $ArticleId, 'ArticleRate' => $ArticleRate, 'CreatedDate' => date("Y-m-d H:i:s")]
                    );
                }
            }
            DB::table('inwardarticle')->insertGetId(
                ['ArticleId' => $ArticleId, 'InwardId' => $inwardid, 'ArticleRate' => $ArticleRate, 'ArticleColor' => $ArticleColor, 'ArticleSize' => $ArticleSize, 'ArticleRatio' => $ArticleRatio, 'CreatedDate' => date("Y-m-d H:i:s")]
            );
            $mixnopacks = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='" . $ArticleId . "'");
            if ($mixnopacks[0]->total > 0) {
                $totalnopacks = ($TotalSetQuantity + $mixnopacks[0]->NoPacks);
                DB::table('mixnopacks')
                    ->where('Id', $mixnopacks[0]->Id)
                    ->update(['NoPacks' => $totalnopacks, 'UpdatedDate' => date("Y-m-d H:i:s")]);
            } else {
                DB::table('mixnopacks')->insertGetId(
                    ['ArticleId' => $ArticleId, 'NoPacks' => $NoPacks, 'CreatedDate' => date("Y-m-d H:i:s"), 'UpdatedDate' => date("Y-m-d H:i:s")]
                );
            }
        }
        return response()->json(array("GRN_Id" => $inwardgrnid, "GRN_Number" => $GRN_Number), 200);
    }

    private function GetGRNNumber()
    {
        $array = array();
        $fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
        $inwardgrndata = DB::select('SELECT GRN, FinancialYearId From inwardgrn order by Id desc limit 0,1');
        if (count($inwardgrndata) > 0) {
            if ($fin_yr[0]->Id > $inwardgrndata[0]->FinancialYearId) {
                $array["GRN_Number"] = 1;
                $array["GRN_Number_Financial_Id"] = $fin_yr[0]->Id;
                $array["GRN_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
                return $array;
            } else {
                $array["GRN_Number"] = ($inwardgrndata[0]->GRN) + 1;
                $array["GRN_Number_Financial_Id"] = $fin_yr[0]->Id;
                $array["GRN_Number_Financial"] = ($inwardgrndata[0]->GRN) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
                return $array;
            }
        } else {
            $array["GRN_Number"] = 1;
            $array["GRN_Number_Financial_Id"] = $fin_yr[0]->Id;
            $array["GRN_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
            return $array;
        }
    }

    public function GeInward()
    {
        return DB::select("select dd.* from (select VendorName(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as Name ,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, InwardSOCheck(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SOID, SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck,inw.GRN, concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number, igrn.InwardDate from `inward` inw left join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId group by GRN order by GRN asc) as dd where dd.SOID=0 order by GRN DESC");
    }
   
    
    
    
   
    
    public function PostInward(Request $request)
       {
           $data = $request->all();
           $search = $data["search"];
           $startnumber = $data["start"];
           $vnddataTotal = DB::table(DB::raw('(
            SELECT SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR \',\')) AS SODataCheck
            FROM `inward` inw
            INNER JOIN article a ON a.Id = inw.ArticleId
            WHERE a.ArticleOpenFlag = 0 
            GROUP BY inw.GRN
        ) AS dd'))
        ->select(DB::raw('COUNT(*) AS Total'))
        ->whereRaw('dd.SODataCheck = ?', ['0'])
        ->get();
    
           $vnTotal = $vnddataTotal[0]->Total;
           $length = $data["length"];
           if ($search['value'] != null && strlen($search['value']) > 2) {
               $searchstring = "where f.SODataCheck=0 and (f.GRN_Number like '%" . $search['value'] . "%' OR cast(f.InwardDate as char) like '%" . $search['value'] . "%' OR  f.Name like '%" . $search['value'] . "%' OR  f.TotalInwardPieces like '%" . $search['value'] . "%' OR f.Title like '%" . $search['value'] . "%' OR f.PurchaseNumber like '%" . $search['value'] . "%' OR f.ArticleNo like '%" . $search['value'] . "%')";
               $vnddataTotalFilter = DB::select("select count(*)
               as Total  from (select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, VendorName(a.Id) as Name, CountNoPacks(GROUP_CONCAT(DISTINCT CONCAT(inw.NoPacks) ORDER BY a.Id SEPARATOR ',')) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber, DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate, SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, c.Title FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId left join po p on p.ArticleId=a.Id left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=a.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId  where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) and s.Id IS NULL group by GRN) as f 
               
                " . $searchstring);
               $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
           } else {
               $searchstring = "where f.SODataCheck=0";
               $vnddataTotalFilterValue = $vnTotal;
           }
           $column = $data["order"][0]["column"];
           switch ($column) {
               case 1:
                   $ordercolumn = "f.GRN";
                   break;
               case 2:
                   $ordercolumn = "f.Name";
                   break;
               case 3:
                   $ordercolumn = "f.Title";
                   break;
               case 5:
                   $ordercolumn = "date(f.inwdate)";
                   break;
               case 6:
                   $ordercolumn = "f.PurchaseNumber";
                   break;
               default:
                   $ordercolumn = "f.GRN";
                   break;
           }
   
           $order = "";
           if ($data["order"][0]["dir"]) {
               $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
           }
           $vnddata =  DB::select("SELECT f.ArticleNo, f.Name, f.Notes, f.Cancellation, f.Id, f.GRN, f.GRN_Number, f.inwdate, f.InwardDate, f.SODataCheck, f.TotalInwardPieces, f.Title, f.PurchaseNumber
           FROM (
               SELECT 
                   GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, 
                   v.Name, 
                   inwc.Notes, 
                   'Cancellation' as Cancellation, 
                   igrn.Id, 
                   inwcl.GRN, 
                   CONCAT(igrn.GRN, '/', fn.StartYear,'-', fn.EndYear) as GRN_Number, 
                   igrn.InwardDate as inwdate, 
                   DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate, 
                   'SODataCheck' as SODataCheck, 
                   CountNoPacks(GROUP_CONCAT(DISTINCT CONCAT(inw.NoPacks) ORDER BY a.Id SEPARATOR ',')) as TotalInwardPieces, 
                   (CASE WHEN c.Title IS NULL THEN cc.Title ELSE c.Title END) as Title, 
                   (CASE WHEN pn.PurchaseNumber IS NULL THEN '0' ELSE CONCAT(pn.PurchaseNumber, '/', fy1.StartYear, '-', fy1.EndYear) END) as PurchaseNumber 
               FROM 
                   inwardgrn igrn 
                   INNER JOIN inwardcancellationlogs inwcl ON igrn.Id = inwcl.GRN 
                   INNER JOIN inwardcancellation inwc ON igrn.Id = inwc.GRN 
                   INNER JOIN financialyear fn ON fn.Id = igrn.FinancialYearId 
                   LEFT JOIN article a ON a.Id = inwcl.ArticleId 
                   LEFT JOIN po p ON p.ArticleId = a.Id 
                   LEFT JOIN vendor v ON v.Id = p.VendorId 
                   LEFT JOIN inward inw ON inw.Id = igrn.Id 
                   LEFT JOIN category c ON c.Id = p.CategoryId 
                   LEFT JOIN category cc ON cc.Id = a.CategoryId 
                   LEFT JOIN purchasenumber pn ON pn.Id = p.PO_Number 
                   LEFT JOIN financialyear fy1 ON fy1.Id = pn.FinancialYearId 
               GROUP BY inwc.GRN 
           
               UNION ALL 
           
               SELECT 
                   GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, 
                   v.Name, 
                   '', 
                   0 as Cancellation, 
                   igrn.Id, 
                   inw.GRN, 
                   CONCAT(igrn.GRN, '/', fn.StartYear,'-', fn.EndYear) as GRN_Number, 
                   igrn.InwardDate as inwdate, 
                   DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate, 
                   SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, 
                   CountNoPacks(GROUP_CONCAT(DISTINCT CONCAT(inw.NoPacks) ORDER BY a.Id SEPARATOR ',')) as TotalInwardPieces, 
                   (CASE WHEN c.Title IS NULL THEN cc.Title ELSE c.Title END) as Title, 
                   (CASE WHEN pn.PurchaseNumber IS NULL THEN '0' ELSE CONCAT(pn.PurchaseNumber, '/', fy1.StartYear, '-', fy1.EndYear) END) as PurchaseNumber 
               FROM 
                   inward inw 
                   INNER JOIN inwardgrn igrn ON igrn.Id = inw.GRN 
                   INNER JOIN financialyear fn ON fn.Id = igrn.FinancialYearId 
                   INNER JOIN article a ON a.Id = inw.ArticleId 
                   LEFT JOIN po p ON p.ArticleId = a.Id 
                   LEFT JOIN vendor v ON v.Id = p.VendorId 
                   LEFT JOIN so s ON s.ArticleId = inw.ArticleId 
                   LEFT JOIN category c ON c.Id = p.CategoryId 
                   LEFT JOIN category cc ON cc.Id = a.CategoryId 
                   LEFT JOIN purchasenumber pn ON pn.Id = p.PO_Number 
                   LEFT JOIN financialyear fy1 ON fy1.Id = pn.FinancialYearId 
               WHERE a.Id NOT IN (SELECT Id FROM `article` WHERE ArticleOpenFlag = 1) 
               GROUP BY inw.GRN
           ) as f
           " . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length);
           $totalNoPacks = 0;
           foreach ($vnddata as $vnd) {

           

               $grninwaards = DB::select("select NoPacks from inward where GRN=$vnd->GRN");
               foreach ($grninwaards as $grninwaard) {
                   $arrayGrninwaard = (array)$grninwaard;
                   if (strpos($arrayGrninwaard['NoPacks'], ',') !== false) {
                       $totalNoPacks += array_sum(explode(",", $arrayGrninwaard['NoPacks']));
                   } else {
                       $totalNoPacks += $arrayGrninwaard['NoPacks'];
                   }
                   $vnd->TotalNoPacks = $totalNoPacks;
               }
               $totalNoPacks = 0;
               
           }
           return array(
               'recordsTotal' => $vnTotal,
               'recordsFiltered' => $vnddataTotalFilterValue,
               'response' => 'success',
               'startnumber' => $startnumber,
               'data' => $vnddata,
           );
       }
   
    
    
    public function UpdateInward(Request $request)
    {

        $data = $request->all();
        $dataresult = DB::select('select inw.*, a.ArticleNumber, p.PO_Number, p.Id as POID, c.Colorflag from inward inw left join article a on a.Id=inw.ArticleId left join po p on p.ArticleId=inw.ArticleId left join category c on c.Id = a.CategoryId where inw.Id="' . $data['id'] . '"');
        $Colorflag = $dataresult[0]->Colorflag;
        $ArticleId = $dataresult[0]->ArticleId;
        $ArticleRatio = $data['RatioId'];
        $NoPacks = "";
        if ($Colorflag == 1) {
            foreach ($data['ColorId'] as $vl) {
                $numberofpacks = $vl["Id"];
                if (isset($data["NoPacks_" . $numberofpacks])) {
                    $NoPacks .= $data["NoPacks_" . $numberofpacks] . ",";
                } else {
                    $NoPacks .= "0,";
                }
            }
        } else {
            $NoPacks .= $data['NoPacks'];
        }
        $NoPacks = rtrim($NoPacks, ',');
        $countcolor = count($data['ColorId']);
        $countration = array_sum(explode(",", $data['RatioId']));
        $countNoPacks = array_sum(explode(",", $NoPacks));
        $RatioId = explode(",", $data['RatioId']);
        $rejections = [];
        if (isset($data['RejectionId'])) {
            foreach ($data['RejectionId'] as $rejection) {
                if ($data["Rej_" . $rejection['Id']] != 0) {
                    array_push($rejections, ['Id' => $rejection['Id'], 'RejectionType' =>  $rejection['RejectionType'], 'RejectionPacks' => $data["Rej_" . $rejection['Id']]]);
                }
            }
            if (count($rejections) == 0) {
                $rejections = null;
            } else {
                $rejections = json_encode($rejections);
            }
        } else {
            $rejections = null;
        }
        if ($Colorflag == 1) {
            $TotalSetQuantity = ($countNoPacks * $countration);
        } else {
            $TotalSetQuantity = ($countNoPacks * ($countration * $countcolor));
        }
        if ($data['VendorId'] == null || $data['VendorId'] == 0) {
            $VendorId = 0;
        } else {
            $VendorId = $data['VendorId'];
        }
        if ($data["ArticleStatus"] == true) {
            $ArticleStatus = 1;
        } else {
            $ArticleStatus = 0;
        }
        $ActiveInward = DB::select("select ig.VendorId, a.ArticleRatio, a.ArticleRate, i.Weight, i.NoPacks from inward i inner join inwardgrn ig on ig.Id=i.GRN left join article a on a.Id=i.ArticleId where i.Id = '" . $data['id'] . "'");
        $logDesc = "";
        if ($ActiveInward[0]->VendorId  != (int)$data['VendorId']) {
            $logDesc = $logDesc . 'Vendor,';
        }
        if ($ActiveInward[0]->ArticleRatio  != $data['RatioId']) {
            $logDesc = $logDesc . 'Ratio,';
        }
        if ($ActiveInward[0]->ArticleRate != $data['Rate']) {
            $logDesc = $logDesc . 'Rate,';
        }
        if ($ActiveInward[0]->Weight != $data['Weight']) {
            $logDesc = $logDesc . 'Weight,';
        }
        if ($ActiveInward[0]->NoPacks != $NoPacks) {
            $logDesc = $logDesc . 'Quantity,';
        }
        $newLogDesc = rtrim($logDesc, ',');
        DB::beginTransaction();
        try {
            DB::table('article')
                ->where('Id', $ArticleId)
                ->update(['ArticleRate' => $data['Rate'], 'ArticleRatio' => $ArticleRatio, 'ArticleStatus' => $ArticleStatus, 'UpdatedDate' => date("Y-m-d H:i:s")]);

            DB::table('articlerate')
                ->where('ArticleId', $ArticleId)
                ->update(['ArticleRate' => $data['Rate'], 'UpdatedDate' => date("Y-m-d H:i:s")]);
            foreach ($data['SizeId'] as $key => $vl) {
                DB::table('articleratio')
                    ->where('ArticleSizeId', $vl["Id"])
                    ->where('ArticleId', $ArticleId)
                    ->update(['ArticleRatio' => $RatioId[$key]]);
            }
            DB::table('inwardgrn')
                ->where('GRN', $data['GRN_Number'])
                ->update(['InwardDate' => $data['InwardDate'], 'Remark' => $data['Remark'], 'VendorId' => $VendorId]);
            Inward::where('id', $data['id'])->update(array(
                'NoPacks' =>  $NoPacks,
                'SalesNoPacks' =>  $NoPacks,
                'InwardDate' =>  $data['InwardDate'],
                'TotalSetQuantity' => $TotalSetQuantity,
                'rejections' => $rejections,
                'Weight' =>  $data['Weight']
            ));
            DB::commit();
            $userName = Users::where('Id', $data['LoggedId'])->first();
            $grnNumber = $data['GRN_Number'];
            $inwardRec = DB::select("select igrn.Id as grnId, concat($grnNumber,'/',fn.StartYear,'-',fn.EndYear) as GRNnumber from inwardgrn igrn inner join financialyear fn on fn.Id=igrn.FinancialYearId where igrn.Id= '" . $data['GRN_Number'] . "'");
            $artRecor = Article::where('Id', $ArticleId)->first();
            UserLogs::create([
                'Module' => 'Inward',
                'ModuleNumberId' => $inwardRec[0]->grnId,
                'LogType' => 'Updated',
                'LogDescription' => $userName['Name'] . " " . 'updated article ' . $artRecor->ArticleNumber . ' with ' . $newLogDesc . ' in inward GRN number' . " " . $inwardRec[0]->GRNnumber,
                'UserId' => $data['LoggedId'],
                'updated_at' => null
            ]);
            return response()->json("SUCCESS", 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json("Error", 200);
        }
    }
    public function Deleteinward($id, $ArticleId, $LoggedId)
    {
        DB::table('article')
            ->where('Id', $ArticleId)
            ->update(['ArticleRate' => '', 'ArticleColor' => '', 'ArticleSize' => '', 'ArticleRatio' => '', 'UpdatedDate' => date("Y-m-d H:i:s")]);
        DB::table('inwardarticle')->where('InwardId', '=', $id)->delete();
        DB::table('articlecolor')->where('ArticleId', '=', $ArticleId)->delete();
        DB::table('articlesize')->where('ArticleId', '=', $ArticleId)->delete();
        DB::table('articleratio')->where('ArticleId', '=', $ArticleId)->delete();
        $articleRecord =  Article::where('Id', $ArticleId)->first();
        if ($articleRecord->ArticleOpenFlag == 0) {
            DB::table('articlerate')->where('ArticleId', '=', $ArticleId)->delete();
        }
        $articleopenflag = DB::select("SELECT inw.TotalSetQuantity, a.ArticleOpenFlag FROM `inward` inw inner join article a on a.Id=inw.ArticleId where inw.Id='" . $id . "'");
        if ($articleopenflag[0]->ArticleOpenFlag == 1) {
            $mixnopacks = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='" . $ArticleId . "'");
            if ($mixnopacks[0]->total > 0) {
                $totalnopacks = ($mixnopacks[0]->NoPacks - $articleopenflag[0]->TotalSetQuantity);
                DB::table('mixnopacks')
                    ->where('Id', $mixnopacks[0]->Id)
                    ->update(['NoPacks' => $totalnopacks, 'UpdatedDate' => date("Y-m-d H:i:s")]);
            }
        }
        $userName = Users::where('Id', $LoggedId)->first();
        $inwardRec = DB::select("select ig.Id as GrnId, a.ArticleNumber,concat(ig.GRN,'/', fn.StartYear,'-',fn.EndYear) as GRNnumber from inward i inner join inwardgrn ig on ig.Id=i.GRN inner join article a on a.Id=i.ArticleId inner join financialyear fn on fn.Id=ig.FinancialYearId where i.Id= '" . $id . "'");
        UserLogs::create([
            'Module' => 'Inward',
            'ModuleNumberId' => $inwardRec[0]->GrnId,
            'LogType' => 'Deleted',
            'LogDescription' => $userName['Name'] . " deleted article " . $inwardRec[0]->ArticleNumber . " from Inward GRN Number " . $inwardRec[0]->GRNnumber,
            'UserId' => $userName->Id,
            'updated_at' => null
        ]);
        DB::table('inward')->where('Id', '=', $id)->delete();
        return response()->json("SUCCESS", 200);
    }

    public function DeleteinwardGRN($GRN, $LoggedId)
    {
        $inwarddeletecheck = DB::select("SELECT ar.ArticleOpenFlag, (case ar.ArticleOpenFlag when '1' then IF(CONVERT((select mr.NoPacks from mixnopacks mr where mr.ArticleId=inw.ArticleId),UNSIGNED)>=CONVERT(inw.NoPacks,UNSIGNED),'true','false') else 0 END) MixDeleteStatus, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId left JOIN po p on p.ArticleId = ar.Id inner Join category c on c.Id=ar.CategoryId left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId left join articlecolor ac on ac.ArticleId=inw.ArticleId where inw.GRN='" . $GRN . "' group by inw.Id");
        foreach ($inwarddeletecheck as $data) {
            if ($data->ArticleOpenFlag == 1) {
                if ($data->MixDeleteStatus == "false") {
                    return response()->json("AlreadyMixAssign", 200);
                }
                continue;
            } else {
                if ($data->SOID == '1') {
                    return response()->json("AlreadyArticleAssign", 200);
                }
                continue;
            }
        }
        $inwardlist = DB::select("SELECT Id, ArticleId FROM `inward` where GRN ='" . $GRN . "'");
        foreach ($inwardlist as $vl) {
            $id = $vl->Id;
            $ArticleId = $vl->ArticleId;
            DB::table('articlerate')
                ->where('ArticleId', $ArticleId)
                ->update(['ArticleRate' => '']);

            DB::table('article')
                ->where('Id', $ArticleId)
                ->update(['ArticleRate' => '', 'ArticleColor' => '', 'ArticleSize' => '', 'ArticleRatio' => '', 'ArticleStatus' => 0, 'UpdatedDate' => date("Y-m-d H:i:s")]);
            DB::table('articlecolor')->where('ArticleId', '=', $ArticleId)->delete();
            DB::table('articlesize')->where('ArticleId', '=', $ArticleId)->delete();
            DB::table('articleratio')->where('ArticleId', '=', $ArticleId)->delete();
            $articleRecord =  Article::where('Id', $ArticleId)->first();
            if ($articleRecord->ArticleOpenFlag == 0) {
                DB::table('articlerate')->where('ArticleId', '=', $ArticleId)->delete();
            }
            $articleopenflag = DB::select("SELECT inw.TotalSetQuantity, a.ArticleOpenFlag FROM `inward` inw inner join article a on a.Id=inw.ArticleId where inw.Id='" . $id . "'");
            if ($articleopenflag[0]->ArticleOpenFlag == 1) {
                $mixnopacks = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='" . $ArticleId . "'");
                if ($mixnopacks[0]->total > 0) {
                    $totalnopacks = ($mixnopacks[0]->NoPacks - $articleopenflag[0]->TotalSetQuantity);
                    DB::table('mixnopacks')
                        ->where('Id', $mixnopacks[0]->Id)
                        ->update(['NoPacks' => $totalnopacks, 'UpdatedDate' => date("Y-m-d H:i:s")]);
                }
            }
            DB::table('inward')->where('Id', '=', $id)->delete();
            DB::table('inwardarticle')->where('InwardId', '=', $id)->delete();
        }
        $inwardRecord = DB::table('inwardgrn')->where('Id', '=', $GRN)->first();
        $userName = Users::where('Id', $LoggedId)->first();
        $inwardRec = DB::select("select concat($inwardRecord->GRN,'/', fn.StartYear,'-',fn.EndYear) as GRNnumber from inwardgrn igrn inner join financialyear fn on fn.Id=igrn.FinancialYearId where igrn.Id= '" . $GRN . "'");
        UserLogs::create([
            'Module' => 'Inward',
            'ModuleNumberId' => $inwardRecord->Id,
            'LogType' => 'Deleted',
            'LogDescription' => $userName['Name'] . " " . 'deleted inward GRN with GRN number' . " " . $inwardRec[0]->GRNnumber,
            'UserId' => $userName['Id'],
            'updated_at' => null
        ]);

        return response()->json("SUCCESS", 200);
    }
    public function Cancellationinwardgrn(Request $request)
    {
        $data = $request->all();
        $GRN = $data["GRN"];
        $Notes = $data["Notes"];
        DB::table('inwardcancellation')->insertGetId(
            ['GRN' => $GRN, 'Notes' => $Notes, 'CreatedDate' => date("Y-m-d H:i:s"), 'UpdatedDate' => date("Y-m-d H:i:s")]
        );
        $inwarddeletecheck = DB::select("SELECT ar.ArticleOpenFlag, (case ar.ArticleOpenFlag when '1' then IF(CONVERT((select mr.NoPacks from mixnopacks mr where mr.ArticleId=inw.ArticleId),UNSIGNED)>=CONVERT(inw.NoPacks,UNSIGNED),'true','false') else 0 END) MixDeleteStatus, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId left JOIN po p on p.ArticleId = ar.Id inner Join category c on c.Id=ar.CategoryId left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId left join articlecolor ac on ac.ArticleId=inw.ArticleId where inw.GRN='" . $GRN . "' group by inw.Id");
        foreach ($inwarddeletecheck as $data) {
            if ($data->ArticleOpenFlag == 1) {
                if ($data->MixDeleteStatus == "false") {
                    return response()->json("AlreadyMixAssign", 200);
                }
                continue;
            } else {
                if ($data->SOID == '1') {
                    return response()->json("AlreadyArticleAssign", 200);
                }
                continue;
            }
        }
        $inwardlist = DB::select("SELECT Id, ArticleId, NoPacks, InwardDate, GRN, Weight, created_at, updated_at FROM `inward` where GRN ='" . $GRN . "'");
        foreach ($inwardlist as $vl) {
            $id = $vl->Id;
            $ArticleId = $vl->ArticleId;
            $articleopenflag = DB::select("SELECT inw.TotalSetQuantity, a.ArticleOpenFlag FROM `inward` inw inner join article a on a.Id=inw.ArticleId where inw.Id='" . $id . "'");
            if ($articleopenflag[0]->ArticleOpenFlag == 1) {
                $mixnopacks = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='" . $ArticleId . "'");
                if ($mixnopacks[0]->total > 0) {
                    $totalnopacks = ($mixnopacks[0]->NoPacks - $articleopenflag[0]->TotalSetQuantity);
                    DB::table('mixnopacks')
                        ->where('Id', $mixnopacks[0]->Id)
                        ->update(['NoPacks' => $totalnopacks, 'UpdatedDate' => date("Y-m-d H:i:s")]);
                }
            }
            $NoPacks = $vl->NoPacks;
            $InwardDate = $vl->InwardDate;
            $GRN = $vl->GRN;
            $Weight = $vl->Weight;
            $created_at = $vl->created_at;
            $updated_at = $vl->updated_at;
            DB::table('inwardcancellationlogs')->insertGetId(
                ['InwardId' => $id, 'ArticleId' => $ArticleId, 'NoPacks' => $NoPacks, 'InwardDate' => $InwardDate, 'GRN' => $GRN, 'Weight' => $Weight, 'created_at' => $created_at, 'updated_at' => $updated_at]
            );

            DB::table('inward')->where('Id', '=', $id)->delete();
        }

        return response()->json("SUCCESS", 200);
    }

    public function GetInwardIdWise($id)
    {
        $getArticle = DB::select('SELECT inw.ArticleId,a.ArticleOpenFlag  FROM `inward` inw inner join article a on a.Id=inw.ArticleId where inw.Id= "' . $id . '"');
        if ($getArticle[0]->ArticleOpenFlag == 0) {
            return DB::select("SELECT inw.Id,inw.rejections ,ar.ArticleStatus, ingrn.GRN as GRN_Number, concat(ingrn.GRN, '/', fn1.StartYear,'-',fn1.EndYear) as GRN_Number_FinancialYear, inw.ArticleId,p.PO_Number,p.VendorId as VID,purn.PurchaseNumber, concat(purn.PurchaseNumber, '/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber_FinancialYear, (case when p.CategoryId IS NULL then ar.CategoryId else p.CategoryId end) as CategoryId,(case when p.SubCategoryId IS NULL then ar.SubCategoryId else p.SubCategoryId end) as SubCategoryId , subc.Name as Subcategorytitle,rs.SeriesName , cc.Colorflag, cc.Title, b.Name as BrandName, inw.GRN,ar.ArticleNumber, ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,ar.StyleDescription,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId inner join inwardgrn ingrn on ingrn.Id=inw.GRN LEFT JOIN po p on p.ArticleId = ar.Id LEFT join purchasenumber purn on purn.Id=p.PO_Number LEFT join financialyear fn on fn.Id=purn.FinancialYearId inner join financialyear fn1 on fn1.Id=ingrn.FinancialYearId LEFT Join category c on c.Id=p.CategoryId LEFT Join category cc on cc.Id=ar.CategoryId LEFT Join subcategory subc on subc.Id=ar.SubCategoryId   LEFT join rangeseries  rs on rs.Id=ar.SeriesId  LEFT join brand b on b.Id=ar.BrandId where inw.Id=" . $id);
        } else {
            return DB::select("SELECT inw.Id, inw.rejections , a.ArticleStatus, ingrn.GRN as GRN_Number, inw.ArticleId, p.PO_Number,p.VendorId as VID,purn.PurchaseNumber, concat(purn.PurchaseNumber, '/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber_FinancialYear, a.CategoryId, a.SubCategoryId, subc.Name as Subcategorytitle, rs.SeriesName ,   cc.Colorflag, cc.Title, b.Name as BrandName, a.ArticleNumber, inw.GRN,ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,a.StyleDescription,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN inwardarticle ar on ar.InwardId = inw.Id inner join inwardgrn ingrn on ingrn.Id=inw.GRN LEFT JOIN po p on p.ArticleId = ar.ArticleId left join purchasenumber purn on purn.Id=p.PO_Number inner join article a on a.Id=inw.ArticleId LEFT Join category c on c.Id=p.CategoryId LEFT Join category cc on cc.Id=a.CategoryId LEFT Join subcategory subc on subc.Id=a.SubCategoryId  LEFT join rangeseries  rs on rs.Id=ar.SeriesId   left join brand b on b.Id=a.BrandId left join financialyear fn on fn.Id=ingrn.FinancialYearId where inw.Id=" . $id);
        }
    }





    public function InwardListFromGRN($id, Request $request)
    {
           $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select("select count(*) as Total from ( SELECT (case ar.ArticleOpenFlag when '1' then IF(CONVERT((select mr.NoPacks from mixnopacks mr where mr.ArticleId=inw.ArticleId), UNSIGNED)>=CONVERT(inw.NoPacks, UNSIGNED),'true','false') else 0 END) MixDeleteStatus,inw.Id,inw.ArticleId,v.Name,count(ac.ArticleId) as ColorCount,cc.Colorflag, cc.Title, inw.GRN,ar.ArticleNumber, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,ar.ArticleOpenFlag,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId left JOIN po p on p.ArticleId = ar.Id left Join category c on c.Id=p.CategoryId left Join category cc on cc.Id=ar.CategoryId left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId left join articlecolor ac on ac.ArticleId=inw.ArticleId where inw.GRN='" . $id . "' group by inw.Id ) as d ");
        $vnTotal = $vnddataTotal[0]->Total;
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "where d.NoPacks like '%" . $search['value'] . "%' OR d.Name like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%' OR d.Title like '%" . $search['value'] . "%' ";
            $vnddataTotalFilter = DB::select("select count(*) as Total from ( SELECT (case ar.ArticleOpenFlag when '1' then IF(CONVERT((select mr.NoPacks from mixnopacks mr where mr.ArticleId=inw.ArticleId), UNSIGNED)>=CONVERT(inw.NoPacks, UNSIGNED),'true','false') else 0 END) MixDeleteStatus,inw.Id,inw.ArticleId,v.Name,count(ac.ArticleId) as ColorCount,cc.Colorflag, cc.Title, inw.GRN,ar.ArticleNumber, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,ar.ArticleOpenFlag,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId left JOIN po p on p.ArticleId = ar.Id left Join category c on c.Id=p.CategoryId left Join category cc on cc.Id=ar.CategoryId left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId left join articlecolor ac on ac.ArticleId=inw.ArticleId where inw.GRN='" . $id . "' group by inw.Id ) as d "  . $searchstring );
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
                $ordercolumn = "d.Name";
                break;
            case 3:
                $ordercolumn = "d.Title";
                break;
            case 4:
            $ordercolumn = "d.NoPacks";
            break;
            default:
                $ordercolumn = "d.CreatedDate";
                break;
        }
        $order = "";
        if ($data["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
        }
        $vnddata = DB::select("select d.* from (SELECT (case ar.ArticleOpenFlag when '1' then IF(CONVERT((select mr.NoPacks from mixnopacks mr where mr.ArticleId=inw.ArticleId), UNSIGNED)>=CONVERT(inw.NoPacks, UNSIGNED),'true','false') else 0 END) MixDeleteStatus,inw.Id,inw.ArticleId,v.Name,count(ac.ArticleId) as ColorCount,cc.Colorflag, cc.Title, inw.GRN,ar.ArticleNumber, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,ar.ArticleOpenFlag,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId left JOIN po p on p.ArticleId = ar.Id left Join category c on c.Id=p.CategoryId left Join category cc on cc.Id=ar.CategoryId left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId left join articlecolor ac on ac.ArticleId=inw.ArticleId where inw.GRN='" . $id . "' group by inw.Id) as d " . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length);
    
           foreach ($vnddata as $key => $val) {
                $object = (object)$val;
                $object->ArticleColor = json_decode($val->ArticleColor, false);
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
    
        
        
        
        
        
        // $as = DB::select("SELECT (case ar.ArticleOpenFlag when '1' then IF(CONVERT((select mr.NoPacks from mixnopacks mr where mr.ArticleId=inw.ArticleId), UNSIGNED)>=CONVERT(inw.NoPacks, UNSIGNED),'true','false') else 0 END) MixDeleteStatus,inw.Id,inw.ArticleId,v.Name,count(ac.ArticleId) as ColorCount,cc.Colorflag, cc.Title, inw.GRN,ar.ArticleNumber, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,ar.ArticleOpenFlag,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId left JOIN po p on p.ArticleId = ar.Id left Join category c on c.Id=p.CategoryId left Join category cc on cc.Id=ar.CategoryId left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId left join articlecolor ac on ac.ArticleId=inw.ArticleId where inw.GRN='" . $id . "' group by inw.Id");
        // foreach ($as as $key => $val) {
        //     $object = (object)$val;
        //     $object->ArticleColor = json_decode($val->ArticleColor, false);
        // }

        // return $as;
    }

    // public function InwardListFromGRN($id)
    // {
    //     $as = DB::select("SELECT (case ar.ArticleOpenFlag when '1' then IF(CONVERT((select mr.NoPacks from mixnopacks mr where mr.ArticleId=inw.ArticleId), UNSIGNED)>=CONVERT(inw.NoPacks, UNSIGNED),'true','false') else 0 END) MixDeleteStatus,inw.Id,inw.ArticleId,v.Name,count(ac.ArticleId) as ColorCount,cc.Colorflag, cc.Title, inw.GRN,ar.ArticleNumber, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,ar.ArticleOpenFlag,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId left JOIN po p on p.ArticleId = ar.Id left Join category c on c.Id=p.CategoryId left Join category cc on cc.Id=ar.CategoryId left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId left join articlecolor ac on ac.ArticleId=inw.ArticleId where inw.GRN='" . $id . "' group by inw.Id");
    //     foreach ($as as $key => $val) {
    //         $object = (object)$val;
    //         $object->ArticleColor = json_decode($val->ArticleColor, false);
    //     }

    //     return $as;
    // }

    public function InwardDateRemarkFromGRN($id)
    {
        //added by yashvi
        return DB::select("SELECT v.Name AS NAME, ig.*, concat(ig.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number_FinancialYear FROM `inwardgrn` ig inner join financialyear fn on fn.Id=ig.FinancialYearId LEFT JOIN vendor v ON ig.VendorId = v.Id where ig.Id = '" . $id . "'");
        // return DB::select("SELECT ig.*, concat(ig.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number_FinancialYear FROM `inwardgrn` ig inner join financialyear fn on fn.Id=ig.FinancialYearId where ig.Id = '" . $id . "'");
    }

    public function getinwardgrn()
    {
        $array = array();
        $inwardGRNdata = DB::select('SELECT Id, GRN, FinancialYearId From inwardgrn order by Id desc limit 0,1');
        $fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
        if (count($inwardGRNdata) > 0) {
            if ($fin_yr[0]->Id > $inwardGRNdata[0]->FinancialYearId) {
                $array["GRN"] = 1;
                $array["Id"] = $inwardGRNdata[0]->Id + 1;
                $array["GRN_Financial"] =  1 . "/" . $fin_yr[0]->CurrentFinancialYear;
                return response()->json($array, 200);
            } else {
                $array["GRN"] = ($inwardGRNdata[0]->GRN) + 1;
                $array["Id"] = $inwardGRNdata[0]->Id + 1;
                $array["GRN_Financial"] = ($inwardGRNdata[0]->Id) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
                return response()->json($array, 200);
            }
        } else {
            $array["GRN"] = 1;
            $array["Id"] = 1;
            $array["GRN_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
            return response()->json($array, 200);
        }
    }

    public function GetEditArticalIdWise($id)
    {
        return DB::select("SELECT p.NumPacks,ar.ArticleNumber,cc.Colorflag, v.Name FROM `article` ar left JOIN po p on ar.Id = p.ArticleId left JOIN category c on c.Id=p.CategoryId left JOIN category cc on cc.Id=ar.CategoryId left join vendor v on v.Id=p.VendorId WHERE ar.Id=" . $id);
    }

    public function GetInwardChallen($id, $type)
    {
        if ($type == 0) {
            $getinwardchallen = DB::select("SELECT inward.rejections , inwc.Notes , u.Name as PreparedBy , ingrn.VendorId, concat(ingrn.GRN,'/' ,fy.StartYear,'-',fy.EndYear) as GRN, concat(pur.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber,p.PoDate,inw.Id, inwart.ArticleRate as mixrate, inwart.ArticleColor as mixcolor, inwart.ArticleSize as mixsize, inwart.ArticleRatio as mixratio, ingrn.InwardDate,brn.Name as BrandName, ingrn.Remark, v.Name, v.Address, v.GSTNumber, art.ArticleNumber, c.Title, inw.NoPacks, c.ArticleOpenFlag, c.Colorflag, art.ArticleColor, art.ArticleSize, art.ArticleRate, art.ArticleRatio, CountNoPacks(art.ArticleRatio) as TotalArticleRatio, art.StyleDescription, inw.Weight FROM inwardcancellationlogs inw inner join inwardgrn ingrn on inw.GRN=ingrn.Id left join inward on inward.Id=inw.InwardId inner join inwardcancellation inwc on inwc.GRN=inw.GRN inner join article art on inw.ArticleId=art.Id left join po p on p.ArticleId=art.Id left join purchasenumber pur on pur.Id=p.PO_Number left join vendor v on v.Id=p.VendorId inner join category c on c.Id=art.CategoryId left join inwardarticle inwart on inwart.InwardId=inw.InwardId left join brand brn on brn.Id=art.BrandId inner join financialyear fy on fy.Id=ingrn.FinancialYearId left join financialyear fy1 on fy1.Id=pur.FinancialYearId left join users u on u.Id=ingrn.UserId where inw.GRN ='" . $id . "' group by inw.Id order by inw.Id asc");
        } else {
            $getinwardchallen = DB::select("SELECT inw.rejections , 'Notes', u.Name as PreparedBy, ingrn.VendorId, concat(ingrn.GRN,'/' ,fy.StartYear,'-',fy.EndYear) as GRN, concat(pur.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber,p.PoDate,inw.Id, inwart.ArticleRate as mixrate, inwart.ArticleColor as mixcolor, inwart.ArticleSize as mixsize, inwart.ArticleRatio as mixratio, ingrn.InwardDate,brn.Name as BrandName, ingrn.Remark, v.Name, v.Address, v.GSTNumber, art.ArticleNumber, c.Title, inw.NoPacks, c.ArticleOpenFlag, c.Colorflag, art.ArticleColor, art.ArticleSize, art.ArticleRate, art.ArticleRatio, CountNoPacks(art.ArticleRatio) as TotalArticleRatio, art.StyleDescription, inw.Weight FROM inward inw inner join inwardgrn ingrn on inw.GRN=ingrn.Id inner join article art on inw.ArticleId=art.Id left join po p on p.ArticleId=art.Id left join purchasenumber pur on pur.Id=p.PO_Number left join vendor v on v.Id=p.VendorId inner join category c on c.Id=art.CategoryId left join inwardarticle inwart on inwart.InwardId=inw.Id left join brand brn on brn.Id=art.BrandId inner join financialyear fy on fy.Id=ingrn.FinancialYearId left join financialyear fy1 on fy1.Id=pur.FinancialYearId left join users u on u.Id=ingrn.UserId where inw.GRN ='" . $id . "' group by inw.Id order by inw.Id asc");
        }
        if ($getinwardchallen[0]->VendorId != 0) {
            $getdata = DB::select("select * from vendor where Id='" . $getinwardchallen[0]->VendorId . "'");
            $vendorInformation = true;
            $vendorName = $getdata[0]->Name;
            $vendorAddress = $getdata[0]->Address;
            $vendorGSTNumber = $getdata[0]->GSTNumber;
        } else {
            $vendorInformation = false;
            $vendorName = "";
            $vendorAddress = "";
            $vendorGSTNumber = "";
        }
        $challendata = [];
        $countNoPacks = 0;
        $countRate = 0;
        $countWight = 0;
        foreach ($getinwardchallen as $vl) {
            $ArticleOpenFlag = $vl->ArticleOpenFlag;
            $Colorflag = $vl->Colorflag;
            $InwardDate = $vl->InwardDate;
            $GRN = $vl->GRN;
            $rejections = $vl->rejections;
            $Remark = $vl->Remark;
            $Name = $vl->Name;
            $Address = $vl->Address;
            $GSTNumber = $vl->GSTNumber;
            $ArticleNumber = $vl->ArticleNumber;
            $Title = $vl->Title;
            $StyleDescription = $vl->StyleDescription;
            $NoPacks = $vl->NoPacks;
            $PreparedBy = $vl->PreparedBy;
            $Notes = $vl->Notes;
            $Weight = $vl->Weight;
            $PurchaseNumber = $vl->PurchaseNumber;
            $PoDate = $vl->PoDate;
            $BrandName = $vl->BrandName;
            $TotalArticleRatio = $vl->TotalArticleRatio;
            $ArticleRatio = $vl->ArticleRatio;
            $countWight += $Weight;
            if (strpos($NoPacks, ',') !== false) {
                $singlecountNoPacks = array_sum(explode(",", $NoPacks));
                $countNoPacks += array_sum(explode(",", $NoPacks));
                $string = explode(',', $NoPacks);
                $stringcomma = 1;
            } else {
                $singlecountNoPacks = $NoPacks;
                $countNoPacks += $NoPacks;
                $string = $NoPacks;
                $stringcomma = 0;
            }
            if ($ArticleOpenFlag == 0) {
                $ArticleRate = $vl->ArticleRate;
                $countRate = $ArticleRate++;
                if($vl->ArticleColor == null){
                    $vl->ArticleColor = '[]';
                }
                if($vl->ArticleSize == null){
                    $vl->ArticleSize = '[]';
                }
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
                if ($Colorflag == 0) {
                    $getcolorcount = count($getcolor);
                    $Tempnumber = ($singlecountNoPacks / ($getcolorcount * $TotalArticleRatio));
                    if ((int)$Tempnumber != $Tempnumber) {
                        $TempSet = floor($Tempnumber);
                        $mult = ($TotalArticleRatio * $getcolorcount);
                        $div = ($singlecountNoPacks / ($mult));

                        $TotalValue = $singlecountNoPacks - (floor($div) * $mult);
                        $Totalstockvalue = $TempSet . ":" . $TotalValue;
                    } else {
                        $Totalstockvalue = $Tempnumber;
                    }
                } else {
                    $Totalstockvalue = "";
                    if ($stringcomma == 1) {
                        foreach ($string as $value) {
                            if($TotalArticleRatio == 0){
                                $TotalArticleRatio = 1;
                            }
                            $Tempnumber = ($value / $TotalArticleRatio);

                            if ((int)$Tempnumber != $Tempnumber) {
                                $TempSet = floor($Tempnumber);
                                $Totaltmp = $TempSet * $TotalArticleRatio;
                                $TotalValue = $value - $Totaltmp;
                                $Totalstockvalue .= $TempSet . ":" . $TotalValue . ",";
                            } else {
                                $Totalstockvalue .= $Tempnumber . ",";
                            }
                        }
                        $Totalstockvalue = rtrim($Totalstockvalue, ',');
                    } else {
                        $Tempnumber = ($singlecountNoPacks / $TotalArticleRatio);
                        if ((int)$Tempnumber != $Tempnumber) {
                            $TempSet = floor($Tempnumber);
                            $Totaltmp = $TempSet * $TotalArticleRatio;
                            $TotalValue = $singlecountNoPacks - $Totaltmp;
                            $Totalstockvalue = $TempSet . ":" . $TotalValue;
                        } else {
                            $Totalstockvalue = $Tempnumber;
                        }
                    }
                }
            } else {
                $ArticleRate = $vl->mixrate;
                $ArticleRatio = $vl->mixratio;
                if (strpos($vl->mixratio, ',') !== false) {
                    $ArticlemixRatio = array_sum(explode(",", $ArticleRatio));
                } else {
                    $ArticlemixRatio = $ArticleRatio;
                }
                $countRate = $ArticleRate++;
                $mixcolor = json_decode($vl->mixcolor);
                $mixsize = json_decode($vl->mixsize);
                $ArticleColor = "";
                if ($mixcolor) {
                    foreach ($mixcolor as $vl) {
                        $ArticleColor .= $vl->Name . ",";
                    }
                    $ArticleColor = rtrim($ArticleColor, ',');
                } else {
                    $ArticleColor = "";
                }
                $ArticleSize = "";
                if ($mixsize) {
                    foreach ($mixsize as $vl) {
                        $ArticleSize .= $vl->Name . ",";
                    }
                    $ArticleSize = rtrim($ArticleSize, ',');
                } else {
                    $ArticleSize = "";
                }

                $Totalstockvalue = $NoPacks * $ArticlemixRatio;
            }
            $numbers_array = explode(",", $NoPacks);
            $sum = array_sum($numbers_array);
            $TotalQty = $sum;

            $numbers_colorQty = explode(",", $ArticleColor);

            $numbers_packQty = explode(",", $NoPacks);
            // return $numbers_colorQty;
            if($numbers_colorQty == [""]){
                $numbers_colorQty = $numbers_packQty;
            }
            $C=array_combine($numbers_colorQty,$numbers_packQty);

            $jsonData = json_encode($C);

            $arrayData = json_decode($jsonData, true);
            $TotalCQty="";
            $allkey="";
            $allvalue="";
            foreach ($arrayData as $key => $value) { 
                if (!empty($key)) {
                    $TotalCQty .= '<b>' . $key . " : " . '</b>' . $value . ", ";
                } else {
                    $TotalCQty .= '<b>' . '--' . " : " . '</b>'. $value. ', ';
                }
            }


            $challendata[] = json_decode(json_encode(array("ColorWiseQty"  => $TotalCQty,"Notes" => $Notes,"TotalQty" => $TotalQty , "rejections" => $rejections,  "PreparedBy" => $PreparedBy, "InwardDate" => $InwardDate, "PoDate" => $PoDate, "GRN" => $GRN, "PurchaseNumber" => $PurchaseNumber, "BrandName" => $BrandName, "Remark" => $Remark, "Name" => $Name, "Address" => $Address, "GSTNumber" => $GSTNumber, "ArticleNumber" => $ArticleNumber, "Title" => $Title, "StyleDescription" => $StyleDescription, "TotalSetQuantity" => $Totalstockvalue, "NoPacks" => $NoPacks, "ArticleRate" => number_format($ArticleRate, 2), "Weight" => number_format($Weight, 2), "ArticleColor" => $ArticleColor, "ArticleSize" => $ArticleSize, "ArticleRatio" => $ArticleRatio)), false);
        }

        $as  = array($challendata, array("countNoPacks" => $countNoPacks, "

        " => $countRate, "countWight" => number_format($countWight, 2), "vendorInformation" => $vendorInformation, "vendorName" => $vendorName, "vendorAddress" => $vendorAddress, "vendorGSTNumber" => $vendorGSTNumber));
        return $as;
    }

    public function inwardcolorcheck()
    {
        $article = DB::select("SELECT (CASE WHEN ac.ArticleId IS NULL THEN '0' ELSE ac.ArticleId END) AS articlecolorcheck, a.* FROM `article` a left join articlecolor ac on ac.ArticleId=a.Id group by a.Id");
        foreach ($article as $key => $vl) {
            if ($vl->articlecolorcheck == 0 && $vl->ArticleOpenFlag == 0 && $vl->ArticleRate != "") {
                $getcolor = json_decode($vl->ArticleColor);
                $getsize = json_decode($vl->ArticleSize);
                $ArticleId = $vl->Id;
                $getratio = explode(",", $vl->ArticleRatio);
                foreach ($getcolor as $vl) {
                    DB::table('articlecolor')->insertGetId(
                        ['ArticleId' => $ArticleId, 'ArticleColorId' => $vl->Id, 'ArticleColorName' => $vl->Name, 'CreatedDate' => date("Y-m-d H:i:s")]
                    );
                }
                foreach ($getsize as $key => $vl) {
                    DB::table('articlesize')->insertGetId(
                        ['ArticleId' => $ArticleId, 'ArticleSize' => $vl->Id, 'ArticleSizeName' => $vl->Name, 'CreatedDate' => date("Y-m-d H:i:s")]
                    );
                    DB::table('articleratio')->insertGetId(
                        ['ArticleId' => $ArticleId, 'ArticleSizeId' => $vl->Id, 'ArticleRatio' => $getratio[$key], 'CreatedDate' => date("Y-m-d H:i:s")]
                    );
                }
            }
        }
    }
    public function InwardLogs($GRNId)
    {
        return DB::select("select * from (select ul.Id as UserLogsId, ul.Module, ul.LogType, ul.ModuleNumberId, ul.LogDescription, DATE_FORMAT(ul.created_at ,'%d-%m-%Y') as CreatedDate, DATE_FORMAT(ul.created_at ,'%H:%i:%s') as CreatedTime, u.Name, u.Status, ur.Role from userlogs ul inner join users u on u.Id=ul.UserId inner join userrole ur on ur.RoleType=u.Role) as dd where dd.ModuleNumberId= '" . $GRNId . "' and dd.Module='Inward' order by dd.UserLogsId desc ");
    }
}
