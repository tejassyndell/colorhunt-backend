<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Outward;
use App\Transportoutlet;

class OutwardController extends Controller
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
	
	public function GenerateOWNumber($UserId)
    {
		$array = array();
		$fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
        $ownumberdata = DB::select('SELECT Id, FinancialYearId, OutwardNumber From outwardnumber order by Id desc limit 0,1');
		
		if(count($ownumberdata)>0){
			if($fin_yr[0]->Id > $ownumberdata[0]->FinancialYearId){
				$array["OW_Number"] = 1;
				$array["OW_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["OW_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			} else{
				$array["OW_Number"] = ($ownumberdata[0]->OutwardNumber) + 1;
				$array["OW_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["OW_Number_Financial"] = ($ownumberdata[0]->OutwardNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			}
		}
        else{
			$array["OW_Number"] = 1;
			$array["OW_Number_Financial_Id"] = $fin_yr[0]->Id;
			$array["OW_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
			return $array;
        }
    }

    ///Outward Module
    public function AddOutward(Request $request)
    {
		$data = $request->all();
		//print_r($data); exit;
		DB::beginTransaction(); 
		try {
			//$data = $request->all();
			//echo "<pre>"; print_r($data); exit;
			$dataresult= DB::select('SELECT c.Colorflag FROM `article` a inner join category c on c.Id=a.CategoryId where a.Id="'.$data['ArticleId'].'"'); 
			$Colorflag = $dataresult[0]->Colorflag;
			
			$datanopacks= DB::select('SELECT OutwardNoPacks FROM `so` where ArticleId="'.$data['ArticleId'].'" and SoNumberId="'.$data['SoId'].'"');
			$search = $datanopacks[0]->OutwardNoPacks;
			
			$outwardadd = array();
			$searchString = ',';
			if( strpos($search, $searchString) !== false ) {
				$string = explode(',', $search);
				$stringcomma = 1;
			}else{
				$search;
				$stringcomma = 0;
			}
			
			$NoPacks = "";
			$SalesNoPacks = "";
			if($Colorflag==1){
				foreach($data['ArticleSelectedColor'] as $key => $vl){
					$numberofpacks = $vl["Id"];
					if($data["NoPacksNew_".$numberofpacks]!=""){
						if($stringcomma==1){
							if($string[$key]<$data["NoPacksNew_".$numberofpacks]){
								return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
							}
							
							$SalesNoPacks .= ($string[$key] - $data["NoPacksNew_".$numberofpacks]).",";
						}else{
							if($search<$data["NoPacksNew_".$numberofpacks]){
								return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
							}
							
							$SalesNoPacks .= ($search - $data["NoPacksNew_".$numberofpacks]).",";
						}
						$NoPacks .= $data["NoPacksNew_".$numberofpacks].",";
					}
					else{
						$NoPacks .= "0,";
						$SalesNoPacks .= $string[$key].",";
					}
				}
			} else{
				if(isset($data['NoPacksNew'])){
					$NoPacks = $data['NoPacksNew'];
					if($search<$data['NoPacksNew']){
						return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
					}
					$SalesNoPacks = ($search - $data['NoPacksNew']);
				}else{
					return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
				}
			}
			
			//echo $data['NoPacksNew']; exit; 
			//echo "asdas"; exit;
			//echo $data['NoPacksNew']; exit;
			$NoPacks = rtrim($NoPacks,',');
			$SalesNoPacks = rtrim($SalesNoPacks,',');
			//echo "<br/>";
			$CheckSalesNoPacks = explode(',', $NoPacks);
			//var_dump($CheckSalesNoPacks);
			//return $NoPacks."->".$SalesNoPacks;
			//exit;
			
			$tmp = array_filter($CheckSalesNoPacks);
			if(empty($tmp)){
				return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
			}
			if($data['Discount']==""){
				$Discount = 0;
			}else{
				$Discount = $data['Discount'];
			}
			
			
			
			if($data['OutwardNumberId']=="Add"){
				$generate_OWNumber = $this->GenerateOWNumber($data['UserId']);
				$OW_Number = $generate_OWNumber['OW_Number'];
				$OW_Number_Financial_Id = $generate_OWNumber['OW_Number_Financial_Id'];
				//$generate_OWNumber['OW_Number'];
				//$generate_OWNumber['OW_Number_Financial'];
				//$generate_OWNumber['OW_Number_Financial_Id'];
				
				$OutwardNumberId = DB::table('outwardnumber')->insertGetId(
					['OutwardNumber' =>  $OW_Number, "FinancialYearId"=>$OW_Number_Financial_Id,'SoId'=>$data['SoId'], 'UserId'=>$data['UserId'], 'OutwardDate' =>  $data['OutwardDate'], 'GSTAmount'=>$data['GST'], 'GSTPercentage'=>$data['GST_Percentage'], 'GSTType'=>$data['GSTType'], 'Discount'=>$Discount, 'Remarks'=>$data['Remarks'], 'created_at'=>date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s')]
				);
				
				
				
			}else{
				$checksonumber = DB::select("SELECT OutwardNumber FROM `outwardnumber` where Id ='".$data['OutwardNumberId']."'");
				if(!empty($checksonumber)){
					$OW_Number = $checksonumber[0]->OutwardNumber;
					$OutwardNumberId = $data['OutwardNumberId'];
					
					DB::table('outwardnumber')
					->where('Id', $OutwardNumberId)
					->update(['OutwardDate'=>$data['OutwardDate'], 'SoId'=>$data['SoId'], 'UserId'=>$data['UserId'], 'GSTAmount'=>$data['GST'], 'GSTPercentage'=>$data['GST_Percentage'], 'GSTType'=>$data['GSTType'], 'Discount'=>$Discount, 'Remarks'=>$data['Remarks'], 'updated_at'=>date('Y-m-d H:i:s')]);
				}
			}	
			
			$sonumberdata = DB::select('SELECT count(*) as total, NoPacks  FROM `outward` where OutwardNumberId="'.$OutwardNumberId.'" and ArticleId="'.$data['ArticleId'].'"');
			$getnppacks = $sonumberdata[0]->NoPacks;
			
			
			$updated = DB::table('so')
				->where('ArticleId', $data['ArticleId'])
				->where('SoNumberId', $data['SoId'])
				->update(['OutwardNoPacks' => $SalesNoPacks]);
			
			//$articleCheck = DB::select("select count(*) as Total from (select OutwardNoPacksCheck(sn.Id, s.ArticleId) as OutwardNoPacksCheck from so s inner join article a on a.Id=s.ArticleId inner join sonumber sn on sn.Id=s.SoNumberId where s.SoNumberId='".$data['SoId']."') as d where OutwardNoPacksCheck=0");
			$articleCheck = DB::select("select OutwardNoPacksCheck from (select OutwardNoPacksCheck(sn.Id, s.ArticleId) as OutwardNoPacksCheck from so s inner join article a on a.Id=s.ArticleId inner join sonumber sn on sn.Id=s.SoNumberId where s.SoNumberId='".$data['SoId']."' and s.ArticleId='".$data['ArticleId']."') as d where OutwardNoPacksCheck=1");
			if(isset($articleCheck[0]->OutwardNoPacksCheck)==1){
				DB::table('so')
				->where('ArticleId', $data['ArticleId'])
				->where('SoNumberId', $data['SoId'])
				->update(['Status' => 1]);
			}
			
			if(isset($data['OutwardWeight'])){
				$OutwardWeight = $data['OutwardWeight'];
			}else{
				$OutwardWeight =  "";
			}
			
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
					//echo "ELSE"; exit;
					$nopacksadded .= $getnppacks + $NoPacks.",";
				}
				$nopacksadded = rtrim($nopacksadded,',');
				
				DB::table('outward')
					->where('OutwardNumberId', $OutwardNumberId)
					->where('ArticleId', $data['ArticleId'])
					->update(['NoPacks'=>$nopacksadded,'OutwardBox'=>$data["OutwardBox"],'OutwardRate'=>$data["OutwardRate"], 'OutwardWeight'=>$OutwardWeight, 'updated_at'=>date('Y-m-d H:i:s')]);

				if($data['ArticleOpenFlag']==0){
					if( strpos($nopacksadded, ',') !== false ) {
						$nopacksadded = explode(',', $nopacksadded);
						foreach($data['ArticleSelectedColor'] as $key => $vl){
							$numberofpacks = $vl["Id"];
							$res = DB::select("select Id from outwardpacks where ColorId='".$numberofpacks."' and ArticleId='".$data['ArticleId']."'");
							DB::table('outwardpacks')
							->where('Id', $res[0]->Id)
							->update(['NoPacks'=>$nopacksadded[$key], 'UpdatedDate'=>date('Y-m-d H:i:s')]);
						}
					}else{
						foreach($data['ArticleSelectedColor'] as $key => $vl){
							$numberofpacks = $vl["Id"];
							$res = DB::select("select Id from outwardpacks where ColorId='".$numberofpacks."' and ArticleId='".$data['ArticleId']."'");
							DB::table('outwardpacks')
							->where('Id', $res[0]->Id)
							->update(['NoPacks'=>$nopacksadded, 'UpdatedDate'=>date('Y-m-d H:i:s')]);
						}
					}
				}else{
					$datavl = DB::select('select Id from outward where OutwardNumberId = "'.$OutwardNumberId.'" and ArticleId = "'.$data['ArticleId'].'"');
					
					DB::table('outwardpacks')
					->where('Id', $datavl[0]->Id)
					->update(['NoPacks'=>$nopacksadded, 'UpdatedDate'=>date('Y-m-d H:i:s')]);
				}
				
				
				
			} else
			{
			   $outwardadd['OutwardNumberId'] = $OutwardNumberId;
			   $outwardadd["ArticleId"] = $data['ArticleId'];
			   $outwardadd["NoPacks"] = $NoPacks;
			   $outwardadd["OutwardBox"] = $data["OutwardBox"];
			   $outwardadd['OutwardRate']= $data["OutwardRate"];
			   $outwardadd['OutwardWeight']= $OutwardWeight;
			   $outwardadd["ArticleOpenFlag"] = $data["ArticleOpenFlag"];
			   $outwardadd["PartyId"] = $data["PartyId"];
			   $outwardadd["PartyDiscount"] = $data['PartyDiscount'];
			   $field = Outward::create($outwardadd);
			   $outward_insertedid =$field->id;
			   
			    $partydata = DB::select('SELECT count(*) as TotalRow FROM `party` where OutletAssign = 1 and Id = '.$data["PartyId"]);
				if($partydata[0]->TotalRow > 0){
					$trandata = DB::select('SELECT count(*) as TotalRow FROM `transportoutlet` where OutwardNumberId="'.$OutwardNumberId.'"');
					if($trandata[0]->TotalRow == 0){
						Transportoutlet::create(array("OutwardNumberId"=>$OutwardNumberId, "PartyId"=>$data["PartyId"], "TransportStatus"=>0));
					}
				}

				if($data['ArticleOpenFlag']==0){
					if( strpos($NoPacks, ',') !== false ) {
						$NoPacks = explode(',', $NoPacks);
						foreach($data['ArticleSelectedColor'] as $key => $vl){
							$numberofpacks = $vl["Id"];
							DB::table('outwardpacks')->insertGetId(
								['ArticleId' =>  $data['ArticleId'], 'ColorId'=>$numberofpacks, 'OutwardId'=> $outward_insertedid,'NoPacks'=>$NoPacks[$key], 'PartyId' =>$data["PartyId"], 'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
							);
						}
					}else{
						foreach($data['ArticleSelectedColor'] as $key => $vl){
							$numberofpacks = $vl["Id"];
							DB::table('outwardpacks')->insertGetId(
								['ArticleId' =>  $data['ArticleId'], 'ColorId'=>$numberofpacks, 'OutwardId'=> $outward_insertedid,'NoPacks'=>$NoPacks, 'PartyId' =>$data["PartyId"],'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
							);
						}
					}
				}else{
					$datavl = DB::select('select Id from outward where OutwardNumberId = "'.$OutwardNumberId.'" and ArticleId = "'.$data['ArticleId'].'"');
					
					DB::table('outwardpacks')->insertGetId(
						['ArticleId' =>  $data['ArticleId'], 'ColorId'=>0, 'OutwardId'=> $outward_insertedid,'NoPacks'=>$NoPacks, 'PartyId' =>$data["PartyId"],'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
					);
				}
			   //outwardpacks
			}
			
			//echo $NoPacks."\n\n";
			//echo $SalesNoPacks."\n\n";
			
			//echo "<pre>"; print_r($data); exit;
			//$field = Outward::create($request->all());
			DB::commit();
			return response()->json(array("OutwardNumberId"=>$OutwardNumberId, "OW_Number"=>$OW_Number), 200);
		}catch (\Exception $e) {
			DB::rollback();
			return response()->json("", 200);
		}
    }
	
	public function OutwardListFromOWNO($Id){
		return DB::select('SELECT o.Id , o.PartyDiscount, o.OutwardNumberId, o.OutwardBox, o.OutwardRate,o.OutwardWeight, owdn.OutwardNumber, owdn.OutwardDate, owdn.GSTAmount, owdn.GSTPercentage, owdn.GSTType, o.NoPacks, a.ArticleNumber, a.Id as ArticleId, concat(owdn.OutwardNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as OW_Number_FinancialYear FROM `outward` o inner join outwardnumber owdn on o.OutwardNumberId=owdn.Id inner join article a on a.Id=o.ArticleId inner join financialyear fn on fn.Id=owdn.FinancialYearId where o.OutwardNumberId="'.$Id.'"');
	}
	
	public function GetOutwardIdWise($id)
    {
        return DB::select('SELECT o.*, p.Discount as PartyDiscount, concat(own.OutwardNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as OutwardNumber From outward o inner join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId inner join party p on p.Id=o.PartyId WHERE o.Id = ' . $id . '');
    }
	 
	public function GetArticle()
    {
		return DB::select('select * from (SELECT art.*, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId group by inw.Id) as t where SalesNoPacksCheck!=1');
		//return DB::select('SELECT art.*, art.ArticleNumber, s.ArticleId, inw.NoPacks, inw.SalesNoPacks From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId group by inw.Id');
		//return DB::select('SELECT art.*, art.ArticleNumber, s.ArticleId From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId where s.ArticleId is NULL');
    }
	
	public function OutwardDateGstFromOWNO($id)
     {
		return DB::select("SELECT own.*, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber, concat(own.OutwardNumber, '/',fn1.StartYear,'-',fn1.EndYear) as OW_Number_FinancialYear FROM `outwardnumber` own inner join sonumber sn on sn.Id=own.SoId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join financialyear fn1 on fn1.Id=own.FinancialYearId inner join users u on u.Id=sn.UserId where own.Id='".$id."'");
	 }
	 
    public function GeOutward($UserId)
    {
		$userrole = DB::select("SELECT Role FROM `users`where Id='".$UserId."'");
		if($userrole[0]->Role==2){
			$wherecustom ="";
		}else{
			$wherecustom ="where own.UserId='".$UserId."'";
		}
		
		//echo "asdas"; exit;
		//return DB::select("SELECT own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, own.OutwardNumber, own.OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId group by o.OutwardNumberId");
        return DB::select("SELECT GetTotalOutwardOrderPieces(own.Id) as TotalOutwardPieces, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber,  own.OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId ".$wherecustom." group by o.OutwardNumberId order by o.Id desc");
		//return Outward::all();
    }
	
// 	public function PostOutward(Request $request)
// 	{
// 		$data = $request->all();	
// 		$search = $data['dataTablesParameters']["search"];
// 		$UserId = $data['UserID'];
// 		$startnumber = $data['dataTablesParameters']["start"];
		
		
// 		$vnddataTotal = DB::select("select count(*) as Total from (SELECT GetTotalOutwardOrderPieces(own.Id) as TotalOutwardPieces, sn.UserId, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber,  own.OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId group by o.OutwardNumberId order by o.Id desc) as d");
// 		$vntotal=$vnddataTotal[0]->Total;		
// 		$length = $data['dataTablesParameters']["length"];
		
		
// 		$userrole = DB::select("SELECT Role FROM `users`where Id='".$UserId."'");
// 		/* if($userrole[0]->Role==2){
// 			$wherecustom ="";
// 			if($search['value'] != null && strlen($search['value']) > 2){
// 				//$searchstring = "and oln.OutletNumber like '%".$search['value']."%'";
// 				$searchstring = "where d.OutwardNumber like '%".$search['value']."%' OR d.Name like '%".$search['value']."%'";
// 				$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT GetTotalOutwardOrderPieces(own.Id) as TotalOutwardPieces, sn.UserId, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber,  own.OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId group by o.OutwardNumberId order by o.Id desc) as d ".$searchstring);
// 				$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
// 			}else{
// 				$searchstring = "";
// 				$vnddataTotalFilterValue = $vntotal;
// 			}
			
// 		}else{
// 			$wherecustom ="where d.UserId='".$UserId."'";
// 			if($search['value'] != null && strlen($search['value']) > 2){
// 				$searchstring = " and d.OutletNumber like '%".$search['value']."%' OR cast(d.cdate as char) like '%".$search['value']."%'";
// 				$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT GetTotalOutwardOrderPieces(own.Id) as TotalOutwardPieces, sn.UserId, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber,  own.OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId) as d ".$wherecustom.' '.$searchstring);
// 				$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
// 			}else{
// 				$searchstring = "";
// 				$vnddataTotalFilterValue = $vntotal;
// 			}
// 		} */
		
// 		$wherecustom ="";
// 			if($search['value'] != null && strlen($search['value']) > 2){
// 				//$searchstring = "and oln.OutletNumber like '%".$search['value']."%'";
// 				$searchstring = "where d.OutwardNumber like '%".$search['value']."%' OR d.SoNumber like '%".$search['value']."%' OR cast(d.OutwardDate as char) like '%".$search['value']."%' OR d.Name like '%".$search['value']."%' OR d.ArticleNumber like '%".$search['value']."%'";
// 				$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT GetTotalOutwardOrderPieces(own.Id) as TotalOutwardPieces, sn.UserId, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, DATE_FORMAT(own.OutwardDate, \"%d/%m/%Y\") as OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId group by o.OutwardNumberId order by o.Id desc) as d ".$searchstring);
// 				$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
// 			}else{
// 				$searchstring = "";
// 				$vnddataTotalFilterValue = $vntotal;
// 			}
// 		//end
// 		$column = $data['dataTablesParameters']["order"][0]["column"];
// 		switch ($column) {
// 			case 1:
// 				$ordercolumn = "d.OutwardNumberId";
// 				break;
// 			case 2:
// 				$ordercolumn = "d.SoNumber";
// 				break;	
// 			case 3:
// 				$ordercolumn = "d.Name";
// 				break;	
// 			case 5:
// 				$ordercolumn = "date(d.owdate)";
// 				break;
// 			default:
// 				$ordercolumn = "date(d.owdate)";
// 				break;
// 		}
		
// 		$order = "";
// 		if($data['dataTablesParameters']["order"][0]["dir"]){
// 			$order = "order by ".$ordercolumn." ".$data['dataTablesParameters']["order"][0]["dir"];
// 		}
		
// 		//"SELECT GetTotalOutwardOrderPieces(own.Id) as TotalOutwardPieces, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber,  own.OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId ".$wherecustom." group by o.OutwardNumberId order by o.Id desc"
		
// 		$vnddata = DB::select("select d.* from (SELECT GetTotalOutwardOrderPieces(own.Id) as TotalOutwardPieces, sn.UserId, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber,  own.OutwardDate as owdate, DATE_FORMAT(own.OutwardDate, \"%d/%m/%Y\") as OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId group by o.OutwardNumberId) as d ".$wherecustom." ".$searchstring." ".$order." limit ".$data['dataTablesParameters']["start"].",".$length);
		
	
// 		return array(
// 				'datadraw'=>$data['dataTablesParameters']["draw"],
// 				'recordsTotal'=>$vntotal,
// 				'recordsFiltered'=>$vnddataTotalFilterValue,
// 				'response' => 'success',
// 				'startnumber' => $startnumber,
// 				'search'=>count($vnddata),
// 				'data' => $vnddata,
// 			);
		
// 	}


    public function PostOutward(Request $request)
	{
		$data = $request->all();	
		$search = $data['dataTablesParameters']["search"];
		$UserId = $data['UserID'];
		$startnumber = $data['dataTablesParameters']["start"];
		
		
		//$vnddataTotal = DB::select("select count(*) as Total from (SELECT GetTotalOutwardOrderPieces(own.Id) as TotalOutwardPieces, sn.UserId, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber,  own.OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId group by o.OutwardNumberId order by o.Id desc) as d");
		$vnddataTotal = DB::select("select count(*) as Total from (SELECT own.Id, p.Name, own.SoId, o.OutwardNumberId FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId group by o.OutwardNumberId order by o.Id desc) as d");
		$vntotal=$vnddataTotal[0]->Total;		
		$length = $data['dataTablesParameters']["length"];
		
		
		$userrole = DB::select("SELECT Role FROM `users`where Id='".$UserId."'");
		/* if($userrole[0]->Role==2){
			$wherecustom ="";
			if($search['value'] != null && strlen($search['value']) > 2){
				//$searchstring = "and oln.OutletNumber like '%".$search['value']."%'";
				$searchstring = "where d.OutwardNumber like '%".$search['value']."%' OR d.Name like '%".$search['value']."%'";
				$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT GetTotalOutwardOrderPieces(own.Id) as TotalOutwardPieces, sn.UserId, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber,  own.OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId group by o.OutwardNumberId order by o.Id desc) as d ".$searchstring);
				$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
			}else{
				$searchstring = "";
				$vnddataTotalFilterValue = $vntotal;
			}
			
		}else{
			$wherecustom ="where d.UserId='".$UserId."'";
			if($search['value'] != null && strlen($search['value']) > 2){
				$searchstring = " and d.OutletNumber like '%".$search['value']."%' OR cast(d.cdate as char) like '%".$search['value']."%'";
				$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT GetTotalOutwardOrderPieces(own.Id) as TotalOutwardPieces, sn.UserId, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber,  own.OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId) as d ".$wherecustom.' '.$searchstring);
				$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
			}else{
				$searchstring = "";
				$vnddataTotalFilterValue = $vntotal;
			}
		} */
		
		$wherecustom ="";
			if($search['value'] != null && strlen($search['value']) > 2){
				//$searchstring = "and oln.OutletNumber like '%".$search['value']."%'";
				$searchstring = "where d.OutwardNumber like '%".$search['value']."%' OR d.SoNumber like '%".$search['value']."%' OR cast(d.OutwardDate as char) like '%".$search['value']."%' OR d.Name like '%".$search['value']."%' OR d.ArticleNumber like '%".$search['value']."%'";
				//$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT GetTotalOutwardOrderPieces(own.Id) as TotalOutwardPieces, sn.UserId, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, DATE_FORMAT(own.OutwardDate, \"%d/%m/%Y\") as OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId group by o.OutwardNumberId order by o.Id desc) as d ".$searchstring);
				$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT sn.UserId, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, DATE_FORMAT(own.OutwardDate, \"%d/%m/%Y\") as OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId group by o.OutwardNumberId order by o.Id desc) as d ".$searchstring);
				
				$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
			}else{
				$searchstring = "";
				$vnddataTotalFilterValue = $vntotal;
			}
		//end
		$column = $data['dataTablesParameters']["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "d.OutwardNumberId";
				break;
			case 2:
				$ordercolumn = "d.SoNumber";
				break;	
			case 3:
				$ordercolumn = "d.Name";
				break;	
			case 5:
				$ordercolumn = "date(d.owdate)";
				break;
			default:
				$ordercolumn = "date(d.owdate)";
				break;
		}
		
		$order = "";
		if($data['dataTablesParameters']["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data['dataTablesParameters']["order"][0]["dir"];
		}
		
		//"SELECT GetTotalOutwardOrderPieces(own.Id) as TotalOutwardPieces, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber,  own.OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId ".$wherecustom." group by o.OutwardNumberId order by o.Id desc"
		
		//$vnddata = DB::select("select d.* from (SELECT GetTotalOutwardOrderPieces(own.Id) as TotalOutwardPieces, sn.UserId, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber,  own.OutwardDate as owdate, DATE_FORMAT(own.OutwardDate, \"%d/%m/%Y\") as OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId group by o.OutwardNumberId) as d ".$wherecustom." ".$searchstring." ".$order." limit ".$data['dataTablesParameters']["start"].",".$length);
		$vnddata = DB::select("select d.* from (SELECT CountNoPacks(GROUP_CONCAT(CONCAT(o.NoPacks) ORDER BY a.Id SEPARATOR ',')) as TotalOutwardPieces, sn.UserId, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, own.OutwardDate as owdate, DATE_FORMAT(own.OutwardDate, '%d/%m/%Y') as OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId group by o.OutwardNumberId) as d ".$wherecustom." ".$searchstring." ".$order." limit ".$data['dataTablesParameters']["start"].",".$length);
		
	
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
	
    public function Deleteoutward($id, $ArticleId)
    {
        //echo $id."\n";
		//echo $ArticleId."\n\n";
		$datanopacks = DB::select('SELECT s.Id, s.NoPacks, s.OutwardNoPacks, own.SoId, o.OutwardNumberId, o.NoPacks, o.ArticleId, own.outwardnumber FROM `outward` o inner join outwardnumber own on own.Id=o.OutwardNumberId inner join so s on s.SoNumberId=own.SoId where o.Id="'.$id.'" and s.ArticleId="'.$ArticleId.'"');
		//echo "<pre>"; print_r($datanopacks); exit;
		//$datanopacks= DB::select('SELECT s.ArticleId, s.NoPacks, inw.NoPacks as InwardNoPacks, inw.SalesNoPacks FROM `so` s left join inward inw on inw.ArticleId=s.ArticleId where s.Id="'.$id.'"');
		$search = $datanopacks[0]->NoPacks;
		$SoId = $datanopacks[0]->SoId;
		$OutwardNoPacks = $datanopacks[0]->OutwardNoPacks;
		$OutwardNumberId = $datanopacks[0]->OutwardNumberId;
		
		$searchString = ',';
		$AddSalesNoPacks = "";
		if( strpos($search, $searchString) !== false ) {
			$string = explode(',', $search);
			$OutwardNoPacks = explode(',', $OutwardNoPacks);
			
			
			foreach($string as $key => $vl){
				$AddSalesNoPacks .= ($OutwardNoPacks[$key] + $vl).",";
			}
			//$AddSalesNoPacks = rtrim($AddSalesNoPacks,',');
		}else{
			$AddSalesNoPacks .= ($search + $OutwardNoPacks).",";
		}
		
		
		$AddSalesNoPacks = rtrim($AddSalesNoPacks,',');
		//echo $AddSalesNoPacks;
		
		
		//echo "<pre>"; print_r($datanopacks); exit;
		DB::beginTransaction();
		try {
			DB::table('outward')
			->where('Id', '=', $id)
			->delete();
			
			DB::table('so')
            ->where('ArticleId', $ArticleId)
			->where('SoNumberId', $SoId)
            ->update(['OutwardNoPacks' => $AddSalesNoPacks,'Status'=>0]);
			
			DB::table('outwardpacks')
			->where('OutwardId', '=', $id)
			->delete();
			
			$ot = DB::select('select count(*) as Total from outward where OutwardNumberId='.$OutwardNumberId);
			if($ot[0]->Total == 0){
				DB::table('transportoutlet')
				->where('OutwardNumberId', '=', $OutwardNumberId)
				->delete();
			}
			DB::commit();
			
			return response()->json("SUCCESS", 200);
		} catch (\Exception $e) {
			DB::rollback();
			
			return response()->json("", 200);
		}
    }
	
	public function DeleteOWNumber($OWNO, $SOID)
	{
		//echo $OWNO;
	    //$solist = DB::select("SELECT s.ArticleId, s.NoPacks, inw.NoPacks as InwardNoPacks, inw.SalesNoPacks FROM `so` s left join inward inw on inw.ArticleId=s.ArticleId where SoNumberId ='".$SONO."'");
		//SELECT * FROM `outward` o inner join outwardnumber own on own.Id=o.OutwardNumberId where o.OutwardNumberId=1
		//SELECT s.SoNumberId, s.ArticleId, s.NoPacks, s.OutwardNoPacks, own.*, (select NoPacks from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as NoPacks FROM `so` s inner join outwardnumber own on own.SoId=s.SoNumberId where s.SoNumberId=1 and s.ArticleId in (1,2)
		//SELECT s.Id, s.SoNumberId, s.ArticleId, s.NoPacks, s.OutwardNoPacks, (select NoPacks from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as NoPacks FROM `so` s inner join outwardnumber own on own.SoId=s.SoNumberId where s.SoNumberId="2" and s.ArticleId in (select ArticleId from outward where OutwardNumberId="4") group by Id
		//echo 'SELECT s.Id, s.SoNumberId, s.ArticleId, s.NoPacks, s.OutwardNoPacks, a.ArticleNumber, (select NoPacks from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as OWNoPacks FROM `so` s inner join outwardnumber own on own.SoId=s.SoNumberId inner join article a on a.Id=s.ArticleId where s.SoNumberId="'.$SOID.'" and s.ArticleId in (select ArticleId from outward where OutwardNumberId="'.$OWNO.'") group by Id';
		//echo 'select * from (SELECT s.Id, s.SoNumberId, s.ArticleId, s.NoPacks, s.OutwardNoPacks, a.ArticleNumber, (select Id from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as OId, (select NoPacks from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as NoPacks1 FROM `so` s inner join outwardnumber own on own.SoId=s.SoNumberId inner join article a on a.Id=s.ArticleId where s.SoNumberId="2" and s.ArticleId in (select ArticleId from outward where OutwardNumberId="4")) as d where OId IS NOT NULL group by Id';
		//$solist = DB::select('SELECT s.Id, s.SoNumberId, s.ArticleId, s.OutwardNoPacks, a.ArticleNumber, (select Id from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as OId,(select NoPacks from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as NoPacks FROM `so` s inner join outwardnumber own on own.SoId=s.SoNumberId inner join article a on a.Id=s.ArticleId where s.SoNumberId="'.$SOID.'" and s.ArticleId in (select ArticleId from outward where OutwardNumberId="'.$OWNO.'") group by Id');
		//
		
		$checkdata = DB::select('SELECT count(*) as TotalRow FROM `outwardnumber` otn inner join outward o on o.OutwardNumberId=otn.Id inner join salesreturn s on s.OutwardId=o.Id where otn.Id="'.$OWNO.'"');
		if($checkdata[0]->TotalRow > 0){
			//echo "More then zero";
			return response()->json(array("id"=>"", "AssignSalseReturn"=>"true"), 200);
		}else{
			/* echo "else";
			echo "\n";
			echo "OWNO - ".$OWNO;
			echo "\n";
			echo "SOID - ".$SOID;
			exit;
			 */
			DB::beginTransaction();
			try {
				$solist = DB::select('select * from (SELECT s.Id, s.SoNumberId, s.ArticleId, s.OutwardNoPacks, a.ArticleNumber, (select Id from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as OId, (select NoPacks from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as NoPacks FROM `so` s inner join outwardnumber own on own.SoId=s.SoNumberId inner join article a on a.Id=s.ArticleId where s.SoNumberId="'.$SOID.'" and s.ArticleId in (select ArticleId from outward where OutwardNumberId="'.$OWNO.'")) as d where OId IS NOT NULL group by Id');
				//echo "<pre>"; print_r($solist); exit;
				foreach($solist as $vl){
					$ArticleId = $vl->ArticleId;
					$search = $vl->NoPacks;
					$SoId = $vl->SoNumberId;
					$OId = $vl->OId;
					$OutwardNoPacks = $vl->OutwardNoPacks;

					$searchString = ',';
					$AddSalesNoPacks = "";
					if( strpos($search, $searchString) !== false ) {
						$string = explode(',', $search);
						$OutwardNoPacks = explode(',', $OutwardNoPacks);
						
						
						foreach($string as $key => $vl){
							$AddSalesNoPacks .= ($OutwardNoPacks[$key] + $vl).",";
						}
						//$AddSalesNoPacks = rtrim($AddSalesNoPacks,',');
					}else{
						$AddSalesNoPacks .= ($search + $OutwardNoPacks).",";
					}


					$AddSalesNoPacks = rtrim($AddSalesNoPacks,',');
					//echo "Add -> ".$AddSalesNoPacks;
					DB::table('outward')
					->where('Id', '=', $OId)
					->delete();
					
					DB::table('outwardpacks')
					->where('OutwardId', '=', $OId)
					->delete();
					
					DB::table('so')
					->where('ArticleId', $ArticleId)
					->where('SoNumberId', $SoId)
					->update(['OutwardNoPacks' => $AddSalesNoPacks, 'Status'=>0]);
							
				}
			
			    DB::table('outwardnumber')
				->where('Id', '=', $OWNO)
				->delete();
				
				DB::table('transportoutlet')
				->where('OutwardNumberId', '=', $OWNO)
				->delete();
				
				DB::commit();
				return response()->json(array("Id"=>"SUCCESS"), 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json(array("Id"=>""), 200);
			}
		}
	}
	
    public function UpdateOutward(Request $request)
     {
        $data = $request->all();
		//echo "<pre>"; print_r($data); exit;
		//SELECT s.Id, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, o.NoPacks, s.OutwardNoPacks FROM `outward` o inner join outwardnumber own on own.Id=o.OutwardNumberId inner join article a on o.ArticleId=a.Id inner join so s on s.SoNumberId=own.SoId where o.Id=3 and s.ArticleId=4
		//SELECT c.Colorflag, o.NoPacks as OWNopacks, s.OutwardNoPacks as TotalNoPacks FROM `outward` o inner join outwardnumber own on own.Id=o.OutwardNumberId inner join article a on o.ArticleId=a.Id inner join so s on s.SoNumberId=own.SoId inner join po p on p.ArticleId=o.ArticleId inner join category c on c.Id=p.CategoryId where o.Id=3 and s.ArticleId=4
		//SELECT c.Colorflag, o.NoPacks as OWNopacks, s.OutwardNoPacks as TotalNoPacks FROM `outward` o inner join outwardnumber own on own.Id=o.OutwardNumberId inner join so s on s.SoNumberId=own.SoId inner join po p on p.ArticleId=o.ArticleId inner join category c on c.Id=p.CategoryId where o.Id=3 and s.ArticleId=4
		//echo 'SELECT c.Colorflag, o.NoPacks as OWNopacks, s.OutwardNoPacks FROM `outward` o inner join outwardnumber own on own.Id=o.OutwardNumberId inner join so s on s.SoNumberId=own.SoId inner join po p on p.ArticleId=o.ArticleId inner join category c on c.Id=p.CategoryId where o.Id="'.$data['id'].'" and s.ArticleId="'.$data['ArticleId'].'"';
		$dataresult= DB::select('SELECT c.Colorflag, o.NoPacks as OWNopacks, s.OutwardNoPacks FROM `outward` o inner join outwardnumber own on own.Id=o.OutwardNumberId inner join so s on s.SoNumberId=own.SoId left join po p on p.ArticleId=o.ArticleId left join article a on a.Id=o.ArticleId left join category c on c.Id=a.CategoryId where o.Id="'.$data['id'].'" and s.ArticleId="'.$data['ArticleId'].'"'); 
		$Colorflag = $dataresult[0]->Colorflag;
		$OutwardSalesNoPacks = $dataresult[0]->OutwardNoPacks;
		$OWNopacks = $dataresult[0]->OWNopacks;
		
		if( strpos($OWNopacks, ',') !== false ) {
			$OutwardSalesNoPacks = explode(',', $OutwardSalesNoPacks);
			$OWNopacks = explode(',', $OWNopacks);
			$stringcomma = 1;
		}else{
			$stringcomma = 0;
		}
		
		$updateNoPacks = "";
		$UpdateInwardNoPacks = "";
		if($Colorflag==1){
			foreach($data['ArticleSelectedColor'] as $key => $vl){
				$numberofpacks = $vl["Id"];
				$sosale = $OutwardSalesNoPacks[$key];
				$outwardsale = $OWNopacks[$key];
				$getnopacks = $data["NoPacksNew_".$numberofpacks];
				
				if($data["NoPacksNew_".$numberofpacks]!=""){
					if($stringcomma==1){
						/* echo "asdasd";
echo $inwardsale."\n";
	echo $sosale."\n";					exit; */
						$total = ($sosale + $outwardsale);
						//echo $total; exit;
						if($total<$getnopacks){
							return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
						}
						
						$updateNoPacks .= $getnopacks.",";
						
						//3<4
						if($outwardsale<$getnopacks){
							$UpdateInwardNoPacks .= ($sosale - ($getnopacks - $outwardsale)).",";
						}else if($outwardsale == $getnopacks){
							$UpdateInwardNoPacks .= $sosale.",";
						}else{
							if($sosale==0){
								$UpdateInwardNoPacks .= ($outwardsale - $getnopacks).",";
							}else{
								$UpdateInwardNoPacks .= ($sosale + ($outwardsale - $getnopacks)).",";
							}
						}
						
					}else{
						$total = $OutwardSalesNoPacks + $OWNopacks;
						
						if($total<$getnopacks){
							return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
						}
						
						if($OWNopacks<$getnopacks){
							$UpdateInwardNoPacks .= ($OutwardSalesNoPacks - ($getnopacks - $OWNopacks)).",";
						}else if($outwardsale == $getnopacks){
							$UpdateInwardNoPacks .= $OutwardSalesNoPacks.",";
						}else{
							if($sosale==0){
								$UpdateInwardNoPacks .= ($OWNopacks - $getnopacks).",";
							}else{
								$UpdateInwardNoPacks .= ($OutwardSalesNoPacks + ($OWNopacks - $getnopacks)).",";
							}
						}
						
						$updateNoPacks .= $getnopacks.",";
						//$UpdateInwardNoPacks .= $total;
					}
					//$TotalNoPacks .= $data["NoPacksNew_".$numberofpacks].",";
				}
				else{
					$updateNoPacks .= "0,";
					$UpdateInwardNoPacks .= ($sosale + $outwardsale).",";
				}
			}
		} else{
			$updateNoPacks .= $data['NoPacksNew'].",";
			$total = $OutwardSalesNoPacks + $OWNopacks;
			if($total<$data["NoPacksNew"]){
				return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
			}
			
			if($OWNopacks<$data['NoPacksNew']){
				$UpdateInwardNoPacks .= ($OutwardSalesNoPacks - ($data['NoPacksNew'] - $OWNopacks)).",";
			}else if($OWNopacks == $data['NoPacksNew']){
				$UpdateInwardNoPacks .= $OutwardSalesNoPacks.",";
			}else{
				if($OutwardSalesNoPacks==0){
					$UpdateInwardNoPacks .= ($OWNopacks - $data['NoPacksNew']).",";
				}else{
					$UpdateInwardNoPacks .= ($OutwardSalesNoPacks + ($OWNopacks - $data['NoPacksNew'])).",";
				}
			}
		}
		
		$updateNoPacks = rtrim($updateNoPacks,',');
		$UpdateInwardNoPacks = rtrim($UpdateInwardNoPacks,',');
		
		$CheckupdateNoPacks = explode(',', $updateNoPacks);
		$tmp = array_filter($CheckupdateNoPacks);
		if (empty($tmp)) {
			//echo "All zeros!";
			return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
		}
		
		/* echo $UpdateInwardNoPacks."\n";
		echo $data['ArticleId']."\n";
		echo $data['SoId']."\n";
		echo "<pre>"; print_r($data); exit; */
		//echo "OutwardNoPacks: ".$UpdateInwardNoPacks."\n\n";
		//echo "OWNopacks: ".$OWNopacks."\n\n";
		//exit;
		if(isset($data['OutwardWeight'])){
			$OutwardWeight = $data['OutwardWeight'];
		}else{
			$OutwardWeight =  "";
		}
			
		DB::beginTransaction();
		try {
			DB::table('so')
				->where('ArticleId', $data['ArticleId'])
				->where('SoNumberId', $data['SoId'])
				->update(['OutwardNoPacks' => $UpdateInwardNoPacks]);
			
			DB::table('outwardnumber')
            ->where('Id', $data['OutwardNumberId'])
            ->update(['OutwardDate'=>$data['OutwardDate'],'GSTAmount' =>$data['GST'],'GSTPercentage'=>$data['GST_Percentage'], 'Discount'=>$data['Discount'], 'Remarks'=>$data['Remarks']]);

		
			Outward::where('id', $data['id'])->update(array(
				 'NoPacks' => $updateNoPacks,
				 'PartyDiscount' => $data['PartyDiscount'],
				 'OutwardBox' => $data['OutwardBox'],
				 'OutwardRate' => $data['OutwardRate'],
				 'OutwardWeight' => $OutwardWeight
			 ));
			
			$checknopacks = DB::select("select OutwardNoPacksCheck(".$data['SoId'].",".$data['ArticleId'].") as OutwardNoPacksCheck");
			if($checknopacks[0]->OutwardNoPacksCheck==0){
				DB::table('so')
				->where('ArticleId', $data['ArticleId'])
				->where('SoNumberId', $data['SoId'])
				->update(['Status' => 0]);
			}else{
				DB::table('so')
				->where('ArticleId', $data['ArticleId'])
				->where('SoNumberId', $data['SoId'])
				->update(['Status' => 1]);
			}
			
			if($data['ArticleOpenFlag']==0){
				if( strpos($updateNoPacks, ',') !== false ) {
					$updateNoPacks = explode(',', $updateNoPacks);
					foreach($data['ArticleSelectedColor'] as $key => $vl){
						$numberofpacks = $vl["Id"];
						$res = DB::select("select Id from outwardpacks where ColorId='".$numberofpacks."' and OutwardId='".$data['id']."' and ArticleId='".$data['ArticleId']."'");
						DB::table('outwardpacks')
						->where('Id', $res[0]->Id)
						->update(['NoPacks'=>$updateNoPacks[$key], 'UpdatedDate'=>date('Y-m-d H:i:s')]);
					}
				}else{
					foreach($data['ArticleSelectedColor'] as $key => $vl){
						$numberofpacks = $vl["Id"];
						$res = DB::select("select Id from outwardpacks where ColorId='".$numberofpacks."' and OutwardId='".$data['id']."' and ArticleId='".$data['ArticleId']."'");
						DB::table('outwardpacks')
						->where('Id', $res[0]->Id)
						->update(['NoPacks'=>$updateNoPacks, 'UpdatedDate'=>date('Y-m-d H:i:s')]);
					}
				}
			}else{
				DB::table('outwardpacks')
					->where('OutwardId', $data['id'])
					->where('ArticleId', $data['ArticleId'])
					->update(['NoPacks'=>$updateNoPacks, 'UpdatedDate'=>date('Y-m-d H:i:s')]);
			}
			DB::commit();
			return response()->json("SUCCESS", 200);
		} catch (\Exception $e) {
			DB::rollback();
			return response()->json("", 200);
		}
		
		
		//echo "TotalNoPacks: ".$TotalNoPacks."\n";
		/* echo "InwardSalesNoPacks: ".$InwardSalesNoPacks."\n";
		echo "SONopacks: ".$SONopacks."\n";
		exit; */
		
        /*  $data = $request->all();
         Outward::where('id', $data['id'])->update(array(
                 'SoId' 	  =>  $data['SoId'],
                 'NumPacks' =>  $data['NumPacks'],
                 'Amount' =>  $data['Amount'],
                 'GSTParcentes' =>  $data['GSTParcentes'],
                 'GST' =>  $data['GST']
             ));
         return response()->json("SUCCESS", 200); */
     }
    
	
	//SELECT s.Id, s.ArticleId, s.Status, sn.SoNumber, sn.PartyId, sn.Transporter, sn.UserId FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId where status in (0) group by SoNumberId
	//SELECT s.SoNumberId, sn.SoNumber, s.ArticleId, s.Status, sn.PartyId, sn.Transporter, sn.UserId FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId where status in (0) group by SoNumberId
	//SELECT s.ArticleId, sn.PartyId, sn.Transporter, sn.Destination, s.NoPacks, s.OutwardNoPacks, art.ArticleNumber, art.ArticleRate, art.ArticleColor, art.ArticleSize, art.ArticleRatio FROM `so` s inner join article art on art.Id=s.ArticleId inner join sonumber sn on sn.Id=s.SoNumberId where s.SoNumberId = 13
	public function RemainingSO(){
		return  DB::select("SELECT s.SoNumberId, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber , s.ArticleId, s.Status, sn.PartyId, sn.Transporter, sn.UserId FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=sn.FinancialYearId where status in (0) group by SoNumberId order by SoNumber ASC");
	}
	
	public function RemainingOutwardSO(){
		//return  DB::select("SELECT s.SoNumberId, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber , s.ArticleId, s.Status, sn.PartyId, sn.Transporter, sn.UserId FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=sn.FinancialYearId where status in (0) group by SoNumberId");
		return  DB::select("SELECT p.Name, s.SoNumberId, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber , s.ArticleId, s.Status, sn.PartyId, sn.Transporter, sn.UserId, sn.SoDate FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join party p on p.Id=sn.PartyId where s.status in (0) group by SoNumberId");
	}

	public function RemainingPostOutwardSO(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
		
		$vnddataTotal = DB::select("select count(*) as Total from(SELECT p.Name, s.SoNumberId, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber , s.ArticleId, s.Status, sn.PartyId, sn.Transporter, sn.UserId, sn.SoDate FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join party p on p.Id=sn.PartyId where s.status in (0) group by SoNumberId)as d");
		$vntotal = $vnddataTotal[0]->Total;
		$length = $data["length"];
		
		if($search['value'] != null && strlen($search['value']) > 2){
			$searchstring = " WHERE d.Name like '%".$search['value']."%' Or d.SoNumber like '%".$search['value']."%' OR d.ArticleNumber like '%".$search['value']."%' OR cast(d.SoDate as char) like '%".$search['value']."%'";
			//$vnddataTotalFilter = DB::select("select count(*) as Total from(SELECT p.Name, s.SoNumberId, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber , s.ArticleId, s.Status, sn.PartyId, sn.Transporter, sn.UserId, sn.SoDate FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join party p on p.Id=sn.PartyId where s.status in (0) group by SoNumberId)as d ".$searchstring);
			$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNumber, p.Name, s.SoNumberId, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber , s.ArticleId, s.Status, sn.PartyId, sn.Transporter, sn.UserId, DATE_FORMAT(sn.SoDate, \"%d/%m/%Y\") as SoDate FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join party p on p.Id=sn.PartyId inner join article a on a.Id=s.ArticleId where s.status in (0) group by SoNumberId) as d ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "";
			$vnddataTotalFilterValue = $vntotal;
		}
		
	  $column = $data["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "d.Name";
				break;
			case 2:
				$ordercolumn = "d.SoNumber";
				break;	
			case 6:
				$ordercolumn = "date(d.sdate)";
				break;	
			default:
				$ordercolumn = "d.Name";
				break;
		}
		
		$order = "";
		if($data["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data["order"][0]["dir"];
		}
		
		
		
		//$vnddata = DB::select("select d.* from(SELECT GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNumber, GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY a.Id SEPARATOR ',') as AllArticleId, CountNoPacks(GROUP_CONCAT(CONCAT(s.NoPacks) ORDER BY a.Id SEPARATOR ',')) as TotalNoPacks, p.Name, s.SoNumberId, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber , s.ArticleId, s.Status, sn.PartyId, sn.Transporter, sn.UserId, sn.SoDate FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join party p on p.Id=sn.PartyId inner join article a on a.Id=s.ArticleId where s.status in (0) group by SoNumberId) as d ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		$vnddata = DB::select("select d.* from(SELECT GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNumber, GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY a.Id SEPARATOR ',') as AllArticleId, GROUP_CONCAT(CONCAT(s.Id) ORDER BY a.Id SEPARATOR ',') as sIds, CountNoPacks(GROUP_CONCAT(CONCAT(s.NoPacks) ORDER BY a.Id SEPARATOR ',')) as TotalNoPacks, p.Name, s.SoNumberId, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber , s.ArticleId, s.Status, sn.PartyId, sn.Transporter, sn.UserId, sn.SoDate as sdate, DATE_FORMAT(sn.SoDate, \"%d/%m/%Y\") as SoDate FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join party p on p.Id=sn.PartyId inner join article a on a.Id=s.ArticleId where s.status in (0) group by SoNumberId) as d ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		
		
		foreach($vnddata as $vl){
			$TotalSendNoPacks = 0;
			$TotalRemainingNoPacks = 0;
			$TotalNoPacks = 0;
				$object = (object)$vl;
				$getsochallen = DB::select("select s.Id, u.Name as UserName, pt.Name, pt.Address, pt.PhoneNumber, pt.GSTNumber, son.SoDate, son.GSTAmount, son.GSTPercentage, son.GSTType, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber, son.Transporter, son.Destination, son.Remarks, s.NoPacks, art.Id as ArticleId, art.ArticleOpenFlag, art.ArticleNumber, c.Title, c.Colorflag, art.ArticleColor, art.ArticleSize, art.ArticleRate, s.ArticleRate as SoArticleRate, art.ArticleRatio from so s inner join sonumber son on son.Id=s.SoNumberId inner join users u on u.Id=son.UserId inner join financialyear fn on fn.Id=son.FinancialYearId inner join article art on art.Id=s.ArticleId left join po p on p.ArticleId=art.Id left join purchasenumber pur on pur.Id=p.PO_Number left join category c on c.Id=art.CategoryId inner join party pt on pt.Id=son.PartyId where s.SoNumberId='" . $object->SoNumberId . "' group by s.Id order by s.Id ASC");
		//echo "<pre>"; print_r($getsochallen); exit;
		$challendata = [];
		$TotalNoPacks = 0;
		$TotalAmount = 0;
		$TotalQuantityPic = 0;
		$TotalSendNoPacks = 0;
		$TotalRemainingNoPacks = 0;
		if(date('m') >= 06) {
		   $d = date('Y-m-d', strtotime('+1 years'));
		   $FinanceYear =  date('y') .'-'.date('y', strtotime($d));
		} else {
		  $d = date('Y-m-d', strtotime('-1 years'));
		  $FinanceYear =  date('y', strtotime($d)).'-'.date('y');
		}

		foreach($getsochallen as $vl){
			//echo $vl->InwardDate;
			$Name = $vl->Name;
			$UserName = $vl->UserName;
			$Address = $vl->Address;
			$PhoneNumber = $vl->PhoneNumber;
			$GSTNumber = $vl->GSTNumber;
			$ArticleNumber = $vl->ArticleNumber;
			$ArticleOpenFlag = $vl->ArticleOpenFlag;
			$Title = $vl->Title;
			$SoDate = $vl->SoDate;
			$SoNumber = $vl->SoNumber;
			$Transporter = $vl->Transporter;
			$Destination = $vl->Destination;
			$Remarks = $vl->Remarks;
			$NoPacks = $vl->NoPacks;
			$Colorflag = $vl->Colorflag;
			$ArticleId = $vl->ArticleId;
			$GSTAmount = $vl->GSTAmount;
			$GSTPercentage = $vl->GSTPercentage;
			$GSTType = $vl->GSTType;
			
			
			$getart = $this->GetSOArticleData($object->SoNumberId, $ArticleId, 0);
			$RemainingNoPacks = $getart[0]->SalesNoPacks;
			
			$SendNoPacks  = "";
			
			if( strpos($RemainingNoPacks, ',') !== false ) {
				$remnopack = explode(',', $RemainingNoPacks);
				$nopackcheck = explode(',', $NoPacks);
				
				
				foreach($remnopack as $key => $rvl){
					$SendNoPacks .= ($nopackcheck[$key] - $rvl).',';
				}
				$SendNoPacks = rtrim($SendNoPacks, ',');
				$TotalSendNoPacks+= array_sum(explode(",",$SendNoPacks));
				$TotalRemainingNoPacks+= array_sum(explode(",",$RemainingNoPacks));
			}else{
				$SendNoPacks = ($NoPacks - $RemainingNoPacks);
				$TotalSendNoPacks+= $SendNoPacks;
				$TotalRemainingNoPacks+= $RemainingNoPacks;
			}
			
			
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
		}
		
		//"TotalNoPacks"=>$TotalNoPacks, "TotalSendNoPacks" =>$TotalSendNoPacks, "TotalRemainingNoPacks" =>$TotalRemainingNoPacks,
		$object->TotalNoPacks = $TotalNoPacks;
		$object->TotalSendNoPacks = $TotalSendNoPacks;
		$object->TotalRemainingNoPacks = $TotalRemainingNoPacks;		
				
				
				
				
				
				
				
				
				
				///$object->AllArticleId = 1111; 
				/* if( strpos($object->AllArticleId, ',') !== true ) {
					$AllArticleId = explode(",",$object->AllArticleId);
					$sIds = explode(",",$object->sIds);
					foreach($AllArticleId as $key1 => $vl1){
						//return $vl1;
						//return $sIds[$key1];
						
						$getart = $this->SOGetSOArticleData($object->SoNumberId, $vl1, 0);
						//return $getart; exit;
						$RemainingNoPacks = $getart[0]->SalesNoPacks;
			
						$SendNoPacks  = "";
						$getsochallen = DB::select("SELECT NoPacks  FROM `so` WHERE `Id` = '".$sIds[$key1]."'");
						if( strpos($RemainingNoPacks, ',') !== false ) {
							//return $getsochallen; exit;
							$remnopack = explode(',', $RemainingNoPacks);
							$nopackcheck = explode(',', $getsochallen[0]->NoPacks);
							$TotalNoPacks+= array_sum(explode(",",$getsochallen[0]->NoPacks));
							
							foreach($remnopack as $key => $rvl){
								$SendNoPacks .= ($nopackcheck[$key] - $rvl).',';
							}
							$SendNoPacks = rtrim($SendNoPacks, ',');
							$TotalSendNoPacks+= array_sum(explode(",",$SendNoPacks));
							$TotalRemainingNoPacks+= array_sum(explode(",",$RemainingNoPacks));
						}else{
							$TotalNoPacks+= $getsochallen[0]->NoPacks;
							$SendNoPacks = ($getsochallen[0]->NoPacks - $RemainingNoPacks);
							$TotalSendNoPacks+= $SendNoPacks;
							$TotalRemainingNoPacks+= $RemainingNoPacks;
						}
					}
					
					$object->TotalNoPacks = $TotalNoPacks;
					$object->TotalSendNoPacks = $TotalSendNoPacks;
					$object->TotalRemainingNoPacks = $TotalRemainingNoPacks;
					
				} else{
					//$np = $SalesNoPacks;
					
					$getart = $this->SOGetSOArticleData($object->SoNumberId, $object->AllArticleId, 0);
						$RemainingNoPacks = $getart[0]->SalesNoPacks;
						$SendNoPacks  = "";
						$getsochallen = DB::select("SELECT NoPacks  FROM `so` WHERE `Id` = '".$object->sIds."'");
						
						if( strpos($RemainingNoPacks, ',') !== false ) {
							$remnopack = explode(',', $RemainingNoPacks);
							$nopackcheck = explode(',', $getsochallen[0]->NoPacks);
							
							
							foreach($remnopack as $key => $rvl){
								$SendNoPacks .= ($nopackcheck[$key] - $rvl).',';
							}
							$SendNoPacks = rtrim($SendNoPacks, ',');
							$TotalSendNoPacks+= array_sum(explode(",",$SendNoPacks));
							$TotalRemainingNoPacks+= array_sum(explode(",",$RemainingNoPacks));
							$TotalNoPacks+= array_sum(explode(",",$getsochallen[0]->NoPacks));
						}else{
							$TotalNoPacks+= $getsochallen[0]->NoPacks;
							$SendNoPacks = ($getsochallen[0]->NoPacks - $RemainingNoPacks);
							$TotalSendNoPacks+= $SendNoPacks;
							$TotalRemainingNoPacks+= $RemainingNoPacks;
							
						}
						
					$object->TotalNoPacks = $TotalNoPacks;
					$object->TotalSendNoPacks = $TotalSendNoPacks;
					$object->TotalRemainingNoPacks = $TotalRemainingNoPacks;
				} */
				/* $getart = $this->GetSOArticleData($id, $ArticleId, 0);
				
				$RemainingNoPacks = $getart[0]->SalesNoPacks;
				$CheckSalesNoPacks = $getart[0]->CheckSalesNoPacks;
				
				$object->RemainingNoPacks = $RemainingNoPacks;
				$object->CheckSalesNoPacks = $CheckSalesNoPacks; */
		}
		//return $vnddata; exit;
		
		//"TotalSendNoPacks" =>$TotalSendNoPacks, "TotalRemainingNoPacks" =>$TotalRemainingNoPacks,
		return array(
				'datadraw'=>$data["draw"],
				'recordsTotal'=>$vntotal,
				'recordsFiltered'=>$vnddataTotalFilterValue,
				'response' => 'success',
				'startnumber' => $startnumber,
				'search'=>count($vnddata),
				'data' => $vnddata,
			);
	}
	
	
	public function GetSOData($OWNO, $Id){
		//return $Id;
		$basicdetails = DB::select("SELECT s.PartyId, s.GSTAmount, s.GSTPercentage, p.Discount as PartyDiscount, p.GSTType, s.Transporter, s.Destination, s.Remarks FROM sonumber s inner join party p on p.Id=s.PartyId where s.Id = '".$Id."'");
		//$allarticles = DB::select("select s.ArticleId, a.ArticleNumber from so s inner join article a on a.Id=s.ArticleId  where s.SoNumberId='".$Id."'");
		//select * from (select s.ArticleId, a.ArticleNumber, OutwardNoPacksCheck(sn.Id, s.ArticleId) as OutwardNoPacksCheck from so s inner join article a on a.Id=s.ArticleId inner join sonumber sn on sn.Id=s.SoNumberId where s.SoNumberId=19) as d where OutwardNoPacksCheck=0
// 		$allarticles = DB::select("select * from (select s.ArticleId, a.ArticleNumber, OutwardNoPacksCheck('".$Id."', s.ArticleId) as OutwardNoPacksCheck from so s inner join article a on a.Id=s.ArticleId  where s.SoNumberId='".$Id."') as d where d.OutwardNoPacksCheck=0");
		//return $Id; exit;
		$allarticles = DB::select("select * from (select s.ArticleId, a.ArticleNumber from so s inner join article a on a.Id=s.ArticleId  where s.SoNumberId='".$Id."') as d ");
		$checkdata = DB::select('SELECT count(*) as TotalRow FROM `outwardnumber` otn inner join outward o on o.OutwardNumberId=otn.Id inner join salesreturn s on s.OutwardId=o.Id where otn.Id="'.$OWNO.'"');
		if($checkdata[0]->TotalRow > 0){
			$Outwardstatus = true;
		}else {
			$Outwardstatus = false;
		}
		$arraydata =array("BasicDetails"=>$basicdetails, "Articles"=>$allarticles, "Outwardstatus"=>$Outwardstatus);
		
		return $arraydata;
		//echo "<pre>"; print_r($arraydata); exit;
		//return DB::select("SELECT s.ArticleId, sn.PartyId, s.Transporter, s.Destination, s.NoPacks, s.OutwardNoPacks, art.ArticleNumber, art.ArticleRate, art.ArticleColor, art.ArticleSize, art.ArticleRatio FROM `so` s inner join article art on art.Id=s.ArticleId inner join sonumber sn on sn.Id=s.SoNumberId where s.SoNumberId = '". $Id ."'");
	}
	
	public function SOGetSOArticleData($SOId, $Id, $OWID){
		return DB::select("SELECT s.NoPacks, s.OutwardNoPacks as SalesNoPacks, CommaZeroValue(s.OutwardNoPacks) as CheckSalesNoPacks  FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=p.CategoryId inner join so s on s.ArticleId=art.Id inner join articlerate artr on artr.ArticleId=art.Id where art.Id='". $Id ."' and s.SoNumberId='". $SOId ."'");
	}
	
	public function GetSOArticleData($SOId, $Id, $OWID){
		//echo "SELECT art.ArticleOpenFlag, art.ArticleNumber, art.ArticleRate, art.ArticleRatio, art.ArticleSize, art.ArticleColor, c.Colorflag, s.NoPacks, s.OutwardNoPacks as SalesNoPacks, o.OutwardBox, o.OutwardRate, o.OutwardWeight FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=p.CategoryId inner join outward o on o.Id=".$OWID." inner join so s on s.ArticleId=art.Id where art.Id='". $Id ."' and s.SoNumberId='". $SOId ."'";
		//exit;
		if($OWID==0){
			return DB::select("SELECT art.Id as ArticleId, art.ArticleOpenFlag, art.ArticleNumber, s.ArticleRate, art.ArticleRatio, art.ArticleSize, art.ArticleColor, c.Colorflag, s.NoPacks, s.OutwardNoPacks as SalesNoPacks FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=art.CategoryId inner join so s on s.ArticleId=art.Id inner join articlerate artr on artr.ArticleId=art.Id where art.Id='". $Id ."' and s.SoNumberId='". $SOId ."'");
		}else{
			return DB::select("SELECT art.Id as ArticleId, art.ArticleOpenFlag, art.ArticleNumber, s.ArticleRate, art.ArticleRatio, art.ArticleSize, art.ArticleColor, c.Colorflag, s.NoPacks, s.OutwardNoPacks as SalesNoPacks, o.OutwardBox, o.OutwardRate, o.OutwardWeight, o.PartyDiscount FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=art.CategoryId inner join outward o on o.Id=".$OWID." inner join so s on s.ArticleId=art.Id where art.Id='". $Id ."' and s.SoNumberId='". $SOId ."'");
		}
		
		//return DB::select("SELECT c.Colorflag, s.NoPacks, s.OutwardNoPacks as SalesNoPacks, art.ArticleNumber, art.ArticleRate, art.ArticleColor, art.ArticleSize, art.ArticleRatio FROM `so` s inner join article art on art.Id=s.ArticleId inner join sonumber sn on sn.Id=s.SoNumberId inner join po p on p.ArticleId=art.Id inner join category c on c.Id=p.CategoryId where s.SoNumberId = '". $SOId ."' and s.ArticleId='". $Id ."'");
	}
	
	/* public function OutwardDataCheckUserWise($UserId, $OWNO)
     {
		$userrole = DB::select("SELECT Role FROM `users`where Id='".$UserId."'");
		if($userrole[0]->Role==2){
			$wherecustom ="";
		}else{
			$wherecustom ="UserId='". $UserId ."' and ";
		}
		
		$checkdata = DB::select("SELECT count(*) as TotalRow FROM `outwardnumber` where ".$wherecustom." Id='". $OWNO ."'");
		$array = array();
		if($checkdata[0]->TotalRow>0){
			$array["Rights"] = true;
		}else{
			$array["Rights"] = false;
		}
		return response()->json($array, 200);
		//echo "<pre>"; print_r($checkdata); exit;
		// return DB::select("SELECT * FROM `sonumber` where Id='". $SONO ."'");
	 } */
	 
	public function GetOutwardChallen($id){
		// echo $id; exit;
		//select p.Name, p.Address, p.GSTNumber, d.OutwardDate,  d.OutwardNumber, d.SoNumber, d.Transporter, d.GSTAmount, d.GSTPercentage,  o.NoPacks, i.Weight, c.Title, d.* from (SELECT u.Name, sn.PartyId, sn.Transporter, s.Id, s.SoNumberId,sn.SoNumber, s.ArticleId, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, own.OutwardNumber, own.OutwardDate, own.GSTAmount, own.GSTPercentage, (select Id from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as OId, (select NoPacks from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as NoPacks FROM `so` s inner join outwardnumber own on own.SoId=s.SoNumberId inner join article a on a.Id=s.ArticleId inner join sonumber sn on sn.Id=s.SoNumberId inner join users u on u.Id=sn.UserId where s.SoNumberId="1" and s.ArticleId in (select ArticleId from outward where OutwardNumberId="5")) as d inner join outward o on o.Id=OId inner join inward i on i.ArticleId=d.ArticleId inner join po po on po.ArticleId=d.ArticleId inner join category c on c.Id=po.CategoryId inner join party p on p.Id=d.PartyId where OId IS NOT NULL group by d.Id
		//select p.Name, p.Address, p.GSTNumber, d.OutwardDate,  d.OutwardNumber, d.SoNumber, d.Transporter, d.GSTAmount, d.GSTPercentage,  o.NoPacks, i.Weight,d.* from (SELECT u.Name, sn.PartyId, sn.Transporter, s.Id, s.SoNumberId,sn.SoNumber, s.ArticleId, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, own.OutwardNumber, own.OutwardDate, own.GSTAmount, own.GSTPercentage, (select Id from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as OId, (select NoPacks from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as NoPacks FROM `so` s inner join outwardnumber own on own.SoId=s.SoNumberId inner join article a on a.Id=s.ArticleId inner join sonumber sn on sn.Id=s.SoNumberId inner join users u on u.Id=sn.UserId where s.SoNumberId="1" and s.ArticleId in (select ArticleId from outward where OutwardNumberId="5")) as d inner join outward o on o.Id=OId inner join inward i on i.ArticleId=d.ArticleId inner join party p on p.Id=d.PartyId where OId IS NOT NULL group by d.Id
		//select d.*, o.NoPacks, i.Weight, p.Name, p.Address, p.GSTNumber from (SELECT u.Name, sn.PartyId, sn.Transporter, s.Id, s.SoNumberId,sn.SoNumber, s.ArticleId, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, (select Id from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as OId, (select NoPacks from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as NoPacks FROM `so` s inner join outwardnumber own on own.SoId=s.SoNumberId inner join article a on a.Id=s.ArticleId inner join sonumber sn on sn.Id=s.SoNumberId inner join users u on u.Id=sn.UserId where s.SoNumberId="1" and s.ArticleId in (select ArticleId from outward where OutwardNumberId="5")) as d inner join outward o on o.Id=OId inner join inward i on i.ArticleId=d.ArticleId inner join party p on p.Id=d.PartyId where OId IS NOT NULL group by d.Id
		//select d.*, o.NoPacks, i.Weight from (SELECT u.Name, sn.Transporter, s.Id, s.SoNumberId,sn.SoNumber, s.ArticleId, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, (select Id from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as OId, (select NoPacks from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as NoPacks FROM `so` s inner join outwardnumber own on own.SoId=s.SoNumberId inner join article a on a.Id=s.ArticleId inner join sonumber sn on sn.Id=s.SoNumberId inner join users u on u.Id=sn.UserId where s.SoNumberId="1" and s.ArticleId in (select ArticleId from outward where OutwardNumberId="5")) as d inner join outward o on o.Id=OId inner join inward i on i.ArticleId=d.ArticleId where OId IS NOT NULL group by d.Id	
		//select d.*, o.NoPacks from (SELECT u.Name, sn.Transporter, s.Id, s.SoNumberId,sn.SoNumber, s.ArticleId, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, (select Id from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as OId, (select NoPacks from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as NoPacks FROM `so` s inner join outwardnumber own on own.SoId=s.SoNumberId inner join article a on a.Id=s.ArticleId inner join sonumber sn on sn.Id=s.SoNumberId inner join users u on u.Id=sn.UserId where s.SoNumberId="1" and s.ArticleId in (select ArticleId from outward where OutwardNumberId="5")) as d inner join outward o on o.Id=OId where OId IS NOT NULL group by d.Id
		//$getoutwardchallen = DB::select('select d.Name as UserName, p.Name, p.Address, p.GSTNumber, d.OutwardDate,  d.OutwardNumber, concat(FirstCharacterConcat(d.Name), d.SoNumber) as SoNumber, d.Transporter, d.GSTAmount, d.GSTPercentage,d.Discount, d.ArticleOpenFlag, d.ArticleNumber, c.Title, c.Colorflag, d.ArticleColor, d.ArticleSize, d.ArticleRate, d.ArticleRatio, o.OutwardBox, o.OutwardRate, o.OutwardWeight, o.NoPacks, i.Weight from (SELECT u.Name, sn.PartyId, sn.Transporter, s.Id, s.SoNumberId,sn.SoNumber, s.ArticleId, a.ArticleOpenFlag, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, own.OutwardNumber, own.OutwardDate, own.GSTAmount, own.GSTPercentage, own.Discount,(select Id from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as OId, (select NoPacks from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as NoPacks FROM `so` s inner join outwardnumber own on own.SoId=s.SoNumberId inner join article a on a.Id=s.ArticleId inner join sonumber sn on sn.Id=s.SoNumberId inner join users u on u.Id=sn.UserId where s.ArticleId in (select ArticleId from outward where OutwardNumberId="' . $id . '") and own.Id="' . $id . '") as d inner join outward o on o.Id=OId inner join inward i on i.ArticleId=d.ArticleId inner join po po on po.ArticleId=d.ArticleId inner join category c on c.Id=po.CategoryId inner join party p on p.Id=d.PartyId where OId IS NOT NULL group by d.Id order by o.OutwardBox ASC');
		
		//select d.Name as UserName, p.Name, p.Address, p.GSTNumber, d.OutwardDate, d.OutwardNumber1, d.OutwardNumber, concat(FirstCharacterConcat(d.Name), d.SoNumber) as SoNumber, d.Sonumber1, d.Transporter, d.GSTAmount, d.GSTPercentage,d.Discount, d.ArticleOpenFlag, d.ArticleNumber, c.Title, c.Colorflag, d.ArticleColor, d.ArticleSize, d.ArticleRate, d.ArticleRatio, o.OutwardBox, o.OutwardRate, o.OutwardWeight, o.NoPacks, i.Weight from (SELECT u.Name, sn.PartyId, sn.Transporter, s.Id, s.SoNumberId, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber1 ,sn.SoNumber, s.ArticleId, a.ArticleOpenFlag, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, concat(own.OutwardNumber, '/',fn1.StartYear,'-',fn1.EndYear) as OutwardNumber1 ,own.OutwardNumber, own.OutwardDate, own.GSTAmount, own.GSTPercentage, own.Discount,(select Id from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as OId, (select NoPacks from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as NoPacks FROM `so` s inner join outwardnumber own on own.SoId=s.SoNumberId inner join article a on a.Id=s.ArticleId inner join sonumber sn on sn.Id=s.SoNumberId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join financialyear fn1 on fn1.Id=own.FinancialYearId where s.ArticleId in (select ArticleId from outward where OutwardNumberId="3") and own.Id="3") as d inner join outward o on o.Id=OId inner join inward i on i.ArticleId=d.ArticleId inner join po po on po.ArticleId=d.ArticleId inner join category c on c.Id=po.CategoryId inner join party p on p.Id=d.PartyId where OId IS NOT NULL group by d.Id order by o.OutwardBox ASC
		//$getoutwardchallen = DB::select('select d.Name as UserName, p.Name, p.Address, p.GSTNumber, d.OutwardDate, d.OutwardNumber, d.SoNumber, d.Transporter, d.GSTAmount, d.GSTPercentage,d.Discount, d.ArticleOpenFlag, d.ArticleNumber, c.Title, c.Colorflag, d.ArticleColor, d.ArticleSize, d.ArticleRate, d.ArticleRatio, o.OutwardBox, o.OutwardRate, o.OutwardWeight, o.NoPacks, i.Weight from (SELECT u.Name, sn.PartyId, sn.Transporter, s.Id, s.SoNumberId, concat(FirstCharacterConcat(u.Name), sn.SoNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as SoNumber, s.ArticleId, a.ArticleOpenFlag, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, concat(own.OutwardNumber, \'/\',fn1.StartYear,\'-\',fn1.EndYear) as OutwardNumber, own.OutwardDate, own.GSTAmount, own.GSTPercentage, own.Discount,(select Id from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as OId, (select NoPacks from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as NoPacks FROM `so` s inner join outwardnumber own on own.SoId=s.SoNumberId inner join article a on a.Id=s.ArticleId inner join sonumber sn on sn.Id=s.SoNumberId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join financialyear fn1 on fn1.Id=own.FinancialYearId where s.ArticleId in (select ArticleId from outward where OutwardNumberId="' . $id . '") and own.Id="' . $id . '") as d inner join outward o on o.Id=OId inner join inward i on i.ArticleId=d.ArticleId inner join po po on po.ArticleId=d.ArticleId inner join category c on c.Id=po.CategoryId inner join party p on p.Id=d.PartyId where OId IS NOT NULL group by d.Id order by o.OutwardBox ASC');
		
		
		
		//$getoutwardchallen = DB::select('SELECT d.NAME AS username, p.NAME, p.address, p.gstnumber, d.outwarddate, d.outwardnumber, d.sonumber, d.transporter, d.gstamount, d.gstpercentage, d.GSTType, d.discount, d.articleopenflag, d.articlenumber, c.title, c.colorflag, d.articlecolor, d.articlesize, d.articlerate, d.articleratio, d.OutwardBox, d.OutwardRate, o.PartyDiscount, o.OutwardWeight, d.nopacks, i.weight FROM (SELECT u.NAME, sn.partyid, sn.transporter, s.id, s.sonumberid, concat(Firstcharacterconcat(u.NAME), sn.sonumber, \'/\', fn.startyear,\'-\',fn.endyear) AS sonumber, s.articleid, a.articleopenflag, a.articlenumber, a.articlerate, a.articlecolor, a.articlesize, a.articleratio, concat(own.outwardnumber, \'/\',fn1.startyear,\'-\',fn1.endyear) AS outwardnumber, own.outwarddate, own.gstamount, own.gstpercentage, own.GSTType, own.discount, ( SELECT GROUP_CONCAT(id SEPARATOR \',\') as id FROM outward WHERE outwardnumberid=own.Id and ArticleId=s.ArticleId) AS oid, ( SELECT GROUP_CONCAT(nopacks SEPARATOR \'#\')as nopacks FROM outward WHERE outwardnumberid=own.id AND articleid=s.articleid) AS nopacks, ( SELECT GROUP_CONCAT(OutwardBox SEPARATOR \'#\')as nopacks FROM outward WHERE outwardnumberid=own.id AND articleid=s.articleid) AS OutwardBox,( SELECT GROUP_CONCAT(OutwardRate SEPARATOR \'#\') as OutwardRate FROM outward WHERE outwardnumberid=own.id AND  articleid=s.articleid) AS OutwardRate FROM `so` s INNER JOIN outwardnumber own ON own.soid=s.sonumberid INNER JOIN article a ON a.id=s.articleid INNER JOIN sonumber sn ON sn.id=s.sonumberid INNER JOIN users u ON u.id=sn.userid INNER JOIN financialyear fn ON fn.id=sn.financialyearid INNER JOIN financialyear fn1 ON fn1.id=own.financialyearid WHERE s.articleid IN (SELECT articleid FROM outward WHERE OutwardNumberId="' . $id . '") and own.Id="' . $id . '") AS d INNER JOIN outward o ON o.id IN(oid) INNER JOIN inward i ON i.articleid=d.articleid INNER JOIN po po ON po.articleid=d.articleid INNER JOIN category c ON c.id=po.categoryid INNER JOIN party p ON p.id=d.partyid WHERE oid IS NOT NULL GROUP BY d.id ORDER BY o.OutwardBox ASC');
		
		
		//$getoutwardchallen = DB::select("SELECT d.NAME AS username, p.NAME, p.address, p.gstnumber, d.outwarddate, d.outwardnumber, d.GSTType, d.sonumber, d.transporter, d.gstamount, d.gstpercentage, d.discount, d.articleopenflag, d.articlenumber, c.title, c.colorflag, d.articlecolor, d.articlesize, d.articlerate, d.articleratio, d.OutwardBox, d.OutwardRate, o.PartyDiscount, o.OutwardWeight, d.nopacks, i.weight FROM (SELECT u.NAME, sn.partyid, sn.transporter, s.id, s.sonumberid, concat(Firstcharacterconcat(u.NAME), sn.sonumber, '/', fn.startyear,'-',fn.endyear) AS sonumber, s.articleid, a.articleopenflag, a.articlenumber, a.articlerate, a.articlecolor, a.articlesize, a.articleratio, concat(own.outwardnumber, '/',fn1.startyear,'-',fn1.endyear) AS outwardnumber, own.GSTType, own.outwarddate, own.gstamount, own.gstpercentage, own.discount, ( SELECT GROUP_CONCAT(id SEPARATOR ',') as id FROM outward WHERE outwardnumberid=own.Id and ArticleId=s.ArticleId) AS oid, ( SELECT GROUP_CONCAT(nopacks SEPARATOR '#')as nopacks FROM outward WHERE outwardnumberid=own.id AND articleid=s.articleid) AS nopacks, ( SELECT GROUP_CONCAT(OutwardBox SEPARATOR '#')as nopacks FROM outward WHERE outwardnumberid=own.id AND articleid=s.articleid) AS OutwardBox,( SELECT GROUP_CONCAT(OutwardRate SEPARATOR '#') as OutwardRate FROM outward WHERE outwardnumberid=own.id AND  articleid=s.articleid) AS OutwardRate FROM `so` s INNER JOIN outwardnumber own ON own.soid=s.sonumberid INNER JOIN article a ON a.id=s.articleid INNER JOIN sonumber sn ON sn.id=s.sonumberid INNER JOIN users u ON u.id=sn.userid INNER JOIN financialyear fn ON fn.id=sn.financialyearid INNER JOIN financialyear fn1 ON fn1.id=own.financialyearid WHERE s.articleid IN (SELECT articleid FROM outward WHERE OutwardNumberId='" . $id . "') and own.Id='" . $id . "') AS d INNER JOIN outward o ON o.id IN(oid) INNER JOIN inward i ON i.articleid=d.articleid INNER JOIN po po ON po.articleid=d.articleid INNER JOIN category c ON c.id=po.categoryid INNER JOIN party p ON p.id=d.partyid WHERE oid IS NOT NULL GROUP BY d.id ORDER BY o.OutwardBox ASC");
		$getoutwardchallen = DB::select("SELECT d.NAME AS username, p.NAME, p.Address, p.PhoneNumber, p.gstnumber, d.outwarddate, d.outwardnumber, d.GSTType, d.sonumber, d.transporter, d.gstamount, d.gstpercentage, d.discount, d.Remarks, d.articleopenflag, d.articlenumber, c.title, c.colorflag, d.articlecolor, d.articlesize, d.articlerate, d.articleratio, d.OutwardBox, d.OutwardRate, o.PartyDiscount, o.OutwardWeight, d.nopacks, i.weight FROM (SELECT u.NAME, sn.partyid, sn.transporter, s.id, s.sonumberid, concat(Firstcharacterconcat(u.NAME), sn.sonumber, '/', fn.startyear,'-',fn.endyear) AS sonumber, s.articleid, a.articleopenflag, a.articlenumber, a.articlerate, a.articlecolor, a.articlesize, a.articleratio, a.CategoryId, concat(own.outwardnumber, '/',fn1.startyear,'-',fn1.endyear) AS outwardnumber, own.GSTType, own.outwarddate, own.gstamount, own.gstpercentage, own.discount, own.Remarks, ( SELECT GROUP_CONCAT(id SEPARATOR ',') as id FROM outward WHERE outwardnumberid=own.Id and ArticleId=s.ArticleId) AS oid, ( SELECT GROUP_CONCAT(nopacks SEPARATOR '#')as nopacks FROM outward WHERE outwardnumberid=own.id AND articleid=s.articleid) AS nopacks, ( SELECT GROUP_CONCAT(OutwardBox SEPARATOR '#')as nopacks FROM outward WHERE outwardnumberid=own.id AND articleid=s.articleid) AS OutwardBox,( SELECT GROUP_CONCAT(OutwardRate SEPARATOR '#') as OutwardRate FROM outward WHERE outwardnumberid=own.id AND  articleid=s.articleid) AS OutwardRate FROM `so` s INNER JOIN outwardnumber own ON own.soid=s.sonumberid INNER JOIN article a ON a.id=s.articleid INNER JOIN sonumber sn ON sn.id=s.sonumberid INNER JOIN users u ON u.id=sn.userid INNER JOIN financialyear fn ON fn.id=sn.financialyearid INNER JOIN financialyear fn1 ON fn1.id=own.financialyearid WHERE s.articleid IN (SELECT articleid FROM outward WHERE OutwardNumberId='" . $id . "') and own.Id='" . $id . "') AS d INNER JOIN outward o ON o.id IN(oid) INNER JOIN inward i ON i.articleid=d.articleid left JOIN po po ON po.articleid=d.articleid INNER JOIN category c ON c.id=d.categoryid INNER JOIN party p ON p.id=d.partyid WHERE oid IS NOT NULL GROUP BY d.id ORDER BY o.OutwardBox ASC");
		//return print_r($getoutwardchallen); exit;
		$array = [];
		$PartyTotalDiscount = 0;
		//$PartyDiscount = 0;
		
		foreach($getoutwardchallen as $vl){
			//$outward = object($vl);
			//echo "<pre>"; print_r($vl);
			$object = (object)$vl;
			
			$username = $vl->username;
			$Name = $vl->NAME;
			$address = $vl->Address;
			$PhoneNumber = $vl->PhoneNumber;
			$gstnumber = $vl->gstnumber;
			$outwarddate = $vl->outwarddate;
			$outwardnumber = $vl->outwardnumber;
			$sonumber = $vl->sonumber;
			$transporter = $vl->transporter;
			$gstamount = $vl->gstamount;
			$gstpercentage = $vl->gstpercentage;
			$GSTType = $vl->GSTType;
			$discount = $vl->discount;
			$Remarks = $vl->Remarks;
			$articleopenflag = $vl->articleopenflag;
			$articlenumber = $vl->articlenumber;
			$title = $vl->title;
			$colorflag = $vl->colorflag;
			$articlecolor = $vl->articlecolor;
			$articlesize = $vl->articlesize;
			$articlerate = $vl->articlerate;
			$articleratio = $vl->articleratio;
			$OutwardBox = $vl->OutwardBox;
			$OutwardRate = $vl->OutwardRate;
			$PartyDiscount = $vl->PartyDiscount;
			$OutwardWeight = $vl->OutwardWeight;
			$nopacks = $vl->nopacks;
			$weight = $vl->weight;
			if($vl->PartyDiscount!=0){
				$PartyTotalDiscount = $vl->PartyDiscount++;	
			}
			
			
			if(strpos($OutwardBox, "#") !== false ) {
				$OutwardBox1 = explode('#', $OutwardBox);
				$OutwardRate1 = explode('#', $vl->OutwardRate);
				$nopacks1 = explode('#', $vl->nopacks);
				//$tmp = [];
				foreach($OutwardBox1 as $key => $outwarddata){
					//$srno = $Id++;
					//$tmp["SRNO"] = $srno;
					$tmp["OutwardBox"] = $outwarddata;
					$tmp["UserName"] = $username;
					$tmp["Name"] = $Name;
					$tmp["Address"] = $address;
					$tmp["PhoneNumber"] = $PhoneNumber;
					$tmp["GSTNumber"] = $gstnumber;
					$tmp["OutwardDate"] = $outwarddate;
					$tmp["OutwardNumber"] = $outwardnumber;
					$tmp["SoNumber"] = $sonumber;
					$tmp["Transporter"] = $transporter;
					$tmp["GSTAmount"] = $gstamount;
					$tmp["GSTPercentage"] = $gstpercentage;
					$tmp["GSTType"] = $GSTType;
					$tmp["Discount"] = $discount;
					$tmp["Remarks"] = $Remarks;
					$tmp["ArticleOpenFlag"] = $articleopenflag;
					$tmp["ArticleNumber"] = $articlenumber;
					$tmp["Title"] = $title;
					$tmp["Colorflag"] = $colorflag;
					$tmp["ArticleColor"] = $articlecolor;
					$tmp["ArticleSize"] = $articlesize;
					$tmp["ArticleRate"] = $articlerate;
					$tmp["ArticleRatio"] = $articleratio;
					$tmp["OutwardRate"] = $OutwardRate1[$key];
					$tmp["PartyDiscount"] = $PartyDiscount;
					$tmp["OutwardWeight"] = $OutwardWeight;
					$tmp["NoPacks"] = $nopacks1[$key];
					$tmp["Weight"] = $weight;
					array_push($array, $tmp);
					break;
				}
				
			}else{
				    
				array_push($array, array("OutwardBox"=>$OutwardBox,"UserName"=>$username,"Name"=>$Name,"Address"=>$address,"PhoneNumber"=>$PhoneNumber,"GSTNumber"=>$gstnumber,"OutwardDate"=>$outwarddate,"OutwardNumber"=>$outwardnumber,"SoNumber"=>$sonumber,"Transporter"=>$transporter,"GSTAmount"=>$gstamount,"GSTPercentage"=>$gstpercentage,"GSTType"=>$GSTType,"Discount"=>$discount,"Remarks"=>$Remarks,"ArticleOpenFlag"=>$articleopenflag,"ArticleNumber"=>$articlenumber,"Title"=>$title,"Colorflag"=>$colorflag,"ArticleColor"=>$articlecolor,"ArticleSize"=>$articlesize,"ArticleRate"=>$articlerate,"ArticleRatio"=>$articleratio,"OutwardRate"=>$OutwardRate,"PartyDiscount"=>$PartyDiscount,"OutwardWeight"=>$OutwardWeight,"NoPacks"=>$nopacks,"Weight"=>$weight));
				//array_push($array, $object);
			}
		}
		
		
		asort($array);
		$arrayorderdata = array_values($array);
		
		//return $arrayorderdata; exit;
		
		//exit;
		$challendata = [];
		$outwdbox = [];
		$TotalNoPacks = 0;
		$TotalAmount = 0;
		$TotalQuantityPic = 0;
		$TotalWeight = 0;
		$TotalQuantityInSet = 0; //jignesh
		
		
		/* $one = [];
		$one_1 = {};
		$two = [];
		$two_1 = {};
		$three = [];
		$three_1 = {}; */
		if(date('m') >= 06) {
		   $d = date('Y-m-d', strtotime('+1 years'));
		   $FinanceYear =  date('y') .'-'.date('y', strtotime($d));
		} else {
		  $d = date('Y-m-d', strtotime('-1 years'));
		  $FinanceYear =  date('y', strtotime($d)).'-'.date('y');
		}

		$result = [];
		$arrayvl = array();
		$Weight1 = 0;
		$ch = 0;
		$i = 0;
		$SRNO = 1;
		//$Weight1 = 0;
		//$i = 0;
		foreach($arrayorderdata as $vl){
			//echo $vl->InwardDate;
			$srno = $SRNO++;
			$Name = $vl["Name"];
			$UserName = $vl['UserName'];
			$Address = $vl['Address'];
			$PhoneNumber = $vl['PhoneNumber'];
			$GSTNumber = $vl['GSTNumber'];
			$OutwardDate = $vl['OutwardDate'];
			$OutwardNumber = $vl['OutwardNumber'];
			$SoNumber = $vl['SoNumber'];
			$Transporter = $vl['Transporter'];
			$Remarks = $vl['Remarks'];
			$ArticleOpenFlag = $vl['ArticleOpenFlag'];
			$ArticleNumber = $vl['ArticleNumber'];
			$Title = $vl['Title'];
			
			$NoPacks = $vl['NoPacks'];
			$ArticleRate = $vl['ArticleRate'];
			$ArticleRatio = $vl['ArticleRatio'];
			$OutwardBox = $vl['OutwardBox'];
			$OutwardRate = $vl['OutwardRate'];
			$OutwardWeight = $vl['OutwardWeight'];
			$PartyDiscount = $vl['PartyDiscount'];
			$Weight = $vl['Weight'];
			$Colorflag = $vl['Colorflag'];
			
			$GSTPercentage = $vl['GSTPercentage'];
			$GSTAmount = $vl['GSTAmount'];
			$Discount = $vl['Discount'];
			
			/* if($i == 0){
				$one[$OutwardBox] = 
			}
			$one = [];
			$two = [];
			$three = []; */
			if($Colorflag==0){
				if($ArticleOpenFlag==0){
					$task_array = json_decode($vl['ArticleColor']);
					$countcolor = count($task_array);
					$ArticleRatio = $vl['ArticleRatio'];
					if( strpos($ArticleRatio, ',') !== false ) {
						$TotalArticleRatio = array_sum(explode(",",$ArticleRatio));
					}else{
						$TotalArticleRatio = $ArticleRatio;
					}
					$TotalNoPacks+= $NoPacks;
					//$QuantityPic = $NoPacks * $countcolor * $TotalArticleRatio;
				}else{
					//$QuantityPic = $NoPacks;
				}
			} else{
				if($ArticleOpenFlag==0){
					$task_array = json_decode($vl['ArticleColor']);
					$ArticleRatio = $vl['ArticleRatio'];
					$countcolor = count($task_array);
					$TotalArticleRatio = array_sum(explode(",",$ArticleRatio));
					$countNoSet = array_sum(explode(",",$NoPacks));
					$TotalNoPacks+= array_sum(explode(",",$NoPacks));
					//$QuantityPic = $countNoSet * $TotalArticleRatio;
				}else{
					//$QuantityPic = $NoPacks;
				}
			}
			//$TotalQuantityPic += $QuantityPic;
			
			if($ArticleOpenFlag==0){
				
				if( strpos($NoPacks, ',') !== false ) {
					$countNoSet = array_sum(explode(",",$NoPacks));
					//$TotalNoPacks+= array_sum(explode(",",$NoPacks));
					$TotalArticleRatio = array_sum(explode(",",$ArticleRatio));
				}else{
					if( strpos($ArticleRatio, ',') !== false ) {
						$ArticleRatio = array_sum(explode(",",$ArticleRatio));
					}
					$countNoSet = $NoPacks;
					//$TotalNoPacks+= $NoPacks;
					$TotalArticleRatio = $ArticleRatio;
				}
				
				$Weight = $Weight * $countNoSet;
				$TotalWeight +=$Weight;
				if($PartyDiscount!=0){
					$Amount = $countNoSet * $OutwardRate;
					$DiscountAmount = (($Amount * $PartyDiscount) / 100);
					$Amount = $Amount - $DiscountAmount;
				}else{
					$Amount = $countNoSet * $OutwardRate;
				}
				
				$TotalAmount += $Amount;
				
				
				$getcolor = json_decode($vl['ArticleColor']);
				$getsize = json_decode($vl['ArticleSize']);
				
				$ArticleColor = "";
				foreach($getcolor as $vl){
					$ArticleColor .= $vl->Name.", ";
				}
				$ArticleColor = rtrim($ArticleColor,', ');
				
				$ArticleSize = "";
				foreach($getsize as $vl){
					$ArticleSize .= $vl->Name.", ";
				}
				$ArticleSize = rtrim($ArticleSize,', ');
			}else{
				$countNoSet = $NoPacks;
					
				$ArticleRatio = "";
				$ArticleRate = "";
				$ArticleColor = "";
				$ArticleSize = "";
				//$Weight = "";
				if($OutwardWeight!=""){
					$Weight =  $OutwardWeight * $countNoSet;
					//$Weight = number_format($Weight,2);
					$TotalWeight +=$Weight;
				}else{
					$Weight = "";
				}
				
				
				if($PartyDiscount!=0){
					$Amount = $NoPacks * $OutwardRate;
					$DiscountAmount = (($Amount * $PartyDiscount) / 100);
					$Amount = $Amount - $DiscountAmount;
				}else{
					$Amount = $NoPacks * $OutwardRate;
				}
				//$Amount = $NoPacks * $OutwardRate;
				$TotalAmount += $Amount;
			}
			
			if($Weight==""){
				$weightset = 0;
			}else{
				$weightset =$Weight;
			}
			if($ch == 0){
				array_push($arrayvl, $OutwardBox);
				$ch = $OutwardBox;
				$Weight1 = "";
				$Weight1 = $weightset;
			}else{
				if($ch!=$OutwardBox){
					array_push($arrayvl, $OutwardBox);
					$ch = $OutwardBox;
					$Weight1 = $weightset;
					$i++;
				}else{
					$Weight1 += $weightset;
				}
			}
			
			$qtyTotal = array_sum(explode(",",$NoPacks)); //jignesh
			$TotalQuantityInSet += $qtyTotal;
			$result[$i]['OutwardBox'] = $ch;
			$result[$i]['Weight'] = number_format($Weight1,2);
			$result[$i]['ArticleNumber'][] = $ArticleNumber;
			$result[$i]['ArticleColor'][] = $ArticleColor;
			$result[$i]['Srno'][] = $srno;
			$result[$i]['Title'][] = $Title;
			$result[$i]['ArticleRatio'][] = $ArticleRatio;
			$result[$i]['QuantityInSet'][] = $NoPacks;
			$result[$i]['ArticleRate'][] = $ArticleRate;
			$result[$i]['Amount'][] = number_format($Amount, 2);
			$result[$i]['ArticleSize'][] = $ArticleSize;
			$result[$i]['OutwardRate'][] = number_format($OutwardRate, 2);
			//$result[$i]['Weight'][] = $Weight;
			$result[$i]['Transporter'][] = $Transporter;
			$result[$i]['PartyDiscount'][] = $PartyDiscount;
			$challendata[] = json_decode(json_encode(array("UserName"=>$UserName, "OutwardDate"=>$OutwardDate,"OutwardNumber"=>$OutwardNumber,"SoNumber"=>$SoNumber,"Name"=>$Name,"Address"=>$Address, "PhoneNumber"=>$PhoneNumber,"GSTNumber"=>$GSTNumber,"Remarks"=>$Remarks, "Srno"=>$srno,"ArticleNumber"=>$ArticleNumber,"Title"=>$Title, "ArticleRatio"=>$ArticleRatio, "QuantityInSet"=>$NoPacks,"TotalQtyInSet"=>$qtyTotal, "ArticleRate"=>$ArticleRate, "Amount"=>$Amount, "ArticleColor"=>$ArticleColor, "ArticleSize"=>$ArticleSize, "OutwardBox"=>$OutwardBox, "OutwardRate"=>$OutwardRate, "Weight"=>$Weight, "Transporter"=>$Transporter, "PartyDiscount"=>$PartyDiscount)), false);
		}
		
		
		$TotalFinalAmount = 0;
		$SubTotalAmount = 0;
		$TotalFinalAmountDiscount = 0;
		$GSTLabel = "";
		$GSTValue = 0;
		$CGSTValue = 0;
		$SGSTValue = 0;
		
		if($Discount>0 || $Discount!=""){
			$TotalFinalAmountDiscount = (($TotalAmount * $Discount) / 100);
			$SubTotalAmount = $TotalAmount - $TotalFinalAmountDiscount;	
			$TotalFinalAmount = $SubTotalAmount;	
		}else{
			if($TotalFinalAmount==0){
				$TotalFinalAmount = $TotalAmount;
			}
		}
		
		if($GSTPercentage!="" || $GSTAmount!=""){
			if($GSTPercentage>0){
				$GSTLabel = "GST ".$GSTPercentage."%";
				//$GSTValue = number_format((($TotalFinalAmount * $GSTPercentage) / 100), 2);
				$GSTValue = (($TotalFinalAmount * $GSTPercentage) / 100);
				$CGSTValue = ($GSTValue / 2);
				$SGSTValue = ($GSTValue / 2);
				//$TotalFinalAmount = ($TotalFinalAmount + $GSTValue);
				$TotalGSTValue = round(($GSTValue / 2), 2)  * 2;
				
				$TotalFinalAmount = ($TotalFinalAmount + $TotalGSTValue);
				//return number_format($TotalFinalAmount , 2);
				//exit;
			} else{
				$GSTValue = number_format($GSTAmount, 2);
				$GSTValue1 = $GSTAmount;
				$TotalFinalAmount = ($TotalFinalAmount + $GSTValue1);
				$GSTLabel = "GST Amount";
			}
		}
		
		$SubtotalStatus = 0;
		if(is_float($TotalFinalAmount)){
			$SubtotalStatus = 1;
		}
		
		//return $PartyTotalDiscount;
		
		//return $SubtotalStatus.'==>'.$TotalFinalAmount;
		/* return $TotalFinalAmount."\n"; 
		echo $TotalAmount."\n";
		echo $Discount."\n";
		exit;
		
		
		/* if($Discount>0 || $Discount!=""){
			if($TotalFinalAmount>0){
				//$TotalFinalAmount = ($TotalFinalAmount - $Discount);
				$TotalFinalAmountDiscount = (($TotalFinalAmount * $Discount) / 100);
				$TotalFinalAmount = $TotalFinalAmount - $TotalFinalAmountDiscount;
			}else{
				//$TotalFinalAmount = ($TotalAmount - $Discount);
				$TotalFinalAmountDiscount = (($TotalAmount * $Discount) / 100);
				$TotalFinalAmount = $TotalAmount - $TotalFinalAmountDiscount;
			}			
		}else{
			if($TotalFinalAmount==0){
				$TotalFinalAmount = $TotalAmount;
			}
		} */
			
		//echo $TotalFinalAmount; exit;
		if($TotalWeight!=0){
			$TotalWeight = number_format($TotalWeight,2);
		}
		
		//echo "<pre>"; print_r($result); print_r($arrayvl); print_r($challendata); exit;
		$as  = array($challendata, array("RoundOff"=>$this->splitter(number_format($TotalFinalAmount, 2, '.', '')),"SubtotalStatus"=>$SubtotalStatus, "PartyTotalDiscount"=>$PartyTotalDiscount, "TotalNoPacks"=>$TotalNoPacks, "TotalQuantityAllInSet"=>$TotalQuantityInSet, "TotalAmount"=>number_format($TotalAmount, 2), "TotalWeight"=>$TotalWeight, "ExtraDiscountpercentage" => $Discount,"Discount"=>number_format($TotalFinalAmountDiscount, 2), "SubTotalAmount"=>number_format($SubTotalAmount, 2), "TotalFinalAmount"=>number_format($TotalFinalAmount, 2), "GSTLabel"=>$GSTLabel, "GSTPercentage"=>(int)$GSTPercentage,  "GSTValue"=>$GSTValue, "CGSTValue"=>number_format($CGSTValue, 2) , "SGSTValue"=>number_format($SGSTValue, 2), "GSTType"=>$GSTType), $result);
		//print_r($as); exit;
		return $as;
	}
	 
	function truncate_number($number, $precision = 2) {

		// Zero causes issues, and no need to truncate
		if (0 == (int)$number) {
			return $number;
		}

		// Are we negative?
		$negative = $number / abs($number);

		// Cast the number to a positive to solve rounding
		$number = abs($number);

		// Calculate precision number for dividing / multiplying
		$precision = pow(10, $precision);

		// Run the math, re-applying the negative value to ensure
		// returns correctly negative / positive
		return floor( $number * $precision ) / $precision * $negative;
	}

	/* function splitter($val)
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
	} */
	
	function splitter($val)
	{
		$totalroundamount = $val; 
		$str = (string) $val;
		$splitted = explode(".",$str);
		
		$whole = (integer)$splitted[0];
		$num = (integer) $splitted[1];
		
		$lennum = strlen($num);
		$den = (integer)  pow(10,strlen($splitted[1]));
		
		$number_var ="";
		$adjust_amount = "";
		if($num!=0){
			$roundoff_check = true;
			if($num>=50){
				$number_var = "Up";
				$am_set = (100 - $num);
				if($am_set>10){
					$adjust_amount = "0.". $am_set;
				}
				else{
					$adjust_amount = "0.0".$am_set;
				}
			}else{
				$number_var = "Down";
				
				if($num>10){
					$adjust_amount = "0.".$num;	
				}else{
					$adjust_amount = "0.0".$num;	
				}
			} 
			$totalroundamount = number_format(round($totalroundamount), 2);
		}else{
		  $number_var = "Zero";
		  $roundoff_check = false;
		  $adjust_amount = 0;
		  //round figure amount
		}
	  return array("Roundoff"=>$roundoff_check,'RoundValueSign'=>$number_var,'TotalRoundAmount'=>$totalroundamount,'AdjustAmount'=>$adjust_amount);
	}
	

	//SELECT art.ArticleNumber, art.ArticleRate, art.ArticleRatio, art.ArticleSize, art.ArticleColor, c.Colorflag, s.NoPacks, o.NoPacks as OutwardNoPacks, s.OutwardNoPacks as SalesNoPacks FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=p.CategoryId inner join outward o on o.Id=2 inner join so s on s.ArticleId=art.Id where art.Id=1 and s.SoNumberId=1
}
