<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportAllStocksExport;

class ReportController extends Controller
{
    
	public function GetAllStocks()
    {
		//$data = DB::select("select dd.*, (CASE dd.ArticleOpenFlag When 0 then GetStockArticlePieces(dd.Id) else dd.TotalNoPacksStocks END) as TotalPieces from (SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalNoPacksStocks, GROUP_CONCAT(DISTINCT CONCAT(ac.ArticleColorName) ORDER BY ac.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleRatio, cat.Colorflag, cat.Title FROM `inward` inw inner join article a on a.Id=inw.ArticleId inner join articlecolor ac on ac.ArticleId=a.Id inner join articlesize asz on asz.ArticleId=a.Id inner join po p on p.ArticleId=a.Id inner join category cat on cat.Id=p.CategoryId where a.ArticleOpenFlag=0 group by a.Id HAVING TotalNoPacksStocks > 0 Union SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, '-', mxn.NoPacks as TotalNoPacksStocks, '-', '-', '-', '-',c.Title FROM `mixnopacks` mxn inner join article a on a.Id=mxn.ArticleId inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId where a.ArticleOpenFlag=1 HAVING TotalNoPacksStocks > 0) as dd");
		
		
		//$data = DB::select("select dd.*, CountNoPacks(dd.ArticleRatio) as TotalArticleRatio  from (SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalPieces, GROUP_CONCAT(DISTINCT CONCAT(ac.ArticleColorName) ORDER BY ac.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleRatio, cat.Colorflag, cat.Title FROM `inward` inw inner join article a on a.Id=inw.ArticleId inner join articlecolor ac on ac.ArticleId=a.Id inner join articlesize asz on asz.ArticleId=a.Id inner join po p on p.ArticleId=a.Id inner join category cat on cat.Id=p.CategoryId where a.ArticleOpenFlag=0 group by a.Id HAVING TotalPieces > 0 Union SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, '-', mxn.NoPacks as TotalPieces, '-', '-', '-', '-',c.Title FROM `mixnopacks` mxn inner join article a on a.Id=mxn.ArticleId inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId where a.ArticleOpenFlag=1 HAVING TotalPieces > 0) as dd");
		//$data = DB::select("select dd.*, CountNoPacks(dd.ArticleRatio) as TotalArticleRatio  from (SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalPieces, GROUP_CONCAT(DISTINCT CONCAT(ac.ArticleColorName) ORDER BY ac.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleRatio, cat.Colorflag, cat.Title FROM `inward` inw inner join article a on a.Id=inw.ArticleId inner join articlecolor ac on ac.ArticleId=a.Id inner join articlesize asz on asz.ArticleId=a.Id inner join po p on p.ArticleId=a.Id inner join category cat on cat.Id=p.CategoryId inner join productlaunch pl on pl.ArticleId=a.Id where pl.ProductStatus= 1 and a.ArticleOpenFlag=0 group by a.Id HAVING TotalPieces > 0 Union SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, '-', mxn.NoPacks as TotalPieces, '-', '-', '-', '-',c.Title FROM `mixnopacks` mxn inner join article a on a.Id=mxn.ArticleId inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId where a.ArticleOpenFlag=1 HAVING TotalPieces > 0) as dd");
		//$data = DB::select("select dv.Id, dv.ArticleNumber, dv.ArticleOpenFlag, dv.SalesNoPacks, dv.TotalPieces, dv.ArticleColor, dv.ArticleSize, dv.ArticleRatio, dv.Colorflag, dv.Title, CountNoPacks(dv.ArticleRatio) as TotalArticleRatio from (select * from (SELECT (case when pl.ProductStatus IS NULL then 1 else pl.ProductStatus end)  as ProductStatusData, a.Id, a.ArticleNumber, a.ArticleOpenFlag, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalPieces, GROUP_CONCAT(DISTINCT CONCAT(ac.ArticleColorName) ORDER BY ac.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleRatio, cat.Colorflag, cat.Title FROM `inward` inw inner join article a on a.Id=inw.ArticleId left join articlecolor ac on ac.ArticleId=a.Id left join articlesize asz on asz.ArticleId=a.Id left join po p on p.ArticleId=a.Id inner join category cat on cat.Id=a.CategoryId left join productlaunch pl on pl.ArticleId=a.Id group by a.Id) as ddd where ddd.ProductStatusData= 1 and ddd.ArticleOpenFlag=0 HAVING ddd.TotalPieces > 0 Union SELECT 1, a.Id, a.ArticleNumber, a.ArticleOpenFlag, '-', mxn.NoPacks as TotalPieces, '-', '-', '-', '-',c.Title FROM `mixnopacks` mxn inner join article a on a.Id=mxn.ArticleId left join po p on p.ArticleId=a.Id left join category c on c.Id=a.CategoryId where a.ArticleOpenFlag=1 HAVING TotalPieces > 0) as dv");
		//$data = DB::select("select dv.Id, dv.ArticleNumber, dv.ArticleOpenFlag, dv.SalesNoPacks, dv.TotalPieces, dv.ArticleColor, dv.ArticleSize, dv.ArticleRatio, dv.Colorflag, dv.Title, dv.BrandName, dv.Subcategory,  dv.SeriesName, dv.Series, dv.StyleDescription, CountNoPacks(dv.ArticleRatio) as TotalArticleRatio from (select * from (SELECT (case when pl.ProductStatus IS NULL then 1 else pl.ProductStatus end)  as ProductStatusData, a.Id, a.ArticleNumber, a.ArticleOpenFlag, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalPieces, GROUP_CONCAT(DISTINCT CONCAT(ac.ArticleColorName) ORDER BY ac.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleRatio, cat.Colorflag, cat.Title, bn.Name as BrandName, subc.Name as Subcategory, rs.SeriesName, rs.Series, a.StyleDescription FROM `inward` inw inner join article a on a.Id=inw.ArticleId left join articlecolor ac on ac.ArticleId=a.Id left join articlesize asz on asz.ArticleId=a.Id left join po p on p.ArticleId=a.Id inner join category cat on cat.Id=a.CategoryId left join productlaunch pl on pl.ArticleId=a.Id left join brand bn on bn.Id= a.BrandId left join subcategory subc on subc.Id=a.SubCategoryId left join rangeseries rs on rs.Id=a.SeriesId group by a.Id) as ddd where ddd.ProductStatusData= 1 and ddd.ArticleOpenFlag=0 HAVING ddd.TotalPieces > 0 Union SELECT 1, a.Id, a.ArticleNumber, a.ArticleOpenFlag, '-', mxn.NoPacks as TotalPieces, '-', '-', '-', '-',c.Title, '-', '-', '-', '-', '-' FROM `mixnopacks` mxn inner join article a on a.Id=mxn.ArticleId left join po p on p.ArticleId=a.Id left join category c on c.Id=a.CategoryId where a.ArticleOpenFlag=1 HAVING TotalPieces > 0) as dv");
		
		$data = DB::select("select dv.Id, dv.ArticleNumber, dv.ArticleOpenFlag, dv.SalesNoPacks, dv.TotalPieces, dv.ArticleColor, dv.ArticleSize, dv.ArticleRatio, dv.Colorflag, dv.Title, dv.BrandName, dv.Subcategory,  dv.SeriesName, dv.Series, dv.StyleDescription, CountNoPacks(dv.ArticleRatio) as TotalArticleRatio, DATE_FORMAT(dv.created_at, '%d/%m/%Y') as InwardDate  from (select * from (SELECT (case when pl.ProductStatus IS NULL then 1 else pl.ProductStatus end)  as ProductStatusData, a.Id, a.ArticleNumber, a.ArticleOpenFlag, inw.created_at, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalPieces, GROUP_CONCAT(DISTINCT CONCAT(ac.ArticleColorName) ORDER BY ac.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleRatio, cat.Colorflag, cat.Title, bn.Name as BrandName, subc.Name as Subcategory, rs.SeriesName, rs.Series, a.StyleDescription FROM `inward` inw inner join article a on a.Id=inw.ArticleId left join articlecolor ac on ac.ArticleId=a.Id left join articlesize asz on asz.ArticleId=a.Id left join po p on p.ArticleId=a.Id inner join category cat on cat.Id=a.CategoryId left join productlaunch pl on pl.ArticleId=a.Id left join brand bn on bn.Id= a.BrandId left join subcategory subc on subc.Id=a.SubCategoryId left join rangeseries rs on rs.Id=a.SeriesId group by a.Id) as ddd where ddd.ProductStatusData= 1 and ddd.ArticleOpenFlag=0 HAVING ddd.TotalPieces > 0 Union SELECT 1, a.Id, a.ArticleNumber, a.ArticleOpenFlag, '-', mxn.NoPacks as TotalPieces, '-', '-', '-', '-',c.Title, '-', '-', '-', '-', '-','-' FROM `mixnopacks` mxn inner join article a on a.Id=mxn.ArticleId left join po p on p.ArticleId=a.Id left join category c on c.Id=a.CategoryId where a.ArticleOpenFlag=1 HAVING TotalPieces > 0) as dv");
		//select dv.Id, dv.ArticleNumber, dv.ArticleOpenFlag, dv.SalesNoPacks, dv.TotalPieces, dv.ArticleColor, dv.ArticleSize, dv.ArticleRatio, dv.Colorflag, dv.Title, dv.BrandName, dv.Subcategory,  dv.SeriesName, dv.Series, CountNoPacks(dv.ArticleRatio) as TotalArticleRatio from (select * from (SELECT (case when pl.ProductStatus IS NULL then 1 else pl.ProductStatus end)  as ProductStatusData, a.Id, a.ArticleNumber, a.ArticleOpenFlag, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalPieces, GROUP_CONCAT(DISTINCT CONCAT(ac.ArticleColorName) ORDER BY ac.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleRatio, cat.Colorflag, cat.Title, bn.Name as BrandName, subc.Name as Subcategory, rs.SeriesName, rs.Series FROM `inward` inw inner join article a on a.Id=inw.ArticleId left join articlecolor ac on ac.ArticleId=a.Id left join articlesize asz on asz.ArticleId=a.Id left join po p on p.ArticleId=a.Id inner join category cat on cat.Id=a.CategoryId left join productlaunch pl on pl.ArticleId=a.Id left join brand bn on bn.Id= a.BrandId left join subcategory subc on subc.Id=a.SubCategoryId left join rangeseries rs on rs.Id=a.SeriesId group by a.Id) as ddd where ddd.ProductStatusData= 1 and ddd.ArticleOpenFlag=0 HAVING ddd.TotalPieces > 0 Union SELECT 1, a.Id, a.ArticleNumber, a.ArticleOpenFlag, '-', mxn.NoPacks as TotalPieces, '-', '-', '-', '-',c.Title, '-', '-', '-', '-' FROM `mixnopacks` mxn inner join article a on a.Id=mxn.ArticleId left join po p on p.ArticleId=a.Id left join category c on c.Id=a.CategoryId where a.ArticleOpenFlag=1 HAVING TotalPieces > 0) as dv
		//return array("data"=>$data);
	  
		//echo "<pre>"; print_r($data); exit;
		
		foreach($data as $vl){
			$object = (object)$vl;
			
			$TotalPieces = $vl->TotalPieces;
			$TotalArticleRatio = $vl->TotalArticleRatio;
			$NoPacks = $vl->SalesNoPacks;
			$Colorflag = $vl->Colorflag;
			$getcolorcount= count(explode(",",$vl->ArticleColor));
			
			if( strpos($NoPacks, ',') !== false ) {
				$singlecountNoPacks = $TotalPieces;
				$string = explode(',', $NoPacks);
				$stringcomma = 1;
			}else{
				$singlecountNoPacks = $NoPacks;
				$string = $NoPacks;
				$stringcomma = 0;
			}
			
			//Logic to get stock
			if($vl->ArticleOpenFlag==0){
				if($Colorflag==0){
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
				$Totalstockvalue = $TotalPieces;
			}
			$object->TotalNoPacks = $Totalstockvalue;
		}
		
		
		//echo "<pre>"; print_r($data); exit;
		
		return array("data"=>$data);
	}
	
	public function PostAllStocks(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
		$vnddataTotal = DB::select("select count(*) as Total from (select dd.*, CountNoPacks(dd.ArticleRatio) as TotalArticleRatio  from (SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalPieces, GROUP_CONCAT(DISTINCT CONCAT(ac.ArticleColorName) ORDER BY ac.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleRatio, cat.Colorflag, cat.Title FROM `inward` inw inner join article a on a.Id=inw.ArticleId inner join articlecolor ac on ac.ArticleId=a.Id inner join articlesize asz on asz.ArticleId=a.Id left join po p on p.ArticleId=a.Id inner join category cat on cat.Id=a.CategoryId where a.ArticleOpenFlag=0 group by a.Id HAVING TotalPieces > 0 Union SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, '-', mxn.NoPacks as TotalPieces, '-', '-', '-', '-',c.Title FROM `mixnopacks` mxn inner join article a on a.Id=mxn.ArticleId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where a.ArticleOpenFlag=1 HAVING TotalPieces > 0) as dd) as ddd");
		$vnTotal = $vnddataTotal[0]->Total;
		$length = $data["length"];
		
		if($search['value'] != null && strlen($search['value']) > 2){
			//$searchstring = "and d.GRN_Number like '%".$search['value']."%' OR d.Name like '%".$search['value']."%'";
			$searchstring = "where ddd.PurchaseNumber like '%".$search['value']."%' OR ddd.ArticleNumber like '%".$search['value']."%' OR ddd.Name like '%".$search['value']."%' OR ddd.Title like '%".$search['value']."%'";
			$vnddataTotalFilter = DB::select("select count(*) as Total from (select dd.*, CountNoPacks(dd.ArticleRatio) as TotalArticleRatio  from (SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalPieces, GROUP_CONCAT(DISTINCT CONCAT(ac.ArticleColorName) ORDER BY ac.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleRatio, cat.Colorflag, cat.Title FROM `inward` inw inner join article a on a.Id=inw.ArticleId inner join articlecolor ac on ac.ArticleId=a.Id inner join articlesize asz on asz.ArticleId=a.Id left join po p on p.ArticleId=a.Id inner join category cat on cat.Id=a.CategoryId where a.ArticleOpenFlag=0 group by a.Id HAVING TotalPieces > 0 Union SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, '-', mxn.NoPacks as TotalPieces, '-', '-', '-', '-',c.Title FROM `mixnopacks` mxn inner join article a on a.Id=mxn.ArticleId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where a.ArticleOpenFlag=1 HAVING TotalPieces > 0) as dd) as ddd ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "";
			$vnddataTotalFilterValue = $vnTotal;
		}
		
		//Filter Orderby value code
		$column = $data["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "ddd.ArticleNumber";
				break;	
			case 2:
				$ordercolumn = "ddd.Title";
				break;	
			default:
				$ordercolumn = "ddd.ArticleNumber";
				break;
		}
		
		$order = "";	
		if($data["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data["order"][0]["dir"];
		}
		//end
		
		$vnddata = DB::select("select ddd.* from (select dd.*, CountNoPacks(dd.ArticleRatio) as TotalArticleRatio  from (SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalPieces, GROUP_CONCAT(DISTINCT CONCAT(ac.ArticleColorName) ORDER BY ac.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleRatio, cat.Colorflag, cat.Title FROM `inward` inw inner join article a on a.Id=inw.ArticleId inner join articlecolor ac on ac.ArticleId=a.Id inner join articlesize asz on asz.ArticleId=a.Id left join po p on p.ArticleId=a.Id inner join category cat on cat.Id=a.CategoryId where a.ArticleOpenFlag=0 group by a.Id HAVING TotalPieces > 0 Union SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, '-', mxn.NoPacks as TotalPieces, '-', '-', '-', '-',c.Title FROM `mixnopacks` mxn inner join article a on a.Id=mxn.ArticleId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where a.ArticleOpenFlag=1 HAVING TotalPieces > 0) as dd) as ddd ".$searchstring." ".$order." limit ".$data["start"].",".$length);

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
	
	 
	public function exportallstocks()
    {
		return Excel::download(new ReportAllStocksExport(), 'Export-All-Stocks.xls');
    }
	
	public function GetPolist()
    {
		return DB::select('SELECT p.Id, pn.Id as POId, pn.PurchaseNumber, concat(fn.StartYear,\'-\',fn.EndYear) as FinancialYear, v.Name, c.Title, p.ArticleId, p.NumPacks, ar.ArticleNumber, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number left join inward inw on inw.ArticleId = p.ArticleId inner join financialyear fn on fn.Id=pn.FinancialYearId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id');
		
		
    }
	
	public function PostPoReport(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
		$vnddataTotal = DB::select("SELECT count(*) as Total from (select p.Id From po p left join purchasenumber pn on pn.Id=p.PO_Number left join  inward inw on inw.ArticleId = p.ArticleId group by pn.Id) as f");
		//$vnddataTotal = DB::select("SELECT count(*) as Total From po p left join purchasenumber pn on pn.Id=p.PO_Number left join  inward inw on inw.ArticleId = p.ArticleId");
		$vnTotal = $vnddataTotal[0]->Total;
		$length = $data["length"];
		
		if($search['value'] != null && strlen($search['value']) > 2){
			$searchstring = "where ddd.PurchaseNumber like '%".$search['value']."%' OR cast(ddd.PoDate as char) like '%".$search['value']."%' OR ddd.ArticleNumber like '%".$search['value']."%' OR ddd.Title like '%".$search['value']."%' OR ddd.Name like '%".$search['value']."%' OR ddd.Title like '%".$search['value']."%' OR ddd.TotalPieces like '%".$search['value']."%'";
			//return "select count(*) as Total from (SELECT p.Id, pn.Id as POId, p.PoDate, concat(pn.PurchaseNumber,'/', fn.StartYear,\"-\",fn.EndYear) as PurchaseNumber,v.Name, c.Title, ar.ArticleNumber, inw.ArticleId as InwardArticleId From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where InwardArticleId IS NULL ".$searchstring;
			$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT p.Id, pn.Id as POId, p.PoDate as pdate, DATE_FORMAT(p.PoDate, '%d/%m/%Y') as PoDate, GROUP_CONCAT(DISTINCT CONCAT(ar.ArticleNumber) ORDER BY ar.Id SEPARATOR ',') as ArticleNumber, concat(pn.PurchaseNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber, v.Name, c.Title, p.PO_Number, GetTotalPOPieces(p.PO_Number) as TotalPieces, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd ".$searchstring);
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
		if($data["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data["order"][0]["dir"];
		}
		//end
		
		$vnddata = DB::select("select ddd.* from (SELECT p.Id, pn.Id as POId, p.PoDate as pdate, DATE_FORMAT(p.PoDate, '%d/%m/%Y') as PoDate, GROUP_CONCAT(DISTINCT CONCAT(ar.ArticleNumber) ORDER BY ar.Id SEPARATOR ',') as ArticleNumber, concat(pn.PurchaseNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber, v.Name, c.Title, p.PO_Number, GetTotalPOPieces(p.PO_Number) as TotalPieces, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		//return "select ddd.* from (SELECT p.Id, pn.Id as POId, DATE_FORMAT(p.PoDate, \"%d/%m/%Y\") as PoDate, concat(pn.PurchaseNumber,'/', fn.StartYear,\"-\",fn.EndYear) as PurchaseNumber, v.Name, c.Title, p.ArticleId, p.NumPacks, ar.ArticleNumber, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where ddd.InwardArticleId IS NULL ".$searchstring." ".$order." limit ".$data["start"].",".$length;
		
		
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
	
	public function GetInwardList()
	{
		return DB::select("SELECT VendorName(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as Name , GetTotalInwardPieces(igrn.GRN) as TotalInwardPieces, SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, inw.GRN,igrn.GRN as GRN_Number,igrn.InwardDate,GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY inw.Id SEPARATOR ',') as ArticleNumber, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join article a on a.Id=inw.ArticleId left join po p on p.ArticleId=a.Id left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId group by inw.GRN  order by igrn.Id DESC");
		
		//select dd.* from (select VendorName(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as Name ,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, InwardSOCheck(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SOID, SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck,inw.GRN, concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number, igrn.InwardDate from `inward` inw left join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId group by GRN order by GRN asc) as dd where dd.SOID=0 order by GRN DESC
	}
	
	 public function PostReportInwardList(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
		//$vnddataTotal = DB::select("select count(*) as Total from (select VendorName(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as Name ,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, InwardSOCheck(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SOID, SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck,inw.GRN, concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number, igrn.InwardDate from `inward` inw left join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId group by GRN order by GRN asc) as dd order by dd.GRN DESC");
		$vnddataTotal = DB::select("select count(*) as Total from (select inw.GRN FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId left join po p on p.ArticleId=a.Id left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId group by GRN) as f");
		$vnTotal = $vnddataTotal[0]->Total;
		$length = $data["length"];
		
		if($search['value'] != null && strlen($search['value']) > 2){
			//$searchstring = "and d.GRN_Number like '%".$search['value']."%' OR d.Name like '%".$search['value']."%'";
			//$searchstring = " where dd.GRN_Number like '%".$search['value']."%' OR dd.Name like '%".$search['value']."%'";
			$searchstring = "where dd.GRN_Number like '%".$search['value']."%' OR cast(dd.InwardDate as char) like '%".$search['value']."%' OR  dd.Name like '%".$search['value']."%' OR  dd.TotalInwardPieces like '%".$search['value']."%' OR dd.Title like '%".$search['value']."%' OR dd.PurchaseNumber like '%".$search['value']."%' OR dd.ArticleNo like '%".$search['value']."%'";
			$vnddataTotalFilter = DB::select("select count(*) as Total  from (select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, VendorName(a.Id) as Name, GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber, DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate, c.Title FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId left join po p on p.ArticleId=a.Id left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=a.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number left join financialyear fy1 on fy1.Id=pn.FinancialYearId  group by GRN order by GRN asc) as dd ".$searchstring." order by dd.GRN DESC");
			
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "";
			$vnddataTotalFilterValue = $vnTotal;
		}
		
		//Filter Orderby value code
		$column = $data["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "dd.GRN";
				break;	
			case 2:
				$ordercolumn = "dd.Name";
				break;	
			case 3:
				$ordercolumn = "dd.Title";
				break;
			case 5:
				$ordercolumn = "date(dd.inwdate)";
				break;
			case 6:
				$ordercolumn = "dd.PurchaseNumber";
				break;
			default:
				$ordercolumn = "dd.GRN";
				break;
		}
		
		$order = "";	
		if($data["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data["order"][0]["dir"];
		}
		//end
		
		//$vnddata = DB::select("select dd.* from (select inwc.Notes,'Cancellation',VendorName(a.Id) as Name, GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inwcl.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate, 'SODataCheck', c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM inwardgrn igrn inner join `inwardcancellationlogs` inwcl on igrn.Id=inwcl.GRN inner join `inwardcancellation` inwc on igrn.Id=inwc.GRN  inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inwcl.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId  inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId group by GRN UNION ALL select '',0,VendorName(a.Id) as Name,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate,SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN   inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId  left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId  where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) group by GRN) as dd  ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		$vnddata = DB::select("select dd.* from (select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, inwc.Notes,'Cancellation',VendorName(a.Id) as Name, igrn.Id, GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inwcl.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate, 'SODataCheck', c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM inwardgrn igrn inner join `inwardcancellationlogs` inwcl on igrn.Id=inwcl.GRN inner join `inwardcancellation` inwc on igrn.Id=inwc.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inwcl.ArticleId left join po p on p.ArticleId=a.Id left join vendor v on v.Id=p.VendorId left join category c on c.Id=a.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number left join financialyear fy1 on fy1.Id=pn.FinancialYearId group by inwc.GRN UNION ALL select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, '',0,VendorName(a.Id) as Name,igrn.Id,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate,SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN   inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId left join po p on p.ArticleId=a.Id left join vendor v on v.Id=p.VendorId  left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=a.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId  where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) group by inw.GRN) as dd ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		
		//$vnddata = DB::select("select dd.* from (select GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',') as SODataCheck11,VendorName(a.Id) as Name,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate,SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN   inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId  left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId group by GRN order by GRN asc) as dd ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		//"select * from (select inwc.Notes,'Cancellation',VendorName(a.Id) as Name, GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inwcl.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate, 'SODataCheck', c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM inwardgrn igrn inner join `inwardcancellationlogs` inwcl on igrn.Id=inwcl.GRN inner join `inwardcancellation` inwc on igrn.Id=inwc.GRN  inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inwcl.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId  inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId group by GRN UNION ALL select '',0,VendorName(a.Id) as Name,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d/%m/%Y') as InwardDate,SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN   inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId inner join po p on p.ArticleId=a.Id inner join vendor v on v.Id=p.VendorId  left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=p.CategoryId inner join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId  where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) group by GRN) as f ORDER BY `f`.`GRN`  DESC"
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
	
	public function GetSolist($UserId)
    {
		/* $userrole = DB::select("SELECT Role FROM `users`where Id='".$UserId."'");
		if($userrole[0]->Role==2){
			$wherecustom ="";
		}else{
			$wherecustom ="where son.UserId='".$UserId."'";
		} */
		return DB::select("SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join users u on u.Id=son.UserId inner join financialyear fn on fn.Id=son.FinancialYearId group by s.SoNumberId");
		
		//select * from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId ".$wherecustom." group by s.SoNumberId) as ddd where ddd.OWID=0 order by ddd.Id desc
    }
	
	public function PostReportSolist(Request $request)
	{
		
		
		$data = $request->all();	
		$search = $data['dataTablesParameters']["search"];
		$UserId = $data['UserID'];
		$startnumber = $data['dataTablesParameters']["start"];
		
		$vnddataTotal = DB::select("select count(*) as Total from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join users u on u.Id=son.UserId inner join financialyear fn on fn.Id=son.FinancialYearId group by s.SoNumberId) as d");
		$vntotal=$vnddataTotal[0]->Total;		
		$length = $data['dataTablesParameters']["length"];
		
		
		$userrole = DB::select("SELECT Role FROM `users`where Id='".$UserId."'");
		if($userrole[0]->Role==2){
			$wherecustom ="";
			if($search['value'] != null && strlen($search['value']) > 2){
				//$searchstring = "and oln.OutletNumber like '%".$search['value']."%'";
				$searchstring = " where d.SoNumber like '%".$search['value']."%' OR d.Name like '%".$search['value']."%' OR CAST(d.TotalSoPieces as SIGNED INTEGER) like '%".$search['value']."%'   OR cast(d.cdate as char) like '%".$search['value']."%' OR d.ArticleNumber like '%".$search['value']."%'";
				
				$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate,DATE_FORMAT(son.SoDate, \"%d/%m/%Y\") as cdate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join users u on u.Id=son.UserId inner join financialyear fn on fn.Id=son.FinancialYearId group by s.SoNumberId) as d ".$searchstring);
				$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
			}else{
				$searchstring = "";
				$vnddataTotalFilterValue = $vntotal;
			}
			
		}else{
			$wherecustom ="where d.UserId='".$UserId."'";
			if($search['value'] != null && strlen($search['value']) > 2){
				$searchstring = " where d.SoNumber like '%".$search['value']."%' OR d.Name like '%".$search['value']."%' OR CAST(d.TotalSoPieces as SIGNED INTEGER) like '%".$search['value']."%'   OR cast(d.cdate as char) like '%".$search['value']."%' OR d.ArticleNumber like '%".$search['value']."%'";
				$vnddataTotalFilter = DB::select("SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate,DATE_FORMAT(son.SoDate, \"%d/%m/%Y\") as cdate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId ".$wherecustom." group by s.SoNumberId) as d ".$searchstring);
				$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
			}else{
				$searchstring = "";
				$vnddataTotalFilterValue = $vntotal;
			}
		}
		//end
		$column = $data['dataTablesParameters']["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "d.SoNumber";
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
				$ordercolumn = "d.SoDate";
				break;
		}
		
		$order = "";
		if($data['dataTablesParameters']["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data['dataTablesParameters']["order"][0]["dir"];
		}
		
	
		
		$vnddata = DB::select("select d.* from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate,DATE_FORMAT(son.SoDate, \"%d/%m/%Y\") as cdate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId group by s.SoNumberId) as d ".$wherecustom." ".$searchstring." ".$order." limit ".$data['dataTablesParameters']["start"].",".$length);
		
	
		return array(
				'datadraw'=>$data['dataTablesParameters']["draw"],
				'recordsTotal'=>$vntotal,
				'recordsFiltered'=>$vnddataTotalFilterValue,
				'response' => 'success',
				'startnumber' => $startnumber,
				'search'=>count($vnddata),
				'data' => $vnddata,
			);
		
	}
	
	public function GetOutletStocks($PartyId)
    {
		//$data = DB::select("SELECT o.ArticleId, a.ArticleNumber, GROUP_CONCAT(DISTINCT CONCAT(arc.ArticleColorName) ORDER BY arc.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleOpenFlag, own.SoId, son.PartyId, c.Title, GetOutletSingleArticle(son.PartyId,o.ArticleId) as STOCKS, CountNoPacks(GetOutletSingleArticle(son.PartyId,o.ArticleId)) as COUNTVL FROM `outward` o inner join article a on a.Id=o.ArticleId inner join outwardnumber own on own.OutwardNumber=o.OutwardNumberId inner join sonumber son on son.Id=own.SoId inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId inner join articlecolor arc on arc.ArticleId=a.Id inner join articlesize asz on asz.ArticleId=a.Id where son.PartyId='".$PartyId."' group by o.ArticleId having COUNTVL>0");
		//$data = DB::select("SELECT c.Colorflag, (OutwardPieces(o.ArticleId, '".$PartyId."') - OutletPieces(o.ArticleId, '".$PartyId."')  -  SalesReturnPieces(o.ArticleId, '".$PartyId."')) as TotalPieces,o.ArticleId, a.ArticleRatio,  CountNoPacks(a.ArticleRatio) as TotalArticleRatio, a.ArticleNumber, GROUP_CONCAT(DISTINCT CONCAT(arc.ArticleColorName) ORDER BY arc.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleOpenFlag, own.SoId, son.PartyId, c.Title, GetOutletSingleArticle(son.PartyId,o.ArticleId) as STOCKS, CountNoPacks(GetOutletSingleArticle(son.PartyId,o.ArticleId)) as COUNTVL FROM `outward` o inner join article a on a.Id=o.ArticleId inner join outwardnumber own on own.OutwardNumber=o.OutwardNumberId inner join sonumber son on son.Id=own.SoId inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId left join articlecolor arc on arc.ArticleId=a.Id left join articlesize asz on asz.ArticleId=a.Id where son.PartyId='".$PartyId."' group by o.ArticleId having COUNTVL>0");
		//$data = DB::select("SELECT c.Colorflag, (OutwardPieces(o.ArticleId, '".$PartyId."') - OutletPieces(o.ArticleId, '".$PartyId."')  -  SalesReturnPieces(o.ArticleId, '".$PartyId."')) as TotalPieces,o.ArticleId, a.ArticleRatio,  CountNoPacks(a.ArticleRatio) as TotalArticleRatio, a.ArticleNumber, GROUP_CONCAT(DISTINCT CONCAT(arc.ArticleColorName) ORDER BY arc.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleOpenFlag, own.SoId, son.PartyId, c.Title, GetOutletSingleArticle(son.PartyId,o.ArticleId) as STOCKS, CountNoPacks(GetOutletSingleArticle(son.PartyId,o.ArticleId)) as COUNTVL FROM `outward` o inner join article a on a.Id=o.ArticleId inner join outwardnumber own on own.OutwardNumber=o.OutwardNumberId inner join sonumber son on son.Id=own.SoId inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId left join articlecolor arc on arc.ArticleId=a.Id left join articlesize asz on asz.ArticleId=a.Id where son.PartyId='".$PartyId."' group by o.ArticleId having TotalPieces>0");
		
		
		//$data = DB::select("SELECT c.Colorflag, (OutwardPieces(o.ArticleId, '".$PartyId."') - OutletPieces(o.ArticleId, '".$PartyId."')  -  SalesReturnPieces(o.ArticleId, '".$PartyId."')) as TotalPieces,o.ArticleId, a.ArticleRatio,  CountNoPacks(a.ArticleRatio) as TotalArticleRatio, a.ArticleNumber, GROUP_CONCAT(DISTINCT CONCAT(arc.ArticleColorName) ORDER BY arc.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleOpenFlag, own.SoId, son.PartyId, c.Title, GetOutletSingleArticle(son.PartyId,o.ArticleId) as STOCKS, CountNoPacks(GetOutletSingleArticle(son.PartyId,o.ArticleId)) as COUNTVL FROM `outward` o inner join article a on a.Id=o.ArticleId inner join outwardnumber own on own.OutwardNumber=o.OutwardNumberId inner join sonumber son on son.Id=own.SoId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId left join articlecolor arc on arc.ArticleId=a.Id left join articlesize asz on asz.ArticleId=a.Id where son.PartyId='".$PartyId."' group by o.ArticleId having TotalPieces>0");
		//$data = DB::select("SELECT c.Colorflag, (OutwardPieces(o.ArticleId, '".$PartyId."') - OutletPieces(o.ArticleId, '".$PartyId."')  -  SalesReturnPieces(o.ArticleId, '".$PartyId."')) as TotalPieces,o.ArticleId, a.ArticleRatio,  CountNoPacks(a.ArticleRatio) as TotalArticleRatio, a.ArticleNumber, GROUP_CONCAT(DISTINCT CONCAT(arc.ArticleColorName) ORDER BY arc.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleOpenFlag, own.SoId, son.PartyId, c.Title, GetOutletSingleArticle(topp.PartyId,o.ArticleId) as STOCKS, CountNoPacks(GetOutletSingleArticle(topp.PartyId,o.ArticleId)) as COUNTVL FROM `outward` o inner join article a on a.Id=o.ArticleId inner join outwardnumber own on own.OutwardNumber=o.OutwardNumberId inner join sonumber son on son.Id=own.SoId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId left join articlecolor arc on arc.ArticleId=a.Id left join articlesize asz on asz.ArticleId=a.Id left join transportoutwardpacks topp on topp.ArticleId=a.Id  where topp.PartyId='".$PartyId."' group by o.ArticleId having TotalPieces>0");
		//$data = DB::select("SELECT c.Colorflag, o.ArticleId, a.ArticleRatio,  CountNoPacks(a.ArticleRatio) as TotalArticleRatio, a.ArticleNumber, GROUP_CONCAT(DISTINCT CONCAT(arc.ArticleColorName) ORDER BY arc.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleOpenFlag, own.SoId, son.PartyId, c.Title, GetOutletSingleArticle(topp.PartyId,o.ArticleId) as STOCKS, CountNoPacks(GetOutletSingleArticle(topp.PartyId,o.ArticleId)) as TotalPieces FROM `outward` o inner join article a on a.Id=o.ArticleId inner join outwardnumber own on own.OutwardNumber=o.OutwardNumberId inner join sonumber son on son.Id=own.SoId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId left join articlecolor arc on arc.ArticleId=a.Id left join articlesize asz on asz.ArticleId=a.Id left join transportoutwardpacks topp on topp.ArticleId=a.Id  where topp.PartyId='".$PartyId."' group by o.ArticleId having TotalPieces>0");
		//$data = DB::select("select f.*,CountNoPacks(f.STOCKS) as TotalPieces  from (SELECT c.Colorflag, o.ArticleId, a.ArticleRatio,  CountNoPacks(a.ArticleRatio) as TotalArticleRatio, a.ArticleNumber, GROUP_CONCAT(DISTINCT CONCAT(arc.ArticleColorName) ORDER BY arc.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleOpenFlag, own.SoId, son.PartyId, c.Title, GetOutletSingleArticle(topp.PartyId,o.ArticleId) as STOCKS FROM `outward` o inner join article a on a.Id=o.ArticleId inner join outwardnumber own on own.OutwardNumber=o.OutwardNumberId inner join sonumber son on son.Id=own.SoId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId left join articlecolor arc on arc.ArticleId=a.Id left join articlesize asz on asz.ArticleId=a.Id left join transportoutwardpacks topp on topp.ArticleId=a.Id  where topp.PartyId='".$PartyId."' group by o.ArticleId) as f having TotalPieces>0");
		
		
		//$data = DB::select("select f.*,CountNoPacks(f.STOCKS) as TotalPieces  from (SELECT c.Colorflag, topp.ArticleId, a.ArticleRatio,  CountNoPacks(a.ArticleRatio) as TotalArticleRatio, a.ArticleNumber, GROUP_CONCAT(DISTINCT CONCAT(arc.ArticleColorName) ORDER BY arc.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleOpenFlag, own.SoId, son.PartyId, c.Title, GetOutletSingleArticle(topp.PartyId,a.Id) as STOCKS FROM transportoutwardpacks topp inner join article a on a.Id=topp.ArticleId  left join `outward` o on o.ArticleId = topp.ArticleId left join outwardnumber own on own.OutwardNumber=o.OutwardNumberId left join sonumber son on son.Id=own.SoId inner join category c on c.Id=a.CategoryId left join articlecolor arc on arc.ArticleId=a.Id left join articlesize asz on asz.ArticleId=a.Id  where topp.PartyId='".$PartyId."' group by topp.ArticleId) as f having TotalPieces>0");
		$data = DB::select("select f.*,CountNoPacks(f.STOCKS) as TotalPieces  from (SELECT c.Colorflag, topp.ArticleId, a.ArticleRatio,  CountNoPacks(a.ArticleRatio) as TotalArticleRatio, a.ArticleNumber, GROUP_CONCAT(DISTINCT CONCAT(arc.ArticleColorName) ORDER BY arc.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleOpenFlag, own.SoId, son.PartyId, c.Title, GetOutletSingleArticle(topp.PartyId,a.Id) as STOCKS, bn.Name as BrandName, subc.Name as Subcategory, rs.SeriesName, rs.Series, a.StyleDescription FROM transportoutwardpacks topp inner join article a on a.Id=topp.ArticleId  left join `outward` o on o.ArticleId = topp.ArticleId left join outwardnumber own on own.OutwardNumber=o.OutwardNumberId left join sonumber son on son.Id=own.SoId inner join category c on c.Id=a.CategoryId left join articlecolor arc on arc.ArticleId=a.Id left join articlesize asz on asz.ArticleId=a.Id left join brand bn on bn.Id= a.BrandId left join subcategory subc on subc.Id=a.SubCategoryId left join rangeseries rs on rs.Id=a.SeriesId where topp.PartyId='".$PartyId."' group by topp.ArticleId) as f having TotalPieces>0");
		//return array("data"=>$data);
		
		foreach($data as $vl){
			$object = (object)$vl;
			
			$TotalPieces = $vl->TotalPieces;
			$TotalArticleRatio = $vl->TotalArticleRatio;
			
			$NoPacks = $vl->STOCKS;
			$Colorflag = $vl->Colorflag;
			$getcolorcount= count(explode(",",$vl->ArticleColor));
			
			if( strpos($NoPacks, ',') !== false ) {
				$TotalPieces= array_sum(explode(",",$NoPacks));
				$singlecountNoPacks = $TotalPieces;
				$string = explode(',', $NoPacks);
				$stringcomma = 1;
			}else{
				$singlecountNoPacks = $NoPacks;
				$string = $NoPacks;
				$stringcomma = 0;
			}
			
			//Logic to get stock
			if($vl->ArticleOpenFlag==0){
				if($Colorflag==0){
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
				$Totalstockvalue = $TotalPieces;
			}
			
			/* //Logic to get stock
			if($vl->ArticleOpenFlag==0){
				$Tempnumber = ($TotalPieces/$TotalArticleRatio);
				$checkFloarvalue = is_float($Tempnumber);
			
				if((int)$Tempnumber != $Tempnumber){
					$TempSet = floor($Tempnumber);
					$Totaltmp = $TempSet * $TotalArticleRatio;
					$TotalValue = $TotalPieces - $Totaltmp;
					$Totalstockvalue= $TempSet."-".$TotalValue;
				}else{
					$Totalstockvalue = $Tempnumber;
				}
			}else{
				$Totalstockvalue = $TotalPieces;
			} */
			$object->TotalNoPacks = $Totalstockvalue;
		}
		
		return array("data"=>$data);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//For testing - start
	public function teststockopenflag(){
		$colorflag = DB::select("SELECT a.Id, a.ArticleNumber, mn.NoPacks as SalesNoPacks FROM mixnopacks mn left join `article` a on mn.ArticleId=a.Id where a.ArticleOpenFlag='1'");
		if($colorflag){
			$article_exist = 1;
			$UserId = 53;
			foreach($colorflag as $vl){
				echo $this->articlesearch($UserId, 0, $vl->ArticleNumber, $vl->SalesNoPacks);
			}
		}	
	}
	
	//test stock
	public function teststock(){
		$data = DB::select("SELECT c.Colorflag, a.Id, a.ArticleNumber, a.ArticleColor, i.SalesNoPacks FROM `article` a inner join category c on c.Id=a.CategoryId  inner join inward i on i.ArticleId=a.Id where a.articleStatus = 1 and a.ArticleOpenFlag = 0 group by i.ArticleId");
		//$data = DB::select("SELECT c.Colorflag, a.Id, a.ArticleNumber, a.ArticleColor, i.SalesNoPacks FROM `article` a inner join category c on c.Id=a.CategoryId  left join inward i on i.ArticleId=a.Id where a.articleStatus = 1 and a.ArticleOpenFlag = 0 group by a.Id limit 500,500");
		//$data = DB::select("SELECT c.Colorflag, a.Id, a.ArticleNumber, a.ArticleColor FROM `article` a inner join category c on c.Id=a.CategoryId where a.articleStatus = 1 and a.ArticleOpenFlag = 0 limit 500,500");
		//$data = DB::select("SELECT c.Colorflag, a.Id, a.ArticleNumber, a.ArticleColor FROM `article` a inner join category c on c.Id=a.CategoryId where a.articleStatus = 1 and a.ArticleOpenFlag = 0 and a.ArticleNumber='650203521'");
		foreach($data as $vl){
			//echo $vl->Colorflag." - ".$vl->Id." - ".$vl->ArticleNumber." - ".$vl->ArticleColor;
			//echo "<br />";
			
			$getcolor = json_decode($vl->ArticleColor);
			$ArticleName = $vl->ArticleNumber;
			$SalesNoPacks = $vl->SalesNoPacks;
			$Colorflag = $vl->Colorflag;
			$UserId = 53;
			foreach($getcolor as $key1 => $vl){
				
				$ColorId = $vl->Id;
				
				
				if($Colorflag==1){
					if( strpos($SalesNoPacks, ',') !== true ) {
						$NoPacks = explode(",",$SalesNoPacks);
						$np = $NoPacks[$key1];
					} else{
						$np = $SalesNoPacks;
					}
				} else{
					$np = $SalesNoPacks;
				}
				
				echo $this->articlesearch($UserId, $ColorId, $ArticleName, $np);
				//echo "<br />";
			}
			//echo "<br />";
		}
	}
	
	public function articlesearch($UserId, $ColorId, $ArticleName, $SalesNoPacks){
		/* $ArticleName = $request->ArticleName;
		$ColorId = $request->ColorId;
		$UserId = $request->UserId; */
		
		$getuserrole = DB::select("select Role from users where Id=".$UserId);
		//print_r($getuserrole); exit;
		$UserRoleFlag = 1;
		
		//return $userrole);
		$UserRole = $getuserrole[0]->Role;
		
		if($UserRole==3 || $UserRole==5 || $UserRole==6 || $UserRole==7)
		{
			$UserRoleFlag = 0;
		}else{
			$UserRoleFlag = 1;
		}
		//exit;
		
		$inwarddata ="";
		$ColorNoPacks = "";
		$ArticleSizeSet = "";
		$ArticleColorSet = "";
		$Colorflag = 0;
		$historyofsale =[];
		$articleRejected="";
		$articleCancelled = "";
		//$salesorderpending = "";
		
		$history_newarray = [];
		$total_stock = 0;
		$totaloutwardquantity = 0;
		
		$inward_exist = 0;
		$sales_exist = 0;
		$articlerej_exist = 0;
		$articlecan_exist = 0;
		
		$grandtotalinwardquantity = 0;
					$grandtotalinwardaveragerate = 0;
					$grandtotaloutwardquantity = 0;
					$grandtotaloutwardaveragerate = 0;
					$grandtotaloutwardvalue = 0;
		
		//return $ArticleName;
		//"SELECT concat(p.PO_Number,'/' ,f.StartYear,'-',f.EndYear) as PurchaseOrderNumber, b.Name as BrandName, v.Name as VendorName, c.Title, c.Colorflag, c.ArticleOpenFlag, pn.PoDate, p.NumPacks, p.PO_Image, i.NoPacks, i.GRN, ig.GRN, ig.FinancialYearId, a.* FROM `article` a inner join po p on p.ArticleId=a.Id inner join purchasenumber pn on pn.PurchaseNumber=p.PO_Number inner join financialyear f on f.Id = pn.FinancialYearId inner join vendor v on v.Id=p.VendorId inner join category c on c.Id=p.CategoryId left join brand b on b.Id=p.BrandId left join inward i on i.ArticleId=a.Id left join inwardgrn ig on ig.Id=i.GRN where a.ArticleNumber = '15212'";
		$Podata = DB::select("SELECT concat(p.PO_Number,'/' ,f.StartYear,'-',f.EndYear) as PurchaseOrderNumber, b.Name as BrandName, v.Name as VendorName, c.Title, c.Colorflag, c.ArticleOpenFlag, DATE_FORMAT(pn.PoDate, '%d/%m/%Y') as PoDate, pn.Id as PNID, p.NumPacks as PO_Peace, p.PO_Image, a.Id, a.ArticleStatus FROM `article` a left join po p on p.ArticleId=a.Id left join purchasenumber pn on pn.Id=p.PO_Number left join financialyear f on f.Id = pn.FinancialYearId left join vendor v on v.Id=p.VendorId inner join category c on c.Id=a.CategoryId left join brand b on b.Id=a.BrandId where a.ArticleNumber = '".$ArticleName."'");
		
		if($Podata){
			$article_exist = 1;
			$articleRejected = DB::select("SELECT DATE_FORMAT(UpdatedDate, '%d/%m/%Y') as RejectDate, ArticleColor, ArticleSize, ArticleRatio, ArticleRate, StyleDescription, Remarks as Reason  FROM `rejectionarticle` where ArticleNumber = '".$ArticleName."'");
			$articleCancelled = DB::select("SELECT DATE_FORMAT(ic.CreatedDate, '%d/%m/%Y') as CancelledDate, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, icl.GRN as Id, icl.NoPacks, icl.InwardDate, ic.Notes, ic.GRN FROM `inwardcancellationlogs` icl inner join article a on a.Id=icl.ArticleId inner join inwardcancellation ic on ic.GRN=icl.GRN where ArticleId = '".$Podata[0]->Id."'");

			$inwardcheck = DB::select("SELECT count(*) as Total FROM `inward` i inner join article a on a.Id=i.ArticleId where a.ArticleNumber= '".$ArticleName."'");
				
			if($inwardcheck[0]->Total>0){
						//return $historyofsale;
				$inwarddata = DB::select("SELECT a.Id as ArticlelId, i.Id as InwardId, ig.Id as InwardgrnId, concat(ig.GRN,'/' ,f.StartYear,'-',f.EndYear) as Grnorder, DATE_FORMAT(i.InwardDate, '%d/%m/%Y') as InwardDate, v.Name as VendorName, i.NoPacks, i.SalesNoPacks, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.StyleDescription,a.ArticleStatus, a.OpeningStock, ar.ArticleRate FROM `article` a inner join inward i on i.ArticleId=a.Id inner join inwardgrn ig on ig.Id=i.GRN inner join financialyear f on f.Id=ig.FinancialYearId left join po p on p.ArticleId=a.Id left join vendor v on v.Id=p.VendorId left join articlerate ar on ar.ArticleId=a.Id where a.ArticleNumber='".$ArticleName."' group by i.Id");
						//return $inwarddata;
				if($Podata[0]->ArticleOpenFlag==0){
					$ArticleColor = json_decode($inwarddata[0]->ArticleColor, true);
					$ArticleSize = json_decode($inwarddata[0]->ArticleSize, true);
					//$NoPacks = explode(",",$inwarddata[0]->NoPacks);
					
					/* foreach($ArticleColor as $key => $vl){
						$ArticleColorSet .= $vl["Name"].",";
						if($ColorId == $vl["Id"]){
							$ColorNoPacks .= $NoPacks[$key];
						}
					} */
				
					if($Podata[0]->Colorflag==1){
						//return $inwarddata; exit;
						foreach($inwarddata as $key => $vl){
							$OpeningStockP = $vl->OpeningStock;
							if( strpos($vl->NoPacks, ',') !== true ) {
								$NoPacks = explode(",",$inwarddata[$key]->NoPacks);
								foreach($ArticleColor as $key1 => $vl1){
									$ArticleColorSet .= $vl1["Name"].",";
									if($ColorId == $vl1["Id"]){
										$ColorNoPacks .= $NoPacks[$key1];
										$NoPacks = $NoPacks[$key1]; 
									}
								}
							} else{
								$ColorNoPacks .= $vl->NoPacks;
								$NoPacks = $vl->NoPacks;
							}
							
							if($OpeningStockP==1){
								//$ColorNoPacks =$vl->NoPacks;
								$historyofsale[] = array("date"=>$vl->InwardDate, "particulars"=>array("status"=>0,"partyname"=>$vl->VendorName, "type"=>"Opening Stock"), "ordertype"=>"Opening Stock", "orderno"=>$vl->Grnorder, "challanno"=>$vl->InwardgrnId, "quantity"=>$NoPacks, "rate"=>$vl->ArticleRate,"amount"=>($NoPacks * $vl->ArticleRate),"closingquantity"=>$ColorNoPacks);
							}else{
								//return $vl->NoPacks; exit;
								//return $vl; exit;
								$historyofsale[] = array("date"=>$vl->InwardDate, "particulars"=>array("status"=>0,"partyname"=>$vl->VendorName, "type"=>"V222"), "ordertype"=>"Purchase", "orderno"=>$vl->Grnorder, "challanno"=>$vl->InwardgrnId, "quantity"=>$NoPacks, "rate"=>$vl->ArticleRate,"amount"=>($NoPacks * $vl->ArticleRate),"closingquantity"=>$ColorNoPacks);
							}	
						}
						/* if($inwarddata[0]->OpeningStock==1){
							$historyofsale[] = array("date"=>$inwarddata[0]->InwardDate, "particulars"=>array("status"=>0,"partyname"=>$inwarddata[0]->VendorName, "type"=>"Opening Stock"), "ordertype"=>"Opening Stock", "orderno"=>$inwarddata[0]->Grnorder, "challanno"=>$inwarddata[0]->InwardgrnId, "quantity"=>$ColorNoPacks, "rate"=>$inwarddata[0]->ArticleRate,"amount"=>($ColorNoPacks * $inwarddata[0]->ArticleRate),"closingquantity"=>$ColorNoPacks);
						} else{
							$historyofsale[] = array("date"=>$inwarddata[0]->InwardDate, "particulars"=>array("status"=>0,"partyname"=>$inwarddata[0]->VendorName, "type"=>"V"), "ordertype"=>"Purchase", "orderno"=>$inwarddata[0]->Grnorder, "challanno"=>$inwarddata[0]->InwardgrnId, "quantity"=>$ColorNoPacks, "rate"=>$inwarddata[0]->ArticleRate,"amount"=>($ColorNoPacks * $inwarddata[0]->ArticleRate),"closingquantity"=>$ColorNoPacks);
						} */
						
					} else{
						
						foreach($inwarddata as $key => $vl){
							if( strpos($vl->NoPacks, ',') !== true ) {
								$NoPacks = explode(",",$inwarddata[$key]->NoPacks);
								foreach($ArticleColor as $key1 => $vl1){
									$ArticleColorSet .= $vl1["Name"].",";
									if($ColorId == $vl1["Id"]){
										$ColorNoPacks .= $NoPacks[$key1];
										$NoPacks = $NoPacks[$key1]; 
									}
								}
							}else{
								$ColorNoPacks .= $vl->NoPacks;
								$NoPacks = $vl->NoPacks;
							}
							
							
							if($vl->OpeningStock==1){
								//$ColorNoPacks .=$vl->NoPacks;
								$historyofsale[] = array("date"=>$vl->InwardDate, "particulars"=>array("status"=>0,"partyname"=>$vl->VendorName, "type"=>"Opening Stock"), "ordertype"=>"Opening Stock", "orderno"=>$vl->Grnorder, "challanno"=>$vl->InwardgrnId, "quantity"=>$NoPacks, "rate"=>$vl->ArticleRate,"amount"=>($NoPacks * $vl->ArticleRate),"closingquantity"=>$ColorNoPacks);
							}else{
								$historyofsale[] = array("date"=>$vl->InwardDate, "particulars"=>array("status"=>0,"partyname"=>$vl->VendorName, "type"=>"V33"), "ordertype"=>"Purchase", "orderno"=>$vl->Grnorder, "challanno"=>$vl->InwardgrnId, "quantity"=>$NoPacks, "rate"=>$vl->ArticleRate,"amount"=>($NoPacks * $vl->ArticleRate),"closingquantity"=>$ColorNoPacks);
							}	
						}
						
						/* 
						$ColorNoPacks .= $inwarddata[0]->NoPacks;
						if($inwarddata[0]->OpeningStock==1){
							$historyofsale[] = array("date"=>$inwarddata[0]->InwardDate, "particulars"=>array("status"=>0,"partyname"=>$inwarddata[0]->VendorName, "type"=>"Opening Stock"), "ordertype"=>"Opening Stock", "orderno"=>$inwarddata[0]->Grnorder, "challanno"=>$inwarddata[0]->InwardgrnId, "quantity"=>$ColorNoPacks, "rate"=>$inwarddata[0]->ArticleRate,"amount"=>($ColorNoPacks * $inwarddata[0]->ArticleRate),"closingquantity"=>$ColorNoPacks);
						}
						else{
							$historyofsale[] = array("date"=>$inwarddata[0]->InwardDate, "particulars"=>array("status"=>0,"partyname"=>$inwarddata[0]->VendorName, "type"=>"V"), "ordertype"=>"Purchase", "orderno"=>$inwarddata[0]->Grnorder, "challanno"=>$inwarddata[0]->InwardgrnId, "quantity"=>$ColorNoPacks, "rate"=>$inwarddata[0]->ArticleRate,"amount"=>($ColorNoPacks * $inwarddata[0]->ArticleRate),"closingquantity"=>$ColorNoPacks);
						} */
						
					}

				} else{
					if($inwarddata){
						foreach($inwarddata as $key => $vl){
							if($vl->OpeningStock==1){
								$ColorNoPacks .=$vl->NoPacks;
								$historyofsale[] = array("date"=>$vl->InwardDate, "particulars"=>array("status"=>0,"partyname"=>$vl->VendorName, "type"=>"Opening Stock"), "ordertype"=>"Opening Stock", "orderno"=>$vl->Grnorder, "challanno"=>$vl->InwardgrnId, "quantity"=>$vl->NoPacks, "rate"=>$vl->ArticleRate,"amount"=>"","closingquantity"=>$vl->NoPacks);
							}else{
								$historyofsale[] = array("date"=>$vl->InwardDate, "particulars"=>array("status"=>0,"partyname"=>$vl->VendorName, "type"=>"V111"), "ordertype"=>"Purchase", "orderno"=>$vl->Grnorder, "challanno"=>$vl->InwardgrnId, "quantity"=>$vl->NoPacks, "rate"=>$vl->ArticleRate,"amount"=>"","closingquantity"=>"");
							}	
						}
					}
				
					if($vl->OpeningStock==0){
						$ColorNoPacks .=0;
					}
				}
			
				//return $historyofsale; exit;
				$salesorderpending = DB::select("SELECT s.Id as SOID, sn.Id as SoNumberId, DATE_FORMAT(sn.SoDate, '%d/%m/%Y') as SoDate, concat(sn.SoNumber, '/',f.StartYear,'-',f.EndYear) as SoNumber, p.Name as PartyName, s.ArticleRate, s.NoPacks, s.OutwardNoPacks,  sn.Transporter, sn.Destination, sn.Remarks, sn.UserId FROM `so` s inner join sonumber sn on sn.Id=s.SoNumberId inner join financialyear f on f.Id=sn.FinancialYearId inner join party p on p.Id=sn.PartyId where s.ArticleId = '".$Podata[0]->Id."' and Status=0");
				$outwardorder = DB::select("SELECT o.Id as OutwardId, own.Id as OutwardNoId, DATE_FORMAT(own.OutwardDate, '%d/%m/%Y') as OutwardDate, p.Name as PartyName, concat(own.OutwardNumber, '/',f.StartYear,'-',f.EndYear) as OutwardNumber, o.NoPacks, o.OutwardRate, o.OutwardBox, o.PartyDiscount, own.GSTAmount, own.GSTPercentage, own.GSTType, own.Discount FROM `outward` o inner join outwardnumber own on o.OutwardNumberId=own.Id inner join financialyear f on f.Id=own.FinancialYearId inner join party p on p.Id=o.PartyId where o.ArticleId = '".$Podata[0]->Id."'");
				$salesreturnorder = DB::select("SELECT sr.Id as SalesReturnId, DATE_FORMAT(srn.CreatedDate, '%d/%m/%Y') as SalesReturnDate, srn.Id as SalesReturnNumberId, sr.NoPacks, concat(srn.SalesReturnNumber, '/',f.StartYear,'-',f.EndYear) as SalesReturnNumber, p.Name as PartyName FROM `salesreturn` sr inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber inner join party p on p.Id=srn.PartyId inner join financialyear f on f.Id=srn.FinancialYearId where sr.ArticleId='".$Podata[0]->Id."'");
				$purchasereturnorder = DB::select("SELECT pr.Id as PurchaseReturnId, prn.Id PurchaseReturnNumberId, DATE_FORMAT(prn.CreatedDate, '%d/%m/%Y') as PurchaseReturnDate, pr.ReturnNoPacks as NoPacks, concat(prn.PurchaseReturnNumber, '/',f.StartYear,'-',f.EndYear) as PurchaseReturnNumber, v.Name as VendorName FROM `purchasereturn` pr inner join purchasereturnnumber prn on prn.Id=pr.PurchaseReturnNumber inner join vendor v on v.Id=prn.VendorId inner join financialyear f on f.Id=prn.FinancialYearId where pr.ArticleId='".$Podata[0]->Id."'");
				$stocktransfer_cons = DB::select("SELECT st.Id, st.StocktransferNumberId, st.ConsumedNoPacks, DATE_FORMAT(stn.StocktransferDate, '%d/%m/%Y') as StocktransferDate, concat(stn.StocktransferNumber, '/',f.StartYear,'-',f.EndYear) as StocktransferNumber FROM `stocktransfer` st inner join article a on a.Id=st.ConsumedArticleId inner join stocktransfernumber stn on stn.Id=st.StocktransferNumberId inner join financialyear f on f.Id=stn.FinancialYearId where st.ConsumedArticleId = '".$Podata[0]->Id."'");
				$stocktransfer_prod = DB::select("SELECT st.Id, st.StocktransferNumberId, st.TransferNoPacks, DATE_FORMAT(stn.StocktransferDate, '%d/%m/%Y') as StocktransferDate, concat(stn.StocktransferNumber, '/',f.StartYear,'-',f.EndYear) as StocktransferNumber FROM `stocktransfer` st inner join article a on a.Id=st.ConsumedArticleId inner join stocktransfernumber stn on stn.Id=st.StocktransferNumberId inner join financialyear f on f.Id=stn.FinancialYearId where st.TransferArticleId = '".$Podata[0]->Id."'");
				$stocktransfer_shortage = DB::select("SELECT st.Id, st.StocktransferNumberId, st.NoPacks, DATE_FORMAT(stn.StocktransferDate, '%d/%m/%Y') as StocktransferDate, concat(stn.StocktransferNumber, '/',f.StartYear,'-',f.EndYear) as StocktransferNumber FROM `stockshortage` st inner join article a on a.Id=st.ArticleId inner join stocktransfernumber stn on stn.Id=st.StocktransferNumberId inner join financialyear f on f.Id=stn.FinancialYearId where st.ArticleId = '".$Podata[0]->Id."'");
			
				if($Podata[0]->Colorflag==1){
					$Colorflag = $Podata[0]->Colorflag;
					if($outwardorder){
						foreach($outwardorder as $key => $vl){
							$OwNoPacksPCS = 0;
							$OwNoPacks = explode(",",$vl->NoPacks);
							
							$object = (object)$vl;
							
							foreach($ArticleColor as $key => $vl){
								if($ColorId == $vl["Id"]){
									$object->OwNoPacks = $OwNoPacks[$key];
									$OwNoPacksPCS = $OwNoPacks[$key];
								}
							}
							
							if($OwNoPacksPCS!=0){
								$historyofsale[] = array("date"=>$object->OutwardDate, "particulars"=>array("status"=>2,"partyname"=>$object->PartyName, "type"=>"P"), "ordertype"=>"Outward", "orderno"=>$object->OutwardNumber, "challanno"=>$object->OutwardNoId, "quantity"=>$OwNoPacksPCS, "rate"=>$object->OutwardRate,"amount"=>($OwNoPacksPCS * $object->OutwardRate),"closingquantity"=>"");
							}
						}
					}
					
					
					if($stocktransfer_shortage){
						foreach($stocktransfer_shortage as $key => $vl){
							//$salresreturn[] = 
							$STShortageNoPacksPCS = 0;
							$STShortageNoPacks = explode(",",$vl->NoPacks);
							
							$object = (object)$vl;
							foreach($ArticleColor as $key => $vl){
								if($ColorId == $vl["Id"]){
									$object->STShortageNoPacks = $STShortageNoPacks[$key];
									$STShortageNoPacksPCS = $STShortageNoPacks[$key];
								}
							}
							
							if($STShortageNoPacksPCS!=0){
								$historyofsale[] = array("date"=>$object->StocktransferDate, "particulars"=>array("status"=>7,"partyname"=>'', "type"=>"Shortage"), "ordertype"=>"Shortage", "orderno"=>$object->StocktransferNumber, "challanno"=>$object->StocktransferNumberId, "quantity"=>$STShortageNoPacksPCS, "rate"=>"","amount"=>"","closingquantity"=>"");
							}
						}
					}
					
					if($stocktransfer_cons){
						foreach($stocktransfer_cons as $key => $vl){
							//$salresreturn[] = 
							$STConNoPacksPCS = 0;
							$STConNoPacks = explode(",",$vl->ConsumedNoPacks);
							
							$object = (object)$vl;
							foreach($ArticleColor as $key => $vl){
								if($ColorId == $vl["Id"]){
									$object->STConNoPacks = $STConNoPacks[$key];
									$STConNoPacksPCS = $STConNoPacks[$key];
								}
							}
							
							if($STConNoPacksPCS!=0){
								$historyofsale[] = array("date"=>$object->StocktransferDate, "particulars"=>array("status"=>5,"partyname"=>'', "type"=>"Stocktransfer"), "ordertype"=>"Stocktransfer Consumed", "orderno"=>$object->StocktransferNumber, "challanno"=>$object->StocktransferNumberId, "quantity"=>$STConNoPacksPCS, "rate"=>"","amount"=>"","closingquantity"=>"");
							}
						}
					}
					
					
					if($stocktransfer_prod){
						foreach($stocktransfer_prod as $key => $vl){
							//$salresreturn[] = 
							$STProNoPacksPCS = 0;
							$STProNoPacks = explode(",",$vl->TransferNoPacks);
							
							$object = (object)$vl;
							foreach($ArticleColor as $key => $vl){
								if($ColorId == $vl["Id"]){
									$object->STProNoPacks = $STProNoPacks[$key];
									$STProNoPacksPCS = $STProNoPacks[$key];
								}
							}
							
							if($STProNoPacksPCS!=0){
								$historyofsale[] = array("date"=>$object->StocktransferDate, "particulars"=>array("status"=>6,"partyname"=>'', "type"=>"Stocktransfer"), "ordertype"=>"Stocktransfer Production", "orderno"=>$object->StocktransferNumber, "challanno"=>$object->StocktransferNumberId, "quantity"=>$STProNoPacksPCS, "rate"=>"","amount"=>"","closingquantity"=>"");
							}
						}
					}
					
					if($salesreturnorder){
						foreach($salesreturnorder as $key => $vl){
							//$salresreturn[] = 
							$SRNoPacksPCS = 0;
							$SRNoPacks = explode(",",$vl->NoPacks);
							
							$object = (object)$vl;
							foreach($ArticleColor as $key => $vl){
								if($ColorId == $vl["Id"]){
									$object->SRNoPacks = $SRNoPacks[$key];
									$SRNoPacksPCS = $SRNoPacks[$key];
								}
							}
							
							if($SRNoPacksPCS!=0){
								$historyofsale[] = array("date"=>$object->SalesReturnDate, "particulars"=>array("status"=>3,"partyname"=>$object->PartyName,"type"=>"P"), "ordertype"=>"SalesReturn", "orderno"=>$object->SalesReturnNumber, "challanno"=>$object->SalesReturnNumberId, "quantity"=>$SRNoPacksPCS, "rate"=>"","amount"=>"","closingquantity"=>"");
							}
						}
						
					}
					if($purchasereturnorder){
						foreach($purchasereturnorder as $key => $vl){
							$PRNoPacksPCS = 0;
							$PRNoPacks = explode(",",$vl->NoPacks);
							
							$object = (object)$vl;
							foreach($ArticleColor as $key => $vl){
								if($ColorId == $vl["Id"]){
									$object->PRNoPacks = $PRNoPacks[$key];
									$PRNoPacksPCS = $PRNoPacks[$key];
								}
							}
							
							if($PRNoPacksPCS!=0){
								$historyofsale[] = array("date"=>$object->PurchaseReturnDate, "particulars"=>array("status"=>4,"partyname"=>$object->VendorName,"type"=>"V"), "ordertype"=>"PurchaseReturn", "orderno"=>$object->PurchaseReturnNumber, "challanno"=>$object->PurchaseReturnNumberId, "quantity"=>$PRNoPacksPCS, "rate"=>"","amount"=>"","closingquantity"=>"");
							}
						}
					}
				
					//return $salesorderpending;
					if($salesorderpending){
						$salespending = array();
						foreach($salesorderpending as $key => $vl){
							$SoNoPacksPCS = 0;
							$SoOutwardNoPacksPCS = "";
							$SoNoPacks = explode(",",$vl->NoPacks);
							$SoOutwardNoPacks = explode(",",$vl->OutwardNoPacks);
							
							$object = (object)$vl;
							//return $ArticleColor;
							foreach($ArticleColor as $key => $vl){
								if($ColorId == $vl["Id"]){
									$object->SoNoPacks = $SoNoPacks[$key];
									$object->SoOutwardNoPacks = $SoOutwardNoPacks[$key];
									
									$SoNoPacksPCS = $SoNoPacks[$key];
									$SoOutwardNoPacksPCS = $SoOutwardNoPacks[$key];
									/* if($SoNoPacksPCS!=0{
										$outwrdsalepcs = $SoOutwardNoPacksPCS;
										//$outwrdsalepcs = ($SoNoPacksPCS - $SoOutwardNoPacksPCS);
									}else{
										$outwrdsalepcs = $SoOutwardNoPacksPCS;
									} */
									
								}
							}
							
							if($SoNoPacksPCS!=0 && $SoOutwardNoPacksPCS!=0){
								$salespending[] = array("date"=>$object->SoDate, "particulars"=>array("salesdate"=>$object->SoDate,"partyname"=>$object->PartyName,"type"=>"P","rate"=>$object->ArticleRate, "quantity"=>$SoOutwardNoPacksPCS, "orderno"=>$object->SoNumber, "challanno"=>$object->SoNumberId, "challantype"=>1), "ordertype"=>"Sales", "orderno"=>$object->SoNumber, "quantity"=>"", "rate"=>"","amount"=>"","closingquantity"=>"");
							}
							
						}
						
						if(!empty($salespending)){
							$historyofsale[] = array("ordertype"=>"Sales", "particulars"=>array("status"=>1, "type"=>"P", "TotelSalesPending"=>0, "salespending"=>$salespending));
						}
						
						
					}
								
				} else{
					if($outwardorder){
						foreach($outwardorder as $key => $vl){
							$object = (object)$vl;
							//$OwNoPacksPCS = 0;
							$object->OwNoPacks = $vl->NoPacks;
							$OwNoPacksPCS = $vl->NoPacks;
							
							if($OwNoPacksPCS!=0){
								$historyofsale[] = array("date"=>$object->OutwardDate, "particulars"=>array("status"=>2, "partyname"=>$object->PartyName, "type"=>"P"), "ordertype"=>"Outward", "orderno"=>$object->OutwardNumber, "challanno"=>$object->OutwardNoId, "quantity"=>$OwNoPacksPCS, "rate"=>$object->OutwardRate,"amount"=>($OwNoPacksPCS * $object->OutwardRate),"closingquantity"=>"");
							}
						}
					}
					
					
					if($stocktransfer_shortage){
						foreach($stocktransfer_shortage as $key => $vl){
							//$salresreturn[] = 
							$object = (object)$vl;
							$STShortageNoPacksPCS = $vl->NoPacks;
							
							if($STShortageNoPacksPCS!=0){
								$historyofsale[] = array("date"=>$object->StocktransferDate, "particulars"=>array("status"=>7,"partyname"=>'', "type"=>"ST"), "ordertype"=>"Shortage", "orderno"=>$object->StocktransferNumber, "challanno"=>$object->StocktransferNumberId, "quantity"=>$STShortageNoPacksPCS, "rate"=>"","amount"=>"","closingquantity"=>"");
							}
						}
					}
					
					if($stocktransfer_cons){
						foreach($stocktransfer_cons as $key => $vl){
							$object = (object)$vl;
							$STConNoPacksPCS = $vl->ConsumedNoPacks;
							if($STConNoPacksPCS!=0){
								$historyofsale[] = array("date"=>$object->StocktransferDate, "particulars"=>array("status"=>5,"partyname"=>'', "type"=>"S"), "ordertype"=>"Stocktransfer Consumed", "orderno"=>$object->StocktransferNumber, "challanno"=>$object->StocktransferNumberId, "quantity"=>$STConNoPacksPCS, "rate"=>"","amount"=>"","closingquantity"=>"");
							}
						}
					}
					
					
					if($stocktransfer_prod){
						foreach($stocktransfer_prod as $key => $vl){
							$object = (object)$vl;
							$STProNoPacksPCS = $vl->TransferNoPacks;
							if($STProNoPacksPCS!=0){
								$historyofsale[] = array("date"=>$object->StocktransferDate, "particulars"=>array("status"=>6,"partyname"=>'', "type"=>"S"), "ordertype"=>"Stocktransfer Production", "orderno"=>$object->StocktransferNumber, "challanno"=>$object->StocktransferNumberId, "quantity"=>$STProNoPacksPCS, "rate"=>"","amount"=>"","closingquantity"=>"");
							}
						}
					}
					
					if($salesreturnorder){
						foreach($salesreturnorder as $key => $vl){
							$object = (object)$vl;
							$SRNoPacksPCS = $vl->NoPacks;
							if($SRNoPacksPCS!=0){
								$historyofsale[] = array("date"=>$object->SalesReturnDate, "particulars"=>array("status"=>3,"partyname"=>$object->PartyName,"type"=>"P"), "ordertype"=>"SalesReturn", "orderno"=>$object->SalesReturnNumber, "challanno"=>$object->SalesReturnNumberId, "quantity"=>$SRNoPacksPCS, "rate"=>"","amount"=>"","closingquantity"=>"");
							}
						}
					}
					
					if($purchasereturnorder){
						foreach($purchasereturnorder as $key => $vl){
							$object = (object)$vl;
							$PRNoPacksPCS = $vl->NoPacks;
							
							if($PRNoPacksPCS!=0){
								$historyofsale[] = array("date"=>$object->PurchaseReturnDate, "particulars"=>array("status"=>4,"partyname"=>$object->VendorName,"type"=>"V"), "ordertype"=>"PurchaseReturn", "orderno"=>$object->PurchaseReturnNumber, "challanno"=>$object->PurchaseReturnNumberId, "quantity"=>$PRNoPacksPCS, "rate"=>"","amount"=>"","closingquantity"=>"");
							}
						}
					}
					
					if($salesorderpending){
						$salespending = array();
						foreach($salesorderpending as $key => $vl){
							$object = (object)$vl;
							$object->SoNoPacks = $vl->NoPacks;
							$object->SoOutwardNoPacks = $vl->OutwardNoPacks;
							
							$SoNoPacksPCS = $vl->NoPacks;
							$SoOutwardNoPacksPCS = $vl->OutwardNoPacks;

							if($SoNoPacksPCS!=0 && $SoOutwardNoPacksPCS!=0){
								$salespending[] = array("date"=>$object->SoDate, "particulars"=>array("salesdate"=>$object->SoDate,"partyname"=>$object->PartyName, "type"=>"P","rate"=>$object->ArticleRate, "quantity"=>$SoOutwardNoPacksPCS, "orderno"=>$object->SoNumber,"challanno"=>$object->SoNumberId, "challantype"=>1), "ordertype"=>"Sales", "orderno"=>$object->SoNumber, "quantity"=>"", "rate"=>"","amount"=>"","closingquantity"=>"");
							}						
						}
						
						if(!empty($salespending)){
							$historyofsale[] = array("ordertype"=>"Sales", "particulars"=>array("status"=>1, "type"=>"P", "TotelSalesPending"=>0,"salespending"=>$salespending));
						}
						
					}
					
					if($Podata[0]->ArticleOpenFlag==0){
						foreach($ArticleColor as $key => $vl){
							$ArticleColorSet .= $vl["Name"].",";
						}
						
						$ArticleColorSet = rtrim($ArticleColorSet,',');
				
						
						
					
					}else{
						$ArticleColorSet = 0;
						$ArticleSizeSet .=0;
						
					}
					
					$Colorflag = $Podata[0]->Colorflag;
					$ColorNoPacks .= $inwarddata[0]->NoPacks;
					
				}
			
				if($Podata[0]->ArticleOpenFlag==0){
					foreach($ArticleSize as $vl){
							$ArticleSizeSet .= $vl["Name"].",";
					}
					$ArticleSizeSet = rtrim($ArticleSizeSet,',');
				}
			
			
			
				if($historyofsale){
					
					//print_r($historyofsale);
					// Comparison function 
					 
					  
					// Sort the array  
					//usort($historyofsale, "date_compare"); 
					//usort($historyofsale, array($this, "date_compare"));
					//print_r($historyofsale);
					
					//return $historyofsale; exit;
					usort($historyofsale, function($element1, $element2){
						$ordertype = $element1['ordertype'];
						$ordertype2 = $element2['ordertype'];
						if($ordertype!="Sales" && $ordertype2!="Sales"){
							$var = $element1['date'];
							$date = str_replace('/', '-', $var);
							$f1 =  date('Y-m-d', strtotime($date));

							$var2 = $element2['date'];
							$date2 = str_replace('/', '-', $var2);
							$f2 =  date('Y-m-d', strtotime($date2));

							$datetime1 = strtotime($f1); 
							$datetime2 = strtotime($f2); 
							return $datetime1 - $datetime2; 
						}
					});
					
					//return $historyofsale;
					//exit;
					$count = 0;
					$innersalesclosingquantity = 0;
					
					
					
					//usort($historyofsale, "date_sort");
					
					
					
					foreach($historyofsale as $key => $vl){
						$quantityval="";
						$quantity = 0;
						$ordertype = $vl['ordertype'];
						//$object = json_decode(json_encode($vl));
						$object = (object)$vl;
						//return print_r($object);
						if($ordertype=="Purchase" || $ordertype=="Opening Stock"){
							$quantityval = $object->quantity;
							$count = $count + $quantityval;
							$grandtotalinwardquantity = $grandtotalinwardquantity + $quantityval;
							$object->closingquantity = $count;
							if($object->rate){
								$object->rate = number_format($object->rate,2);
							}
						} else if($ordertype=="Outward"){
							//return $object;
							$quantity = (int)$object->quantity;
							$count = $count - $quantity;
							$grandtotaloutwardquantity = $grandtotaloutwardquantity + $quantity;
							$totaloutwardquantity = $quantity++;
							
							$object->closingquantity = $count;
							$object->rate = number_format($object->rate,2);
						} else if($ordertype=="Shortage"){
							//return $object;
							$quantity = (int)$object->quantity;
							$count = $count - $quantity;
							$totaloutwardquantity = $quantity++;
							$object->closingquantity = $count;
							if($object->rate){
								$object->rate = number_format($object->rate,2);
							}
						} else if($ordertype=="Stocktransfer Production"){
							$quantityval = $object->quantity;
							$count = $count + $quantityval;
							$grandtotalinwardquantity = $grandtotalinwardquantity + $quantityval;
							$object->closingquantity = $count;
							if($object->rate){
								$object->rate = number_format($object->rate,2);
							}
						} else if($ordertype=="Stocktransfer Consumed"){
							//return $object;
							$quantity = (int)$object->quantity;
							$count = $count - $quantity;
							$grandtotaloutwardquantity = $grandtotaloutwardquantity + $quantity;
							$totaloutwardquantity = $quantity++;
							
							$object->closingquantity = $count;
							if($object->rate){
								$object->rate = number_format($object->rate,2);
							}
						} else if($ordertype=="Opening Stock"){
							$count = $count + (int)$object->quantity;
							//$totaloutwardquantity = (int)$object->quantity++;
							$object->closingquantity = $count;
						} else if($ordertype=="SalesReturn"){
							$count = $count + (int)$object->quantity;
							$grandtotalinwardquantity = $grandtotalinwardquantity + (int)$object->quantity;
							//$totaloutwardquantity = (int)$object->quantity++;
							$object->closingquantity = $count;
						} else if($ordertype=="PurchaseReturn"){
							$count = $count - (int)$object->quantity;
							//$totaloutwardquantity = (int)$object->quantity++;
							$grandtotaloutwardquantity = $grandtotaloutwardquantity + (int)$object->quantity;
							$object->closingquantity = $count;
						} else if($ordertype=="Sales"){
							
							foreach($object->particulars['salespending'] as &$val){
								if($innersalesclosingquantity==0){
									$innersalesclosingquantity = $val["particulars"]["quantity"];
								}else{
									$innersalesclosingquantity = $innersalesclosingquantity + $val["particulars"]["quantity"];
								}
								$grandtotaloutwardquantity = $grandtotaloutwardquantity + $val["particulars"]["quantity"];
								$val["closingquantity"] = $innersalesclosingquantity;
							}
							$object->quantity = $innersalesclosingquantity;
							$object->TotelSalesPending = $count - $innersalesclosingquantity;
							//$object->TotelSalesPending = $innersalesclosingquantity;
						} else{
							
						}
						/* if($vl['ordertype']=="purchase"){
							$quantityval = $vl['quantity'];
							$count = $quantityval++;
						} */
						//return print_r($object);
						$history_newarray[] = $object;
					}
					
					
					
					$total_stock = $count - $innersalesclosingquantity;
					//return print_r($history_newarray);
				}
			}else{
				$article_exist = 2;
			}
			
			if($articleRejected){
					$articlerej_exist = 0;
					if($Podata[0]->ArticleOpenFlag==0){
						$RejectedArticleColor = json_decode($articleRejected[0]->ArticleColor, true);
						$RejectedArticleSize = json_decode($articleRejected[0]->ArticleSize, true);
						$RejectedArticleColorSet = "";
						$RejectedArticleSizeSet = "";
						foreach($RejectedArticleColor as $key => $vl){
							$RejectedArticleColorSet .= $vl["Name"].",";
						}
						foreach($RejectedArticleSize as $key => $vl){
							$RejectedArticleSizeSet .= $vl["Name"].",";
						}
						
						$RejectedArticleColorSet = rtrim($RejectedArticleColorSet,',');
						$RejectedArticleSizeSet = rtrim($RejectedArticleSizeSet,',');
					}
					else{
						$RejectedArticleColorSet ="";
						$RejectedArticleSizeSet = "";
					}
					
					foreach($articleRejected as $key => $vl){
						//$arNoPacks = explode(",",$vl->NoPacks);
						$object = (object)$vl;
						$object->ArticleColor = $RejectedArticleColorSet;
						$object->ArticleSize = $RejectedArticleSizeSet;
						
						/* if($Podata[0]->Colorflag==1){
							foreach($RejectedArticleColor as $key => $vl){
								if($ColorId == $vl["Id"]){
									$object->NoPacks = $arNoPacks[$key];
								}
							}
						} */
					}
				}
			
				if($articleCancelled){
					$articlecan_exist = 0;
					if($Podata[0]->ArticleOpenFlag==0){
						$CancelledArticleColor = json_decode($articleCancelled[0]->ArticleColor, true);
						$CancelledArticleSize = json_decode($articleCancelled[0]->ArticleSize, true);
						
						$CancelledArticleColorSet = "";
						$CancelledArticleSizeSet = "";
						foreach($CancelledArticleColor as $key => $vl){
							$CancelledArticleColorSet .= $vl["Name"].",";
						}
						foreach($CancelledArticleSize as $key => $vl){
							$CancelledArticleSizeSet .= $vl["Name"].",";
						}
						
						$CancelledArticleColorSet = rtrim($CancelledArticleColorSet,',');
						$CancelledArticleSizeSet = rtrim($CancelledArticleSizeSet,',');
						
					} else{
						$CancelledArticleColorSet ="";
						$CancelledArticleSizeSet =  "";
					}
					
					foreach($articleCancelled as $key => $vl){
						$acNoPacks = explode(",",$vl->NoPacks);
						$object = (object)$vl;
						
						$object->ArticleSize = $CancelledArticleSizeSet;
						$object->ArticleColor = $CancelledArticleColorSet;
						
						if($Podata[0]->Colorflag==1){
							foreach($CancelledArticleColor as $key => $vl){
								if($ColorId == $vl["Id"]){
									$object->NoPacks = $acNoPacks[$key];
								}
							}
						}
					}
				}
		}else{
			$article_exist = 0;
		}
		
		
				
		
		if($UserRoleFlag==0){
			$history_newarray = [];
		}
		
		//return print_r($articleRejected);
		//$data  = array("ArticleExist"=>$article_exist, "grandtotalinwardquantity"=>$grandtotalinwardquantity, "grandtotaloutwardquantity"=>$grandtotaloutwardquantity, "PurchaseOrder"=>$Podata,  "InwardData"=>array("InwardExist"=>$inward_exist,"InwardOrder" => $inwarddata, "NoPcks" => $ColorNoPacks, "ArticleSizeSet"=>$ArticleSizeSet, "Colorflag"=>$Colorflag, "ArticleColorSet"=>$ArticleColorSet), "SalesOrderHistory"=>array("SalesExist"=>$sales_exist,"TotalStock"=>$total_stock, "UserRoleFlag"=>$UserRoleFlag, "TotalOutwardQuantity" => $totaloutwardquantity, "SalesOrderHistory"=>$history_newarray), "ArticleRejected"=>$articleRejected, "ArticleRejExist"=>$articlerej_exist, "ArticleCancelled"=>$articleCancelled, "ArticleCanExist"=>$articlecan_exist);
		//$data  = array("ArticleExist"=>$article_exist, "grandtotalinwardquantity"=>$grandtotalinwardquantity, "grandtotaloutwardquantity"=>$grandtotaloutwardquantity, "PurchaseOrder"=>$Podata,  "InwardData"=>array("InwardExist"=>$inward_exist,"InwardOrder" => $inwarddata, "NoPcks" => $ColorNoPacks, "ArticleSizeSet"=>$ArticleSizeSet, "Colorflag"=>$Colorflag, "ArticleColorSet"=>$ArticleColorSet), "SalesOrderHistory"=>array("SalesExist"=>$sales_exist,"TotalStock"=>$total_stock, "UserRoleFlag"=>$UserRoleFlag, "TotalOutwardQuantity" => $totaloutwardquantity, "SalesOrderHistory"=>$history_newarray));
		if($SalesNoPacks!=$total_stock){
			return "ArticleName: ".$ArticleName." | ColorId:".$ColorId." | <span style='background:red'>TotalStock: ".$total_stock."</span> | SalesNoPacks: ".$SalesNoPacks." | Total inward: ".$grandtotalinwardquantity." | Total Outward: ".$grandtotaloutwardquantity;
		} else{
			//return "ArticleName: ".$ArticleName." | ColorId:".$ColorId." | TotalStock: ".$total_stock." | SalesNoPacks: ".$SalesNoPacks." | Total inward: ".$grandtotalinwardquantity." | Total Outward: ".$grandtotaloutwardquantity;
		}
		
	}
	
	//For testing - end
}
