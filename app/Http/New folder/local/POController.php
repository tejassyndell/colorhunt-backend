<?php

namespace App\Http\Controllers;

use App\PO;
use App\WorkOrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use File;

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
	
	public function checkimage($id){
		//Storage::disk('local')->put('subhash.txt', 'Contents');
		//Storage::prepend('file.log', 'Prepended Text');
		////Storage::append('file.log', 'Appended  Text');
		//$poimagecheck = DB::select("select PO_Image from purchasenumber where Id = '".$id."'");
		//return $poimagecheck[0]->PO_Image;
		//return Storage::exists('po/'.$poimagecheck[0]->PO_Image);
		$poimagecheck = DB::select("select PO_Image from purchasenumber where Id = '".$id."'");
		
				if($poimagecheck[0]->PO_Image!=""){
					$file = public_path('uploads/po/'.$poimagecheck[0]->PO_Image);
					if(file_exists($file))
					{
						unlink($file);
					}
				}
				
		/* $file = public_path('uploads/po/'.$poimagecheck[0]->PO_Image);
		//$file1 = 'uploads/po/'.$poimagecheck[0]->PO_Image;
		//return $file;
				if(file_exists($file))
				{
					//return "found";
					// 1. possibility
					//Storage::delete('po/'.$poimagecheck[0]);
					// 2. possibility
					unlink($file);
				}else{
					return "not found";
				} */
	}
	
	private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    ///PO Module
    public function AddPO(Request $request)
    {
		$data = $request->all();
		//return $data; exit;
		
		if($data["SubCategoryId"]==""){
			$data["SubCategoryId"] = 0;
		}
		
		/* $Ponumberid = DB::table('purchasenumber')->insertGetId(
                ['PurchaseNumber' =>  $data['PO_Number'],"FinancialYearId"=>$fin_yr[0]->Id,"UserId"=>$data['UserId'],'VendorId'=>$data['VendorId'],'Remarks'=>$data['Remarks'],'PoDate'=>$data['PoDate'],'CreatedDate' => date('Y-m-d H:i:s')]
            ); */
		
		$MultipleImageflag = 0;
		if($data["MultipleImage"]==true || $data["MultipleImage"]==1){
			$MultipleImageflag = 1;
		}
		
		if($data['BrandId']==""){
			$BrandId = 0;
		}else{
			$BrandId = $data['BrandId'];
		}
		
		$PO_Image ="";
		if(!isset($data['Remarks'])){
			$data['Remarks'] = "";	
		}
		
		if(isset($data['PO_Image'])){
			
			
			
			$file = $data['PO_Image'];
			
			$name=$file->getClientOriginalName();
			$randomstring = $this->generateRandomString();
			$name_extension = explode(".",$name);
			$PO_Image = $randomstring.'.'.$name_extension[1];
			//echo "<pre>"; print_r($data); echo $name; exit;
			$file->move('uploads/po',$PO_Image);
			
			
			if($MultipleImageflag==0){
				if($data['PO_Number']!="Add"){
					$poimagecheck = DB::select("select PO_Image from purchasenumber where Id = '".$data['PO_Number']."'");
					if($poimagecheck[0]->PO_Image!=""){
						$file = public_path('uploads/po/'.$poimagecheck[0]->PO_Image);
						if(file_exists($file))
						{
							unlink($file);
						}
					}
				}
			}
			
		}
		
		if($data['PO_Number']=="Add"){
			$generate_PO = $this->GetPONumber();
			$PO_Number = $generate_PO['PO_Number'];
			$PO_Number_Financial_Id = $generate_PO['PO_Number_Financial_Id'];
			$Ponumberid = DB::table('purchasenumber')->insertGetId(
			['PurchaseNumber'=>$PO_Number,"FinancialYearId"=>$PO_Number_Financial_Id, 'UserId'=>$data['UserId'], 'VendorId'=>$data['VendorId'], 'MultipleImage'=>($MultipleImageflag==0 ? 0 : 1), 'PO_Image'=>($MultipleImageflag==0 ? $PO_Image : '') , 'PoDate'=>$data['PoDate'], 'Remarks'=>$data['Remarks'],'CreatedDate' => date('Y-m-d H:i:s')]);
		}else{
			$pon = DB::select("select PurchaseNumber from purchasenumber where Id = ".$data['PO_Number']);
			$PO_Number = $pon[0]->PurchaseNumber;
			$Ponumberid = $data['PO_Number'];
			
			if(isset($data['PO_Image'])){
				
				DB::table('purchasenumber')
				->where('Id', $Ponumberid)
				->update(['VendorId'=>$data['VendorId'], 'MultipleImage'=>($MultipleImageflag==0 ? 0 : 1), 'PO_Image'=>($MultipleImageflag==0 ? $PO_Image : '') ,'PoDate'=>$data['PoDate'], 'Remarks'=>$data['Remarks']]);
			}else{
				DB::table('purchasenumber')
				->where('Id', $Ponumberid)
				->update(['VendorId'=>$data['VendorId'], 'MultipleImage'=>($MultipleImageflag==0 ? 0 : 1),'PoDate'=>$data['PoDate'], 'Remarks'=>$data['Remarks']]);
			}
		}
		
		
		$flagcheck = DB::select("select ArticleOpenFlag from category where Id = '".$data["CategoryId"]."'");
		$fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
		
		$ArticleOpenFlag = $flagcheck[0]->ArticleOpenFlag;
		
		/* $ArticleId = DB::table('article')->insertGetId(
			['ArticleNumber' =>  $data['ArticleId'], "StyleDescription"=>$data['StyleDescription'], "ArticleOpenFlag"=>$ArticleOpenFlag, "ArticleStatus"=>$ArticleOpenFlag]
		); */
		
	
		$data['PO_Number'] = $Ponumberid;
		$data["ArticleId"] = $data['ArticleId'];
		//$ArticleId;
		$data['PO_Image'] = ($MultipleImageflag==1 ? $PO_Image : '');
		$data['BrandId'] = $BrandId;
		$field = PO::create($data);
		
		return response()->json(array("PO_Id"=>$Ponumberid, "PO_Number"=>$PO_Number), 200);
			
		/* $dataresult= DB::select('SELECT * FROM `article` WHERE `ArticleNumber`="'.$data['ArticleId'].'"'); 
        if($dataresult){
            return response()->json('allreadyexits', 201);
        }else{
			
			$flagcheck = DB::select("select ArticleOpenFlag from category where Id = '".$data["CategoryId"]."'");
			$fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
			
			$ArticleOpenFlag = $flagcheck[0]->ArticleOpenFlag; */
			
			/* $ArticleId = DB::table('article')->insertGetId(
				['ArticleNumber' =>  $data['ArticleId'], "StyleDescription"=>$data['StyleDescription'], "ArticleOpenFlag"=>$ArticleOpenFlag, "ArticleStatus"=>$ArticleOpenFlag]
			); */
			
		
			/* $data['PO_Number'] = $Ponumberid;
			$data["ArticleId"] = $data['ArticleId'];
			//$ArticleId;
			$data['PO_Image'] = ($MultipleImageflag==1 ? $PO_Image : '');
			$data['BrandId'] = $BrandId;
			$field = PO::create($data);
			
			return response()->json(array("PO_Id"=>$Ponumberid, "PO_Number"=>$PO_Number), 200);
			//return response()->json($field, 200);
        } */
		
    }
	
    public function GePO()
    {
		return DB::select('select * from (SELECT p.Id, pn.Id as POId, pn.PurchaseNumber, concat(fn.StartYear,\'-\',fn.EndYear) as FinancialYear, v.Name, c.Title, p.ArticleId, p.NumPacks, ar.ArticleNumber, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where InwardArticleId IS NULL order by Id ASC');
        //return DB::select('select * from (SELECT p.Id, pn.Id as POId, pn.PurchaseNumber, v.Name, c.Title, p.ArticleId, p.NumPacks, ar.ArticleNumber, inw.ArticleId as InwardArticleId,COALESCE(ws.Name,'-') as WorkStatusName1 From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where InwardArticleId IS NULL');
		//return DB::select('SELECT p.Id, pn.Id as POId, pn.PurchaseNumber, v.Name, c.Title, p.ArticleId, p.NumPacks, ar.ArticleNumber, inw.ArticleId as InwardArticleId From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number left join inward inw on inw.ArticleId = p.ArticleId group by pn.Id');
    }
	 public function PostPo(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
		//$vnddataTotal = DB::select("select count(*) as Total from (SELECT p.Id, pn.Id as POId, concat(pn.PurchaseNumber,'/', fn.StartYear,\"-\",fn.EndYear) as PurchaseNumber, v.Name, c.Title, p.ArticleId, p.NumPacks, ar.ArticleNumber, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where InwardArticleId IS NULL order by Id ASC");
		//$vnddataTotal = DB::select("SELECT count(*) as Total From po p  left join purchasenumber pn on pn.Id=p.PO_Number left join  inward inw on inw.ArticleId = p.ArticleId where inw.ArticleId IS NULL");
		$vnddataTotal = DB::select("select count(*) as Total from (SELECT p.Id, pn.Id as POId, p.PoDate as pdate, DATE_FORMAT(p.PoDate, '%d/%m/%Y') as PoDate, GROUP_CONCAT(DISTINCT CONCAT(ar.ArticleNumber) ORDER BY ar.Id SEPARATOR ',') as ArticleNumber,concat(pn.PurchaseNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber, v.Name, c.Title, p.PO_Number, GetTotalPOPieces(p.PO_Number) as TotalPieces, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where InwardArticleId IS NULL");
		$vnTotal = $vnddataTotal[0]->Total;
		$length = $data["length"];
		
		if($search['value'] != null && strlen($search['value']) > 2){
			$searchstring = "and (ddd.PurchaseNumber like '%".$search['value']."%' OR cast(ddd.PoDate as char) like '%".$search['value']."%' OR ddd.ArticleNumber like '%".$search['value']."%' OR ddd.Title like '%".$search['value']."%' OR ddd.Name like '%".$search['value']."%' OR ddd.Title like '%".$search['value']."%' OR ddd.TotalPieces like '%".$search['value']."%')";
			//return "select count(*) as Total from (SELECT p.Id, pn.Id as POId, p.PoDate, concat(pn.PurchaseNumber,'/', fn.StartYear,\"-\",fn.EndYear) as PurchaseNumber,v.Name, c.Title, ar.ArticleNumber, inw.ArticleId as InwardArticleId From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where InwardArticleId IS NULL ".$searchstring;
			$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT p.Id, pn.Id as POId, p.PoDate as pdate, DATE_FORMAT(p.PoDate, '%d/%m/%Y') as PoDate, GROUP_CONCAT(DISTINCT CONCAT(ar.ArticleNumber) ORDER BY ar.Id SEPARATOR ',') as ArticleNumber,concat(pn.PurchaseNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber, v.Name, c.Title, p.PO_Number, GetTotalPOPieces(p.PO_Number) as TotalPieces, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where InwardArticleId IS NULL ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "";
			$vnddataTotalFilterValue = $vnTotal;
		}
		
		//Filter Orderby value code
		$column = $data["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "date(ddd.pdate)";
				break;	
			/* case 2:
				$ordercolumn = "ddd.Id";
				break; */	
			case 2:
				$ordercolumn = "CAST(ddd.PurchaseNumber as SIGNED INTEGER)";
				break;	
			case 3:
				$ordercolumn = "ddd.Name";
				break;
			/* case 5:
				$ordercolumn = "ddd.Title";
				break; */
			case 4:
				$ordercolumn = "CAST(ddd.TotalPieces as SIGNED INTEGER)";
				break;
			default:
				$ordercolumn = "ddd.Id";
				break;
		}
		
		$order = "";	
		if($data["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data["order"][0]["dir"];
		}
		//end
		
		// "select ddd.* from (SELECT p.Id, pn.Id as POId, concat(pn.PurchaseNumber,'/', fn.StartYear,\"-\",fn.EndYear) as PurchaseNumber, v.Name, c.Title, p.ArticleId, p.NumPacks, ar.ArticleNumber, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where ddd.InwardArticleId IS NULL ".$searchstring." ".$order." limit ".$data["start"].",".$length;
		//return "select ddd.* from (SELECT p.Id, pn.Id as POId, p.PoDate, concat(pn.PurchaseNumber,'/', fn.StartYear,\"-\",fn.EndYear) as PurchaseNumber, v.Name, c.Title, p.ArticleId, p.NumPacks, ar.ArticleNumber, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where ddd.InwardArticleId IS NULL ".$searchstring." ".$order." limit ".$data["start"].",".$length;
		$vnddata = DB::select("select ddd.* from (SELECT p.Id, pn.Id as POId, p.PoDate as pdate, DATE_FORMAT(p.PoDate, '%d/%m/%Y') as PoDate, GROUP_CONCAT(DISTINCT CONCAT(ar.ArticleNumber) ORDER BY ar.Id SEPARATOR ',') as ArticleNumber, pn.PurchaseNumber as PurchaseNo, concat(pn.PurchaseNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber, v.Name, c.Title, p.PO_Number, GetTotalPOPieces(p.PO_Number) as TotalPieces, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where ddd.InwardArticleId IS NULL ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		//return "select ddd.* from (SELECT p.Id, pn.Id as POId, DATE_FORMAT(p.PoDate, \"%d/%m/%Y\") as PoDate, concat(pn.PurchaseNumber,'/', fn.StartYear,\"-\",fn.EndYear) as PurchaseNumber, v.Name, c.Title, p.ArticleId, p.NumPacks, ar.ArticleNumber, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where ddd.InwardArticleId IS NULL ".$searchstring." ".$order." limit ".$data["start"].",".$length;
		//return "select ddd.* from (SELECT p.Id, pn.Id as POId, p.PoDate as pdate, DATE_FORMAT(p.PoDate, '%d/%m/%Y') as PoDate, pn.PurchaseNumber as PurchaseNo, concat(pn.PurchaseNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber, v.Name, c.Title, p.PO_Number, GetTotalPOPieces(p.PO_Number) as TotalPieces, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where ddd.InwardArticleId IS NULL ".$searchstring." ".$order." limit ".$data["start"].",".$length;
		
		//select * from (SELECT p.Id, pn.Id as POId, pn.PurchaseNumber, concat(fn.StartYear,\'-\',fn.EndYear) as FinancialYear, v.Name, c.Title, p.ArticleId, p.NumPacks, ar.ArticleNumber, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where InwardArticleId IS NULL order by Id ASC ".$searchstring." ".$order." limit ".$data["start"].",".$length
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
	public function InwardGetPOList($GRN)
    {
		//return $GRN; exit;
		$getvenderId = DB::select('SELECT count(inw.Id) as Total, p.VendorId FROM `inward` inw left join po p on p.ArticleId=inw.ArticleId where inw.GRN="'.$GRN.'" order by inw.Id asc limit 0,1');
		//echo "<pre>"; print_r($getvenderId); exit;
		$VendorId = "";
		if($getvenderId[0]->Total>0){
			$VendorId = " and f.VendorId=".$getvenderId[0]->VendorId;
		}
		//return DB::select("SELECT p.*, a.ArticleNumber, pn.PurchaseNumber, inw.ArticleId From po p left join inward inw on inw.ArticleId = p.ArticleId left join purchasenumber pn on pn.Id=p.PO_Number inner join article a on a.Id=p.ArticleId where a.Id in (SELECT Id FROM `article` where ArticleOpenFlag=1) UNION SELECT p.*, a.ArticleNumber, pn.PurchaseNumber, inw.ArticleId From po p left join inward inw on inw.ArticleId = p.ArticleId left join purchasenumber pn on pn.Id=p.PO_Number inner join article a on a.Id=p.ArticleId where inw.ArticleId is NULL ".$VendorId);
		return DB::select("select t.* from (select dd.* from (SELECT p.Id, p.PO_Number, p.VendorId, p.CategoryId, p.BrandId, p.NumPacks, p.PoDate, p.Remarks, p.PO_Image, p.WorkOrderStatusId, p.WorkOrderDate, p.created_at, p.updated_at, prn.ReturnNoPacks, CountNoPacks(prn.ReturnNoPacks) as TotalReturnNoPacks, CountNoPacks(inw.NoPacks) as TotalNoPacks, a.ArticleNumber, pn.PurchaseNumber, inw.ArticleId From po p left join inward inw on inw.ArticleId = p.ArticleId left join purchasenumber pn on pn.Id=p.PO_Number inner join article a on a.Id=p.ArticleId left join purchasereturn prn on prn.ArticleId=p.ArticleId where a.Id in (SELECT Id FROM `article` where ArticleOpenFlag=1)) as dd group by dd.Id UNION select f.* from (SELECT p.Id, p.PO_Number, p.VendorId, p.CategoryId, p.BrandId, p.NumPacks, p.PoDate, p.Remarks, p.PO_Image, p.WorkOrderStatusId, p.WorkOrderDate, p.created_at, p.updated_at, prn.ReturnNoPacks, CountNoPacks(prn.ReturnNoPacks) as TotalReturnNoPacks, CountNoPacks(inw.NoPacks) as TotalNoPacks, a.ArticleNumber, pn.PurchaseNumber, inw.ArticleId From po p left join inward inw on inw.ArticleId = p.ArticleId left join purchasenumber pn on pn.Id=p.PO_Number inner join article a on a.Id=p.ArticleId left join purchasereturn prn on prn.ArticleId=p.ArticleId) as f where f.TotalReturnNoPacks=f.TotalNoPacks or f.ArticleId is NULL ".$VendorId." group by Id) as t group by t.Id");
	}
	
	public function GetPOChallen($id){
		//return DB::select("SELECT u.Name as PreparedBy, pn.MultipleImage, pn.PO_Image, p.Id, v.Name, v.Address, v.GSTNumber, p.PoDate, concat(pn.PurchaseNumber, '/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber, p.Remarks, ar.ArticleNumber, ar.StyleDescription, br.Name as Brandname, c.Title, p.NumPacks, p.PO_Image as MultiplePO_Image From po p inner join article ar on ar.Id=p.ArticleId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fn on fn.Id=pn.FinancialYearId inner join vendor v on v.Id= p.VendorId inner join category c on c.Id=p.CategoryId inner join users u on u.Id=pn.UserId left join brand br on br.Id=p.BrandId WHERE pn.Id = " . $id);
		return DB::select("SELECT u.Name as PreparedBy, pn.MultipleImage, pn.PO_Image, p.Id, v.Name, v.Address, v.GSTNumber, pn.PoDate, concat(pn.PurchaseNumber, '/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber, pn.Remarks, ar.ArticleNumber, ar.StyleDescription, br.Name as Brandname, subc.Name as SubcategoryName, r.Series as SeriesNo, c.Title, p.NumPacks, p.PO_Image as MultiplePO_Image From po p inner join article ar on ar.Id=p.ArticleId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fn on fn.Id=pn.FinancialYearId inner join vendor v on v.Id= p.VendorId inner join category c on c.Id=p.CategoryId inner join users u on u.Id=pn.UserId left join brand br on br.Id=p.BrandId left join subcategory subc on subc.Id=ar.SubCategoryId left join rangeseries r on r.Id=ar.SeriesId WHERE pn.Id = " . $id);
		
		//return response()->json("SUCCESS", 200);
	}
	
    public function DeletePO($id, $POId, $ArtId)
    {
		DB::beginTransaction();
		try {
			//DB::table('article')->where('Id', '=', $ArtId)->delete();
			//DB::table('articlerate')->where('ArticleId', '=', $ArtId)->delete();
			DB::table('articlephotos')->where('ArticlesId', '=', $ArtId)->delete();
			//DB::table('purchasenumber')->where('Id', '=', $POId)->delete();
			DB::table('po')->where('Id', '=', $id)->delete();
			DB::commit();
			return response()->json("SUCCESS", 200);
		} catch (\Exception $e) {
			DB::rollback();
			$status = "Error";
			return response()->json($status, 200);
		}
    }
	
	public function deletepopon($PON)
    {
		$polist = DB::select("SELECT Id, ArticleId FROM `po` where PO_Number ='".$PON."'");
		//return $polist;
		try {
			foreach($polist as $vl){
				$id = $vl->Id;
				$ArtId = $vl->ArticleId;
				
				//DB::table('article')->where('Id', '=', $ArtId)->delete();
				//DB::table('articlerate')->where('ArticleId', '=', $ArtId)->delete();
				DB::table('articlephotos')->where('ArticlesId', '=', $ArtId)->delete();
				//DB::table('purchasenumber')->where('Id', '=', $POId)->delete();
				DB::table('po')->where('Id', '=', $id)->delete();
			 }
			return response()->json("SUCCESS", 200);
		} catch (\Exception $e) {
			$status = "Error";
			return response()->json($status, 200);
		}
    }
	
    public function UpdatePo(Request $request)
    {
		//echo "asdasd"; print_r($request->all()); 
		$data = $request->all();
		if($data["SubCategoryId"]==""){
			$data["SubCategoryId"] = 0;
		}
		//return print_r($data); exit;
		//exit;
		//return $data; exit;
		$flagcheck = DB::select("select ArticleOpenFlag from category where Id = '".$data["CategoryId"]."'");
		//print_r($flagcheck); exit;
		$ArticleOpenFlag = $flagcheck[0]->ArticleOpenFlag;
		$MultipleImageflag = 0;
		if($data["MultipleImage"]=="true" || $data["MultipleImage"]==1){
			$MultipleImageflag = 1;
		}
		$status = "";
		
		//return $MultipleImageflag; exit;
        //echo $data['CategoryId']; echo "<pre>"; print_r($data); exit;
		if($data['BrandId']==""){
			$BrandId = 0;
		}else{
			$BrandId = $data['BrandId'];
		}
		
		$PO_Image ='';
		if(isset($data['PO_Image'])){
			
			$file = $data['PO_Image'];
			
			$name=$file->getClientOriginalName();
			$randomstring = $this->generateRandomString();
			$name_extension = explode(".",$name);
			$PO_Image = $randomstring.'.'.$name_extension[1];
			//echo "<pre>"; print_r($data); echo $name; exit;
			$file->move('uploads/po',$PO_Image);
			
			if($MultipleImageflag==1){
				$poimagecheck = DB::select("select PO_Image from po where Id = '".$data['id']."'");
			}else{
				$poimagecheck = DB::select("select PO_Image from purchasenumber where Id = '".$data['PO_Number']."'");
			}
			if($poimagecheck[0]->PO_Image!=""){
				$file = public_path('uploads/po/'.$poimagecheck[0]->PO_Image);
				if(file_exists($file))
				{
					unlink($file);
				}
			}
		}
			
					
		DB::beginTransaction();
		try {
			DB::table('article')
            ->where('Id', $data["ArtId"])
            ->update(["ArticleOpenFlag" => $ArticleOpenFlag, "ArticleStatus"=>$ArticleOpenFlag]);
			 
			
			if($data['WorkOrderStatusId']!=""){
				if(isset($data['PO_Image'])){
					PO::where('Id', $data['id'])->update(array(
						'VendorId' => $data['VendorId'],
						'CategoryId' => (int)$data['CategoryId'],
						'SubCategoryId' => (int)$data['SubCategoryId'],
						'NumPacks' => $data['NumPacks'],
						'PoDate' => $data['PoDate'],
						'BrandId' => $BrandId,
						'Remarks' => $data['Remarks'],
						'PO_Image' => ($MultipleImageflag==1 ? $PO_Image : ''),
						'WorkOrderStatusId' => $data['WorkOrderStatusId'],
						'WorkOrderDate' => $data['WorkOrderDate']
					));
				} else{
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
				$poworkorder = DB::select("SELECT count(*) as total FROM `poworkorder` where POID ='".$data["id"]."' and WorkOrderStatusId ='".$data["WorkOrderStatusId"]."'");
				if($poworkorder[0]->total>0){
					DB::table('poworkorder')
					->where('POID', $data["id"])
					->where('WorkOrderStatusId', $data["WorkOrderStatusId"])
					->update(['WorkOrderStatusId'=>$data['WorkOrderStatusId'], 'CreatedDate' => $data['WorkOrderDate']]);
				}else{
					DB::table('poworkorder')->insertGetId(
						['WorkOrderStatusId'=>$data['WorkOrderStatusId'], 'POID'=>$data['id'], 'CreatedDate' => $data['WorkOrderDate']]
					);
				}
			}else{
				if(isset($data['PO_Image'])){
					PO::where('Id', $data['id'])->update(array(
						'VendorId' => $data['VendorId'],
						'CategoryId' => (int)$data['CategoryId'],
						'SubCategoryId' => (int)$data['SubCategoryId'],
						'NumPacks' => $data['NumPacks'],
						'PoDate' => $data['PoDate'],
						'PO_Image' => ($MultipleImageflag==1 ? $PO_Image : ''),
						'BrandId' => $BrandId,
						'Remarks' => $data['Remarks']
					));
				} else{
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
			
			
			if(isset($data['PO_Image'])){
				
				DB::table('purchasenumber')
				->where('Id', $data['PO_Number'])
				->update(['VendorId'=>$data['VendorId'], 'MultipleImage'=>($MultipleImageflag==0 ? 0 : 1), 'PO_Image'=>($MultipleImageflag==0 ? $PO_Image : '') , 'PoDate'=>$data['PoDate'], 'Remarks'=>$data['Remarks']]);
			}else{
				DB::table('purchasenumber')
				->where('Id', $data['PO_Number'])
				->update(['VendorId'=>$data['VendorId'], 'MultipleImage'=>($MultipleImageflag==0 ? 0 : 1), 'PoDate'=>$data['PoDate'], 'Remarks'=>$data['Remarks']]);
			}
			
			DB::commit();
			return response()->json("SUCCESS", 200);
		} catch (\Exception $e) {
			DB::rollback();
			$status = $e."Error";
			return response()->json($status, 200);
		}
    }
	
    public function GetPoIdWise($id)
    {
		//$as =  DB::select('SELECT p.*,ar.ArticleOpenFlag, pn.PurchaseNumber, concat(pn.PurchaseNumber, \'/\', fn.StartYear,\'-\',fn.EndYear) as FinancialYear, ar.ArticleNumber, ar.StyleDescription From po p left join article ar on ar.Id=p.ArticleId left join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fn on fn.Id=pn.FinancialYearId WHERE p.Id =' . $id . '');
		$as = DB::select("SELECT p.*, c.Title as CategoryName, subc.Name as SubCategoryName, r.Series, ar.StyleDescription, ar.SeriesId, ar.BrandId, b.Name as BrandName, ar.ArticleOpenFlag, pn.PurchaseNumber, concat(pn.PurchaseNumber, '/', fn.StartYear,'-',fn.EndYear) as FinancialYear, ar.ArticleNumber, ar.StyleDescription From po p left join article ar on ar.Id=p.ArticleId left join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fn on fn.Id=pn.FinancialYearId left join rangeseries r on r.Id=ar.SeriesId left join subcategory subc on subc.Id = p.SubCategoryId left join category c on c.Id = p.CategoryId left join brand b on b.Id=ar.BrandId WHERE p.Id = ". $id);
		//echo "<pre>"; print_r($as); exit;
		foreach($as as $key => $val){
			$object = (object)$val;
			$object->ArticleOpenFlag = (integer)$val->ArticleOpenFlag;
		}
		
		return $as;
	}
	
	public function GetPONumber()
    {
		//return "test"; //exit;
		
		//$financialyear_query = DB::select('SELECT MIN(Id) as MinId, MAX(Id) as MaxId, count(Id) as TotalId, max(CAST(GRN as INT)) as GRN, (CASE WHEN MONTH(NOW())>3 THEN concat(DATE_FORMAT(NOW(), "%y"), '-',DATE_FORMAT(NOW(), "%y")+1) ELSE concat(DATE_FORMAT(NOW(), "%y")-1,'-', DATE_FORMAT(NOW(), "%y")) END) AS current_year, (CASE WHEN MONTH(InwardDate)>3 THEN concat(DATE_FORMAT(InwardDate, "%y"), '-',DATE_FORMAT(InwardDate, "%y")+1) ELSE concat(DATE_FORMAT(InwardDate, "%y")-1,'-', DATE_FORMAT(InwardDate, "%y")) END) AS financial_year From inwardgrn group by financial_year order by financial_year desc limit 0,1');
		/* $financialyear_query = DB::select("SELECT MIN(Id) as MinId, MAX(Id) as MaxId, count(Id) as TotalId, max(CAST(PurchaseNumber as INT)) as PurchaseNumber, (CASE WHEN MONTH(NOW())>3 THEN concat(DATE_FORMAT(NOW(), \"%y\"), '-',DATE_FORMAT(NOW(), \"%y\")+1) ELSE concat(DATE_FORMAT(NOW(), \"%y\")-1,'-', DATE_FORMAT(NOW(), \"%y\")) END) AS current_year, (CASE WHEN MONTH(CreatedDate)>3 THEN concat(DATE_FORMAT(CreatedDate, \"%y\"), '-',DATE_FORMAT(CreatedDate, \"%y\")+1) ELSE concat(DATE_FORMAT(CreatedDate, \"%y\")-1,'-', DATE_FORMAT(CreatedDate, \"%y\")) END) AS financial_year From purchasenumber group by financial_year order by financial_year desc limit 0,1");
		//echo "<pre>"; print_r($financialyear_query); exit;
		//X290g5esZg
		
		if(empty($financialyear_query)){
			$date = date("Y-m-d");
			//$date = "2014-04-01";
			$month =  date('m', strtotime($date));
			$year =  date('y', strtotime($date));
			$FinanceYear = "";
			//$_SESSION["count"] =1;
			//$count = 1;
			if($month > 03) {
				$d = date('Y-m-d', strtotime('+1 years', strtotime($date)));
				$FinanceYear =  $year .'-'.date('y', strtotime($d));
				
			} else {
				$d = date('Y-m-d', strtotime('-1 years', strtotime($date)));
				$FinanceYear =  date('y', strtotime($d)).'-'.$year;
			}
			
			$current_year = $FinanceYear;
			$financial_year = $FinanceYear;
			$TotalId = 0;
		} else{
			//$current_year = "16-17";
			//$financial_year = "15-16";
			$current_year = $financialyear_query[0]->current_year;
			$financial_year = $financialyear_query[0]->financial_year;
			$TotalId = $financialyear_query[0]->TotalId;
		}
		
		//echo $TotalId; exit;
		//echo "<pre>"; echo $financial_year; exit;
		
		//$current_year = $financialyear_query[0]->current_year;
		//$financial_year = $financialyear_query[0]->financial_year;

		//echo $FinanceYear; exit; 
		
		
		if($current_year > $financial_year){
			$array["PO_Number"] = 1;
			$array["PO_Number_Financial"] = 1 . "/" . $current_year;
			//$array["PO_Number_Financial_Year"] = $current_year;
			return response()->json($array, 200);
		}else{
			if($TotalId>0){
				$array["PO_Number"] = ($podata[0]->PurchaseNumber) + 1;
				$array["PO_Number_Financial"] = ($podata[0]->PurchaseNumber) + 1 . "/" . $financial_year;
				//$array["PO_Number_Financial_Year"] = $financial_year;
				return response()->json($array, 200);
			}else {
				$array["PO_Number"] = 1;
				$array["PO_Number_Financial"] = 1 . "/" . $financial_year;
				//$array["PO_Number_Financial_Year"] = $financial_year;
				return response()->json($array, 200);
			}
		} */
		
		
		/* 
		$array = array();
        $podata = DB::select('SELECT PurchaseNumber, FinancialYearId From purchasenumber order by Id desc limit 0,1');
		//wpsuperp
		$fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
		
		
		if(count($podata)>0){
			if($fin_yr[0]->Id > $podata[0]->FinancialYearId){
				$array["PO_Number"] = 1;
				$array["PO_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return response()->json($array, 200);
			} else{
				$array["PO_Number"] = ($podata[0]->PurchaseNumber) + 1;
				$array["PO_Number_Financial"] = ($podata[0]->PurchaseNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return response()->json($array, 200);
			}
		}
        else{
			$array["PO_Number"] = 1;
			$array["PO_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
			return response()->json($array, 200);
        } */
		
		
		$array = array();
        $fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
		$podata = DB::select('SELECT PurchaseNumber, FinancialYearId From purchasenumber order by Id desc limit 0,1');
		//return $podata; exit;
		
		
		if(count($podata)>0){
			if($fin_yr[0]->Id > $podata[0]->FinancialYearId){
				$array["PO_Number"] = 1;
				$array["PO_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["PO_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			} else{
				//return $podata; exit;
				$array["PO_Number"] = ($podata[0]->PurchaseNumber) + 1;
				$array["PO_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["PO_Number_Financial"] = ($podata[0]->PurchaseNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			}
		}
        else{
			$array["PO_Number"] = 1;
			$array["PO_Number_Financial_Id"] = $fin_yr[0]->Id;
			$array["PO_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
			return $array;
        }
    }
	
	
	public function polistfrompon($id)
     {
		//$as = DB::select("SELECT inw.Id,inw.ArticleId,v.Name,count(ac.ArticleId) as ColorCount,c.Colorflag, c.Title, inw.GRN,ar.ArticleNumber, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,ar.ArticleOpenFlag,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId inner JOIN po p on p.ArticleId = ar.Id inner Join category c on c.Id=p.CategoryId inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId left join articlecolor ac on ac.ArticleId=inw.ArticleId where inw.GRN='". $id ."' group by inw.Id");
		$as = DB::select("SELECT po.Id, po.PO_Number, po.ArticleId,v.Name, c.Colorflag, c.Title as CategoryName, subc.Name as SubcategoryName, r.Series as SeriesNo, b.Name as BrandName,   pon.PurchaseNumber,ar.ArticleNumber, ar.ArticleOpenFlag,po.NumPacks as NoPacks,pon.PoDate FROM `po` po left join purchasenumber pon on pon.Id=po.PO_Number LEFT JOIN article ar on ar.Id = po.ArticleId left Join category c on c.Id=ar.CategoryId left join subcategory subc on subc.Id=ar.SubCategoryId left join rangeseries r on r.Id= ar.SeriesId inner join vendor v on v.Id=po.VendorId left join articlecolor ac on ac.ArticleId=po.ArticleId left join brand b on b.Id=ar.BrandId where po.PO_Number='".$id."' group by po.Id");

		return $as;
		//echo "<pre>"; print_r($as); exit;
		//return DB::select("SELECT inw.Id,inw.ArticleId,v.Name,count(ac.ArticleId) as ColorCount,c.Colorflag, c.Title, inw.GRN,ar.ArticleNumber, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,ar.ArticleOpenFlag,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId inner JOIN po p on p.ArticleId = ar.Id inner Join category c on c.Id=p.CategoryId inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId left join articlecolor ac on ac.ArticleId=inw.ArticleId where inw.GRN='". $id ."' group by inw.Id");
	 }
	 
	 public function podateremarkfromPO($id)
     {
		// return DB::select("SELECT * FROM `inwardgrn` inw inner join  where Id='". $id ."'");
		return DB::select("SELECT pn.*, concat(pn.PurchaseNumber, '/',fn.StartYear,'-',fn.EndYear) as PO_Number_FinancialYear FROM `purchasenumber` pn inner join financialyear fn on fn.Id=pn.FinancialYearId where pn.Id = '". $id ."'");
	 }
	 
	
	///Artical
    public function GetArtical()
    {
		return DB::select('SELECT * FROM `article`');
    }
	
	public function approvedarticallist()
    {
		return DB::select('SELECT * FROM `article` where ArticleStatus="1"');
    }
	
    public function GetArticalIdWise($id)
    {
        return DB::select("SELECT p.NumPacks, purn.PurchaseNumber, concat(purn.PurchaseNumber, '/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber_FinancialYear, ar.Id as ArticleId, ar.ArticleNumber,ar.ArticleStatus,ar.ArticleOpenFlag, ar.StyleDescription, c.Colorflag, c.Title, p.VendorId as VID, v.Name, br.Name as BrandName FROM `article` ar INNER JOIN po p on ar.Id = p.ArticleId INNER JOIN category c on c.Id=p.CategoryId INNER join vendor v on v.Id=p.VendorId inner join purchasenumber purn on purn.Id=p.PO_Number left join brand br on br.Id=ar.BrandId inner join financialyear fn on fn.Id=purn.FinancialYearId WHERE p.Id='". $id ."'");
	}
	public function GetArticaldata($Id)
	{
		return DB::select('SELECT ArticleRate FROM `articlerate` where ArticleId = "'.$Id.'"');
	}
	
	public function AddArticleRateChange(Request $request)
    {
        $data = $request->all();
		//echo "<pre>";
		//print_r($data); exit;
		
		$articledata = DB::select("select count(*) as Total from articlerate where ArticleId = '".$data['ArticleId']."'");
		if($articledata[0]->Total>0){
			DB::table('articlerate')
			->where('ArticleId', $data['ArticleId'])
			->update(['ArticleRate' => $data['ArticleRate'], 'UpdatedDate'=>date("Y-m-d H:i:s")]);
		}else{
			DB::table('articlerate')->insertGetId(
				['ArticleId'=>$data['ArticleId'], 'ArticleRate' => $data['ArticleRate'], 'CreatedDate' => date("Y-m-d H:i:s")]
			);
		}
		return response()->json(array("Id"=>"Success"), 200);
    }
	
	public function ArticleRateAssignSO(){
		$data = DB::select("select * from articlerate");
		foreach($data as $vl){
			$ArticleId = $vl->ArticleId;
			$ArticleRate = $vl->ArticleRate;
			
			DB::table('so')
			->where('ArticleId', $ArticleId)
			->update(['ArticleRate' => $ArticleRate]);
		}
	}
	
	
	///Work Order Status
	public function AddworkOrder(Request $request)
    {
        $data = $request->all();
		$dataresult= DB::select('SELECT * FROM `workorderstatus` WHERE `Name` LIKE "'.$data['Name'].'"'); 
        if($dataresult){
            return response()->json('allreadyexits', 201);
        }else{
            //$field = WorkOrderStatus::create($request->all());
			DB::table('workorderstatus')->insertGetId(
				['Name'=>$data['Name']]
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
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
		$vnddataTotal = DB::select("SELECT count(*) as Total From workorderstatus");
		$ventotal =  $vnddataTotal[0]->Total;
		$length = $data["length"];
		
		if($search['value'] != null && strlen($search['value']) > 2){
			$searchstring = "WHERE Name like '%".$search['value']."%'";
			$vnddataTotalFilter = DB::select("SELECT count(*) as Total From workorderstatus ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "";
			$vnddataTotalFilterValue = $ventotal;
		}
		
			//Filter Orderby value code
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
		if($data["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data["order"][0]["dir"];
		}
		//end
		
		$vnddata = DB::select("SELECT * From workorderstatus ".$searchstring."".$order." limit ".$data["start"].",".$length);
		return array(
				'datadraw'=>$data["draw"],
				'recordsTotal'=>$ventotal,
				'recordsFiltered'=>$vnddataTotalFilterValue,
				'response' => 'success',
				'startnumber' => $startnumber,
				'search'=>count($vnddata),
				'data' => $vnddata,
			);
	}
    public function UpdateworkOrder(Request $request)
    {
		
        $data = $request->all();
		DB::table('workorderstatus')
            ->where('Id', $data["id"])
            ->update(['Name' => $data["Name"]]);
			
        //echo "<pre>"; print_r($data); exit; 
		/* WorkOrderStatus::where('Id', $data['id'])
			->update(
			array('Name' => $data['Name'])
			 );*/
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
	
}
