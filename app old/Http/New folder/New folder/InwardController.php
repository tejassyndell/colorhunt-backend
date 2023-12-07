<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Inward;

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

     ///Imward Module
     public function AddInward(Request $request)
     {
		$data = $request->all();
		
		if($data['GRN_Number']=="Add"){
			
			$generate_GRN = $this->GetGRNNumber();
		}
		
		
		if($data["ArticleStatus"]==true){
			$ArticleStatus = 1;
		}else{
			$ArticleStatus = 0;
		}
		
		$addarticles = array();
		$dataresult= DB::select('SELECT p.*, a.ArticleOpenFlag, c.Colorflag FROM `po` p left join category c on c.Id = p.CategoryId inner join article a on a.Id=p.ArticleId where p.Id="'.$data['PoId'].'"'); 
		//echo "<pre>"; print_r($dataresult); exit;
		$Colorflag = $dataresult[0]->Colorflag;
		$ArticleId = $dataresult[0]->ArticleId;
		$ArticleOpenFlag = $dataresult[0]->ArticleOpenFlag;
		
		$ArticleColor = json_encode($data['ColorId']);
		$ArticleSize = json_encode($data['SizeId']);
		$ArticleRatio = $data['RatioId'];
		 
		$NoPacks = "";
		if($Colorflag==1){
			foreach($data['ColorId'] as $vl){
				$numberofpacks = $vl["Id"];
				if($data["NoPacks_".$numberofpacks]==0){
					return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
				}
				$NoPacks .= $data["NoPacks_".$numberofpacks].",";
			}
		} else{
			if($data['NoPacks']==0){
				return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
			}
			$NoPacks .= $data['NoPacks'];
		}
		
		$NoPacks = rtrim($NoPacks,',');
		//echo $NoPacks; exit;
		$countcolor = count($data['ColorId']);
		$countration = array_sum(explode(",",$data['RatioId']));
		$countNoPacks = array_sum(explode(",",$NoPacks));
		
		if($Colorflag==1){
			$TotalSetQuantity = ($countNoPacks * $countration);
		}else{
			$TotalSetQuantity = ($countNoPacks * ($countration * $countcolor));
		}
		/* echo "Countcolor: ".$countcolor."\n";
		echo "Countsize: ".$countration."\n";
		echo "countNoPacks: ".$countNoPacks."\n";
		echo "TotalQuantity: ".$TotalSetQuantity."\n\n";
		echo "<pre>"; print_r($data); exit; */
		
		if($ArticleOpenFlag==0){
			$updated = DB::table('article')
            ->where('Id', $ArticleId)
            ->update(['ArticleRate' => $data['Rate'], 'ArticleColor' => $ArticleColor, 'ArticleSize' => $ArticleSize, 'ArticleRatio' => $ArticleRatio, 'ArticleStatus'=>$ArticleStatus, 'UpdatedDate' => date("Y-m-d H:i:s")]);
			//DB::table('articlecolor')->where('ArticleId', '=', $ArticleId)->delete();
			
			//Article color size and ratio insert - start
			$getratio = explode(",",$data['RatioId']);
			foreach($data['ColorId'] as $vl){
				$colorgetId = DB::table('articlecolor')->insertGetId(
					['ArticleId'=>$ArticleId, 'ArticleColorId' => $vl["Id"], 'ArticleColorName' => $vl["Name"],'CreatedDate' => date("Y-m-d H:i:s")]
				);
			}
			
			foreach($data['SizeId'] as $key => $vl){
				$articlesize = DB::table('articlesize')->insertGetId(
					['ArticleId'=>$ArticleId, 'ArticleSize' => $vl["Id"],'ArticleSizeName' => $vl["Name"],'CreatedDate' => date("Y-m-d H:i:s")]
				);
				
				DB::table('articleratio')->insertGetId(
					['ArticleId'=>$ArticleId, 'ArticleSizeId' => $vl["Id"], 'ArticleRatio' => $getratio[$key],'CreatedDate' => date("Y-m-d H:i:s")]
				);
			}
			//Article color size and ratio insert - end
		
			$articledata = DB::select("select count(*) as Total from articlerate where ArticleId = '".$ArticleId."'");
			if($articledata[0]->Total>0){
				DB::table('articlerate')
				->where('ArticleId', $ArticleId)
				->update(['ArticleRate' => $data['Rate'], 'UpdatedDate'=>date("Y-m-d H:i:s")]);
			}else{
				DB::table('articlerate')->insertGetId(
					['ArticleId'=>$ArticleId, 'ArticleRate' => $data['Rate'], 'CreatedDate' => date("Y-m-d H:i:s")]
				);
			}
		
		}else{
			$TotalSetQuantity = $countNoPacks;
		}
		
		
		
		if($data['VendorId']==""){
			$VendorId = 0;
		}else{
			$VendorId = $data['VendorId'];
		}
		
		/* if($data['BrandId']==""){
			$BrandId = 0;
		}else{
			$BrandId = $data['BrandId'];
		} */
		
		if($data['GRN_Number']=="Add"){
			$GRN_Number = $generate_GRN['GRN_Number'];
			$GRN_Number_Financial_Id = $generate_GRN['GRN_Number_Financial_Id'];
			//$generate_GRN['GRN_Number'];
			//$generate_GRN['GRN_Number_Financial'];
			//$generate_GRN['GRN_Number_Financial_Id'];
			$inwardgrnid = DB::table('inwardgrn')->insertGetId(
			['GRN'=>$GRN_Number,"FinancialYearId"=>$GRN_Number_Financial_Id, 'InwardDate'=>$data['InwardDate'], 'Remark'=>$data['Remark'], 'VendorId'=>$VendorId, 'UserId'=>$data['UserId'],'CreatedDate' => date('Y-m-d H:i:s')]);
		}else{
			$inwardgrn = DB::select("select GRN from inwardgrn where Id = ".$data['GRN_Number']);
			$GRN_Number = $inwardgrn[0]->GRN;
			$inwardgrnid = $data['GRN_Number'];
			DB::table('inwardgrn')
			->where('Id', $inwardgrnid)
			->update(['InwardDate'=>$data['InwardDate'], 'Remark'=>$data['Remark'], 'VendorId'=>$VendorId]);
		}
	
		$inwardid = DB::table('inward')->insertGetId(
                ['ArticleId'=>$ArticleId, 'NoPacks' => $NoPacks, 'SalesNoPacks' => $NoPacks, "InwardDate" => $data['InwardDate'],'GRN' => $inwardgrnid,"Weight" => $data['Weight'],'TotalSetQuantity'=>$TotalSetQuantity, 'created_at' => date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')]
            );
		 
		if($ArticleOpenFlag==1){
			$GetInwardArticleTotal = DB::select("SELECT count(*) as InwardArticleTotal, Id FROM `inwardarticle` where ArticleId ='".$ArticleId."' limit 0,1");
			DB::table('inwardarticle')->insertGetId(
					['ArticleId'=>$ArticleId, 'InwardId' => $inwardid, 'ArticleRate' => $data['Rate'], 'ArticleColor' => $ArticleColor, 'ArticleSize' => $ArticleSize, 'ArticleRatio' => $ArticleRatio, 'CreatedDate' => date("Y-m-d H:i:s")]
				);
			
			$articledata = DB::select("select count(*) as Total from articlerate where ArticleId = '".$ArticleId."'");
			if($articledata[0]->Total>0){
				/* DB::table('articlerate')
				->where('ArticleId', $ArticleId)
				->update(['ArticleRate' => $data['Rate'], 'UpdatedDate'=>date("Y-m-d H:i:s")]); */
				
			}else{
				DB::table('articlerate')->insertGetId(
					['ArticleId'=>$ArticleId, 'ArticleRate' => $data['Rate'], 'CreatedDate' => date("Y-m-d H:i:s")]
				);
			}
			
			$mixnopacks = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='".$ArticleId."'");
			if($mixnopacks[0]->total>0){
				$totalnopacks = ($TotalSetQuantity + $mixnopacks[0]->NoPacks);
				DB::table('mixnopacks')
				->where('Id', $mixnopacks[0]->Id)
				->update(['NoPacks'=>$totalnopacks, 'UpdatedDate' => date("Y-m-d H:i:s")]);
			}else{
				DB::table('mixnopacks')->insertGetId(
					['ArticleId'=>$ArticleId, 'NoPacks' => $NoPacks, 'CreatedDate' => date("Y-m-d H:i:s"), 'UpdatedDate' => date("Y-m-d H:i:s")]
				);
			}
		}
		return response()->json(array("GRN_Id"=>$inwardgrnid, "GRN_Number"=>$GRN_Number), 200);
     }
	 
	private function GetGRNNumber()
    {
		$array = array();
        $fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
		$inwardgrndata = DB::select('SELECT GRN, FinancialYearId From inwardgrn order by Id desc limit 0,1');
		//echo "<pre>"; print_r($fin_yr); exit;
		
		
		if(count($inwardgrndata)>0){
			if($fin_yr[0]->Id > $inwardgrndata[0]->FinancialYearId){
				$array["GRN_Number"] = 1;
				$array["GRN_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["GRN_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			} else{
				$array["GRN_Number"] = ($inwardgrndata[0]->GRN) + 1;
				$array["GRN_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["GRN_Number_Financial"] = ($inwardgrndata[0]->GRN) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			}
		}
        else{
			$array["GRN_Number"] = 1;
			$array["GRN_Number_Financial_Id"] = $fin_yr[0]->Id;
			$array["GRN_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
			return $array;
        }
    }
	
     public function GeInward()
     {
		
		//return DB::select("select * from (SELECT VendorName(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as Name , GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate,GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY inw.Id SEPARATOR ',') as ArticleNumber, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId group by inw.GRN order by igrn.Id DESC) as ddd where ddd.SOID =0 order by GRN DESC");
		//return DB::select("select VendorName(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as Name ,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces,inw.GRN, concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number, igrn.InwardDate, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID from `inward` inw left join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId where s.Id is null group by GRN order by GRN DESC");
		//return DB::select("select dd.* from (select VendorName(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as Name ,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',') as dddf, GROUP_CONCAT(DISTINCT CONCAT(s.Id) ORDER BY s.Id SEPARATOR ',') as soid1, SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck,inw.GRN, concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number, igrn.InwardDate, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID from `inward` inw left join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId group by GRN order by GRN desc) as dd where dd.SOID=0");
		
		return DB::select("select dd.* from (select VendorName(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as Name ,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, InwardSOCheck(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SOID, SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck,inw.GRN, concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number, igrn.InwardDate from `inward` inw left join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId group by GRN order by GRN asc) as dd where dd.SOID=0 order by GRN DESC");
	 }
	 
	public function PostInward(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
		//$vnddataTotal = DB::select("select count(*) as Total from (select inw.GRN FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) and s.Id IS NULL group by GRN) as f");
		//select count(*) as Total from (select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck FROM `inward` inw inner join article a on a.Id=inw.ArticleId where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) group by inw.GRN) as dd where dd.SODataCheck = 1
		$vnddataTotal = DB::select("select count(*) as Total from (select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck FROM `inward` inw inner join article a on a.Id=inw.ArticleId where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) group by inw.GRN) as dd where dd.SODataCheck = 0");
		//select IF(ISNULL(inw.GRN),s.Id,NULL), s.Id, inw.GRN FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) group by GRN

		$vnTotal = $vnddataTotal[0]->Total;
		$length = $data["length"];
		
		if($search['value'] != null && strlen($search['value']) > 2){
			
			//$searchstring = "and d.GRN_Number like '%".$search['value']."%' OR d.Name like '%".$search['value']."%'";
			//$searchstring = "where f.GRN_Number like '%".$search['value']."%' OR cast(f.InwardDate as char) like '%".$search['value']."%' OR  f.Name like '%".$search['value']."%' OR  f.TotalInwardPieces like '%".$search['value']."%' OR f.Title like '%".$search['value']."%' OR f.PurchaseNumber like '%".$search['value']."%'";
			$searchstring = "where f.SODataCheck=0 and (f.GRN_Number like '%".$search['value']."%' OR cast(f.InwardDate as char) like '%".$search['value']."%' OR  f.Name like '%".$search['value']."%' OR  f.TotalInwardPieces like '%".$search['value']."%' OR f.Title like '%".$search['value']."%' OR f.PurchaseNumber like '%".$search['value']."%' OR f.ArticleNo like '%".$search['value']."%')";
			//$vnddataTotalFilter = DB::select("select count(*) as Total  from (select VendorName(a.Id) as Name, GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber, DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate, SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, c.Title FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId  where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) and s.Id IS NULL group by GRN) as f ".$searchstring);
			$vnddataTotalFilter = DB::select("select count(*) as Total  from (select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, VendorName(a.Id) as Name, GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber, DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate, SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, c.Title FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId left join po p on p.ArticleId=a.Id left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=a.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId  where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) and s.Id IS NULL group by GRN) as f ".$searchstring);
			//select count(*) as Total from (select VendorName(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as Name ,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, InwardSOCheck(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SOID, SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck,inw.GRN, concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number, igrn.InwardDate from `inward` inw left join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId group by GRN order by GRN asc) as dd where dd.SOID=0 and dd.GRN_Number like '%21/%' OR dd.Name like '%21/%' order by dd.GRN DESC
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "where f.SODataCheck=0";
			$vnddataTotalFilterValue = $vnTotal;
		}
		
		//Filter Orderby value code
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
		if($data["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data["order"][0]["dir"];
		}
		//end
		
		//$vnddata = DB::select("select dd.* from (select VendorName(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as Name ,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, InwardSOCheck(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SOID, SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck,inw.GRN, concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number, igrn.InwardDate from `inward` inw left join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId group by GRN order by GRN asc) as dd where dd.SOID=0 ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		//$vnddata = DB::select("select * from (select dd.* from (select VendorName(a.Id) as Name,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate,(CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId) as dd where dd.SOID = '0' group by dd.GRN) as f ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		//$vnddata = DB::select("select * from (select GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',') as SODataCheck11,VendorName(a.Id) as Name,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, \"%d/%m/%Y\") as InwardDate,SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, c.Title FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=p.CategoryId where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) and s.Id IS NULL group by GRN) as f ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		
		
		//$vnddata = DB::select("select * from (select GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',') as SODataCheck11,VendorName(a.Id) as Name,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate,SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN   inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId  left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId  where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) and s.Id IS NULL group by GRN) as f ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		//$vnddata = DB::select("select * from (select GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',') as SODataCheck11,VendorName(a.Id) as Name,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate,SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN   inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId  left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId  where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) group by GRN) as f ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		
		
		
		//$vnddata = DB::select("select * from (select inwc.Notes,'Cancellation',VendorName(a.Id) as Name, GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inwcl.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate, 'SODataCheck', c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM inwardgrn igrn inner join `inwardcancellationlogs` inwcl on igrn.Id=inwcl.GRN inner join `inwardcancellation` inwc on igrn.Id=inwc.GRN  inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inwcl.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId  inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId group by GRN UNION ALL select '',0,VendorName(a.Id) as Name,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate,SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN   inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId  left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId  where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) group by GRN) as f ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		//$vnddata = DB::select("select * from (select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, inwc.Notes,'Cancellation',VendorName(a.Id) as Name, GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inwcl.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate, 'SODataCheck', c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM inwardgrn igrn inner join `inwardcancellationlogs` inwcl on igrn.Id=inwcl.GRN inner join `inwardcancellation` inwc on igrn.Id=inwc.GRN inner join inward i on i.GRN=igrn.Id  inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inwcl.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId  inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId group by i.GRN UNION ALL select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, '',0,VendorName(a.Id) as Name,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate,SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN   inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId  left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId  where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) group by inw.GRN) as f ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		//return "select * from (select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, inwc.Notes,'Cancellation',VendorName(a.Id) as Name, GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inwcl.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate, 'SODataCheck', c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM inwardgrn igrn inner join `inwardcancellationlogs` inwcl on igrn.Id=inwcl.GRN inner join `inwardcancellation` inwc on igrn.Id=inwc.GRN inner join inward i on i.GRN=igrn.Id  inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inwcl.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId  inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId group by i.GRN UNION ALL select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, '',0,VendorName(a.Id) as Name,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate,SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN   inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId  left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId  where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) group by inw.GRN) as f ".$searchstring." ".$order." limit ".$data["start"].",".$length;
		
		//$vnddata = DB::select("select * from (select inwc.Notes,'Cancellation',VendorName(a.Id) as Name, GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inwcl.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate, 'SODataCheck', c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM inwardgrn igrn inner join `inwardcancellationlogs` inwcl on igrn.Id=inwcl.GRN inner join `inwardcancellation` inwc on igrn.Id=inwc.GRN  inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inwcl.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId  inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId group by GRN UNION ALL select '',0,VendorName(a.Id) as Name,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate,SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN   inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId  left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId  where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) group by GRN) as f ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		
		
		//$vnddata = DB::select("select * from (select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, inwc.Notes,'Cancellation',VendorName(a.Id) as Name, igrn.Id, GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inwcl.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate, 'SODataCheck', c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM inwardgrn igrn inner join `inwardcancellationlogs` inwcl on igrn.Id=inwcl.GRN inner join `inwardcancellation` inwc on igrn.Id=inwc.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inwcl.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId group by inwc.GRN UNION ALL select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, '',0,VendorName(a.Id) as Name,igrn.Id,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate,SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN   inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId  left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId  where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) group by inw.GRN) as f ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		$vnddata = DB::select("select * from (select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, inwc.Notes,'Cancellation',VendorName(a.Id) as Name, igrn.Id, GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inwcl.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate, 'SODataCheck', (case when c.Title IS NULL then cc.Title else c.Title end) as Title, (case when pn.PurchaseNumber IS NULL then 0 else concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) end)  as PurchaseNumber FROM inwardgrn igrn inner join `inwardcancellationlogs` inwcl on igrn.Id=inwcl.GRN inner join `inwardcancellation` inwc on igrn.Id=inwc.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inwcl.ArticleId left join po p on p.ArticleId=a.Id left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join category cc on cc.Id=a.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number left join financialyear fy1 on fy1.Id=pn.FinancialYearId group by inwc.GRN UNION ALL select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, '',0,VendorName(a.Id) as Name,igrn.Id,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate,SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, (case when c.Title IS NULL then cc.Title else c.Title end) as Title, (case when pn.PurchaseNumber IS NULL then 0 else concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) end)  as PurchaseNumber FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN   inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId left join po p on p.ArticleId=a.Id left join vendor v on v.Id=p.VendorId  left join so s on s.ArticleId=inw.ArticleId left join category c on c.Id=p.CategoryId left join category cc on cc.Id=a.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number left join financialyear fy1 on fy1.Id=pn.FinancialYearId  where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) group by inw.GRN) as f ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		
		//return "select * from (select GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',') as SODataCheck11,VendorName(a.Id) as Name,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate,SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, c.Title FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=p.CategoryId where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) and s.Id IS NULL group by GRN) as f ".$searchstring." ".$order." limit ".$data["start"].",".$length;
		
//select GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',') as SODataCheck11,VendorName(a.Id) as Name,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate,SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) group by GRN		
		//select ddd.* from (select VendorName(a.Id) as Name,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate,(CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId) as ddd where ddd.SOID = '0' group by ddd.GRN  
		
		//select * from (select dd.* from (select VendorName(a.Id) as Name,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate,(CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId) as dd where dd.SOID = '0' group by dd.GRN) as f order by f.GRN DESC
		
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
	 
	 
     public function UpdateInward(Request $request)
     {
		 
        $data = $request->all();
		
		//echo "<pre>"; print_r($data); exit;
		//echo $data["PoId"];
		//echo "<pre>"; print_r($data);  exit;
		$dataresult= DB::select('select inw.*, a.ArticleNumber, p.PO_Number, p.Id as POID, c.Colorflag from inward inw left join article a on a.Id=inw.ArticleId left join po p on p.ArticleId=inw.ArticleId left join category c on c.Id = a.CategoryId where inw.Id="'.$data['id'].'"'); 
		//echo "<pre>"; print_r($dataresult); exit;
		$Colorflag = $dataresult[0]->Colorflag;
		$ArticleId = $dataresult[0]->ArticleId;
		//$articleratio = DB::select('select * from articleratio where ArticleId="'.$ArticleId.'"');
		//echo "<pre>"; print_r($articleratio); print_r($data); exit;
		$ArticleColor = json_encode($data['ColorId']);
		$ArticleSize = json_encode($data['SizeId']);
		$ArticleRatio = $data['RatioId'];
		
		$NoPacks = "";
		if($Colorflag==1){
			foreach($data['ColorId'] as $vl){
				$numberofpacks = $vl["Id"];
				if(isset($data["NoPacks_".$numberofpacks])){
					$NoPacks .= $data["NoPacks_".$numberofpacks].",";
				}else{
					$NoPacks .= "0,";
				}
			}
		} else{
			$NoPacks .= $data['NoPacks'];
		}
		
		$NoPacks = rtrim($NoPacks,',');
		
		$countcolor = count($data['ColorId']);
		$countration = array_sum(explode(",",$data['RatioId']));
		$countNoPacks = array_sum(explode(",",$NoPacks));
		$RatioId = explode(",",$data['RatioId']);
		if($Colorflag==1){
			$TotalSetQuantity = ($countNoPacks * $countration);
		}else{
			$TotalSetQuantity = ($countNoPacks * ($countration * $countcolor));
		}
		
		//echo $TotalSetQuantity; exit;
			
		/* $articleopenflag = DB::select("SELECT ArticleOpenFlag FROM `article` where Id = '".$data['ArticleId']."'");
		if($articleopenflag[0]->ArticleOpenFlag==1){
			$mixnopacks = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='".$ArticleId."'");
			if($mixnopacks[0]->total>0){
				$totalnopacks = ($TotalSetQuantity + $mixnopacks[0]->NoPacks);
				DB::table('mixnopacks')
				->where('Id', $mixnopacks[0]->Id)
				->update(['NoPacks'=>$totalnopacks, 'UpdatedDate' => date("Y-m-d H:i:s")]);
			}
		} */
		
		if($data['VendorId']==null || $data['VendorId']==0){
			$VendorId = 0;
		}else{
			$VendorId = $data['VendorId'];
		}
		
		/* $mixnopacks = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='".$ArticleId."'");
		if($mixnopacks[0]->total>0){
			$totalnopacks = (($mixnopacks[0]->NoPacks - $NoPacks) + $TotalSetQuantity);
		}
		echo $mixnopacks[0]->NoPacks."\n";
		echo $NoPacks."\n";
		echo $TotalSetQuantity."\n";
		echo $totalnopacks."\n";
echo "<pre>";
print_r($mixnopacks);
exit; */
		if($data["ArticleStatus"]==true){
			$ArticleStatus = 1;
		}else{
			$ArticleStatus = 0;
		}
		
		DB::beginTransaction();
		try {
			DB::table('article')
				->where('Id', $ArticleId)
				->update(['ArticleRate' => $data['Rate'], 'ArticleRatio' => $ArticleRatio, 'ArticleStatus'=>$ArticleStatus, 'UpdatedDate' => date("Y-m-d H:i:s")]);
			
			DB::table('articlerate')
				->where('ArticleId', $ArticleId)
				->update(['ArticleRate' => $data['Rate'], 'UpdatedDate'=>date("Y-m-d H:i:s")]);
			
			
			
			foreach($data['SizeId'] as $key => $vl){	
				DB::table('articleratio')
				->where('ArticleSizeId', $vl["Id"])
				->where('ArticleId', $ArticleId)
				->update(['ArticleRatio' => $RatioId[$key]]);
			}
				
			DB::table('inwardgrn')
				->where('GRN', $data['GRN_Number'])
				->update(['InwardDate'=>$data['InwardDate'], 'Remark'=>$data['Remark'], 'VendorId'=>$VendorId]);
				
			Inward::where('id', $data['id'])->update(array(
					 'NoPacks' =>  $NoPacks,
					 'SalesNoPacks' =>  $NoPacks,
					 'InwardDate' =>  $data['InwardDate'],
					 'TotalSetQuantity' => $TotalSetQuantity,
					 'Weight' =>  $data['Weight']
			));
			
			DB::commit();
			return response()->json("SUCCESS", 200);
		} catch (\Exception $e) {
			DB::rollback();
			return response()->json("Error", 200);
		}
			
		
		
		/* $mixnopacks = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='".$ArticleId."'");
		if($mixnopacks[0]->total>0){
			$totalnopacks = (($mixnopacks[0]->NoPacks - $NoPacks) + $TotalSetQuantity);
			
			DB::table('mixnopacks')
            ->where('Id', $mixnopacks[0]->Id)
            ->update(['NoPacks'=>$totalnopacks, 'UpdatedDate' => date("Y-m-d H:i:s")]);
		}else{
			DB::table('mixnopacks')->insertGetId(
				['NoPacks' => $NoPacks, 'CreatedDate' => date("Y-m-d H:i:s"), 'UpdatedDate' => date("Y-m-d H:i:s")]
			);
		} */
         //return response()->json("SUCCESS", 200);
     }
     public function Deleteinward($id, $ArticleId)
     {
		$updated = DB::table('article')
            ->where('Id', $ArticleId)
            ->update(['ArticleRate' => '', 'ArticleColor' => '', 'ArticleSize' => '', 'ArticleRatio' => '', 'UpdatedDate' => date("Y-m-d H:i:s")]);
			
		DB::table('articlerate')
            ->where('ArticleId', $ArticleId)
            ->update(['ArticleRate' => '']);
			
		DB::table('inwardarticle')->where('InwardId', '=', $id)->delete();
		DB::table('articlecolor')->where('ArticleId', '=', $ArticleId)->delete();
		DB::table('articlesize')->where('ArticleId', '=', $ArticleId)->delete();
		DB::table('articleratio')->where('ArticleId', '=', $ArticleId)->delete();
		
		$articleopenflag = DB::select("SELECT inw.TotalSetQuantity, a.ArticleOpenFlag FROM `inward` inw inner join article a on a.Id=inw.ArticleId where inw.Id='".$id."'");
		if($articleopenflag[0]->ArticleOpenFlag==1){
			$mixnopacks = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='".$ArticleId."'");
			if($mixnopacks[0]->total>0){
				$totalnopacks = ($mixnopacks[0]->NoPacks - $articleopenflag[0]->TotalSetQuantity);
				DB::table('mixnopacks')
				->where('Id', $mixnopacks[0]->Id)
				->update(['NoPacks'=>$totalnopacks, 'UpdatedDate' => date("Y-m-d H:i:s")]);
			}
		}
       DB::table('inward')->where('Id', '=', $id)->delete();
		return response()->json("SUCCESS", 200);
     }
	 
	 public function DeleteinwardGRN($GRN)
     {
		 $inwarddeletecheck = DB::select("SELECT ar.ArticleOpenFlag, (case ar.ArticleOpenFlag when '1' then IF(CONVERT((select mr.NoPacks from mixnopacks mr where mr.ArticleId=inw.ArticleId),UNSIGNED)>=CONVERT(inw.NoPacks,UNSIGNED),'true','false') else 0 END) MixDeleteStatus, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId left JOIN po p on p.ArticleId = ar.Id inner Join category c on c.Id=ar.CategoryId left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId left join articlecolor ac on ac.ArticleId=inw.ArticleId where inw.GRN='".$GRN."' group by inw.Id");
		 
		 foreach($inwarddeletecheck as $data){
			 if($data->ArticleOpenFlag==1){
				 if($data->MixDeleteStatus=="false"){
					return response()->json("AlreadyMixAssign", 200);
				 }
				 continue;
			 }else{
				if($data->SOID=='1'){
					 return response()->json("AlreadyArticleAssign", 200);
				 }
				 continue;
			 }
		 }
		 /* return $inwarddeletecheck;
		 exit; */
		 $inwardlist = DB::select("SELECT Id, ArticleId FROM `inward` where GRN ='".$GRN."'");
		 foreach($inwardlist as $vl){
			$id = $vl->Id;
			$ArticleId = $vl->ArticleId;
			
			DB::table('articlerate')
            ->where('ArticleId', $ArticleId)
            ->update(['ArticleRate' => '']);
			
			DB::table('article')
            ->where('Id', $ArticleId)
            ->update(['ArticleRate' => '', 'ArticleColor' => '', 'ArticleSize' => '', 'ArticleRatio' => '', 'ArticleStatus'=>0, 'UpdatedDate' => date("Y-m-d H:i:s")]);
		
			DB::table('articlecolor')->where('ArticleId', '=', $ArticleId)->delete();
			DB::table('articlesize')->where('ArticleId', '=', $ArticleId)->delete();
			DB::table('articleratio')->where('ArticleId', '=', $ArticleId)->delete();
		
			$articleopenflag = DB::select("SELECT inw.TotalSetQuantity, a.ArticleOpenFlag FROM `inward` inw inner join article a on a.Id=inw.ArticleId where inw.Id='".$id."'");
			if($articleopenflag[0]->ArticleOpenFlag==1){
				$mixnopacks = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='".$ArticleId."'");
				if($mixnopacks[0]->total>0){
					$totalnopacks = ($mixnopacks[0]->NoPacks - $articleopenflag[0]->TotalSetQuantity);
					DB::table('mixnopacks')
					->where('Id', $mixnopacks[0]->Id)
					->update(['NoPacks'=>$totalnopacks, 'UpdatedDate' => date("Y-m-d H:i:s")]);
				}
			}
		
			DB::table('inward')->where('Id', '=', $id)->delete();
			DB::table('inwardarticle')->where('InwardId', '=', $id)->delete();
		 }
		 
		 return response()->json("SUCCESS", 200);
     }
	 
	 
	 public function Cancellationinwardgrn(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();
		$GRN = $data["GRN"];		
		$Notes = $data["Notes"];
		
		/* $oldTask = Inward::find($id); 
		return print_r($oldTask); exit;
$newTask = $oldTask->replicate(); 
$newTask->setTable('inwardcancellationlogs');
$newTask->save();

return "ac"; exit; */
		
		//return array('GRN' => $GRN, 'Notes' => $Notes , 'CreatedDate' => date("Y-m-d H:i:s"), 'UpdatedDate' => date("Y-m-d H:i:s"));
		//exit;
		
		DB::table('inwardcancellation')->insertGetId(
			['GRN' => $GRN, 'Notes' => $Notes , 'CreatedDate' => date("Y-m-d H:i:s"), 'UpdatedDate' => date("Y-m-d H:i:s")]
		);
			
		//return $data;
		/* echo $GRN = $data["GRN"];
		echo "<br /><br /><br />";
		echo $notes = $data["notes"];
		exit; */
		$inwarddeletecheck = DB::select("SELECT ar.ArticleOpenFlag, (case ar.ArticleOpenFlag when '1' then IF(CONVERT((select mr.NoPacks from mixnopacks mr where mr.ArticleId=inw.ArticleId),UNSIGNED)>=CONVERT(inw.NoPacks,UNSIGNED),'true','false') else 0 END) MixDeleteStatus, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId left JOIN po p on p.ArticleId = ar.Id inner Join category c on c.Id=ar.CategoryId left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId left join articlecolor ac on ac.ArticleId=inw.ArticleId where inw.GRN='".$GRN."' group by inw.Id");
		 
		 foreach($inwarddeletecheck as $data){
			 if($data->ArticleOpenFlag==1){
				 if($data->MixDeleteStatus=="false"){
					return response()->json("AlreadyMixAssign", 200);
				 }
				 continue;
			 }else{
				if($data->SOID=='1'){
					 return response()->json("AlreadyArticleAssign", 200);
				 }
				 continue;
			 }
		 }
		 
		 $inwardlist = DB::select("SELECT Id, ArticleId, NoPacks, InwardDate, GRN, Weight, created_at, updated_at FROM `inward` where GRN ='".$GRN."'");
		 foreach($inwardlist as $vl){
			$id = $vl->Id;
			$ArticleId = $vl->ArticleId;
			
			
			/* DB::table('articlerate')
            ->where('ArticleId', $ArticleId)
            ->update(['ArticleRate' => '']);
			
			DB::table('article')
            ->where('Id', $ArticleId)
            ->update(['ArticleRate' => '', 'ArticleColor' => '', 'ArticleSize' => '', 'ArticleRatio' => '', 'ArticleStatus'=>0, 'UpdatedDate' => date("Y-m-d H:i:s")]);
		
			DB::table('articlecolor')->where('ArticleId', '=', $ArticleId)->delete();
			DB::table('articlesize')->where('ArticleId', '=', $ArticleId)->delete();
			DB::table('articleratio')->where('ArticleId', '=', $ArticleId)->delete(); */
		
			$articleopenflag = DB::select("SELECT inw.TotalSetQuantity, a.ArticleOpenFlag FROM `inward` inw inner join article a on a.Id=inw.ArticleId where inw.Id='".$id."'");
			if($articleopenflag[0]->ArticleOpenFlag==1){
				$mixnopacks = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='".$ArticleId."'");
				if($mixnopacks[0]->total>0){
					$totalnopacks = ($mixnopacks[0]->NoPacks - $articleopenflag[0]->TotalSetQuantity);
					DB::table('mixnopacks')
					->where('Id', $mixnopacks[0]->Id)
					->update(['NoPacks'=>$totalnopacks, 'UpdatedDate' => date("Y-m-d H:i:s")]);
				}
			}
		
			
			//inwardcancellationlogs
			//insert into inwardcancellationlogs (ArticleId, NoPacks, InwardDate, GRN, Weight, created_at, updated_at) select ArticleId, NoPacks, InwardDate, GRN, Weight, created_at, updated_at from inward where Id = 209
			
			
			/* $inwardlist = DB::select("SELECT Id, ArticleId FROM `inward` where GRN ='".$GRN."'");
			 foreach($inwardlist as $vl){
				$id = $vl->Id;
			 } */
			 
			 $NoPacks = $vl->NoPacks;
			$InwardDate = $vl->InwardDate;
			$GRN = $vl->GRN;
			$Weight = $vl->Weight;
			$created_at = $vl->created_at;
			$updated_at = $vl->updated_at;
			
			DB::table('inwardcancellationlogs')->insertGetId(
				['InwardId'=>$id,'ArticleId' => $ArticleId, 'NoPacks' => $NoPacks , 'InwardDate'=> $InwardDate, 'GRN'=> $GRN, 'Weight'=> $Weight, 'created_at' => $created_at, 'updated_at' => $updated_at]
			);
			
			DB::table('inward')->where('Id', '=', $id)->delete();
			//DB::table('inwardarticle')->where('InwardId', '=', $id)->delete();
		 }
		 
		 return response()->json("SUCCESS", 200);
     }
	 
     public function GetInwardIdWise($id)
     {
		 $getArticle = DB::select('SELECT inw.ArticleId,a.ArticleOpenFlag  FROM `inward` inw inner join article a on a.Id=inw.ArticleId where inw.Id= "'.$id.'"');
         //return DB::select('SELECT * From inward WHERE Id = '.$id.'');  
		 if($getArticle[0]->ArticleOpenFlag==0){
			return DB::select("SELECT inw.Id,ar.ArticleStatus, ingrn.GRN as GRN_Number, concat(ingrn.GRN, '/', fn1.StartYear,'-',fn1.EndYear) as GRN_Number_FinancialYear, inw.ArticleId,p.PO_Number,p.VendorId as VID,purn.PurchaseNumber, concat(purn.PurchaseNumber, '/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber_FinancialYear, (case when p.CategoryId IS NULL then ar.CategoryId else p.CategoryId end) as CategoryId, cc.Colorflag, cc.Title, b.Name as BrandName, inw.GRN,ar.ArticleNumber, ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,ar.StyleDescription,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId inner join inwardgrn ingrn on ingrn.Id=inw.GRN LEFT JOIN po p on p.ArticleId = ar.Id LEFT join purchasenumber purn on purn.Id=p.PO_Number LEFT join financialyear fn on fn.Id=purn.FinancialYearId inner join financialyear fn1 on fn1.Id=ingrn.FinancialYearId LEFT Join category c on c.Id=p.CategoryId LEFT Join category cc on cc.Id=ar.CategoryId LEFT join brand b on b.Id=ar.BrandId where inw.Id=".$id);
		 }else{
			//return DB::select('SELECT inw.Id,ar.ArticleStatus, ingrn.GRN as GRN_Number, inw.ArticleId,p.Id, p.PO_Number,p.VendorId as VID,purn.PurchaseNumber, concat(purn.PurchaseNumber, \'/\', fn.StartYear,\'-\',fn.EndYear) as PurchaseNumber_FinancialYear, p.CategoryId, c.Colorflag, c.Title, b.Name as BrandName, a.ArticleNumber, inw.GRN,ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,ar.StyleDescription,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN inwardarticle ar on ar.InwardId = inw.Id inner join inwardgrn ingrn on ingrn.Id=inw.GRN LEFT JOIN po p on p.ArticleId = ar.ArticleId inner join purchasenumber purn on purn.Id=p.PO_Number LEFT Join category c on c.Id=p.CategoryId inner join article a on a.Id=inw.ArticleId inner join brand b on b.Id=p.BrandId where inw.Id="'. $id .'"');
			return DB::select("SELECT inw.Id,a.ArticleStatus, ingrn.GRN as GRN_Number, inw.ArticleId, p.PO_Number,p.VendorId as VID,purn.PurchaseNumber, concat(purn.PurchaseNumber, '/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber_FinancialYear, a.CategoryId, cc.Colorflag, cc.Title, b.Name as BrandName, a.ArticleNumber, inw.GRN,ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,a.StyleDescription,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN inwardarticle ar on ar.InwardId = inw.Id inner join inwardgrn ingrn on ingrn.Id=inw.GRN LEFT JOIN po p on p.ArticleId = ar.ArticleId left join purchasenumber purn on purn.Id=p.PO_Number inner join article a on a.Id=inw.ArticleId LEFT Join category c on c.Id=p.CategoryId LEFT Join category cc on cc.Id=a.CategoryId left join brand b on b.Id=a.BrandId left join financialyear fn on fn.Id=ingrn.FinancialYearId where inw.Id=". $id);
		 }
	 }
	 
	 public function InwardListFromGRN($id)
     {
		//$as = DB::select("SELECT (case ar.ArticleOpenFlag when '1' then IF(CONVERT((select mr.NoPacks from mixnopacks mr where mr.ArticleId=inw.ArticleId), UNSIGNED)>=CONVERT(inw.NoPacks, UNSIGNED),'true','false') else 0 END) MixDeleteStatus,inw.Id,inw.ArticleId,v.Name,count(ac.ArticleId) as ColorCount,c.Colorflag, c.Title, inw.GRN,ar.ArticleNumber, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,ar.ArticleOpenFlag,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId inner JOIN po p on p.ArticleId = ar.Id inner Join category c on c.Id=p.CategoryId inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId left join articlecolor ac on ac.ArticleId=inw.ArticleId where inw.GRN='". $id ."' group by inw.Id");
		//$as = DB::select("SELECT (case ar.ArticleOpenFlag when '1' then IF(CONVERT((select mr.NoPacks from mixnopacks mr where mr.ArticleId=inw.ArticleId), UNSIGNED)>=CONVERT(inw.NoPacks, UNSIGNED),'true','false') else 0 END) MixDeleteStatus,inw.Id,inw.ArticleId,v.Name,count(ac.ArticleId) as ColorCount,c.Colorflag, (case when c.Title IS NULL then cc.Title else c.Title end) as Title, inw.GRN,ar.ArticleNumber, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,ar.ArticleOpenFlag,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId left JOIN po p on p.ArticleId = ar.Id left Join category c on c.Id=p.CategoryId left Join category cc on cc.Id=ar.CategoryId left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId left join articlecolor ac on ac.ArticleId=inw.ArticleId where inw.GRN='". $id ."' group by inw.Id");
		$as = DB::select("SELECT (case ar.ArticleOpenFlag when '1' then IF(CONVERT((select mr.NoPacks from mixnopacks mr where mr.ArticleId=inw.ArticleId), UNSIGNED)>=CONVERT(inw.NoPacks, UNSIGNED),'true','false') else 0 END) MixDeleteStatus,inw.Id,inw.ArticleId,v.Name,count(ac.ArticleId) as ColorCount,cc.Colorflag, cc.Title, inw.GRN,ar.ArticleNumber, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,ar.ArticleOpenFlag,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId left JOIN po p on p.ArticleId = ar.Id left Join category c on c.Id=p.CategoryId left Join category cc on cc.Id=ar.CategoryId left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId left join articlecolor ac on ac.ArticleId=inw.ArticleId where inw.GRN='". $id ."' group by inw.Id");
		foreach($as as $key => $val){
			$object = (object)$val;
			$object->ArticleColor = json_decode($val->ArticleColor, false);
		}
		
		return $as;
		//echo "<pre>"; print_r($as); exit;
		//return DB::select("SELECT inw.Id,inw.ArticleId,v.Name,count(ac.ArticleId) as ColorCount,c.Colorflag, c.Title, inw.GRN,ar.ArticleNumber, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,ar.ArticleOpenFlag,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId inner JOIN po p on p.ArticleId = ar.Id inner Join category c on c.Id=p.CategoryId inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId left join articlecolor ac on ac.ArticleId=inw.ArticleId where inw.GRN='". $id ."' group by inw.Id");
	 }
	 
	 public function InwardDateRemarkFromGRN($id)
     {
		// return DB::select("SELECT * FROM `inwardgrn` inw inner join  where Id='". $id ."'");
		return DB::select("SELECT ig.*, concat(ig.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number_FinancialYear FROM `inwardgrn` ig inner join financialyear fn on fn.Id=ig.FinancialYearId where ig.Id = '". $id ."'");
	 }
	 
	 public function getinwardgrn()
    {
		//return "test"; //exit;
		$array = array();
        $inwardGRNdata = DB::select('SELECT Id, GRN, FinancialYearId From inwardgrn order by Id desc limit 0,1');
		$fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
		//echo "<pre>"; print_r($fin_yr); exit;
		
		/* $date = "2012-04-01";
		//$date = date('Y-m-d');
		$month =  date('m', strtotime($date));
		$year =  date('y', strtotime($date));
		

		if(count($inwardGRNdata)>0){
			 if($month > 03) {
				$d = date('Y-m-d', strtotime('+1 years', strtotime($date)));
				$FinanceYear =  $year .'-'.date('y', strtotime($d));
			} else {
				$d = date('Y-m-d', strtotime('-1 years', strtotime($date)));
				$FinanceYear =  date('y', strtotime($d)).'-'.$year;
			}
			
			$array["GRN"] = ($inwardGRNdata[0]->GRN) + 1;
			$array["Id"] = $inwardGRNdata[0]->Id + 1;
			return response()->json($array, 200);
		}
        else{
			$array["GRN"] = 1;
			$array["Id"] = 1;
			return response()->json($array, 200);
        } */
		
		
		
		if(count($inwardGRNdata)>0){
			if($fin_yr[0]->Id > $inwardGRNdata[0]->FinancialYearId){
				$array["GRN"] = 1;
				$array["Id"] = $inwardGRNdata[0]->Id + 1;
				$array["GRN_Financial"] =  1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return response()->json($array, 200);
			} else{
				$array["GRN"] = ($inwardGRNdata[0]->GRN) + 1;
				$array["Id"] = $inwardGRNdata[0]->Id + 1;
				$array["GRN_Financial"] = ($inwardGRNdata[0]->Id) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return response()->json($array, 200);
			}
		}
        else{
			$array["GRN"] = 1;
			$array["Id"] = 1;
			$array["GRN_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
			return response()->json($array, 200);
        }
    }
	
	


	public function GetEditArticalIdWise($id)
	{
			//return DB::select('SELECT p.NumPacks,ar.ArticleNumber,c.Colorflag, v.Name FROM `article` ar inner JOIN po p on ar.Id = p.ArticleId INNER JOIN category c on c.Id=p.CategoryId inner join vendor v on v.Id=p.VendorId WHERE ar.Id= "' . $id . '"');
			return DB::select("SELECT p.NumPacks,ar.ArticleNumber,cc.Colorflag, v.Name FROM `article` ar left JOIN po p on ar.Id = p.ArticleId left JOIN category c on c.Id=p.CategoryId left JOIN category cc on cc.Id=ar.CategoryId left join vendor v on v.Id=p.VendorId WHERE ar.Id=" . $id );
	}
	
	public function GetInwardChallen($id, $type){
		
		//return $type; exit;
		//SELECT pur.PurchaseNumber, ingrn.GRN, ingrn.InwardDate, ingrn.Remark, art.ArticleNumber, c.Title, inw.TotalSetQuantity, inw.NoPacks, art.ArticleColor, art.ArticleSize, art.ArticleRate, inw.Weight FROM inward inw inner join inwardgrn ingrn on inw.GRN=ingrn.Id inner join article art on inw.ArticleId=art.Id inner join po p on p.ArticleId=art.Id inner join purchasenumber pur on pur.Id=p.PO_Number inner join category c on c.Id=p.CategoryId where inw.GRN = 3
		//$getinwardchallen = DB::select('SELECT ingrn.InwardDate, ingrn.GRN, pur.PurchaseNumber, ingrn.Remark, v.Name, v.Address, v.GSTNumber, art.ArticleNumber, c.Title, inw.TotalSetQuantity, inw.NoPacks, art.ArticleColor, art.ArticleSize, art.ArticleRate, inw.Weight FROM inward inw inner join inwardgrn ingrn on inw.GRN=ingrn.Id inner join article art on inw.ArticleId=art.Id inner join po p on p.ArticleId=art.Id inner join purchasenumber pur on pur.Id=p.PO_Number inner join vendor v on v.Id=p.VendorId inner join category c on c.Id=p.CategoryId where inw.GRN =' . $id . '');
		if($type==0){ 
			//$getinwardchallen = DB::select('SELECT  inwc.Notes, u.Name as PreparedBy, ingrn.VendorId, concat(ingrn.GRN,\'/\' ,fy.StartYear,\'-\',fy.EndYear) as GRN, concat(pur.PurchaseNumber,\'/\' ,fy1.StartYear,\'-\',fy1.EndYear) as PurchaseNumber,p.PoDate,inw.Id, inwart.ArticleRate as mixrate, inwart.ArticleColor as mixcolor, inwart.ArticleSize as mixsize, inwart.ArticleRatio as mixratio, ingrn.InwardDate,brn.Name as BrandName, ingrn.Remark, v.Name, v.Address, v.GSTNumber, art.ArticleNumber, c.Title, inw.NoPacks, c.ArticleOpenFlag, c.Colorflag, art.ArticleColor, art.ArticleSize, art.ArticleRate, art.ArticleRatio, CountNoPacks(art.ArticleRatio) as TotalArticleRatio, art.StyleDescription, inw.Weight FROM inwardcancellationlogs inw inner join inwardgrn ingrn on inw.GRN=ingrn.Id inner join inwardcancellation inwc on inwc.GRN=inw.GRN inner join article art on inw.ArticleId=art.Id left join po p on p.ArticleId=art.Id left join purchasenumber pur on pur.Id=p.PO_Number left join vendor v on v.Id=p.VendorId inner join category c on c.Id=art.CategoryId left join inwardarticle inwart on inwart.InwardId=inw.InwardId left join brand brn on brn.Id=p.BrandId inner join financialyear fy on fy.Id=ingrn.FinancialYearId left join financialyear fy1 on fy1.Id=pur.FinancialYearId inner join users u on u.Id=ingrn.UserId where inw.GRN =' . $id . ' order by inw.Id asc');
			$getinwardchallen = DB::select("SELECT  inwc.Notes, u.Name as PreparedBy, ingrn.VendorId, concat(ingrn.GRN,'/' ,fy.StartYear,'-',fy.EndYear) as GRN, concat(pur.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber,p.PoDate,inw.Id, inwart.ArticleRate as mixrate, inwart.ArticleColor as mixcolor, inwart.ArticleSize as mixsize, inwart.ArticleRatio as mixratio, ingrn.InwardDate,brn.Name as BrandName, ingrn.Remark, v.Name, v.Address, v.GSTNumber, art.ArticleNumber, c.Title, inw.NoPacks, c.ArticleOpenFlag, c.Colorflag, art.ArticleColor, art.ArticleSize, art.ArticleRate, art.ArticleRatio, CountNoPacks(art.ArticleRatio) as TotalArticleRatio, art.StyleDescription, inw.Weight FROM inwardcancellationlogs inw inner join inwardgrn ingrn on inw.GRN=ingrn.Id inner join inwardcancellation inwc on inwc.GRN=inw.GRN inner join article art on inw.ArticleId=art.Id left join po p on p.ArticleId=art.Id left join purchasenumber pur on pur.Id=p.PO_Number left join vendor v on v.Id=p.VendorId inner join category c on c.Id=art.CategoryId left join inwardarticle inwart on inwart.InwardId=inw.InwardId left join brand brn on brn.Id=art.BrandId inner join financialyear fy on fy.Id=ingrn.FinancialYearId left join financialyear fy1 on fy1.Id=pur.FinancialYearId left join users u on u.Id=ingrn.UserId where inw.GRN ='" . $id . "' order by inw.Id asc");
		} else{
			//$getinwardchallen = DB::select('SELECT "Notes", u.Name as PreparedBy, ingrn.VendorId, concat(ingrn.GRN,\'/\' ,fy.StartYear,\'-\',fy.EndYear) as GRN, concat(pur.PurchaseNumber,\'/\' ,fy1.StartYear,\'-\',fy1.EndYear) as PurchaseNumber,p.PoDate,inw.Id, inwart.ArticleRate as mixrate, inwart.ArticleColor as mixcolor, inwart.ArticleSize as mixsize, inwart.ArticleRatio as mixratio, ingrn.InwardDate,brn.Name as BrandName, ingrn.Remark, v.Name, v.Address, v.GSTNumber, art.ArticleNumber, c.Title, inw.NoPacks, c.ArticleOpenFlag, c.Colorflag, art.ArticleColor, art.ArticleSize, art.ArticleRate, art.ArticleRatio, CountNoPacks(art.ArticleRatio) as TotalArticleRatio, art.StyleDescription, inw.Weight FROM inward inw inner join inwardgrn ingrn on inw.GRN=ingrn.Id inner join article art on inw.ArticleId=art.Id left join po p on p.ArticleId=art.Id left join purchasenumber pur on pur.Id=p.PO_Number left join vendor v on v.Id=p.VendorId inner join category c on c.Id=art.CategoryId left join inwardarticle inwart on inwart.InwardId=inw.Id left join brand brn on brn.Id=p.BrandId inner join financialyear fy on fy.Id=ingrn.FinancialYearId left join financialyear fy1 on fy1.Id=pur.FinancialYearId inner join users u on u.Id=ingrn.UserId where inw.GRN =' . $id . ' order by inw.Id asc');
			$getinwardchallen = DB::select("SELECT 'Notes', u.Name as PreparedBy, ingrn.VendorId, concat(ingrn.GRN,'/' ,fy.StartYear,'-',fy.EndYear) as GRN, concat(pur.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber,p.PoDate,inw.Id, inwart.ArticleRate as mixrate, inwart.ArticleColor as mixcolor, inwart.ArticleSize as mixsize, inwart.ArticleRatio as mixratio, ingrn.InwardDate,brn.Name as BrandName, ingrn.Remark, v.Name, v.Address, v.GSTNumber, art.ArticleNumber, c.Title, inw.NoPacks, c.ArticleOpenFlag, c.Colorflag, art.ArticleColor, art.ArticleSize, art.ArticleRate, art.ArticleRatio, CountNoPacks(art.ArticleRatio) as TotalArticleRatio, art.StyleDescription, inw.Weight FROM inward inw inner join inwardgrn ingrn on inw.GRN=ingrn.Id inner join article art on inw.ArticleId=art.Id left join po p on p.ArticleId=art.Id left join purchasenumber pur on pur.Id=p.PO_Number left join vendor v on v.Id=p.VendorId inner join category c on c.Id=art.CategoryId left join inwardarticle inwart on inwart.InwardId=inw.Id left join brand brn on brn.Id=art.BrandId inner join financialyear fy on fy.Id=ingrn.FinancialYearId left join financialyear fy1 on fy1.Id=pur.FinancialYearId left join users u on u.Id=ingrn.UserId where inw.GRN ='" . $id . "' group by inw.Id order by inw.Id asc");
		}
		
		
		//echo "<pre>"; print_r($getinwardchallen); 
		
		//echo "<pre>"; print_r($getinwardchallen);
		
		if($getinwardchallen[0]->VendorId!=0){
			$getdata = DB::select("select * from vendor where Id='".$getinwardchallen[0]->VendorId."'");
			$vendorInformation = true;
			$vendorName = $getdata[0]->Name;
			$vendorAddress = $getdata[0]->Address;
			$vendorGSTNumber = $getdata[0]->GSTNumber;
		}else{
			$vendorInformation = false;
			$vendorName = "";
			$vendorAddress = "";
			$vendorGSTNumber = "";
		}
		
		
		/* if($getinwardchallen>0){
			echo "CHECK";
			//echo $getinwardchallen[0]->Id;
		}else{
			echo "EXIST";
		} */
		//exit;
		$challendata = [];
		$countNoPacks = 0;
		$countRate = 0;
		$countWight = 0;
		foreach($getinwardchallen as $vl){
			//echo $vl->InwardDate;
			
		
			$ArticleOpenFlag = $vl->ArticleOpenFlag;
			$Colorflag = $vl->Colorflag;
			$InwardDate = $vl->InwardDate;
			$GRN = $vl->GRN;
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
			if( strpos($NoPacks, ',') !== false ) {
				$singlecountNoPacks = array_sum(explode(",",$NoPacks));
				$countNoPacks+= array_sum(explode(",",$NoPacks));
				$string = explode(',', $NoPacks);
				$stringcomma = 1;
			}else{
				$singlecountNoPacks = $NoPacks;
				$countNoPacks+= $NoPacks;
				$string = $NoPacks;
				$stringcomma = 0;
			}
			
			
			if($ArticleOpenFlag==0){
				$ArticleRate = $vl->ArticleRate;
				$countRate += $ArticleRate;
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
				
				if($Colorflag==0){
					$getcolorcount = count($getcolor);
					$Tempnumber = ($singlecountNoPacks/($getcolorcount * $TotalArticleRatio));
					
					if((int)$Tempnumber != $Tempnumber){
						$TempSet = floor($Tempnumber);
						$mult = ($TotalArticleRatio*$getcolorcount);
						$div = ($singlecountNoPacks/($mult));
						
						$TotalValue = $singlecountNoPacks - (floor($div) * $mult);
						$Totalstockvalue = $TempSet."-".$TotalValue;
					}else{
						$Totalstockvalue = $Tempnumber;
					}
				}else{
					$Totalstockvalue ="";
					if($stringcomma==1){
						foreach($string as $value){
							$Tempnumber = ($value/$TotalArticleRatio);
							
							if((int)$Tempnumber != $Tempnumber){
								$TempSet = floor($Tempnumber);
								$Totaltmp = $TempSet * $TotalArticleRatio;
								$TotalValue = $value - $Totaltmp;
								$Totalstockvalue .= $TempSet."-".$TotalValue.",";
							}else{
								$Totalstockvalue .= $Tempnumber.",";
							}
						}
						$Totalstockvalue = rtrim($Totalstockvalue,',');
					}else{
						$Tempnumber = ($singlecountNoPacks/$TotalArticleRatio);
							
						if((int)$Tempnumber != $Tempnumber){
							$TempSet = floor($Tempnumber);
							$Totaltmp = $TempSet * $TotalArticleRatio;
							$TotalValue = $singlecountNoPacks - $Totaltmp;
							$Totalstockvalue = $TempSet."-".$TotalValue;
						}else{
							$Totalstockvalue = $Tempnumber;
						}
					}
					
				}
			}else{
				$ArticleRate = $vl->mixrate;
				$ArticleRatio = $vl->mixratio;
				if( strpos($vl->mixratio, ',') !== false ) {
					$ArticlemixRatio = array_sum(explode(",",$ArticleRatio));
				}else{
					$ArticlemixRatio = $ArticleRatio;
				}
				$countRate += $ArticleRate;
				
				$mixcolor = json_decode($vl->mixcolor);
				$mixsize = json_decode($vl->mixsize);
				
				$ArticleColor = "";
				foreach($mixcolor as $vl){
					$ArticleColor .= $vl->Name.",";
				}
				$ArticleColor = rtrim($ArticleColor,',');
				
				$ArticleSize = "";
				foreach($mixsize as $vl){
					$ArticleSize .= $vl->Name.",";
				}
				$ArticleSize = rtrim($ArticleSize,',');
				$Totalstockvalue = $NoPacks * $ArticlemixRatio;
			}
			
		
			$challendata[] = json_decode(json_encode(array("Notes"=>$Notes, "PreparedBy"=>$PreparedBy, "InwardDate"=>$InwardDate,"PoDate"=>$PoDate,"GRN"=>$GRN,"PurchaseNumber"=>$PurchaseNumber,"BrandName"=>$BrandName, "Remark"=>$Remark,"Name"=>$Name,"Address"=>$Address,"GSTNumber"=>$GSTNumber,"ArticleNumber"=>$ArticleNumber,"Title"=>$Title,"StyleDescription"=>$StyleDescription, "TotalSetQuantity"=>$Totalstockvalue, "NoPacks"=>$NoPacks, "ArticleRate"=>number_format($ArticleRate, 2),"Weight"=>number_format($Weight,2),"ArticleColor"=>$ArticleColor, "ArticleSize"=>$ArticleSize, "ArticleRatio"=>$ArticleRatio)), false);
		}
		
		$as  = array($challendata, array("countNoPacks"=>$countNoPacks, "countRate"=>$countRate,"countWight"=>number_format($countWight,2), "vendorInformation" => $vendorInformation,"vendorName" => $vendorName,"vendorAddress" => $vendorAddress,"vendorGSTNumber" => $vendorGSTNumber));
		
		//echo "<pre>"; print_r($getinwardchallen); exit;
		return $as;
	}


	public function inwardcolorcheck(){
		$article = DB::select("SELECT (CASE WHEN ac.ArticleId IS NULL THEN '0' ELSE ac.ArticleId END) AS articlecolorcheck, a.* FROM `article` a left join articlecolor ac on ac.ArticleId=a.Id group by a.Id");
		//echo "<pre>"; print_r($article); exit;
		foreach($article as $key => $vl){
			//echo $vl->ArticleRate; exit;
			if($vl->articlecolorcheck==0 && $vl->ArticleOpenFlag==0 && $vl->ArticleRate!=""){
				$getcolor = json_decode($vl->ArticleColor);
				$getsize = json_decode($vl->ArticleSize);
				$ArticleId = $vl->Id;
				$getratio = explode(",",$vl->ArticleRatio);
				foreach($getcolor as $vl){
					//$ArticleColor .= $vl->Name.",";
					$colorgetId = DB::table('articlecolor')->insertGetId(
						['ArticleId'=>$ArticleId, 'ArticleColorId' => $vl->Id, 'ArticleColorName' => $vl->Name,'CreatedDate' => date("Y-m-d H:i:s")]
					);
				}
				
				foreach($getsize as $key => $vl){
					//$ArticleSize = $vl->Name;
					$articlesize = DB::table('articlesize')->insertGetId(
						['ArticleId'=>$ArticleId, 'ArticleSize' => $vl->Id,'ArticleSizeName' => $vl->Name,'CreatedDate' => date("Y-m-d H:i:s")]
					);
					
					DB::table('articleratio')->insertGetId(
						['ArticleId'=>$ArticleId, 'ArticleSizeId' => $vl->Id, 'ArticleRatio' => $getratio[$key],'CreatedDate' => date("Y-m-d H:i:s")]
					);
				}
				
			}
		}
	}
}
