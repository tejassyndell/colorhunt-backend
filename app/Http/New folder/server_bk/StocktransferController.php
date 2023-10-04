<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Stockshortage;
use App\Stocktransfer;

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
		
		if(count($stnumberdata)>0){
			if($fin_yr[0]->Id > $stnumberdata[0]->FinancialYearId){
				$array["ST_Number"] = 1;
				$array["ST_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["ST_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			} else{
				$array["ST_Number"] = ($stnumberdata[0]->StocktransferNumber) + 1;
				$array["ST_Number_Financial_Id"] = $fin_yr[0]->Id;
				$array["ST_Number_Financial"] = ($stnumberdata[0]->StocktransferNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
				return $array;
			}
		}
        else{
			$array["ST_Number"] = 1;
			$array["ST_Number_Financial_Id"] = $fin_yr[0]->Id;
			$array["ST_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
			return $array;
        }
    }
	
	public function AddStocktransfer(Request $request)
    {
		$data = $request->all();
		//return print_r($data); exit;
		//DB::beginTransaction(); 
		
		//try {
			if($data['StocktransferNumberId']=="Add"){
				$generate_STNumber = $this->GenerateSTNumber($data['UserId']);
				$ST_Number = $generate_STNumber['ST_Number'];
				$ST_Number_Financial_Id = $generate_STNumber['ST_Number_Financial_Id'];
				
				$stocktransfernumberId = DB::table('stocktransfernumber')->insertGetId(
					['StocktransferNumber' =>  $ST_Number, "FinancialYearId"=>$ST_Number_Financial_Id,'StocktransferDate' =>  $data['StocktransferDate'], 'UserId'=>$data['UserId'],'created_at'=>date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s')]
				);
			}else{
				$checksonumber = DB::select("SELECT StocktransferNumber FROM `stocktransfernumber` where Id ='".$data['StocktransferNumberId']."'");
				if(!empty($checksonumber)){
					$ST_Number = $checksonumber[0]->StocktransferNumber;
					$stocktransfernumberId = $data['StocktransferNumberId'];
					
					DB::table('stocktransfernumber')
					->where('Id', $stocktransfernumberId)
					->update(['StocktransferDate' =>  $data['StocktransferDate'], 'UserId'=>$data['UserId'], 'updated_at'=>date('Y-m-d H:i:s')]);
				}
			}
			
			if($data['TransferType']=="1"){
				
				$articleflag = DB::select("SELECT c.Colorflag, c.ArticleOpenFlag FROM `article` a inner join category c on c.Id=a.CategoryId where a.Id='".$data['ArticleId']."'");
				if($articleflag[0]->ArticleOpenFlag==1){
					$mixnopacks = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='".$data['ArticleId']."'");
					if($mixnopacks[0]->total>0){
						$NoPacks = $data['NoPacksNew'];
						$Consumption_Source = "";
						$totalnopacks = ($mixnopacks[0]->NoPacks - $NoPacks);
						DB::table('mixnopacks')
						->where('Id', $mixnopacks[0]->Id)
						->update(['NoPacks'=>$totalnopacks, 'UpdatedDate' => date("Y-m-d H:i:s")]);
					}
				}else{
					$dataresult= DB::select('SELECT c.Colorflag, i.SalesNoPacks FROM `article` a inner join inward i on i.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where a.Id="'.$data['ArticleId'].'"'); 
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
					
					
					//consumption
					$NoPacks = "";
					$Consumption_Source = "";
					if($Colorflag==1){
						foreach($data['ArticleSelectedColor'] as $key => $vl){
							$numberofpacks = $vl["Id"];
							if($data["NoPacksNew_".$numberofpacks]!=""){
								if($stringcomma==1){
									if($string[$key]<$data["NoPacksNew_".$numberofpacks]){
										return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
									}
									
									$Consumption_Source .= ($string[$key] - $data["NoPacksNew_".$numberofpacks]).",";
								}else{
									if($search<$data["NoPacksNew_".$numberofpacks]){
										return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
									}
									
									$Consumption_Source .= ($search - $data["NoPacksNew_".$numberofpacks]).",";
								}
								$NoPacks .= $data["NoPacksNew_".$numberofpacks].",";
							}
							else{
								$NoPacks .= "0,";
								$Consumption_Source .= $string[$key].",";
							}
						}
					} else{
						if(isset($data['NoPacksNew'])){
							$NoPacks = $data['NoPacksNew'];
							if($search<$data['NoPacksNew']){
								return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
							}
							$Consumption_Source = ($search - $data['NoPacksNew']);
						}else{
							return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
						}
					}
					
					$CheckSalesNoPacks = explode(',', $NoPacks);
					$Consumption_Source = rtrim($Consumption_Source,',');
					
					$tmp = array_filter($CheckSalesNoPacks);
					if(empty($tmp)){
						return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
					}
					
					DB::table('inward')
						->where('ArticleId', $data['ArticleId'])
						->update(['SalesNoPacks'=>$Consumption_Source,'updated_at'=>date('Y-m-d H:i:s')]);
				}
					
				$articleflagpro = DB::select("SELECT c.Colorflag, c.ArticleOpenFlag FROM `article` a inner join category c on c.Id=a.CategoryId where a.Id='".$data['ProductionArticleId']."'");
				if($articleflagpro[0]->ArticleOpenFlag==1){
					$mixnopacksprod = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='".$data['ProductionArticleId']."'");
					if($mixnopacksprod[0]->total>0){
						$ProductionNoPacks = $data['ProductionNoPacksNew'];
						$Production_Destination = "";
						$totalnopacksprod = ($mixnopacksprod[0]->NoPacks + $ProductionNoPacks);
						DB::table('mixnopacks')
						->where('Id', $mixnopacksprod[0]->Id)
						->update(['NoPacks'=>$totalnopacksprod, 'UpdatedDate' => date("Y-m-d H:i:s")]);
					}
				} else{				
					
					$productiondataresult= DB::select('SELECT c.Colorflag, i.SalesNoPacks FROM `article` a inner join inward i on i.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where a.Id="'.$data['ProductionArticleId'].'"'); 
					$productionColorflag = $productiondataresult[0]->Colorflag;
					$productionsearch = $productiondataresult[0]->SalesNoPacks;
					
					$searchStringpro = ',';
					if( strpos($productionsearch, $searchStringpro) !== false ) {
						$productionstring = explode(',', $productionsearch);
						$productionstringcomma = 1;
					}else{
						$productionsearch;
						$productionstringcomma = 0;
					}
					
					//Production
					$ProductionNoPacks = "";
					$Production_Destination = "";
					if($productionColorflag==1){
						foreach($data['ProductionArticleSelectedColor'] as $key => $vl){
							$production_numberofpacks = $vl["Id"];
							if($data["ProductionNoPacksNew_".$production_numberofpacks]!=""){
								if($productionstringcomma==1){
									/* if($productionstring[$key]<$data["ProductionNoPacksNew_".$production_numberofpacks]){
										return response()->json(array("id"=>"", "ProductionNoOfSetNotMatch"=>"true"), 200);
									} */
									
									$Production_Destination .= ($productionstring[$key] + $data["ProductionNoPacksNew_".$production_numberofpacks]).",";
								}else{
									/* if($productionsearch<$data["ProductionNoPacksNew_".$production_numberofpacks]){
										return response()->json(array("id"=>"", "ProductionNoOfSetNotMatch"=>"true"), 200);
									} */
									
									$Production_Destination .= ($productionsearch + $data["ProductionNoPacksNew_".$production_numberofpacks]).",";
								}
								$ProductionNoPacks .= $data["ProductionNoPacksNew_".$production_numberofpacks].",";
							}
							else{
								$ProductionNoPacks .= "0,";
								$Production_Destination .= $productionstring[$key].",";
							}
						}
					} else{
						if(isset($data['ProductionNoPacksNew'])){
							$ProductionNoPacks = $data['ProductionNoPacksNew'];
							/* if($productionsearch<$data['ProductionNoPacksNew']){
								return response()->json(array("id"=>"", "ProductionNoOfSetNotMatch"=>"true"), 200);
							} */
							$Production_Destination = ($productionsearch + $data['ProductionNoPacksNew']);
						}else{
							return response()->json(array("id"=>"", "as"=>"","ProductionZeroNotAllow"=>"true"), 200);
						}
					}
					
					$CheckProductionSalesNoPacks = explode(',', $ProductionNoPacks);
					$Production_Destination = rtrim($Production_Destination,',');
					
					$tmp = array_filter($CheckProductionSalesNoPacks);
					if(empty($tmp)){
						return response()->json(array("id"=>"","as111"=>"", "ProductionZeroNotAllow"=>"true"), 200);
					}
					
					DB::table('inward')
						->where('ArticleId', $data['ProductionArticleId'])
						->update(['SalesNoPacks'=>$Production_Destination,'updated_at'=>date('Y-m-d H:i:s')]);
				}
						
				//Stocktransfer
				$stocktransferadd = array();
				$stocktransferadd['StocktransferNumberId'] = $stocktransfernumberId;
				$stocktransferadd["ConsumedArticleId"] = $data['ArticleId'];
				$stocktransferadd["ConsumedNoPacks"] = rtrim($NoPacks,',');
				$stocktransferadd["TotalConsumedNoPacks"] = $Consumption_Source;
				$stocktransferadd["TransferArticleId"] = $data['ProductionArticleId'];
				$stocktransferadd['TransferNoPacks']= rtrim($ProductionNoPacks,',');
				$stocktransferadd['TotalTransferNoPacks']= $Production_Destination;
				Stocktransfer::create($stocktransferadd);
				//$field = Stocktransfer::create($stocktransferadd);
				//$stocktransfer_insertedid =$field->id;
				
				return response()->json(array("StocktransferNumberId"=>$stocktransfernumberId, "ST_Number"=>$ST_Number), 200);
				
			} else{
				$articleflag = DB::select("SELECT c.Colorflag, c.ArticleOpenFlag FROM `article` a inner join category c on c.Id=a.CategoryId where a.Id='".$data['ArticleId']."'");
				if($articleflag[0]->ArticleOpenFlag==1){
					$mixnopacks = DB::select("SELECT count(*) as total, Id, NoPacks FROM `mixnopacks` where ArticleId ='".$data['ArticleId']."'");
					if($mixnopacks[0]->total>0){
						$NoPacks = $data['NoPacksNew'];
						$Consumption_Source = "";
						$totalnopacks = ($mixnopacks[0]->NoPacks - $NoPacks);
						DB::table('mixnopacks')
						->where('Id', $mixnopacks[0]->Id)
						->update(['NoPacks'=>$totalnopacks, 'UpdatedDate' => date("Y-m-d H:i:s")]);
					}
				}else{
					$dataresult= DB::select('SELECT c.Colorflag, i.SalesNoPacks FROM `article` a inner join inward i on i.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where a.Id="'.$data['ArticleId'].'"'); 
					$Colorflag = $dataresult[0]->Colorflag;
					$search = $dataresult[0]->SalesNoPacks;
					
					$stocktransferadd = array();
					$searchString = ',';
					if( strpos($search, $searchString) !== false ) {
						$string = explode(',', $search);
						$stringcomma = 1;
					}else{
						$search;
						$stringcomma = 0;
					}
					
					//consumption
					$NoPacks = "";
					$Consumption_Source = "";
					if($Colorflag==1){
						foreach($data['ArticleSelectedColor'] as $key => $vl){
							$numberofpacks = $vl["Id"];
							if($data["NoPacksNew_".$numberofpacks]!=""){
								if($stringcomma==1){
									if($string[$key]<$data["NoPacksNew_".$numberofpacks]){
										return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
									}
									
									$Consumption_Source .= ($string[$key] - $data["NoPacksNew_".$numberofpacks]).",";
								}else{
									if($search<$data["NoPacksNew_".$numberofpacks]){
										return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
									}
									
									$Consumption_Source .= ($search - $data["NoPacksNew_".$numberofpacks]).",";
								}
								$NoPacks .= $data["NoPacksNew_".$numberofpacks].",";
							}
							else{
								$NoPacks .= "0,";
								$Consumption_Source .= $string[$key].",";
							}
						}
					} else{
						if(isset($data['NoPacksNew'])){
							$NoPacks = $data['NoPacksNew'];
							if($search<$data['NoPacksNew']){
								return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
							}
							$Consumption_Source = ($search - $data['NoPacksNew']);
						}else{
							return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
						}
					}
					
					$CheckSalesNoPacks = explode(',', $NoPacks);
					$Consumption_Source = rtrim($Consumption_Source,',');
					
					$tmp = array_filter($CheckSalesNoPacks);
					if(empty($tmp)){
						return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
					}
					
					DB::table('inward')
						->where('ArticleId', $data['ArticleId'])
						->update(['SalesNoPacks'=>$Consumption_Source,'updated_at'=>date('Y-m-d H:i:s')]);
				}
				
				
				$stocktransferadd['StocktransferNumberId'] = $stocktransfernumberId;
				$stocktransferadd["ArticleId"] = $data['ArticleId'];
				$stocktransferadd["NoPacks"] = rtrim($NoPacks,',');
				$stocktransferadd["TotalNoPacks"] = $Consumption_Source;
				
				Stockshortage::create($stocktransferadd);
				
				return response()->json(array("StocktransferNumberId"=>$stocktransfernumberId, "ST_Number"=>$ST_Number), 200);
			}
		//}
	}
	
	public function StockshortageListFromSTNO($STNO){
		return DB::select("SELECT s.Id , s.ArticleId, s.NoPacks, stn.StocktransferNumber , stn.StocktransferDate, a.ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear FROM `stockshortage` s inner join stocktransfernumber stn on stn.Id=s.StocktransferNumberId inner join article a on a.Id=s.ArticleId inner join financialyear fn on fn.Id=stn.FinancialYearId where s.StocktransferNumberId =  '".$STNO ."'");
	}
	
	public function StocktransferListFromSTNO($STNO){
		return DB::select("SELECT s.Id , s.ConsumedArticleId, s.ConsumedNoPacks, s.TransferArticleId , s.TransferNoPacks, stn.StocktransferNumber , stn.StocktransferDate, a.ArticleNumber as ConsumedArticle, aa.ArticleNumber as TransferArticle, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear FROM `stocktransfer` s inner join stocktransfernumber stn on stn.Id=s.StocktransferNumberId inner join article a on a.Id=s.ConsumedArticleId inner join article aa on aa.Id=s.TransferArticleId inner join financialyear fn on fn.Id=stn.FinancialYearId where s.StocktransferNumberId = '".$STNO ."'");	
	}
	
	public function StocktransferDateFromSTNO($id)
	{
		return DB::select("SELECT stn.StocktransferNumber, stn.StocktransferDate, stn.Remarks, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as ST_Number_FinancialYear from stocktransfernumber stn inner join financialyear fn on fn.Id=stn.FinancialYearId  where stn.Id = '".$id."'");
	}

	public function Deletestocktransfer($id, $type){
		if($type==2){
			$dataresult= DB::select("SELECT c.Colorflag, c.ArticleOpenFlag, a.ArticleColor, i.SalesNoPacks, ss.Id, ss.ArticleId, ss.StocktransferNumberId, ss.NoPacks FROM `stockshortage` ss inner join article a on a.Id=ss.ArticleId inner join inward i on i.ArticleId=a.Id left join category c on c.Id=a.CategoryId where ss.Id='".$id."'"); 
			//return print_r($dataresult); exit;
			$Colorflag = $dataresult[0]->Colorflag;
			$ArticleColor = json_decode($dataresult[0]->ArticleColor);
			$string = $dataresult[0]->SalesNoPacks;
			$ShortageDataNoPacks = $dataresult[0]->NoPacks;
			$ArticleId = $dataresult[0]->ArticleId;
			
			if( strpos($string, ',') !== false ) {
				$ShortageDataNoPacks = explode(',', $ShortageDataNoPacks);
				$string = explode(',', $string);
				$stringcomma = 1;
			}else{
				$stringcomma = 0;
			}
			
			$ShortageNoPacks = "";
			if($Colorflag==1){
				foreach($ArticleColor as $key => $vl){
					if($stringcomma==1){
						$ShortageNoPacks .= ($string[$key] + $ShortageDataNoPacks[$key]).",";
					}else{
						$ShortageNoPacks .= ($string + $ShortageDataNoPacks).",";
					}
				}
			} else{
				$ShortageNoPacks .= ($string + $ShortageDataNoPacks).",";
			}
			$ShortageNoPacks = rtrim($ShortageNoPacks,',');
			
			DB::beginTransaction();
			try {
				DB::table('stockshortage')
				->where('Id', '=', $id)
				->delete();
				
				DB::table('inward')
				->where('ArticleId', $ArticleId)
				->update(['SalesNoPacks' => $ShortageNoPacks]);
				
				DB::commit();
				return response()->json(array("id"=>"SUCCESS"), 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json(array("Id"=>""), 200);
			}
		} else{
			$dataresult= DB::select("SELECT c.Colorflag as ConsumedColorflag, cc.Colorflag as TransferColorflag, c.ArticleOpenFlag as ConsumedArticleOpenFlag, cc.ArticleOpenFlag as TransferArticleOpenFlag, a.ArticleColor as ConsumedArticleColor, aa.ArticleColor as TransferArticleColor, ss.ConsumedArticleId, ss.ConsumedNoPacks, i.SalesNoPacks as ConsumedSalesNoPacks, ss.TransferArticleId, ss.TransferNoPacks, ii.SalesNoPacks as TransferSalesNoPacks, ss.Id, ss.StocktransferNumberId FROM `stocktransfer` ss inner join article a on a.Id=ss.ConsumedArticleId inner join article aa on aa.Id=ss.TransferArticleId inner join inward i on i.ArticleId=a.Id inner join inward ii on ii.ArticleId=aa.Id left join category c on c.Id=a.CategoryId left join category cc on cc.Id=aa.CategoryId where ss.Id='".$id."'");
		
		
			$Colorflag = $dataresult[0]->ConsumedColorflag;
			$ArticleColor = json_decode($dataresult[0]->ConsumedArticleColor);
			$string = $dataresult[0]->ConsumedSalesNoPacks;
			$ConsumedDataNoPacks = $dataresult[0]->ConsumedNoPacks;
			$ConsumedArticleId = $dataresult[0]->ConsumedArticleId;
			
			if( strpos($string, ',') !== false ) {
				$ConsumedDataNoPacks = explode(',', $ConsumedDataNoPacks);
				$string = explode(',', $string);
				$stringcomma = 1;
			}else{
				$stringcomma = 0;
			}
			
			$ConsumedNoPacks = "";
			if($Colorflag==1){
				foreach($ArticleColor as $key => $vl){
					if($stringcomma==1){
						$ConsumedNoPacks .= ($string[$key] + $ConsumedDataNoPacks[$key]).",";
					}else{
						$ConsumedNoPacks .= ($string + $ConsumedDataNoPacks).",";
					}
				}
			} else{
				$ConsumedNoPacks .= ($string + $ConsumedDataNoPacks).",";
			}
			$ConsumedNoPacks = rtrim($ConsumedNoPacks,',');
			
			
			
			
			$TransferColorflag = $dataresult[0]->TransferColorflag;
			$TransferArticleColor = json_decode($dataresult[0]->TransferArticleColor);
			$Transferstring = $dataresult[0]->TransferSalesNoPacks;
			$TransferDataNoPacks = $dataresult[0]->TransferNoPacks;
			$TransferArticleId = $dataresult[0]->TransferArticleId;
			
			if( strpos($Transferstring, ',') !== false ) {
				$TransferDataNoPacks = explode(',', $TransferDataNoPacks);
				$Transferstring = explode(',', $Transferstring);
				$Transferstringcomma = 1;
			}else{
				$Transferstringcomma = 0;
			}
			
			$TransferNoPacks = "";
			if($TransferColorflag==1){
				foreach($TransferArticleColor as $key => $vl){
					if($Transferstringcomma==1){
						$TransferNoPacks .= ($Transferstring[$key] + $TransferDataNoPacks[$key]).",";
					}else{
						$TransferNoPacks .= ($Transferstring + $TransferDataNoPacks).",";
					}
				}
			} else{
				$TransferNoPacks .= ($Transferstring + $TransferDataNoPacks).",";
			}
			$TransferNoPacks = rtrim($TransferNoPacks,',');
			
			DB::beginTransaction();
			try {
				DB::table('stocktransfer')
				->where('Id', '=', $id)
				->delete();
				
				DB::table('inward')
				->where('ArticleId', $ConsumedArticleId)
				->update(['SalesNoPacks' => $ConsumedNoPacks]);
				
				DB::table('inward')
				->where('ArticleId', $TransferArticleId)
				->update(['SalesNoPacks' => $TransferNoPacks]);
				
				DB::commit();
				return response()->json(array("id"=>"SUCCESS"), 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json(array("Id"=>""), 200);
			}
			
			
		}
		//return $id;
		//exit;
	}

	public function PostStocktransfer(Request $request){
		$data = $request->all();	
		$search = $data['dataTablesParameters']["search"];
		$UserId = $data['UserID'];
		$startnumber = $data['dataTablesParameters']["start"];
		
		
		$vnddataTotal = DB::select("select count(*) as Total from (SELECT stn.UserId, stn.Id, st.ConsumedArticleId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY stn.Id SEPARATOR ',') as ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as StocktransferNumber,  stn.StocktransferDate FROM `stocktransfer` st inner join article a on a.Id=st.ConsumedArticleId left join stocktransfernumber stn on st.StocktransferNumberId=stn.Id inner join users u on u.Id=stn.UserId inner join financialyear fn on fn.Id=stn.FinancialYearId group by st.StocktransferNumberId  order by stn.Id desc) as d");
		$vntotal=$vnddataTotal[0]->Total;		
		$length = $data['dataTablesParameters']["length"];
		
		
		$userrole = DB::select("SELECT Role FROM `users`where Id='".$UserId."'");
		
		$wherecustom ="";
		if($search['value'] != null && strlen($search['value']) > 2){
			//$searchstring = "and oln.OutletNumber like '%".$search['value']."%'";
			$searchstring = "where d.StocktransferNumber like '%".$search['value']."%' OR cast(d.StocktransferDate as char) like '%".$search['value']."%' OR d.ArticleNumber like '%".$search['value']."%'";
			$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT stn.UserId, stn.Id, st.ConsumedArticleId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY stn.Id SEPARATOR ',') as ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as StocktransferNumber,  stn.StocktransferDate FROM `stocktransfer` st inner join article a on a.Id=st.ConsumedArticleId left join stocktransfernumber stn on st.StocktransferNumberId=stn.Id inner join users u on u.Id=stn.UserId inner join financialyear fn on fn.Id=stn.FinancialYearId group by st.StocktransferNumberId  order by stn.Id desc) as d ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "";
			$vnddataTotalFilterValue = $vntotal;
		}
		//end
		$column = $data['dataTablesParameters']["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "d.StocktransferNumber";
				break;
			case 2:
				$ordercolumn = "date(d.StocktransferDate)";
				break;
			default:
				$ordercolumn = "d.StocktransferNumber";
				break;
		}
		
		$order = "";
		if($data['dataTablesParameters']["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data['dataTablesParameters']["order"][0]["dir"];
		}
		
		//$vnddata = DB::select("select d.* from (SELECT stn.UserId, stn.Id, st.ConsumedArticleId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY stn.Id SEPARATOR ',') as ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as StocktransferNumber, stn.StocktransferDate FROM `stocktransfer` st inner join article a on a.Id=st.ConsumedArticleId left join stocktransfernumber stn on st.StocktransferNumberId=stn.Id inner join users u on u.Id=stn.UserId inner join financialyear fn on fn.Id=stn.FinancialYearId group by st.StocktransferNumberId) as d ".$wherecustom." ".$searchstring." ".$order." limit ".$data['dataTablesParameters']["start"].",".$length);
		//$vnddata = DB::select("select d.* from (SELECT stn.UserId, stn.Id, st.ConsumedArticleId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY stn.Id SEPARATOR ',') as ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as StocktransferNumber, stn.StocktransferDate FROM `stocktransfer` st inner join article a on a.Id=st.ConsumedArticleId left join stocktransfernumber stn on st.StocktransferNumberId=stn.Id inner join users u on u.Id=stn.UserId inner join financialyear fn on fn.Id=stn.FinancialYearId group by st.StocktransferNumberId UNION All SELECT stn.UserId, stn.Id, st.ArticleId as ConsumedArticleId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY stn.Id SEPARATOR ',') as ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as StocktransferNumber, stn.StocktransferDate FROM `stockshortage` st inner join article a on a.Id=st.ArticleId left join stocktransfernumber stn on st.StocktransferNumberId=stn.Id inner join users u on u.Id=stn.UserId inner join financialyear fn on fn.Id=stn.FinancialYearId group by st.StocktransferNumberId) as d ".$wherecustom." ".$searchstring." ".$order." limit ".$data['dataTablesParameters']["start"].",".$length);
		$vnddata = DB::select("select d.* from (select dd.* from (SELECT stn.UserId, stn.Id, st.ConsumedArticleId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY stn.Id SEPARATOR ',') as ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as StocktransferNumber, stn.StocktransferDate FROM `stocktransfer` st inner join article a on a.Id=st.ConsumedArticleId left join stocktransfernumber stn on st.StocktransferNumberId=stn.Id inner join users u on u.Id=stn.UserId inner join financialyear fn on fn.Id=stn.FinancialYearId group by st.StocktransferNumberId UNION All SELECT stn.UserId, stn.Id, st.ArticleId as ConsumedArticleId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY stn.Id SEPARATOR ',') as ArticleNumber, concat(stn.StocktransferNumber, '/',fn.StartYear,'-',fn.EndYear) as StocktransferNumber, stn.StocktransferDate FROM `stockshortage` st inner join article a on a.Id=st.ArticleId left join stocktransfernumber stn on st.StocktransferNumberId=stn.Id inner join users u on u.Id=stn.UserId inner join financialyear fn on fn.Id=stn.FinancialYearId group by st.StocktransferNumberId) as dd  group by dd.StocktransferNumber) as d ".$wherecustom." ".$searchstring." ".$order." limit ".$data['dataTablesParameters']["start"].",".$length);
		
	
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

}
