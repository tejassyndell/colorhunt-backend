<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\SO;
use App\Users;
use App\Inward;
use App\Outward;
use App\Salesreturn;
use App\Purchasereturns;
use App\Stocktransfer;
use App\Stockshortage;
use App\Article;
use App\UserLogs;
use App\TransportOutwardpacks;
use App\Outletimport;
use App\OutletNumber;
use App\OutletSalesreturn;
use App\OutletSalesReturnPacks;


class SOController extends Controller
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
    public function AddSO(Request $request)
    {
        $data = $request->all();
        if ($data['SoNumberId'] == "Add") {
            $generate_SONUMBER = $this->GenerateSoNumber($data['UserId']);
            $SO_Number = $generate_SONUMBER['SO_Number'];
            $SO_Number_Financial_Id = $generate_SONUMBER['SO_Number_Financial_Id'];
            $SoNumberId = DB::table('sonumber')->insertGetId(
                ['SoNumber' =>  $SO_Number, "FinancialYearId" => $SO_Number_Financial_Id, 'UserId' => $data['UserId'], 'PartyId' =>  $data['PartyId'], 'SoDate' => $data['Date'], 'Destination' => $data['Destination'], 'Transporter' => $data['Transporter'], 'Remarks' => $data['Remarks'], 'GSTAmount' => $data['GST'], 'GSTPercentage' => $data['GST_Percentage'], 'GSTType' => $data['GSTType'], 'CreatedDate' => date('Y-m-d H:i:s')]
            );
            $userName = Users::where('Id', $data['UserId'])->first();
            $sodRec = DB::select("select concat($SO_Number,'/', fn.StartYear,'-',fn.EndYear) as SONumber from sonumber sn inner join financialyear fn on fn.Id=sn.FinancialYearId where sn.Id= '" . $SoNumberId . "'");
            UserLogs::create([
                'Module' => 'SO',
                'ModuleNumberId' => $SoNumberId,
                'LogType' => 'Created',
                'LogDescription' => $userName['Name'] . " " . 'created so with SO Number' . " " . $sodRec[0]->SONumber,
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);
            $artRecor = Article::where('Id', $data['ArticleId'])->first();
            UserLogs::create([
                'Module' => 'SO',
                'ModuleNumberId' => $SoNumberId,
                'LogType' => 'Updated',
                'LogDescription' => $userName['Name'] . " " . 'added article ' . $artRecor->ArticleNumber . ' in so with SO Number' . " " . $sodRec[0]->SONumber,
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);
        } else {
            $checksonumber = DB::select("SELECT SoNumber FROM `sonumber` where Id ='" . $data['SoNumberId'] . "'");
            if (!empty($checksonumber)) {
                $SO_Number = $checksonumber[0]->SoNumber;
                $SoNumberId = $data['SoNumberId'];

                DB::table('sonumber')
                    ->where('Id', $SoNumberId)
                    ->update(['SoDate' => $data['Date'], 'PartyId' =>  $data['PartyId'], 'Destination' => $data['Destination'], 'Transporter' => $data['Transporter'], 'Remarks' => $data['Remarks'], 'GSTAmount' => $data['GST'], 'GSTPercentage' => $data['GST_Percentage'], 'GSTType' => $data['GSTType']]);
            }
            $userName = Users::where('Id', $data['UserId'])->first();
            $sodRec = DB::select("select concat($SO_Number,'/', fn.StartYear,'-',fn.EndYear) as SONumber from sonumber sn inner join financialyear fn on fn.Id=sn.FinancialYearId where sn.Id= '" . $SoNumberId . "'");
            $artRecor = Article::where('Id', $data['ArticleId'])->first();
            UserLogs::create([
                'Module' => 'SO',
                'ModuleNumberId' => $SoNumberId,
                'LogType' => 'Updated',
                'LogDescription' => $userName['Name'] . " " . 'added article ' . $artRecor->ArticleNumber . ' in so with SO Number' . " " . $sodRec[0]->SONumber,
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);
        }
        if (isset($data['ArticleRate'])) {
            $ArticleRate = $data['ArticleRate'];
        } else {
            $artratedata = DB::select("select * from articlerate where ArticleId='" . $data['ArticleId'] . "'");
            // if($data['PartyId'])
            $partyrec = DB::table('party')->where('Id', $data['PartyId'])->first();
            $partyuser =  Users::where('Id', $partyrec->UserId)->first();
            if ($partyuser) {
                if ($partyuser->PartyId != 0) {
                    $outpartyrec = DB::table('party')->where('Id', $partyuser->PartyId)->first();
                    $ArticleRate = $artratedata[0]->ArticleRate + $outpartyrec->OutletArticleRate;
                } else {
                    $ArticleRate = $artratedata[0]->ArticleRate;
                }
            } else {
                $ArticleRate = $artratedata[0]->ArticleRate;
            }
            // Party::where('Id' , $data['PartyId'])->first();

        }
        if ($data["ArticleOpenFlag"] == 1) {
            $mixnopacks = DB::select('SELECT * FROM `mixnopacks` where ArticleId="' . $data['ArticleId'] . '"');
            $NoPacks = "";
            $SalesNoPacks = "";
            if (isset($data['NoPacksNew'])) {
                $NoPacks .= $data['NoPacksNew'];
                if ($data['NoPacksNew'] == 0) {
                    return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                }
                if ($data['NoPacks'] < $data['NoPacksNew']) {
                    return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                }
                $SalesNoPacks .= ($mixnopacks[0]->NoPacks - $data['NoPacksNew']);
            } else {
                return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
            }
            $sonumberdata = DB::select('SELECT count(*) as total, NoPacks  FROM `so` where SoNumberId="' . $SoNumberId . '" and ArticleId="' . $data['ArticleId'] . '"');
            $getnppacks = $sonumberdata[0]->NoPacks;
            DB::table('mixnopacks')
                ->where('ArticleId', $data['ArticleId'])
                ->update(['NoPacks' => $SalesNoPacks]);
            if ($sonumberdata[0]->total > 0) {
                $nopacksadded = $getnppacks + $NoPacks;

                DB::table('so')
                    ->where('SoNumberId', $SoNumberId)
                    ->where('ArticleId', $data['ArticleId'])
                    ->update(['NoPacks' => $nopacksadded, 'OutwardNoPacks' => $nopacksadded, 'ArticleRate' => $ArticleRate]);
            } else {
                $soadd['SoNumberId'] = $SoNumberId;
                $soadd["ArticleId"] = $data['ArticleId'];
                $soadd["NoPacks"] = $NoPacks;
                $soadd["OutwardNoPacks"] = $NoPacks;
                $soadd["ArticleRate"] = $ArticleRate;
                $field = SO::create($soadd);
            }
            return response()->json(array("SoNumberId" => $SoNumberId, "SO_Number" => $SO_Number), 200);
        } else {
            $soadd = array();
            $dataresult = DB::select('SELECT c.Colorflag FROM `article` a inner join category c on c.Id=a.CategoryId where a.Id="' . $data['ArticleId'] . '"');
            $Colorflag = $dataresult[0]->Colorflag;
            $datanopacks = DB::select('SELECT SalesNoPacks FROM `inward` where ArticleId="' . $data['ArticleId'] . '" order by Id desc limit 0,1');
            $search = $datanopacks[0]->SalesNoPacks;
            $searchString = ',';
            if (strpos($search, $searchString) !== false) {
                $string = explode(',', $search);
                $stringcomma = 1;
            } else {
                $search;
                $stringcomma = 0;
            }
            $NoPacks = "";
            $SalesNoPacks = "";
            if ($Colorflag == 1) {
                foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                    $numberofpacks = $vl["Id"];
                    if ($data["NoPacksNew_" . $numberofpacks] != "") {
                        if ($stringcomma == 1) {
                            if ($string[$key] < $data["NoPacksNew_" . $numberofpacks]) {
                                return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                            }
                            $SalesNoPacks .= ($string[$key] - $data["NoPacksNew_" . $numberofpacks]) . ",";
                        } else {
                            if ($search < $data["NoPacksNew_" . $numberofpacks]) {
                                return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                            }
                            $SalesNoPacks .= ($search - $data["NoPacksNew_" . $numberofpacks]) . ",";
                        }
                        $NoPacks .= $data["NoPacksNew_" . $numberofpacks] . ",";
                    } else {
                        $NoPacks .= "0,";
                        $SalesNoPacks .= $string[$key] . ",";
                    }
                }
            } else {
                if (isset($data['NoPacksNew'])) {
                    $NoPacks .= $data['NoPacksNew'];
                    if ($search < $data['NoPacksNew']) {
                        return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                    }
                    $SalesNoPacks .= ($search - $data['NoPacksNew']);
                } else {
                    return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
                }
            }
            $NoPacks = rtrim($NoPacks, ',');
            $SalesNoPacks = rtrim($SalesNoPacks, ',');
            $CheckSalesNoPacks = explode(',', $NoPacks);
            $tmp = array_filter($CheckSalesNoPacks);
            if (empty($tmp)) {
                return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
            }
            $sonumberdata = DB::select('SELECT count(*) as total, NoPacks  FROM `so` where SoNumberId="' . $SoNumberId . '" and ArticleId="' . $data['ArticleId'] . '"');
            $getnppacks = $sonumberdata[0]->NoPacks;
            DB::table('inward')
                ->where('ArticleId', $data['ArticleId'])
                ->update(['SalesNoPacks' => $SalesNoPacks]);
            if ($sonumberdata[0]->total > 0) {
                $nopacksadded = "";
                if (strpos($NoPacks, ',') !== false) {
                    $NoPacks1 = explode(',', $NoPacks);
                    $getnppacks = explode(',', $getnppacks);
                    foreach ($getnppacks as $key => $vl) {
                        $nopacksadded .= $NoPacks1[$key] + $vl . ",";
                    }
                } else {
                    $nopacksadded .= $getnppacks + $NoPacks . ",";
                }
                $nopacksadded = rtrim($nopacksadded, ',');
                DB::table('so')
                    ->where('SoNumberId', $SoNumberId)
                    ->where('ArticleId', $data['ArticleId'])
                    ->update(['NoPacks' => $nopacksadded, 'OutwardNoPacks' => $nopacksadded, 'ArticleRate' => $ArticleRate]);
            } else {
                $soadd['SoNumberId'] = $SoNumberId;
                $soadd["ArticleId"] = $data['ArticleId'];
                $soadd["NoPacks"] = $NoPacks;
                $soadd["OutwardNoPacks"] = $NoPacks;
                $soadd["ArticleRate"] = $ArticleRate;
                SO::create($soadd);
            }
            return response()->json(array("SoNumberId" => $SoNumberId, "SO_Number" => $SO_Number), 200);
        }
    }

    public function GetSO($UserId)
    {
        $userrole = DB::select("SELECT Role FROM `users`where Id='" . $UserId . "'");
        if ($userrole[0]->Role == 2) {
            $wherecustom = "";
        } else {
            $wherecustom = "where son.UserId='" . $UserId . "'";
        }
        return DB::select("select * from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId " . $wherecustom . " group by s.SoNumberId) as ddd where ddd.OWID=0 order by ddd.Id desc");
    }

    public function PostSolist(Request $request)
    {
        $data = $request->all();
        $search = $data['dataTablesParameters']["search"];
        $UserId = $data['UserID'];
        $startnumber = $data['dataTablesParameters']["start"];
        $userrole = DB::select("SELECT Role FROM `users`where Id='" . $UserId . "'");
        if ($userrole[0]->Role == 2 || $userrole[0]->Role == 8) {
            $whereuser = "";
        } else {
            $whereuser = "and u.Id='" . $UserId . "'";
        }
        $vnddataTotal = DB::select('select count(*) Total from (SELECT son.Id, s.Status FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId left join outwardnumber own on own.SoId= son.Id where son.id in (son.Id) and own.Id is NULL and s.Status!=1 ' . $whereuser . ' group by s.SoNumberId) as d');
        $vntotal = $vnddataTotal[0]->Total;
        $length = $data['dataTablesParameters']["length"];
        if ($userrole[0]->Role == 2 || $userrole[0]->Role == 8) {
            if ($search['value'] != null && strlen($search['value']) > 2) {
                $searchstring = " where d.SoNumber like '%" . $search['value'] . "%' OR d.Name like '%" . $search['value'] . "%'  OR CAST(d.TotalSoPieces as SIGNED INTEGER) like '%" . $search['value'] . "%' OR cast(d.cdate as char) like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%'";
                $vnddataTotalFilter = DB::select("select count(*) Total from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate,DATE_FORMAT(son.SoDate, '%d/%m/%Y') as cdate, son.Destination, son.Transporter, son.UserId, concat(IFNULL(partyuser.Name,u.Name),son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId left join users partyuser on partyuser.Id=p.UserId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId left join outwardnumber own on own.SoId= son.Id where son.id in (son.Id) and own.Id is NULL and s.Status!=1 " . $whereuser . " group by s.SoNumberId) as d " . $searchstring);
                $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
            } else {
                $searchstring = "";
                $vnddataTotalFilterValue = $vntotal;
            }
        } else {
            if ($search['value'] != null && strlen($search['value']) > 2) {
                $searchstring = " where d.SoNumber like '%" . $search['value'] . "%' OR d.Name like '%" . $search['value'] . "%'  OR CAST(d.TotalSoPieces as SIGNED INTEGER) like '%" . $search['value'] . "%'  OR cast(d.cdate as char) like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%'";
                $vnddataTotalFilter = DB::select("select count(*) Total from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate,DATE_FORMAT(son.SoDate, '%d/%m/%Y') as cdate, son.Destination, son.Transporter, son.UserId, concat(IFNULL(partyuser.Name,u.Name),son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId  left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId left join users partyuser on partyuser.Id=p.UserId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId left join outwardnumber own on own.SoId= son.Id where son.id in (son.Id) and own.Id is NULL and s.Status!=1 " . $whereuser . " group by s.SoNumberId) as d " . $searchstring);
                $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
            } else {
                $searchstring = "";
                $vnddataTotalFilterValue = $vntotal;
            }
        }
        $column = $data['dataTablesParameters']["order"][0]["column"];
        switch ($column) {
            case 1:
                $ordercolumn = "d.Id";
                break;
            case 2:
                $ordercolumn = "d.Name";
                break;
            case 3:
                $ordercolumn = "CAST(d.TotalSoPieces as SIGNED INTEGER)";
                break;
            case 4:
                $ordercolumn = "date(d.SoDate)";
                break;
            default:
                $ordercolumn = "d.Id";
                break;
        }
        $order = "";
        if ($data['dataTablesParameters']["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data['dataTablesParameters']["order"][0]["dir"];
        }
        $vnddata = DB::select("select d.* from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, partyuser.Name as PartyUserName  , u.Name as UserName, p.UserId as PartyUserId ,  s.Status, p.Name, (CASE WHEN own.Id IS NULL THEN 0 ELSE 1 END) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate,DATE_FORMAT(son.SoDate, '%d/%m/%Y') as cdate, son.Destination, son.Transporter, son.UserId, concat(IFNULL(partyuser.Name,u.Name),son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id right join party p on p.Id=son.PartyId left join users partyuser on partyuser.Id=p.UserId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId left join outwardnumber own on own.SoId= son.Id where own.Id is NULL  and s.Status!=1 " . $whereuser . " group by s.SoNumberId) as d  " . $searchstring . " " . $order . " limit " . $data['dataTablesParameters']["start"] . "," . $length);
        $totalSoPieces = 0;
        foreach ($vnddata as $vnd) {
            $getSoes = SO::where('SoNumberId', $vnd->Id)->where('Status', '!=', 1)->get();
            foreach ($getSoes as $getso) {
                if (strpos($getso->OutwardNoPacks, ',') !== false) {
                    $totalSoPieces += array_sum(explode(",", $getso->OutwardNoPacks));
                } else {
                    $singlecountNoPacks = $getso->OutwardNoPacks;
                    $totalSoPieces += $getso->OutwardNoPacks;
                }
                $vnd->TotalSoPieces = $totalSoPieces;
            }
            $totalSoPieces = 0;
        }


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

    public function DeleteSONumber($SONO, $LoggedId)
    {
        $solist = DB::select("SELECT s.Id, s.ArticleId, a.ArticleOpenFlag FROM `so` s inner join article a on a.Id=s.ArticleId where s.SoNumberId='" . $SONO . "'");
        foreach ($solist as $vl) {
            if ($vl->ArticleOpenFlag == 0) {
                $data = DB::select("SELECT s.Id, s.ArticleId, s.NoPacks, inw.NoPacks as InwardNoPacks, inw.SalesNoPacks FROM `so` s left join inward inw on inw.ArticleId=s.ArticleId where s.Id = '" . $vl->Id . "'");
                $ArticleId = $data[0]->ArticleId;
                $search = $data[0]->NoPacks;
                $SalesNoPacks = $data[0]->SalesNoPacks;
                $searchString = ',';
                $AddSalesNoPacks = "";
                if (strpos($search, $searchString) !== false) {
                    $string = explode(',', $search);
                    $SalesNoPacks = explode(',', $SalesNoPacks);
                    foreach ($string as $key => $vl) {
                        $AddSalesNoPacks .= ($SalesNoPacks[$key] + $vl) . ",";
                    }
                } else {
                    $AddSalesNoPacks .= ($search + $SalesNoPacks) . ",";
                }
                $AddSalesNoPacks = rtrim($AddSalesNoPacks, ',');
                DB::table('so')
                    ->where('Id', '=', $data[0]->Id)
                    ->delete();
                DB::table('inward')
                    ->where('ArticleId', $ArticleId)
                    ->update(['SalesNoPacks' => $AddSalesNoPacks]);
            } else {
                $data = DB::select("SELECT mxp.NoPacks, s.Id, s.SoNumberId, s.ArticleId, s.NoPacks as SONoPacks, s.Status FROM `so` s inner join mixnopacks mxp on mxp.ArticleId=s.ArticleId where s.Id = '" . $vl->Id . "'");
                $ArticleId = $data[0]->ArticleId;
                $search = $data[0]->SONoPacks;
                $SalesNoPacks = $data[0]->NoPacks;
                $searchString = ',';
                $AddSalesNoPacks = ($search + $SalesNoPacks);
                DB::table('so')
                    ->where('Id', '=', $data[0]->Id)
                    ->delete();
                DB::table('mixnopacks')
                    ->where('ArticleId', $ArticleId)
                    ->update(['NoPacks' => $AddSalesNoPacks]);
            }
        }
        $userName = Users::where('Id', $LoggedId)->first();
        $soRec = DB::select("select concat(sn.SoNumber,'/', fn.StartYear,'-',fn.EndYear) as SOnumber from sonumber sn inner join financialyear fn on fn.Id=sn.FinancialYearId where sn.Id= '" . $SONO . "'");

        UserLogs::create([
            'Module' => 'SO',
            'ModuleNumberId' => $SONO,
            'LogType' => 'Deleted',
            'LogDescription' => $userName['Name'] . " " . 'deleted so with SO Number' . " " . $soRec[0]->SOnumber,
            'UserId' => $userName['Id'],
            'updated_at' => null
        ]);
        return response()->json("SUCCESS", 200);
    }
    public function DeleteSO($id, $ArticleOpenFlag, $LoggedId)
    {
        $userName = Users::where('Id', $LoggedId)->first();
        $soRec = DB::select("select sn.id as SoNumberId, a.ArticleNumber, concat(sn.SoNumber,'/', fn.StartYear,'-',fn.EndYear) as SOnumber from so s inner join sonumber sn on sn.Id=s.SoNumberId inner join article a on a.Id=s.ArticleId inner join financialyear fn on fn.Id=sn.FinancialYearId where s.Id= '" . $id . "'");
        if ($ArticleOpenFlag == 1) {
            $datanopacks = DB::select('SELECT s.ArticleId, s.NoPacks, mxp.NoPacks as MaxNoPacks FROM `so` s left join mixnopacks mxp on mxp.ArticleId=s.ArticleId where s.Id="' . $id . '"');
            $ArticleId = $datanopacks[0]->ArticleId;
            $search = $datanopacks[0]->NoPacks;
            $SalesNoPacks = $datanopacks[0]->MaxNoPacks;
            $AddSalesNoPacks = ($search + $SalesNoPacks);
            DB::beginTransaction();
            try {
                UserLogs::create([
                    'Module' => 'SO',
                    'ModuleNumberId' => $soRec[0]->SoNumberId,
                    'LogType' => 'Deleted',
                    'LogDescription' => $userName['Name'] . ' deleted article ' . $soRec[0]->ArticleNumber . ' from SO Number ' . $soRec[0]->SOnumber,
                    'UserId' => $userName['Id'],
                    'updated_at' => null
                ]);
                DB::table('so')
                    ->where('Id', '=', $id)
                    ->delete();
                DB::table('mixnopacks')
                    ->where('ArticleId', $ArticleId)
                    ->update(['NoPacks' => $AddSalesNoPacks]);
                DB::commit();
                return response()->json("SUCCESS", 200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json("", 200);
            }
        } else {
            $datanopacks = DB::select('SELECT s.ArticleId, s.NoPacks, inw.NoPacks as InwardNoPacks, inw.SalesNoPacks FROM `so` s left join inward inw on inw.ArticleId=s.ArticleId where s.Id="' . $id . '"');
            $ArticleId = $datanopacks[0]->ArticleId;
            $search = $datanopacks[0]->NoPacks;
            $SalesNoPacks = $datanopacks[0]->SalesNoPacks;
            $searchString = ',';
            $AddSalesNoPacks = "";
            if (strpos($search, $searchString) !== false) {
                $string = explode(',', $search);
                $SalesNoPacks = explode(',', $SalesNoPacks);
                foreach ($string as $key => $vl) {
                    $AddSalesNoPacks .= ($SalesNoPacks[$key] + $vl) . ",";
                }
            } else {
                $AddSalesNoPacks .= ($search + $SalesNoPacks) . ",";
            }
            $AddSalesNoPacks = rtrim($AddSalesNoPacks, ',');
            DB::beginTransaction();
            try {
                UserLogs::create([
                    'Module' => 'SO',
                    'ModuleNumberId' => $soRec[0]->SoNumberId,
                    'LogType' => 'Deleted',
                    'LogDescription' => $userName['Name'] . ' deleted article ' . $soRec[0]->ArticleNumber . ' from SO Number ' . $soRec[0]->SOnumber,
                    'UserId' => $userName['Id'],
                    'updated_at' => null
                ]);
                DB::table('so')
                    ->where('Id', '=', $id)
                    ->delete();
                DB::table('inward')
                    ->where('ArticleId', $ArticleId)
                    ->update(['SalesNoPacks' => $AddSalesNoPacks]);
                DB::commit();
                return response()->json("SUCCESS", 200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json("", 200);
            }
        }
    }

    public function UpdateSo(Request $request)
    {
        $data = $request->all();

        if ($data["ArticleOpenFlag"] == 1) {
            $mixnopacks = DB::select('SELECT *, (select Nopacks from so where id="' . $data['id'] . '") as SONopacks FROM `mixnopacks` where ArticleId= "' . $data['ArticleId'] . '"');
            $InwardSalesNoPacks = $mixnopacks[0]->NoPacks;
            $SONopacks = $mixnopacks[0]->SONopacks;
            $UpdateInwardNoPacks = "";
            $updateNoPacks = $data['NoPacksNew'];
            $total = $InwardSalesNoPacks + $SONopacks;
            if ($total < $data["NoPacksNew"]) {
                return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
            }
            if ($SONopacks < $data['NoPacksNew']) {
                $UpdateInwardNoPacks = ($InwardSalesNoPacks - ($data['NoPacksNew'] - $SONopacks));
            } else if ($SONopacks == $data['NoPacksNew']) {
                $UpdateInwardNoPacks = $InwardSalesNoPacks;
            } else {
                if ($InwardSalesNoPacks == 0) {
                    $UpdateInwardNoPacks = ($SONopacks - $data['NoPacksNew']);
                } else {
                    $UpdateInwardNoPacks = ($InwardSalesNoPacks + ($SONopacks - $data['NoPacksNew']));
                }
            }
            if (empty($updateNoPacks)) {
                return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
            }
            $sodRec = DB::select("select sn.Id as SoNumberId, concat(sn.SoNumber,'/', fn.StartYear,'-',fn.EndYear) as SONumber from sonumber sn inner join financialyear fn on fn.Id=sn.FinancialYearId where sn.Id= '" . $data['SoNumberId'] . "'");
            $userName = Users::where('Id', $data['UserId'])->first();
            $artRecor = Article::where('Id', $data['ArticleId'])->first();
            $ActiveSO  = DB::select("select sn.*, s.NoPacks from so s inner join sonumber sn on sn.Id=s.SoNumberId where s.Id='" . $data['id'] . "'");
            $logDesc = "";
            if ($ActiveSO[0]->Destination  != $data['Destination']) {
                $logDesc = $logDesc . 'Destination,';
            }
            if ($ActiveSO[0]->Transporter != $data['Transporter']) {
                $logDesc = $logDesc . 'Transporter,';
            }
            if ($ActiveSO[0]->Remarks != $data['Remarks']) {
                $logDesc = $logDesc . 'Remarks,';
            }
            if ($ActiveSO[0]->GSTAmount != $data['GST']) {
                $logDesc = $logDesc . 'GSTAmount,';
            }
            if ($ActiveSO[0]->GSTPercentage != $data['GST_Percentage']) {
                $logDesc = $logDesc . 'GST_Percentage,';
            }
            if ($ActiveSO[0]->NoPacks != $updateNoPacks) {
                $logDesc = $logDesc . 'Pieces,';
            }
            $newLogDesc = rtrim($logDesc, ',');
            DB::beginTransaction();
            try {
                DB::table('mixnopacks')
                    ->where('ArticleId', $data['ArticleId'])
                    ->update(['NoPacks' => $UpdateInwardNoPacks]);

                DB::table('sonumber')
                    ->where('Id', $data['SoNumberId'])
                    ->update(['SoDate' => $data['Date'], 'PartyId' =>  $data['PartyId'], 'Destination' => $data['Destination'], 'Transporter' => $data['Transporter'], 'Remarks' => $data['Remarks'], 'GSTAmount' => $data['GST'], 'GSTPercentage' => $data['GST_Percentage']]);
                SO::where('id', $data['id'])->update(array(
                    'NoPacks' => $updateNoPacks,
                    'OutwardNoPacks' => $updateNoPacks
                ));
                DB::commit();
                UserLogs::create([
                    'Module' => 'SO',
                    'ModuleNumberId' => $sodRec[0]->SoNumberId,
                    'LogType' => 'Updated',
                    'LogDescription' => $userName['Name'] . ' updated ' . $newLogDesc . ' of article ' . $artRecor->ArticleNumber . ' in SO Number ' . $sodRec[0]->SONumber,
                    'UserId' => $userName['Id'],
                    'updated_at' => null
                ]);
                return response()->json("SUCCESS", 200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json("", 200);
            }
        } else {
            $dataresult = DB::select('SELECT c.Colorflag, inw.NoPacks as TotalNoPacks, inw.SalesNoPacks as InwardSalesNoPacks, (select Nopacks from so where id="' . $data['id'] . '") as SONopacks FROM `article` a inner join category c on c.Id=a.CategoryId inner join inward inw on inw.ArticleId=a.Id where a.Id="' . $data['ArticleId'] . '"');
            $Colorflag = $dataresult[0]->Colorflag;
            $InwardSalesNoPacks = $dataresult[0]->InwardSalesNoPacks;
            $SONopacks = $dataresult[0]->SONopacks;
            if (strpos($SONopacks, ',') !== false) {
                $InwardSalesNoPacks = explode(',', $InwardSalesNoPacks);
                $SONopacks = explode(',', $SONopacks);
                $stringcomma = 1;
            } else {
                $stringcomma = 0;
            }
            $updateNoPacks = "";
            $UpdateInwardNoPacks = "";
            if ($Colorflag == 1) {
                foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                    $numberofpacks = $vl["Id"];
                    $inwardsale = $InwardSalesNoPacks[$key];
                    $sosale = $SONopacks[$key];
                    $getnopacks = $data["NoPacksNew_" . $numberofpacks];
                    if ($data["NoPacksNew_" . $numberofpacks] != "") {
                        if ($stringcomma == 1) {
                            $total = ($inwardsale + $sosale);
                            if ($total < $getnopacks) {
                                return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                            }
                            $updateNoPacks .= $getnopacks . ",";
                            if ($sosale < $getnopacks) {
                                $UpdateInwardNoPacks .= ($inwardsale - ($getnopacks - $sosale)) . ",";
                            } else if ($sosale == $getnopacks) {
                                $UpdateInwardNoPacks .= $inwardsale . ",";
                            } else {
                                if ($inwardsale == 0) {
                                    $UpdateInwardNoPacks .= ($sosale - $getnopacks) . ",";
                                } else {
                                    $UpdateInwardNoPacks .= ($inwardsale + ($sosale - $getnopacks)) . ",";
                                }
                            }
                        } else {
                            $total = $InwardSalesNoPacks + $SONopacks;
                            if ($total < $getnopacks) {
                                return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                            }
                            if ($SONopacks < $getnopacks) {
                                $UpdateInwardNoPacks .= ($InwardSalesNoPacks - ($getnopacks - $SONopacks)) . ",";
                            } else if ($sosale == $getnopacks) {
                                $UpdateInwardNoPacks .= $InwardSalesNoPacks . ",";
                            } else {
                                if ($inwardsale == 0) {
                                    $UpdateInwardNoPacks .= ($SONopacks - $getnopacks) . ",";
                                } else {
                                    $UpdateInwardNoPacks .= ($InwardSalesNoPacks + ($SONopacks - $getnopacks)) . ",";
                                }
                            }
                            $updateNoPacks .= $getnopacks . ",";
                        }
                    } else {
                        $updateNoPacks .= "0,";
                        $UpdateInwardNoPacks .= ($inwardsale + $sosale) . ",";
                    }
                }
            } else {
                $updateNoPacks .= $data['NoPacksNew'] . ",";
                $total = $InwardSalesNoPacks + $SONopacks;
                if ($total < $data["NoPacksNew"]) {
                    return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                }
                if ($SONopacks < $data['NoPacksNew']) {
                    $UpdateInwardNoPacks .= ($InwardSalesNoPacks - ($data['NoPacksNew'] - $SONopacks)) . ",";
                } else if ($SONopacks == $data['NoPacksNew']) {
                    $UpdateInwardNoPacks .= $InwardSalesNoPacks . ",";
                } else {
                    if ($InwardSalesNoPacks == 0) {
                        $UpdateInwardNoPacks .= ($SONopacks - $data['NoPacksNew']) . ",";
                    } else {
                        $UpdateInwardNoPacks .= ($InwardSalesNoPacks + ($SONopacks - $data['NoPacksNew'])) . ",";
                    }
                }
            }
            $updateNoPacks = rtrim($updateNoPacks, ',');
            $UpdateInwardNoPacks = rtrim($UpdateInwardNoPacks, ',');
            $CheckupdateNoPacks = explode(',', $updateNoPacks);
            $tmp = array_filter($CheckupdateNoPacks);
            if (empty($tmp)) {
                return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
            }
            DB::beginTransaction();
            try {
                $sodRec = DB::select("select sn.Id as SoNumberId, concat(sn.SoNumber,'/', fn.StartYear,'-',fn.EndYear) as SONumber from sonumber sn inner join financialyear fn on fn.Id=sn.FinancialYearId where sn.Id= '" . $data['SoNumberId'] . "'");
                $userName = Users::where('Id', $data['UserId'])->first();
                $artRecor = Article::where('Id', $data['ArticleId'])->first();
                $ActiveSO  = DB::select("select sn.*, s.NoPacks from so s inner join sonumber sn on sn.Id=s.SoNumberId where s.Id='" . $data['id'] . "'");
                $logDesc = "";
                if ($ActiveSO[0]->Destination  != $data['Destination']) {
                    $logDesc = $logDesc . 'Destination,';
                }
                if ($ActiveSO[0]->Transporter != $data['Transporter']) {
                    $logDesc = $logDesc . 'Transporter,';
                }
                if ($ActiveSO[0]->Remarks != $data['Remarks']) {
                    $logDesc = $logDesc . 'Remarks,';
                }
                if ($ActiveSO[0]->GSTAmount != $data['GST']) {
                    $logDesc = $logDesc . 'GSTAmount,';
                }
                if ($ActiveSO[0]->GSTPercentage != $data['GST_Percentage']) {
                    $logDesc = $logDesc . 'GST_Percentage,';
                }
                if ($ActiveSO[0]->NoPacks != $updateNoPacks) {
                    $logDesc = $logDesc . 'Pieces,';
                }
                $newLogDesc = rtrim($logDesc, ',');
                DB::table('inward')
                    ->where('ArticleId', $data['ArticleId'])
                    ->update(['SalesNoPacks' => $UpdateInwardNoPacks]);
                DB::table('sonumber')
                    ->where('Id', $data['SoNumberId'])
                    ->update(['SoDate' => $data['Date'], 'PartyId' =>  $data['PartyId'], 'Destination' => $data['Destination'], 'Transporter' => $data['Transporter'], 'Remarks' => $data['Remarks'], 'GSTAmount' => $data['GST'], 'GSTPercentage' => $data['GST_Percentage']]);
                SO::where('id', $data['id'])->update(array(
                    'NoPacks' => $updateNoPacks,
                    'OutwardNoPacks' => $updateNoPacks
                ));
                DB::commit();
                UserLogs::create([
                    'Module' => 'SO',
                    'ModuleNumberId' => $sodRec[0]->SoNumberId,
                    'LogType' => 'Updated',
                    'LogDescription' => $userName['Name'] . ' updated article ' . $newLogDesc . ' of article ' . $artRecor->ArticleNumber . ' in SO Number ' . $sodRec[0]->SONumber,
                    'UserId' => $userName['Id'],
                    'updated_at' => null
                ]);
                return response()->json("SUCCESS", 200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json("", 200);
            }
        }
    }

    public function GetSoIdWise($id)
    {
        return DB::select("SELECT s.*, concat(IFNULL(partyuser.Name,u.Name),son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber , son.GSTAmount, son.GSTPercentage, son.GSTType From so s inner join sonumber son on son.Id=s.SoNumberId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on son.UserId=u.Id inner join party p on p.Id=son.PartyId  left join users partyuser on partyuser.Id=p.UserId WHERE s.Id = $id");
    }

    

    public function GetArticle()
    {
        return DB::select('select * from (SELECT art.*, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId group by inw.Id union all SELECT a.*, m.ArticleId, m.NoPacks, m.SalesNoPacks, 0 FROM `mixnopacks` m inner join article a on a.Id=m.ArticleId where m.NoPacks!=0) as t where SalesNoPacksCheck!=1 and t.ArticleStatus = 1  group by t.Id');

    }

    public function GetArticlesOfOutlet($id)
    {
        return DB::select('select * from (SELECT oi.PartyId, art.*, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId inner join outletimport oi on oi.ArticleId = art.Id group by inw.Id ) as t where PartyId ='. $id .' and SalesNoPacksCheck!=1 and t.ArticleStatus = 1 group by t.Id');

    }



    // public function GetArticleSyn(Request $request)
    // {
    //     // return DB::select('select * from (SELECT art.*, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId group by inw.Id union all SELECT a.*, m.ArticleId, m.NoPacks, m.SalesNoPacks, 0 FROM `mixnopacks` m inner join article a on a.Id=m.ArticleId where m.NoPacks!=0) as t where  t.ArticleStatus = 1  group by t.Id');
     

    //     $withoutOpenFlag =  DB::select('SELECT art.*, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) AS SalesNoPacksCheck FROM inward inw LEFT JOIN articlelaunch arl ON arl.ArticleId = inw.ArticleId LEFT JOIN so s ON s.ArticleId = inw.ArticleId LEFT JOIN article art ON art.Id = inw.ArticleId WHERE arl.PartyId ='. $request->pid . ' AND  arl.LaunchDate <= CURDATE() GROUP BY arl.ArticleId order by arl.id ASC');

    //     // $ApprovedArt = DB::select('select * from (SELECT art.*, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId group by inw.Id union all SELECT a.*, m.ArticleId, m.NoPacks, m.SalesNoPacks, 0 FROM `mixnopacks` m inner join article a on a.Id=m.ArticleId where m.NoPacks!=0) as t where SalesNoPacksCheck!=1 and t.ArticleStatus = 1 group by t.Id');

    //     $withOpenFlag = DB::select('SELECT * FROM ( SELECT art.*, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) AS SalesNoPacksCheck FROM inward inw LEFT JOIN so s ON s.ArticleId = inw.ArticleId LEFT JOIN article art ON art.Id = inw.ArticleId GROUP BY inw.Id UNION ALL SELECT a.*, m.ArticleId, m.NoPacks, m.SalesNoPacks, 0 FROM `mixnopacks` m INNER JOIN article a ON a.Id = m.ArticleId WHERE m.NoPacks != 0 ) AS t WHERE t.ArticleStatus = 1 AND t.ArticleOpenFlag = 1 GROUP BY t.Id ORDER BY `t`.`ArticleOpenFlag` ASC');

    //     $forAllParty = DB::select('SELECT art.*, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) AS SalesNoPacksCheck FROM inward inw LEFT JOIN articlelaunch arl ON arl.ArticleId = inw.ArticleId LEFT JOIN so s ON s.ArticleId = inw.ArticleId LEFT JOIN article art ON art.Id = inw.ArticleId WHERE arl.PartyId =0 AND arl.LaunchDate <= CURDATE() GROUP BY arl.ArticleId');



    //     $arr1 = $withoutOpenFlag;
    //     $arr2 = $withOpenFlag;
    //     $arr3 = $forAllParty;
    //     // $arr4 = $ApprovedArt;

    //     return array_merge($arr3, $arr1, $arr2);
    

    // }
    
    public function GetArticleSyn(Request $request)
    {
        //OLD
        // return DB::select('select * from (SELECT art.*, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId group by inw.Id union all SELECT a.*, m.ArticleId, m.NoPacks, m.SalesNoPacks, 0 FROM `mixnopacks` m inner join article a on a.Id=m.ArticleId where m.NoPacks!=0) as t where  t.ArticleStatus = 1  group by t.Id');
        //OLD
     
        $withoutOpenFlag =  DB::select('SELECT art.Id, art.ArticleNumber, art.ArticleStatus, art.StyleDescription, art.ArticleOpenFlag, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) AS SalesNoPacksCheck FROM inward inw LEFT JOIN articlelaunch arl ON arl.ArticleId = inw.ArticleId LEFT JOIN so s ON s.ArticleId = inw.ArticleId LEFT JOIN article art ON art.Id = inw.ArticleId WHERE arl.PartyId ='. $request->pid . ' AND  arl.LaunchDate <= CURDATE() GROUP BY arl.ArticleId order by arl.id ASC');

        // $ApprovedArt = DB::select('select * from (SELECT art.*, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId group by inw.Id union all SELECT a.*, m.ArticleId, m.NoPacks, m.SalesNoPacks, 0 FROM `mixnopacks` m inner join article a on a.Id=m.ArticleId where m.NoPacks!=0) as t where SalesNoPacksCheck!=1 and t.ArticleStatus = 1 group by t.Id');

        $withOpenFlag = DB::select('SELECT * FROM ( SELECT art.Id, art.ArticleNumber, art.ArticleStatus, art.StyleDescription, art.ArticleOpenFlag, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) AS SalesNoPacksCheck FROM inward inw LEFT JOIN so s ON s.ArticleId = inw.ArticleId LEFT JOIN article art ON art.Id = inw.ArticleId GROUP BY inw.Id UNION ALL SELECT a.Id, a.ArticleNumber, a.ArticleStatus, a.StyleDescription, a.ArticleOpenFlag, m.ArticleId, m.NoPacks, m.SalesNoPacks, 0 FROM `mixnopacks` m INNER JOIN article a ON a.Id = m.ArticleId WHERE m.NoPacks != 0 ) AS t WHERE t.ArticleStatus = 1 AND t.ArticleOpenFlag = 1 GROUP BY t.Id ORDER BY `t`.`ArticleOpenFlag` ASC');

        $forAllParty = DB::select('SELECT art.Id, art.ArticleNumber, art.ArticleStatus, art.StyleDescription, art.ArticleOpenFlag, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) AS SalesNoPacksCheck FROM inward inw LEFT JOIN articlelaunch arl ON arl.ArticleId = inw.ArticleId LEFT JOIN so s ON s.ArticleId = inw.ArticleId LEFT JOIN article art ON art.Id = inw.ArticleId WHERE arl.PartyId =0 AND arl.LaunchDate <= CURDATE() GROUP BY arl.ArticleId');


        $arr1 = $withoutOpenFlag;
        $arr2 = $withOpenFlag;
        $arr3 = $forAllParty;
        // $arr4 = $ApprovedArt;

        $userid =1;

        $allArts = array_merge($arr3, $arr1, $arr2);
        // return $allArts;
        
        $filteredData = collect($allArts)->filter(function($item) {
            $salesNoPacks = array_map('intval', explode(',', $item->SalesNoPacks));
            $sum = array_sum($salesNoPacks);
            return $sum !== 0;
        })->values()->all();
 
 return $filteredData;


    }


    public function GetPartyArticle($id)
    {
        return DB::select('SELECT * FROM ( SELECT otn.PartyId, a.*, m.ArticleId, m.NoPacks, m.SalesNoPacks, 0 FROM `mixnopacks` m INNER JOIN article a ON a.Id=m.ArticleId LEFT JOIN outlet O ON a.Id=O.ArticleId LEFT JOIN outletnumber otn ON otn.Id = O.OutletNumberId WHERE m.NoPacks!=0 and otn.PartyId = '.$id.' ) AS t GROUP BY t.Id');
        // return DB::select('select * from (SELECT art.*, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId group by inw.Id union all SELECT a.*, m.ArticleId, m.NoPacks, m.SalesNoPacks, 0 FROM `mixnopacks` m inner join article a on a.Id=m.ArticleId where m.NoPacks!=0) as t where SalesNoPacksCheck!=1 and t.ArticleStatus = 1  group by t.Id');
    }

    public function GenerateSoNumber($UserId)
    {
        $array = array();
        $fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
        $sonumberdata = DB::select('SELECT Id, FinancialYearId, SoNumber From sonumber where UserId="' . $UserId . '" order by Id desc limit 0,1');
        if (count($sonumberdata) > 0) {
            if ($fin_yr[0]->Id > $sonumberdata[0]->FinancialYearId) {
                $array["SO_Number"] = 1;
                $array["SO_Number_Financial_Id"] = $fin_yr[0]->Id;
                $array["SO_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
                return $array;
            } else {
                $array["SO_Number"] = ($sonumberdata[0]->SoNumber) + 1;
                $array["SO_Number_Financial_Id"] = $fin_yr[0]->Id;
                $array["SO_Number_Financial"] = ($sonumberdata[0]->SoNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
                return $array;
            }
        } else {
            $array["SO_Number"] = 1;
            $array["SO_Number_Financial_Id"] = $fin_yr[0]->Id;
            $array["SO_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
            return $array;
        }
    }

    public function GetInwardArticleData($ArticleId)
    {
        $totalInward = 0;
        $totalOutwards = 0;
        $inwards  = Inward::where('ArticleId', $ArticleId)->get();
        $outwards  = Outward::where('ArticleId', $ArticleId)->get();
        $salesreturns = Salesreturn::where('ArticleId', $ArticleId)->get();
        $purchasereturns = Purchasereturns::where('ArticleId', $ArticleId)->get();
        $consumestocktransfers = Stocktransfer::where('ConsumedArticleId', $ArticleId)->get();
        $transferstocktransfers = Stocktransfer::where('TransferArticleId', $ArticleId)->get();
        $shortedStocks = Stockshortage::where('ArticleId', $ArticleId)->get();
        $sorecords  = SO::where('ArticleId', $ArticleId)->where('Status', 0)->get();
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
        $articleflagcheck = DB::select('SELECT ArticleOpenFlag FROM `article` where Id = "' . $ArticleId . '"');
        if ($articleflagcheck[0]->ArticleOpenFlag == 0) {
            $datasso =   DB::select("SELECT art.ArticleOpenFlag, art.ArticleNumber, ar.ArticleRate, art.ArticleRatio, art.ArticleSize, art.ArticleColor, (case when c.Colorflag IS NULL then cc.Colorflag else c.Colorflag end) as Colorflag,(case when c.Title IS NULL then cc.Title else c.Title end) as Category, i.SalesNoPacks FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=p.CategoryId left join category cc on cc.Id=art.CategoryId left join articlerate ar on ar.ArticleId=art.Id inner join inward i on i.ArticleId=art.Id where art.Id='" . $ArticleId . "' order by i.Id desc limit 0,1");
            foreach ($datasso as $so) {
                $so->NoPacks = $TotalRemaining;
            }
            return $datasso;
        } else {
            $datasso =  DB::select("SELECT c.Title as Category, a.ArticleNumber, ar.ArticleRate, a.ArticleOpenFlag FROM `mixnopacks` mxp inner join article a on a.Id=mxp.ArticleId left join category c on c.Id=a.CategoryId left join articlerate ar on ar.ArticleId=a.Id where mxp.ArticleId ='" . $ArticleId . "'");
            foreach ($datasso as $so) {
                $so->NoPacks = $TotalRemaining;
            }
            return $datasso;
        }
    }
    
    public function GetInwardArticleDataSO($userid, $PartyId, $ArticleId)
    {
        $articleflagcheck = DB::select('SELECT ArticleOpenFlag FROM `article` where Id = "' . $ArticleId . '"');
        $outletarticlerate = DB::select('SELECT UserId , OutletArticleRate, OutletAssign FROM `party` where Id = "' . $PartyId . '"');
        $totalInward = 0;
        $totalOutwards = 0;
        $inwards  = Inward::where('ArticleId', $ArticleId)->get();
        $outwards  = Outward::where('ArticleId', $ArticleId)->get();
        $salesreturns = Salesreturn::where('ArticleId', $ArticleId)->get();
        $purchasereturns = Purchasereturns::where('ArticleId', $ArticleId)->get();
        $consumestocktransfers = Stocktransfer::where('ConsumedArticleId', $ArticleId)->get();
        $transferstocktransfers = Stocktransfer::where('TransferArticleId', $ArticleId)->get();
        $shortedStocks = Stockshortage::where('ArticleId', $ArticleId)->get();
        $sorecords  = SO::where('ArticleId', $ArticleId)->where('Status', 0)->get();
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
                    $totalOutwards = $totalOutwards + array_sum(explode(",", $sorecord->OutwardNoPacks));
                } else {
                    $totalOutwards = $totalOutwards + $sorecord->OutwardNoPacks;
                }
            }
        }
        $TotalRemaining =  $totalInward - $totalOutwards;
        if ($outletarticlerate[0]->OutletAssign == 1) {
        $outletArticleRate = $outletarticlerate[0]->OutletArticleRate;
        } else {
            if (!is_null($outletarticlerate[0]->UserId)) {
                $userrecord = Users::where('Id', $outletarticlerate[0]->UserId)->first();
                if ($userrecord->PartyId != 0) {
                    $outletparty = DB::select('SELECT OutletArticleRate FROM `party` where Id = "' . $userrecord->PartyId . '"');
                    $outletArticleRate = $outletparty[0]->OutletArticleRate;
                } else {
                    // $outletArticleRate = 0;
                    $outletArticleRate = $outletarticlerate[0]->OutletArticleRate;
                }
            } else {
                // $outletArticleRate = 0;
                $outletArticleRate = $outletarticlerate[0]->OutletArticleRate;
            }
        }

        if ($articleflagcheck[0]->ArticleOpenFlag == 0) {
            $data = DB::select("SELECT art.ArticleOpenFlag, art.ArticleNumber, ar.ArticleRate, art.ArticleRatio, art.ArticleSize, art.ArticleColor, (case when c.Colorflag IS NULL then cc.Colorflag else c.Colorflag end) as Colorflag, (case when c.Title IS NULL then cc.Title else c.Title end) as Category,  i.NoPacks, i.SalesNoPacks FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=p.CategoryId left join category cc on cc.Id=art.CategoryId left join articlerate ar on ar.ArticleId=art.Id inner join inward i on i.ArticleId=art.Id where art.Id='" . $ArticleId . "' order by i.Id desc limit 0,1");
            foreach ($data as $key => $vl) {
                $object = (object)$vl;
                $object->NoPacks = $TotalRemaining;
                if (count($outletarticlerate) != 0) {
                    $object->ArticleRate = $object->ArticleRate + $outletArticleRate;
                }
            }
            return $data;
        } else {
            $data = DB::select("SELECT mxp.NoPacks, a.ArticleNumber,c.Title as Category,  ar.ArticleRate, a.ArticleOpenFlag FROM `mixnopacks` mxp inner join article a on a.Id=mxp.ArticleId left join category c on c.Id=a.CategoryId left join articlerate ar on ar.ArticleId=a.Id where mxp.ArticleId ='" . $ArticleId . "'");
            foreach ($data as $key => $vl) {
                $object = (object)$vl;
                $object->NoPacks = $TotalRemaining;
                $object->ArticleRate = (int)$object->ArticleRate + (int)$outletArticleRate;
            }
            return $data;
        }
    }





    public function SoListFromSONO($id, Request $request)
    {
        
             $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select('select count(*) as Total from (SELECT s.Id ,u.Name as UserName ,  p.UserId as PartyUserId,  s.SoNumberId, concat(son.SoNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as SoNumberGen ,  son.SoDate, son.Destination, son.Transporter, s.NoPacks, a.ArticleNumber, p.Name, a.ArticleOpenFlag ,c.Title as Category FROM `so` s inner join sonumber son on s.SoNumberId=son.Id inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on son.UserId=u.Id inner JOIN party p ON p.Id=son.PartyId  inner join article a on a.Id=s.ArticleId inner join category c on c.Id=a.CategoryId where s.SoNumberId="' . $id . '" group by s.Id order by s.Id ASC ) as d');
         $vnTotal = $vnddataTotal[0]->Total;
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "where d.NoPacks like '%" . $search['value'] . "%' OR d.Name like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%' OR d.Category like '%" . $search['value'] . "%' ";
            $vnddataTotalFilter = DB::select('select count(*) as Total from (SELECT s.Id ,u.Name as UserName ,  p.UserId as PartyUserId,  s.SoNumberId, concat(son.SoNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as SoNumberGen ,  son.SoDate, son.Destination, son.Transporter, s.NoPacks, a.ArticleNumber, p.Name, a.ArticleOpenFlag ,c.Title as Category FROM `so` s inner join sonumber son on s.SoNumberId=son.Id inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on son.UserId=u.Id inner JOIN party p ON p.Id=son.PartyId  inner join article a on a.Id=s.ArticleId inner join category c on c.Id=a.CategoryId where s.SoNumberId="' . $id . '" group by s.Id order by s.Id ASC ) as d '  . $searchstring );
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
                $ordercolumn = "d.Category";
                break;
            case 3:
                $ordercolumn = "d.SoNumber";
                break;
            case 4:
            $ordercolumn = "d.Name";
            break;
            
            case 5:
                $ordercolumn = "d.NoPacks";
            break;
            
            
            default:
                $ordercolumn = "d.SoDate";
                break;
        }
        $order = "";
        if ($data["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
        }
        $vnddata = DB::select('select d.* from (SELECT s.Id ,u.Name as UserName ,  p.UserId as PartyUserId,  s.SoNumberId, concat(son.SoNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as SoNumberGen ,  son.SoDate, son.Destination, son.Transporter, s.NoPacks, a.ArticleNumber, p.Name, a.ArticleOpenFlag ,c.Title as Category FROM `so` s inner join sonumber son on s.SoNumberId=son.Id inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on son.UserId=u.Id inner JOIN party p ON p.Id=son.PartyId  inner join article a on a.Id=s.ArticleId inner join category c on c.Id=a.CategoryId where s.SoNumberId="' . $id . '" group by s.Id order by s.Id ASC) as d ' . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length );
    
           foreach ($vnddata as $sodata) {
            if (!is_null($sodata->PartyUserId)) {
                $user = Users::where('Id', $sodata->PartyUserId)->first();
                $sodata->SoNumber = str_replace(' ', '', $user->Name . $sodata->SoNumberGen);
            } else {
                $sodata->SoNumber =  str_replace(' ', '', $sodata->UserName . $sodata->SoNumberGen);
            }
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


    // public function SoListFromSONO($id)
    // {
    //     $soListFromSONO = DB::select('SELECT s.Id ,u.Name as UserName ,  p.UserId as PartyUserId,  s.SoNumberId, concat(son.SoNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as SoNumberGen ,  son.SoDate, son.Destination, son.Transporter, s.NoPacks, a.ArticleNumber, p.Name, a.ArticleOpenFlag ,c.Title as Category FROM `so` s inner join sonumber son on s.SoNumberId=son.Id inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on son.UserId=u.Id inner JOIN party p ON p.Id=son.PartyId  inner join article a on a.Id=s.ArticleId inner join category c on c.Id=a.CategoryId where s.SoNumberId="' . $id . '" group by s.Id order by s.Id ASC');
    //     foreach ($soListFromSONO as $sodata) {
    //         if (!is_null($sodata->PartyUserId)) {
    //             $user = Users::where('Id', $sodata->PartyUserId)->first();
    //             $sodata->SoNumber = str_replace(' ', '', $user->Name . $sodata->SoNumberGen);
    //         } else {
    //             $sodata->SoNumber =  str_replace(' ', '', $sodata->UserName . $sodata->SoNumberGen);
    //         }
    //     }
    //     return $soListFromSONO;
    // }

    public function SoDateRemarkFromSONO($id)
    {
        return DB::select("SELECT p.Name, sn.*, concat(IFNULL(partyuser.Name,u.Name),sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SO_Number_FinancialYear  FROM `sonumber` sn inner join financialyear fn on fn.Id=sn.FinancialYearId inner join users u on sn.UserId=u.Id inner join party p on p.Id=sn.PartyId  left join users partyuser on partyuser.Id=p.UserId where sn.Id = '" . $id . "'");
    }

    //Sales Return Group data get on listing - start
  
    public function SrListFromSRONO($id, Request $request)
    {
        
        
        
        $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select("select count(*) as Total from (SELECT concat(own.OutwardNumber, '/',f.StartYear,'-',f.EndYear) as OutwardNumber, sr.Id , sr.CreatedDate, sr.SalesReturnNumber, concat(FirstCharacterConcat(u.Name), srn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sr.NoPacks, a.ArticleNumber, p.Name as PartyName, a.ArticleOpenFlag FROM `salesreturn` sr inner join salesreturnnumber srn on sr.SalesReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN party p ON p.Id=srn.PartyId inner join article a on a.Id=sr.ArticleId inner join outward o on o.Id=sr.OutwardId left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear f on f.Id=own.FinancialYearId where sr.SalesReturnNumber = '" . $id . "') as d");
        $vnTotal = $vnddataTotal[0]->Total;
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "where d.NoPacks like '%" . $search['value'] . "%' OR d.PartyName like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%' OR d.SalesReturnNumber like '%" . $search['value'] . "%' ";
            $vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT concat(own.OutwardNumber, '/',f.StartYear,'-',f.EndYear) as OutwardNumber, sr.Id , sr.CreatedDate, sr.SalesReturnNumber, concat(FirstCharacterConcat(u.Name), srn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sr.NoPacks, a.ArticleNumber, p.Name as PartyName, a.ArticleOpenFlag FROM `salesreturn` sr inner join salesreturnnumber srn on sr.SalesReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN party p ON p.Id=srn.PartyId inner join article a on a.Id=sr.ArticleId inner join outward o on o.Id=sr.OutwardId left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear f on f.Id=own.FinancialYearId where sr.SalesReturnNumber = '" . $id . "') as d "  . $searchstring );
            $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
        } else {
            $searchstring = "";
            $vnddataTotalFilterValue = $vnTotal;
        }
        $column = $data["order"][0]["column"];
        switch ($column) {
         
            case 2:
                $ordercolumn = "d.ArticleNumber";
                break;
            case 3:
                $ordercolumn = "d.PartyName";
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
        $vnddata = DB::select("select d.* from (SELECT concat(own.OutwardNumber, '/',f.StartYear,'-',f.EndYear) as OutwardNumber, sr.Id , sr.CreatedDate, sr.SalesReturnNumber, concat(FirstCharacterConcat(u.Name), srn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sr.NoPacks, a.ArticleNumber, p.Name as PartyName, a.ArticleOpenFlag FROM `salesreturn` sr inner join salesreturnnumber srn on sr.SalesReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN party p ON p.Id=srn.PartyId inner join article a on a.Id=sr.ArticleId inner join outward o on o.Id=sr.OutwardId left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear f on f.Id=own.FinancialYearId where sr.SalesReturnNumber = " . $id . ") as d " . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length   );
    
    

        return array(
            'datadraw' => $data["draw"],
            'recordsTotal' => $vnTotal,
            'recordsFiltered' => $vnddataTotalFilterValue,
            'response' => 'success',
            'startnumber' => $startnumber,
            'search' => count($vnddata),
            'data' => $vnddata,
        );
        
        
        
        
        //  return DB::select("SELECT concat(own.OutwardNumber, '/',f.StartYear,'-',f.EndYear) as OutwardNumber, sr.Id , sr.CreatedDate, sr.SalesReturnNumber, concat(FirstCharacterConcat(u.Name), srn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sr.NoPacks, a.ArticleNumber, p.Name as PartyName, a.ArticleOpenFlag FROM `salesreturn` sr inner join salesreturnnumber srn on sr.SalesReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN party p ON p.Id=srn.PartyId inner join article a on a.Id=sr.ArticleId inner join outward o on o.Id=sr.OutwardId left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear f on f.Id=own.FinancialYearId where sr.SalesReturnNumber = '" . $id . "'");
        
        
        
        
       
    }
  
    // public function SrListFromSRONO($id)
    // {
    //     return DB::select("SELECT concat(own.OutwardNumber, '/',f.StartYear,'-',f.EndYear) as OutwardNumber, sr.Id , sr.CreatedDate, sr.SalesReturnNumber, concat(FirstCharacterConcat(u.Name), srn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sr.NoPacks, a.ArticleNumber, p.Name as PartyName, a.ArticleOpenFlag FROM `salesreturn` sr inner join salesreturnnumber srn on sr.SalesReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN party p ON p.Id=srn.PartyId inner join article a on a.Id=sr.ArticleId inner join outward o on o.Id=sr.OutwardId left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear f on f.Id=own.FinancialYearId where sr.SalesReturnNumber = '" . $id . "'");
    // }
    //Sales Return Group data get on listing - end

    //Outlet Sales Return Group data get on listing - start

    public function SroListFromSRONO($id, Request $request )
    {
        
        
        
        
          $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select("select count(*) as Total from (SELECT sr.Id , srn.SalesReturnNumber  as SaleReturnNumberId ,  c.Title as Category , sr.CreatedDate, sr.SalesReturnNumber, concat(FirstCharacterConcat(u.Name), srn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sr.NoPacks, a.ArticleNumber, p.Name as PartyName, a.ArticleOpenFlag FROM `outletsalesreturn` sr inner join outletsalesreturnnumber srn on sr.SalesReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN party p ON p.Id=srn.PartyId inner join article a on a.Id=sr.ArticleId inner join category c on c.Id=a.CategoryId  where sr.SalesReturnNumber = '" . $id . "') as d");
        $vnTotal = $vnddataTotal[0]->Total;
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "where d.NoPacks like '%" . $search['value'] . "%' OR d.PartyName like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%' OR d.SalesReturnNumber like '%" . $search['value'] . "%' ";
            $vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT sr.Id , srn.SalesReturnNumber  as SaleReturnNumberId ,  c.Title as Category , sr.CreatedDate, sr.SalesReturnNumber, concat(FirstCharacterConcat(u.Name), srn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sr.NoPacks, a.ArticleNumber, p.Name as PartyName, a.ArticleOpenFlag FROM `outletsalesreturn` sr inner join outletsalesreturnnumber srn on sr.SalesReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN party p ON p.Id=srn.PartyId inner join article a on a.Id=sr.ArticleId inner join category c on c.Id=a.CategoryId  where sr.SalesReturnNumber = '" . $id . "') as d "  . $searchstring );
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
                $ordercolumn = "d.Category";
                break;
            case 3:
                $ordercolumn = "d.PartyName";
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
        $vnddata = DB::select("select d.* from (SELECT sr.Id , srn.SalesReturnNumber  as SaleReturnNumberId ,  c.Title as Category , sr.CreatedDate, sr.SalesReturnNumber, concat(FirstCharacterConcat(u.Name), srn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sr.NoPacks, a.ArticleNumber, p.Name as PartyName, a.ArticleOpenFlag FROM `outletsalesreturn` sr inner join outletsalesreturnnumber srn on sr.SalesReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN party p ON p.Id=srn.PartyId inner join article a on a.Id=sr.ArticleId inner join category c on c.Id=a.CategoryId  where sr.SalesReturnNumber = '" . $id . "') as d " . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length   );
    
    

        return array(
            'datadraw' => $data["draw"],
            'recordsTotal' => $vnTotal,
            'recordsFiltered' => $vnddataTotalFilterValue,
            'response' => 'success',
            'startnumber' => $startnumber,
            'search' => count($vnddata),
            'data' => $vnddata,
        );
        
        
        
        
        
        // return DB::select("SELECT sr.Id , srn.SalesReturnNumber  as SaleReturnNumberId ,  c.Title as Category , sr.CreatedDate, sr.SalesReturnNumber, concat(FirstCharacterConcat(u.Name), srn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sr.NoPacks, a.ArticleNumber, p.Name as PartyName, a.ArticleOpenFlag FROM `outletsalesreturn` sr inner join outletsalesreturnnumber srn on sr.SalesReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN party p ON p.Id=srn.PartyId inner join article a on a.Id=sr.ArticleId inner join category c on c.Id=a.CategoryId  where sr.SalesReturnNumber = '" . $id . "'");
    }

    // public function SroListFromSRONO($id)
    // {
    //     return DB::select("SELECT sr.Id , srn.SalesReturnNumber  as SaleReturnNumberId ,  c.Title as Category , sr.CreatedDate, sr.SalesReturnNumber, concat(FirstCharacterConcat(u.Name), srn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sr.NoPacks, a.ArticleNumber, p.Name as PartyName, a.ArticleOpenFlag FROM `outletsalesreturn` sr inner join outletsalesreturnnumber srn on sr.SalesReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN party p ON p.Id=srn.PartyId inner join article a on a.Id=sr.ArticleId inner join category c on c.Id=a.CategoryId  where sr.SalesReturnNumber = '" . $id . "'");
    // }
    //Outlet Sales Return Group data get on listing - end

    //Sales return id wise get the data - start
    public function SrDateRemarkFromSRONO($id)
    {
        return DB::select("SELECT concat(sn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sn.Id, sn.PartyId, sn.OutletPartyId, sn.Remarks FROM `salesreturnnumber` sn inner join financialyear fn on fn.Id=sn.FinancialYearId where sn.Id ='" . $id . "'");
    }
    //Sales return id wise get the data - end

    //outlet Sales return id wise get the data - start
    public function SroDateRemarkFromSRONO($id)
    {
                return DB::select("SELECT p.Name AS NAME, concat(sn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sn.Id, sn.PartyId, sn.OutletPartyId, sn.Remarks FROM `outletsalesreturnnumber` sn inner join financialyear fn on fn.Id=sn.FinancialYearId LEFT JOIN party p ON sn.PartyId = p.Id where sn.Id ='" . $id . "'");
        // return DB::select("SELECT concat(sn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sn.Id, sn.PartyId, sn.OutletPartyId, sn.Remarks FROM `outletsalesreturnnumber` sn inner join financialyear fn on fn.Id=sn.FinancialYearId where sn.Id ='" . $id . "'");
    }
    //outlet Sales return id wise get the data - end

    //Purchase Return Group data get on listing - start

    public function PrListFromPRONO($id, Request $request)
    {
        
        
        $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select('SELECT count(*) as Total FROM `purchasereturn` sr inner join purchasereturnnumber srn on sr.PurchaseReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN vendor p ON p.Id=sr.VendorId inner join article a on a.Id=sr.ArticleId inner join category c on c.Id=a.CategoryId inner join inward i on i.Id=sr.InwardId left join inwardgrn ig on ig.Id=i.GRN inner join financialyear f on f.Id=ig.FinancialYearId where sr.PurchaseReturnNumber = "' . $id . '"');
        $vnTotal = $vnddataTotal[0]->Total;
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "where d.Pieces like '%" . $search['value'] . "%' OR d.VendorName like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%' OR d.Category like '%" . $search['value'] . "%' ";
            $vnddataTotalFilter = DB::select('select count(*) as Total from (SELECT concat(ig.GRN, "/",f.StartYear,"-",f.EndYear) as GRN, sr.Id , c.Title as Category ,sr.CreatedDate, sr.ReturnNoPacks as Pieces, a.ArticleNumber, p.Name as VendorName, a.ArticleOpenFlag FROM `purchasereturn` sr inner join purchasereturnnumber srn on sr.PurchaseReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN vendor p ON p.Id=sr.VendorId inner join article a on a.Id=sr.ArticleId inner join category c on c.Id=a.CategoryId inner join inward i on i.Id=sr.InwardId left join inwardgrn ig on ig.Id=i.GRN inner join financialyear f on f.Id=ig.FinancialYearId where sr.PurchaseReturnNumber = "' . $id . '") as d '  . $searchstring );
            $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
        } else {
            $searchstring = "";
            $vnddataTotalFilterValue = $vnTotal;
        }
        $column = $data["order"][0]["column"];
        switch ($column) {
            case 2:
                $ordercolumn = "d.VendorName";
                break;
            case 3:
                $ordercolumn = "d.ArticleNumber";
                break;
            case 4:
                $ordercolumn = "d.Category";
                break;
            default:
                $ordercolumn = "d.CreatedDate";
                break;
        }
        $order = "";
        if ($data["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
        }
        $vnddata = DB::select('select d.* from (SELECT concat(ig.GRN, "/",f.StartYear,"-",f.EndYear) as GRN, sr.Id , c.Title as Category ,sr.CreatedDate, sr.ReturnNoPacks as Pieces, a.ArticleNumber, p.Name as VendorName, a.ArticleOpenFlag FROM `purchasereturn` sr inner join purchasereturnnumber srn on sr.PurchaseReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN vendor p ON p.Id=sr.VendorId inner join article a on a.Id=sr.ArticleId inner join category c on c.Id=a.CategoryId inner join inward i on i.Id=sr.InwardId left join inwardgrn ig on ig.Id=i.GRN inner join financialyear f on f.Id=ig.FinancialYearId where sr.PurchaseReturnNumber = "' . $id . '") as d '  . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length);
    
        $totalNoPacks = 0;

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

    // public function PrListFromPRONO($id)
    // {
    //     return DB::select('SELECT concat(ig.GRN, "/",f.StartYear,"-",f.EndYear) as GRN, sr.Id , c.Title as Category ,sr.CreatedDate, sr.ReturnNoPacks as Pieces, a.ArticleNumber, p.Name as VendorName, a.ArticleOpenFlag FROM `purchasereturn` sr inner join purchasereturnnumber srn on sr.PurchaseReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN vendor p ON p.Id=sr.VendorId inner join article a on a.Id=sr.ArticleId inner join category c on c.Id=a.CategoryId inner join inward i on i.Id=sr.InwardId left join inwardgrn ig on ig.Id=i.GRN inner join financialyear f on f.Id=ig.FinancialYearId where sr.PurchaseReturnNumber = "' . $id . '"');
    // }
    //Purchase Return Group data get on listing - end

    //Purchase return id wise get the data - start
    public function PrDateRemarkFromPRONO($id)
    {
        return DB::select("SELECT CONCAT( prn.PurchaseReturnNumber, '/', fn.StartYear, '-', fn.EndYear ) AS PRO_Number_FinancialYear, prn.Id, prn.VendorId, prn.Remark, v.Name FROM `purchasereturnnumber` prn INNER JOIN financialyear fn ON fn.Id = prn.FinancialYearId LEFT JOIN vendor v ON v.Id = prn.VendorId where prn.Id ='" . $id . "'");    
    }
    //Purchase return id wise get the data - end

    public function SODataCheckUserWise($UserId, $SONO)
    {
        $getdatauser = DB::select("SELECT u.Email, r.* FROM `users` u inner join userrights r on r.RoleId=u.Role where u.Id = '" . $UserId . "' and r.PageId = 5");
        $userrole = DB::select("SELECT Role FROM `users`where Id='" . $UserId . "'");
        if ($getdatauser[0]->ListRights == 1) {
            $wherecustom = "";
        } else {
            if ($userrole[0]->Role == 2) {
                $wherecustom = "";
            } else {
                $wherecustom = "UserId='" . $UserId . "' and ";
            }
        }
        $checkdata = DB::select("SELECT count(*) as TotalRow FROM `sonumber` where " . $wherecustom . " Id='" . $SONO . "'");
        $array = array();
        if ($checkdata[0]->TotalRow > 0) {
            $array["Rights"] = true;
        } else {
            $array["Rights"] = false;
        }
        return response()->json($array, 200);
    }

    public function getParty()
    {
        return DB::select("SELECT party.Id, party.Name FROM party where Status=1 AND party.UserId IS NOT NULL order by Name asc");
    }

    public function RemainingSOWithParty($Id)
    {
        return  DB::select("SELECT s.SoNumberId, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber , s.ArticleId, s.Status, sn.PartyId, sn.Transporter, sn.UserId, p.Name FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join users u on u.Id=sn.UserId inner join party p on p.Id=sn.PartyId where status in (0) and sn.PartyId='" . $Id . "' group by SoNumberId");
    }

    public function AddSOStatus(Request $request)
    {
        $data = $request->all();
        if ($data['Status']) {
            DB::beginTransaction();
            try {
                DB::table('sostatus')->insertGetId(
                    ['SoId' =>  $data['SoId'], 'UserId' => $data['UserId'], 'PartyId' =>  $data['PartyId'], 'SoStatusDate' => date('Y-m-d')]
                );
                SO::where('SoNumberId', $data['SoId'])->update(array(
                    'Status' => $data['Status']
                ));
                DB::commit();
                return response()->json("SUCCESS", 200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json("", 200);
            }
        }
    }

    public function SOStatusList($UserId)
    {
        $userrole = DB::select("SELECT Role FROM `users`where Id='" . $UserId . "'");
        if ($userrole[0]->Role == 2) {
            $wherecustom = "";
        } else {
            $wherecustom = "where sos.UserId = '" . $UserId . "'";
        }
        return DB::select("SELECT sos.*, u.Name as UserName, p.Name as PartyName, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `sostatus` sos inner join users u on u.Id=sos.UserId inner join sonumber sn on sn.Id=sos.SoId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join party p on p.Id=sos.PartyId " . $wherecustom);
    }

    public function GetSoStatus($id)
    {
        return DB::select("select * from sostatus where Id='" . $id . "'");
    }

    public function DeleteSOStatus($id)
    {
        $getsochallen = DB::select('select u.Id as UserId, son.SoNumber, s.Id as SOID, s.NoPacks, art.Id as ArticleId, art.ArticleOpenFlag, art.ArticleNumber, c.Title, c.Colorflag, art.ArticleColor, art.ArticleSize, art.ArticleRate, s.ArticleRate as SoArticleRate, art.ArticleRatio from so s inner join sonumber son on son.Id=s.SoNumberId inner join users u on u.Id=son.UserId inner join article art on art.Id=s.ArticleId left join po p on p.ArticleId=art.Id left join purchasenumber pur on pur.Id=p.PO_Number inner join category c on c.Id=art.CategoryId where s.SoNumberId=' . $id . ' order by s.Id ASC');
        DB::beginTransaction();
        try {
            foreach ($getsochallen as $vl) {
                $object = (object)$vl;

                $UserId = $vl->UserId;
                $ArticleOpenFlag = $vl->ArticleOpenFlag;
                $SoNumberId = $vl->SoNumber;
                $Colorflag = $vl->Colorflag;
                $ArticleId = $vl->ArticleId;
                $SOID = $vl->SOID;
                $ArticleColor = $vl->ArticleColor;
                $getart = $this->GetSOArticleData($id, $ArticleId, 0);
                $RemainingNoPacks = $getart[0]->SalesNoPacks;
                $CheckSalesNoPacks = $getart[0]->CheckSalesNoPacks;
                $object->RemainingNoPacks = $RemainingNoPacks;
                $object->CheckSalesNoPacks = $CheckSalesNoPacks;
                if ($CheckSalesNoPacks == 0) {
                    DB::table('sostatus')->insertGetId(
                        ['SoNumberId' => $SoNumberId, 'SoId' => $SOID, 'UserId' => $UserId, 'SoStatusDate' => date('Y-m-d h:i:s')]
                    );
                    if ($Colorflag == 0) {
                        if ($ArticleOpenFlag == 0) {
                            $slnopack = DB::select("select SalesNoPacks from inward where ArticleId='" . $ArticleId . "'");
                            $totalsalesnopack = $slnopack[0]->SalesNoPacks + $RemainingNoPacks;
                            DB::table('inward')
                                ->where('ArticleId', $ArticleId)
                                ->update(['SalesNoPacks' => $totalsalesnopack, 'updated_at' => date('Y-m-d h:i:s')]);
                        } else {
                            $getdata = DB::select("SELECT NoPacks FROM `mixnopacks` where ArticleId='" . $ArticleId . "'");
                            $totalsalesnopack = $getdata[0]->NoPacks + $RemainingNoPacks;
                            DB::table('mixnopacks')
                                ->where('ArticleId', $ArticleId)
                                ->update(['NoPacks' => $totalsalesnopack]);
                        }
                    } else {
                        if ($ArticleOpenFlag == 0) {
                            $slnopack = DB::select("select SalesNoPacks from inward where ArticleId='" . $ArticleId . "'");
                            if (strpos($slnopack[0]->SalesNoPacks, ',') !== false) {
                                $NoPacks1 = explode(',', $RemainingNoPacks);
                                $SalesNoPacks = explode(',', $slnopack[0]->SalesNoPacks);
                                $stringcomma = 1;
                            } else {
                                $stringcomma = 0;
                            }
                            $getcolor = json_decode($ArticleColor);
                            if ($stringcomma == 1) {
                                $totalsalesnopack = "";
                                foreach ($getcolor as $key => $vl) {
                                    $totalsalesnopack .= ($NoPacks1[$key] + $SalesNoPacks[$key]) . ",";
                                }
                                $totalsalesnopack = rtrim($totalsalesnopack, ',');
                            } else {
                                $totalsalesnopack = ($RemainingNoPacks + $slnopack[0]->SalesNoPacks);
                            }
                            DB::table('inward')
                                ->where('ArticleId', $ArticleId)
                                ->update(['SalesNoPacks' => $totalsalesnopack, 'updated_at' => date('Y-m-d h:i:s')]);
                        } else {
                            $getdata = DB::select("SELECT NoPacks FROM `mixnopacks` where ArticleId='" . $ArticleId . "'");
                            $totalsalesnopack = $getdata[0]->NoPacks + $RemainingNoPacks;
                            DB::table('mixnopacks')
                                ->where('ArticleId', $ArticleId)
                                ->update(['NoPacks' => $totalsalesnopack]);
                        }
                    }
                } else {
                    continue;
                }
                DB::table('so')
                    ->where('SoNumberId', $id)
                    ->update(['Status' => 1, 'updated_at' => date('Y-m-d h:i:s')]);
            }
            DB::commit();
            return response()->json("SUCCESS", 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json("", 200);
        }
    }

    public function GetSOArticleData($SOId, $Id, $OWID)
    {
        if ($OWID == 0) {
            return DB::select("SELECT art.Id as ArticleId, s.NoPacks, s.OutwardNoPacks as SalesNoPacks, CommaZeroValue(s.OutwardNoPacks) as CheckSalesNoPacks  FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=p.CategoryId inner join so s on s.ArticleId=art.Id inner join articlerate artr on artr.ArticleId=art.Id where art.Id='" . $Id . "' and s.SoNumberId='" . $SOId . "'");
        } else {
            return DB::select("SELECT art.Id as ArticleId, s.NoPacks, s.OutwardNoPacks as SalesNoPacks, CommaZeroValue(s.OutwardNoPacks) as CheckSalesNoPacks, o.OutwardBox, o.OutwardRate, o.OutwardWeight FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=p.CategoryId inner join outward o on o.Id=" . $OWID . " inner join so s on s.ArticleId=art.Id where art.Id='" . $Id . "' and s.SoNumberId='" . $SOId . "'");
        }
    }

    public function GetSoChallen($id)
    {
        $getsochallen = DB::select("select s.Id, u.Name as UserName,pt.UserId as PartyUserId , pt.Name, pt.Address, pt.State , pt.City , pt.PinCode , pt.Country , pt.PhoneNumber, pt.GSTNumber, son.SoDate, son.GSTAmount, son.GSTPercentage, son.GSTType, concat( son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber, son.Transporter, son.Destination, son.Remarks, s.NoPacks, art.Id as ArticleId, art.ArticleOpenFlag, art.ArticleNumber, c.Title, c.Colorflag, art.ArticleColor, art.ArticleSize, art.ArticleRate, s.ArticleRate as SoArticleRate, art.ArticleRatio from so s inner join sonumber son on son.Id=s.SoNumberId inner join users u on u.Id=son.UserId inner join financialyear fn on fn.Id=son.FinancialYearId inner join article art on art.Id=s.ArticleId left join po p on p.ArticleId=art.Id left join purchasenumber pur on pur.Id=p.PO_Number left join category c on c.Id=art.CategoryId  inner join party pt on pt.Id=son.PartyId where s.SoNumberId='" . $id . "' group by s.Id order by c.Title ASC");
        $party  = Users::where('Id', $getsochallen[0]->PartyUserId)->first();
        $SalesPerson = "";
        $SoNumber = $getsochallen[0]->SoNumber;
        if (!is_null($party)) {
            $SalesPerson = $party->Name;
            $SoNumber = str_replace(' ', '', $party->Name . $SoNumber);
        } else {
            $SalesPerson = $getsochallen[0]->UserName;
            $SoNumber = str_replace(' ', '', $getsochallen[0]->UserName . $SoNumber);
        }
        $challendata = [];
        $TotalNoPacks = 0;
        $TotalAmount = 0;
        $TotalSendNoPacks = 0;
        $TotalRemainingNoPacks = 0;
        foreach ($getsochallen as $vl) {
            if (!is_null($vl->City)) {
                $fullAddress = $vl->Address . ', ' . $vl->City . ', ' . $vl->State . ', ' . $vl->Country . ' - ' . $vl->PinCode;
            } else {
                $fullAddress = $vl->Address;
            }
            $Name = $vl->Name;
            $UserName = $vl->UserName;
            $SalesPerson = $SalesPerson;
            $Address = $fullAddress;
            $PhoneNumber = $vl->PhoneNumber;
            $GSTNumber = $vl->GSTNumber;
            $ArticleNumber = $vl->ArticleNumber;
            $ArticleOpenFlag = $vl->ArticleOpenFlag;
            $Title = $vl->Title;
            $SoDate = $vl->SoDate;
            $SoNumber = $SoNumber;
            $Transporter = $vl->Transporter;
            $Destination = $vl->Destination;
            $Remarks = $vl->Remarks;
            $NoPacks = $vl->NoPacks;
            $Colorflag = $vl->Colorflag;
            $ArticleId = $vl->ArticleId;
            $GSTAmount = $vl->GSTAmount;
            $GSTPercentage = $vl->GSTPercentage;
            $GSTType = $vl->GSTType;
            $getart = $this->GetSOArticleData($id, $ArticleId, 0);
            $RemainingNoPacks = $getart[0]->SalesNoPacks;
            $SendNoPacks  = "";
            if (strpos($RemainingNoPacks, ',') !== false) {
                $remnopack = explode(',', $RemainingNoPacks);
                $nopackcheck = explode(',', $NoPacks);
                foreach ($remnopack as $key => $rvl) {
                    $SendNoPacks .= ($nopackcheck[$key] - $rvl) . ',';
                }
                $SendNoPacks = rtrim($SendNoPacks, ',');
                $TotalSendNoPacks += array_sum(explode(",", $SendNoPacks));
                $TotalRemainingNoPacks += array_sum(explode(",", $RemainingNoPacks));
            } else {
                $SendNoPacks = ($NoPacks - $RemainingNoPacks);
                $TotalSendNoPacks += $SendNoPacks;
                $TotalRemainingNoPacks += $RemainingNoPacks;
            }
            if ($Colorflag == 0) {
                if ($ArticleOpenFlag == 0) {
                    $ArticleRatio = $vl->ArticleRatio;
                    $TotalNoPacks += $NoPacks;
                } else {
                    $TotalNoPacks += $NoPacks;
                }
            } else {
                if ($ArticleOpenFlag == 0) {
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
                $ArticleRate = $vl->SoArticleRate;
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
                $ArticleRatio = "";

				$TotalCQty = "";

                $ArticleRate = $vl->SoArticleRate;
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


            

            $challendata[] = json_decode(json_encode(array("SalesPerson" => $SalesPerson, "allkeys" => $allkey , "allvalues" => $allvalue , "UserName" => $UserName, "SoDate" => $SoDate, "SoNumber" => $SoNumber, "Remarks" => $Remarks, "Name" => $Name, "PhoneNumber" => $PhoneNumber, "Address" => $Address, "GSTNumber" => $GSTNumber, "ArticleNumber" => $ArticleNumber, "Title" => $Title,"ColorQty" => $C, "ArticleRatio" => $ArticleRatio, "QuantityInSet" => $NoPacks, "TotalQty" => $TotalQty ,'TotalQtyWithColor' => $TotalCQty ,"ArticleRate" => number_format($ArticleRate, 2), "Amount" => number_format($Amount, 2), "ArticleColor" => $ArticleColor, "ArticleSize" => $ArticleSize, "Transporter" => $Transporter, "Destination" => $Destination, "ArticleId" => $ArticleId, "SendNoPacks" => $SendNoPacks
              , "RemainingNoPacks" => $RemainingNoPacks)), false);
        }

        $TotalFinalAmount = 0;
        $GSTLabel = "";
        $GSTValue = 0;
        $CGSTValue = 0;
        $SGSTValue = 0;
        if ($TotalFinalAmount == 0) {
            $TotalFinalAmount = $TotalAmount;
        }
        if ($GSTPercentage != "" || $GSTAmount != "") {
            if ($GSTPercentage > 0) {
                $GSTLabel = "GST " . $GSTPercentage . "%";
                $GSTValue = (($TotalFinalAmount * $GSTPercentage) / 100);
                $CGSTValue = ($GSTValue / 2);
                $SGSTValue = ($GSTValue / 2);
                $TotalGSTValue = round(($GSTValue / 2), 2)  * 2;
                $TotalFinalAmount = ($TotalFinalAmount + $TotalGSTValue);
            } else {
                $GSTValue = number_format($GSTAmount, 2);
                $GSTValue1 = $GSTAmount;
                $TotalFinalAmount = ($TotalFinalAmount + $GSTValue1);
                $GSTLabel = "GST Amount";
            }
        }
        $as  = array($challendata, array("TotalNoPacks" => $TotalNoPacks, "TotalSendNoPacks" => $TotalSendNoPacks, "TotalRemainingNoPacks" => $TotalRemainingNoPacks, "TotalAmount" => number_format($TotalAmount, 2), "RoundOff" => $this->splitter(number_format($TotalFinalAmount, 2, '.', '')), "TotalFinalAmount" => number_format($TotalFinalAmount, 2), "GSTLabel" => $GSTLabel, "GSTPercentage" => (int)$GSTPercentage,  "GSTValue" => $GSTValue, "CGSTValue" => number_format($CGSTValue, 2), "SGSTValue" => number_format($SGSTValue, 2), "GSTType" => $GSTType));
        return $as;
    }

    function splitter($val)
    {
        $totalroundamount = $val;
        $str = (string) $val;
        $splitted = explode(".", $str);
        $num = (int) $splitted[1];
        $number_var = "";
        $adjust_amount = "";
        if ($num != 0) {
            $roundoff_check = true;
            if ($num >= 50) {
                $number_var = "Up";
                $am_set = (100 - $num);
                if ($am_set > 10) {
                    $adjust_amount = "0." . $am_set;
                } else {
                    $adjust_amount = "0.0" . $am_set;
                }
            } else {
                $number_var = "Down";

                if ($num > 10) {
                    $adjust_amount = "0." . $num;
                } else {
                    $adjust_amount = "0.0" . $num;
                }
            }
            $totalroundamount = number_format(round($totalroundamount), 2);
        } else {
            $number_var = "Zero";
            $roundoff_check = false;
            $adjust_amount = 0;
        }
        return array("Roundoff" => $roundoff_check, 'RoundValueSign' => $number_var, 'TotalRoundAmount' => $totalroundamount, 'AdjustAmount' => $adjust_amount);
    }

    public function SalesReturnOutlet($id)
    {
        return DB::select("SELECT p.Name, otn.OutletPartyId as Id FROM `outletnumber` otn inner join party p on p.Id=otn.OutletPartyId where otn.PartyId = '" . $id . "' AND p.Status=1 AND p.UserId IS NOT NULL group by otn.OutletPartyId");
    }

    public function SalesReturnArticle($id)
    {
        return DB::select("SELECT a.ArticleNumber, a.Id FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id  where o.PartyId='" . $id . "' group by s.ArticleId union SELECT ArticleNumber, Id FROM `article` where ArticleOpenFlag=1");
    }

    public function salesreturn_outletarticle($id)
    {
        return DB::select("SELECT a.ArticleNumber, a.Id FROM `outletnumber` oln inner join outlet o on o.OutletNumberId=oln.Id left join article a on a.Id=o.ArticleId where oln.OutletPartyId='" . $id . "' group by o.ArticleId");
    }

    public function SalesReturn_ArticletoOutward($PartyId, $ArticleId, $Type)
    {
        if ($Type == 1) {
            return DB::select("SELECT a.ArticleNumber, a.Id, son.Id as OutwardNumberId, son.OutletNumber , concat(son.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber FROM `outletnumber` son inner join outlet s on s.OutletNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join financialyear fn on fn.Id=son.FinancialYearId  where son.OutletPartyId='" . $PartyId . "' and s.ArticleId = '" . $ArticleId . "' group by OutletNumber");
        } else {
            return DB::select("SELECT a.ArticleNumber, a.Id, own.Id as OutwardNumberId, own.OutwardNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, IFNULL(tpo.TransportStatus, 1) as TransportStatus FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId left join transportoutlet tpo on tpo.OutwardNumberId=own.Id where o.PartyId='" . $PartyId . "' and o.ArticleId = '" . $ArticleId . "' and IFNULL(tpo.TransportStatus, 1)=1  group by OutwardNumber");
        }
    }

    Public function SalesReturn_OutwardGetData($PartyId, $ArticleId, $OutwardNumberId)
    {
        $partydata = DB::select("select OutletAssign from party where Id=" . $PartyId);
        if ($partydata[0]->OutletAssign == 1) {
            $pendingoutlet = 0;
            // $data = DB::select("select ar.ArticleNumber, CASE WHEN (oi.ArticleColor IS NULL) THEN ar.ArticleColor ELSE oi.ArticleColor END AS `ArticleColor`, ar.ArticleSize, ar.ArticleRatio, ar.ArticleOpenFlag, CASE WHEN (art.ArticleRate IS NULL) THEN ar.ArticleRate ELSE art.ArticleRate END  as ArticleRate , p.OutletArticleRate as OutletArticleRate, c.Colorflag, c.Title as Category ,  ddd.ArticleId, ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks + d.OutletSalesReturn_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks + d.OutletSalesReturn_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN D.OutletSalesReturn_NoPacks IS NULL THEN '0' ELSE D.OutletSalesReturn_NoPacks END) as OutletSalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `transportoutwardpacks` onp inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='" . $ArticleId . "' and onp.PartyId='" . $PartyId . "' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='" . $ArticleId . "' and onp.PartyId='" . $PartyId . "' group by onp.ColorId) AS B ON A.ColorId=B.ColorId LEFT JOIN ( SELECT sum(srp.NoPacks) as SalesReturn_NoPacks, srp.ColorId, srp.ArticleId, srp.Id, c.Colorflag  FROM `salesreturnpacks` srp inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='" . $ArticleId . "' and srp.PartyId='" . $PartyId . "' group by srp.ColorId) AS C ON A.ColorId=C.ColorId LEFT JOIN ( select sum(f.NoPacks) as OutletSalesReturn_NoPacks, f.ColorId, f.ArticleId, f.OutletId, f.Id, f.Colorflag from (SELECT srp.NoPacks, srp.ColorId, srp.ArticleId, srp.OutletId, srp.Id, c.Colorflag FROM `outletsalesreturnpacks` srp inner join outletsalesreturn osr on osr.OutletId=srp.OutletId inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='" . $ArticleId . "' and osr.OutletPartyId='" . $PartyId . "' group by srp.Id) as f group by f.ColorId) AS D ON A.ColorId=D.ColorId) as d group by d.ArticleId) as ddd inner join article ar on ar.Id=ddd.ArticleId inner join category c on c.Id=ar.CategoryId left join articlerate art on art.ArticleId=ar.Id left join party p on p.Id='" . $PartyId . "' left join outletimport oi on oi.ArticleId='" . $ArticleId . "' and oi.PartyId = '" . $PartyId . "'");
            $allRecords = DB::select('(select `outletsalesreturn`.`NoPacks` as `NoPacks`, 2 as type, `outletsalesreturnnumber`.`CreatedDate` as `SortDate` from `outletsalesreturn` inner join `outletsalesreturnnumber` on `outletsalesreturn`.`SalesReturnNumber` = `outletsalesreturnnumber`.`Id` where (`ArticleId` = ' . $ArticleId . ' and `outletsalesreturn`.`OutletPartyId` = ' . $PartyId . ')) union (select `outlet`.`NoPacks` as `NoPacks`, 1 as type, `outletnumber`.`CreatedDate` as `SortDate` from `outlet` inner join `outletnumber` on `outlet`.`OutletNumberId` = `outletnumber`.`Id` where (`ArticleId` = ' . $ArticleId . ' and `outletnumber`.`PartyId` = ' . $PartyId . ')) union (select `outward`.`NoPacks` as `NoPacks`, 0 as type, `outwardnumber`.`created_at` as `SortDate` from `outward` inner join `transportoutlet` on `outward`.`OutwardNumberId` = `transportoutlet`.`OutwardNumberId` inner join `outwardnumber` on `outward`.`OutwardNumberId` = `outwardnumber`.`Id` where (`ArticleId` = ' . $ArticleId . ' and `transportoutlet`.`TransportStatus` = 1 and `outward`.`PartyId` = ' . $PartyId . ')) union (select `salesreturn`.`NoPacks` as `NoPacks`, 3 as type ,`salesreturnnumber`.`CreatedDate` as `SortDate` from `outward` inner join `salesreturn` on `salesreturn`.`OutwardId` = `outward`.`Id` inner join `salesreturnnumber` on `salesreturnnumber`.`Id` = `salesreturn`.`SalesReturnNumber` where (`outward`.`PartyId` = ' . $PartyId . ' and `outward`.`ArticleId` = ' . $ArticleId . ')) order by `SortDate` asc');
            if (!isset($allRecords[0])) {
                $outletArticle = Outletimport::where('ArticleId', $ArticleId)->first();
                if ($outletArticle) {
                    $outletArticleColors = json_decode($outletArticle->ArticleColor);
                } else {
                    $articleRecord = Article::where('Id', $ArticleId)->first();
                    $outletArticleColors  = json_decode($articleRecord->ArticleColor);
                }
                $outletArticleColors =  (array)$outletArticleColors;
                if (count($outletArticleColors) != 0) {
                    $SalesNoPacks = [];
                    foreach ($outletArticleColors as $makearray) {
                        array_push($SalesNoPacks, 0);
                    }
                    $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $ArticleId)->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
                    $TotalTransportOutwardpacks = 0;
                    if (count($transportOutwardpacks) != 0) {
                        $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                        $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
                        foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                            $outletArticle = Outletimport::where('ArticleId', $ArticleId)->first();
                            if ($outletArticle) {
                                $articleColors = json_decode($outletArticle->ArticleColor);
                            } else {
                                $article = Article::select('ArticleColor')->where('Id', $ArticleId)->first();
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
                    $pendingoutlet =  $newimplodeSalesNoPacks;
                } else {
                    $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks')->where('ArticleId', $ArticleId)->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
                    $TotalTransportOutwardpacks = 0;
                    if (count($transportOutwardpacks) != 0) {
                        $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                        $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
                        foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                            $TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
                        }
                    }
                    $pendingoutlet =  $TotalTransportOutwardpacks;
                }
            } else {
                $outletArticle = Outletimport::where('ArticleId', $ArticleId)->first();
                if ($outletArticle) {
                    $outletArticleColors = json_decode($outletArticle->ArticleColor);
                } else {
                    $articleRecord = Article::where('Id', $ArticleId)->first();
                    $outletArticleColors  = json_decode($articleRecord->ArticleColor);
                }
                $outletArticleColors =  (array)$outletArticleColors;
                if (count($outletArticleColors) != 0) {
                    $SalesNoPacks = [];
                    foreach (explode(",", $allRecords[0]->NoPacks) as $makearray) {
                        array_push($SalesNoPacks, 0);
                    }
                    $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $ArticleId)->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
                    $TotalTransportOutwardpacks = 0;
                    if (count($transportOutwardpacks) != 0) {
                        $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                        $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
                        foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                            $outletArticle = Outletimport::where('ArticleId', $ArticleId)->first();
                            if ($outletArticle) {
                                $articleColors = json_decode($outletArticle->ArticleColor);
                            } else {
                                $article = Article::select('ArticleColor')->where('Id', $ArticleId)->first();
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
                    foreach ($allRecords as  $allRecord) {
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
                    $pendingoutlet =  $newimplodeSalesNoPacks;
                } else {
                    $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks')->where('ArticleId', $ArticleId)->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
                    $TotalTransportOutwardpacks = 0;
                    if (count($transportOutwardpacks) != 0) {
                        $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                        $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
                        foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                            $TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
                        }
                    }
                    $TotalInwardPacks = $TotalTransportOutwardpacks;
                    $TotalOutwardPacks = 0;
                    foreach ($allRecords as  $allRecord) {
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
                    $pendingoutlet =  $TotalInwardPacks - $TotalOutwardPacks;
                }
            }
            // $pendingoutlet = DB::select("select ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `transportoutwardpacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='" . $ArticleId . "' and onp.PartyId='" . $PartyId . "' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='" . $ArticleId . "' and onp.PartyId='" . $PartyId . "' group by onp.ColorId) AS B ON A.ColorId=B.ColorId LEFT JOIN ( SELECT sum(srp.NoPacks) as SalesReturn_NoPacks, srp.ColorId, srp.ArticleId, srp.Id, c.Colorflag  FROM `salesreturnpacks` srp left join po p on p.ArticleId = srp.ArticleId inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='" . $ArticleId . "' and srp.PartyId='" . $PartyId . "' group by srp.ColorId) AS C ON A.ColorId=C.ColorId) as d group by d.ArticleId) as ddd");
        }
        $getdata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutwardId, o.NoPacks as OutwardNoPacks, o.OutwardRate FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where o.PartyId='" . $PartyId . "' and o.ArticleId = '" . $ArticleId . "' and o.OutwardNumberId='" . $OutwardNumberId . "' group by OutwardNumber");
        if ($getdata[0]->ArticleOpenFlag == 0) {
            if ($getdata[0]->Colorflag == 1) {
                $as = DB::select("select GROUP_CONCAT(CONCAT(ddd.NoPacks) SEPARATOR ',') as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `salesreturnpacks` where OutwardId= '" . $getdata[0]->OutwardId . "' group by ColorId) as ddd");
                $SalesReturnNoPacks = "";
                if ($as[0]->SalesReturnNoPacks != "") {
                    $SalesReturnNoPacks = $as[0]->SalesReturnNoPacks;
                    $npacks = explode(",", $getdata[0]->OutwardNoPacks);
                    $spacks = explode(",", $as[0]->SalesReturnNoPacks);
                    $newdata  = "";
                    foreach ($npacks as $key => $vl) {
                        $newdata .= ($vl - $spacks[$key]) . ',';
                    }
                    $newdata = rtrim($newdata, ',');
                } else {
                    $newdata = $getdata[0]->OutwardNoPacks;
                }
                foreach ($getdata as $key => $val) {
                    $object = (object)$val;
                    if ($SalesReturnNoPacks != "") {
                        $object->SalesReturnNoPacks = $SalesReturnNoPacks;
                    } else {
                        $object->SalesReturnNoPacks = "";
                    }
                    $object->OutwardNoPacks_New = $newdata;
                    if ($partydata[0]->OutletAssign == 1) {
                        $object->Outlet_Total_PNoPacks = $pendingoutlet;
                        $object->OutletParty = 1;
                    } else {
                        $object->OutletParty = 0;
                    }
                }
            } else {
                $as = DB::select("select ddd.NoPacks as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `salesreturnpacks` where OutwardId= '" . $getdata[0]->OutwardId . "' group by ColorId) as ddd");
                $spacks = "";
                if (isset($as[0]->SalesReturnNoPacks) != "") {
                    $npacks = $getdata[0]->OutwardNoPacks;
                    $spacks = $as[0]->SalesReturnNoPacks;
                    $newdata = $npacks - $spacks;
                } else {
                    $newdata = $getdata[0]->OutwardNoPacks;
                }
                foreach ($getdata as $key => $val) {
                    $object = (object)$val;

                    if ($spacks != "") {
                        $object->SalesReturnNoPacks = $spacks;
                    } else {
                        $object->SalesReturnNoPacks = "";
                    }
                    $object->OutwardNoPacks_New = $newdata;
                    if ($partydata[0]->OutletAssign == 1) {
                        $object->Outlet_Total_PNoPacks = $pendingoutlet;
                        $object->OutletParty = 1;
                    } else {
                        $object->OutletParty = 0;
                    }
                }
            }
        } else {
            $as = DB::select("select ddd.NoPacks as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `salesreturnpacks` where OutwardId= '" . $getdata[0]->OutwardId . "' group by ColorId) as ddd");
            foreach ($getdata as $key => $val) {
                $object = (object)$val;
                if (isset($as[0]->SalesReturnNoPacks) != "") {
                    $spacks = $as[0]->SalesReturnNoPacks;
                    $newdata = $getdata[0]->OutwardNoPacks - $as[0]->SalesReturnNoPacks;
                    $object->SalesReturnNoPacks = $newdata;
                } else {
                    $newdata = $getdata[0]->OutwardNoPacks;
                    $object->SalesReturnNoPacks = $newdata;
                }
                $object->OutwardNoPacks_New = $newdata;
                if ($partydata[0]->OutletAssign == 1) {
                    if ($pendingoutlet != 0) {
                        $object->Outlet_Total_PNoPacks = $pendingoutlet;
                    } else {
                        $object->Outlet_Total_PNoPacks = 0;
                    }
                    $object->OutletParty = 1;
                } else {
                    $object->OutletParty = 0;
                }
            }
        }
        return $getdata;
    }
    public function SalesReturn_OutletGetData($PartyId, $ArticleId, $OutwardNumberId)
    {
        $OutletNumberData = OutletNumber::where('OutletPartyId', $PartyId)->where('Id', $OutwardNumberId)->first();
        if (Outletimport::where('ArticleId', $ArticleId)->where('PartyId', $OutletNumberData->PartyId)->first()) {
            $getdata = DB::select("SELECT a.ArticleNumber, s.ArticleRate, oi.ArticleColor AS `ArticleColor`, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, a.StyleDescription, a.ArticleStatus, a.CategoryId, a.SubCategoryId, a.SeriesId, a.BrandId, a.Orderset, a.OpeningStock, c.Colorflag, c.Title as Category, s.Id as OutletId, s.NoPacks as OutletNoPacks, son.PartyId FROM `outletnumber` son inner join outlet s on s.OutletNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join financialyear fn on fn.Id=son.FinancialYearId inner join category c on c.Id=a.CategoryId left join outletimport oi on oi.ArticleId=s.ArticleId where son.OutletPartyId='" . $PartyId . "' and s.ArticleId = '" . $ArticleId . "' and son.Id='" . $OutwardNumberId . "' group by OutletNumber");
        } else {
            $getdata = DB::select("SELECT a.ArticleNumber, s.ArticleRate, a.ArticleColor AS `ArticleColor`, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, a.StyleDescription, a.ArticleStatus, a.CategoryId, a.SubCategoryId, a.SeriesId, a.BrandId, a.Orderset, a.OpeningStock, c.Colorflag, c.Title as Category, s.Id as OutletId, s.NoPacks as OutletNoPacks, son.PartyId FROM `outletnumber` son inner join outlet s on s.OutletNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join financialyear fn on fn.Id=son.FinancialYearId inner join category c on c.Id=a.CategoryId left join outletimport oi on oi.ArticleId=s.ArticleId where son.OutletPartyId='" . $PartyId . "' and s.ArticleId = '" . $ArticleId . "' and son.Id='" . $OutwardNumberId . "' group by OutletNumber");
        }
        // $getdata = DB::select("SELECT a.ArticleNumber, s.ArticleRate, CASE WHEN (oi.ArticleColor IS NULL) THEN a.ArticleColor ELSE oi.ArticleColor END AS `ArticleColor`, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, a.StyleDescription, a.ArticleStatus, a.CategoryId, a.SubCategoryId, a.SeriesId, a.BrandId, a.Orderset, a.OpeningStock, c.Colorflag, c.Title as Category, s.Id as OutletId, s.NoPacks as OutletNoPacks FROM `outletnumber` son inner join outlet s on s.OutletNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join financialyear fn on fn.Id=son.FinancialYearId inner join category c on c.Id=a.CategoryId left join outletimport oi on oi.ArticleId=s.ArticleId where son.OutletPartyId='" . $PartyId . "' and s.ArticleId = '" . $ArticleId . "' and son.Id='" . $OutwardNumberId . "' group by OutletNumber");
        if ($getdata[0]->ArticleOpenFlag == 0) {
            if ($getdata[0]->Colorflag == 1) {
                $as = DB::select("select GROUP_CONCAT(CONCAT(ddd.NoPacks) SEPARATOR ',') as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `outletsalesreturnpacks` where OutletId= '" . $getdata[0]->OutletId . "' group by ColorId) as ddd");
                $s_as = DB::select("select GROUP_CONCAT(CONCAT(ddd.NoPacks) SEPARATOR ',') as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `salesreturnpacks` where OutwardId= '" . $getdata[0]->OutletId . "' group by ColorId) as ddd");
                $SalesReturnNoPacks = "";
                if ($as[0]->SalesReturnNoPacks != "") {
                    $SalesReturnNoPacks = $as[0]->SalesReturnNoPacks;
                    $npacks = explode(",", $getdata[0]->OutletNoPacks);
                    $spacks = explode(",", $as[0]->SalesReturnNoPacks);
                    $s_spacks = explode(",", $s_as[0]->SalesReturnNoPacks);
                    $newdata  = "";
                    foreach ($npacks as $key => $vl) {
                        $newdata .= ($vl - $spacks[$key]) . ',';
                    }
                    $newdata = rtrim($newdata, ',');
                } else {
                    $newdata = $getdata[0]->OutletNoPacks;
                }
                // if ($s_as[0]->SalesReturxanNoPacks != "") {
                //     $SalesReturnNoPacks = $s_as[0]->SalesReturnNoPacks;
                //     $npacks = explode(",", $newdata);
                //     $s_spacks = explode(",", $s_as[0]->SalesReturnNoPacks);
                //     $newdata1  = "";
                //     foreach ($npacks as $key => $vl) {
                //         $newdata1 .= ($vl - $s_spacks[$key]) . ',';
                //     }
                //     $newdata = rtrim($newdata1, ',');
                // } else {
                //     $newdata = $getdata[0]->OutletNoPacks;
                // }
                if ($s_as[0]->SalesReturnNoPacks == "") {
                    $newdata = $getdata[0]->OutletNoPacks;
                }
                foreach ($getdata as $key => $val) {
                    $object = (object)$val;
                    $object->OutletNoPacks = $newdata;
                    if ($SalesReturnNoPacks != "") {
                        $object->SalesReturnNoPacks = $SalesReturnNoPacks;
                    } else {
                        $object->SalesReturnNoPacks = "";
                    }
                    $OutletNoPacks = [];
                    $NewOutletNoPacks = explode(",", $newdata);
                    $NewSalesReturnNoPacks = explode(",", $object->SalesReturnNoPacks);
                    foreach ($NewOutletNoPacks as $makearray) {
                        array_push($OutletNoPacks, 0);
                    }
                    $count = 0;
                    if ($NewSalesReturnNoPacks[0] == "") {
                        $object = (object)$val;
                        $object->OutletNoPacks = $newdata;
                        if ($SalesReturnNoPacks != "") {
                            $object->SalesReturnNoPacks = $SalesReturnNoPacks;
                        } else {
                            $object->SalesReturnNoPacks = "";
                        }
                    } else {
                        foreach ($NewOutletNoPacks as $newOutletNoPack) {
                            $OutletNoPacks[$count] = $NewOutletNoPacks[$count] - $NewSalesReturnNoPacks[$count];
                            $count = $count + 1;
                        }
                        $object->OutletNoPacks =  implode(",", $OutletNoPacks);
                    }
                }
                return $getdata;
            } else {
                $as = DB::select("select ddd.NoPacks as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `outletsalesreturnpacks` where OutletId= '" . $getdata[0]->OutletId . "' group by ColorId) as ddd");
                $s_as = DB::select("select ddd.NoPacks as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `salesreturnpacks` where OutwardId= '" . $getdata[0]->OutletId . "' group by ColorId) as ddd");
                $spacks = "";
                if (isset($as[0]->SalesReturnNoPacks) != "") {
                    $npacks = $getdata[0]->OutletNoPacks;
                    $spacks = $as[0]->SalesReturnNoPacks;
                    $s_spacks = $s_as[0]->SalesReturnNoPacks;
                    $newdata = $npacks - $spacks;
                } else {
                    $newdata = $getdata[0]->OutletNoPacks;
                }
                foreach ($getdata as $key => $val) {
                    $object = (object)$val;
                    $object->OutletNoPacks = $newdata;
                    if ($spacks != "") {
                        $object->SalesReturnNoPacks = $spacks;
                        $object->OutletNoPacks = $newdata - $object->SalesReturnNoPacks;
                    } else {
                        $object->SalesReturnNoPacks = "";
                    }
                }
            }
        } else {
            $as = DB::select("select ddd.NoPacks as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `outletsalesreturnpacks` where OutletId= '" . $getdata[0]->OutletId . "' group by ColorId) as ddd");
            $s_as = DB::select("select ddd.NoPacks as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `salesreturnpacks` where OutwardId= '" . $getdata[0]->OutletId . "' group by ColorId) as ddd");
            foreach ($getdata as $key => $val) {
                $object = (object)$val;
                if (isset($as[0]->SalesReturnNoPacks) != "") {
                    $spacks = $as[0]->SalesReturnNoPacks;
                    $object->SalesReturnNoPacks = $as[0]->SalesReturnNoPacks;
                    $object->before = $getdata[0]->OutletNoPacks;
                    $object->OutletNoPacks = $getdata[0]->OutletNoPacks - $as[0]->SalesReturnNoPacks;
                } else {
                    $newdata = $getdata[0]->OutletNoPacks;
                    $object->SalesReturnNoPacks = "";
                }
            }
        }

        return $getdata;
    }

    public function SalesReturnGetArticleData($ArtId)
    {
        return DB::select("SELECT a.*,c.Colorflag FROM `article` a inner join category c on c.Id=a.CategoryId where a.Id = '" . $ArtId . "'");
    }

    public function GenerateSRNumber($UserId)
    {
        $array = array();
        $fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
        $srnumberdata = DB::select('SELECT Id, FinancialYearId, SalesReturnNumber From salesreturnnumber order by Id desc limit 0,1');
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

    public function AddSalesReturn(Request $request)
{
    $data = $request->all();
    $OutletPartyId = 0;
    if ($data['OutletPartyId'] != "") {
        $OutletPartyId = $data['OutletPartyId'];
    }
    $userName = Users::where('Id', $data['UserId'])->first();
    if ($data['SRNumberId'] == "Add") {
        $generate_SRNUMBER = $this->GenerateSRNumber($data['UserId']);
        $SR_Number = $generate_SRNUMBER['SR_Number'];
        $SR_Number_Financial_Id = $generate_SRNUMBER['SR_Number_Financial_Id'];
        $SRNumberId = DB::table('salesreturnnumber')->insertGetId(
            ['SalesReturnNumber' =>  $SR_Number, "FinancialYearId" => $SR_Number_Financial_Id, 'PartyId' =>  $data['PartyId'], 'OutletPartyId' => $OutletPartyId, 'Remarks' => $data['Remark'], 'CreatedDate' => date('Y-m-d H:i:s')]
        );
        $salesRetRec = DB::select("select concat($SR_Number,'/', fn.StartYear,'-',fn.EndYear) as Salesreturnnumber from salesreturnnumber srn inner join financialyear fn on fn.Id=srn.FinancialYearId where srn.Id= '" . $SRNumberId . "'");
        UserLogs::create([
            'Module' => 'Sales Return',
            'ModuleNumberId' => $SRNumberId,
            'LogType' => 'Created',
            'LogDescription' => $userName['Name'] . " " . 'created sales return with SalesReturn Number' . " " . $salesRetRec[0]->Salesreturnnumber,
            'UserId' => $userName['Id'],
            'updated_at' => null
        ]);
        $artRateRecord = Article::where('Id', $data["ArticleId"])->first();
        UserLogs::create([
            'Module' => 'Sales Return',
            'ModuleNumberId' => $SRNumberId,
            'LogType' => 'Updated',
            'LogDescription' => $userName['Name'] . " " . 'added article ' . $artRateRecord->ArticleNumber . ' in sales return with SalesReturn Number' . " " . $salesRetRec[0]->Salesreturnnumber,
            'UserId' => $userName['Id'],
            'updated_at' => null
        ]);
    } else {
        $checksonumber = DB::select("SELECT SalesReturnNumber FROM `salesreturnnumber` where Id ='" . $data['SRNumberId'] . "'");
        if (!empty($checksonumber)) {
            $SR_Number = $checksonumber[0]->SalesReturnNumber;
            $SRNumberId = $data['SRNumberId'];
            DB::table('salesreturnnumber')
                ->where('Id', $SRNumberId)
                ->update(['PartyId' =>  $data['PartyId'], 'OutletPartyId' => $OutletPartyId, 'Remarks' => $data['Remark']]);
            $salesRetRec = DB::select("select concat($SR_Number,'/', fn.StartYear,'-',fn.EndYear) as Salesreturnnumber from salesreturnnumber srn inner join financialyear fn on fn.Id=srn.FinancialYearId where srn.Id= '" . $SRNumberId . "'");
            $artRateRecord = Article::where('Id', $data["ArticleId"])->first();
            UserLogs::create([
                'Module' => 'Sales Return',
                'ModuleNumberId' => $SRNumberId,
                'LogType' => 'Updated',
                'LogDescription' => $userName['Name'] . " " . 'added article ' . $artRateRecord->ArticleNumber . ' in sales return with SalesReturn Number' . " " . $salesRetRec[0]->Salesreturnnumber,
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);
        }
    }
    if ($data['OutletPartyId'] != "") {
        if ($data['ArticleOpenFlag'] == 1) {
            if (isset($data['NoPacksNew']) == "") {
                return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
            }
            if ($data['partyflag'] == true) {
                if ($data['NoPacks_TotalOutlet'] < $data['NoPacksNew']) {
                    return response()->json(array("id" => "", "OutletNoOfSetNotMatch" => "true"), 200);
                }
            }
            // if ($data['NoPacks'] < $data['NoPacksNew']) {
            //     return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
            // }
            $getdata = DB::select("SELECT * FROM `mixnopacks` where ArticleId='" . $data["ArticleId"] . "'");
            $getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutwardId, o.NoPacks as OutwardNoPacks FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId inner join category c on c.Id=a.CategoryId where o.PartyId='" . $data["PartyId"] . "' and o.ArticleId = '" . $data["ArticleId"] . "' and o.OutwardNumberId='" . $data["OutwardNumberId"] . "' group by OutwardNumber");
            $OutwardId = $getalldata[0]->OutwardId;
            if (!empty($getdata)) {
                $InwardNoPacks = $getdata[0]->NoPacks;
                $NoPacks = $data["NoPacksNew"];
                $totalnopacks = ($InwardNoPacks + $NoPacks);
                DB::beginTransaction();
                try {
                    DB::table('mixnopacks')
                        ->where('ArticleId', $data['ArticleId'])
                        ->update(['NoPacks' => $totalnopacks]);
                    $salesreturnId = DB::table('salesreturn')->insertGetId(
                        ["SalesReturnNumber" => $SRNumberId, 'OutwardId' => $OutwardId, 'ArticleId' => $data['ArticleId'], 'NoPacks' =>  $NoPacks, 'UserId' =>  $data['UserId'], 'CreatedDate' => date('Y-m-d H:i:s')]
                    );
                    DB::table('salesreturnpacks')->insertGetId(
                        ['SalesReturnId' => $salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId' => 0, 'OutwardId' => $OutwardId, 'NoPacks' => $NoPacks, 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]
                    );
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
            $checkoutlet = DB::select("SELECT count(*) as Total FROM `outletimport` where ArticleId='" . $data['ArticleId'] . "'");

            if ($checkoutlet[0]->Total > 0) {
                return response()->json(array("id" => "", "StockUpload" => "true"), 200);
            }
            $getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutletId, o.NoPacks as OutletNoPacks FROM article a inner join outlet o on o.ArticleId=a.Id left join outletnumber own on own.Id=o.OutletNumberId inner join financialyear fn on fn.Id=own.FinancialYearId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where own.Id='" . $data["OutwardNumberId"] . "' and o.ArticleId = '" . $data["ArticleId"] . "' and o.OutletNumberId='" . $data["OutwardNumberId"] . "' group by OutletNumber");
            $getdata = DB::select("SELECT SalesNoPacks FROM `inward` where ArticleId='" . $data['ArticleId'] . "'");
            $InwardSalesNoPacks = $getdata[0]->SalesNoPacks;
            if ($getalldata[0]->ArticleOpenFlag == 0 && $getalldata[0]->Colorflag == 0) {
                if ($data['NoPacksNew'] == "") {
                    return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
                }
                if ($data['partyflag'] == true) {
                    if ($data['NoPacks_TotalOutlet'] < $data['NoPacksNew']) {
                        return response()->json(array("id" => "", "OutletNoOfSetNotMatch" => "true"), 200);
                    }
                }
                $as = DB::select("select sum(ddd.NoPacks) as TotalNoPacks from (select NoPacks FROM `salesreturnpacks` where OutwardId= '" . $getalldata[0]->OutletId . "' and Outletflag='1' group by NoPacks) as ddd");
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
                $as = DB::select("select GROUP_CONCAT(CONCAT(ddd.NoPacks) SEPARATOR ',') as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `salesreturnpacks` where OutwardId= '" . $getalldata[0]->OutletId . "' and Outletflag='1' group by ColorId) as ddd");
                if ($as[0]->SalesReturnNoPacks != "") {
                    $npacks = explode(",", $getalldata[0]->OutletNoPacks);
                    $spacks = explode(",", $as[0]->SalesReturnNoPacks);
                    $newdata  = "";
                    foreach ($npacks as $key => $vl) {
                        $newdata .= ($vl - $spacks[$key]) . ',';
                    }
                    $newdata = rtrim($newdata, ',');
                } else {
                    $newdata = $getalldata[0]->OutletNoPacks;
                }
            }
            $search = $getalldata[0]->OutletNoPacks;
            $OutwardId = $getalldata[0]->OutletId;
            $NoPacks = "";
            $SalesNoPacks = "";
            $UpdateInwardNoPacks = "";
            $searchString = ',';
            if (strpos($search, $searchString) !== false) {
                $InwardSalesNoPacks = explode(',', $InwardSalesNoPacks);
                $stringcomma = 1;
            } else {
                $search;
                $InwardSalesNoPacks = $InwardSalesNoPacks;
                $stringcomma = 0;
            }
            if ($data['ArticleColorFlag'] == "Yes") {
                foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                    $numberofpacks = $vl["Id"];
                    $InwardSalesNoPacks_VL = $InwardSalesNoPacks[$key];
                    if ($data["NoPacksNew_" . $numberofpacks] != "") {
                        if ($stringcomma == 1) {
                            if ($data['partyflag'] == true) {
                                if ($data['NoPacks_TotalOutlet_' . $numberofpacks] < $data['NoPacksNew_' . $numberofpacks]) {
                                    return response()->json(array("id" => "", "OutletNoOfSetNotMatch" => "true"), 200);
                                }
                            } else {
                                if ($data["NoPacks_" . $numberofpacks] < $data["NoPacksNew_" . $numberofpacks]) {
                                    return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                                }
                            }
                            $SalesNoPacks .= ($data["NoPacks_" . $numberofpacks] - $data["NoPacksNew_" . $numberofpacks]) . ",";
                            $UpdateInwardNoPacks .= ($InwardSalesNoPacks_VL + $data["NoPacksNew_" . $numberofpacks]) . ",";
                        } else {
                            if ($data['partyflag'] == true) {
                                if ($data['NoPacks_TotalOutlet_' . $numberofpacks] < $data['NoPacksNew_' . $numberofpacks]) {
                                    return response()->json(array("id" => "", "OutletNoOfSetNotMatch" => "true"), 200);
                                }
                            } else {
                                if ($data["NoPacks_" . $numberofpacks] < $data["NoPacksNew_" . $numberofpacks]) {
                                    return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                                }
                            }
                            $SalesNoPacks .= ($data["NoPacks_" . $numberofpacks] - $data["NoPacksNew_" . $numberofpacks]) . ",";
                            $UpdateInwardNoPacks .= ($InwardSalesNoPacks + $data["NoPacksNew_" . $numberofpacks]) . ",";
                        }
                        $NoPacks .= $data["NoPacksNew_" . $numberofpacks] . ",";
                    } else {
                        $NoPacks .= "0,";
                        $SalesNoPacks .= $data["NoPacks_" . $numberofpacks] . ",";
                        $UpdateInwardNoPacks .= ($InwardSalesNoPacks_VL + $data["NoPacksNew_" . $numberofpacks]) . ",";
                    }
                }
                $NoPacks = rtrim($NoPacks, ',');
            } else {
                if (isset($data['NoPacksNew'])) {
                    $NoPacks = $data['NoPacksNew'];
                    $SalesNoPacks .= ($data["NoPacks"] - $data['NoPacksNew']);
                    $UpdateInwardNoPacks = ($InwardSalesNoPacks + $NoPacks) . ",";
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
                $salesreturnId = DB::table('salesreturn')->insertGetId(
                    ["SalesReturnNumber" => $SRNumberId, 'OutwardId' => $OutwardId, 'Outletflag' => 1, 'ArticleId' => $data['ArticleId'], 'NoPacks' => $NoPacks, 'UserId' => $data['UserId'], 'CreatedDate' => date('Y-m-d H:i:s')]
                );
                DB::table('inward')
                    ->where('ArticleId', $data['ArticleId'])
                    ->update(['SalesNoPacks' => $UpdateInwardNoPacks]);
                if ($data['ArticleOpenFlag'] == 0) {
                    if (strpos($NoPacks, ',') !== false) {
                        $NoPacks = explode(',', $NoPacks);
                        foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                            $numberofpacks = $vl["Id"];
                            DB::table('salesreturnpacks')->insertGetId(
                                ['SalesReturnId' => $salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId' => $numberofpacks, 'OutwardId' => $OutwardId, 'Outletflag' => 1, 'NoPacks' => $NoPacks[$key], 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]
                            );
                        }
                    } else {
                        foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                            $numberofpacks = $vl["Id"];
                            DB::table('salesreturnpacks')->insertGetId(
                                ['SalesReturnId' => $salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId' => $numberofpacks, 'OutwardId' => $OutwardId, 'Outletflag' => 1, 'NoPacks' => $NoPacks, 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]
                            );
                        }
                    }
                } else {
                    DB::table('salesreturnpacks')->insertGetId(
                        ['SalesReturnId' => $salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId' => 0, 'OutwardId' => $OutwardId, 'Outletflag' => 1, 'NoPacks' => $NoPacks, 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]
                    );
                }
                DB::commit();
                return response()->json(array("SRNumberId" => $SRNumberId, "id" => "SUCCESS"), 200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(array("id" => ""), 200);
            }
        }
    } else {
        if ($data['ArticleOpenFlag'] == 1) {
            if (isset($data['NoPacksNew']) == "") {
                return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
            }
            if ($data['partyflag'] == true) {
                if ($data['NoPacks_TotalOutlet'] < $data['NoPacksNew']) {
                    return response()->json(array("id" => "", "OutletNoOfSetNotMatch" => "true"), 200);
                }
            }
            if ($data['NoPacks'] < $data['NoPacksNew']) {
                return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
            }
            $getdata = DB::select("SELECT * FROM `mixnopacks` where ArticleId='" . $data["ArticleId"] . "'");
            $getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutwardId, o.NoPacks as OutwardNoPacks FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId inner join category c on c.Id=a.CategoryId where o.PartyId='" . $data["PartyId"] . "' and o.ArticleId = '" . $data["ArticleId"] . "' and o.OutwardNumberId='" . $data["OutwardNumberId"] . "' group by OutwardNumber");
            $OutwardId = $getalldata[0]->OutwardId;
            if (!empty($getdata)) {
                $InwardNoPacks = $getdata[0]->NoPacks;
                $NoPacks = $data["NoPacksNew"];
                $totalnopacks = ($InwardNoPacks + $NoPacks);
                DB::beginTransaction();
                try {
                    
                    
                    
                    
                    
                    
                    //Nitin Art Stock Status
                          $isOutlet = DB::select("SELECT OutletAssign FROM `party` where Id ='" . $data['PartyId'] . "'");
                            if ($isOutlet[0]->OutletAssign == 1) { 
                            // Fetch the current SalesNoPacks value
                                $currentSalesNoPacks = DB::table('artstockstatus')
                                    ->where(['outletId' => $data['PartyId']])
                                    ->where(['ArticleId' => $data["ArticleId"]])
                                    ->value('SalesNoPacks');
                                
                                    
                                    $artD = DB::table('article')
                                        ->join('category', 'article.CategoryId', '=', 'category.Id')
                                        ->where('article.Id', $data["ArticleId"])
                                        ->first();
    
                                // Calculate the new SalesNoPacks value by adding the new value to the current value
                                $newSalesNoPacks = $currentSalesNoPacks - $NoPacks;
                                
                                // Perform the updateOrInsert operation with the new SalesNoPacks value
                                DB::table('artstockstatus')->updateOrInsert(
                                    [
                                        'outletId' => $data['PartyId'],
                                        'ArticleId' => $data['ArticleId']
                                    ],
                                    [
                                        'Title' => $artD->Title,
                                        'ArticleNumber' => $artD->ArticleNumber,
                                        'SalesNoPacks' => $newSalesNoPacks,
                                        'TotalPieces' => $newSalesNoPacks
                                    ]
                                );
                            }
                    //close
                    
                    
                    
                    
                    
                    
                    
                    
                    DB::table('mixnopacks')
                        ->where('ArticleId', $data['ArticleId'])
                        ->update(['NoPacks' => $totalnopacks]);
                    $salesreturnId = DB::table('salesreturn')->insertGetId(
                        ["SalesReturnNumber" => $SRNumberId, 'OutwardId' => $OutwardId, 'Outletflag' => 0, 'ArticleId' => $data['ArticleId'], 'NoPacks' => $NoPacks, 'UserId' => $data['UserId'], 'OutwardRate' => $data['OutwardRate'], 'CreatedDate' => date('Y-m-d H:i:s')]
                    );
                    DB::table('salesreturnpacks')->insertGetId(
                        ['SalesReturnId' => $salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId' => 0, 'OutwardId' => $OutwardId, 'Outletflag' => 0, 'NoPacks' => $NoPacks, 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]
                    );
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
            $getdata = DB::select("SELECT SalesNoPacks FROM `inward` where ArticleId='" . $data['ArticleId'] . "'");
            $InwardSalesNoPacks = $getdata[0]->SalesNoPacks;
            $getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutwardId, o.NoPacks as OutwardNoPacks FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where o.PartyId='" . $data["PartyId"] . "' and o.ArticleId = '" . $data["ArticleId"] . "' and o.OutwardNumberId='" . $data["OutwardNumberId"] . "' group by OutwardNumber");
            if ($getalldata[0]->ArticleOpenFlag == 0 && $getalldata[0]->Colorflag == 0) {
                if ($data['NoPacksNew'] == "") {
                    return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
                }
                if ($data['partyflag'] == true) {
                    if ($data['NoPacks_TotalOutlet'] < $data['NoPacksNew']) {
                        return response()->json(array("id" => "", "OutletNoOfSetNotMatch" => "true"), 200);
                    }
                }
                $as = DB::select("select sum(ddd.NoPacks) as TotalNoPacks from (select NoPacks FROM `salesreturnpacks` where OutwardId= '" . $getalldata[0]->OutwardId . "' group by NoPacks) as ddd");
                $spacks = "";
                if (isset($as[0]->TotalNoPacks) != "") {
                    $npacks = $getalldata[0]->OutwardNoPacks;
                    $spacks = $as[0]->TotalNoPacks;
                    $newdata = $npacks - $spacks;
                    $NoPacksNew = $data['NoPacksNew'];
                    if ($newdata < $NoPacksNew) {
                        return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                    }
                } else {
                    $newdata = $getalldata[0]->OutwardNoPacks;
                }
            } else {
                $as = DB::select("select GROUP_CONCAT(CONCAT(ddd.NoPacks) SEPARATOR ',') as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `salesreturnpacks` where OutwardId= '" . $getalldata[0]->OutwardId . "' group by ColorId) as ddd");
                if ($as[0]->SalesReturnNoPacks != "") {
                    $npacks = explode(",", $getalldata[0]->OutwardNoPacks);
                    $spacks = explode(",", $as[0]->SalesReturnNoPacks);
                    $newdata  = "";
                    foreach ($npacks as $key => $vl) {
                        $newdata .= ($vl - $spacks[$key]) . ',';
                    }
                    $newdata = rtrim($newdata, ',');
                } else {
                    $newdata = $getalldata[0]->OutwardNoPacks;
                }
            }
            $search = $getalldata[0]->OutwardNoPacks;
            $OutwardId = $getalldata[0]->OutwardId;
            $NoPacks = "";
            $SalesNoPacks = "";
            $UpdateInwardNoPacks = "";
            $searchString = ',';
            if (strpos($search, $searchString) !== false) {
                $InwardSalesNoPacks = explode(',', $InwardSalesNoPacks);
                $stringcomma = 1;
            } else {
                $search;
                $InwardSalesNoPacks = $InwardSalesNoPacks;
                $stringcomma = 0;
            }
            if ($data['ArticleColorFlag'] == "Yes") {
                foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                    $numberofpacks = $vl["Id"];
                    $InwardSalesNoPacks_VL = $InwardSalesNoPacks[$key];
                    if ($data["NoPacksNew_" . $numberofpacks] != "") {
                        if ($stringcomma == 1) {
                            if ($data['partyflag'] == true) {
                                if ($data['NoPacks_TotalOutlet_' . $numberofpacks] < $data['NoPacksNew_' . $numberofpacks]) {
                                    return response()->json(array("id" => "", "OutletNoOfSetNotMatch" => "true"), 200);
                                }
                            } else {
                                if ($data["NoPacks_" . $numberofpacks] < $data["NoPacksNew_" . $numberofpacks]) {
                                    return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                                }
                            }
                            $SalesNoPacks .= ($data["NoPacks_" . $numberofpacks] - $data["NoPacksNew_" . $numberofpacks]) . ",";
                            $UpdateInwardNoPacks .= ($InwardSalesNoPacks_VL + $data["NoPacksNew_" . $numberofpacks]) . ",";
                        } else {
                            if ($data['partyflag'] == true) {
                                if ($data['NoPacks_TotalOutlet_' . $numberofpacks] < $data['NoPacksNew_' . $numberofpacks]) {
                                    return response()->json(array("id" => "", "OutletNoOfSetNotMatch" => "true"), 200);
                                }
                            } else {
                                if ($data["NoPacks_" . $numberofpacks] < $data["NoPacksNew_" . $numberofpacks]) {
                                    return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                                }
                            }
                            $SalesNoPacks .= ($data["NoPacks_" . $numberofpacks] - $data["NoPacksNew_" . $numberofpacks]) . ",";
                            $UpdateInwardNoPacks .= ($InwardSalesNoPacks + $data["NoPacksNew_" . $numberofpacks]) . ",";
                        }
                        $NoPacks .= $data["NoPacksNew_" . $numberofpacks] . ",";
                    } else {
                        $NoPacks .= "0,";
                        $SalesNoPacks .= $data["NoPacks_" . $numberofpacks] . ",";
                        $UpdateInwardNoPacks .= ($InwardSalesNoPacks_VL + $data["NoPacksNew_" . $numberofpacks]) . ",";
                    }
                }
                $NoPacks = rtrim($NoPacks, ',');
                
                
                
                
                
            //Nitin Art Stock Status
                $isOutlet = DB::select("SELECT OutletAssign FROM `party` where Id ='" . $data['PartyId'] . "'");
                if ($isOutlet[0]->OutletAssign == 1) { 
                    $currentSalesNoPacks = DB::table('artstockstatus')
                            ->where(['outletId' => $data['PartyId']])
                            ->where(['ArticleId' => $data['ArticleId']])
                            ->value('SalesNoPacks');
                        
                        // Check if $currentSalesNoPacks is not empty
                        
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
                                    'outletId' => $data['PartyId'],
                                    'ArticleId' => $data['ArticleId']
                                ],
                                [
                                    'Title' => $artD->Title,
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
                    $UpdateInwardNoPacks = ($InwardSalesNoPacks + $NoPacks) . ",";
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
                $salesreturnId = DB::table('salesreturn')->insertGetId(
                    ["SalesReturnNumber" => $SRNumberId, 'OutwardId' => $OutwardId, 'ArticleId' => $data['ArticleId'], 'NoPacks' => $NoPacks, 'UserId' => $data['UserId'], 'OutwardRate' => $data['OutwardRate'], 'CreatedDate' => date('Y-m-d H:i:s')]
                );
                DB::table('inward')
                    ->where('ArticleId', $data['ArticleId'])
                    ->update(['SalesNoPacks' => $UpdateInwardNoPacks]);
                if ($data['ArticleOpenFlag'] == 0) {
                    if (strpos($NoPacks, ',') !== false) {
                        $NoPacks = explode(',', $NoPacks);
                        foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                            $numberofpacks = $vl["Id"];
                            DB::table('salesreturnpacks')->insertGetId(
                                ['SalesReturnId' => $salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId' => $numberofpacks, 'OutwardId' => $OutwardId, 'NoPacks' => $NoPacks[$key], 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]
                            );
                        }
                    } else {
                        foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                            $numberofpacks = $vl["Id"];
                            DB::table('salesreturnpacks')->insertGetId(
                                ['SalesReturnId' => $salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId' => $numberofpacks, 'OutwardId' => $OutwardId, 'NoPacks' => $NoPacks, 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]
                            );
                        }
                    }
                } else {
                    DB::table('salesreturnpacks')->insertGetId(
                        ['SalesReturnId' => $salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId' => 0, 'OutwardId' => $OutwardId, 'NoPacks' => $NoPacks, 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]
                    );
                }
                DB::commit();
                return response()->json(array("SRNumberId" => $SRNumberId, "id" => "SUCCESS"), 200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(array("id" => ""), 200);
            }
        }
    }
}


    public function getsalesreturn($id)
    {
        return DB::select("SELECT concat(otn.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, slr.Id, p.Name, a.ArticleNumber, FirstCharacterConcat(u.Name), slr.NoPacks, slr.CreatedDate FROM `salesreturn` slr inner join party p on p.Id=slr.PartyId inner join article a on a.Id=slr.ArticleId inner join users u on u.Id=slr.UserId inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=otn.FinancialYearId");
    }

    public function PostSalesReturn(Request $request)
    {
        $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select("select count(*) as Total from (SELECT slr.Id FROM `salesreturn` slr inner join salesreturnnumber  sln on sln.Id=slr.SalesReturnNumber inner join party p on p.Id=sln.PartyId inner join article a on a.Id=slr.ArticleId inner join users u on u.Id=slr.UserId inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=otn.FinancialYearId group by slr.SalesReturnNumber) as d");
        $vnTotal = $vnddataTotal[0]->Total;
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "WHERE d.SalesReturnNumber like '%" . $search['value'] . "%' OR d.Name like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%' OR cast(d.CreatedDate as char) like '%" . $search['value'] . "%'";
            $vnddataTotalFilter = DB::select("select count(*) as Total from (select * from (SELECT GetTotalSrOrderPieces(son.Id) as TotalSRPieces, DATE_FORMAT(son.CreatedDate, '%d/%m/%Y') as CreatedDate, son.Id, p.Name, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, concat(son.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SalesReturnNumber FROM `salesreturn` s inner join article a on a.Id=s.ArticleId left join salesreturnnumber son on s.SalesReturnNumber=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=s.UserId inner join outward o on o.Id=s.OutwardId group by s.SalesReturnNumber) as ddd order by ddd.Id desc) as d " . $searchstring);
            $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
        } else {
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
        if ($data["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
        }
        $vnddata = DB::select("select d.* from (select * from (SELECT GetTotalSrOrderPieces(son.Id) as TotalSRPieces, partyuser.Name As SalesPerson, DATE_FORMAT(son.CreatedDate, '%d/%m/%Y') as CreatedDate, son.Id, p.Name, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, concat(son.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SalesReturnNumber FROM `salesreturn` s inner join article a on a.Id=s.ArticleId left join salesreturnnumber son on s.SalesReturnNumber=son.Id inner join party p on p.Id=son.PartyId left join users partyuser on partyuser.Id=p.UserId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=s.UserId inner join outward o on o.Id=s.OutwardId group by s.SalesReturnNumber) as ddd order by ddd.Id desc) as d " . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length);
        $totalNoPacks = 0;
        foreach ($vnddata as $vnd) {
            $getSalesReturns = Salesreturn::where('SalesReturnNumber', $vnd->Id)->get();
            foreach ($getSalesReturns as $getSalesReturn) {
                if (strpos($getSalesReturn->NoPacks, ',') !== false) {
                    $totalNoPacks += array_sum(explode(",", $getSalesReturn->NoPacks));
                } else {
                    $singlecountNoPacks = $getSalesReturn->NoPacks;
                    $totalNoPacks += $getSalesReturn->NoPacks;
                }
                $vnd->TotalNoPacks = $totalNoPacks;
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
    public function PurchaseReturnArticle($id)
    {
        return  DB::select("select ddd.* from (SELECT p.ArticleId, a.ArticleNumber,i.NoPacks, CommaZeroValue(i.SalesNoPacks) as CheckNoPacks, i.SalesNoPacks FROM `po` p inner join article a on a.Id=p.ArticleId inner join inward i on i.ArticleId=a.Id where p.VendorId = '" . $id . "' and a.ArticleOpenFlag = 0) as ddd where ddd.CheckNoPacks=0 UNION select Id, ArticleNumber, '','','' from article where ArticleOpenFlag = 1");
    }

    public function PurchaseReturn_GetInwardNumber($VendorId, $ArticleId)
    {
        //added by yashvi
        return DB::select("SELECT a.Id, a.ArticleNumber, inw.Id as InwardNumberId,inw.GRN, concat(inwn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRNNumber FROM inwardgrn inwn inner join inward inw on inw.GRN = inwn.Id inner join article a on a.Id=inw.ArticleId  inner join financialyear fn on fn.Id=inwn.FinancialYearId left join po p on p.ArticleId = a.Id where inw.ArticleId='" . $ArticleId . "' and p.VendorId='" . $VendorId . "' group by GRN");
        // return DB::select("SELECT a.Id, a.ArticleNumber, inw.Id as InwardNumberId,inw.GRN, concat(inwn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRNNumber FROM inwardgrn inwn inner join inward inw on inw.GRN = inwn.Id inner join article a on a.Id=inw.ArticleId  inner join financialyear fn on fn.Id=inwn.FinancialYearId left join po p on p.ArticleId = a.Id where inw.ArticleId='" . $ArticleId . "' and p.VendorId='" . $VendorId . "' group by GRN");
    }

    public function PurcahseReturn_InwardGetData($VendorId, $ArticleId, $InwardNumberId)
    {
        $getdata = DB::select("SELECT a.*,c.Colorflag, c.Title as Category, inw.Id as InwardId,inw.NoPacks, inw.SalesNoPacks, inw.GRN, concat(inw.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRNNumber FROM inwardgrn inwn inner join inward inw on inw.GRN = inwn.Id inner join article a on a.Id=inw.ArticleId inner join financialyear fn on fn.Id=inwn.FinancialYearId left join po p on p.ArticleId = a.Id inner join category c on c.Id=a.CategoryId where inw.ArticleId='" . $ArticleId . "' and p.VendorId='" . $VendorId . "' and inw.Id = '" . $InwardNumberId . "' group by GRN");
        if ($getdata[0]->ArticleOpenFlag == 1) {
            $as = DB::select("SELECT sum(ReturnNoPacks) as NoPacks FROM `purchasereturn` where InwardId='" . $InwardNumberId . "' and VendorId ='" . $VendorId . "' and ArticleId = '" . $ArticleId . "'");
            foreach ($getdata as $key => $val) {
                $object = (object)$val;
                if (isset($as[0]->NoPacks) != "") {
                    $newdata = $getdata[0]->NoPacks - $as[0]->NoPacks;
                    $object->NoPacks = $newdata;
                } else {
                    $newdata = $getdata[0]->NoPacks;
                    $object->NoPacks = $newdata;
                }
                $object->NoPacks = $newdata;
            }
        }
        return $getdata;
    }

    public function GeneratePRNumber($UserId)
    {
        $array = array();
        $fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
        $prnumberdata = DB::select('SELECT Id, FinancialYearId, PurchaseReturnNumber From purchasereturnnumber order by Id desc limit 0,1');
        if (count($prnumberdata) > 0) {
            if ($fin_yr[0]->Id > $prnumberdata[0]->FinancialYearId) {
                $array["PR_Number"] = 1;
                $array["PR_Number_Financial_Id"] = $fin_yr[0]->Id;
                $array["PR_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
                return $array;
            } else {
                $array["PR_Number"] = ($prnumberdata[0]->PurchaseReturnNumber) + 1;
                $array["PR_Number_Financial_Id"] = $fin_yr[0]->Id;
                $array["PR_Number_Financial"] = ($prnumberdata[0]->PurchaseReturnNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
                return $array;
            }
        } else {
            $array["PR_Number"] = 1;
            $array["PR_Number_Financial_Id"] = $fin_yr[0]->Id;
            $array["PR_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
            return $array;
        }
    }

    public function AddPurchaseReturn(Request $request)
    {
        $data = $request->all();
        $userName = Users::where('Id', $data['UserId'])->first();
        if ($data['PRNumberId'] == "Add") {
            $generate_PRNUMBER = $this->GeneratePRNumber($data['UserId']);
            $PR_Number = $generate_PRNUMBER['PR_Number'];
            $PR_Number_Financial_Id = $generate_PRNUMBER['PR_Number_Financial_Id'];
            $PRNumberId = DB::table('purchasereturnnumber')->insertGetId(
                ['PurchaseReturnNumber' =>  $PR_Number, "FinancialYearId" => $PR_Number_Financial_Id, 'VendorId' => $data['VendorId'], 'Remark' => $data['Remark'], 'CreatedDate' => date('Y-m-d H:i:s')]
            );
            $purchaseRetRec = DB::select("select concat($PR_Number,'/', fn.StartYear,'-',fn.EndYear) as Purchasereturnnumber from purchasereturnnumber prn inner join financialyear fn on fn.Id=prn.FinancialYearId where prn.Id= '" . $PRNumberId . "'");
            UserLogs::create([
                'Module' => 'Purchase Return',
                'ModuleNumberId' => $PRNumberId,
                'LogType' => 'Created',
                'LogDescription' => $userName['Name'] . " " . 'created purchase return with PurchaseReturn Number' . " " . $purchaseRetRec[0]->Purchasereturnnumber,
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);
            $artRateRecord = Article::where('Id', $data['ArticleId'])->first();
            UserLogs::create([
                'Module' => 'Purchase Return',
                'ModuleNumberId' => $PRNumberId,
                'LogType' => 'Updated',
                'LogDescription' => $userName['Name'] . " " . 'added article ' . $artRateRecord->ArticleNumber . ' in purchase return with PurchaseReturn Number' . " " . $purchaseRetRec[0]->Purchasereturnnumber,
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);
        } else {
            $checksonumber = DB::select("SELECT PurchaseReturnNumber FROM `purchasereturnnumber` where Id ='" . $data['PRNumberId'] . "'");
            if (!empty($checksonumber)) {
                $PR_Number = $checksonumber[0]->PurchaseReturnNumber;
                $PRNumberId = $data['PRNumberId'];
                DB::table('purchasereturnnumber')
                    ->where('Id', $PRNumberId)
                    ->update(['VendorId' =>  $data['VendorId'], 'Remark' => $data['Remark']]);
            }
            $artRateRecord = Article::where('Id', $data['ArticleId'])->first();
            $purchaseRetRec = DB::select("select concat($PR_Number,'/', fn.StartYear,'-',fn.EndYear) as Purchasereturnnumber from purchasereturnnumber prn inner join financialyear fn on fn.Id=prn.FinancialYearId where prn.Id= '" . $PRNumberId . "'");
            UserLogs::create([
                'Module' => 'Purchase Return',
                'ModuleNumberId' => $PRNumberId,
                'LogType' => 'Updated',
                'LogDescription' => $userName['Name'] . " " . 'added article ' . $artRateRecord->ArticleNumber . ' in purchase return with PurchaseReturn Number' . " " . $purchaseRetRec[0]->Purchasereturnnumber,
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);
        }
        if ($data["ArticleOpenFlag"] == 1) {
            $NoPacks = "";
            $SalesNoPacks = "";
            if (isset($data['NoPacksNew'])) {
                $NoPacks .= $data['NoPacksNew'];
                if ($data['NoPacks'] < $data['NoPacksNew']) {
                    return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                }
                $SalesNoPacks .= ($data['NoPacks'] - $data['NoPacksNew']);
            } else {
                return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
            }
            $articlerate = DB::select('SELECT ArticleRate FROM `articlerate` where ArticleId="' . $data['ArticleId'] . '"');
            DB::table('mixnopacks')
                ->where('ArticleId', $data['ArticleId'])
                ->update(['NoPacks' => $SalesNoPacks]);
            DB::table('purchasereturn')->insertGetId(
                ["PurchaseReturnNumber" => $PRNumberId, 'VendorId' =>  $data['VendorId'], 'ArticleId' =>  $data['ArticleId'], 'InwardId' => $data['InwardNumberId'], 'UserId' => $data['UserId'], 'TotalNoPacks' => $data['NoPacks'], 'RemainingNoPacks' => $SalesNoPacks, 'ReturnNoPacks' => $NoPacks, 'ArticleRate' => $articlerate[0]->ArticleRate, 'CreatedDate' => date('Y-m-d H:i:s')]
            );
            
            
            
            
            
            
            //Nitin Art Stock Status
            $currentSalesNoPacks = DB::table('artstockstatus')
                    ->where(['outletId' => 0])
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
                            'outletId' => 0,
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
                            'outletId' => 0,
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
            
            
            
            
            
            return response()->json(array("PRNumberId" => $PRNumberId, "id" => "SUCCESS"), 200);
        } else {
            $dataresult = DB::select('SELECT (inw.SalesNoPacks) as SalesNoPacks, c.Colorflag FROM `po` p inner join inward inw on inw.ArticleId=p.ArticleId inner join category c on c.Id=p.CategoryId where p.ArticleId="' . $data['ArticleId'] . '"');
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
            $SalesNoPacks = "";
            if ($Colorflag == 1) {
                foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                    $numberofpacks = $vl["Id"];
                    if ($data["NoPacksNew_" . $numberofpacks] != "") {
                        if ($stringcomma == 1) {
                            if ($string[$key] < $data["NoPacksNew_" . $numberofpacks]) {
                                return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                            }
                            $SalesNoPacks .= ($string[$key] - $data["NoPacksNew_" . $numberofpacks]) . ",";
                        } else {
                            if ($search < $data["NoPacksNew_" . $numberofpacks]) {
                                return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                            }
                            $SalesNoPacks .= ($search - $data["NoPacksNew_" . $numberofpacks]) . ",";
                        }
                        $NoPacks .= $data["NoPacksNew_" . $numberofpacks] . ",";
                    } else {
                        $NoPacks .= "0,";
                        $SalesNoPacks .= $string[$key] . ",";
                    }
                }
            } else {
                if (isset($data['NoPacksNew'])) {
                    $NoPacks .= $data['NoPacksNew'];
                    if ($search < $data['NoPacksNew']) {
                        return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                    }
                    $SalesNoPacks .= ($search - $data['NoPacksNew']);
                } else {
                    return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
                }
            }
            
            $NoPacks = rtrim($NoPacks, ',');
            
            
            //Nitin Art Stock Status
            $currentSalesNoPacks = DB::table('artstockstatus')
                    ->where(['outletId' => 0])
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
                            'outletId' => 0,
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
                            'outletId' => 0,
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
            
           
           
           
           
            $SalesNoPacks = rtrim($SalesNoPacks, ',');
            $CheckSalesNoPacks = explode(',', $NoPacks);
            $tmp = array_filter($CheckSalesNoPacks);
            if (empty($tmp)) {
                return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
            }
            DB::table('purchasereturn')->insertGetId(
                ["PurchaseReturnNumber" => $PRNumberId, 'VendorId' =>  $data['VendorId'], 'ArticleId' =>  $data['ArticleId'], 'InwardId' => $data['InwardNumberId'], 'UserId' => $data['UserId'], 'TotalNoPacks' => $search, 'RemainingNoPacks' => $SalesNoPacks, 'ReturnNoPacks' => $NoPacks, 'ArticleRate' => $data['ArticleRate'], 'CreatedDate' => date('Y-m-d H:i:s')]
            );
            DB::table('inward')
                ->where('ArticleId', $data['ArticleId'])
                ->update(['SalesNoPacks' => $SalesNoPacks]);
            return response()->json(array("PRNumberId" => $PRNumberId, "id" => "SUCCESS"), 200);
        }
    }

    public function DeletePurchaseReturn($purid, $LoggedId)
    {
        $purchaseReturnNumber = DB::table('purchasereturnnumber')->where('Id', $purid)->first();
        $purchaseRecords = DB::table('purchasereturn')->where('PurchaseReturnNumber', $purchaseReturnNumber->Id)->get();
        foreach ($purchaseRecords as $purchaseRecord) {
            $article = Article::where('Id', $purchaseRecord->ArticleId)->first();
            $inward = Inward::where('Id', $purchaseRecord->InwardId)->first();
            if ($article->ArticleOpenFlag == 1) {
                $mixnopacks = DB::select('SELECT NoPacks FROM `mixnopacks` where ArticleId="' . $purchaseRecord->ArticleId . '"');
                $mixNoPacksGot = $mixnopacks[0]->NoPacks;
                DB::table('mixnopacks')
                    ->where('ArticleId', $purchaseRecord->ArticleId)
                    ->update(['NoPacks' => $mixNoPacksGot + (int)$purchaseRecord->ReturnNoPacks]);
                $inward = Inward::where('Id', $purchaseRecord->InwardId)->update(['SalesNoPacks' => (int)$inward->SalesNoPacks + (int)$purchaseRecord->ReturnNoPacks]);
            } else {
                if (strpos($inward->SalesNoPacks, ',') !== false) {
                    $SalesNoPacks = explode(',', $inward->SalesNoPacks);
                    $purchaseReturnPacksArray = explode(',', $purchaseRecord->ReturnNoPacks);
                    $count = 0;
                    foreach ($purchaseReturnPacksArray as $purcret) {
                        $SalesNoPacks[$count] = $SalesNoPacks[$count] +  $purcret;
                        $count = $count + 1;
                    }
                    $inward = Inward::where('Id', $purchaseRecord->InwardId)->update(['SalesNoPacks' => implode(',', $SalesNoPacks)]);
                } else {
                    $inward = Inward::where('Id', $purchaseRecord->InwardId)->update(['SalesNoPacks' => (int)$inward->SalesNoPacks + (int)$purchaseRecord->ReturnNoPacks]);
                }
            }
            $userName = Users::where('Id', $LoggedId)->first();
            $puReRec = DB::select("select concat(prn.PurchaseReturnNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseReturnnumber from purchasereturnnumber prn inner join financialyear fn on fn.Id=prn.FinancialYearId where prn.Id= '" . $purid . "'");
            $purchaseRecords = DB::table('purchasereturn')->where('Id', $purchaseRecord->Id)->delete();
        }
        UserLogs::create([
            'Module' => 'Purchase Return',
            'ModuleNumberId' => $purchaseReturnNumber->Id,
            'LogType' => 'Deleted',
            'LogDescription' => $userName['Name'] . " " . 'deleted purchase return with PurchaseReturn Number' . " " . $puReRec[0]->PurchaseReturnnumber,
            'UserId' => $userName['Id'],
            'updated_at' => null
        ]);
        return response()->json(array("id" => "SUCCESS"), 200);
    }



    public function DeletePurchaseReturnRecord($id, $LoggedId)
    {
        $articledata = DB::select("SELECT pur.VendorId, pur.ArticleId, pur.InwardId, pur.ArticleId, pur.ReturnNoPacks, a.ArticleNumber, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, c.Colorflag FROM `purchasereturn` pur inner join article a on a.Id=pur.ArticleId inner join category c on c.Id=a.CategoryId where pur.Id ='" . $id . "'");
        $PurchaseReturnNoPacks = $articledata[0]->ReturnNoPacks;
        $ArticleColor = json_decode($articledata[0]->ArticleColor);
        $userName = Users::where('Id', $LoggedId)->first();
        $puReRec = DB::select("select prn.Id as PurchaseReturnNumberId, a.ArticleNumber, concat(prn.PurchaseReturnNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseReturnnumber from purchasereturn p inner join purchasereturnnumber prn on prn.Id=p.PurchaseReturnNumber inner join article a on a.Id=p.ArticleId inner join financialyear fn on fn.Id=prn.FinancialYearId where p.Id= '" . $id . "'");
    
        if ($articledata[0]->ArticleOpenFlag == 1) {
            $mixnopacks = DB::select('SELECT NoPacks FROM `mixnopacks` where ArticleId="' . $articledata[0]->ArticleId . '"');
    
            $InwardNoPacks = $mixnopacks[0]->NoPacks;
    
            $PurchaseNoPacks = ($InwardNoPacks + $PurchaseReturnNoPacks);
    
            DB::beginTransaction();
            try {
                 
                 
            
            //Nitin Art Stock Status
                $currentSalesNoPacks = DB::table('artstockstatus')
                        ->where(['outletId' => 0])
                        ->where(['ArticleId' => $articledata[0]->ArticleId])
                        ->value('SalesNoPacks');
                
                $NPacks = $currentSalesNoPacks + $PurchaseReturnNoPacks;
                
                DB::table('artstockstatus')->where('artstockstatus.ArticleId', $articledata[0]->ArticleId)->where('outletId', 0)->update(['SalesNoPacks' => $NPacks, 'TotalPieces' => $NPacks]);
           //close
                DB::table('mixnopacks')
                    ->where('ArticleId', $articledata[0]->ArticleId)
                    ->update(['NoPacks' => $PurchaseNoPacks]);
                DB::table('purchasereturn')
                    ->where('Id', '=', $id)
                    ->delete();
                UserLogs::create([
                    'Module' => 'Purchase Return',
                    'ModuleNumberId' => $puReRec[0]->PurchaseReturnNumberId,
                    'LogType' => 'Deleted',
                    'LogDescription' => $userName->Name . ' deleted article ' . $puReRec[0]->ArticleNumber . ' from PurchaseReturn Number ' . $puReRec[0]->PurchaseReturnnumber,
                    'UserId' => $userName->Id,
                    'updated_at' => null
                ]);
                DB::commit();
                return response()->json(array("id" => "SUCCESS"), 200);
            } catch (\Exception $e) {
                return $e;
                DB::rollback();
                return response()->json(array("Id" => ""), 200);
            }
            //}
        } else {
            //return $articledata[0]->ArticleId; exit;
            $dataresult = DB::select('SELECT inw.SalesNoPacks, c.Colorflag FROM `po` p inner join inward inw on inw.ArticleId=p.ArticleId inner join category c on c.Id=p.CategoryId where p.ArticleId="' . $articledata[0]->ArticleId . '"');
            $Colorflag = $dataresult[0]->Colorflag;
            $search = $dataresult[0]->SalesNoPacks;
    
            if (strpos($search, ',') !== false) {
                $string = explode(',', $search);
                $PurchaseReturnNoPacks = explode(',', $PurchaseReturnNoPacks);
                $stringcomma = 1;
            } else {
                $stringcomma = 0;
            }
            $PurchaseNoPacks = "";
            if ($Colorflag == 1) {
                foreach ($ArticleColor as $key => $vl) {
                    $PurchaseRNoPacks = $PurchaseReturnNoPacks[$key];
                    if ($stringcomma == 1) {
                        $PurchaseNoPacks .= ($string[$key] + $PurchaseRNoPacks) . ",";
                    } else {
                        $PurchaseNoPacks .= ($search + $PurchaseReturnNoPacks) . ",";
                    }
                }
            } else {
                $PurchaseNoPacks .= ($search + $PurchaseReturnNoPacks) . ",";
            }
            $PurchaseNoPacks = rtrim($PurchaseNoPacks, ',');
            DB::beginTransaction();
            try {
                
            
                //Nitin Art Stock Status
                $currentSalesNoPacks = DB::table('artstockstatus')
                    ->where(['outletId' => 0])
                    ->where(['ArticleId' => $articledata[0]->ArticleId])
                    ->value('SalesNoPacks');
                    
                $string = implode(',', $PurchaseReturnNoPacks);
    
                $currentSalesNoPacksArray = explode(',', $currentSalesNoPacks);
                $dataNoPacksNewArray = explode(',', $string);
            
                // Perform element-wise addition
                $newSalesNoPacksArray = [];
    
                for ($i = 0; $i < count($dataNoPacksNewArray); $i++) {
                    $newSalesNoPacksArray[$i] = (int)$currentSalesNoPacksArray[$i] + (int)$dataNoPacksNewArray[$i];
                }
                
            
                $packes = implode(',', $newSalesNoPacksArray);
                
                $packesArray = explode(',', $packes);
                $sum = array_sum($packesArray);
                // return $sum;
                DB::table('artstockstatus')->where('artstockstatus.ArticleId', $articledata[0]->ArticleId)->where('outletId', 0)->update(['SalesNoPacks' => $packes, 'TotalPieces' => $sum]);
                //close
           
                
                UserLogs::create([
                    'Module' => 'Purchase Return',
                    'ModuleNumberId' => $puReRec[0]->PurchaseReturnNumberId,
                    'LogType' => 'Deleted',
                    'LogDescription' => $userName->Name . ' deleted article ' . $puReRec[0]->ArticleNumber . ' from purchaseReturn Number ' . $puReRec[0]->PurchaseReturnnumber,
                    'UserId' => $userName->Id,
                    'updated_at' => null
                ]);
                DB::table('purchasereturn')
                    ->where('Id', '=', $id)
                    ->delete();
                DB::table('inward')
                    ->where('ArticleId', $articledata[0]->ArticleId)
                    ->update(['SalesNoPacks' => $PurchaseNoPacks]);
                DB::commit();
                return response()->json(array("id" => "SUCCESS"), 200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(array("Id" => ""), 200);
            }
        }
    }

    public function deleteSalesReturn($id, $LoggedId)
    {
        $salesreturns = DB::table('salesreturn')->where('SalesReturnNumber', $id)->get();
        foreach ($salesreturns as $salesreturn) {
            $article = Article::where('Id', $salesreturn->ArticleId)->first();
            $inward = Inward::where('ArticleId', $salesreturn->ArticleId)->first();
            if ($article->ArticleOpenFlag == 1) {
                $totalInward = 0;
                $totalOutwards = 0;
                $inwards  = Inward::where('ArticleId', $salesreturn->ArticleId)->get();
                $outwards  = Outward::where('ArticleId', $salesreturn->ArticleId)->get();
                $salesreturns = Salesreturn::where('ArticleId', $salesreturn->ArticleId)->get();
                $purchasereturns = Purchasereturns::where('ArticleId', $salesreturn->ArticleId)->get();
                $consumestocktransfers = Stocktransfer::where('ConsumedArticleId', $salesreturn->ArticleId)->get();
                $transferstocktransfers = Stocktransfer::where('TransferArticleId', $salesreturn->ArticleId)->get();
                $shortedStocks = Stockshortage::where('ArticleId', $salesreturn->ArticleId)->get();
                $sorecords  = SO::where('ArticleId', $salesreturn->ArticleId)->where('Status', 0)->get();
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
                if ($TotalRemaining >= (int)$salesreturn->NoPacks) {
                    DB::table('mixnopacks')->where('ArticleId', $salesreturn->ArticleId)->update([
                        'NoPacks' => $TotalRemaining
                    ]);
                    $salesreturnPacksRecords  = DB::table('salesreturnpacks')->where(['SalesReturnId' => $salesreturn->Id, 'OutwardId' => $salesreturn->OutwardId, 'ArticleId' => $salesreturn->ArticleId])->get();
                    foreach ($salesreturnPacksRecords as $salesreturnPacksRecord) {
                        DB::table('salesreturnpacks')->where('Id', $salesreturnPacksRecord->Id)->delete();
                    }
                } else {
                    return response()->json(['status' => 'failed'], 200);
                }
            } else {
                if (strpos($inward->SalesNoPacks, ',') != false) {
                    $noPackes = explode(',', $inward->SalesNoPacks);
                    $salesReturnNoPacksArray = explode(',', $salesreturn->NoPacks);
                    $count = 0;
                    $checkUpdate = true;
                    foreach ($salesReturnNoPacksArray as $salesReturnNoPack) {
                        if ($noPackes[$count] < $salesReturnNoPack) {
                            $checkUpdate = false;
                            break;
                        }
                        $noPackes[$count] = $noPackes[$count] - $salesReturnNoPack;
                        $count = $count + 1;
                    }
                    if ($checkUpdate == true) {
                        Inward::where('ArticleId', $salesreturn->ArticleId)->update([
                            'SalesNoPacks' => implode(',', $noPackes)
                        ]);
                        $salesreturnPacksRecords  = DB::table('salesreturnpacks')->where(['SalesReturnId' => $salesreturn->Id, 'OutwardId' => $salesreturn->OutwardId, 'ArticleId' => $salesreturn->ArticleId])->get();
                        foreach ($salesreturnPacksRecords as $salesreturnPacksRecord) {
                            DB::table('salesreturnpacks')->where('Id', $salesreturnPacksRecord->Id)->delete();
                        }
                    } else {
                        return response()->json(['status' => 'failed'], 200);
                    }
                } else {
                    if ((int)$inward->SalesNoPacks >= (int)$salesreturn->NoPacks) {
                        Inward::where('ArticleId', $salesreturn->ArticleId)->update([
                            'SalesNoPacks' => (int)$inward->SalesNoPacks - (int)$salesreturn->NoPacks
                        ]);
                        $salesreturnPacksRecords  = DB::table('salesreturnpacks')->where(['SalesReturnId' => $salesreturn->Id, 'OutwardId' => $salesreturn->OutwardId, 'ArticleId' => $salesreturn->ArticleId])->get();
                        foreach ($salesreturnPacksRecords as $salesreturnPacksRecord) {
                            DB::table('salesreturnpacks')->where('Id', $salesreturnPacksRecord->Id)->delete();
                        }
                    } else {
                        return response()->json(['status' => 'failed'], 200);
                    }
                }
            }
            $userName = Users::where('Id', $LoggedId)->first();
            $sodRec = DB::select("select sn.Id as SalesReturnNumberId, concat(sn.SalesReturnNumber,'/', fn.StartYear,'-',fn.EndYear) as SalesReturnnumber from salesreturnnumber sn inner join financialyear fn on fn.Id=sn.FinancialYearId where sn.Id= '" . $id . "'");
            DB::table('salesreturn')->where('Id', $salesreturn->Id)->delete();
            UserLogs::create([
                'Module' => 'Sales Return',
                'ModuleNumberId' => $sodRec[0]->SalesReturnNumberId,
                'LogType' => 'Deleted',
                'LogDescription' => $userName['Name'] . " " . 'deleted sales return with SalesReturn Number' . " " . $sodRec[0]->SalesReturnnumber,
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);
        }
        return response()->json(array("status" => "success"), 200);
    }


    public function  deleteSalesReturnRecord($id, $LoggedId)
    {

        $articledata = DB::select("SELECT slr.SalesReturnNumber, slr.ArticleId, slr.NoPacks, a.ArticleNumber, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, c.Colorflag FROM `salesreturn` slr inner join article a on a.Id=slr.ArticleId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where slr.Id ='" . $id . "'");
        $SalesReturnNoPacks = $articledata[0]->NoPacks;
        $SalesReturnNumber = $articledata[0]->SalesReturnNumber;
        $ArticleColor = json_decode($articledata[0]->ArticleColor);
        //echo $articledata[0]->Colorflag;
        //print_r($articledata); exit;
        if ($articledata[0]->ArticleOpenFlag == 1) {
            $r = DB::table('salesreturn')
            ->join('salesreturnnumber', 'salesreturn.salesreturnnumber', '=', 'salesreturnnumber.Id')
            ->where('salesreturn.Id', '=', $id)
            ->select('salesreturn.*', 'salesreturnnumber.PartyId')
            ->first();

            $DeleteNoPackes = $r->NoPacks;
            $DeleteNoPackes;
            $ArticleId = $r->ArticleId;
            $OutletPartyId = $r->PartyId;
            
            
                    // Fetch the current SalesNoPacks value
            $currentSalesNoPacks = DB::table('artstockstatus')
                ->where(['outletId' => $OutletPartyId])
                ->where(['ArticleId' => $ArticleId])
                ->value('SalesNoPacks');
                
            
             $artD = DB::table('article')
                ->where('Id', $ArticleId)
                ->first();
            
            // Calculate the new SalesNoPacks value by adding the new value to the current value
            $newSalesNoPacks = $currentSalesNoPacks + $DeleteNoPackes;
            
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
            $getdata = DB::select("SELECT NoPacks FROM `mixnopacks` where ArticleId='" . $articledata[0]->ArticleId . "'");

            $InwardNoPacks = $getdata[0]->NoPacks;
            $NoPacks = $SalesReturnNoPacks;

            if ($InwardNoPacks < $NoPacks) {
                return response()->json(array("Alreadyexist" => "true"), 200);
            } else {

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
                        ->update(['NoPacks' => $totalnopacks]);

                    /* DB::table('salesreturnpacks')
					->where('SalesReturnId', '=', $id)
					->delete(); */
                    $userName = Users::where('Id', $LoggedId)->first();
                    $sodRec = DB::select("select a.ArticleNumber, sn.Id as SalesReturnNumberId, concat(sn.SalesReturnNumber,'/', fn.StartYear,'-',fn.EndYear) as SalesReturnnumber from salesreturn s inner join salesreturnnumber sn on sn.Id=s.SalesReturnNumber inner join article a on a.Id=s.ArticleId inner join financialyear fn on fn.Id=sn.FinancialYearId where s.Id= '" . $id . "'");
                    UserLogs::create([
                        'Module' => 'Sales Return',
                        'ModuleNumberId' => $sodRec[0]->SalesReturnNumberId,
                        'LogType' => 'Deleted',
                        'LogDescription' => $userName->Name . " " . 'deleted article ' . $sodRec[0]->ArticleNumber . ' from SalesReturn Number ' . $sodRec[0]->SalesReturnnumber,
                        'UserId' => $userName['Id'],
                        'updated_at' => null
                    ]);
                    DB::table('salesreturn')
                        ->where('Id', '=', $id)
                        ->delete();

                    DB::table('salesreturnpacks')
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
            $getdata = DB::select("SELECT SalesNoPacks FROM `inward` where ArticleId='" . $articledata[0]->ArticleId . "'");
            $SalesNoPacks = $getdata[0]->SalesNoPacks;
            
            //echo $SalesReturnNoPacks;
            //print_r($getdata); exit;
            if (strpos($SalesNoPacks, ',') !== false) {
                $SalesNoPacks = explode(',', $SalesNoPacks);
                $SalesReturnNoPacks = explode(',', $SalesReturnNoPacks);
                $stringcomma = 1;
            } else {
                $stringcomma = 0;
            }

            $NoPacks = "";
            $UpdateInwardNoPacks = "";
            if ($articledata[0]->Colorflag == "1") {
                foreach ($ArticleColor as $key => $vl) {
                    $numberofpacks = $vl->Id;
                    $inwardsale = $SalesNoPacks[$key];

                    $SalesReturn = $SalesReturnNoPacks[$key];
                    //exit;
                    if ($inwardsale < $SalesReturn) {
                        return response()->json(array("Alreadyexist" => "true"), 200);
                    }

                    if ($stringcomma == 1) {
                        $NoPacks .= $SalesReturn . ",";
                        $UpdateInwardNoPacks .= ($inwardsale - $SalesReturn) . ",";
                    } else {
                        $NoPacks = $SalesReturn . ",";
                        $UpdateInwardNoPacks = ($SalesNoPacks - $SalesReturn) . ",";
                    }
                }
                $NoPacks = rtrim($NoPacks, ',');
                $UpdateInwardNoPacks = rtrim($UpdateInwardNoPacks, ',');



                //start
                
                $data['ArticleId'] = $articledata[0]->ArticleId ;
                $r = DB::table('salesreturn')
                ->join('salesreturnnumber', 'salesreturn.salesreturnnumber', '=', 'salesreturnnumber.Id')
                ->where('salesreturn.Id', '=', $id)
                ->select('salesreturn.*', 'salesreturnnumber.PartyId')
                ->first();
                $data['OutletPartyId'] = $r->PartyId;        
        
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


            } else {
                /* echo $SalesNoPacks;
				echo "\n";
				echo $SalesReturnNoPacks;
				echo "\n";
				echo $NoPacks; */

                if ($SalesNoPacks < $SalesReturnNoPacks) {
                    return response()->json(array("Alreadyexist" => "true"), 200);
                }

                $NoPacks = $SalesReturnNoPacks;
                $UpdateInwardNoPacks = ($SalesNoPacks - $NoPacks);
            }
            /* echo "Flag false";
			echo $NoPacks;
			echo $UpdateInwardNoPacks;
			print_r($getdata); exit; */
            DB::beginTransaction();
            try {
                DB::table('inward')
                    ->where('ArticleId', $articledata[0]->ArticleId)
                    ->update(['SalesNoPacks' => $UpdateInwardNoPacks]);

                DB::table('salesreturnpacks')
                    ->where('SalesReturnId', '=', $id)
                    ->delete();
                $userName = Users::where('Id', $LoggedId)->first();
                $sodRec = DB::select("select a.ArticleNumber, sn.Id as SalesReturnNumberId, concat(sn.SalesReturnNumber,'/', fn.StartYear,'-',fn.EndYear) as SalesReturnnumber from salesreturn s inner join salesreturnnumber sn on sn.Id=s.SalesReturnNumber inner join article a on a.Id=s.ArticleId inner join financialyear fn on fn.Id=sn.FinancialYearId where s.Id= '" . $id . "'");
                UserLogs::create([
                    'Module' => 'Sales Return',
                    'ModuleNumberId' => $sodRec[0]->SalesReturnNumberId,
                    'LogType' => 'Deleted',
                    'LogDescription' => $userName->Name . " " . 'deleted article ' . $sodRec[0]->ArticleNumber . ' from SalesReturn Number ' . $sodRec[0]->SalesReturnnumber,
                    'UserId' => $userName['Id'],
                    'updated_at' => null
                ]);
                DB::table('salesreturn')
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


    public function GetPurchaseReturn($id)
    {
        return DB::select("SELECT pr.Id, pr.TotalNoPacks, pr.RemainingNoPacks, pr.ReturnNoPacks, a.ArticleNumber, FirstCharacterConcat(u.Name) as UserName, v.Name, pr.CreatedDate FROM `purchasereturn` pr inner join article a on a.Id=pr.ArticleId inner join users u on u.Id=pr.UserId left join vendor v on v.Id=pr.VendorId order by pr.Id ASC");
    }

    public function PostPurchaseReturn(Request $request)
    {
        $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select("select count(*) as Total from (SELECT pr.Id  FROM `purchasereturn` pr inner join article a on a.Id=pr.ArticleId inner join users u on u.Id=pr.UserId left join vendor v on v.Id=pr.VendorId group by pr.PurchaseReturnNumber) as d");
        $vnTotal = $vnddataTotal[0]->Total;
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "WHERE d.Pieces like '%" . $search['value'] . "%' OR d.PurchaseReturnNumber like '%" . $search['value'] . "%' OR d.Name like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%' OR cast(d.cdate as char) like '%" . $search['value'] . "%'";
            $vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT pr.Id, concat(prn.PurchaseReturnNumber,'/', f.StartYear,'-',f.EndYear) as PurchaseReturnNumber, GetTotalPROrderPieces(pr.PurchaseReturnNumber) as Pieces, pr.TotalNoPacks, pr.RemainingNoPacks, pr.ReturnNoPacks, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNumber, FirstCharacterConcat(u.Name) as UserName, v.Name, DATE_FORMAT(pr.CreatedDate, '%d/%m/%Y') as cdate, pr.CreatedDate FROM `purchasereturn` pr inner join article a on a.Id=pr.ArticleId inner join users u on u.Id=pr.UserId left join vendor v on v.Id=pr.VendorId inner join purchasereturnnumber prn on prn.Id=pr.PurchaseReturnNumber inner join financialyear f on f.Id=prn.FinancialYearId group by pr.PurchaseReturnNumber) as d " . $searchstring);
            $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
        } else {
            $searchstring = "";
            $vnddataTotalFilterValue = $vnTotal;
        }
        $column = $data["order"][0]["column"];
        switch ($column) {
            case 1:
                $ordercolumn = "CAST(d.PurchaseReturnNumber as SIGNED INTEGER)";
                break;
            case 2:
                $ordercolumn = "d.Name";
                break;
            case 4:
                $ordercolumn = "d.CreatedDate";
                break;
            default:
                $ordercolumn = "d.CreatedDate";
                break;
        }
        $order = "";
        if ($data["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
        }
        $vnddata = DB::select("select d.* from (SELECT prn.Id as PRNO, concat(prn.PurchaseReturnNumber,'/', f.StartYear,'-',f.EndYear) as PurchaseReturnNumber,  GetTotalPROrderPieces(pr.PurchaseReturnNumber) as Pieces, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY prn.Id SEPARATOR ',') as ArticleNumber, FirstCharacterConcat(u.Name) as UserName, v.Name, DATE_FORMAT(pr.CreatedDate, '%d/%m/%Y') as cdate, pr.CreatedDate FROM `purchasereturn` pr inner join article a on a.Id=pr.ArticleId inner join users u on u.Id=pr.UserId left join vendor v on v.Id=pr.VendorId left join purchasereturnnumber prn on pr.PurchaseReturnNumber=prn.Id inner join financialyear f on f.Id=prn.FinancialYearId group by pr.PurchaseReturnNumber) as d " . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length);
        $totalNoPacks = 0;
        foreach ($vnddata as $vnd) {
            $getPurchaseReturns = Purchasereturns::where('PurchaseReturnNumber', $vnd->PRNO)->get();
            foreach ($getPurchaseReturns as $getPurchaseReturn) {
                if (strpos($getPurchaseReturn->ReturnNoPacks, ',') !== false) {
                    $totalNoPacks += array_sum(explode(",", $getPurchaseReturn->ReturnNoPacks));
                } else {
                    $singlecountNoPacks = $getPurchaseReturn->ReturnNoPacks;
                    $totalNoPacks += $getPurchaseReturn->ReturnNoPacks;
                }
                $vnd->Pieces = $totalNoPacks;
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

    public function GetSalesReturnChallan($Id)
    {
        $getdata = DB::select("SELECT srn.OutletPartyId FROM `salesreturn` s inner join salesreturnnumber srn on srn.Id=s.SalesReturnNumber where s.SalesReturnNumber ='" . $Id . "'");
        if ($getdata[0]->OutletPartyId == 0) {
            $sl = 0;
            $getsrchallen = DB::select("SELECT concat(otn.OutwardNumber,'/' ,fn2.StartYear,'-',fn2.EndYear) as OutwardNumber, concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, '%Y-%m-%d') as SRDate, srn.Remarks, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate11, slr.OutwardRate as SRArticleRate  FROM `salesreturn` slr inner join article a on a.Id=slr.ArticleId inner join category c on c.Id=a.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join party p on p.Id = srn.PartyId  inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn2 on fn2.Id=otn.FinancialYearId where slr.SalesReturnNumber='" . $Id . "'");
        } else {
            $sl = 1;
            
          //OLD QUERY WITH GROUP BY CATEGOTY 
            // $getsrchallen = DB::select("SELECT concat(otn.OutwardNumber,'/' ,fn2.StartYear,'-',fn2.EndYear) as OutwardNumber,concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, '%Y-%m-%d') as SRDate, srn.Remarks, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, pp.Name as OutletPartyName, pp.Address as OutletPartyAddress, pp.GSTNumber as OutletPartyGSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate11, slr.OutwardRate as SRArticleRate  FROM `salesreturn` slr inner join article a on a.Id=slr.ArticleId inner join category c on c.Id=a.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join party p on p.Id = srn.PartyId inner join party pp on pp.Id=srn.OutletPartyId inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn2 on fn2.Id=otn.FinancialYearId where slr.SalesReturnNumber='" . $Id . "' group by c.Title ASC");
            
             //NEW QUERY WITHOUT GROUP BY 
            $getsrchallen = DB::select("SELECT concat(otn.OutwardNumber,'/' ,fn2.StartYear,'-',fn2.EndYear) as OutwardNumber,concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, '%Y-%m-%d') as SRDate, srn.Remarks, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, pp.Name as OutletPartyName, pp.Address as OutletPartyAddress, pp.GSTNumber as OutletPartyGSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate11, slr.OutwardRate as SRArticleRate  FROM `salesreturn` slr inner join article a on a.Id=slr.ArticleId inner join category c on c.Id=a.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join party p on p.Id = srn.PartyId inner join party pp on pp.Id=srn.OutletPartyId inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn2 on fn2.Id=otn.FinancialYearId where slr.SalesReturnNumber='" . $Id . "' ");
       
            
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
            $OutwardNumber = $vl->OutwardNumber;
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

            $challendata[] = json_decode(json_encode(array("ColorWiseQty" => $TotalCQty,"OutwardNumber" => $OutwardNumber,"TotalQty" => $TotalQty , "SalesReturnNumber" => $SalesReturnNumber, "UserName" => $UserName, "SRDate" => $SRDate, "Id" => $Id, "Name" => $Name, "Address" => $Address, "Outletdata" => $outletdata, "OutletPartyName" => $OutletPartyName, "OutletPartyAddress" => $OutletPartyAddress, "OutletPartyGSTNumber" => $OutletPartyGSTNumber, "GSTNumber" => $GSTNumber, "Remark" => $Remark, "ArticleNumber" => $ArticleNumber, "Title" => $Title, "ArticleRatio" => $ArticleRatio, "QuantityInSet" => $NoPacks, "ArticleRate" => number_format($ArticleRate, 2), "Amount" => number_format($Amount, 2), "ArticleColor" => $ArticleColor, "ArticleSize" => $ArticleSize)), false);
        }
        $as  = array($challendata, array("TotalNoPacks" => $TotalNoPacks, "TotalAmount" => number_format($TotalAmount, 2)));
        return $as;
    }

    public function GetPurchaseReturnChallan($Id)
    {
        $getsrchallen = DB::select("SELECT prn.Remark, (case pur.ArticleRate when 0 then a.ArticleRate else pur.ArticleRate END) as PRArticleRate, concat(ig.GRN, '/',f.StartYear,'-',f.EndYear) as GRN,concat(prn.PurchaseReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as PurchaseReturnNumber, pur.Id, DATE_FORMAT(pur.CreatedDate, '%Y-%m-%d') as PRDate, c.Title, v.Name, v.Address, v.GSTNumber, pur.TotalNoPacks, pur.RemainingNoPacks, pur.ReturnNoPacks, u.Name as UserName, a.ArticleNumber, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag FROM `purchasereturn` pur inner join article a on a.Id=pur.ArticleId inner join users u on u.Id=pur.UserId inner join vendor v on v.Id=pur.VendorId inner join category c on c.Id=a.CategoryId inner join purchasereturnnumber prn on prn.Id=pur.PurchaseReturnNumber inner join financialyear fn on fn.Id=prn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join inward i on i.Id=pur.InwardId left join inwardgrn ig on ig.Id=i.GRN inner join financialyear f on f.Id=ig.FinancialYearId where prn.Id = '" . $Id . "'ORDER BY c.Title ASC");
        $challendata = [];
        $TotalNoPacks = 0;
        $TotalAmount = 0;
        foreach ($getsrchallen as $vl) {
            $GRN = $vl->GRN;
            $Name = $vl->Name;
            $UserName = $vl->UserName;
            $Address = $vl->Address;
            $GSTNumber = $vl->GSTNumber;
            $ArticleNumber = $vl->ArticleNumber;
            $ArticleOpenFlag = $vl->ArticleOpenFlag;
            $Title = $vl->Title;
            $PRDate = $vl->PRDate;
            $Remark = $vl->Remark;
            $Id = $vl->Id;
            $PurchaseReturnNumber = $vl->PurchaseReturnNumber;
            $NoPacks = $vl->ReturnNoPacks;
            if ($ArticleOpenFlag == 0) {
                $ArticleRatio = $vl->ArticleRatio;
                if (strpos($NoPacks, ',') !== true) {
                    $countNoSet = array_sum(explode(",", $NoPacks));
                    $TotalNoPacks += array_sum(explode(",", $NoPacks));
                } else {
                    $countNoSet = $NoPacks;
                    $TotalNoPacks += $NoPacks;
                }
                $ArticleRate = $vl->PRArticleRate;
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
                $ArticleRatio = "";

				$TotalCQty = "";

                $ArticleRate = $vl->PRArticleRate;
                $Amount = $countNoSet * $ArticleRate;
                $TotalAmount += $Amount;
                $ArticleColor = "";
                $ArticleSize = "";
            }

            $numbers_colorQty = explode(",", $ArticleColor);

            $numbers_packQty = explode(",", $NoPacks);


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

            $challendata[] = json_decode(json_encode(array("ColorWiseQty" => $TotalCQty,"Remark" => $Remark, "GRN" => $GRN, "PurchaseReturnNumber" => $PurchaseReturnNumber, "UserName" => $UserName, "PRDate" => $PRDate, "Id" => $Id, "Name" => $Name, "Address" => $Address, "GSTNumber" => $GSTNumber, "ArticleNumber" => $ArticleNumber, "Title" => $Title, "ArticleRatio" => $ArticleRatio, "QuantityInSet" => $NoPacks, "ArticleRate" => number_format($ArticleRate, 2), "Amount" => number_format($Amount, 2), "ArticleColor" => $ArticleColor, "ArticleSize" => $ArticleSize)), false);
        }
        $as  = array($challendata, array("TotalNoPacks" => $TotalNoPacks, "TotalAmount" => number_format($TotalAmount, 2)));
        return $as;
    }

    public function CategoryArticleList($CategoryId)
    {
        return DB::select("SELECT a.Id,  a.ArticleStatus, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck,substring_index(GROUP_CONCAT(DISTINCT CONCAT(ap.Name) ORDER BY ap.Id SEPARATOR ','), ',', 2) as Images, a.ArticleNumber, c.Title, ar.ArticleRate FROM `article` a left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId inner join articlerate ar on ar.ArticleId=a.Id left join articlephotos ap on ap.ArticlesId=a.Id inner join inward inw on inw.ArticleId=a.Id where c.Id='" . $CategoryId . "' and a.ArticleStatus=1 and ap.Name!='' group by a.Id HAVING SalesNoPacksCheck = 0");
    }

    public function ArticleDetails($ArtId)
    {
        $articleflagcheck = DB::select('SELECT ArticleOpenFlag FROM `article` where Id = "' . $ArtId . '"');
        if ($articleflagcheck[0]->ArticleOpenFlag == 0) {
            return DB::select("SELECT art.Id, (CASE ap.Name WHEN NULL then '0' ELSE GROUP_CONCAT(DISTINCT CONCAT(ap.Name) ORDER BY ap.Id SEPARATOR ',') END) as Images, art.ArticleNumber, c.Title, ar.ArticleRate,  art.ArticleColor,  art.ArticleSize, art.ArticleRatio, i.SalesNoPacks, c.Colorflag, art.ArticleOpenFlag FROM article art inner join articlerate ar on ar.ArticleId=art.Id inner join category c on c.Id=art.CategoryId inner join inward i on i.ArticleId=art.Id left join articlephotos ap on ap.ArticlesId=art.Id where art.Id ='" . $ArtId . "'");
        } else {
            return DB::select("SELECT mxp.ArticleId as Id, mxp.NoPacks, ar.ArticleRate,  a.ArticleNumber, a.ArticleOpenFlag FROM `mixnopacks` mxp inner join article a on a.Id=mxp.ArticleId inner join articlerate ar on ar.ArticleId=mxp.ArticleId where mxp.ArticleId ='" . $ArtId . "'");
        }
    }
    public function getsalesreturnIdwise($SrId)
    {
        $data =  DB::select("select a.*, p.OutletAssign , p.Id as PartyId ,  sr.NoPacks as SalesReturnPacks , o.NoPacks as OutwardPacks , c.Id as CategoryId ,c.Colorflag, c.Title as Category ,sr.NoPacks , srn.Id as SalesReturnNumberId , concat(own.OutwardNumber ,'/', fn.StartYear,'-',fn.EndYear) as OutwardNumber ,o.OutwardRate ,  sr.Remark , srn.PartyId ,sr.ArticleId ,sr.OutwardId from salesreturn sr inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber inner join party p on p.Id=srn.PartyId inner join outward o on o.Id=sr.OutwardId inner join outwardnumber own on own.Id=o.OutwardNumberId inner join article a on a.Id=sr.ArticleId inner join category c on c.Id=a.CategoryId inner join financialyear fn on fn.Id=own.FinancialYearId where sr.Id=" . $SrId);
        if ($data[0]->OutletAssign == 1) {
            $PartyId = $data[0]->PartyId;
            $ArticleId = $data[0]->ArticleId;
            $allRecords = DB::select('(select `outletsalesreturn`.`NoPacks` as `NoPacks`, 2 as type, `outletsalesreturnnumber`.`CreatedDate` as `SortDate` from `outletsalesreturn` inner join `outletsalesreturnnumber` on `outletsalesreturn`.`SalesReturnNumber` = `outletsalesreturnnumber`.`Id` where (`ArticleId` = ' . $data[0]->ArticleId . ' and `outletsalesreturn`.`OutletPartyId` = ' . $PartyId . ')) union (select `outlet`.`NoPacks` as `NoPacks`, 1 as type, `outletnumber`.`CreatedDate` as `SortDate` from `outlet` inner join `outletnumber` on `outlet`.`OutletNumberId` = `outletnumber`.`Id` where (`ArticleId` = ' . $data[0]->ArticleId . ' and `outletnumber`.`PartyId` = ' . $PartyId . ')) union (select `outward`.`NoPacks` as `NoPacks`, 0 as type, `outwardnumber`.`created_at` as `SortDate` from `outward` inner join `transportoutlet` on `outward`.`OutwardNumberId` = `transportoutlet`.`OutwardNumberId` inner join `outwardnumber` on `outward`.`OutwardNumberId` = `outwardnumber`.`Id` where (`ArticleId` = ' . $data[0]->ArticleId . ' and `transportoutlet`.`TransportStatus` = 1 and `outward`.`PartyId` = ' . $PartyId . ')) union (select `salesreturn`.`NoPacks` as `NoPacks`, 3 as type ,`salesreturnnumber`.`CreatedDate` as `SortDate` from `outward` inner join `salesreturn` on `salesreturn`.`OutwardId` = `outward`.`Id` inner join `salesreturnnumber` on `salesreturnnumber`.`Id` = `salesreturn`.`SalesReturnNumber` where (`outward`.`PartyId` = ' . $PartyId . ' and `outward`.`ArticleId` = ' . $data[0]->ArticleId . ')) order by `SortDate` asc');
            if (!isset($allRecords[0])) {
                $outletArticle = Outletimport::where('ArticleId', $ArticleId)->first();
                if ($outletArticle) {
                    $outletArticleColors = json_decode($outletArticle->ArticleColor);
                } else {
                    $articleRecord = Article::where('Id', $ArticleId)->first();
                    $outletArticleColors  = json_decode($articleRecord->ArticleColor);
                }
                $outletArticleColors =  (array)$outletArticleColors;
                if (count($outletArticleColors) != 0) {
                    $SalesNoPacks = [];
                    foreach ($outletArticleColors as $makearray) {
                        array_push($SalesNoPacks, 0);
                    }
                    $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $ArticleId)->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
                    $TotalTransportOutwardpacks = 0;
                    if (count($transportOutwardpacks) != 0) {
                        $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                        $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
                        foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                            $outletArticle = Outletimport::where('ArticleId', $ArticleId)->first();
                            if ($outletArticle) {
                                $articleColors = json_decode($outletArticle->ArticleColor);
                            } else {
                                $article = Article::select('ArticleColor')->where('Id', $ArticleId)->first();
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
                    $data[0]->pendingoutlet =  $newimplodeSalesNoPacks;
                } else {
                    $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks')->where('ArticleId', $ArticleId)->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
                    $TotalTransportOutwardpacks = 0;
                    if (count($transportOutwardpacks) != 0) {
                        $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                        $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
                        foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                            $TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
                        }
                    }
                    $data[0]->pendingoutlet =  $TotalTransportOutwardpacks;
                }
            } else {
                $outletArticle = Outletimport::where('ArticleId', $ArticleId)->first();
                if ($outletArticle) {
                    $outletArticleColors = json_decode($outletArticle->ArticleColor);
                } else {
                    $articleRecord = Article::where('Id', $ArticleId)->first();
                    $outletArticleColors  = json_decode($articleRecord->ArticleColor);
                }
                $outletArticleColors =  (array)$outletArticleColors;
                if (count($outletArticleColors) != 0) {
                    $SalesNoPacks = [];
                    foreach (explode(",", $allRecords[0]->NoPacks) as $makearray) {
                        array_push($SalesNoPacks, 0);
                    }
                    $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $ArticleId)->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
                    $TotalTransportOutwardpacks = 0;
                    if (count($transportOutwardpacks) != 0) {
                        $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                        $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
                        foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                            $outletArticle = Outletimport::where('ArticleId', $ArticleId)->first();
                            if ($outletArticle) {
                                $articleColors = json_decode($outletArticle->ArticleColor);
                            } else {
                                $article = Article::select('ArticleColor')->where('Id', $ArticleId)->first();
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
                    foreach ($allRecords as  $allRecord) {
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
                    $data[0]->pendingoutlet =  $newimplodeSalesNoPacks;
                } else {
                    $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks')->where('ArticleId', $ArticleId)->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
                    $TotalTransportOutwardpacks = 0;
                    if (count($transportOutwardpacks) != 0) {
                        $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                        $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
                        foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                            $TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
                        }
                    }
                    $TotalInwardPacks = $TotalTransportOutwardpacks;
                    $TotalOutwardPacks = 0;
                    foreach ($allRecords as  $allRecord) {
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
                    $data[0]->pendingoutlet =  $TotalInwardPacks - $TotalOutwardPacks;
                }
            }
        }
        // else{
        //     return  $dat
        // }
        return $data;
        // echo $SrId ;
    }

    public function updateSalesReturn(Request $request)
{
    $data = $request->all();
    // return $data;
    $ArticleId = $data['ArticleId'];
    $salesReturnRec = DB::select("select sr.NoPacks as SalesReturnPacks , sr.SalesReturnNumber , sr.Id as salesreturnId from salesreturn sr  where sr.SalesReturnNumber=" . $data['SRNumberId'] . " and sr.ArticleId=" . $data['ArticleId']);
    $artRecor = Article::where('Id', $data['ArticleId'])->first();
    $userName = Users::where('Id', $data['UserId'])->first();
    $sodRec = DB::select("select sn.Id as SalesReturnNumberId, concat(sn.SalesReturnNumber,'/', fn.StartYear,'-',fn.EndYear) as SalesReturnnumber from salesreturnnumber sn inner join financialyear fn on fn.Id=sn.FinancialYearId where sn.Id= '" . $data['SRNumberId'] . "'");
    $inwardRec = Inward::where('ArticleId', $ArticleId)->first();
    if ($data['ArticleOpenFlag'] == 0) {
        $InwardSalesNoPacksArray = explode(",", $inwardRec->SalesNoPacks);
        $salesReturnNoPacksArray = explode(",", $salesReturnRec[0]->SalesReturnPacks);
        $newInwardSalesNoPacksArray = $InwardSalesNoPacksArray;
        $newReturnNoPacks = "";
        $articleSelectedColors = $data['ArticleSelectedColor'];
        $count = 0;
        foreach ($articleSelectedColors as $articleSelectedColor) {
            $newInwardSalesNoPacksArray[$count] = ($newInwardSalesNoPacksArray[$count] - $salesReturnNoPacksArray[$count]) + $data["NoPacksNew_" . $articleSelectedColor['Id']];
            if ($data["NoPacksNew_" . $articleSelectedColor['Id']] > $data["NoPacks_" . $articleSelectedColor['Id']]) {
                return response()->json(["status" => "failed", "NoOfSetNotMatch" => true], 200);
            }
            if (count($articleSelectedColors) == $count + 1) {
                $newReturnNoPacks = $newReturnNoPacks . $data["NoPacksNew_" . $articleSelectedColor['Id']];
            } else {
                $newReturnNoPacks = $newReturnNoPacks . $data["NoPacksNew_" . $articleSelectedColor['Id']] . ",";
            }
            UserLogs::create([
                'Module' => 'Sales Return',
                'ModuleNumberId' => $sodRec[0]->SalesReturnNumberId,
                'LogType' => 'Updated',
                'LogDescription' => $userName->Name . ' upadated ' . $newLogDesc . ' of article ' . $artRecor->ArticleNumber . ' in SalesReturn Number ' . $sodRec[0]->SalesReturnnumber,
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);
            Salesreturn::where('Id', $salesReturnRec[0]->salesreturnId)->update([
                'NoPacks' => $data['NoPacksNew']
            ]);
            DB::table('salesreturnpacks')->where(['SalesReturnId' => $salesReturnRec[0]->salesreturnId, 'ArticleId' => $ArticleId, 'ColorId' => 0])
                ->update(['NoPacks' => $data['NoPacks']]);
            return response()->json(["status" => "success", "id" => $salesReturnRec[0]->SalesReturnNumber], 200);
            $count  = $count + 1;
            
        }
        
        
          //Nitin Art Stock Status 
        
          $isOutlet = DB::select("SELECT OutletAssign FROM `party` where Id ='" . $data['PartyId'] . "'");
            if ($isOutlet[0]->OutletAssign == 1) {
            $prePacks = $salesReturnRec[0]->SalesReturnPacks;
            $newPakes = $newReturnNoPacks;
            $currentSalesNoPacks = DB::table('artstockstatus')
                    ->where(['outletId' => $data['PartyId']])
                    ->where(['ArticleId' => $data['ArticleId']])
                    ->value('SalesNoPacks');
                
                    // Convert comma-separated values to arrays
                    $currentSalesNoPacksArray = explode(',', $currentSalesNoPacks);
                    $dataNoPacksNewArray = explode(',', $newPakes);
                    $preSalesReturnNoPacksArray = explode(',', $prePacks);
                
                    // Perform element-wise addition
                    $newSalesNoPacksArray = [];

                    for ($i = 0; $i < count($dataNoPacksNewArray); $i++) {
                        $newSalesNoPacksArray[$i] = (int)$currentSalesNoPacksArray[$i] + (int)$preSalesReturnNoPacksArray[$i] - (int)$dataNoPacksNewArray[$i];
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
                            'outletId' => $data['PartyId'],
                            'ArticleId' => $data['ArticleId']
                        ],
                        [
                            'ArticleNumber' => $artD->ArticleNumber,
                            'SalesNoPacks' => $newSalesNoPacks,
                            'TotalPieces' => $sum
                        ]
                    );
            }
            //Close
            
        
        foreach ($articleSelectedColors as $articleSelectedColor) {
            DB::table('salesreturnpacks')->where(['SalesReturnId' => $salesReturnRec[0]->salesreturnId, 'ArticleId' => $ArticleId, 'ColorId' => $articleSelectedColor['Id']])
                ->update(['NoPacks' => $data["NoPacksNew_" . $articleSelectedColor["Id"]]]);
        }
        $ActiveSalesReturn = Salesreturn::where('Id', $salesReturnRec[0]->salesreturnId)->first();
        $newLogDesc = "";
        if ($ActiveSalesReturn->NoPacks  != $newReturnNoPacks) {
            $newLogDesc = 'Pieces';
        }
        UserLogs::create([
            'Module' => 'Sales Return',
            'ModuleNumberId' => $sodRec[0]->SalesReturnNumberId,
            'LogType' => 'Updated',
            'LogDescription' => $userName->Name . ' upadated ' . $newLogDesc . ' of article ' . $artRecor->ArticleNumber . ' in SalesReturn Number ' . $sodRec[0]->SalesReturnnumber,
            'UserId' => $userName->Id,
            'updated_at' => null
        ]);
        Inward::where('ArticleId', $ArticleId)->update(['SalesNoPacks' => implode(',', $newInwardSalesNoPacksArray)]);
        Salesreturn::where('Id', $salesReturnRec[0]->salesreturnId)->update([
            'NoPacks' => $newReturnNoPacks
        ]);

        return response()->json(["status" => "success", "id" => $salesReturnRec[0]->SalesReturnNumber], 200);
    } else {
        if ($data['NoPacksNew'] > $data['NoPacks']) {
            return response()->json(["status" => "failed", "NoOfSetNotMatch" => true], 200);
        }
        $mixpackRec  = DB::table('mixnopacks')->where('ArticleId', $ArticleId)->first();
        $newNoPacksNew = ((int)$mixpackRec->NoPacks  - (int)$salesReturnRec[0]->SalesReturnPacks) + $data['NoPacksNew'];
        DB::table('mixnopacks')->where('ArticleId', $ArticleId)->update([
            "NoPacks" => $newNoPacksNew
        ]);
        $ActiveSalesReturn = Salesreturn::where('Id', $salesReturnRec[0]->salesreturnId)->first();
        $newLogDesc = "";
        if ($ActiveSalesReturn->NoPacks  != $data['NoPacks']) {
            $newLogDesc = 'Pieces';
        }
        UserLogs::create([
            'Module' => 'Sales Return',
            'ModuleNumberId' => $sodRec[0]->SalesReturnNumberId,
            'LogType' => 'Updated',
            'LogDescription' => $userName->Name . ' upadated ' . $newLogDesc . ' of article ' . $artRecor->ArticleNumber . ' in SalesReturn Number ' . $sodRec[0]->SalesReturnnumber,
            'UserId' => $userName['Id'],
            'updated_at' => null
        ]);
        Salesreturn::where('Id', $salesReturnRec[0]->salesreturnId)->update([
            'NoPacks' => $data['NoPacksNew']
        ]);
        DB::table('salesreturnpacks')->where(['SalesReturnId' => $salesReturnRec[0]->salesreturnId, 'ArticleId' => $ArticleId, 'ColorId' => 0])
            ->update(['NoPacks' => $data['NoPacks']]);
        return response()->json(["status" => "success", "id" => $salesReturnRec[0]->SalesReturnNumber], 200);
    }
}

    public function getOutletsalesreturnidwise($id)
    {
        return DB::select("select osrenum.Id as OutletSalesReturnNumberId ,concat(otn.OutletNumber,'/', fn.StartYear,'-',fn.EndYear) as OutletNumber , osre.PartyId ,osre.OutletPartyId  , osre.ArticleId , osre.NoPacks as OutletSalesReturn ,a.Id as ArticleId , a.ArticleNumber  , osre.OutletId ,ot.NoPacks as OutletNoPacks , ot.ArticleRate, a.ArticleColor ,a.ArticleSize,a.ArticleRatio ,c.Id as CategoryId , c.Title as Category ,c.Colorflag ,c.ArticleOpenFlag from outletsalesreturn osre inner join outletsalesreturnnumber osrenum on osrenum.Id=osre.SalesReturnNumber inner join outlet ot on osre.OutletId=ot.Id inner join outletnumber otn on otn.Id=ot.OutletNumberId inner join financialyear fn on fn.Id=otn.FinancialYearId inner join article a on osre.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where osre.Id=" . $id);
    }
    public function updateOutletSalesReturnForm(Request $request)
    {
        $data  = $request->all();
        $ArticleId = $data['ArticleId'];
        $SRNumberId =  $data['SRNumberId'];
        $outletSalesReturnRec = DB::select("select osr.Id as Id , osr.SalesReturnNumber , osr.NoPacks as SalesReturnNoPacks from outletsalesreturn osr where osr.SalesReturnNumber=" . $SRNumberId . " and osr.ArticleId =" . $ArticleId);
        $preSalesReturnNoPacks = $outletSalesReturnRec[0]->SalesReturnNoPacks;
        $ArticleSelectedColors =  $data['ArticleSelectedColor'];
        if ($data['ArticleOpenFlag'] == 0) {
            
            
            
            //Nitin Art Stock Status 
                $NoPacks = "";
                $SalesNoPacks = '';
            foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                    $numberofpacks = $vl["Id"];
                    if ($data["NoPacksNew_" . $numberofpacks] != "") {
                        if (1 == 1) {
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
                $NewSalesReturnNoPacks = rtrim($NoPacks, ',');
                
    
                $currentSalesNoPacks = DB::table('artstockstatus')
                        ->where(['outletId' => $data['OutletPartyId']])
                        ->where(['ArticleId' => $data['ArticleId']])
                        ->value('SalesNoPacks');
                    
                    // Check if $currentSalesNoPacks is not empty
                    if ($currentSalesNoPacks !== null) {
                        // Convert comma-separated values to arrays
                        $currentSalesNoPacksArray = explode(',', $currentSalesNoPacks);
                        $dataNoPacksNewArray = explode(',', $NewSalesReturnNoPacks);
                        $preSalesReturnNoPacksArray = explode(',', $preSalesReturnNoPacks);
                    
                        // Perform element-wise addition
                        $newSalesNoPacksArray = [];
    
                        for ($i = 0; $i < count($dataNoPacksNewArray); $i++) {
                            $newSalesNoPacksArray[$i] = (int)$currentSalesNoPacksArray[$i] - (int)$preSalesReturnNoPacksArray[$i] + (int)$dataNoPacksNewArray[$i];
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
                            ->where('Id', $data['ArticleId'])
                            ->first();
                        // Perform the updateOrInsert operation with the new SalesNoPacks value
                        
                        DB::table('artstockstatus')->updateOrInsert(
                            [
                                'outletId' => $data['OutletPartyId'],
                                'ArticleId' => $data['ArticleId']
                            ],
                            [
                                'ArticleNumber' => $artD->ArticleNumber,
                                'SalesNoPacks' => $newSalesNoPacks,
                                'TotalPieces' => $sum
                            ]
                        );
                    }
                
            
            //close
            
            $newSalesReturnNoPacks = "";
            $count = 0;
            foreach ($ArticleSelectedColors as $ArticleSelectedColor) {
                if ($data["NoPacksNew_" . $ArticleSelectedColor['Id']] <= ($data["NoPacks_" . $ArticleSelectedColor['Id']])) {
    
                    if (count($ArticleSelectedColors) == $count + 1) {
                        $newSalesReturnNoPacks = $newSalesReturnNoPacks . $data["NoPacksNew_" . $ArticleSelectedColor['Id']];
                    } else {
                        $newSalesReturnNoPacks = $newSalesReturnNoPacks . $data["NoPacksNew_" . $ArticleSelectedColor['Id']] . ",";
                    }
                } else {
                    return response()->json(["status" => "failed", "NoOfSetNotMatch" => true], 200);
                }
                $count =  $count + 1;
               
            }
        } else {
            if ((int)$data['NoPacksNew'] == 0) {
                return response()->json(["status" => "failed", "ZEROvalue" => true], 200);
            }
            if ((int)$data['NoPacksNew'] <= (int)$data['NoPacks']) {
                $newSalesReturnNoPacks =  (int)$preSalesReturnNoPacks;
            } else {
                return response()->json(["status" => "failed", "NoOfSetNotMatch" => true], 200);
            }
        }
        $artRecor = Article::where('Id', $data['ArticleId'])->first();
        $userName = Users::where('Id', $data['UserId'])->first();
        $sodRec = DB::select("select sn.Id as SalesReturnNumberId, concat(sn.SalesReturnNumber,'/', fn.StartYear,'-',fn.EndYear) as OutletSalesReturnnumber from outletsalesreturnnumber sn inner join financialyear fn on fn.Id=sn.FinancialYearId where sn.Id= '" . $SRNumberId . "'");
        $ActiveSalesReturn = OutletSalesreturn::where('Id', $outletSalesReturnRec[0]->Id)->first();
        $newLogDesc = "";
        if ($ActiveSalesReturn->NoPacks  != $newSalesReturnNoPacks) {
            $newLogDesc = $newLogDesc . 'Pieces';
        }
        if ($ActiveSalesReturn->Remark  != $data['Remark']) {
            $newLogDesc = $newLogDesc . 'Remark';
        }
        $newLogDesccc = rtrim($newLogDesc, ',');
        UserLogs::create([
            'Module' => 'Outlet Sales Return',
            'ModuleNumberId' => $sodRec[0]->SalesReturnNumberId,
            'LogType' => 'Updated',
            'LogDescription' => $userName->Name . ' updated ' . $newLogDesccc . ' of article ' . $artRecor->ArticleNumber . ' in OutletSalesReturn Number ' . $sodRec[0]->OutletSalesReturnnumber,
            'UserId' => $userName->Id,
            'updated_at' => null
        ]);
        OutletSalesreturn::where('Id', $outletSalesReturnRec[0]->Id)->update([
            'NoPacks' => $newSalesReturnNoPacks,
            'Remark' => $data['Remark']
        ]);
        DB::table('outletsalesreturnnumber')->where('Id', $outletSalesReturnRec[0]->SalesReturnNumber)->update([
            'Remarks' => $data['Remark']
        ]);
        if ($data['ArticleOpenFlag'] == 0) {
            foreach ($ArticleSelectedColors as $ArticleSelectedColor) {
                OutletSalesReturnPacks::where(['SalesReturnId' => $outletSalesReturnRec[0]->Id, 'ArticleId' => $ArticleId, 'ColorId' => $ArticleSelectedColor['Id']])->update([
                    'NoPacks' =>  $data["NoPacksNew_" . $ArticleSelectedColor['Id']]
                ]);
            }
        } else {
            OutletSalesReturnPacks::where(['SalesReturnId' => $outletSalesReturnRec[0]->Id, 'ArticleId' => $ArticleId, 'ColorId' => 0])->update([
                'NoPacks' =>  $data["NoPacksNew"]
            ]);
        }
        return response()->json(["status" => "success", "id" => $outletSalesReturnRec[0]->SalesReturnNumber], 200);
    }
        public function SOLogs($SONOId)
    {
        return DB::select("select * from (select ul.Id as UserLogsId, ul.Module, ul.LogType, ul.ModuleNumberId, ul.LogDescription, DATE_FORMAT(ul.created_at ,'%d-%m-%Y') as CreatedDate, DATE_FORMAT(ul.created_at ,'%H:%i:%s') as CreatedTime, u.Name, u.Status, ur.Role from userlogs ul inner join users u on u.Id=ul.UserId inner join userrole ur on ur.RoleType=u.Role) as dd where dd.ModuleNumberId= '" . $SONOId . "' and dd.Module='SO' order by dd.UserLogsId desc ");
    }
    public function SalesReturnLogs($SRONOId)
    {
        return DB::select("select * from (select ul.Id as UserLogsId, ul.Module, ul.LogType, ul.ModuleNumberId, ul.LogDescription, DATE_FORMAT(ul.created_at ,'%d-%m-%Y') as CreatedDate, DATE_FORMAT(ul.created_at ,'%H:%i:%s') as CreatedTime, u.Name, u.Status, ur.Role from userlogs ul inner join users u on u.Id=ul.UserId inner join userrole ur on ur.RoleType=u.Role) as dd where dd.ModuleNumberId= '" . $SRONOId . "' and dd.Module='Sales Return' order by dd.UserLogsId desc ");
    }
    public function PurchaseReturnLogs($PRONOId)
    {
        return DB::select("select * from (select ul.Id as UserLogsId, ul.Module, ul.LogType, ul.ModuleNumberId, ul.LogDescription, DATE_FORMAT(ul.created_at ,'%d-%m-%Y') as CreatedDate, DATE_FORMAT(ul.created_at ,'%H:%i:%s') as CreatedTime, u.Name, u.Status, ur.Role from userlogs ul inner join users u on u.Id=ul.UserId inner join userrole ur on ur.RoleType=u.Role) as dd where dd.ModuleNumberId= '" . $PRONOId . "' and dd.Module='Purchase Return' order by dd.UserLogsId desc ");
    }
}