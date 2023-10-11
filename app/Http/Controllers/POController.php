<?php

namespace App\Http\Controllers;

use App\PO;
use App\WorkOrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Article;
use App\Inward;
use App\Purchasereturns;
use App\UserLogs;
use App\Articlelaunch;
use App\Users;

class POController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // PO table data move on purchasenumber
    //UPDATE purchasenumber INNER JOIN po ON (po.PO_Number = purchasenumber.Id) SET purchasenumber.PoDate = po.PoDate
    public function __invoke(Request $request)
    {
        //
    }

    public function checkimage($id)
    {
        $poimagecheck = DB::select("select PO_Image from purchasenumber where Id = '" . $id . "'");

        if ($poimagecheck[0]->PO_Image != "") {
            $file = public_path('uploads/po/' . $poimagecheck[0]->PO_Image);
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public function AddPO(Request $request)
    {

        

        $data = $request->all();

        
        if ($data["SubCategoryId"] == "") {
            $data["SubCategoryId"] = 0;
        }


        // if($data["SubCategoryId"] == 0){
        //     return response()->json(array("id" => '', "PO_Number" => ''), 200 ); 
        // }


        $MultipleImageflag = 0;
        if ($data["MultipleImage"] == true || $data["MultipleImage"] == 1) {
            $MultipleImageflag = 1;
        }
        if ($data['BrandId'] == "") {
            $BrandId = 0;
        } else {
            $BrandId = $data['BrandId'];
        }
        $PO_Image = "";
        if (!isset($data['Remarks'])) {
            $data['Remarks'] = "";
        }
        if (isset($data['PO_Image'])) {
            $file = $data['PO_Image'];
            $name = $file->getClientOriginalName();
            $randomstring = $this->generateRandomString();
            $name_extension = explode(".", $name);
            $PO_Image = $randomstring . '.' . $name_extension[1];
            $file->move('uploads/po', $PO_Image);
            if ($MultipleImageflag == 0) {
                if ($data['PO_Number'] != "Add") {
                    $poimagecheck = DB::select("select PO_Image from purchasenumber where Id = '" . $data['PO_Number'] . "'");
                    if ($poimagecheck[0]->PO_Image != "") {
                        $file = public_path('uploads/po/' . $poimagecheck[0]->PO_Image);
                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }
                }
            }
        }
        $userName = Users::where('Id', $data['UserId'])->first();
        if ($data['PO_Number'] != "Add") {
            $purchaseNumberRec = DB::select("select concat(prn.PurchaseNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber from purchasenumber prn inner join financialyear fn on fn.Id=prn.FinancialYearId where prn.Id=" . $data['PO_Number']);
        }
        if ($data['PO_Number'] == "Add") {
            $generate_PO = $this->GetPONumber();
            $PO_Number = $generate_PO['PO_Number'];
            $PO_Number_Financial_Id = $generate_PO['PO_Number_Financial_Id'];
            $Ponumberid = DB::table('purchasenumber')->insertGetId(
                ['PurchaseNumber' => $PO_Number, "FinancialYearId" => $PO_Number_Financial_Id, 'UserId' => $data['UserId'], 'VendorId' => $data['VendorId'], 'MultipleImage' => ($MultipleImageflag == 0 ? 0 : 1), 'PO_Image' => ($MultipleImageflag == 0 ? $PO_Image : ''), 'PoDate' => $data['PoDate'], 'Remarks' => $data['Remarks'], 'CreatedDate' => date('Y-m-d H:i:s')]
            );
            $generatedPONum = (int)$PO_Number_Financial_Id;
            $fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` where Id = $generatedPONum");
            $logPONumber = $PO_Number . '/' . $fin_yr[0]->CurrentFinancialYear;
            UserLogs::create([
                'Module' => 'PO',
                'ModuleNumberId' => $Ponumberid,
                'LogType' => 'Created',
                'LogDescription' => $userName['Name'] . " " . 'created PO Number' . " " . $logPONumber,
                'UserId' => $data['UserId'],
                'updated_at' => null
            ]);
            $artRecor = Article::where('Id', $data['ArticleId'])->first();
            UserLogs::create([
                'Module' => 'PO',
                'ModuleNumberId' => $Ponumberid,
                'LogType' => 'Updated',
                'LogDescription' => $userName['Name'] . " " . 'added article ' . $artRecor->ArticleNumber .  ' in PO Number' . " " . $logPONumber,
                'UserId' => $data['UserId'],
                'updated_at' => null
            ]);
        } else {
            $pon = DB::select("select PurchaseNumber from purchasenumber where Id = " . $data['PO_Number']);
            $PO_Number = $pon[0]->PurchaseNumber;
            $Ponumberid = $data['PO_Number'];
            if (isset($data['PO_Image'])) {
                DB::table('purchasenumber')
                    ->where('Id', $Ponumberid)
                    ->update(['VendorId' => $data['VendorId'], 'MultipleImage' => ($MultipleImageflag == 0 ? 0 : 1), 'PO_Image' => ($MultipleImageflag == 0 ? $PO_Image : ''), 'PoDate' => $data['PoDate'], 'Remarks' => $data['Remarks']]);
            } else {
                DB::table('purchasenumber')
                    ->where('Id', $Ponumberid)
                    ->update(['VendorId' => $data['VendorId'], 'MultipleImage' => ($MultipleImageflag == 0 ? 0 : 1), 'PoDate' => $data['PoDate'], 'Remarks' => $data['Remarks']]);
            }
            $artRecor = Article::where('Id', $data['ArticleId'])->first();
            UserLogs::create([
                'Module' => 'PO',
                'ModuleNumberId' => $Ponumberid,
                'LogType' => 'Updated',
                'LogDescription' => $userName['Name'] . " " . 'added article ' . $artRecor->ArticleNumber .  ' in PO Number' . " " . $purchaseNumberRec[0]->PurchaseNumber,
                'UserId' => $data['UserId'],
                'updated_at' => null
            ]);
        }


        $CatIDs = Article::where('Id', $data['ArticleId'])->first();

        $data['SubCategoryId'] = $CatIDs['SubCategoryId'];
        $data['CategoryId'] = $CatIDs['CategoryId'];

        $data['PO_Number'] = $Ponumberid;
        $data["ArticleId"] = $data['ArticleId'];
        $data['PO_Image'] = ($MultipleImageflag == 1 ? $PO_Image : '');
        $data['BrandId'] = $BrandId;
        PO::create($data);
        return response()->json(array("PO_Id" => $Ponumberid, "PO_Number" => $PO_Number), 200);
    }

    public function GePO()
    {
        return DB::select('select * from (SELECT p.Id, pn.Id as POId, pn.PurchaseNumber, concat(fn.StartYear,\'-\',fn.EndYear) as FinancialYear, v.Name, c.Title, p.ArticleId, p.NumPacks, ar.ArticleNumber, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where InwardArticleId IS NULL order by Id ASC');
    }
    public function PostPo(Request $request)
    {
        $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select("select count(*) as Total from (SELECT p.Id, pn.Id as POId, p.PoDate as pdate, DATE_FORMAT(p.PoDate, '%d/%m/%Y') as PoDate, GROUP_CONCAT(DISTINCT CONCAT(ar.ArticleNumber) ORDER BY ar.Id SEPARATOR ',') as ArticleNumber,concat(pn.PurchaseNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber, v.Name, c.Title, p.PO_Number, GetTotalPOPieces(p.PO_Number) as TotalPieces, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where InwardArticleId IS NULL");
        $vnTotal = $vnddataTotal[0]->Total;
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "and (ddd.PurchaseNumber like '%" . $search['value'] . "%' OR cast(ddd.PoDate as char) like '%" . $search['value'] . "%' OR ddd.ArticleNumber like '%" . $search['value'] . "%' OR ddd.Title like '%" . $search['value'] . "%' OR ddd.Name like '%" . $search['value'] . "%' OR ddd.Title like '%" . $search['value'] . "%' OR ddd.TotalPieces like '%" . $search['value'] . "%')";
            $vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT p.Id, pn.Id as POId, p.PoDate as pdate, DATE_FORMAT(p.PoDate, '%d/%m/%Y') as PoDate, GROUP_CONCAT(DISTINCT CONCAT(ar.ArticleNumber) ORDER BY ar.Id SEPARATOR ',') as ArticleNumber,concat(pn.PurchaseNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber, v.Name, c.Title, p.PO_Number, GetTotalPOPieces(p.PO_Number) as TotalPieces, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where InwardArticleId IS NULL " . $searchstring);
            $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
        } else {
            $searchstring = "";
            $vnddataTotalFilterValue = $vnTotal;
        }
        $column = $data["order"][0]["column"];
        switch ($column) {
            case 1:
                $ordercolumn = "date(ddd.pdate)";
                break;
            case 2:
                $ordercolumn = "CAST(ddd.PurchaseNumber as SIGNED INTEGER)";
                break;
            case 3:
                $ordercolumn = "ddd.Name";
                break;
            case 4:
                $ordercolumn = "CAST(ddd.TotalPieces as SIGNED INTEGER)";
                break;
            default:
                $ordercolumn = "ddd.Id";
                break;
        }
        $order = "";
        if ($data["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
        }
        $vnddata = DB::select("select ddd.* from (SELECT p.Id, pn.Id as POId, p.PoDate as pdate, DATE_FORMAT(p.PoDate, '%d/%m/%Y') as PoDate, GROUP_CONCAT(DISTINCT CONCAT(ar.ArticleNumber) ORDER BY ar.Id SEPARATOR ',') as ArticleNumber, pn.PurchaseNumber as PurchaseNo, concat(pn.PurchaseNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber, v.Name, c.Title, p.PO_Number, GetTotalPOPieces(p.PO_Number) as TotalPieces, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where ddd.InwardArticleId IS NULL " . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length);
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
    public function InwardGetPOList($GRN)
    {
        $getvenderId = DB::select('SELECT count(inw.Id) as Total, p.VendorId FROM `inward` inw left join po p on p.ArticleId=inw.ArticleId where inw.GRN="' . $GRN . '" order by inw.Id asc limit 0,1');
        $VendorId = "";
        if ($getvenderId[0]->Total > 0) {
            $VendorId = " and f.VendorId=" . $getvenderId[0]->VendorId;
        }
        $all =  DB::select("select t.* from (select dd.* from (SELECT p.Id, p.PO_Number, p.VendorId, p.CategoryId, p.BrandId, p.NumPacks, p.PoDate, p.Remarks, p.PO_Image, p.WorkOrderStatusId, p.WorkOrderDate, p.created_at, p.updated_at, prn.ReturnNoPacks, CountNoPacks(prn.ReturnNoPacks) as TotalReturnNoPacks, CountNoPacks(inw.NoPacks) as TotalNoPacks, a.ArticleNumber, pn.PurchaseNumber, inw.ArticleId From po p left join inward inw on inw.ArticleId = p.ArticleId left join purchasenumber pn on pn.Id=p.PO_Number inner join article a on a.Id=p.ArticleId left join purchasereturn prn on prn.ArticleId=p.ArticleId where a.Id in (SELECT Id FROM `article` where ArticleOpenFlag=1)) as dd group by dd.Id UNION select f.* from (SELECT p.Id, p.PO_Number, p.VendorId, p.CategoryId, p.BrandId, p.NumPacks, p.PoDate, p.Remarks, p.PO_Image, p.WorkOrderStatusId, p.WorkOrderDate, p.created_at, p.updated_at, prn.ReturnNoPacks, CountNoPacks(prn.ReturnNoPacks) as TotalReturnNoPacks, CountNoPacks(inw.NoPacks) as TotalNoPacks, a.ArticleNumber, pn.PurchaseNumber, inw.ArticleId From po p left join inward inw on inw.ArticleId = p.ArticleId left join purchasenumber pn on pn.Id=p.PO_Number inner join article a on a.Id=p.ArticleId left join purchasereturn prn on prn.ArticleId=p.ArticleId) as f where f.TotalReturnNoPacks=f.TotalNoPacks or f.ArticleId is NULL " . $VendorId . " group by Id) as t group by t.Id");
        foreach ($all  as  $key => $data) {
            $arrayData = (array)$data;
            $articleOpenFlag = DB::select('select Id , ArticleOpenFlag , ArticleNumber from article where ArticleNumber = "' . $arrayData['ArticleNumber'] . '" order by Id desc limit 0,1');
            if ($articleOpenFlag[0]->ArticleOpenFlag != 1) {
                $inwardCreated = DB::select('select Id , created_at as InwardCreated from inward where ArticleId= "' . $arrayData['ArticleId'] . '" order by Id desc limit 0,1');
                $purchaseReturnCreated = DB::select('select Id ,CreatedDate as PurchaseReturnCreated from purchasereturn where ArticleId= "' . $arrayData['ArticleId'] . '" order by Id desc limit 0,1');
                if ($purchaseReturnCreated) {
                    if ($inwardCreated[0]->InwardCreated > $purchaseReturnCreated[0]->PurchaseReturnCreated) {
                        unset($all[$key]);
                    }
                }
            }
        }
        return array_values($all);
    }

    public function GetPOChallen($id)
    {
        return DB::select("SELECT u.Name as PreparedBy, pn.MultipleImage, pn.PO_Image, p.Id, v.Name, v.Address, v.GSTNumber, pn.PoDate, concat(pn.PurchaseNumber, '/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber, pn.Remarks, ar.ArticleNumber, ar.StyleDescription, br.Name as Brandname, subc.Name as SubcategoryName, r.Series as SeriesNo, c.Title, p.NumPacks, p.PO_Image as MultiplePO_Image From po p inner join article ar on ar.Id=p.ArticleId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fn on fn.Id=pn.FinancialYearId inner join vendor v on v.Id= p.VendorId inner join category c on c.Id=p.CategoryId inner join users u on u.Id=pn.UserId left join brand br on br.Id=p.BrandId left join subcategory subc on subc.Id=ar.SubCategoryId left join rangeseries r on r.Id=ar.SeriesId WHERE pn.Id = " . $id);
    }

    public function DeletePO($id, $POId, $ArtId, $LoggedId)
    {
        DB::beginTransaction();
        try {
            DB::table('articlephotos')->where('ArticlesId', '=', $ArtId)->delete();
            $userName = Users::where('Id', $LoggedId)->first();
            $purchaseNumberRec = DB::select("select a.ArticleNumber, prn.Id as PNId, concat(prn.PurchaseNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber from po p inner join purchasenumber prn on prn.Id=p.PO_Number inner join article a on a.Id=p.ArticleId inner join financialyear fn on fn.Id=prn.FinancialYearId where prn.Id= '" . $POId . "'");
            UserLogs::create([
                'Module' => 'PO',
                'ModuleNumberId' => $purchaseNumberRec[0]->PNId,
                'LogType' => 'Deleted',
                'LogDescription' => $userName['Name'] . ' deleted article ' . $purchaseNumberRec[0]->ArticleNumber . ' from PO Number ' . $purchaseNumberRec[0]->PurchaseNumber,
                'UserId' => $LoggedId,
                'updated_at' => null
            ]);
            DB::table('po')->where('Id', '=', $id)->delete();
            DB::commit();

            return response()->json("SUCCESS", 200);
        } catch (\Exception $e) {
            DB::rollback();
            $status = "Error";
            return response()->json($status, 200);
        }
    }

    public function deletepopon($PON,  $LoggedId)
    {
        $polist = DB::select("SELECT Id, ArticleId FROM `po` where PO_Number ='" . $PON . "'");
        try {
            foreach ($polist as $vl) {
                $id = $vl->Id;
                $ArtId = $vl->ArticleId;
                DB::table('articlephotos')->where('ArticlesId', '=', $ArtId)->delete();
                DB::table('po')->where('Id', '=', $id)->delete();
            }
            $userName = Users::where('Id', $LoggedId)->first();
            $purchaseNumberRec = DB::select("select prn.Id, concat(prn.PurchaseNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber from purchasenumber prn inner join financialyear fn on fn.Id=prn.FinancialYearId where prn.Id= '" . $PON . "'");
            UserLogs::create([
                'Module' => 'PO',
                'ModuleNumberId' => $purchaseNumberRec[0]->Id,
                'LogType' => 'Deleted',
                'LogDescription' => $userName['Name'] . " " . 'deleted PO Number' . " " . $purchaseNumberRec[0]->PurchaseNumber,
                'UserId' => $LoggedId,
                'updated_at' => null
            ]);
            return response()->json("SUCCESS", 200);
        } catch (\Exception $e) {
            $status = "Error";
            return response()->json($status, 200);
        }
    }

    public function UpdatePo(Request $request)
    {
        $data = $request->all();
        if ($data["SubCategoryId"] == "") {
            $data["SubCategoryId"] = 0;
        }
        $flagcheck = DB::select("select ArticleOpenFlag from category where Id = '" . $data["CategoryId"] . "'");
        $ArticleOpenFlag = $flagcheck[0]->ArticleOpenFlag;
        $MultipleImageflag = 0;
        if ($data["MultipleImage"] == "true" || $data["MultipleImage"] == 1) {
            $MultipleImageflag = 1;
        }
        $status = "";
        if ($data['BrandId'] == "") {
            $BrandId = 0;
        } else {
            $BrandId = $data['BrandId'];
        }
        $PO_Image = '';
        if (isset($data['PO_Image'])) {
            $file = $data['PO_Image'];
            $name = $file->getClientOriginalName();
            $randomstring = $this->generateRandomString();
            $name_extension = explode(".", $name);
            $PO_Image = $randomstring . '.' . $name_extension[1];
            $file->move('uploads/po', $PO_Image);
            if ($MultipleImageflag == 1) {
                $poimagecheck = DB::select("select PO_Image from po where Id = '" . $data['id'] . "'");
            } else {
                $poimagecheck = DB::select("select PO_Image from purchasenumber where Id = '" . $data['PO_Number'] . "'");
            }
            if ($poimagecheck[0]->PO_Image != "") {
                $file = public_path('uploads/po/' . $poimagecheck[0]->PO_Image);
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }

        $ActivePO  = PO::where('Id', $data['id'])->first();
        $logDesc = "";
        if ($ActivePO->VendorId  != (int)$data['VendorId']) {
            $logDesc = $logDesc . 'Vendor,';
        }
        if ($ActivePO->Remarks  != $data['Remarks']) {
            $logDesc = $logDesc . 'Remarks,';
        }
        if ($ActivePO->NumPacks != (int)$data['NumPacks']) {
            $logDesc = $logDesc . 'Quantity,';
        }
        if ($ActivePO->WorkOrderStatusId != (int)$data['WorkOrderStatusId']) {
            $logDesc = $logDesc . 'WorkOrderStatus,';
        }
        $newLogDesc = rtrim($logDesc, ',');


        DB::beginTransaction();
        try {
            DB::table('article')
                ->where('Id', $data["ArtId"])
                ->update(["ArticleOpenFlag" => $ArticleOpenFlag, "ArticleStatus" => $ArticleOpenFlag]);
            if ($data['WorkOrderStatusId'] != "") {
                if (isset($data['PO_Image'])) {
                    PO::where('Id', $data['id'])->update(array(
                        'VendorId' => $data['VendorId'],
                        'CategoryId' => (int)$data['CategoryId'],
                        'SubCategoryId' => (int)$data['SubCategoryId'],
                        'NumPacks' => $data['NumPacks'],
                        'PoDate' => $data['PoDate'],
                        'BrandId' => $BrandId,
                        'Remarks' => $data['Remarks'],
                        'PO_Image' => ($MultipleImageflag == 1 ? $PO_Image : ''),
                        'WorkOrderStatusId' => $data['WorkOrderStatusId'],
                        'WorkOrderDate' => $data['WorkOrderDate']
                    ));
                } else {
                    PO::where('Id', $data['id'])->update(array(
                        'VendorId' => $data['VendorId'],
                        'CategoryId' => (int)$data['CategoryId'],
                        'SubCategoryId' => (int)$data['SubCategoryId'],
                        'NumPacks' => $data['NumPacks'],
                        'PoDate' => $data['PoDate'],
                        'BrandId' => $BrandId,
                        'Remarks' => $data['Remarks'],
                        'WorkOrderStatusId' => $data['WorkOrderStatusId'],
                        'WorkOrderDate' => $data['WorkOrderDate']
                    ));
                }
                $poworkorder = DB::select("SELECT count(*) as total FROM `poworkorder` where POID ='" . $data["id"] . "' and WorkOrderStatusId ='" . $data["WorkOrderStatusId"] . "'");
                if ($poworkorder[0]->total > 0) {
                    DB::table('poworkorder')
                        ->where('POID', $data["id"])
                        ->where('WorkOrderStatusId', $data["WorkOrderStatusId"])
                        ->update(['WorkOrderStatusId' => $data['WorkOrderStatusId'], 'CreatedDate' => $data['WorkOrderDate']]);
                } else {
                    DB::table('poworkorder')->insertGetId(
                        ['WorkOrderStatusId' => $data['WorkOrderStatusId'], 'POID' => $data['id'], 'CreatedDate' => $data['WorkOrderDate']]
                    );
                }
            } else {
                if (isset($data['PO_Image'])) {
                    PO::where('Id', $data['id'])->update(array(
                        'VendorId' => $data['VendorId'],
                        'CategoryId' => (int)$data['CategoryId'],
                        'SubCategoryId' => (int)$data['SubCategoryId'],
                        'NumPacks' => $data['NumPacks'],
                        'PoDate' => $data['PoDate'],
                        'PO_Image' => ($MultipleImageflag == 1 ? $PO_Image : ''),
                        'BrandId' => $BrandId,
                        'Remarks' => $data['Remarks']
                    ));
                } else {
                    PO::where('Id', $data['id'])->update(array(
                        'VendorId' => $data['VendorId'],
                        'CategoryId' => (int)$data['CategoryId'],
                        'SubCategoryId' => (int)$data['SubCategoryId'],
                        'NumPacks' => $data['NumPacks'],
                        'PoDate' => $data['PoDate'],
                        'BrandId' => $BrandId,
                        'Remarks' => $data['Remarks']
                    ));
                }
            }
            if (isset($data['PO_Image'])) {

                DB::table('purchasenumber')
                    ->where('Id', $data['PO_Number'])
                    ->update(['VendorId' => $data['VendorId'], 'MultipleImage' => ($MultipleImageflag == 0 ? 0 : 1), 'PO_Image' => ($MultipleImageflag == 0 ? $PO_Image : ''), 'PoDate' => $data['PoDate'], 'Remarks' => $data['Remarks']]);
            } else {
                DB::table('purchasenumber')
                    ->where('Id', $data['PO_Number'])
                    ->update(['VendorId' => $data['VendorId'], 'MultipleImage' => ($MultipleImageflag == 0 ? 0 : 1), 'PoDate' => $data['PoDate'], 'Remarks' => $data['Remarks']]);
            }
            DB::commit();
            $userName = Users::where('Id', $data['UserId'])->first();
            $purchaseNumberRec = DB::select("select prn.Id, concat(prn.PurchaseNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber from purchasenumber prn inner join financialyear fn on fn.Id=prn.FinancialYearId where prn.Id=" . $data['PO_Number']);
            $artRecor = Article::where('Id', $data["ArtId"])->first();
            UserLogs::create([
                'Module' => 'PO',
                'ModuleNumberId' => $data['PO_Number'],
                'LogType' => 'Updated',
                'LogDescription' => $userName->Name . " " . "updated " . $newLogDesc . "of article " . $artRecor->ArticleNumber . " in PO Number " . $purchaseNumberRec[0]->PurchaseNumber,
                'UserId' => $data['UserId'],
                'updated_at' => null
            ]);
            return response()->json("SUCCESS", 200);
        } catch (\Exception $e) {
            DB::rollback();
            $status = $e . "Error";
            return response()->json($status, 200);
        }
    }

    public function GetPoIdWise($id)
    {
        $as = DB::select("SELECT v.Name as vname, p.*, c.Title as CategoryName, subc.Name as SubCategoryName, r.Series, ar.StyleDescription, ar.SeriesId, ar.BrandId, b.Name as BrandName, ar.ArticleOpenFlag, pn.PurchaseNumber, concat(pn.PurchaseNumber, '/', fn.StartYear,'-',fn.EndYear) as FinancialYear, ar.ArticleNumber, ar.StyleDescription From po p left join article ar on ar.Id=p.ArticleId left join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fn on fn.Id=pn.FinancialYearId left join rangeseries r on r.Id=ar.SeriesId left join subcategory subc on subc.Id = p.SubCategoryId left join category c on c.Id = p.CategoryId left join brand b on b.Id=ar.BrandId left join vendor v ON p.VendorId = v.Id WHERE p.Id = " . $id);
        // $as = DB::select("SELECT p.*, c.Title as CategoryName, subc.Name as SubCategoryName, r.Series, ar.StyleDescription, ar.SeriesId, ar.BrandId, b.Name as BrandName, ar.ArticleOpenFlag, pn.PurchaseNumber, concat(pn.PurchaseNumber, '/', fn.StartYear,'-',fn.EndYear) as FinancialYear, ar.ArticleNumber, ar.StyleDescription From po p left join article ar on ar.Id=p.ArticleId left join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fn on fn.Id=pn.FinancialYearId left join rangeseries r on r.Id=ar.SeriesId left join subcategory subc on subc.Id = p.SubCategoryId left join category c on c.Id = p.CategoryId left join brand b on b.Id=ar.BrandId WHERE p.Id = " . $id);
        foreach ($as as $key => $val) {
            $object = (object)$val;
            $object->ArticleOpenFlag = (int)$val->ArticleOpenFlag;
        }
        return $as;
    }

    public function GetPONumber()
    {
        $array = array();
        $fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
        $podata = DB::select('SELECT PurchaseNumber, FinancialYearId From purchasenumber order by Id desc limit 0,1');
        if (count($podata) > 0) {
            if ($fin_yr[0]->Id > $podata[0]->FinancialYearId) {
                $array["PO_Number"] = 1;
                $array["PO_Number_Financial_Id"] = $fin_yr[0]->Id;
                $array["PO_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
                return $array;
            } else {
                $array["PO_Number"] = ($podata[0]->PurchaseNumber) + 1;
                $array["PO_Number_Financial_Id"] = $fin_yr[0]->Id;
                $array["PO_Number_Financial"] = ($podata[0]->PurchaseNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
                return $array;
            }
        } else {
            $array["PO_Number"] = 1;
            $array["PO_Number_Financial_Id"] = $fin_yr[0]->Id;
            $array["PO_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
            return $array;
        }
    }


    public function polistfrompon($id, Request $request)
    {
        
       $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select("select count(*) as Total from (SELECT po.Id, po.PO_Number, po.ArticleId,v.Name, c.Colorflag, c.Title as CategoryName, subc.Name as SubcategoryName, r.Series as SeriesNo, b.Name as BrandName,   pon.PurchaseNumber,ar.ArticleNumber, ar.ArticleOpenFlag,po.NumPacks as NoPacks,pon.PoDate FROM `po` po left join purchasenumber pon on pon.Id=po.PO_Number LEFT JOIN article ar on ar.Id = po.ArticleId left Join category c on c.Id=ar.CategoryId left join subcategory subc on subc.Id=ar.SubCategoryId left join rangeseries r on r.Id= ar.SeriesId inner join vendor v on v.Id=po.VendorId left join articlecolor ac on ac.ArticleId=po.ArticleId left join brand b on b.Id=ar.BrandId where po.PO_Number='" . $id . "' group by po.Id) as d");
        $vnTotal = $vnddataTotal[0]->Total;
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "where d.NoPacks like '%" . $search['value'] . "%' OR d.Name like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%' ";
            $vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT po.Id, po.PO_Number, po.ArticleId,v.Name, c.Colorflag, c.Title as CategoryName, subc.Name as SubcategoryName, r.Series as SeriesNo, b.Name as BrandName,   pon.PurchaseNumber,ar.ArticleNumber, ar.ArticleOpenFlag,po.NumPacks as NoPacks,pon.PoDate FROM `po` po left join purchasenumber pon on pon.Id=po.PO_Number LEFT JOIN article ar on ar.Id = po.ArticleId left Join category c on c.Id=ar.CategoryId left join subcategory subc on subc.Id=ar.SubCategoryId left join rangeseries r on r.Id= ar.SeriesId inner join vendor v on v.Id=po.VendorId left join articlecolor ac on ac.ArticleId=po.ArticleId left join brand b on b.Id=ar.BrandId where po.PO_Number='" . $id . "' group by po.Id) as d "  . $searchstring );
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
                $ordercolumn = "d.CategoryName";
                break;
            case 4:
                $ordercolumn = "d.SubcategoryName";
                break;
                
            case 5:
                $ordercolumn = "d.SeriesNo";
                break;
           
            case 6:
                $ordercolumn = "d.NoPacks";
                break;
            
            default:
                $ordercolumn = "d.OutletDate";
                break;
        }
        $order = "";
        if ($data["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
        }
        $vnddata = DB::select("select d.* from (SELECT po.Id, po.PO_Number, po.ArticleId,v.Name, c.Colorflag, c.Title as CategoryName, subc.Name as SubcategoryName, r.Series as SeriesNo, b.Name as BrandName,   pon.PurchaseNumber,ar.ArticleNumber, ar.ArticleOpenFlag,po.NumPacks as NoPacks,pon.PoDate FROM `po` po left join purchasenumber pon on pon.Id=po.PO_Number LEFT JOIN article ar on ar.Id = po.ArticleId left Join category c on c.Id=ar.CategoryId left join subcategory subc on subc.Id=ar.SubCategoryId left join rangeseries r on r.Id= ar.SeriesId inner join vendor v on v.Id=po.VendorId left join articlecolor ac on ac.ArticleId=po.ArticleId left join brand b on b.Id=ar.BrandId where po.PO_Number='" . $id . "' group by po.Id) as d " . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length );
    
    

        return array(
            'datadraw' => $data["draw"],
            'recordsTotal' => $vnTotal,
            'recordsFiltered' => $vnddataTotalFilterValue,
            'response' => 'success',
            'startnumber' => $startnumber,
            'search' => count($vnddata),
            'data' => $vnddata,
        );
	    
	    
        
        
        // $as = DB::select("SELECT po.Id, po.PO_Number, po.ArticleId,v.Name, c.Colorflag, c.Title as CategoryName, subc.Name as SubcategoryName, r.Series as SeriesNo, b.Name as BrandName,   pon.PurchaseNumber,ar.ArticleNumber, ar.ArticleOpenFlag,po.NumPacks as NoPacks,pon.PoDate FROM `po` po left join purchasenumber pon on pon.Id=po.PO_Number LEFT JOIN article ar on ar.Id = po.ArticleId left Join category c on c.Id=ar.CategoryId left join subcategory subc on subc.Id=ar.SubCategoryId left join rangeseries r on r.Id= ar.SeriesId inner join vendor v on v.Id=po.VendorId left join articlecolor ac on ac.ArticleId=po.ArticleId left join brand b on b.Id=ar.BrandId where po.PO_Number='" . $id . "' group by po.Id");
        // return $as;
    }


    // public function polistfrompon($id)
    // {
    //     $as = DB::select("SELECT po.Id, po.PO_Number, po.ArticleId,v.Name, c.Colorflag, c.Title as CategoryName, subc.Name as SubcategoryName, r.Series as SeriesNo, b.Name as BrandName,   pon.PurchaseNumber,ar.ArticleNumber, ar.ArticleOpenFlag,po.NumPacks as NoPacks,pon.PoDate FROM `po` po left join purchasenumber pon on pon.Id=po.PO_Number LEFT JOIN article ar on ar.Id = po.ArticleId left Join category c on c.Id=ar.CategoryId left join subcategory subc on subc.Id=ar.SubCategoryId left join rangeseries r on r.Id= ar.SeriesId inner join vendor v on v.Id=po.VendorId left join articlecolor ac on ac.ArticleId=po.ArticleId left join brand b on b.Id=ar.BrandId where po.PO_Number='" . $id . "' group by po.Id");
    //     return $as;
    // }

    public function podateremarkfromPO($id)
    {
        // return DB::select("SELECT pn.*, v.Name, CONCAT( pn.PurchaseNumber, '/', fn.StartYear, '-', fn.EndYear ) AS PO_Number_FinancialYear FROM `purchasenumber` pn INNER JOIN financialyear fn ON fn.Id = pn.FinancialYearId LEFT JOIN vendor v ON pn.VendorId = v.Id");

        return DB::select("SELECT pn.*,  v.Name, concat(pn.PurchaseNumber, '/',fn.StartYear,'-',fn.EndYear) as PO_Number_FinancialYear FROM `purchasenumber` pn inner join financialyear fn on fn.Id=pn.FinancialYearId LEFT JOIN vendor v ON pn.VendorId = v.Id where pn.Id = '" . $id . "'");

        // return DB::select("SELECT pn.*, concat(pn.PurchaseNumber, '/',fn.StartYear,'-',fn.EndYear) as PO_Number_FinancialYear FROM `purchasenumber` pn inner join financialyear fn on fn.Id=pn.FinancialYearId where pn.Id = '" . $id . "'");
    }

    public function GetArtical()
    {
        return  DB::select('SELECT * FROM `article` where ArticleStatus="1"');
    }

    public function GetArticalForOutlet($id)
    {
        // return $id.'ddd';
        return  DB::select('SELECT a.* FROM `article` a inner join outletimport oi ON oi.ArticleId = a.Id where a.ArticleStatus="1" and oi.PartyId ='. $id);
    }

    public function GetWithoutOpenFlagArtical()
    {
        // Old
        // return  DB::select('SELECT * FROM article WHERE ArticleStatus = "1" AND ArticleOpenFlag = "0" AND NOT EXISTS ( SELECT 1 FROM articlelaunch WHERE articlelaunch.ArticleID = article.id )');
        
        //New
        return  DB::select('select * from (select * from (SELECT art.*, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId group by inw.Id union all SELECT a.*, m.ArticleId, m.NoPacks, m.SalesNoPacks, 0 FROM `mixnopacks` m inner join article a on a.Id=m.ArticleId where m.NoPacks!=0) as t where SalesNoPacksCheck!=1 and t.ArticleStatus = 1 group by t.Id) d WHERE d.ArticleOpenFlag = 0');
    }

    public function getArticalEditData($Id)
    {
        // return DB::select('SELECT a.ArticleNumber, c.Title, ar.ArticleRate, p.Name, i.InwardDate, al.LaunchDate, al.`PartyId` FROM `article` a INNER JOIN articlerate ar ON a.Id = ar.ArticleId INNER JOIN category c ON c.Id = a.CategoryId INNER JOIN inward i ON i.ArticleId = a.Id INNER JOIN articlelaunch al ON al.ArticleId = a.Id INNER JOIN `party` p ON p.Id = al.PartyId where ar.ArticleId = "' . $Id . '"');
        return DB::select('SELECT a.ArticleNumber, c.Title, ar.ArticleRate, i.InwardDate, al.LaunchDate, al.`PartyId` FROM `article` a INNER JOIN articlerate ar ON a.Id = ar.ArticleId INNER JOIN category c ON c.Id = a.CategoryId INNER JOIN inward i ON i.ArticleId = a.Id INNER JOIN articlelaunch al ON al.ArticleId = a.Id where ar.ArticleId = "' . $Id . '"');
    }

    public function addArticleLaunch(Request $request)
    {
        $data = $request->all();

        $userName = Users::where('Id', $data['UserId'])->first();
        
        $artRecord = Article::where('Id', $data['ArticleId'])->first();

        if($data['PartyId'] == 0){
            Articlelaunch::create([
                'ArticleId' => $data['ArticleId'],
                'LaunchDate' => $data['ArticleInDate'],
                'PartyId' => 0
            ]);


            UserLogs::create([
                'Module' => 'Article Launch',
                'LogType' => 'Launched',
                'ModuleNumberId' => $artRecord->Id,
                'LogDescription' => $userName['Name'] . " " . 'Launch article of Article number' . " " . $artRecord->ArticleNumber . " For all parties",
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);

        }else{
            foreach ($data['PartyId'] as $id){
                Articlelaunch::create([
                    'ArticleId' => $data['ArticleId'],
                    'LaunchDate' => $data['ArticleInDate'],
                    'PartyId' => $id['Id']
                ]);
            }

            UserLogs::create([
                'Module' => 'Article Launch',
                'LogType' => 'Launched',
                'ModuleNumberId' => $artRecord->Id,
                'LogDescription' => $userName['Name'] . " " . 'Launch article of Article number' . " " . $artRecord->ArticleNumber,
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);
        }
  




       
        return response()->json(array("Id" => "Success"), 200);
    }


    public function editArticleLaunch(Request $request)
    {
        $data = $request->all();

        $userName = Users::where('Id', $data['UserId'])->first();
        
        $artRecord = Article::where('Id', $data['ArticleId'])->first();

  
        
        
        if($data['PartyId'] == 0){

            // Articlelaunch::where('ArticleId', $data['ArticleId'])->delete();

            Articlelaunch::where('ArticleId', $data['ArticleId'])
            ->update([
                'ArticleId' => $data['ArticleId'],
                'LaunchDate' => $data['ArticleInDate'],
                'PartyId' => 0
            ]);


            UserLogs::create([
                'Module' => 'Article Launch',
                'LogType' => 'Launched',
                'ModuleNumberId' => $artRecord->Id,
                'LogDescription' => $userName['Name'] . " " . 'Launch article of Article number' . " " . $artRecord->ArticleNumber . " For all parties",
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);

        }else{
            $filteredData = collect($data['PartyId'])->unique('Id')->values()->all();

            $filteredData = array_filter($filteredData, function($value) {
                return $value !== null;
            });

            // return $filteredData; 
            Articlelaunch::where('ArticleId', $data['ArticleId'])->delete();
            foreach ($filteredData as $id){
                    Articlelaunch::create([
                        'ArticleId' => $data['ArticleId'],
                        'LaunchDate' => $data['ArticleInDate'],
                        'PartyId' => $id['Id']
                    ]);
            }

            UserLogs::create([
                'Module' => 'Article Launch',
                'LogType' => 'Launched',
                'ModuleNumberId' => $artRecord->Id,
                'LogDescription' => $userName['Name'] . " " . 'Launch article of Article number' . " " . $artRecord->ArticleNumber,
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);
        }
  
        
        // Articlelaunch::where('ArticleId', $data['ArticleId'])
        // ->update([
        //     'ArticleId' => $data['ArticleId'],
        //     'LaunchDate' => $data['ArticleInDate'],
        //     'PartyId' => $data['PartyId']
        // ]);

        // UserLogs::create([
        //     'Module' => 'Article Launch',
        //     'LogType' => 'Launched',
        //     'ModuleNumberId' => $artRecord->Id,
        //     'LogDescription' => $userName['Name'] . " " . 'Edit Launch article of Article number' . " " . $artRecord->ArticleNumber,
        //     'UserId' => $userName['Id'],
        //     'updated_at' => null
        // ]);
 






        return response()->json(array("Id" => "Success"), 200);
    }

    public function articleLaunchList(Request $request)
    {
        $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select("SELECT COUNT(ff.Id) AS Total FROM ( SELECT al.id AS alid, al.PartyId, a.ArticleNumber, CASE WHEN al.PartyId = 0 THEN DATE_FORMAT(al.LaunchDate, '%d-%m-%Y') ELSE '-' END AS LaunchDate, DATE_FORMAT(i.InwardDate, '%d-%m-%Y') as InwardDate, a.StyleDescription, atr.ArticleRate, c.Title AS CategoryName, sc.Name AS SubCategory, b.Name AS BrandName, r.Series, a.Id FROM article a INNER JOIN articlerate atr ON atr.ArticleId = a.Id LEFT JOIN category c ON c.Id = a.CategoryId LEFT JOIN rangeseries r ON r.Id = a.SeriesId LEFT JOIN subcategory sc ON sc.Id = r.SubCategoryId LEFT JOIN brand b ON b.Id = a.BrandId INNER JOIN articlelaunch al ON al.ArticleId = a.Id INNER JOIN inward i ON i.ArticleId = al.ArticleId GROUP BY a.ArticleNumber ) AS ff");
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "WHERE d.ArticleNumber like '%" . $search['value'] . "%'";
            $vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT al.id AS alid, al.PartyId, a.ArticleNumber, CASE WHEN al.PartyId = 0 THEN DATE_FORMAT(al.LaunchDate, '%d-%m-%Y') ELSE '-' END AS LaunchDate, DATE_FORMAT(i.InwardDate, '%d-%m-%Y') as InwardDate, a.StyleDescription, atr.ArticleRate, c.Title AS CategoryName, sc.Name AS SubCategory, b.Name AS BrandName, r.Series, a.Id FROM article a INNER JOIN articlerate atr ON atr.ArticleId = a.Id LEFT JOIN category c ON c.Id = a.CategoryId LEFT JOIN rangeseries r ON r.Id = a.SeriesId LEFT JOIN subcategory sc ON sc.Id = r.SubCategoryId LEFT JOIN brand b ON b.Id = a.BrandId INNER JOIN articlelaunch al ON al.ArticleId = a.Id INNER JOIN inward i ON i.ArticleId = al.ArticleId GROUP BY a.ArticleNumber) as d " . $searchstring);
            $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
        } else {
            $searchstring = "";
            $vnddataTotalFilterValue = $vnddataTotal[0]->Total;
        }
        $column = $data["order"][0]["column"];
        switch ($column) {
            case 1:
                $ordercolumn = "Id";
                break;
            case 2:
                $ordercolumn = "CategoryName";
                break;
            case 3:
                $ordercolumn = "SubCategory";
                break;
            case 4:
                $ordercolumn = "Series";
                break;
            case 5:
                $ordercolumn = "LaunchDate";
                break;
            case 6:
                $ordercolumn = "InwardDate";
                break;    
            default:
                $ordercolumn = "Id";
                break;
        }
        $order = "";
        if ($data["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
        }

        // $vnddata= DB::select("SELECT '-' AS alid, '-' AS PartyId, a.ArticleNumber, '-' AS LaunchDate, DATE_FORMAT(i.InwardDate, '%d-%m-%Y') AS InwardDate, a.StyleDescription, atr.ArticleRate, c.Title AS CategoryName, sc.Name AS SubCategory, b.Name AS BrandName, r.Series, 0 as Id FROM article a INNER JOIN articlerate atr ON atr.ArticleId = a.Id LEFT JOIN category c ON c.Id = a.CategoryId LEFT JOIN rangeseries r ON r.Id = a.SeriesId LEFT JOIN subcategory sc ON sc.Id = r.SubCategoryId LEFT JOIN brand b ON b.Id = a.BrandId INNER JOIN inward i ON i.ArticleId = a.Id WHERE a.ArticleStatus = 1 GROUP BY a.ArticleNumber UNION SELECT al.id AS alid, al.PartyId, a.ArticleNumber, CASE WHEN al.PartyId = 0 THEN DATE_FORMAT(al.LaunchDate, '%d-%m-%Y') ELSE '-' END AS LaunchDate, DATE_FORMAT(i.InwardDate, '%d-%m-%Y') as InwardDate, a.StyleDescription, atr.ArticleRate, c.Title AS CategoryName, sc.Name AS SubCategory, b.Name AS BrandName, r.Series, a.Id FROM article a INNER JOIN articlerate atr ON atr.ArticleId = a.Id LEFT JOIN category c ON c.Id = a.CategoryId LEFT JOIN rangeseries r ON r.Id = a.SeriesId LEFT JOIN subcategory sc ON sc.Id = r.SubCategoryId LEFT JOIN brand b ON b.Id = a.BrandId INNER JOIN articlelaunch al ON al.ArticleId = a.Id INNER JOIN inward i ON i.ArticleId = al.ArticleId " . $searchstring . " GROUP BY a.ArticleNumber  " . $order . " limit " . $data["start"] . "," . $length);
        $vnddata= DB::select("select d.* from (SELECT al.id AS alid, al.PartyId, a.ArticleNumber, CASE WHEN al.PartyId = 0 THEN DATE_FORMAT(al.LaunchDate, '%d-%m-%Y') ELSE '-' END AS LaunchDate, DATE_FORMAT(i.InwardDate, '%d-%m-%Y') as InwardDate, a.StyleDescription, atr.ArticleRate, c.Title AS CategoryName, sc.Name AS SubCategory, b.Name AS BrandName, r.Series, a.Id FROM article a INNER JOIN articlerate atr ON atr.ArticleId = a.Id LEFT JOIN category c ON c.Id = a.CategoryId LEFT JOIN rangeseries r ON r.Id = a.SeriesId LEFT JOIN subcategory sc ON sc.Id = r.SubCategoryId LEFT JOIN brand b ON b.Id = a.BrandId INNER JOIN articlelaunch al ON al.ArticleId = a.Id INNER JOIN inward i ON i.ArticleId = al.ArticleId GROUP BY a.ArticleNumber) as d " . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length);
       
        // $vnddata = DB::select("SELECT a.ArticleNumber, al.LaunchDate, a.StyleDescription, a.ArticleRate, c.Title as CategoryName, sc.Name as SubCategory, b.Name as BrandName, r.Series, a.Id From article a left join category c on c.Id=a.CategoryId left join rangeseries r on r.Id=a.SeriesId left join subcategory sc on sc.Id=r.SubCategoryId left join brand b on b.Id=a.BrandId INNER JOIN articlelaunch al ON al.ArticleId = a.Id " . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length);
        return array(
            'datadraw' => $data["draw"],
            'recordsTotal' => $vnddataTotal[0]->Total,
            'recordsFiltered' => $vnddataTotalFilterValue,
            'response' => 'success',
            'startnumber' => $startnumber,
            'search' => count($vnddata),
            'data' => $vnddata,
        );
    }



    // public function approvedarticallist()
    // {
    //     return DB::select('SELECT * FROM `article` where ArticleStatus="1" ');
    // }

    //adding aditional code 
//change the functionality

public function approvedarticallist($id)
{
    return DB::select('SELECT * FROM `article` a 
                       INNER JOIN `articlephotos` ap ON a.Id = ap.ArticlesId
                       WHERE a.Id = ' . $id);
}

    public function GetArticalIdWise($id)
    {
        $pocheck = PO::where('id', $id)->first();
        $ArtId = $pocheck['ArticleId'];

        if($pocheck['SubCategoryId'] == 0 || $pocheck['CategoryId'] == 0){
            $fixPoByArt = Article::where('id', $ArtId)->first();
          
            PO::where('id', $id)->Update([
                'SubCategoryId' => $fixPoByArt['SubCategoryId'],
                'CategoryId' => $fixPoByArt['CategoryId']
            ]);
        }

        return DB::select("SELECT art.ArticleRate,c.Id as CategoryId , p.NumPacks, purn.PurchaseNumber, concat(purn.PurchaseNumber, '/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber_FinancialYear, ar.Id as ArticleId, ar.ArticleNumber,ar.ArticleStatus,ar.ArticleOpenFlag, ar.StyleDescription, subc.Id as SubCategoryId , subc.Name as Subcategorytitle, rs.SeriesName ,  c.Colorflag, c.Title, p.VendorId as VID, v.Name, br.Name as BrandName FROM `article` ar INNER JOIN po p on ar.Id = p.ArticleId INNER JOIN category c on c.Id=p.CategoryId  INNER JOIN subcategory subc on subc.Id=p.SubCategoryId  LEFT join rangeseries  rs on rs.Id=ar.SeriesId   INNER join vendor v on v.Id=p.VendorId inner join purchasenumber purn on purn.Id=p.PO_Number left join brand br on br.Id=ar.BrandId inner join financialyear fn on fn.Id=purn.FinancialYearId  left join articlerate art on art.ArticleId=ar.Id WHERE p.Id='" . $id . "'");
   }



    public function GetArticaldata($Id)
    {   
        if(isset($Id)){
            return DB::select('SELECT a.ArticleNumber, c.Title, ar.ArticleRate, latest.LatestInwardDate AS InwardDate FROM `article` a INNER JOIN articlerate ar ON a.Id = ar.ArticleId INNER JOIN category c ON c.Id = a.CategoryId INNER JOIN ( SELECT ArticleId, MAX(InwardDate) AS LatestInwardDate FROM inward GROUP BY ArticleId ) latest ON latest.ArticleId = a.Id');
        }else{
            return DB::select('SELECT a.ArticleNumber, c.Title, ar.ArticleRate, i.InwardDate FROM `article` a INNER JOIN articlerate ar ON a.Id = ar.ArticleId INNER JOIN category c ON c.Id = a.CategoryId INNER JOIN inward i ON i.ArticleId = a.Id where ar.ArticleId = "' . $Id . '"');
        }
    }
    
    public function GetAnArticaldata($Id){
        return DB::select('SELECT a.ArticleNumber, c.Title, ar.ArticleRate, i.InwardDate FROM `article` a INNER JOIN articlerate ar ON a.Id = ar.ArticleId INNER JOIN category c ON c.Id = a.CategoryId INNER JOIN inward i ON i.ArticleId = a.Id where ar.ArticleId = "' . $Id . '"');
    }

    public function AddArticleRateChange(Request $request)
    {
        
        $data = $request->all();
        $userName = Users::where('Id', $data['UserId'])->first();
        $artRateRecord = Article::where('Id', $data['ArticleId'])->first();
        $articledata = DB::select("select count(*) as Total from articlerate where ArticleId = '" . $data['ArticleId'] . "'");
        if ($articledata[0]->Total > 0) {
            DB::table('articlerate')
                ->where('ArticleId', $data['ArticleId'])
                ->update(['ArticleRate' => $data['ArticleRate'], 'UpdatedDate' => date("Y-m-d H:i:s")]);
            UserLogs::create([
                'Module' => 'Article Rate Change',
                'ModuleNumberId' => $artRateRecord->Id,
                'LogType' => 'Updated',
                'LogDescription' => $userName['Name'] . " " . 'changed article rate of Article number' . " " . $artRateRecord->ArticleNumber,
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);
        } else {
            DB::table('articlerate')->insertGetId(
                ['ArticleId' => $data['ArticleId'], 'ArticleRate' => $data['ArticleRate'], 'CreatedDate' => date("Y-m-d H:i:s")]
            );
            UserLogs::create([
                'Module' => 'Article Rate Change',
                'ModuleNumberId' => $artRateRecord->Id,
                'LogType' => 'Created',
                'LogDescription' => $userName['Name'] . " " . 'changed article rate of Article number' . " " . $artRateRecord->ArticleNumber,
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);
        }
        return response()->json(array("Id" => "Success"), 200);
    }

    public function ArticleRateAssignSO()
    {
        $data = DB::select("select * from articlerate");
        foreach ($data as $vl) {
            $ArticleId = $vl->ArticleId;
            $ArticleRate = $vl->ArticleRate;

            DB::table('so')
                ->where('ArticleId', $ArticleId)
                ->update(['ArticleRate' => $ArticleRate]);
        }
    }

    public function AddworkOrder(Request $request)
    {
        $data = $request->all();
        $dataresult = DB::select('SELECT * FROM `workorderstatus` WHERE `Name` LIKE "' . $data['Name'] . '"');
        if ($dataresult) {
            return response()->json('allreadyexits', 201);
        } else {
            DB::table('workorderstatus')->insertGetId(
                ['Name' => $data['Name']]
            );
            return response()->json("SUCCESS", 201);
        }
    }
    public function GetworkOrderlist()
    {
        return WorkOrderStatus::all();
    }

    public function PostworkOrderlist(Request $request)
    {
        $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select("SELECT count(*) as Total From workorderstatus");
        $ventotal =  $vnddataTotal[0]->Total;
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "WHERE Name like '%" . $search['value'] . "%'";
            $vnddataTotalFilter = DB::select("SELECT count(*) as Total From workorderstatus " . $searchstring);
            $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
        } else {
            $searchstring = "";
            $vnddataTotalFilterValue = $ventotal;
        }
        $column = $data["order"][0]["column"];
        switch ($column) {
            case 1:
                $ordercolumn = "Name";
                break;
            default:
                $ordercolumn = "Name";
                break;
        }
        $order = "";
        if ($data["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
        }
        $vnddata = DB::select("SELECT * From workorderstatus " . $searchstring . "" . $order . " limit " . $data["start"] . "," . $length);
        return array(
            'datadraw' => $data["draw"],
            'recordsTotal' => $ventotal,
            'recordsFiltered' => $vnddataTotalFilterValue,
            'response' => 'success',
            'startnumber' => $startnumber,
            'search' => count($vnddata),
            'data' => $vnddata,
        );
    }

    public function UpdateworkOrder(Request $request)
    {
        $data = $request->all();
        DB::table('workorderstatus')
            ->where('Id', $data["id"])
            ->update(['Name' => $data["Name"]]);
        return response()->json("SUCCESS", 200);
    }
    public function DeleteworkOrder($id)
    {
        return DB::table('workorderstatus')->where('Id', '=', $id)->delete();
    }
    public function GetworkOrderidwise($id)
    {
        return DB::select('SELECT * From workorderstatus WHERE Id = ' . $id . '');
    }
    public function getPurchaseReturnIdWise($id)
    {
        //added by yashvi
                return DB::select("select v.Name AS vname, a.*,c.Id as CategoryId ,c.Colorflag, c.Title as Category ,inw.SalesNoPacks ,  inw.GRN as InwardNumberId , concat(inwn.GRN,'/', fn.StartYear,'-',fn.EndYear) as GRNnumber ,  purretu.VendorId , purretu.Remark , purretu.ArticleId ,purretu.InwardId , purretu.RemainingNoPacks, purretu.ReturnNoPacks , a.ArticleNumber from purchasereturn purretu inner join inward inw on inw.Id=purretu.InwardId inner join inwardgrn inwn on inwn.Id=inw.GRN inner join financialyear fn on fn.Id=inwn.FinancialYearId inner join article a on a.Id=purretu.ArticleId inner join category c on c.Id=a.CategoryId  LEFT JOIN vendor v ON purretu.VendorId = v.Id where purretu.Id=" . $id);
        // return DB::select("select a.*,c.Id as CategoryId ,c.Colorflag, c.Title as Category ,inw.SalesNoPacks ,  inw.GRN as InwardNumberId , concat(inwn.GRN,'/', fn.StartYear,'-',fn.EndYear) as GRNnumber ,  purretu.VendorId , purretu.Remark , purretu.ArticleId ,purretu.InwardId , purretu.RemainingNoPacks, purretu.ReturnNoPacks , a.ArticleNumber from purchasereturn purretu inner join inward inw on inw.Id=purretu.InwardId inner join inwardgrn inwn on inwn.Id=inw.GRN inner join financialyear fn on fn.Id=inwn.FinancialYearId inner join article a on a.Id=purretu.ArticleId inner join category c on c.Id=a.CategoryId where purretu.Id=" . $id);
    }

    public function updatePurchaseReturn(Request $request)
    {
        $data = $request->all();
        $artRecor = Article::where('Id', $data['ArticleId'])->first();
        $userName = Users::where('Id', $data['UserId'])->first();
        $purRetRec = DB::select("select pn.Id as PurchaseReturnNumberId, concat(pn.PurchaseReturnNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseReturnnumber from purchasereturnnumber pn inner join financialyear fn on fn.Id=pn.FinancialYearId where pn.Id= '" . $data['PRNumberId'] . "'");



        $purchaseReturnRec = DB::select("select pr.Id as purchasereturnId, pr.ReturnNoPacks , pr.TotalNoPacks ,pr.PurchaseReturnNumber ,  inw.SalesNoPacks, inw.Id as InwardId from  purchasereturn pr inner join inward inw on inw.Id=pr.InwardId where  pr.PurchaseReturnNumber=" . $data['PRNumberId'] . " and pr.ArticleId=" . $data['ArticleId']);
        if ($data['ArticleOpenFlag'] == 1) {
            $noPacks = (int)$data['NoPacks'];
            $NoPacksNew = (int)$data['NoPacksNew'];
            $returnsPacks  = $purchaseReturnRec[0]->ReturnNoPacks;
            if ($NoPacksNew <= $noPacks + (int)$returnsPacks) {
                $newInwardSalesNoPacks = ((int)$purchaseReturnRec[0]->SalesNoPacks  + (int)$returnsPacks) - $NoPacksNew;
                // Inward::where('Id', $purchaseReturnRec[0]->InwardId)->update([
                //     'SalesNoPacks' => $newInwardSalesNoPacks
                // ]);
                DB::table('mixnopacks')->where('ArticleId', $data['ArticleId'])->update([
                    "NoPacks" => $newInwardSalesNoPacks
                ]);
                $ActivePurchaseReturn = Purchasereturns::where('Id', $purchaseReturnRec[0]->purchasereturnId)->first();
                $logDesc = "";
                if ($ActivePurchaseReturn->Remark  != $data['Remark']) {
                    $logDesc = $logDesc . 'Remark,';
                }
                if ($ActivePurchaseReturn->ReturnNoPacks  != $NoPacksNew) {
                    $logDesc = $logDesc . 'Pieces,';
                }
                $newLogDesc = rtrim($logDesc, ',');
                Purchasereturns::where(['PurchaseReturnNumber' => $data['PRNumberId'], 'ArticleId' => $data['ArticleId']])->update([
                    'Remark' => $data['Remark'],
                    'ReturnNoPacks' => $NoPacksNew,
                    'RemainingNoPacks' => (int)$purchaseReturnRec[0]->TotalNoPacks - (int)$NoPacksNew
                ]);
                UserLogs::create([
                    'Module' => 'Sales Return',
                    'ModuleNumberId' => $purRetRec[0]->PurchaseReturnNumberId,
                    'LogType' => 'Updated',
                    'LogDescription' => $userName->Name . ' upadated ' . $newLogDesc . ' of article ' . $artRecor->ArticleNumber . ' in PurchaseReturn Number ' . $purRetRec[0]->PurchaseReturnnumber,
                    'UserId' => $userName->Id,
                    'updated_at' => null
                ]);
                return response()->json(["status" => "success", "id" => $purchaseReturnRec[0]->PurchaseReturnNumber], 200);
            } else {
                return response()->json(["status" => "failed", "NoOfSetNotMatch" => true], 200);
            }
        } else {
            // if (strpos($purchaseReturnRec[0]->ReturnNoPacks, ',') !== false) {
            $returnNoPacks = explode(",", $purchaseReturnRec[0]->ReturnNoPacks);
            $salesNoPacks = explode(",", $purchaseReturnRec[0]->SalesNoPacks);
            $newsalesNoPacks = $salesNoPacks;
            $articleSelectedColors = $data['ArticleSelectedColor'];

            $newReturnNoPacks = "";
            $count = 0;

            foreach ($articleSelectedColors as $articleSelectedColor) {
                $newsalesNoPacks[$count] = ($salesNoPacks[$count] + $returnNoPacks[$count]) - $data["NoPacksNew_" . $articleSelectedColor['Id']];
                if ($data["NoPacksNew_" . $articleSelectedColor['Id']] > ($salesNoPacks[$count] + $returnNoPacks[$count])) {
                    return response()->json(["status" => "failed", "NoOfSetNotMatch" => true], 200);
                }
                if (count($articleSelectedColors) == $count + 1) {
                    $newReturnNoPacks = $newReturnNoPacks . $data["NoPacksNew_" . $articleSelectedColor['Id']];
                } else {
                    $newReturnNoPacks = $newReturnNoPacks . $data["NoPacksNew_" . $articleSelectedColor['Id']] . ",";
                }
                $count  = $count + 1;
            }
            $newRemainingPacks = explode(",", (int)$purchaseReturnRec[0]->TotalNoPacks);
            $countRem = 0;
            foreach ($newRemainingPacks as $newRemainingPack) {
                $newRemainingPack[$countRem] -  $newReturnNoPacks[$countRem];
            }
            Inward::where('Id', $purchaseReturnRec[0]->InwardId)->update([
                'SalesNoPacks' => implode(",", $newsalesNoPacks)
            ]);
            $ActivePurchaseReturn = Purchasereturns::where('Id', $purchaseReturnRec[0]->purchasereturnId)->first();
            $logDesc = "";
            if ($ActivePurchaseReturn->Remark  != $data['Remark']) {
                $logDesc = $logDesc . 'Remark,';
            }
            if ($ActivePurchaseReturn->ReturnNoPacks  != $newReturnNoPacks) {
                $logDesc = $logDesc . 'Pieces,';
            }
            $newLogDesc = rtrim($logDesc, ',');
            Purchasereturns::where(['PurchaseReturnNumber' => $data['PRNumberId'], 'ArticleId' => $data['ArticleId']])->update([
                'Remark' => $data['Remark'],
                'ReturnNoPacks' => $newReturnNoPacks,
                'RemainingNoPacks' => implode(",", $newRemainingPacks)
            ]);
            UserLogs::create([
                'Module' => 'Sales Return',
                'ModuleNumberId' => $purRetRec[0]->PurchaseReturnNumberId,
                'LogType' => 'Updated',
                'LogDescription' => $userName->Name . ' upadated ' . $newLogDesc . ' of article ' . $artRecor->ArticleNumber . ' in PurchaseReturn Number ' . $purRetRec[0]->PurchaseReturnnumber,
                'UserId' => $userName->Id,
                'updated_at' => null
            ]);
            return response()->json(["status" => "success", "id" => $purchaseReturnRec[0]->PurchaseReturnNumber], 200);
            // }
            // else {
            //     $noPacks = (int)$data['NoPacks'];
            //     $NoPacksNew = (int)$data['NoPacksNew'];
            //     $returnsPacks  = $purchaseReturnRec[0]->ReturnNoPacks;
            //     if ($NoPacksNew <= $noPacks + (int)$returnsPacks) {
            //         $newInwardSalesNoPacks = ((int)$purchaseReturnRec[0]->SalesNoPacks  + (int)$returnsPacks) - $NoPacksNew;
            //         Inward::where('Id', $purchaseReturnRec[0]->InwardId)->update([
            //             'SalesNoPacks' => $newInwardSalesNoPacks
            //         ]);
            //         Purchasereturns::where(['PurchaseReturnNumber' => $data['PRNumberId'], 'ArticleId' => $data['ArticleId']])->update([
            //             'Remark' => $data['Remark'],
            //             'ReturnNoPacks' => $NoPacksNew,
            //             'RemainingNoPacks'=>(int)$purchaseReturnRec[0]->TotalNoPacks - (int)$NoPacksNew
            //         ]);
            //         return response()->json(["status" => "success", "id" => $purchaseReturnRec[0]->PurchaseReturnNumber], 200);
            //     } else {
            //         return response()->json(["status" => "failed", "NoOfSetNotMatch" => true], 200);
            //     }
            // }
        }
        // return ["data" => $data];
    }
    public function POLogs($NumberId)
    {
        return DB::select("select * from (select ul.Id as UserLogsId, ul.Module, ul.LogType, ul.ModuleNumberId, ul.LogDescription, DATE_FORMAT(ul.created_at ,'%d-%m-%Y') as CreatedDate, DATE_FORMAT(ul.created_at ,'%H:%i:%s') as CreatedTime, u.Name, u.Status, ur.Role from userlogs ul inner join users u on u.Id=ul.UserId inner join userrole ur on ur.RoleType=u.Role) as dd where dd.ModuleNumberId= '". $NumberId ."' and dd.Module='PO' order by dd.UserLogsId desc ");
    }
}