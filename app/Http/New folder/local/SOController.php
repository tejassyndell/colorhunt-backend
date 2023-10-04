<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\SO;

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
     ///SO Module
     public function AddSO(Request $request)
     {
		$data = $request->all();
		//echo "<pre>"; print_r($data); exit;
		
		if($data['SoNumberId']=="Add"){
			$generate_SONUMBER = $this->GenerateSoNumber($data['UserId']);
			$SO_Number = $generate_SONUMBER['SO_Number'];
			$SO_Number_Financial_Id = $generate_SONUMBER['SO_Number_Financial_Id'];
			//$generate_SONUMBER['SO_Number'];
			//$generate_SONUMBER['SO_Number_Financial'];
			//$generate_SONUMBER['SO_Number_Financial_Id'];
			$SoNumberId = DB::table('sonumber')->insertGetId(
				['SoNumber' =>  $SO_Number,"FinancialYearId"=>$SO_Number_Financial_Id,'UserId'=>$data['UserId'], 'PartyId' =>  $data['PartyId'], 'SoDate'=>$data['Date'], 'Destination'=>$data['Destination'],'Transporter'=>$data['Transporter'],'Remarks'=>$data['Remarks'], 'GSTAmount'=>$data['GST'], 'GSTPercentage'=>$data['GST_Percentage'], 'GSTType'=>$data['GSTType'],'CreatedDate' => date('Y-m-d H:i:s')]
			);
		}else{
			$checksonumber = DB::select("SELECT SoNumber FROM `sonumber` where Id ='".$data['SoNumberId']."'");
			//echo "SELECT SoNumber FROM `sonumber` where Id ='".$data['SoNumberId']."'"; exit;
			
			if(!empty($checksonumber)){
				$SO_Number = $checksonumber[0]->SoNumber;
				$SoNumberId = $data['SoNumberId'];
				
				DB::table('sonumber')
				->where('Id', $SoNumberId)
				->update(['SoDate'=>$data['Date'], 'PartyId' =>  $data['PartyId'], 'Destination'=>$data['Destination'],'Transporter'=>$data['Transporter'],'Remarks'=>$data['Remarks'], 'GSTAmount'=>$data['GST'], 'GSTPercentage'=>$data['GST_Percentage'], 'GSTType'=>$data['GSTType']]);
			}
		}
		
		//echo "SO_Number: ".$SO_Number;
		//echo "SoNumberId: ".$SoNumberId;
		$artratedata = DB::select("select * from articlerate where ArticleId='".$data['ArticleId']."'");
		$ArticleRate = $artratedata[0]->ArticleRate; 
		
		if($data["ArticleOpenFlag"]==1){
			$mixnopacks = DB::select('SELECT * FROM `mixnopacks` where ArticleId="'.$data['ArticleId'].'"'); 
			//print_r($mixnopacks); exit;
			$NoPacks = "";
			$SalesNoPacks = "";
			if(isset($data['NoPacksNew'])){
				$NoPacks .= $data['NoPacksNew'];
				
				if($data['NoPacksNew']==0){
					return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
				}
				
				if($mixnopacks[0]->NoPacks<$data['NoPacksNew']){
					return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
				}
				$SalesNoPacks .= ($mixnopacks[0]->NoPacks - $data['NoPacksNew']);
			}else{
				return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
			}
			
			/* echo "NoPacks: ".$NoPacks."\n";
			echo "SalesNoPacks: ".$SalesNoPacks."\n";
			echo "<pre>"; print_r($data); exit; */
			
			$sonumberdata = DB::select('SELECT count(*) as total, NoPacks  FROM `so` where SoNumberId="'.$SoNumberId.'" and ArticleId="'.$data['ArticleId'].'"');
			$getnppacks = $sonumberdata[0]->NoPacks;
			
			//echo "<pre>"; 
			//print_r($nopacksadded);
			//print_r($sonumberdata);
			//echo $SalesNoPacks."\n\n"; 
			//echo $NoPacks."\n\n";
			//echo "<pre>"; print_r($data); exit;
			
			$updated = DB::table('mixnopacks')
				->where('ArticleId', $data['ArticleId'])
				->update(['NoPacks' => $SalesNoPacks]);
				
				
			if($sonumberdata[0]->total>0){
				$nopacksadded = $getnppacks + $NoPacks;
				
				DB::table('so')
					->where('SoNumberId', $SoNumberId)
					->where('ArticleId', $data['ArticleId'])
					->update(['NoPacks'=>$nopacksadded, 'OutwardNoPacks'=>$nopacksadded, 'ArticleRate'=>$ArticleRate]);
			}else{
			   $soadd['SoNumberId'] = $SoNumberId;
			   $soadd["ArticleId"] = $data['ArticleId'];
			   $soadd["NoPacks"] = $NoPacks;
			   $soadd["OutwardNoPacks"] = $NoPacks;
			   $soadd["ArticleRate"] = $ArticleRate;
			   $field = SO::create($soadd);
			}
			
			return response()->json(array("SoNumberId"=>$SoNumberId, "SO_Number"=>$SO_Number), 200);
		}else{		
			//echo "<pre>"; print_r($data); exit;
			$soadd = array();
			
			$dataresult= DB::select('SELECT c.Colorflag FROM `article` a inner join category c on c.Id=a.CategoryId where a.Id="'.$data['ArticleId'].'"'); 
			$Colorflag = $dataresult[0]->Colorflag;
			
			$datanopacks= DB::select('SELECT SalesNoPacks FROM `inward` where ArticleId="'.$data['ArticleId'].'" order by Id desc limit 0,1');
			$search = $datanopacks[0]->SalesNoPacks;
			
			$searchString = ',';
			if( strpos($search, $searchString) !== false ) {
				$string = explode(',', $search);
				$stringcomma = 1;
			}else{
				$search;
				$stringcomma = 0;
			}
			
			//echo $string[1]; exit;
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
					$NoPacks .= $data['NoPacksNew'];
					if($search<$data['NoPacksNew']){
						return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
					}
					$SalesNoPacks .= ($search - $data['NoPacksNew']);
				}else{
					return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
				}
				
			}
			
			$NoPacks = rtrim($NoPacks,',');
			$SalesNoPacks = rtrim($SalesNoPacks,',');
			//echo $NoPacks."\n";
			//echo $SalesNoPacks."\n\n";
			
			$CheckSalesNoPacks = explode(',', $NoPacks);
			//echo "<pre>"; print_r($CheckSalesNoPacks); exit;
			$tmp = array_filter($CheckSalesNoPacks);
			if (empty($tmp)) {
				//echo "All zeros!";
				return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
			}
			
			$sonumberdata = DB::select('SELECT count(*) as total, NoPacks  FROM `so` where SoNumberId="'.$SoNumberId.'" and ArticleId="'.$data['ArticleId'].'"');
			$getnppacks = $sonumberdata[0]->NoPacks;
			
			//echo "<pre>"; 
			//print_r($nopacksadded);
			//print_r($sonumberdata);
			//echo $SalesNoPacks."\n\n"; 
			//echo $NoPacks."\n\n";
			//echo "<pre>"; print_r($data); exit;
			
			$updated = DB::table('inward')
				->where('ArticleId', $data['ArticleId'])
				->update(['SalesNoPacks' => $SalesNoPacks]);
				
				
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
					$nopacksadded .= $getnppacks + $NoPacks.",";
				}
				$nopacksadded = rtrim($nopacksadded,',');
				
				DB::table('so')
					->where('SoNumberId', $SoNumberId)
					->where('ArticleId', $data['ArticleId'])
					->update(['NoPacks'=>$nopacksadded, 'OutwardNoPacks'=>$nopacksadded, 'ArticleRate'=>$ArticleRate]);
			} else{
				$soadd['SoNumberId'] = $SoNumberId;
				$soadd["ArticleId"] = $data['ArticleId'];
				$soadd["NoPacks"] = $NoPacks;
				$soadd["OutwardNoPacks"] = $NoPacks;
				$soadd["ArticleRate"] = $ArticleRate;
				$field = SO::create($soadd);
			}

			return response()->json(array("SoNumberId"=>$SoNumberId, "SO_Number"=>$SO_Number), 200);
		}
    }
	 
    public function GetSO($UserId)
    { 
		$userrole = DB::select("SELECT Role FROM `users`where Id='".$UserId."'");
		if($userrole[0]->Role==2){
			$wherecustom ="";
		}else{
			$wherecustom ="where son.UserId='".$UserId."'";
		}
		
		//SELECT son.Id ,GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoNumber, son.SoDate, son.Destination, son.Transporter, (select CASE WHEN SoId IS NULL THEN '0' ELSE '1' END from outwardnumber where SoId = son.Id and Id=o.OutwardNumberId) as ChecksId, (CASE WHEN own.SoId IS NULL THEN '0' ELSE '1' END) as OWID FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id left join outwardnumber own on own.SoId=s.SoNumberId  left join outward o on o.OutwardNumberId=own.Id group by s.SoNumberId
		//echo "SELECT son.Id ,GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoNumber, son.SoDate, son.Destination, son.Transporter FROM `so` s inner join article a on a.Id=s.ArticleId inner join sonumber son on s.SoNumberId=son.Id ".$wherecustom." group by son.SoNumber";
		//exit;
		//echo "<pre>";
		//print_r($userrole);
		//echo $wherecustom; echo "asd"; exit;
		//echo "SELECT son.Id ,GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoNumber, son.SoDate, son.Destination, son.Transporter FROM `so` s inner join article a on a.Id=s.ArticleId inner join sonumber son on s.SoNumberId=son.Id group by son.SoNumber"; exit;
        //return DB::select('SELECT s.Id , son.SoNumber, s.SoDate, s.Destination, s.Transporter, s.NoPacks, a.ArticleNumber, p.Name FROM `so` s LEFT JOIN party p ON p.Id=PartyId inner join article a on a.Id=s.ArticleId inner join sonumber son on s.SoNumberId=son.Id');
       //return DB::select("SELECT son.Id, OutwardSoList(son.Id) as OWID, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoNumber, son.SoDate, son.Destination, son.Transporter, son.UserId, concate(FirstCharacterConcat(u.Name), son.SoNumber) as Name FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join users u on u.Id=son.UserId ".$wherecustom." group by s.SoNumberId");
	   //SELECT c.Colorflag, a.ArticleOpenFlag, a.ArticleOpenFlag, SUM_OF_LIST(s.NoPacks) TotalNoPacks,(SELECT count(*) FROM `articlecolor` where ArticleId=a.Id) as TotalColor, (SELECT sum(ArticleRatio) FROM `articleratio` where ArticleId=a.Id) as TotalRation, s.* FROM `so` s inner join article a on a.Id=s.ArticleId inner join po po on po.ArticleId=a.Id inner join category c on c.Id=po.CategoryId where s.SoNumberId=9
	   //select (CASE dd.Colorflag WHEN '1' THEN (TotalNoPacks * TotalRation) ELSE CASE dd.ArticleOpenFlag WHEN '1' THEN TotalNoPacks ELSE (TotalNoPacks * TotalColor * TotalRation) END END) as OWID, dd.* from (SELECT c.Colorflag, a.ArticleOpenFlag, SUM_OF_LIST(s.NoPacks) TotalNoPacks,(SELECT count(*) FROM `articlecolor` where ArticleId=a.Id) as TotalColor, (SELECT sum(ArticleRatio) FROM `articleratio` where ArticleId=a.Id) as TotalRation, s.* FROM `so` s inner join article a on a.Id=s.ArticleId inner join po po on po.ArticleId=a.Id inner join category c on c.Id=po.CategoryId where s.SoNumberId=9) as dd
	   
	   return DB::select("select * from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId ".$wherecustom." group by s.SoNumberId) as ddd where ddd.OWID=0 order by ddd.Id desc");
		
	}
	
	public function PostSolist(Request $request)
	{
		
		
		$data = $request->all();	
		$search = $data['dataTablesParameters']["search"];
		$UserId = $data['UserID'];
		$startnumber = $data['dataTablesParameters']["start"];
		$userrole = DB::select("SELECT Role FROM `users`where Id='".$UserId."'");
		if($userrole[0]->Role==2){
			$whereuser ="";
		} else{
			$whereuser ="where d.UserId='".$UserId."'";
		}
		
		$vnddataTotal = DB::select("select count(*) as Total from (select * from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate,DATE_FORMAT(son.SoDate, \"%d/%m/%Y\") as cdate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId group by s.SoNumberId) as ddd where ddd.OWID=0 order by ddd.Id desc) as d ".$whereuser);
		$vntotal=$vnddataTotal[0]->Total;		
		$length = $data['dataTablesParameters']["length"];
		
		
		
		if($userrole[0]->Role==2){
			$wherecustom ="";
			if($search['value'] != null && strlen($search['value']) > 2){
				//$searchstring = "and oln.OutletNumber like '%".$search['value']."%'";
				$searchstring = " where d.SoNumber like '%".$search['value']."%' OR d.Name like '%".$search['value']."%'  OR CAST(d.TotalSoPieces as SIGNED INTEGER) like '%".$search['value']."%' OR cast(d.cdate as char) like '%".$search['value']."%' OR d.ArticleNumber like '%".$search['value']."%'";
				$vnddataTotalFilter = DB::select("select count(*) as Total from (select * from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate,DATE_FORMAT(son.SoDate, \"%d/%m/%Y\") as cdate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId ".$wherecustom." group by s.SoNumberId) as ddd where ddd.OWID=0 order by ddd.Id desc) as d ".$searchstring);
				$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
			}else{
				$searchstring = "";
				$vnddataTotalFilterValue = $vntotal;
			}
			
		}else{
			$wherecustom ="where d.UserId='".$UserId."'";
			if($search['value'] != null && strlen($search['value']) > 2){
				$searchstring = " where d.SoNumber like '%".$search['value']."%' OR d.Name like '%".$search['value']."%'  OR CAST(d.TotalSoPieces as SIGNED INTEGER) like '%".$search['value']."%'  OR cast(d.cdate as char) like '%".$search['value']."%' OR d.ArticleNumber like '%".$search['value']."%'";
				$vnddataTotalFilter = DB::select("select * from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate,DATE_FORMAT(son.SoDate, \"%d/%m/%Y\") as cdate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId ".$wherecustom." group by s.SoNumberId) as ddd where ddd.OWID=0 order by ddd.Id desc) as d ".$searchstring);
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
				//$ordercolumn = "d.SoNumber";
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
		if($data['dataTablesParameters']["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data['dataTablesParameters']["order"][0]["dir"];
		}
		
		//"SELECT GetTotalOutwardOrderPieces(own.Id) as TotalOutwardPieces, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber,  own.OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId ".$wherecustom." group by o.OutwardNumberId order by o.Id desc"
		
		$vnddata = DB::select("select d.* from (select * from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, s.Status, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate,DATE_FORMAT(son.SoDate, \"%d/%m/%Y\") as cdate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId group by s.SoNumberId) as ddd where ddd.OWID=0 and ddd.Status!=1 order by ddd.Id desc) as d ".$wherecustom." ".$searchstring." ".$order." limit ".$data['dataTablesParameters']["start"].",".$length);
		//$vnddata = DB::select("select d.* from (select * from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate,DATE_FORMAT(son.SoDate, \"%d/%m/%Y\") as cdate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId group by s.SoNumberId) as ddd where ddd.OWID=0 order by ddd.Id desc) as d ".$wherecustom." ".$searchstring." ".$order." limit ".$data['dataTablesParameters']["start"].",".$length);
		//return "select d.* from (select * from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate,DATE_FORMAT(son.SoDate, \"%d/%m/%Y\") as cdate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId group by s.SoNumberId) as ddd where ddd.OWID=0 order by ddd.Id desc) as d ".$wherecustom." ".$searchstring." ".$order." limit ".$data['dataTablesParameters']["start"].",".$length;
		
	
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
	
	public function DeleteSONumber($SONO)
	{
		//echo $SONO; exit;
	    //$solist = DB::select("SELECT s.ArticleId, s.NoPacks, inw.NoPacks as InwardNoPacks, inw.SalesNoPacks FROM `so` s left join inward inw on inw.ArticleId=s.ArticleId where SoNumberId ='".$SONO."'");
		$solist = DB::select("SELECT s.Id, s.ArticleId, a.ArticleOpenFlag FROM `so` s inner join article a on a.Id=s.ArticleId where s.SoNumberId='".$SONO."'");
		//echo "<pre>"; print_r($solist); exit;
		foreach($solist as $vl){
			if($vl->ArticleOpenFlag==0){
				$data = DB::select("SELECT s.Id, s.ArticleId, s.NoPacks, inw.NoPacks as InwardNoPacks, inw.SalesNoPacks FROM `so` s left join inward inw on inw.ArticleId=s.ArticleId where s.Id = '".$vl->Id."'");
				
				$ArticleId = $data[0]->ArticleId;
				$search = $data[0]->NoPacks;
				$SalesNoPacks = $data[0]->SalesNoPacks;

				$searchString = ',';
				$AddSalesNoPacks = "";
				if( strpos($search, $searchString) !== false ) {
					$string = explode(',', $search);
					$SalesNoPacks = explode(',', $SalesNoPacks);
					
					
					foreach($string as $key => $vl){
						$AddSalesNoPacks .= ($SalesNoPacks[$key] + $vl).",";
					}
					//$AddSalesNoPacks = rtrim($AddSalesNoPacks,',');
				}else{
					$AddSalesNoPacks .= ($search + $SalesNoPacks).",";
				}


				$AddSalesNoPacks = rtrim($AddSalesNoPacks,',');
				//echo "If"; echo $AddSalesNoPacks; echo "<pre>"; print_r($data); exit;
				//echo "Add -> ".$AddSalesNoPacks;
				DB::table('so')
					->where('Id', '=', $data[0]->Id)
					->delete();
					
				DB::table('inward')
				->where('ArticleId', $ArticleId)
				->update(['SalesNoPacks' => $AddSalesNoPacks]);
			
			}else{
				$data = DB::select("SELECT mxp.NoPacks, s.Id, s.SoNumberId, s.ArticleId, s.NoPacks as SONoPacks, s.Status FROM `so` s inner join mixnopacks mxp on mxp.ArticleId=s.ArticleId where s.Id = '".$vl->Id."'");
				
				$ArticleId = $data[0]->ArticleId;
				$search = $data[0]->SONoPacks;
				$SalesNoPacks = $data[0]->NoPacks;

				$searchString = ',';
				$AddSalesNoPacks = ($search + $SalesNoPacks);
				//echo "else"; echo $AddSalesNoPacks; echo "<pre>"; print_r($data); exit;
				//echo "Add -> ".$AddSalesNoPacks;
				DB::table('so')
					->where('Id', '=', $data[0]->Id)
					->delete();
					
				DB::table('mixnopacks')
				->where('ArticleId', $ArticleId)
				->update(['NoPacks' => $AddSalesNoPacks]);
			}
		}
		
		return response()->json("SUCCESS", 200);
	}
		 
		
     public function DeleteSO($id, $ArticleOpenFlag)
     {
		 //return $ArticleOpenFlag;
		 if($ArticleOpenFlag==1){
			$datanopacks= DB::select('SELECT s.ArticleId, s.NoPacks, mxp.NoPacks as MaxNoPacks FROM `so` s left join mixnopacks mxp on mxp.ArticleId=s.ArticleId where s.Id="'.$id.'"');
			$ArticleId = $datanopacks[0]->ArticleId;
			$search = $datanopacks[0]->NoPacks;
			$SalesNoPacks = $datanopacks[0]->MaxNoPacks;
			
			$AddSalesNoPacks = ($search + $SalesNoPacks);
			
			DB::beginTransaction();
			try {
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
			
		 }else{
			 
			$datanopacks= DB::select('SELECT s.ArticleId, s.NoPacks, inw.NoPacks as InwardNoPacks, inw.SalesNoPacks FROM `so` s left join inward inw on inw.ArticleId=s.ArticleId where s.Id="'.$id.'"');
			$ArticleId = $datanopacks[0]->ArticleId;
			$search = $datanopacks[0]->NoPacks;
			$SalesNoPacks = $datanopacks[0]->SalesNoPacks;
			
			$searchString = ',';
			$AddSalesNoPacks = "";
			if( strpos($search, $searchString) !== false ) {
				$string = explode(',', $search);
				$SalesNoPacks = explode(',', $SalesNoPacks);
				
				foreach($string as $key => $vl){
					$AddSalesNoPacks .= ($SalesNoPacks[$key] + $vl).",";
				}
				//$AddSalesNoPacks = rtrim($AddSalesNoPacks,',');
			}else{
				$AddSalesNoPacks .= ($search + $SalesNoPacks).",";
			}
			
			
			$AddSalesNoPacks = rtrim($AddSalesNoPacks,',');
			//echo $AddSalesNoPacks;
			/* 
			
			print_r($datanopacks); 
			echo "ArticleId: ".$ArticleId;
			echo "<br />";
			echo $AddSalesNoPacks;
			echo "<br />";
			exit; */
			DB::beginTransaction();
			try {
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
		//echo "<pre>"; print_r($data); exit;
		if($data["ArticleOpenFlag"]==1){
			$mixnopacks = DB::select('SELECT *, (select Nopacks from so where id="'.$data['id'].'") as SONopacks FROM `mixnopacks` where ArticleId= "'.$data['ArticleId'].'"');
			$InwardSalesNoPacks = $mixnopacks[0]->NoPacks;
			$SONopacks = $mixnopacks[0]->SONopacks;
			
			
			$UpdateInwardNoPacks = "";
			$updateNoPacks = $data['NoPacksNew'];
			$total = $InwardSalesNoPacks + $SONopacks;
			if($total<$data["NoPacksNew"]){
				return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
			}
			
			if($SONopacks<$data['NoPacksNew']){
				$UpdateInwardNoPacks = ($InwardSalesNoPacks - ($data['NoPacksNew'] - $SONopacks));
			}else if($SONopacks == $data['NoPacksNew']){
				$UpdateInwardNoPacks = $InwardSalesNoPacks;
			}else{
				if($InwardSalesNoPacks==0){
					$UpdateInwardNoPacks = ($SONopacks - $data['NoPacksNew']);
				}else{
					$UpdateInwardNoPacks = ($InwardSalesNoPacks + ($SONopacks - $data['NoPacksNew']));
				}
			}
			
			if (empty($updateNoPacks)) {
				//echo "All zeros!";
				return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
			}
			
			/* echo $updateNoPacks."\n";
			echo $UpdateInwardNoPacks."\n";
			echo "<pre>";
			print_r($mixnopacks);
			print_r($data); 
			exit; */
			
			DB::beginTransaction();
			try {
				DB::table('mixnopacks')
					->where('ArticleId', $data['ArticleId'])
					->update(['NoPacks' => $UpdateInwardNoPacks]);
				
				DB::table('sonumber')
				->where('Id', $data['SoNumberId'])
				->update(['SoDate'=>$data['Date'], 'PartyId' =>  $data['PartyId'],'Destination'=>$data['Destination'],'Transporter'=>$data['Transporter'],'Remarks'=>$data['Remarks'], 'GSTAmount' =>$data['GST'],'GSTPercentage'=>$data['GST_Percentage']]);
		
				SO::where('id', $data['id'])->update(array(
					 'NoPacks' => $updateNoPacks,
					 'OutwardNoPacks' => $updateNoPacks
				));
				 
				DB::commit();
				return response()->json("SUCCESS", 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}
			
		}else{
		
			$dataresult= DB::select('SELECT c.Colorflag, inw.NoPacks as TotalNoPacks, inw.SalesNoPacks as InwardSalesNoPacks, (select Nopacks from so where id="'.$data['id'].'") as SONopacks FROM `article` a inner join category c on c.Id=a.CategoryId inner join inward inw on inw.ArticleId=a.Id where a.Id="'.$data['ArticleId'].'"'); 
			$Colorflag = $dataresult[0]->Colorflag;
			$TotalNoPacks = $dataresult[0]->TotalNoPacks;
			$InwardSalesNoPacks = $dataresult[0]->InwardSalesNoPacks;
			$SONopacks = $dataresult[0]->SONopacks;
			
			//echo "TotalNoPacks: ".$TotalNoPacks."\n";
			/* echo "InwardSalesNoPacks: ".$InwardSalesNoPacks."\n";
			echo "SONopacks: ".$SONopacks."\n";
			exit; */
			if( strpos($SONopacks, ',') !== false ) {
				$InwardSalesNoPacks = explode(',', $InwardSalesNoPacks);
				$SONopacks = explode(',', $SONopacks);
				$stringcomma = 1;
			}else{
				$stringcomma = 0;
			}
			
			$updateNoPacks = "";
			$UpdateInwardNoPacks = "";
			if($Colorflag==1){
				foreach($data['ArticleSelectedColor'] as $key => $vl){
					$numberofpacks = $vl["Id"];
					$inwardsale = $InwardSalesNoPacks[$key];
					$sosale = $SONopacks[$key];
					$getnopacks = $data["NoPacksNew_".$numberofpacks];
					
					if($data["NoPacksNew_".$numberofpacks]!=""){
						if($stringcomma==1){
							/* echo "asdasd";
	echo $inwardsale."\n";
		echo $sosale."\n";					exit; */
							$total = ($inwardsale + $sosale);
							//echo $total; exit;
							if($total<$getnopacks){
								return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
							}
							
							$updateNoPacks .= $getnopacks.",";
							
							//3<4
							if($sosale<$getnopacks){
								$UpdateInwardNoPacks .= ($inwardsale - ($getnopacks - $sosale)).",";
							}else if($sosale == $getnopacks){
								$UpdateInwardNoPacks .= $inwardsale.",";
							}else{
								if($inwardsale==0){
									$UpdateInwardNoPacks .= ($sosale - $getnopacks).",";
								}else{
									$UpdateInwardNoPacks .= ($inwardsale + ($sosale - $getnopacks)).",";
								}
							}
							
						}else{
							$total = $InwardSalesNoPacks + $SONopacks;
							
							if($total<$getnopacks){
								return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
							}
							
							if($SONopacks<$getnopacks){
								$UpdateInwardNoPacks .= ($InwardSalesNoPacks - ($getnopacks - $SONopacks)).",";
							}else if($sosale == $getnopacks){
								$UpdateInwardNoPacks .= $InwardSalesNoPacks.",";
							}else{
								if($inwardsale==0){
									$UpdateInwardNoPacks .= ($SONopacks - $getnopacks).",";
								}else{
									$UpdateInwardNoPacks .= ($InwardSalesNoPacks + ($SONopacks - $getnopacks)).",";
								}
							}
							
							$updateNoPacks .= $getnopacks.",";
							//$UpdateInwardNoPacks .= $total;
						}
						//$TotalNoPacks .= $data["NoPacksNew_".$numberofpacks].",";
					}
					else{
						$updateNoPacks .= "0,";
						$UpdateInwardNoPacks .= ($inwardsale + $sosale).",";
					}
				}
			} else{
				$updateNoPacks .= $data['NoPacksNew'].",";
				$total = $InwardSalesNoPacks + $SONopacks;
				if($total<$data["NoPacksNew"]){
					return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
				}
				
				if($SONopacks<$data['NoPacksNew']){
					$UpdateInwardNoPacks .= ($InwardSalesNoPacks - ($data['NoPacksNew'] - $SONopacks)).",";
				}else if($SONopacks == $data['NoPacksNew']){
					$UpdateInwardNoPacks .= $InwardSalesNoPacks.",";
				}else{
					if($InwardSalesNoPacks==0){
						$UpdateInwardNoPacks .= ($SONopacks - $data['NoPacksNew']).",";
					}else{
						$UpdateInwardNoPacks .= ($InwardSalesNoPacks + ($SONopacks - $data['NoPacksNew'])).",";
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
			
			/* echo "updateNoPacks:".$updateNoPacks."\n";
			echo "UpdateInwardNoPacks:".$UpdateInwardNoPacks."\n";
			echo "<pre>"; 
			print_r($data);
			exit; */
			DB::beginTransaction();
			try {
				DB::table('inward')
					->where('ArticleId', $data['ArticleId'])
					->update(['SalesNoPacks' => $UpdateInwardNoPacks]);
				
				DB::table('sonumber')
				->where('Id', $data['SoNumberId'])
				->update(['SoDate'=>$data['Date'], 'PartyId' =>  $data['PartyId'],'Destination'=>$data['Destination'],'Transporter'=>$data['Transporter'],'Remarks'=>$data['Remarks'], 'GSTAmount' =>$data['GST'],'GSTPercentage'=>$data['GST_Percentage']]);


				SO::where('id', $data['id'])->update(array(
					 'NoPacks' => $updateNoPacks,
					 'OutwardNoPacks' => $updateNoPacks
				 ));
				DB::commit();
				return response()->json("SUCCESS", 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}
		}
     }
     public function GetSoIdWise($id)
     {
         return DB::select('SELECT s.*, concat(FirstCharacterConcat(u.Name), son.SoNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as SoNumber, son.GSTAmount, son.GSTPercentage, son.GSTType From so s inner join sonumber son on son.Id=s.SoNumberId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on son.UserId=u.Id WHERE s.Id = ' . $id . '');
     }
	 
	 ///Artical
    public function GetArticle()
    {
		return DB::select('select * from (SELECT art.*, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId group by inw.Id union all SELECT a.*, m.ArticleId, m.NoPacks, m.SalesNoPacks, 0 FROM `mixnopacks` m inner join article a on a.Id=m.ArticleId where m.NoPacks!=0) as t where SalesNoPacksCheck!=1 and t.ArticleStatus = 1  group by t.Id');
		//return DB::select('select * from (SELECT art.*, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck, substring_index(GROUP_CONCAT(DISTINCT CONCAT(ap.Name) ORDER BY ap.Id SEPARATOR ','), ',', 2) as Images From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId left join articlephotos ap on ap.ArticlesId=art.Id where ap.Name!="" group by inw.Id union all SELECT a.*, m.ArticleId, m.NoPacks, m.SalesNoPacks, 0, 0 FROM `mixnopacks` m inner join article a on a.Id=m.ArticleId where m.NoPacks!=0) as t where SalesNoPacksCheck!=1 and t.ArticleStatus = 1 group by t.Id');
		
		
		//return DB::select('select * from (SELECT art.*, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId group by inw.Id) as t where SalesNoPacksCheck!=1 and t.ArticleStatus = 1  group by t.Id');
		//return DB::select('SELECT art.*, art.ArticleNumber, s.ArticleId, inw.NoPacks, inw.SalesNoPacks From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId group by inw.Id');
		//return DB::select('SELECT art.*, art.ArticleNumber, s.ArticleId From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId where s.ArticleId is NULL');
    }
	
	public function GenerateSoNumber($UserId)
    {
		$array = array();
		$fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
        $sonumberdata = DB::select('SELECT Id, FinancialYearId, SoNumber From sonumber where UserId="'.$UserId.'" order by Id desc limit 0,1');
		
		if(count($sonumberdata)>0){
			if($fin_yr[0]->Id > $sonumberdata[0]->FinancialYearId){
				$array["SO_Number"] = 1;
				$array["SO_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["SO_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			} else{
				$array["SO_Number"] = ($sonumberdata[0]->SoNumber) + 1;
				$array["SO_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["SO_Number_Financial"] = ($sonumberdata[0]->SoNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			}
		}
        else{
			$array["SO_Number"] = 1;
			$array["SO_Number_Financial_Id"] = $fin_yr[0]->Id;
			$array["SO_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
			return $array;
        }



		/* if(count($sonumberdata)>0){
			$array["SoNumberId"] = ($sonumberdata[0]->SoNumber) + 1;
			$array["Id"] = $sonumberdata[0]->Id + 1;
			return response()->json($array, 200);
		}
        else{
			$sonumberdata1 = DB::select('SELECT Id, SoNumber From sonumber order by Id desc limit 0,1');
			//echo "<pre>"; print_r($sonumberdata); exit;
			if(count($sonumberdata1)>0){
				$array["SoNumberId"] = ($sonumberdata1[0]->SoNumber) + 1;
				$array["Id"] = 1;
			}
			else{
				$array["SoNumberId"] = 1;
				$array["Id"] = 1;
			}
			//echo "<pre>"; print_r($array); exit;
			return response()->json($array, 200);
        } */
    }
	
	public function GetInwardArticleData($ArticleId){
		//echo "<pre>"; print_r($ArticleId); exit;
		
		$articleflagcheck = DB::select('SELECT ArticleOpenFlag FROM `article` where Id = "'.$ArticleId.'"');
		//echo "<pre>"; print_r($articleflagcheck); exit;
		if($articleflagcheck[0]->ArticleOpenFlag==0){
			//return DB::select("SELECT art.ArticleOpenFlag, art.ArticleNumber, art.ArticleRate, art.ArticleRatio, art.ArticleSize, art.ArticleColor, (case when c.Colorflag IS NULL then cc.Colorflag else c.Colorflag end) as Colorflag, i.NoPacks, i.SalesNoPacks FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=p.CategoryId left join category cc on cc.Id=art.CategoryId inner join inward i on i.ArticleId=art.Id where art.Id='".$ArticleId."'");
			return DB::select("SELECT art.ArticleOpenFlag, art.ArticleNumber, art.ArticleRate, art.ArticleRatio, art.ArticleSize, art.ArticleColor, (case when c.Colorflag IS NULL then cc.Colorflag else c.Colorflag end) as Colorflag, i.NoPacks, i.SalesNoPacks FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=p.CategoryId left join category cc on cc.Id=art.CategoryId inner join inward i on i.ArticleId=art.Id where art.Id='".$ArticleId."' order by i.Id desc limit 0,1");
		}else{
			return DB::select("SELECT mxp.NoPacks, a.ArticleNumber, a.ArticleOpenFlag FROM `mixnopacks` mxp inner join article a on a.Id=mxp.ArticleId where mxp.ArticleId ='".$ArticleId."'");
		}
		//$sonumberdata1 = DB::select('SELECT sum(TotalSetQuantity) as TotalSetQuantity, ArticleOpenFlag FROM `inward` where ArticleId = "'.$UserId.'"');
		
		//SELECT art.ArticleNumber, art.ArticleRate, art.ArticleRatio, art.ArticleSize, art.ArticleColor, c.Colorflag, i.NoPacks, i.SalesNoPacks, sum(i.TotalSetQuantity) as TotalSetQuantity FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=p.CategoryId inner join inward i on i.ArticleId=art.Id where art.Id=19
		
	}
	
	
	public function SoListFromSONO($id)
     {
		 //echo $id; exit;
		 return DB::select('SELECT s.Id , s.SoNumberId, concat(FirstCharacterConcat(u.Name), son.SoNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as SoNumber,  son.SoDate, son.Destination, son.Transporter, s.NoPacks, a.ArticleNumber, p.Name, a.ArticleOpenFlag FROM `so` s inner join sonumber son on s.SoNumberId=son.Id inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on son.UserId=u.Id inner JOIN party p ON p.Id=son.PartyId inner join article a on a.Id=s.ArticleId where s.SoNumberId="'.$id.'"');
		 //return DB::select("SELECT inw.Id,inw.ArticleId,v.Name, c.Colorflag, c.Title, inw.GRN,ar.ArticleNumber, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID, ar.ArticleRate,ar.ArticleColor,ar.ArticleSize,ar.ArticleRatio,inw.NoPacks,inw.InwardDate,inw.Weight FROM `inward` inw LEFT JOIN article ar on ar.Id = inw.ArticleId inner JOIN po p on p.ArticleId = ar.Id inner Join category c on c.Id=p.CategoryId inner join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId where inw.GRN='". $id ."'");
	 }
	 
	 public function SoDateRemarkFromSONO($id)
     {
		 //return DB::select("SELECT * FROM `sonumber` where Id='". $id ."'");
		 return DB::select("SELECT sn.*, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SO_Number_FinancialYear FROM `sonumber` sn inner join financialyear fn on fn.Id=sn.FinancialYearId inner join users u on sn.UserId=u.Id where sn.Id = '". $id ."'");
	 }
	 
	//Sales Return Group data get on listing - start
	public function SrListFromSRONO($id)
    {
		//echo $id; exit;
		//return DB::select('SELECT sr.Id ,  sr.CreatedDate, sr.SalesReturnNumber, concat(FirstCharacterConcat(u.Name), srn.SalesReturnNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as SRO_Number_FinancialYear, sr.NoPacks, a.ArticleNumber, p.Name as PartyName, a.ArticleOpenFlag FROM `salesreturn` sr inner join salesreturnnumber srn on sr.SalesReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN party p ON p.Id=srn.PartyId inner join article a on a.Id=sr.ArticleId where sr.SalesReturnNumber = "'.$id.'"');
		//return 'SELECT concat(own.OutwardNumber, \'/\',f.StartYear,\'-\',f.EndYear) as GRN, sr.Id , sr.CreatedDate, sr.SalesReturnNumber, concat(FirstCharacterConcat(u.Name), srn.SalesReturnNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as SRO_Number_FinancialYear, sr.NoPacks, a.ArticleNumber, p.Name as PartyName, a.ArticleOpenFlag FROM `salesreturn` sr inner join salesreturnnumber srn on sr.SalesReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN party p ON p.Id=srn.PartyId inner join article a on a.Id=sr.ArticleId inner join outward o on o.Id=sr.OutwardId left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear f on f.Id=own.FinancialYearId where sr.SalesReturnNumber = "'.$id.'"'; exit;
		return DB::select("SELECT concat(own.OutwardNumber, '/',f.StartYear,'-',f.EndYear) as OutwardNumber, sr.Id , sr.CreatedDate, sr.SalesReturnNumber, concat(FirstCharacterConcat(u.Name), srn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sr.NoPacks, a.ArticleNumber, p.Name as PartyName, a.ArticleOpenFlag FROM `salesreturn` sr inner join salesreturnnumber srn on sr.SalesReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN party p ON p.Id=srn.PartyId inner join article a on a.Id=sr.ArticleId inner join outward o on o.Id=sr.OutwardId left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear f on f.Id=own.FinancialYearId where sr.SalesReturnNumber = '".$id."'");
	}
	//Sales Return Group data get on listing - end
	 
	//Outlet Sales Return Group data get on listing - start
	public function SroListFromSRONO($id)
    {
		//echo $id; exit;
		return DB::select("SELECT sr.Id ,  sr.CreatedDate, sr.SalesReturnNumber, concat(FirstCharacterConcat(u.Name), srn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sr.NoPacks, a.ArticleNumber, p.Name as PartyName, a.ArticleOpenFlag FROM `outletsalesreturn` sr inner join outletsalesreturnnumber srn on sr.SalesReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN party p ON p.Id=srn.PartyId inner join article a on a.Id=sr.ArticleId where sr.SalesReturnNumber = '".$id."'");
	}
	//Outlet Sales Return Group data get on listing - end
	
	//Sales return id wise get the data - start
	public function SrDateRemarkFromSRONO($id)
    {
		//return DB::select("SELECT * FROM `sonumber` where Id='". $id ."'");
		return DB::select("SELECT concat(sn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sn.Id, sn.PartyId, sn.OutletPartyId, sn.Remarks FROM `salesreturnnumber` sn inner join financialyear fn on fn.Id=sn.FinancialYearId where sn.Id ='". $id ."'");
	}
	//Sales return id wise get the data - end
	
	//outlet Sales return id wise get the data - start
	public function SroDateRemarkFromSRONO($id)
    {
		//return DB::select("SELECT * FROM `sonumber` where Id='". $id ."'");
		return DB::select("SELECT concat(sn.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SRO_Number_FinancialYear, sn.Id, sn.PartyId, sn.OutletPartyId, sn.Remarks FROM `outletsalesreturnnumber` sn inner join financialyear fn on fn.Id=sn.FinancialYearId where sn.Id ='". $id ."'");
	}
	//outlet Sales return id wise get the data - end
	
	//Purchase Return Group data get on listing - start
	public function PrListFromPRONO($id)
    {
		//echo $id; exit;
		//return DB::select('SELECT sr.Id ,  sr.CreatedDate, sr.PurchaseReturnNumber, concat(FirstCharacterConcat(u.Name), srn.PurchaseReturnNumber,fn.StartYear,'-',fn.EndYear) as PRO_Number_FinancialYear, sr.ReturnNoPacks, a.ArticleNumber, p.Name as VendorName, a.ArticleOpenFlag FROM `purchasereturn` sr inner join purchasereturnnumber srn on sr.PurchaseReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN vendor p ON p.Id=sr.VendorId inner join article a on a.Id=sr.ArticleId where sr.PurchaseReturnNumber = "'.$id.'"');
		
		
		//return DB::select('SELECT sr.Id , sr.CreatedDate, sr.ReturnNoPacks as Pieces, a.ArticleNumber, p.Name as VendorName, a.ArticleOpenFlag FROM `purchasereturn` sr inner join purchasereturnnumber srn on sr.PurchaseReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN vendor p ON p.Id=sr.VendorId inner join article a on a.Id=sr.ArticleId where sr.PurchaseReturnNumber = "'.$id.'"');
		return DB::select('SELECT concat(ig.GRN, "/",f.StartYear,"-",f.EndYear) as GRN, sr.Id , sr.CreatedDate, sr.ReturnNoPacks as Pieces, a.ArticleNumber, p.Name as VendorName, a.ArticleOpenFlag FROM `purchasereturn` sr inner join purchasereturnnumber srn on sr.PurchaseReturnNumber=srn.Id inner join financialyear fn on fn.Id=srn.FinancialYearId inner join users u on sr.UserId=u.Id inner JOIN vendor p ON p.Id=sr.VendorId inner join article a on a.Id=sr.ArticleId inner join inward i on i.Id=sr.InwardId left join inwardgrn ig on ig.Id=i.GRN inner join financialyear f on f.Id=ig.FinancialYearId where sr.PurchaseReturnNumber = "'.$id.'"');
	}
	//Purchase Return Group data get on listing - end
	 
	//Purchase return id wise get the data - start
	public function PrDateRemarkFromPRONO($id)
    {
		//return DB::select("SELECT * FROM `sonumber` where Id='". $id ."'");
		return DB::select("SELECT concat(prn.PurchaseReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as PRO_Number_FinancialYear, prn.Id, prn.VendorId, prn.Remark FROM `purchasereturnnumber` prn inner join financialyear fn on fn.Id=prn.FinancialYearId where prn.Id ='". $id ."'");
	}
	//Purchase return id wise get the data - end
	 
	 public function SODataCheckUserWise($UserId, $SONO)
     {
		$getdatauser = DB::select("SELECT u.Email, r.* FROM `users` u inner join userrights r on r.RoleId=u.Role where u.Id = '".$UserId."' and r.PageId = 5");
		$userrole = DB::select("SELECT Role FROM `users`where Id='".$UserId."'");
		//print_r($getdatauser); exit;
		if($getdatauser[0]->ListRights==1){
			$wherecustom ="";
		}else{
			if($userrole[0]->Role==2){
				$wherecustom ="";
			}else{
				$wherecustom ="UserId='". $UserId ."' and ";
			}
		}
		
		
		
		$checkdata = DB::select("SELECT count(*) as TotalRow FROM `sonumber` where ".$wherecustom." Id='". $SONO ."'");
		$array = array();
		if($checkdata[0]->TotalRow>0){
			$array["Rights"] = true;
		}else{
			$array["Rights"] = false;
		}
		return response()->json($array, 200);
		//echo "<pre>"; print_r($checkdata); exit;
		// return DB::select("SELECT * FROM `sonumber` where Id='". $SONO ."'");
	 }
	 
	public function getParty(){
		return DB::select("SELECT Id, Name FROM `party` order by Name asc");
	}
	
	public function RemainingSOWithParty($Id){
		return  DB::select("SELECT s.SoNumberId, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber , s.ArticleId, s.Status, sn.PartyId, sn.Transporter, sn.UserId, p.Name FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join users u on u.Id=sn.UserId inner join party p on p.Id=sn.PartyId where status in (0) and sn.PartyId='".$Id."' group by SoNumberId");
	}
	
	public function AddSOStatus(Request $request)
	{
		$data = $request->all();
		
		if($data['Status']){
			DB::beginTransaction();
			try {
				DB::table('sostatus')->insertGetId(
					['SoId' =>  $data['SoId'],'UserId'=>$data['UserId'], 'PartyId' =>  $data['PartyId'], 'SoStatusDate'=>date('Y-m-d')]
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
	
	public function SOStatusList($UserId){
		$userrole = DB::select("SELECT Role FROM `users`where Id='".$UserId."'");
		if($userrole[0]->Role==2){
			$wherecustom ="";
		}else{
			$wherecustom ="where sos.UserId = '".$UserId."'";
		}
		return DB::select("SELECT sos.*, u.Name as UserName, p.Name as PartyName, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `sostatus` sos inner join users u on u.Id=sos.UserId inner join sonumber sn on sn.Id=sos.SoId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join party p on p.Id=sos.PartyId ".$wherecustom);
		//return DB::select("SELECT son.Id, OutwardSoList(son.Id) as OWID, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join users u on u.Id=son.UserId ".$wherecustom." group by s.SoNumberId");
	}
	
	public function GetSoStatus($id){
		return DB::select("select * from sostatus where Id='".$id."'");
	}
	
	public function DeleteSOStatus($id)
     {
		$getsochallen = DB::select('select u.Id as UserId, son.SoNumber, s.Id as SOID, s.NoPacks, art.Id as ArticleId, art.ArticleOpenFlag, art.ArticleNumber, c.Title, c.Colorflag, art.ArticleColor, art.ArticleSize, art.ArticleRate, s.ArticleRate as SoArticleRate, art.ArticleRatio from so s inner join sonumber son on son.Id=s.SoNumberId inner join users u on u.Id=son.UserId inner join article art on art.Id=s.ArticleId left join po p on p.ArticleId=art.Id left join purchasenumber pur on pur.Id=p.PO_Number inner join category c on c.Id=art.CategoryId where s.SoNumberId=' . $id . ' order by s.Id ASC');
		
		DB::beginTransaction();
		try {	
			foreach($getsochallen as $vl){
				$object = (object)$vl;
					
				$UserId = $vl->UserId;
				$ArticleNumber = $vl->ArticleNumber;
				$ArticleOpenFlag = $vl->ArticleOpenFlag;
				$SoNumberId = $vl->SoNumber;
				$NoPacks = $vl->NoPacks;
				$Colorflag = $vl->Colorflag;
				$ArticleId = $vl->ArticleId;
				$SOID = $vl->SOID;
				$ArticleColor = $vl->ArticleColor;
				
				$getart = $this->GetSOArticleData($id, $ArticleId, 0);
				
				$RemainingNoPacks = $getart[0]->SalesNoPacks;
				$CheckSalesNoPacks = $getart[0]->CheckSalesNoPacks;
				
				$object->RemainingNoPacks = $RemainingNoPacks;
				$object->CheckSalesNoPacks = $CheckSalesNoPacks;
				if($CheckSalesNoPacks==0){
					DB::table('sostatus')->insertGetId(
						['SoNumberId'=>$SoNumberId, 'SoId'=>$SOID, 'UserId'=>$UserId, 'SoStatusDate'=>date('Y-m-d h:i:s')]
					);
						
					if($Colorflag==0){
						if($ArticleOpenFlag==0){
							//single
							$slnopack = DB::select("select SalesNoPacks from inward where ArticleId='".$ArticleId."'");
							$totalsalesnopack = $slnopack[0]->SalesNoPacks + $RemainingNoPacks;
							
							DB::table('inward')
							->where('ArticleId', $ArticleId)
							->update(['SalesNoPacks'=>$totalsalesnopack, 'updated_at'=>date('Y-m-d h:i:s')]);
						
						}else{
							//MIX
							$getdata = DB::select("SELECT NoPacks FROM `mixnopacks` where ArticleId='".$ArticleId."'");
							$totalsalesnopack = $getdata[0]->NoPacks + $RemainingNoPacks;
							DB::table('mixnopacks')
							->where('ArticleId', $ArticleId)
							->update(['NoPacks' => $totalsalesnopack]);
						}
					} else{
						if($ArticleOpenFlag==0){
							//multiple
							$slnopack = DB::select("select SalesNoPacks from inward where ArticleId='".$ArticleId."'");
							if(strpos($slnopack[0]->SalesNoPacks, ',') !== false) {
								$NoPacks1 = explode(',', $RemainingNoPacks);
								$SalesNoPacks = explode(',', $slnopack[0]->SalesNoPacks);
								$stringcomma = 1;
							}else{
								$stringcomma = 0;
							}
							
							$getcolor = json_decode($ArticleColor);
				
							
							if($stringcomma==1){
								$totalsalesnopack ="";
								foreach($getcolor as $key => $vl){
									$totalsalesnopack .= ($NoPacks1[$key] + $SalesNoPacks[$key]).",";
								}
								$totalsalesnopack = rtrim($totalsalesnopack,',');
							} else{
								$totalsalesnopack = ($RemainingNoPacks + $slnopack[0]->SalesNoPacks);
							}
							
							
							DB::table('inward')
							->where('ArticleId', $ArticleId)
							->update(['SalesNoPacks'=>$totalsalesnopack, 'updated_at'=>date('Y-m-d h:i:s')]);
							
						}else{
							//MIX
							$getdata = DB::select("SELECT NoPacks FROM `mixnopacks` where ArticleId='".$ArticleId."'");
							$totalsalesnopack = $getdata[0]->NoPacks + $RemainingNoPacks;
							DB::table('mixnopacks')
							->where('ArticleId', $ArticleId)
							->update(['NoPacks' => $totalsalesnopack]);
						}
					}
					DB::table('so')
					->where('Id', $SOID)
					->where('ArticleId', $ArticleId)
					->update(['Status'=>1, 'updated_at'=>date('Y-m-d h:i:s')]);
					
				}else{
					continue;
				}
			}
			DB::commit();
			return response()->json("SUCCESS", 200);
		} catch (\Exception $e) {
			DB::rollback();
			return response()->json("", 200);
		}
		
    }

	
	public function GetSOArticleData($SOId, $Id, $OWID){
		//echo "SELECT art.ArticleOpenFlag, art.ArticleNumber, art.ArticleRate, art.ArticleRatio, art.ArticleSize, art.ArticleColor, c.Colorflag, s.NoPacks, s.OutwardNoPacks as SalesNoPacks, o.OutwardBox, o.OutwardRate, o.OutwardWeight FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=p.CategoryId inner join outward o on o.Id=".$OWID." inner join so s on s.ArticleId=art.Id where art.Id='". $Id ."' and s.SoNumberId='". $SOId ."'";
		//exit;
		if($OWID==0){
			return DB::select("SELECT art.Id as ArticleId, s.NoPacks, s.OutwardNoPacks as SalesNoPacks, CommaZeroValue(s.OutwardNoPacks) as CheckSalesNoPacks  FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=p.CategoryId inner join so s on s.ArticleId=art.Id inner join articlerate artr on artr.ArticleId=art.Id where art.Id='". $Id ."' and s.SoNumberId='". $SOId ."'");
		}else{
			return DB::select("SELECT art.Id as ArticleId, s.NoPacks, s.OutwardNoPacks as SalesNoPacks, CommaZeroValue(s.OutwardNoPacks) as CheckSalesNoPacks, o.OutwardBox, o.OutwardRate, o.OutwardWeight FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=p.CategoryId inner join outward o on o.Id=".$OWID." inner join so s on s.ArticleId=art.Id where art.Id='". $Id ."' and s.SoNumberId='". $SOId ."'");
		}
		
		//return DB::select("SELECT c.Colorflag, s.NoPacks, s.OutwardNoPacks as SalesNoPacks, art.ArticleNumber, art.ArticleRate, art.ArticleColor, art.ArticleSize, art.ArticleRatio FROM `so` s inner join article art on art.Id=s.ArticleId inner join sonumber sn on sn.Id=s.SoNumberId inner join po p on p.ArticleId=art.Id inner join category c on c.Id=p.CategoryId where s.SoNumberId = '". $SOId ."' and s.ArticleId='". $Id ."'");
	}
	
	 public function GetSoChallen($id){
		// echo $id; exit;
		//$getsochallen = DB::select('select u.Name as UserName, pt.Name, pt.Address, pt.GSTNumber, son.SoDate, concat(FirstCharacterConcat(u.Name), son.SoNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as SoNumber, son.Transporter, son.Destination, son.Remarks, s.NoPacks, art.Id as ArticleId, art.ArticleOpenFlag, art.ArticleNumber, c.Title, c.Colorflag, art.ArticleColor, art.ArticleSize, art.ArticleRate, s.ArticleRate as SoArticleRate, art.ArticleRatio from so s inner join sonumber son on son.Id=s.SoNumberId inner join users u on u.Id=son.UserId inner join financialyear fn on fn.Id=son.FinancialYearId inner join article art on art.Id=s.ArticleId inner join po p on p.ArticleId=art.Id inner join purchasenumber pur on pur.Id=p.PO_Number inner join category c on c.Id=p.CategoryId inner join party pt on pt.Id=son.PartyId where s.SoNumberId=' . $id . ' order by s.Id ASC');
		$getsochallen = DB::select("select s.Id, u.Name as UserName, pt.Name, pt.Address, pt.PhoneNumber, pt.GSTNumber, son.SoDate, son.GSTAmount, son.GSTPercentage, son.GSTType, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber, son.Transporter, son.Destination, son.Remarks, s.NoPacks, art.Id as ArticleId, art.ArticleOpenFlag, art.ArticleNumber, c.Title, c.Colorflag, art.ArticleColor, art.ArticleSize, art.ArticleRate, s.ArticleRate as SoArticleRate, art.ArticleRatio from so s inner join sonumber son on son.Id=s.SoNumberId inner join users u on u.Id=son.UserId inner join financialyear fn on fn.Id=son.FinancialYearId inner join article art on art.Id=s.ArticleId left join po p on p.ArticleId=art.Id left join purchasenumber pur on pur.Id=p.PO_Number left join category c on c.Id=art.CategoryId inner join party pt on pt.Id=son.PartyId where s.SoNumberId='" . $id . "' group by s.Id order by s.Id ASC");
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
			
			
			$getart = $this->GetSOArticleData($id, $ArticleId, 0);
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
			//$TotalQuantityPic += $QuantityPic;
			
			if($ArticleOpenFlag==0){
				$ArticleRatio = $vl->ArticleRatio;
				
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
				
				//$QuantityPic = $TotalArticleRatio * $countNoSet;
				//$TotalQuantityPic += $QuantityPic;
			
				$ArticleRate = $vl->SoArticleRate;
				$Amount = $countNoSet * $ArticleRate;
				$TotalAmount += $Amount;
				
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
			}else{
				$countNoSet = $NoPacks;
				
				$ArticleRatio = "";
				$ArticleRate = $vl->SoArticleRate;
				$Amount = $countNoSet * $ArticleRate;
				$TotalAmount += $Amount;
				$ArticleColor = "";
				$ArticleSize = "";
			}
			// "QuantityPic"=>$QuantityPic, 
			$challendata[] = json_decode(json_encode(array("UserName"=>$UserName, "SoDate"=>$SoDate,"SoNumber"=>$SoNumber,"Remarks"=>$Remarks,"Name"=>$Name,"PhoneNumber"=>$PhoneNumber,"Address"=>$Address,"GSTNumber"=>$GSTNumber,"ArticleNumber"=>$ArticleNumber,"Title"=>$Title, "ArticleRatio"=>$ArticleRatio, "QuantityInSet"=>$NoPacks,"ArticleRate"=>number_format($ArticleRate, 2), "Amount"=>number_format($Amount, 2), "ArticleColor"=>$ArticleColor, "ArticleSize"=>$ArticleSize, "Transporter"=>$Transporter, "Destination"=>$Destination, "ArticleId"=>$ArticleId, "SendNoPacks" => $SendNoPacks, "RemainingNoPacks"=>$RemainingNoPacks)), false);
		}
		
		
		$TotalFinalAmount = 0;
		$SubTotalAmount = 0;
		$TotalFinalAmountDiscount = 0;
		$GSTLabel = "";
		$GSTValue = 0;
		$CGSTValue = 0;
		$SGSTValue = 0;
		
		if($TotalFinalAmount==0){
			$TotalFinalAmount = $TotalAmount;
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
		
		//echo "<pre>"; print_r($challendata); exit;
		//"TotalQuantityPic"=>$TotalQuantityPic, 
		//exit;
		$as  = array($challendata, array("TotalNoPacks"=>$TotalNoPacks, "TotalSendNoPacks" =>$TotalSendNoPacks, "TotalRemainingNoPacks" =>$TotalRemainingNoPacks, "TotalAmount"=>number_format($TotalAmount, 2),"RoundOff"=>$this->splitter(number_format($TotalFinalAmount, 2, '.', '')), "TotalFinalAmount"=>number_format($TotalFinalAmount, 2), "GSTLabel"=>$GSTLabel, "GSTPercentage"=>(int)$GSTPercentage,  "GSTValue"=>$GSTValue, "CGSTValue"=>number_format($CGSTValue, 2) , "SGSTValue"=>number_format($SGSTValue, 2), "GSTType"=>$GSTType)); 
		//$as  = array($challendata, array("TotalNoPacks"=>$TotalNoPacks, "TotalSendNoPacks" =>$TotalSendNoPacks, "TotalRemainingNoPacks" =>$TotalRemainingNoPacks, "TotalAmount"=>number_format($TotalAmount, 2)));
		//echo "<pre>"; print_r($as); exit;
		return $as;
		
		
 	 }
	 
	 function splitter($val)
	{
		$totalroundamount = $val; 
		$str = (string) $val;
		$splitted = explode(".",$str);
		
		$whole = (integer)$splitted[0];
		$num = (integer) $splitted[1];
		/* if($num>10){
			//echo "Num: ".$num;
			$num = $num.'0';
		} */
		
		$lennum = strlen($num);
		//echo $lennum;
		
		//print_r($splitted);
		//echo $num; 
		$den = (integer)  pow(10,strlen($splitted[1]));
		
		$number_var ="";
		$adjust_amount = "";
		if($num!=0){
			$roundoff_check = true;
			if($num>=50){
				//echo $num; 
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
	
	public function SalesReturnOutlet($id){
		 //SELECT a.ArticleNumber, a.Id FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId where son.PartyId='".$id."' group by s.ArticleId
		 return DB::select("SELECT p.Name, otn.OutletPartyId as Id FROM `outletnumber` otn inner join party p on p.Id=otn.OutletPartyId where otn.PartyId = '".$id."' group by otn.OutletPartyId");
	 }
	 
	 public function SalesReturnArticle($id){
		 //SELECT a.ArticleNumber, a.Id FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId where son.PartyId='".$id."' group by s.ArticleId
		 //return DB::select("SELECT a.ArticleNumber, a.Id FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id  where son.PartyId='".$id."' group by s.ArticleId union SELECT ArticleNumber, Id FROM `article` where ArticleOpenFlag=1");
		 return DB::select("SELECT a.ArticleNumber, a.Id FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id  where o.PartyId='".$id."' group by s.ArticleId union SELECT ArticleNumber, Id FROM `article` where ArticleOpenFlag=1");
	 }
	 
	 public function salesreturn_outletarticle($id){
		 return DB::select("SELECT a.ArticleNumber, a.Id FROM `outletnumber` oln inner join outlet o on o.OutletNumberId=oln.Id left join article a on a.Id=o.ArticleId where oln.OutletPartyId='".$id."' group by o.ArticleId");
	 }
	 
	 public function SalesReturn_ArticletoOutward($PartyId, $ArticleId, $Type){
		 if($Type==1){
			return DB::select("SELECT a.ArticleNumber, a.Id, son.Id as OutwardNumberId, son.OutletNumber , concat(son.Id, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber FROM `outletnumber` son inner join outlet s on s.OutletNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join financialyear fn on fn.Id=son.FinancialYearId  where son.OutletPartyId='".$PartyId."' and s.ArticleId = '".$ArticleId."' group by OutletNumber");
		 }else{
			 return DB::select("SELECT a.ArticleNumber, a.Id, own.Id as OutwardNumberId, own.OutwardNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, IFNULL(tpo.TransportStatus, 1) as TransportStatus FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId left join transportoutlet tpo on tpo.OutwardNumberId=own.Id where o.PartyId='".$PartyId."' and o.ArticleId = '".$ArticleId."' and IFNULL(tpo.TransportStatus, 1)=1  group by OutwardNumber");
			 //return DB::select("SELECT a.ArticleNumber, a.Id, own.Id as OutwardNumberId, own.OutwardNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, IFNULL(tpo.TransportStatus, 0) as TransportStatus FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId left join transportoutlet tpo on tpo.OutwardNumberId=own.Id where o.PartyId='".$PartyId."' and o.ArticleId = '".$ArticleId."' and tpo.TransportStatus!=0 group by OutwardNumber");
			//return DB::select("SELECT a.ArticleNumber, a.Id, own.Id as OutwardNumberId, own.OutwardNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId  where o.PartyId='".$PartyId."' and o.ArticleId = '".$ArticleId."' group by OutwardNumber");
		 }
		
	 }
	 
	 public function SalesReturn_OutwardGetData($PartyId, $ArticleId, $OutwardNumberId){
		 
		
		$partydata = DB::select("select OutletAssign from party where Id=".$PartyId);
		 //return print_r($partydata); exit;
		if($partydata[0]->OutletAssign==1){
			//$pendingoutlet = DB::select("select ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `transportoutwardpacks` onp inner join po p on p.ArticleId = onp.ArticleId inner join category c on c.Id=p.CategoryId where onp.ArticleId='".$ArticleId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp inner join po p on p.ArticleId = onp.ArticleId inner join category c on c.Id=p.CategoryId where onp.ArticleId='".$ArticleId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS B ON A.ColorId=B.ColorId LEFT JOIN ( SELECT sum(srp.NoPacks) as SalesReturn_NoPacks, srp.ColorId, srp.ArticleId, srp.Id, c.Colorflag  FROM `salesreturnpacks` srp inner join po p on p.ArticleId = srp.ArticleId inner join category c on c.Id=p.CategoryId where srp.ArticleId='".$ArticleId."' and srp.PartyId='".$PartyId."' group by srp.ColorId) AS C ON A.ColorId=C.ColorId) as d group by d.ArticleId) as ddd");
			$pendingoutlet = DB::select("select ddd.SalesNoPacks from (select d.ArticleId, (CASE d.Colorflag WHEN '0' THEN (d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outward_NoPacks - d.SalesReturn_NoPacks - d.Outlet_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT A.Outward_NoPacks, (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, A.ArticleId, A.Id, A.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outward_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag FROM `transportoutwardpacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArticleId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS A LEFT JOIN ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnopacks` onp left join po p on p.ArticleId = onp.ArticleId inner join article a on a.Id=onp.ArticleId inner join category c on c.Id=a.CategoryId where onp.ArticleId='".$ArticleId."' and onp.PartyId='".$PartyId."' group by onp.ColorId) AS B ON A.ColorId=B.ColorId LEFT JOIN ( SELECT sum(srp.NoPacks) as SalesReturn_NoPacks, srp.ColorId, srp.ArticleId, srp.Id, c.Colorflag  FROM `salesreturnpacks` srp left join po p on p.ArticleId = srp.ArticleId inner join article a on a.Id=srp.ArticleId inner join category c on c.Id=a.CategoryId where srp.ArticleId='".$ArticleId."' and srp.PartyId='".$PartyId."' group by srp.ColorId) AS C ON A.ColorId=C.ColorId) as d group by d.ArticleId) as ddd");
		}
			
		//$getdata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutwardId, o.NoPacks as OutwardNoPacks FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId where o.PartyId='".$PartyId."' and o.ArticleId = '".$ArticleId."' and o.OutwardNumberId='".$OutwardNumberId."' group by OutwardNumber");
	    $getdata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutwardId, o.NoPacks as OutwardNoPacks, o.OutwardRate FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where o.PartyId='".$PartyId."' and o.ArticleId = '".$ArticleId."' and o.OutwardNumberId='".$OutwardNumberId."' group by OutwardNumber");
		//return $getdata; exit;
		//RETURN print_r($getdata); 
		//print_r($as); 
		//exit;
		if($getdata[0]->ArticleOpenFlag==0){
			//echo "True"; exit;
			
				
			if($getdata[0]->Colorflag==1){
				$as = DB::select("select GROUP_CONCAT(CONCAT(ddd.NoPacks) SEPARATOR ',') as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `salesreturnpacks` where OutwardId= '".$getdata[0]->OutwardId."' group by ColorId) as ddd");
				
				
				
				//return $as; exit;
				$SalesReturnNoPacks = "";
				if($as[0]->SalesReturnNoPacks!=""){
					$SalesReturnNoPacks = $as[0]->SalesReturnNoPacks;
					$npacks = explode(",", $getdata[0]->OutwardNoPacks);
					$spacks = explode(",", $as[0]->SalesReturnNoPacks);
					//print_r($npacks); exit;
					$newdata  = "";
					foreach($npacks as $key => $vl){
						$newdata .= ($vl - $spacks[$key]).',';
					}
					$newdata = rtrim($newdata, ',');
				}else{
					$newdata = $getdata[0]->OutwardNoPacks;
				}
				
				foreach($getdata as $key => $val){
					$object = (object)$val;
					
					if($SalesReturnNoPacks!=""){
						$object->SalesReturnNoPacks = $SalesReturnNoPacks;
					} else{
						$object->SalesReturnNoPacks = "";
					}
					
					$object->OutwardNoPacks_New = $newdata;
					if($partydata[0]->OutletAssign==1){
						$object->Outlet_Total_PNoPacks = $pendingoutlet[0]->SalesNoPacks;
						$object->OutletParty = 1;
					}else{
						$object->OutletParty = 0; 
					}
				}
				
				
				//return $getdata; exit;
			} else{
				$as = DB::select("select ddd.NoPacks as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `salesreturnpacks` where OutwardId= '".$getdata[0]->OutwardId."' group by ColorId) as ddd");
				//return $as; exit;
				$spacks = "";
				if(isset($as[0]->SalesReturnNoPacks)!=""){
					$npacks = $getdata[0]->OutwardNoPacks;
					$spacks = $as[0]->SalesReturnNoPacks;
					//print_r($npacks); exit;
					$newdata = $npacks - $spacks;
				} else{
					$newdata = $getdata[0]->OutwardNoPacks;
				}
				
				foreach($getdata as $key => $val){
					$object = (object)$val;
					
					if($spacks!=""){
						$object->SalesReturnNoPacks = $spacks;
					} else{
						$object->SalesReturnNoPacks = "";
					}
					
					
					$object->OutwardNoPacks_New = $newdata;
					if($partydata[0]->OutletAssign==1){
						$object->Outlet_Total_PNoPacks = $pendingoutlet[0]->SalesNoPacks;
						$object->OutletParty = 1;
					}else{
						$object->OutletParty = 0; 
					}
				}
				
			//	print_r($getdata); exit;
			}
			
		}else{
			//return $getdata[0]->OutwardId; exit;
			//echo "False"; exit;
			$as = DB::select("select ddd.NoPacks as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `salesreturnpacks` where OutwardId= '".$getdata[0]->OutwardId."' group by ColorId) as ddd");
			//return $as; exit;
				
			foreach($getdata as $key => $val){
				$object = (object)$val;
				if(isset($as[0]->SalesReturnNoPacks)!=""){
					$spacks = $as[0]->SalesReturnNoPacks;
					$newdata = $getdata[0]->OutwardNoPacks - $as[0]->SalesReturnNoPacks;
					$object->SalesReturnNoPacks = $newdata;
				} else{
					$newdata = $getdata[0]->OutwardNoPacks;
					$object->SalesReturnNoPacks = $newdata;
				}
				$object->OutwardNoPacks_New = $newdata;
				if($partydata[0]->OutletAssign==1){
					
					if(!empty($pendingoutlet)){
						$object->Outlet_Total_PNoPacks = $pendingoutlet[0]->SalesNoPacks;
					} else{
						$object->Outlet_Total_PNoPacks = 0;
					}
					$object->OutletParty = 1;
				}else{
					$object->OutletParty = 0; 
				}
			}
			
			
		}
		//exit;
		//return print_r($newdata); exit;
	
		return $getdata;
	 }
	 
	 
	 
	 
	 public function SalesReturn_OutletGetData($PartyId, $ArticleId, $OutwardNumberId){
		
		$partydata = DB::select("select OutletAssign from party where Id=".$PartyId);
		 //return print_r($partydata); exit;
		if($partydata[0]->OutletAssign==1){
			//$pendingoutlet = DB::select("select ddd.SalesNoPacks from (select d.ArticleId,d.Outlet_NoPacks,d.SalesReturn_NoPacks, (CASE d.Colorflag WHEN '0' THEN (d.Outlet_NoPacks - d.SalesReturn_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outlet_NoPacks - d.SalesReturn_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, B.ArticleId, B.Id, B.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnumber` otn inner join outlet o on o.OutletNumberId = otn.Id left join po p on p.ArticleId = o.ArticleId inner join article a on a.Id=o.ArticleId inner join category c on c.Id=a.CategoryId  left join  `outletnopacks` onp on onp.OutletId=o.Id where o.ArticleId = ".$ArticleId." and otn.Id = ".$OutwardNumberId." group by onp.ColorId) AS B LEFT JOIN ( SELECT sum(osrp.NoPacks) as SalesReturn_NoPacks, osrp.ColorId FROM `outletnumber` otn inner join outlet o on o.OutletNumberId = otn.Id left join outletnopacks otp on otp.OutletId=o.Id inner join outletsalesreturnpacks osrp on osrp.OutletId=o.Id where o.ArticleId = ".$ArticleId." and otn.Id = ".$OutwardNumberId." group by osrp.ColorId) AS C ON B.ColorId=C.ColorId) as d group by d.ArticleId) as ddd");
			$pendingoutlet = DB::select("select ddd.SalesNoPacks from (select d.ArticleId,d.Outlet_NoPacks,d.SalesReturn_NoPacks, (CASE d.Colorflag WHEN '0' THEN (d.Outlet_NoPacks - d.SalesReturn_NoPacks - d.OSalesReturn_NoPacks) ELSE GROUP_CONCAT(CONCAT((d.Outlet_NoPacks - d.SalesReturn_NoPacks - d.OSalesReturn_NoPacks)) ORDER BY d.Id SEPARATOR ',') END) as SalesNoPacKs from (SELECT (case WHEN C.SalesReturn_NoPacks IS NULL THEN '0' ELSE C.SalesReturn_NoPacks END) as SalesReturn_NoPacks, (case WHEN D.OSalesReturn_NoPacks IS NULL THEN '0' ELSE D.OSalesReturn_NoPacks END) as OSalesReturn_NoPacks, (case WHEN B.Outlet_NoPacks IS NULL THEN '0' ELSE B.Outlet_NoPacks END) as Outlet_NoPacks, B.ArticleId, B.Id, B.Colorflag FROM ( SELECT sum(onp.NoPacks) as Outlet_NoPacks, onp.ColorId, onp.ArticleId, onp.Id, c.Colorflag  FROM `outletnumber` otn inner join outlet o on o.OutletNumberId = otn.Id left join po p on p.ArticleId = o.ArticleId inner join article a on a.Id=o.ArticleId inner join category c on c.Id=a.CategoryId  left join  `outletnopacks` onp on onp.OutletId=o.Id where o.ArticleId = ".$ArticleId." and otn.Id = ".$OutwardNumberId." group by onp.ColorId) AS B LEFT JOIN ( SELECT sum(osrp.NoPacks) as SalesReturn_NoPacks, osrp.ColorId FROM `outletnumber` otn inner join outlet o on o.OutletNumberId = otn.Id left join outletnopacks otp on otp.OutletId=o.Id inner join outletsalesreturnpacks osrp on osrp.OutletId=o.Id where o.ArticleId = ".$ArticleId." and otn.Id = ".$OutwardNumberId." group by osrp.ColorId) AS C ON B.ColorId=C.ColorId LEFT JOIN ( select sum(f.NoPacks) as OSalesReturn_NoPacks, f.ColorId from (SELECT srp.Id, srp.NoPacks, srp.ColorId FROM `outletnumber` otn inner join outlet o on o.OutletNumberId = otn.Id left join outletnopacks otp on otp.OutletId=o.Id inner join salesreturnpacks srp on srp.OutwardId=o.Id where o.ArticleId = ".$ArticleId." and otn.Id = ".$OutwardNumberId." and srp.PartyId=".$PartyId." and srp.Outletflag = '1' group by srp.Id) as f group by f.ColorId) AS D ON B.ColorId=D.ColorId) as d group by d.ArticleId) as ddd");
		}
		//return $pendingoutlet;
		$getdata = DB::select("SELECT a.ArticleNumber, s.ArticleRate, CASE WHEN (oi.ArticleColor IS NULL) THEN a.ArticleColor ELSE oi.ArticleColor END AS `ArticleColor`, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, a.StyleDescription, a.ArticleStatus, a.CategoryId, a.SubCategoryId, a.SeriesId, a.BrandId, a.Orderset, a.OpeningStock, c.Colorflag, s.Id as OutletId, s.NoPacks as OutletNoPacks FROM `outletnumber` son inner join outlet s on s.OutletNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join financialyear fn on fn.Id=son.FinancialYearId inner join category c on c.Id=a.CategoryId left join outletimport oi on oi.ArticleId=s.ArticleId where son.OutletPartyId='".$PartyId."' and s.ArticleId = '".$ArticleId."' and son.Id='".$OutwardNumberId."' group by OutletNumber");
		//return $getdata; exit;
		//RETURN print_r($getdata); 
		//print_r($as); 
		//exit;
		if($getdata[0]->ArticleOpenFlag==0){
			//echo "True"; exit;
			
				
			if($getdata[0]->Colorflag==1){
				$as = DB::select("select GROUP_CONCAT(CONCAT(ddd.NoPacks) SEPARATOR ',') as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `outletsalesreturnpacks` where OutletId= '".$getdata[0]->OutletId."' group by ColorId) as ddd");
				$s_as = DB::select("select GROUP_CONCAT(CONCAT(ddd.NoPacks) SEPARATOR ',') as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `salesreturnpacks` where OutwardId= '".$getdata[0]->OutletId."' group by ColorId) as ddd");
				
				
				
				//return $s_as; exit;
				$SalesReturnNoPacks = "";
				if($as[0]->SalesReturnNoPacks!=""){
					$SalesReturnNoPacks = $as[0]->SalesReturnNoPacks;
					$npacks = explode(",", $getdata[0]->OutletNoPacks);
					//$npacks = explode(",", $pendingoutlet[0]->SalesNoPacks;
					$spacks = explode(",", $as[0]->SalesReturnNoPacks);
					$s_spacks = explode(",", $s_as[0]->SalesReturnNoPacks);
					
					//print_r($npacks); exit;
					$newdata  = "";
					foreach($npacks as $key => $vl){
						$newdata .= ($vl - $spacks[$key]).',';
						/* if($s_spacks!=""){
							$newdata .= ($newdata - $s_spacks[$key]);
						}
						$newdata .= $newdata.','; */
					}
					$newdata = rtrim($newdata, ',');
					//$object->OutletNoPacks = $newdata;
				}else{
					$newdata = $getdata[0]->OutletNoPacks;
					//$object->OutletNoPacks = $newdata - $as[0]->SalesReturnNoPacks;
				}
				
				
				if($s_as[0]->SalesReturnNoPacks!=""){
					$SalesReturnNoPacks = $s_as[0]->SalesReturnNoPacks;
					$npacks = explode(",", $newdata);
					//$npacks = explode(",", $pendingoutlet[0]->SalesNoPacks;
					$s_spacks = explode(",", $s_as[0]->SalesReturnNoPacks);
					
					//print_r($npacks); exit;
					$newdata1  = "";
					foreach($npacks as $key => $vl){
						$newdata1 .= ($vl - $s_spacks[$key]).',';
					}
					$newdata = rtrim($newdata1, ',');
					//$object->OutletNoPacks = $newdata;
				}else{
					$newdata = $getdata[0]->OutletNoPacks;
					//$object->OutletNoPacks = $newdata - $as[0]->SalesReturnNoPacks;
				}
				
				
				foreach($getdata as $key => $val){
					$object = (object)$val;
					$object->OutletNoPacks = $newdata;
					if($SalesReturnNoPacks!=""){
						$object->SalesReturnNoPacks = $SalesReturnNoPacks;
					} else{
						$object->SalesReturnNoPacks = "";
					}
				}
				
				
				return $getdata; exit;
			} else{
				$as = DB::select("select ddd.NoPacks as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `outletsalesreturnpacks` where OutletId= '".$getdata[0]->OutletId."' group by ColorId) as ddd");
				$s_as = DB::select("select ddd.NoPacks as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `salesreturnpacks` where OutwardId= '".$getdata[0]->OutletId."' group by ColorId) as ddd");
				
				//return $as; exit;
				$spacks = "";
				if(isset($as[0]->SalesReturnNoPacks)!=""){
					$npacks = $getdata[0]->OutletNoPacks;
					$spacks = $as[0]->SalesReturnNoPacks;
					$s_spacks = $s_as[0]->SalesReturnNoPacks;
					//print_r($npacks); exit;
					$newdata = $npacks - $spacks;
				} else{
					$newdata = $getdata[0]->OutletNoPacks;
				}
				
				foreach($getdata as $key => $val){
					$object = (object)$val;
					$object->OutletNoPacks = $newdata;
					if($spacks!=""){
						$object->SalesReturnNoPacks = $spacks;
					} else{
						$object->SalesReturnNoPacks = "";
					}
					
				}
				
			//return	print_r($getdata); exit;
			}
			
		}else{
			//return $getdata[0]->OutwardId; exit;
			//echo "False"; exit;
			$as = DB::select("select ddd.NoPacks as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `outletsalesreturnpacks` where OutletId= '".$getdata[0]->OutletId."' group by ColorId) as ddd");
			$s_as = DB::select("select ddd.NoPacks as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `salesreturnpacks` where OutwardId= '".$getdata[0]->OutletId."' group by ColorId) as ddd");
			//return $as; exit;
				
			foreach($getdata as $key => $val){
				$object = (object)$val;
				if(isset($as[0]->SalesReturnNoPacks)!=""){
					$spacks = $as[0]->SalesReturnNoPacks;
					$newdata = $getdata[0]->OutletNoPacks - $as[0]->SalesReturnNoPacks - $s_as[0]->SalesReturnNoPacks;
					$object->SalesReturnNoPacks = $newdata;
				} else{
					$newdata = $getdata[0]->OutletNoPacks;
					$object->SalesReturnNoPacks = $newdata;
				}
			}
			
			/* foreach($getdata as $key => $val){
				$object = (object)$val;
				if(isset($as[0]->SalesReturnNoPacks)!=""){
					$spacks = $as[0]->SalesReturnNoPacks;
					$newdata = $getdata[0]->OutwardNoPacks - $as[0]->SalesReturnNoPacks;
					$object->SalesReturnNoPacks = $newdata;
				} else{
					$newdata = $getdata[0]->OutwardNoPacks;
					$object->SalesReturnNoPacks = $newdata;
				}
				$object->OutwardNoPacks_New = $newdata;
				if($partydata[0]->OutletAssign==1){
					
					if(!empty($pendingoutlet)){
						$object->Outlet_Total_PNoPacks = $pendingoutlet[0]->SalesNoPacks;
					} else{
						$object->Outlet_Total_PNoPacks = 0;
					}
					$object->OutletParty = 1;
				}else{
					$object->OutletParty = 0; 
				}
			} */
			
		}
	
		return $getdata;
	 }
	 
	 public function SalesReturnGetArticleData($ArtId){
		 return DB::select("SELECT a.*,c.Colorflag FROM `article` a inner join category c on c.Id=a.CategoryId where a.Id = '".$ArtId."'");
	 }
	 
	 public function GenerateSRNumber($UserId)
    { 
		$array = array();
		$fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
        $srnumberdata = DB::select('SELECT Id, FinancialYearId, SalesReturnNumber From salesreturnnumber order by Id desc limit 0,1');
		
		if(count($srnumberdata)>0){
			if($fin_yr[0]->Id > $srnumberdata[0]->FinancialYearId){
				$array["SR_Number"] = 1;
				$array["SR_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["SR_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			} else{
				$array["SR_Number"] = ($srnumberdata[0]->SalesReturnNumber) + 1;
				$array["SR_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["SR_Number_Financial"] = ($srnumberdata[0]->SalesReturnNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			}
		}
        else{
			$array["SR_Number"] = 1;
			$array["SR_Number_Financial_Id"] = $fin_yr[0]->Id;
			$array["SR_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
			return $array;
        }
    }
	
	
	
	
	 public function AddSalesReturn(Request $request){
		$data = $request->all();
		$OutletPartyId = 0;
		if($data['OutletPartyId']!=""){				
			$OutletPartyId = $data['OutletPartyId'];			
		}
		
		if($data['SRNumberId']=="Add"){
			$generate_SRNUMBER = $this->GenerateSRNumber($data['UserId']);
			$SR_Number = $generate_SRNUMBER['SR_Number'];
			$SR_Number_Financial_Id = $generate_SRNUMBER['SR_Number_Financial_Id'];
			//$generate_SONUMBER['SO_Number'];
			//$generate_SONUMBER['SO_Number_Financial'];
			//$generate_SONUMBER['SO_Number_Financial_Id'];
			$SRNumberId = DB::table('salesreturnnumber')->insertGetId(
				['SalesReturnNumber' =>  $SR_Number,"FinancialYearId"=>$SR_Number_Financial_Id,'PartyId' =>  $data['PartyId'],'OutletPartyId'=>$OutletPartyId,'Remarks'=>$data['Remark'],'CreatedDate' => date('Y-m-d H:i:s')]
			);
		}else{
			$checksonumber = DB::select("SELECT SalesReturnNumber FROM `salesreturnnumber` where Id ='".$data['SRNumberId']."'");
			//echo "SELECT SoNumber FROM `sonumber` where Id ='".$data['SoNumberId']."'"; exit;
			
			if(!empty($checksonumber)){
				$SR_Number = $checksonumber[0]->SalesReturnNumber;
				$SRNumberId = $data['SRNumberId'];
				
				DB::table('salesreturnnumber')
				->where('Id', $SRNumberId)
				->update(['PartyId' =>  $data['PartyId'],'OutletPartyId'=>$OutletPartyId, 'Remarks'=>$data['Remark']]);
			}
		}
		
		
		
		
		if($data['OutletPartyId']!=""){	
			if($data['ArticleOpenFlag']==1){
				if(isset($data['NoPacksNew'])==""){
					return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
				}
				
				if($data['partyflag']==true){
					if($data['NoPacks_TotalOutlet'] < $data['NoPacksNew']){
						return response()->json(array("id"=>"", "OutletNoOfSetNotMatch"=>"true"), 200);
					}
				}
					
				if($data['NoPacks'] < $data['NoPacksNew']){
					return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
				}			
				//print_r($data); exit;
				$getdata = DB::select("SELECT * FROM `mixnopacks` where ArticleId='".$data["ArticleId"]."'");
				$getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutwardId, o.NoPacks as OutwardNoPacks FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId inner join category c on c.Id=a.CategoryId where o.PartyId='".$data["PartyId"]."' and o.ArticleId = '".$data["ArticleId"]."' and o.OutwardNumberId='".$data["OutwardNumberId"]."' group by OutwardNumber");
				$OutwardId = $getalldata[0]->OutwardId;
				//print_r($getdata); print_r($data); print_r($getalldata); exit;
				if(!empty($getdata)){
					$InwardNoPacks = $getdata[0]->NoPacks;
					$NoPacks = $data["NoPacksNew"];
					
					$totalnopacks = ($InwardNoPacks+$NoPacks);
					//return $totalnopacks; exit;
					
					DB::beginTransaction();
					try {
						//$ddd= array("SalesReturnNumber"=>1,'OutwardId'=>$OutwardId,'PartyId' =>  $data['PartyId'],'OutletPartyId'=>$OutletPartyId,'ArticleId'=>$data['ArticleId'], 'NoPacks' =>  $NoPacks, 'UserId' =>  $data['UserId'], 'CreatedDate'=>date('Y-m-d H:i:s'));
						//print_r($ddd); exit;
						DB::table('mixnopacks')
						->where('ArticleId', $data['ArticleId'])
						->update(['NoPacks'=>$totalnopacks]);
						
						/* $salesreturn_numberid = DB::table('salesreturnnumber')->insertGetId(
						['SalesReturnNumber'=>$SR_Number,"FinancialYearId"=>$SR_Number_Financial_Id, 'CreatedDate' => date('Y-m-d H:i:s')]); */
				
				
						$salesreturnId = DB::table('salesreturn')->insertGetId(
							["SalesReturnNumber"=>$SRNumberId,'OutwardId'=>$OutwardId,'ArticleId'=>$data['ArticleId'], 'NoPacks' =>  $NoPacks, 'UserId' =>  $data['UserId'], 'CreatedDate'=>date('Y-m-d H:i:s')]
						);
						
						DB::table('salesreturnpacks')->insertGetId(
							['SalesReturnId'=>$salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId'=>0, 'OutwardId'=> $OutwardId,'NoPacks'=>$NoPacks, 'PartyId' =>$data["PartyId"],'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
						);
						
						DB::commit();
						return response()->json(array("SRNumberId"=>$SRNumberId, "id"=>"SUCCESS"), 200);
					} catch (\Exception $e) {
						DB::rollback();
						return response()->json("", 200);
					}
				}else{
					return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
				}
			}else{
				/* $partyoutletcheck = DB::select("SELECT OutletAssign FROM `party` where Id='".$data['PartyId']."'");
				if($partyoutletcheck[0]->OutletAssign==1){ */
					/* $getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutwardId, o.NoPacks as OutwardNoPacks FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where o.PartyId='".$data["PartyId"]."' and o.ArticleId = '".$data["ArticleId"]."' and o.OutwardNumberId='".$data["OutwardNumberId"]."' group by OutwardNumber");
				
				} else{ */
					$checkoutlet = DB::select("SELECT count(*) as Total FROM `outletimport` where ArticleId='".$data['ArticleId']."'");
					
					if($checkoutlet[0]->Total>0){
						return response()->json(array("id"=>"", "StockUpload"=>"true"), 200);
					}
					//	$getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutwardId, o.NoPacks as OutwardNoPacks FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where o.PartyId='".$data["PartyId"]."' and o.ArticleId = '".$data["ArticleId"]."' and o.OutwardNumberId='".$data["OutwardNumberId"]."' group by OutwardNumber");
					
						
				//}
				
				//$getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutwardId, o.NoPacks as OutwardNoPacks FROM article a inner join outlet o on o.ArticleId=a.Id left join outletnumber own on own.Id=o.OutletNumberId inner join financialyear fn on fn.Id=own.FinancialYearId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where own.OutletPartyId='".$data["PartyId"]."' and o.ArticleId = '".$data["ArticleId"]."' and o.OutletNumberId='".$data["OutwardNumberId"]."' group by OutletNumber");
				$getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutletId, o.NoPacks as OutletNoPacks FROM article a inner join outlet o on o.ArticleId=a.Id left join outletnumber own on own.Id=o.OutletNumberId inner join financialyear fn on fn.Id=own.FinancialYearId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where own.Id='".$data["OutwardNumberId"]."' and o.ArticleId = '".$data["ArticleId"]."' and o.OutletNumberId='".$data["OutwardNumberId"]."' group by OutletNumber");
			
			
				$getdata = DB::select("SELECT SalesNoPacks FROM `inward` where ArticleId='".$data['ArticleId']."'");
				$InwardSalesNoPacks = $getdata[0]->SalesNoPacks;
					
				//$getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutwardId, o.NoPacks as OutwardNoPacks FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where o.PartyId='".$data["PartyId"]."' and o.ArticleId = '".$data["ArticleId"]."' and o.OutwardNumberId='".$data["OutwardNumberId"]."' group by OutwardNumber");
				
				if($getalldata[0]->ArticleOpenFlag==0 && $getalldata[0]->Colorflag==0){
					
					if($data['NoPacksNew']==""){
						return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
					}
					
					if($data['partyflag']==true){
						if($data['NoPacks_TotalOutlet'] < $data['NoPacksNew']){
							return response()->json(array("id"=>"", "OutletNoOfSetNotMatch"=>"true"), 200);
						}
					}
					
					//return $data['NoPacks_TotalOutlet']; exit;
					$as = DB::select("select sum(ddd.NoPacks) as TotalNoPacks from (select NoPacks FROM `salesreturnpacks` where OutwardId= '".$getalldata[0]->OutletId."' and Outletflag='1' group by NoPacks) as ddd");
					//return $as; exit;
					$spacks = "";
					if(isset($as[0]->TotalNoPacks)!=""){
						$npacks = $getalldata[0]->OutletNoPacks;
						$spacks = $as[0]->TotalNoPacks;
						$newdata = $npacks - $spacks;
						$NoPacksNew = $data['NoPacksNew'];
						if($newdata < $NoPacksNew){
							return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
						}
					} else{
						$newdata = $getalldata[0]->OutletNoPacks;
					}
					
				} else{
					$as = DB::select("select GROUP_CONCAT(CONCAT(ddd.NoPacks) SEPARATOR ',') as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `salesreturnpacks` where OutwardId= '".$getalldata[0]->OutletId."' and Outletflag='1' group by ColorId) as ddd");
					$SalesReturnNoPacks = "";
					if($as[0]->SalesReturnNoPacks!=""){
						$SalesReturnNoPacks = $as[0]->SalesReturnNoPacks;
						$npacks = explode(",", $getalldata[0]->OutletNoPacks);
						$spacks = explode(",", $as[0]->SalesReturnNoPacks);
						//print_r($npacks); exit;
						$newdata  = "";
						foreach($npacks as $key => $vl){
							$newdata .= ($vl - $spacks[$key]).',';
						}
						$newdata = rtrim($newdata, ',');
					}else{
						$newdata = $getalldata[0]->OutletNoPacks;
					}
				}
				
				/* print_r($data);
				print_r($getdata);
				print_r($getalldata);
				print_r($as);
				exit; */
				$search = $getalldata[0]->OutletNoPacks;
				$OutwardId = $getalldata[0]->OutletId;
				
				$NoPacks = "";
				$SalesNoPacks = "";
				$UpdateInwardNoPacks = "";
				$searchString = ',';
				if( strpos($search, $searchString) !== false ) {
					//echo "fffff";
					$string = explode(',', $search);
					$InwardSalesNoPacks = explode(',', $InwardSalesNoPacks);
					$stringcomma = 1;
				} 
				else{
					//echo "333";
					$search;
					$InwardSalesNoPacks = $InwardSalesNoPacks;
					$stringcomma = 0;
				}
				//echo $InwardSalesNoPacks;
				//exit;
				if($data['ArticleColorFlag']=="Yes"){
					foreach($data['ArticleSelectedColor'] as $key => $vl){
						$numberofpacks = $vl["Id"];
						$InwardSalesNoPacks_VL = $InwardSalesNoPacks[$key];
						
						if($data["NoPacksNew_".$numberofpacks]!=""){
							if($stringcomma==1){
								if($data['partyflag']==true){
									if($data['NoPacks_TotalOutlet_'.$numberofpacks] < $data['NoPacksNew_'.$numberofpacks]){
										return response()->json(array("id"=>"", "OutletNoOfSetNotMatch"=>"true"), 200);
									}
								} else{
									if($data["NoPacks_".$numberofpacks]<$data["NoPacksNew_".$numberofpacks]){
										return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
									}
								}
								
								$SalesNoPacks .= ($data["NoPacks_".$numberofpacks] - $data["NoPacksNew_".$numberofpacks]).",";
								$UpdateInwardNoPacks .= ($InwardSalesNoPacks_VL + $data["NoPacksNew_".$numberofpacks]).",";
							}else{
								if($data['partyflag']==true){
									if($data['NoPacks_TotalOutlet_'.$numberofpacks] < $data['NoPacksNew_'.$numberofpacks]){
										return response()->json(array("id"=>"", "OutletNoOfSetNotMatch"=>"true"), 200);
									}
								} else{
									if($data["NoPacks_".$numberofpacks]<$data["NoPacksNew_".$numberofpacks]){
										return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
									}
								}
								
								$SalesNoPacks .= ($data["NoPacks_".$numberofpacks] - $data["NoPacksNew_".$numberofpacks]).",";
								$UpdateInwardNoPacks .= ($InwardSalesNoPacks + $data["NoPacksNew_".$numberofpacks]).",";
							}
							$NoPacks .= $data["NoPacksNew_".$numberofpacks].",";
						}
						else{
							$NoPacks .= "0,";
							$SalesNoPacks .= $data["NoPacks_".$numberofpacks].",";
							$UpdateInwardNoPacks .= ($InwardSalesNoPacks_VL + $data["NoPacksNew_".$numberofpacks]).",";
						}
					}
					$NoPacks = rtrim($NoPacks,',');
				}else{
					if(isset($data['NoPacksNew'])){
						$NoPacks = $data['NoPacksNew'];
						$SalesNoPacks .= ($data["NoPacks"] - $data['NoPacksNew']);
						$UpdateInwardNoPacks = ($InwardSalesNoPacks + $NoPacks).",";
						/* echo $InwardSalesNoPacks;
						echo "\n"; 
						echo $UpdateInwardNoPacks = ($InwardSalesNoPacks + $NoPacks);
						echo "\n";
						exit;*/
					} 
					else{
						return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
					}
				}
				
				
				$SalesNoPacks = rtrim($SalesNoPacks,',');
				$UpdateInwardNoPacks = rtrim($UpdateInwardNoPacks,',');
				/* return "SalesNoPacks - ".$SalesNoPacks."----"."UpdateInwardNoPacks - ".$UpdateInwardNoPacks;
				echo "UpdateInwardNoPacks - ".$UpdateInwardNoPacks;
				exit;  */
				
				
				$CheckSalesNoPacks = explode(',', $NoPacks);
				//return $CheckSalesNoPacks; exit;
				$tmp = array_filter($CheckSalesNoPacks);
				if (empty($tmp)) {
					//echo "All zeros!";
					return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
				}
				
				DB::beginTransaction();
				try {
					
					$salesreturnId = DB::table('salesreturn')->insertGetId(
						["SalesReturnNumber"=>$SRNumberId,'OutwardId'=>$OutwardId, 'Outletflag'=>1, 'ArticleId'=>$data['ArticleId'],'NoPacks'=>$NoPacks,'UserId'=>$data['UserId'],'CreatedDate'=>date('Y-m-d H:i:s')]
					);
					
					DB::table('inward')
					->where('ArticleId', $data['ArticleId'])
					->update(['SalesNoPacks'=>$UpdateInwardNoPacks]);
					
					if($data['ArticleOpenFlag']==0){
						if( strpos($NoPacks, ',') !== false ) {
							$NoPacks = explode(',', $NoPacks);
							foreach($data['ArticleSelectedColor'] as $key => $vl){
								$numberofpacks = $vl["Id"];
								DB::table('salesreturnpacks')->insertGetId(
									['SalesReturnId'=>$salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId'=>$numberofpacks, 'OutwardId'=> $OutwardId, 'Outletflag'=>1,'NoPacks'=>$NoPacks[$key], 'PartyId' =>$data["PartyId"], 'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
								);
							}
						}else{
							foreach($data['ArticleSelectedColor'] as $key => $vl){
								$numberofpacks = $vl["Id"];
								DB::table('salesreturnpacks')->insertGetId(
									['SalesReturnId'=>$salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId'=>$numberofpacks, 'OutwardId'=> $OutwardId, 'Outletflag'=>1,'NoPacks'=>$NoPacks, 'PartyId' =>$data["PartyId"],'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
								);
							}
						}
					}else{
						DB::table('salesreturnpacks')->insertGetId(
							['SalesReturnId'=>$salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId'=>0, 'OutwardId'=> $OutwardId, 'Outletflag'=>1,'NoPacks'=>$NoPacks, 'PartyId' =>$data["PartyId"],'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
						);
					}
						
					DB::commit();
					return response()->json(array("SRNumberId"=>$SRNumberId, "id"=>"SUCCESS"), 200);
				} catch (\Exception $e) {
					DB::rollback();
					return response()->json(array("id"=>""), 200);
				}
			}
		}
		else{
			
			if($data['ArticleOpenFlag']==1){
				if(isset($data['NoPacksNew'])==""){
					return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
				}
				
				if($data['partyflag']==true){
					if($data['NoPacks_TotalOutlet'] < $data['NoPacksNew']){
						return response()->json(array("id"=>"", "OutletNoOfSetNotMatch"=>"true"), 200);
					}
				}
					
				if($data['NoPacks'] < $data['NoPacksNew']){
					return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
				}			
				//print_r($data); exit;
				$getdata = DB::select("SELECT * FROM `mixnopacks` where ArticleId='".$data["ArticleId"]."'");
				$getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutwardId, o.NoPacks as OutwardNoPacks FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId inner join category c on c.Id=a.CategoryId where o.PartyId='".$data["PartyId"]."' and o.ArticleId = '".$data["ArticleId"]."' and o.OutwardNumberId='".$data["OutwardNumberId"]."' group by OutwardNumber");
				$OutwardId = $getalldata[0]->OutwardId;
				//print_r($getdata); print_r($data); print_r($getalldata); exit;
				if(!empty($getdata)){
					$InwardNoPacks = $getdata[0]->NoPacks;
					$NoPacks = $data["NoPacksNew"];
					
					$totalnopacks = ($InwardNoPacks+$NoPacks);
					//return $totalnopacks; exit;
					
					DB::beginTransaction();
					try {
						//$ddd= array("SalesReturnNumber"=>1,'OutwardId'=>$OutwardId,'PartyId' =>  $data['PartyId'],'OutletPartyId'=>$OutletPartyId,'ArticleId'=>$data['ArticleId'], 'NoPacks' =>  $NoPacks, 'UserId' =>  $data['UserId'], 'CreatedDate'=>date('Y-m-d H:i:s'));
						//print_r($ddd); exit;
						DB::table('mixnopacks')
						->where('ArticleId', $data['ArticleId'])
						->update(['NoPacks'=>$totalnopacks]);
						
						/* $salesreturn_numberid = DB::table('salesreturnnumber')->insertGetId(
						['SalesReturnNumber'=>$SR_Number,"FinancialYearId"=>$SR_Number_Financial_Id, 'CreatedDate' => date('Y-m-d H:i:s')]); */
				
				
						$salesreturnId = DB::table('salesreturn')->insertGetId(
							["SalesReturnNumber"=>$SRNumberId,'OutwardId'=>$OutwardId,'Outletflag'=>0,'ArticleId'=>$data['ArticleId'], 'NoPacks'=>$NoPacks, 'UserId'=>$data['UserId'],'OutwardRate'=>$data['OutwardRate'], 'CreatedDate'=>date('Y-m-d H:i:s')]
						);
						
						DB::table('salesreturnpacks')->insertGetId(
							['SalesReturnId'=>$salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId'=>0, 'OutwardId'=> $OutwardId, 'Outletflag'=>0,'NoPacks'=>$NoPacks, 'PartyId' =>$data["PartyId"],'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
						);
						
						DB::commit();
						return response()->json(array("SRNumberId"=>$SRNumberId, "id"=>"SUCCESS"), 200);
					} catch (\Exception $e) {
						DB::rollback();
						return response()->json("", 200);
					}
				}else{
					return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
				}
			}else{
				/* echo "SELECT SalesNoPacks FROM `inward` where ArticleId='".$data['ArticleId']."'";
				exit; */
				$getdata = DB::select("SELECT SalesNoPacks FROM `inward` where ArticleId='".$data['ArticleId']."'");
				$InwardSalesNoPacks = $getdata[0]->SalesNoPacks;
				
				//$getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutwardId, o.NoPacks as OutwardNoPacks FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId where o.PartyId='".$data["PartyId"]."' and o.ArticleId = '".$data["ArticleId"]."' and o.OutwardNumberId='".$data["OutwardNumberId"]."' group by OutwardNumber");
				$getalldata = DB::select("SELECT a.*,c.Colorflag, o.Id as OutwardId, o.NoPacks as OutwardNoPacks FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where o.PartyId='".$data["PartyId"]."' and o.ArticleId = '".$data["ArticleId"]."' and o.OutwardNumberId='".$data["OutwardNumberId"]."' group by OutwardNumber");
				
				if($getalldata[0]->ArticleOpenFlag==0 && $getalldata[0]->Colorflag==0){
					
					if($data['NoPacksNew']==""){
						return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
					}
					
					if($data['partyflag']==true){
						if($data['NoPacks_TotalOutlet'] < $data['NoPacksNew']){
							return response()->json(array("id"=>"", "OutletNoOfSetNotMatch"=>"true"), 200);
						}
					}
					
					//return $data['NoPacks_TotalOutlet']; exit;
					$as = DB::select("select sum(ddd.NoPacks) as TotalNoPacks from (select NoPacks FROM `salesreturnpacks` where OutwardId= '".$getalldata[0]->OutwardId."' group by NoPacks) as ddd");
					//return $as; exit;
					$spacks = "";
					if(isset($as[0]->TotalNoPacks)!=""){
						$npacks = $getalldata[0]->OutwardNoPacks;
						$spacks = $as[0]->TotalNoPacks;
						$newdata = $npacks - $spacks;
						$NoPacksNew = $data['NoPacksNew'];
						if($newdata < $NoPacksNew){
							return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
						}
					} else{
						$newdata = $getalldata[0]->OutwardNoPacks;
					}
					
				} else{
					$as = DB::select("select GROUP_CONCAT(CONCAT(ddd.NoPacks) SEPARATOR ',') as SalesReturnNoPacks from (SELECT ColorId, sum(NoPacks) as NoPacks FROM `salesreturnpacks` where OutwardId= '".$getalldata[0]->OutwardId."' group by ColorId) as ddd");
					$SalesReturnNoPacks = "";
					if($as[0]->SalesReturnNoPacks!=""){
						$SalesReturnNoPacks = $as[0]->SalesReturnNoPacks;
						$npacks = explode(",", $getalldata[0]->OutwardNoPacks);
						$spacks = explode(",", $as[0]->SalesReturnNoPacks);
						//print_r($npacks); exit;
						$newdata  = "";
						foreach($npacks as $key => $vl){
							$newdata .= ($vl - $spacks[$key]).',';
						}
						$newdata = rtrim($newdata, ',');
					}else{
						$newdata = $getalldata[0]->OutwardNoPacks;
					}
				}
				
				/* print_r($data);
				print_r($getdata);
				print_r($getalldata);
				print_r($as);
				exit; */
				$search = $getalldata[0]->OutwardNoPacks;
				$OutwardId = $getalldata[0]->OutwardId;
				
				$NoPacks = "";
				$SalesNoPacks = "";
				$UpdateInwardNoPacks = "";
				$searchString = ',';
				if( strpos($search, $searchString) !== false ) {
					//echo "fffff";
					$string = explode(',', $search);
					$InwardSalesNoPacks = explode(',', $InwardSalesNoPacks);
					$stringcomma = 1;
				} 
				else{
					//echo "333";
					$search;
					$InwardSalesNoPacks = $InwardSalesNoPacks;
					$stringcomma = 0;
				}
				//echo $InwardSalesNoPacks;
				//exit;
				if($data['ArticleColorFlag']=="Yes"){
					foreach($data['ArticleSelectedColor'] as $key => $vl){
						$numberofpacks = $vl["Id"];
						$InwardSalesNoPacks_VL = $InwardSalesNoPacks[$key];
						
						if($data["NoPacksNew_".$numberofpacks]!=""){
							if($stringcomma==1){
								if($data['partyflag']==true){
									if($data['NoPacks_TotalOutlet_'.$numberofpacks] < $data['NoPacksNew_'.$numberofpacks]){
										return response()->json(array("id"=>"", "OutletNoOfSetNotMatch"=>"true"), 200);
									}
								} else{
									if($data["NoPacks_".$numberofpacks]<$data["NoPacksNew_".$numberofpacks]){
										return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
									}
								}
								
								$SalesNoPacks .= ($data["NoPacks_".$numberofpacks] - $data["NoPacksNew_".$numberofpacks]).",";
								$UpdateInwardNoPacks .= ($InwardSalesNoPacks_VL + $data["NoPacksNew_".$numberofpacks]).",";
							}else{
								if($data['partyflag']==true){
									if($data['NoPacks_TotalOutlet_'.$numberofpacks] < $data['NoPacksNew_'.$numberofpacks]){
										return response()->json(array("id"=>"", "OutletNoOfSetNotMatch"=>"true"), 200);
									}
								} else{
									if($data["NoPacks_".$numberofpacks]<$data["NoPacksNew_".$numberofpacks]){
										return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
									}
								}
								
								$SalesNoPacks .= ($data["NoPacks_".$numberofpacks] - $data["NoPacksNew_".$numberofpacks]).",";
								$UpdateInwardNoPacks .= ($InwardSalesNoPacks + $data["NoPacksNew_".$numberofpacks]).",";
							}
							$NoPacks .= $data["NoPacksNew_".$numberofpacks].",";
						}
						else{
							$NoPacks .= "0,";
							$SalesNoPacks .= $data["NoPacks_".$numberofpacks].",";
							$UpdateInwardNoPacks .= ($InwardSalesNoPacks_VL + $data["NoPacksNew_".$numberofpacks]).",";
						}
					}
					$NoPacks = rtrim($NoPacks,',');
				}else{
					if(isset($data['NoPacksNew'])){
						$NoPacks = $data['NoPacksNew'];
						$SalesNoPacks .= ($data["NoPacks"] - $data['NoPacksNew']);
						$UpdateInwardNoPacks = ($InwardSalesNoPacks + $NoPacks).",";
						/* echo $InwardSalesNoPacks;
						echo "\n"; 
						echo $UpdateInwardNoPacks = ($InwardSalesNoPacks + $NoPacks);
						echo "\n";
						exit;*/
					} 
					else{
						return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
					}
				}
				
				
				$SalesNoPacks = rtrim($SalesNoPacks,',');
				$UpdateInwardNoPacks = rtrim($UpdateInwardNoPacks,',');
				/* return "SalesNoPacks - ".$SalesNoPacks."----"."UpdateInwardNoPacks - ".$UpdateInwardNoPacks;
				echo "UpdateInwardNoPacks - ".$UpdateInwardNoPacks;
				exit;  */
				
				
				$CheckSalesNoPacks = explode(',', $NoPacks);
				//return $CheckSalesNoPacks; exit;
				$tmp = array_filter($CheckSalesNoPacks);
				if (empty($tmp)) {
					//echo "All zeros!";
					return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
				}
				
				DB::beginTransaction();
				try {
					
					$salesreturnId = DB::table('salesreturn')->insertGetId(
						["SalesReturnNumber"=>$SRNumberId,'OutwardId'=>$OutwardId, 'ArticleId'=>$data['ArticleId'],'NoPacks'=>$NoPacks,'UserId'=>$data['UserId'],'OutwardRate'=>$data['OutwardRate'],'CreatedDate'=>date('Y-m-d H:i:s')]
					);
					
					DB::table('inward')
					->where('ArticleId', $data['ArticleId'])
					->update(['SalesNoPacks'=>$UpdateInwardNoPacks]);
					
					if($data['ArticleOpenFlag']==0){
						if( strpos($NoPacks, ',') !== false ) {
							$NoPacks = explode(',', $NoPacks);
							foreach($data['ArticleSelectedColor'] as $key => $vl){
								$numberofpacks = $vl["Id"];
								DB::table('salesreturnpacks')->insertGetId(
									['SalesReturnId'=>$salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId'=>$numberofpacks, 'OutwardId'=> $OutwardId,'NoPacks'=>$NoPacks[$key], 'PartyId' =>$data["PartyId"], 'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
								);
							}
						}else{
							foreach($data['ArticleSelectedColor'] as $key => $vl){
								$numberofpacks = $vl["Id"];
								DB::table('salesreturnpacks')->insertGetId(
									['SalesReturnId'=>$salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId'=>$numberofpacks, 'OutwardId'=> $OutwardId,'NoPacks'=>$NoPacks, 'PartyId' =>$data["PartyId"],'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
								);
							}
						}
					}else{
						DB::table('salesreturnpacks')->insertGetId(
							['SalesReturnId'=>$salesreturnId, 'ArticleId' =>  $data['ArticleId'], 'ColorId'=>0, 'OutwardId'=> $OutwardId,'NoPacks'=>$NoPacks, 'PartyId' =>$data["PartyId"],'CreatedDate'=>date('Y-m-d H:i:s'), 'UpdatedDate'=>date('Y-m-d H:i:s')]
						);
					}
						
					DB::commit();
					return response()->json(array("SRNumberId"=>$SRNumberId, "id"=>"SUCCESS"), 200);
				} catch (\Exception $e) {
					DB::rollback();
					return response()->json(array("id"=>""), 200);
				}
			}	
		}
	 }
	 
	 public function getsalesreturn($id){
		 return DB::select("SELECT concat(otn.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, slr.Id, p.Name, a.ArticleNumber, FirstCharacterConcat(u.Name), slr.NoPacks, slr.CreatedDate FROM `salesreturn` slr inner join party p on p.Id=slr.PartyId inner join article a on a.Id=slr.ArticleId inner join users u on u.Id=slr.UserId inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=otn.FinancialYearId");
		 //return DB::select("SELECT slr.Id, p.Name, a.ArticleNumber, FirstCharacterConcat(u.Name), slr.NoPacks, slr.CreatedDate FROM `salesreturn` slr inner join party p on p.Id=slr.PartyId inner join article a on a.Id=slr.ArticleId inner join users u on u.Id=slr.UserId");
	 }
	 
	public function PostSalesReturn(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
		//$vnddataTotal = DB::select("SELECT count(*) as Total FROM `salesreturn` slr inner join party p on p.Id=slr.PartyId inner join article a on a.Id=slr.ArticleId inner join users u on u.Id=slr.UserId inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=otn.FinancialYearId");
		$vnddataTotal = DB::select("select count(*) as Total from (SELECT slr.Id FROM `salesreturn` slr inner join salesreturnnumber  sln on sln.Id=slr.SalesReturnNumber inner join party p on p.Id=sln.PartyId inner join article a on a.Id=slr.ArticleId inner join users u on u.Id=slr.UserId inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=otn.FinancialYearId group by slr.SalesReturnNumber) as d");
		$vnTotal = $vnddataTotal[0]->Total;
		$length = $data["length"];
		
		if($search['value'] != null && strlen($search['value']) > 2){
			$searchstring = "WHERE d.SalesReturnNumber like '%".$search['value']."%' OR d.Name like '%".$search['value']."%' OR d.ArticleNumber like '%".$search['value']."%' OR cast(d.CreatedDate as char) like '%".$search['value']."%'";
			$vnddataTotalFilter = DB::select("select count(*) as Total from (select * from (SELECT GetTotalSrOrderPieces(son.Id) as TotalSRPieces, DATE_FORMAT(son.CreatedDate, '%d/%m/%Y') as CreatedDate, son.Id, p.Name, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, concat(son.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SalesReturnNumber FROM `salesreturn` s inner join article a on a.Id=s.ArticleId left join salesreturnnumber son on s.SalesReturnNumber=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=s.UserId inner join outward o on o.Id=s.OutwardId group by s.SalesReturnNumber) as ddd order by ddd.Id desc) as d ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
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
		if($data["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data["order"][0]["dir"];
		}
		
		$vnddata = DB::select("select d.* from (select * from (SELECT GetTotalSrOrderPieces(son.Id) as TotalSRPieces, DATE_FORMAT(son.CreatedDate, '%d/%m/%Y') as CreatedDate, son.Id, p.Name, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, concat(son.SalesReturnNumber, '/',fn.StartYear,'-',fn.EndYear) as SalesReturnNumber FROM `salesreturn` s inner join article a on a.Id=s.ArticleId left join salesreturnnumber son on s.SalesReturnNumber=son.Id inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=s.UserId inner join outward o on o.Id=s.OutwardId group by s.SalesReturnNumber) as ddd order by ddd.Id desc) as d ".$searchstring." ".$order." limit ".$data["start"].",".$length);
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
	 
	 
	 
	 public function PurchaseReturnArticle($id){
		 //echo "asdasdas"; exit;
		 //SELECT a.ArticleNumber, a.Id FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId where son.PartyId='".$id."' group by s.ArticleId
		 $data = DB::select("select ddd.* from (SELECT p.ArticleId, a.ArticleNumber,i.NoPacks, CommaZeroValue(i.SalesNoPacks) as CheckNoPacks, i.SalesNoPacks FROM `po` p inner join article a on a.Id=p.ArticleId inner join inward i on i.ArticleId=a.Id where p.VendorId = '".$id."' and a.ArticleOpenFlag = 0) as ddd where ddd.CheckNoPacks=0 UNION select Id, ArticleNumber, '','','' from article where ArticleOpenFlag = 1");
		 //echo "<pre>"; print_r($data); exit;
		return $data;
	 }
	 
	 public function PurchaseReturn_GetInwardNumber($VendorId, $ArticleId){
		//return DB::select("SELECT a.ArticleNumber, a.Id, own.Id as OutwardNumberId, own.OutwardNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber FROM `sonumber` son inner join so s on s.SoNumberId=son.Id inner join article a on a.Id=s.ArticleId inner join outward o on o.ArticleId=a.Id left join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId  where o.PartyId='".$PartyId."' and o.ArticleId = '".$ArticleId."' group by OutwardNumber");
		return DB::select("SELECT a.Id, a.ArticleNumber, inw.Id as InwardNumberId,inw.GRN, concat(inwn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRNNumber FROM inwardgrn inwn inner join inward inw on inw.GRN = inwn.Id inner join article a on a.Id=inw.ArticleId  inner join financialyear fn on fn.Id=inwn.FinancialYearId left join po p on p.ArticleId = a.Id where inw.ArticleId='".$ArticleId."' and p.VendorId='".$VendorId."' group by GRN");
	 }
	 
	 public function PurcahseReturn_InwardGetData($VendorId, $ArticleId, $InwardNumberId){
		$getdata = DB::select("SELECT a.*,c.Colorflag, inw.Id as InwardId,inw.NoPacks, inw.SalesNoPacks, inw.GRN, concat(inw.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRNNumber FROM inwardgrn inwn inner join inward inw on inw.GRN = inwn.Id inner join article a on a.Id=inw.ArticleId inner join financialyear fn on fn.Id=inwn.FinancialYearId left join po p on p.ArticleId = a.Id inner join category c on c.Id=a.CategoryId where inw.ArticleId='".$ArticleId."' and p.VendorId='".$VendorId."' and inw.Id = '".$InwardNumberId."' group by GRN");
		if($getdata[0]->ArticleOpenFlag==1){
			$as = DB::select("SELECT sum(ReturnNoPacks) as NoPacks FROM `purchasereturn` where InwardId='".$InwardNumberId."' and VendorId ='".$VendorId."' and ArticleId = '".$ArticleId."'");
			//print_r($getdata); print_r($as); exit;
//return $as; exit;			
			foreach($getdata as $key => $val){
				$object = (object)$val;
				if(isset($as[0]->NoPacks)!=""){
					$spacks = $as[0]->NoPacks;
					$newdata = $getdata[0]->NoPacks - $as[0]->NoPacks;
					$object->NoPacks = $newdata;
				} else{
					$newdata = $getdata[0]->NoPacks;
					$object->NoPacks = $newdata;
				}
				$object->NoPacks = $newdata;
	//			return $newdata; exit;
			}
		}
		//print_r($getdata); exit;
		return $getdata;
		
	 }
	 
	public function GeneratePRNumber($UserId)
    {
		$array = array();
		$fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
        $prnumberdata = DB::select('SELECT Id, FinancialYearId, PurchaseReturnNumber From purchasereturnnumber order by Id desc limit 0,1');
		
		if(count($prnumberdata)>0){
			if($fin_yr[0]->Id > $prnumberdata[0]->FinancialYearId){
				$array["PR_Number"] = 1;
				$array["PR_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["PR_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			} else{
				$array["PR_Number"] = ($prnumberdata[0]->PurchaseReturnNumber) + 1;
				$array["PR_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["PR_Number_Financial"] = ($prnumberdata[0]->PurchaseReturnNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			}
		}
        else{
			$array["PR_Number"] = 1;
			$array["PR_Number_Financial_Id"] = $fin_yr[0]->Id;
			$array["PR_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
			return $array;
        }
    }
	
	 public function AddPurchaseReturn(Request $request)
     {
		$data = $request->all();
		
		if($data['PRNumberId']=="Add"){
			$generate_PRNUMBER = $this->GeneratePRNumber($data['UserId']);
			$PR_Number = $generate_PRNUMBER['PR_Number'];
			$PR_Number_Financial_Id = $generate_PRNUMBER['PR_Number_Financial_Id'];
			$PRNumberId = DB::table('purchasereturnnumber')->insertGetId(
				['PurchaseReturnNumber' =>  $PR_Number,"FinancialYearId"=>$PR_Number_Financial_Id,'VendorId' => $data['VendorId'],'Remark'=>$data['Remark'],'CreatedDate' => date('Y-m-d H:i:s')]
			);
		}else{
			$checksonumber = DB::select("SELECT PurchaseReturnNumber FROM `purchasereturnnumber` where Id ='".$data['PRNumberId']."'");
			//echo "SELECT SoNumber FROM `sonumber` where Id ='".$data['SoNumberId']."'"; exit;
			
			if(!empty($checksonumber)){
				$PR_Number = $checksonumber[0]->PurchaseReturnNumber;
				$PRNumberId = $data['PRNumberId'];
				
				DB::table('purchasereturnnumber')
				->where('Id', $PRNumberId)
				->update(['VendorId' =>  $data['VendorId'], 'Remark'=>$data['Remark']]);
			}
		}
		
		
			
		//echo "<pre>"; print_r($data); exit;
		if($data["ArticleOpenFlag"]==1){
			
			//$mixnopacks = DB::select('SELECT * FROM `mixnopacks` where ArticleId="'.$data['ArticleId'].'"'); 
			
			$NoPacks = "";
			$SalesNoPacks = "";
			if(isset($data['NoPacksNew'])){
				$NoPacks .= $data['NoPacksNew'];
				if($data['NoPacks']<$data['NoPacksNew']){
					return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
				}
				$SalesNoPacks .= ($data['NoPacks'] - $data['NoPacksNew']);
			}else{
				return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
			}
			
			
			$articlerate = DB::select('SELECT ArticleRate FROM `articlerate` where ArticleId="'.$data['ArticleId'].'"'); 
			
			DB::table('mixnopacks')
				->where('ArticleId', $data['ArticleId'])
				->update(['NoPacks' => $SalesNoPacks]);
			
					
			DB::table('purchasereturn')->insertGetId(
				["PurchaseReturnNumber"=>$PRNumberId, 'VendorId' =>  $data['VendorId'], 'ArticleId' =>  $data['ArticleId'],'InwardId'=>$data['InwardNumberId'],'UserId'=>$data['UserId'], 'TotalNoPacks' => $data['NoPacks'],'RemainingNoPacks' =>$SalesNoPacks , 'ReturnNoPacks'=>$NoPacks,'ArticleRate'=>$articlerate[0]->ArticleRate,'CreatedDate' => date('Y-m-d H:i:s')]
			);
			
			return response()->json(array("PRNumberId"=>$PRNumberId, "id"=>"SUCCESS"), 200);
			
		}else{		
			//echo "<pre>"; print_r($data); exit;
			$soadd = array();
			
			/* $dataresult= DB::select('SELECT c.Colorflag FROM `po` p inner join category c on c.Id=p.CategoryId where p.ArticleId="'.$data['ArticleId'].'"'); 
			$Colorflag = $dataresult[0]->Colorflag;
			
			$datanopacks= DB::select('SELECT SalesNoPacks FROM `inward` where ArticleId="'.$data['ArticleId'].'"');
			$search = $datanopacks[0]->SalesNoPacks; */
			
			//$dataresult= DB::select('SELECT sum(inw.SalesNoPacks) as SalesNoPacks, c.Colorflag FROM `po` p inner join inward inw on inw.ArticleId=p.ArticleId inner join category c on c.Id=p.CategoryId where p.ArticleId="'.$data['ArticleId'].'"'); 
			$dataresult= DB::select('SELECT (inw.SalesNoPacks) as SalesNoPacks, c.Colorflag FROM `po` p inner join inward inw on inw.ArticleId=p.ArticleId inner join category c on c.Id=p.CategoryId where p.ArticleId="'.$data['ArticleId'].'"'); 
			$Colorflag = $dataresult[0]->Colorflag;
			$search = $dataresult[0]->SalesNoPacks;
			
			$searchString = ',';
			if( strpos($search, $searchString) !== false ) {
				$string = explode(',', $search);
				$stringcomma = 1;
			}else{
				$search;
				$stringcomma = 0;
			}
			
			//echo $string[1]; exit;
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
					$NoPacks .= $data['NoPacksNew'];
					if($search<$data['NoPacksNew']){
						return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
					}
					$SalesNoPacks .= ($search - $data['NoPacksNew']);
				}else{
					return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
				}
			}
			
			$NoPacks = rtrim($NoPacks,',');
			$SalesNoPacks = rtrim($SalesNoPacks,',');
			$CheckSalesNoPacks = explode(',', $NoPacks);
			//echo "<pre>"; print_r($CheckSalesNoPacks); exit;
			$tmp = array_filter($CheckSalesNoPacks);
			if (empty($tmp)) {
				//echo "All zeros!";
				return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
			}
			
		
			DB::table('purchasereturn')->insertGetId(
				["PurchaseReturnNumber"=>$PRNumberId, 'VendorId' =>  $data['VendorId'],'ArticleId' =>  $data['ArticleId'],'InwardId'=>$data['InwardNumberId'],'UserId'=>$data['UserId'], 'TotalNoPacks' => $search,'RemainingNoPacks' =>$SalesNoPacks , 'ReturnNoPacks'=>$NoPacks,'ArticleRate'=>$data['ArticleRate'],'CreatedDate' => date('Y-m-d H:i:s')]
			);
				
			DB::table('inward')
				->where('ArticleId', $data['ArticleId'])
				->update(['SalesNoPacks' => $SalesNoPacks]);
		   
		   return response()->json(array("PRNumberId"=>$PRNumberId, "id"=>"SUCCESS"), 200);
		  //return response()->json(array("Id"=>"Success"), 200);
		
			//$field = SO::create($request->all());
			//return response()->json($field, 201);
		}
     }
	 
	 public function DeletePurchaseReturn($id){
		
		$articledata = DB::select("SELECT pur.VendorId, pur.ArticleId, pur.InwardId, pur.ArticleId, pur.ReturnNoPacks, a.ArticleNumber, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, c.Colorflag FROM `purchasereturn` pur inner join article a on a.Id=pur.ArticleId inner join category c on c.Id=a.CategoryId where pur.Id ='".$id."'");
		//echo "SELECT pur.ArticleId, pur.ReturnNoPacks, a.ArticleNumber, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, c.Colorflag FROM `purchasereturn` pur inner join article a on a.Id=pur.ArticleId inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId where pur.Id ='".$id."'";
		//print_r($articledata); exit;
		$PurchaseReturnNoPacks = $articledata[0]->ReturnNoPacks;
		$ArticleColor = json_decode($articledata[0]->ArticleColor);
		
		//print_r($articledata); exit;
		if($articledata[0]->ArticleOpenFlag==1){
			$mixnopacks = DB::select('SELECT NoPacks FROM `mixnopacks` where ArticleId="'.$articledata[0]->ArticleId.'"'); 
			
			$InwardNoPacks = $mixnopacks[0]->NoPacks;
			
			//$NoPacks = $PurchaseReturnNoPacks;
			/* if($InwardNoPacks<$PurchaseReturnNoPacks){
				return response()->json(array("Alreadyexist"=>"true"), 200);
			}else{ */
				
				$PurchaseNoPacks = ($InwardNoPacks + $PurchaseReturnNoPacks);
				
				DB::beginTransaction();
				try {
					DB::table('mixnopacks')
					->where('ArticleId', $articledata[0]->ArticleId)
					->update(['NoPacks' => $PurchaseNoPacks]);
					
					DB::table('purchasereturn')
					->where('Id', '=', $id)
					->delete();
					
					DB::commit();
					return response()->json(array("id"=>"SUCCESS"), 200);
				} catch (\Exception $e) {
					DB::rollback();
					return response()->json(array("Id"=>""), 200);
				}
			//}
		}else{
			//return $articledata[0]->ArticleId; exit;
			$dataresult= DB::select('SELECT inw.SalesNoPacks, c.Colorflag FROM `po` p inner join inward inw on inw.ArticleId=p.ArticleId inner join category c on c.Id=p.CategoryId where p.ArticleId="'.$articledata[0]->ArticleId.'"'); 
			$Colorflag = $dataresult[0]->Colorflag;
			$search = $dataresult[0]->SalesNoPacks;
			
			if( strpos($search, ',') !== false ) {
				$string = explode(',', $search);
				$PurchaseReturnNoPacks = explode(',', $PurchaseReturnNoPacks);
				$stringcomma = 1;
			}else{
				$stringcomma = 0;
			}
			
			$PurchaseNoPacks = "";
			if($Colorflag==1){
			
				foreach($ArticleColor as $key => $vl){
					//$string = $string[$key];
					$PurchaseRNoPacks = $PurchaseReturnNoPacks[$key];
					//exit;
					/* if($string<$PurchaseRNoPacks){
						return response()->json(array("Alreadyexist"=>"true"), 200);
					} */
					
					if($stringcomma==1){
						$PurchaseNoPacks .= ($string[$key] + $PurchaseRNoPacks).",";
					}else{
						$PurchaseNoPacks .= ($search + $PurchaseReturnNoPacks).",";
					}
				}
			} else{
				$PurchaseNoPacks .= ($search + $PurchaseReturnNoPacks).",";
			}
			$PurchaseNoPacks = rtrim($PurchaseNoPacks,',');
			
			//return $PurchaseNoPacks; exit;
			/* echo $PurchaseNoPacks."\n";
			echo $articledata[0]->ArticleId."\n";
			echo $id."\n";
			exit; */
			
			DB::beginTransaction();
			try {
				DB::table('purchasereturn')
				->where('Id', '=', $id)
				->delete();
				
				DB::table('inward')
				->where('ArticleId', $articledata[0]->ArticleId)
				->update(['SalesNoPacks' => $PurchaseNoPacks]);
				
				DB::commit();
				return response()->json(array("id"=>"SUCCESS"), 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json(array("Id"=>""), 200);
			}
			
		}
	 }
	 
	 public function DeleteSalesReturn($id){
		
		$articledata = DB::select("SELECT slr.SalesReturnNumber, slr.ArticleId, slr.NoPacks, a.ArticleNumber, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, c.Colorflag FROM `salesreturn` slr inner join article a on a.Id=slr.ArticleId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where slr.Id ='".$id."'");
		$SalesReturnNoPacks = $articledata[0]->NoPacks;
		$SalesReturnNumber = $articledata[0]->SalesReturnNumber;
		$ArticleColor = json_decode($articledata[0]->ArticleColor);
		//echo $articledata[0]->Colorflag;
		//print_r($articledata); exit;
		if($articledata[0]->ArticleOpenFlag==1){
			$getdata = DB::select("SELECT NoPacks FROM `mixnopacks` where ArticleId='".$articledata[0]->ArticleId."'");
			
			$InwardNoPacks = $getdata[0]->NoPacks;
			$NoPacks = $SalesReturnNoPacks;
			
			if($InwardNoPacks<$NoPacks){
				return response()->json(array("Alreadyexist"=>"true"), 200);
			}else{
			
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
					->update(['NoPacks'=>$totalnopacks]);
					
					/* DB::table('salesreturnpacks')
					->where('SalesReturnId', '=', $id)
					->delete(); */
					
					DB::table('salesreturn')
					->where('Id', '=', $id)
					->delete();
					
					DB::table('salesreturnpacks')
					->where('SalesReturnId', '=', $id)
					->delete();
					
					DB::commit();
					return response()->json(array("id"=>"SUCCESS"), 200);
				} catch (\Exception $e) {
					DB::rollback();
					return response()->json(array("id"=>""), 200);
				}
			}
		}else{
			$getdata = DB::select("SELECT SalesNoPacks FROM `inward` where ArticleId='".$articledata[0]->ArticleId."'");
			$SalesNoPacks = $getdata[0]->SalesNoPacks;
			//echo $SalesReturnNoPacks;
			//print_r($getdata); exit;
			if( strpos($SalesNoPacks, ',') !== false ) {
				$SalesNoPacks = explode(',', $SalesNoPacks);
				$SalesReturnNoPacks = explode(',', $SalesReturnNoPacks);
				$stringcomma = 1;
			}else{
				$stringcomma = 0;
			}
			
			$NoPacks = "";
			$UpdateInwardNoPacks = "";
			if($articledata[0]->Colorflag=="1"){
				foreach($ArticleColor as $key => $vl){
					$numberofpacks = $vl->Id;
					$inwardsale = $SalesNoPacks[$key];
					
					$SalesReturn = $SalesReturnNoPacks[$key];
					//exit;
					if($inwardsale<$SalesReturn){
						return response()->json(array("Alreadyexist"=>"true"), 200);
					}
							
					if($stringcomma==1){
						$NoPacks .= $SalesReturn.",";
						$UpdateInwardNoPacks .= ($inwardsale - $SalesReturn).",";
						
					}else{
						$NoPacks = $SalesReturn.",";
						$UpdateInwardNoPacks = ($SalesNoPacks - $SalesReturn).",";
					}
				}
				$NoPacks = rtrim($NoPacks,',');
				$UpdateInwardNoPacks = rtrim($UpdateInwardNoPacks,',');
			} else{
				/* echo $SalesNoPacks;
				echo "\n";
				echo $SalesReturnNoPacks;
				echo "\n";
				echo $NoPacks; */
				
				if($SalesNoPacks<$SalesReturnNoPacks){
					return response()->json(array("Alreadyexist"=>"true"), 200);
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
				->update(['SalesNoPacks'=>$UpdateInwardNoPacks]);
				
				DB::table('salesreturnpacks')
				->where('SalesReturnId', '=', $id)
				->delete();
				
				DB::table('salesreturn')
					->where('Id', '=', $id)
					->delete();
				
				DB::commit();
				return response()->json(array("SRNumberId"=>$SalesReturnNumber,"id"=>"SUCCESS"), 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json(array("id"=>""), 200);
			}
		}	
		//return response()->json("SUCCESS", 200);
	 }
	 
	public function GetPurchaseReturn($id){
		return DB::select("SELECT pr.Id, pr.TotalNoPacks, pr.RemainingNoPacks, pr.ReturnNoPacks, a.ArticleNumber, FirstCharacterConcat(u.Name) as UserName, v.Name, pr.CreatedDate FROM `purchasereturn` pr inner join article a on a.Id=pr.ArticleId inner join users u on u.Id=pr.UserId left join vendor v on v.Id=pr.VendorId order by pr.Id ASC");
	}
	 
	public function PostPurchaseReturn(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
		$vnddataTotal = DB::select("select count(*) as Total from (SELECT pr.Id  FROM `purchasereturn` pr inner join article a on a.Id=pr.ArticleId inner join users u on u.Id=pr.UserId left join vendor v on v.Id=pr.VendorId group by pr.PurchaseReturnNumber) as d");
		$vnTotal= $vnddataTotal[0]->Total;
		$length = $data["length"];
		
		if($search['value'] != null && strlen($search['value']) > 2){
			$searchstring = "WHERE d.Pieces like '%".$search['value']."%' OR d.PurchaseReturnNumber like '%".$search['value']."%' OR d.Name like '%".$search['value']."%' OR d.ArticleNumber like '%".$search['value']."%' OR cast(d.cdate as char) like '%".$search['value']."%'";
			$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT pr.Id, concat(prn.PurchaseReturnNumber,'/', f.StartYear,'-',f.EndYear) as PurchaseReturnNumber, GetTotalPROrderPieces(pr.PurchaseReturnNumber) as Pieces, pr.TotalNoPacks, pr.RemainingNoPacks, pr.ReturnNoPacks, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNumber, FirstCharacterConcat(u.Name) as UserName, v.Name, DATE_FORMAT(pr.CreatedDate, '%d/%m/%Y') as cdate, pr.CreatedDate FROM `purchasereturn` pr inner join article a on a.Id=pr.ArticleId inner join users u on u.Id=pr.UserId left join vendor v on v.Id=pr.VendorId inner join purchasereturnnumber prn on prn.Id=pr.PurchaseReturnNumber inner join financialyear f on f.Id=prn.FinancialYearId group by pr.PurchaseReturnNumber) as d ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
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
		if($data["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data["order"][0]["dir"];
		}
		
		//$vnddata = DB::select("select d.* from (SELECT pr.Id, pr.TotalNoPacks, pr.RemainingNoPacks,  CountNoPacks(pr.ReturnNoPacks) as Pieces, a.ArticleNumber, FirstCharacterConcat(u.Name) as UserName, v.Name, DATE_FORMAT(pr.CreatedDate, \"%d/%m/%Y\") as cdate, pr.CreatedDate FROM `purchasereturn` pr inner join article a on a.Id=pr.ArticleId inner join users u on u.Id=pr.UserId inner join po p on p.ArticleId=a.Id left join vendor v on v.Id=pr.VendorId) as d ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		$vnddata = DB::select("select d.* from (SELECT prn.Id as PRNO, concat(prn.PurchaseReturnNumber,'/', f.StartYear,'-',f.EndYear) as PurchaseReturnNumber,  GetTotalPROrderPieces(pr.PurchaseReturnNumber) as Pieces, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY prn.Id SEPARATOR ',') as ArticleNumber, FirstCharacterConcat(u.Name) as UserName, v.Name, DATE_FORMAT(pr.CreatedDate, '%d/%m/%Y') as cdate, pr.CreatedDate FROM `purchasereturn` pr inner join article a on a.Id=pr.ArticleId inner join users u on u.Id=pr.UserId left join vendor v on v.Id=pr.VendorId left join purchasereturnnumber prn on pr.PurchaseReturnNumber=prn.Id inner join financialyear f on f.Id=prn.FinancialYearId group by pr.PurchaseReturnNumber) as d ".$searchstring." ".$order." limit ".$data["start"].",".$length);
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
	 
	 
	 public function GetSalesReturnChallan($Id){
		//$getdata = DB::select("select OutletPartyId from salesreturn where Id='".$Id."'");
		$getdata = DB::select("SELECT srn.OutletPartyId FROM `salesreturn` s inner join salesreturnnumber srn on srn.Id=s.SalesReturnNumber where s.SalesReturnNumber ='".$Id."'");
		//return $getdata; exit;
		if($getdata[0]->OutletPartyId==0){
			$sl = 0;
			//$getsrchallen = DB::select("SELECT concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, \"%Y-%m-%d\") as SRDate, slr.Remark, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate  FROM `salesreturn` slr inner join party p on p.Id = slr.PartyId inner join article a on a.Id=slr.ArticleId inner join po po on po.ArticleId=a.Id inner join category c on c.Id=po.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id where slr.Id='".$Id."'");
			//$getsrchallen = DB::select("SELECT concat(otn.OutwardNumber,'/' ,fn2.StartYear,'-',fn2.EndYear) as OutwardNumber, concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, '%Y-%m-%d') as SRDate, srn.Remarks, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate  FROM `salesreturn` slr inner join article a on a.Id=slr.ArticleId inner join category c on c.Id=a.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join party p on p.Id = srn.PartyId  inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn2 on fn2.Id=otn.FinancialYearId where slr.SalesReturnNumber='".$Id."'");
			$getsrchallen = DB::select("SELECT concat(otn.OutwardNumber,'/' ,fn2.StartYear,'-',fn2.EndYear) as OutwardNumber, concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, '%Y-%m-%d') as SRDate, srn.Remarks, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate11, slr.OutwardRate as SRArticleRate  FROM `salesreturn` slr inner join article a on a.Id=slr.ArticleId inner join category c on c.Id=a.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join party p on p.Id = srn.PartyId  inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn2 on fn2.Id=otn.FinancialYearId where slr.SalesReturnNumber='".$Id."'");
		}
		else{
			$sl = 1;
			//$getsrchallen = DB::select("SELECT concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, \"%Y-%m-%d\") as SRDate, slr.Remark, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, pp.Name as OutletPartyName, pp.Address as OutletPartyAddress, pp.GSTNumber as OutletPartyGSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate  FROM `salesreturn` slr inner join party p on p.Id = slr.PartyId inner join party pp on pp.Id=slr.PartyId inner join article a on a.Id=slr.ArticleId inner join po po on po.ArticleId=a.Id inner join category c on c.Id=po.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id where slr.Id='".$Id."'");
			//$getsrchallen = DB::select("SELECT concat(otn.OutwardNumber,'/' ,fn2.StartYear,'-',fn2.EndYear) as OutwardNumber,concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, '%Y-%m-%d') as SRDate, srn.Remarks, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, pp.Name as OutletPartyName, pp.Address as OutletPartyAddress, pp.GSTNumber as OutletPartyGSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate  FROM `salesreturn` slr inner join article a on a.Id=slr.ArticleId inner join category c on c.Id=a.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join party p on p.Id = srn.PartyId inner join party pp on pp.Id=srn.OutletPartyId inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn2 on fn2.Id=otn.FinancialYearId where slr.SalesReturnNumber='".$Id."'");
			$getsrchallen = DB::select("SELECT concat(otn.OutwardNumber,'/' ,fn2.StartYear,'-',fn2.EndYear) as OutwardNumber,concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, '%Y-%m-%d') as SRDate, srn.Remarks, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, pp.Name as OutletPartyName, pp.Address as OutletPartyAddress, pp.GSTNumber as OutletPartyGSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate11, slr.OutwardRate as SRArticleRate  FROM `salesreturn` slr inner join article a on a.Id=slr.ArticleId inner join category c on c.Id=a.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join party p on p.Id = srn.PartyId inner join party pp on pp.Id=srn.OutletPartyId inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn2 on fn2.Id=otn.FinancialYearId where slr.SalesReturnNumber='".$Id."'");
		}

/* $sl = 0;
			//$getsrchallen = DB::select("SELECT concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, \"%Y-%m-%d\") as SRDate, slr.Remark, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, pp.Name as OutletPartyName, pp.Address as OutletPartyAddress, pp.GSTNumber as OutletPartyGSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate  FROM `salesreturn` slr inner join party p on p.Id = slr.PartyId inner join party pp on pp.Id=slr.PartyId inner join article a on a.Id=slr.ArticleId inner join po po on po.ArticleId=a.Id inner join category c on c.Id=po.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id where slr.Id='".$Id."'");
			$getsrchallen = DB::select("SELECT concat(otn.OutwardNumber,'/' ,fn2.StartYear,'-',fn2.EndYear) as OutwardNumber, concat(srn.SalesReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as SalesReturnNumber,slr.Id, DATE_FORMAT(slr.CreatedDate, \"%Y-%m-%d\") as SRDate, slr.Remark, slr.NoPacks, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, p.Name, p.Address,  p.GSTNumber, u.Name as UserName, c.Title, art.ArticleRate as SRArticleRate  FROM `salesreturn` slr inner join party p on p.Id = slr.PartyId inner join article a on a.Id=slr.ArticleId inner join po po on po.ArticleId=a.Id inner join category c on c.Id=po.CategoryId inner join users u on u.Id=slr.UserId inner join salesreturnnumber srn on srn.Id=slr.SalesReturnNumber inner join financialyear fn on fn.Id=srn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join outward o on o.Id=slr.OutwardId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn2 on fn2.Id=otn.FinancialYearId where slr.SalesReturnNumber='".$Id."'");
 */ 			
		
		//print_r($getsrchallen); exit;
		$challendata = [];
		$TotalNoPacks = 0;
		$TotalAmount = 0;
		$TotalQuantityPic = 0;
		

		foreach($getsrchallen as $vl){
			//echo $vl->InwardDate;
			$Name = $vl->Name;
			$UserName = $vl->UserName;
			$Address = $vl->Address;
			$GSTNumber = $vl->GSTNumber;
			if($sl==1){
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
			
			if($ArticleOpenFlag==0){
				$ArticleRatio = $vl->ArticleRatio;
				
				if( strpos($NoPacks, ',') !== true ) {
					$countNoSet = array_sum(explode(",",$NoPacks));
					$TotalNoPacks+= array_sum(explode(",",$NoPacks));
					$TotalArticleRatio = array_sum(explode(",",$ArticleRatio));
				}else{
					$countNoSet = $NoPacks;
					$TotalNoPacks+= $NoPacks;
					$TotalArticleRatio = $ArticleRatio;
				}
				
				//$QuantityPic = $TotalArticleRatio * $countNoSet;
				//$TotalQuantityPic += $QuantityPic;
			
				$ArticleRate = $vl->SRArticleRate;
				$Amount = $countNoSet * $ArticleRate;
				$TotalAmount += $Amount;
				
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
			}else{
				$countNoSet = $NoPacks;
				//$QuantityPic = $countNoSet;
				//$TotalQuantityPic += $QuantityPic;
				$TotalNoPacks+= $NoPacks;
				
				$ArticleRatio = "";
				$ArticleRate = $vl->SRArticleRate;
				$Amount = $countNoSet * $ArticleRate;
				$TotalAmount += $Amount;
				$ArticleColor = "";
				$ArticleSize = "";
			}
			$challendata[] = json_decode(json_encode(array("OutwardNumber"=>$OutwardNumber,"SalesReturnNumber"=>$SalesReturnNumber,"UserName"=>$UserName, "SRDate"=>$SRDate,"Id"=>$Id,"Name"=>$Name,"Address"=>$Address,"Outletdata"=>$outletdata,"OutletPartyName"=>$OutletPartyName,"OutletPartyAddress"=>$OutletPartyAddress, "OutletPartyGSTNumber"=>$OutletPartyGSTNumber, "GSTNumber"=>$GSTNumber,"Remark"=>$Remark,"ArticleNumber"=>$ArticleNumber,"Title"=>$Title, "ArticleRatio"=>$ArticleRatio, "QuantityInSet"=>$NoPacks, "ArticleRate"=>number_format($ArticleRate, 2), "Amount"=>number_format($Amount, 2), "ArticleColor"=>$ArticleColor, "ArticleSize"=>$ArticleSize)), false);
		}
		
		//echo "<pre>"; print_r($challendata); exit;
		$as  = array($challendata, array("TotalNoPacks"=>$TotalNoPacks, "TotalAmount"=>number_format($TotalAmount, 2)));
		//echo "<pre>"; print_r($as); exit;
		return $as;
	 
	 }
	 
	 public function GetPurchaseReturnChallan($Id){
		$getsrchallen = DB::select("SELECT prn.Remark, (case pur.ArticleRate when 0 then a.ArticleRate else pur.ArticleRate END) as PRArticleRate, concat(ig.GRN, '/',f.StartYear,'-',f.EndYear) as GRN,concat(prn.PurchaseReturnNumber,'/' ,fn.StartYear,'-',fn.EndYear) as PurchaseReturnNumber, pur.Id, DATE_FORMAT(pur.CreatedDate, '%Y-%m-%d') as PRDate, c.Title, v.Name, v.Address, v.GSTNumber, pur.TotalNoPacks, pur.RemainingNoPacks, pur.ReturnNoPacks, u.Name as UserName, a.ArticleNumber, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag FROM `purchasereturn` pur inner join article a on a.Id=pur.ArticleId inner join users u on u.Id=pur.UserId inner join vendor v on v.Id=pur.VendorId inner join category c on c.Id=a.CategoryId inner join purchasereturnnumber prn on prn.Id=pur.PurchaseReturnNumber inner join financialyear fn on fn.Id=prn.FinancialYearId inner join articlerate art on art.ArticleId=a.Id inner join inward i on i.Id=pur.InwardId left join inwardgrn ig on ig.Id=i.GRN inner join financialyear f on f.Id=ig.FinancialYearId where prn.Id = '".$Id."'");
		//print_r($getsrchallen); exit;
		$challendata = [];
		$TotalNoPacks = 0;
		$TotalAmount = 0;
		$TotalQuantityPic = 0;
		

		foreach($getsrchallen as $vl){
			//echo $vl->InwardDate;
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
			
			if($ArticleOpenFlag==0){
				$ArticleRatio = $vl->ArticleRatio;
				
				if( strpos($NoPacks, ',') !== true ) {
					$countNoSet = array_sum(explode(",",$NoPacks));
					$TotalNoPacks+= array_sum(explode(",",$NoPacks));
					$TotalArticleRatio = array_sum(explode(",",$ArticleRatio));
				}else{
					$countNoSet = $NoPacks;
					$TotalNoPacks+= $NoPacks;
					$TotalArticleRatio = $ArticleRatio;
				}
				
				//$QuantityPic = $TotalArticleRatio * $countNoSet;
				//$TotalQuantityPic += $QuantityPic;
			
				$ArticleRate = $vl->PRArticleRate;
				$Amount = $countNoSet * $ArticleRate;
				$TotalAmount += $Amount;
				
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
			}else{
				$countNoSet = $NoPacks;
				//$QuantityPic = $countNoSet;
				//$TotalQuantityPic += $QuantityPic;
				$TotalNoPacks+= $NoPacks;
				
				$ArticleRatio = "";
				$ArticleRate = $vl->PRArticleRate;
				$Amount = $countNoSet * $ArticleRate;
				$TotalAmount += $Amount;
				$ArticleColor = "";
				$ArticleSize = "";
			}
			$challendata[] = json_decode(json_encode(array("Remark"=>$Remark, "GRN"=>$GRN, "PurchaseReturnNumber"=>$PurchaseReturnNumber, "UserName"=>$UserName, "PRDate"=>$PRDate,"Id"=>$Id,"Name"=>$Name,"Address"=>$Address,"GSTNumber"=>$GSTNumber,"ArticleNumber"=>$ArticleNumber,"Title"=>$Title, "ArticleRatio"=>$ArticleRatio, "QuantityInSet"=>$NoPacks, "ArticleRate"=>number_format($ArticleRate, 2), "Amount"=>number_format($Amount, 2), "ArticleColor"=>$ArticleColor, "ArticleSize"=>$ArticleSize)), false);
		}
		
		//echo "<pre>"; print_r($challendata); exit;
		$as  = array($challendata, array("TotalNoPacks"=>$TotalNoPacks, "TotalAmount"=>number_format($TotalAmount, 2)));
		//echo "<pre>"; print_r($as); exit;
		return $as;
	 
	 }
	 //SELECT pr.TotalNoPacks, pr.RemainingNoPacks, pr.ReturnNoPacks, a.ArticleNumber, FirstCharacterConcat(u.Name), pr.CreatedDate FROM `purchasereturn` pr inner join article a on a.Id=pr.ArticleId inner join users u on u.Id=pr.UserId

	//Front API
	public function CategoryArticleList($CategoryId){
		//return DB::select("SELECT a.Id, GROUP_CONCAT(DISTINCT CONCAT(ap.Name) ORDER BY ap.Id SEPARATOR ',') as Images, a.ArticleNumber, c.Title, ar.ArticleRate FROM `article` a inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId inner join articlerate ar on ar.ArticleId=a.Id left join articlephotos ap on ap.ArticlesId=a.Id where c.Id='".$CategoryId."' group by a.Id");
		//return DB::select("SELECT a.Id, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck, GROUP_CONCAT(DISTINCT CONCAT(ap.Name) ORDER BY ap.Id SEPARATOR ',') as Images, a.ArticleNumber, c.Title, ar.ArticleRate FROM `article` a inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId inner join articlerate ar on ar.ArticleId=a.Id left join articlephotos ap on ap.ArticlesId=a.Id inner join inward inw on inw.ArticleId=a.Id where c.Id='".$CategoryId."' group by a.Id HAVING SalesNoPacksCheck = 0");
		return DB::select("SELECT a.Id,  a.ArticleStatus, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck,substring_index(GROUP_CONCAT(DISTINCT CONCAT(ap.Name) ORDER BY ap.Id SEPARATOR ','), ',', 2) as Images, a.ArticleNumber, c.Title, ar.ArticleRate FROM `article` a left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId inner join articlerate ar on ar.ArticleId=a.Id left join articlephotos ap on ap.ArticlesId=a.Id inner join inward inw on inw.ArticleId=a.Id where c.Id='".$CategoryId."' and a.ArticleStatus=1 and ap.Name!='' group by a.Id HAVING SalesNoPacksCheck = 0");
		// SELECT a.Id,  a.ArticleStatus, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck,substring_index(GROUP_CONCAT(DISTINCT CONCAT(ap.Name) ORDER BY ap.Id SEPARATOR ','), ',', 2) as Images, a.ArticleNumber, c.Title, ar.ArticleRate FROM `article` a left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId inner join articlerate ar on ar.ArticleId=a.Id left join articlephotos ap on ap.ArticlesId=a.Id inner join inward inw on inw.ArticleId=a.Id where c.Id='18' and a.ArticleStatus=1 and ap.Name!='' group by a.Id HAVING SalesNoPacksCheck = 0

	}
	
	public function ArticleDetails($ArtId){
		$articleflagcheck = DB::select('SELECT ArticleOpenFlag FROM `article` where Id = "'.$ArtId.'"');
		//echo "<pre>"; print_r($articleflagcheck); exit;
		if($articleflagcheck[0]->ArticleOpenFlag==0){
			return DB::select("SELECT art.Id, (CASE ap.Name WHEN NULL then '0' ELSE GROUP_CONCAT(DISTINCT CONCAT(ap.Name) ORDER BY ap.Id SEPARATOR ',') END) as Images, art.ArticleNumber, c.Title, ar.ArticleRate,  art.ArticleColor,  art.ArticleSize, art.ArticleRatio, i.SalesNoPacks, c.Colorflag, art.ArticleOpenFlag FROM article art inner join articlerate ar on ar.ArticleId=art.Id inner join category c on c.Id=art.CategoryId inner join inward i on i.ArticleId=art.Id left join articlephotos ap on ap.ArticlesId=art.Id where art.Id ='".$ArtId."'");
		}else{
			return DB::select("SELECT mxp.ArticleId as Id, mxp.NoPacks, ar.ArticleRate,  a.ArticleNumber, a.ArticleOpenFlag FROM `mixnopacks` mxp inner join article a on a.Id=mxp.ArticleId inner join articlerate ar on ar.ArticleId=mxp.ArticleId where mxp.ArticleId ='".$ArtId."'");
		}
		
		//return DB::select("select a.Id, (CASE ap.Name WHEN NULL then '0' ELSE GROUP_CONCAT(DISTINCT CONCAT(ap.Name) ORDER BY ap.Id SEPARATOR ',') END) as Images, a.ArticleNumber, c.Title, c.Colorflag, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, ar.ArticleRate, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck from article a inner join articlerate ar on ar.ArticleId=a.Id inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId inner join inward inw on inw.ArticleId=a.Id left join articlephotos ap on ap.ArticlesId=a.Id where a.Id='".$ArtId."' having SalesNoPacksCheck = 0");
	}

}
