<?php
namespace App\Http\Controllers;

use Exception;
use App\Article;
use App\Imports\DatasImport;
use App\ImportTool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use File;
use App\Inward;
use App\Outletimport;


class ImportToolController extends Controller
{
    /**
     * List all the resources for Field
     *
     * @param null
     *
     * @return mix
     */
    public function index()
    {
        //  return UserRole::all();
    }
	
	public function importcsv(Request $request){
		//return "asdas"; exit;
		/* var_dump(request()->file('Import_CSV'));
		exit; */
		$data = $request->all();
		
		
		/* $file = $data['Import_CSV'];
		$path = $file->getRealPath();
		$getSize = $file->getSize();
        $file11 = file($path); */
		
        // $TotalRecord = count($file);
		$path = request()->file('Import_CSV')->getRealPath();
        $getSize = request()->file('Import_CSV')->getSize();
        $file = file($path);
        $data = array_slice($file, 1);
		//return "111"; exit;
		
	    //$path = $request->file('Import_CSV')->getRealPath();
		//$rows = Excel::import(new DatasImport, $path);
 
		
		try {
			$importData_arr = Excel::toCollection(new DatasImport, $request->file('Import_CSV'));
			//$importData_arr = Excel::toArray(new DatasImport, $request->file('Import_CSV'));
		} catch (NoTypeDetectedException $e) {
			return "Sorry you are using a wrong format to upload files.";
		}
		
		//return $importData_arr; exit;
		try {
			$countvl = 0;
            foreach ($importData_arr[0] as $row) {
				if($row->filter()->isNotEmpty()){
					$countvl++;
				}
			}
		} catch (Exception $e) {
            //dd($e->getMessage());
            return redirect()->back()->with(['err' => "22A file error has occurred while importing files."]);

        }
        $TotalRecord = $countvl;
		
		
		
		foreach ($importData_arr[0] as $row) {
				if($row->filter()->isNotEmpty()){
					//echo $row->articleid;
					//return $row;
					//{"articleid":1230,"category":1,"categoryid":1,"colorflag":0,"articleopenflag":0,"brand":1,"brandid":0,"styledescription":"asd","color":"1,1,2","nopacks":"10,20,20","noofqty":100,"rate":12,"sizeid":1,"ratioid":2,"weight":2,"":null}
					
					//$data = $request->all();
					$imp_articleid = $row["articleid"];
					$imp_category = $row["category"];
					$imp_categoryid = $row["categoryid"];
					$imp_colorflag = $row["colorflag"];
					$imp_articleopenflag = $row["articleopenflag"];
					$imp_brand = $row["brand"];
					$imp_brandid = $row["brandid"];
					$imp_styledescription = $row["styledescription"];
					$imp_color = $row["color"];
					$imp_nopacks = $row["nopacks"];
					// $imp_noofqty = $row["noofqty"];
					$imp_rate = $row["rate"];
					$imp_sizeid = $row["sizeid"];
					$imp_ratioid = $row["ratioid"];
					$imp_weight = $row["weight"];
					$ArticleStatus = 1;
					
					$ArticleColorData = array();
					if( strpos($imp_color, ',') !== false ) {
						$arr_color = explode(',', $imp_color);
						foreach($arr_color as $vl){
							$col_replace = trim(str_replace("Col_","",$vl));
							
							$color_array = DB::select("SELECT Id, Name FROM `color` where Name = '".$col_replace."'");
							array_push($ArticleColorData, array("Id"=>$color_array[0]->Id, "Name"=>$color_array[0]->Name));
						}
					}else{
					    $col_replace = trim(str_replace("Col_","",$imp_color));
						$color_array = DB::select("SELECT Id, Name FROM `color` where Name = '".$col_replace."'");
						array_push($ArticleColorData, array("Id"=>$color_array[0]->Id, "Name"=>$color_array[0]->Name));
					}
					
					
					$ArticleSizeData = array();
					if( strpos($imp_sizeid, ',') !== false ) {
						$imp_sizeid = explode(',', $imp_sizeid);
						foreach($imp_sizeid as $vl){
							$size_replace = trim(str_replace("Size_","",$vl));
							
							$size_array = DB::select("SELECT Id, Name FROM `size` where Name = '".$size_replace."'");
							array_push($ArticleSizeData, array("Id"=>$size_array[0]->Id, "Name"=>$size_array[0]->Name));
						}
					}else{
					    
					    $size_replace = trim(str_replace("Size_","",$imp_sizeid));
						$size_array = DB::select("SELECT Id, Name FROM `size` where Name = '".$size_replace."'");
							array_push($ArticleSizeData, array("Id"=>$size_array[0]->Id, "Name"=>$size_array[0]->Name));
					}
				
						
					$Colorflag = $imp_colorflag;
					$ArticleOpenFlag = $imp_articleopenflag;
					$ArticleRatio = $imp_ratioid;
					
					$ArticleColor = json_encode($ArticleColorData);
					$ArticleSize = json_encode($ArticleSizeData);
					
					$dataresult= DB::select('SELECT * FROM `article` WHERE `ArticleNumber` LIKE "'.$imp_articleid.'"');
//exit;					
					if($dataresult){
						return response()->json(array("Message"=>"Error", "Article"=>$imp_articleid), 200);
					}else{
						$article_array = array("ArticleNumber"=>$imp_articleid, "ArticleRate"=>$imp_rate, 'ArticleColor' => $ArticleColor, 'ArticleSize' => $ArticleSize, 'ArticleRatio' => $ArticleRatio, "CategoryId"=>$imp_categoryid, "ArticleOpenFlag"=> $ArticleOpenFlag, "StyleDescription"=>$imp_styledescription, 'ArticleStatus'=>$ArticleStatus, 'BrandId' =>$imp_brandid, "OpeningStock"=>1);
						$get_articleid = Article::create($article_array)->id;
						$generate_GRN = $this->GetGRNNumber();
					}
					
					$ArticleId = $get_articleid;
					$NoPacks = $imp_nopacks;
					$countcolor = count($ArticleColorData);
					$countration = array_sum(explode(",",$imp_ratioid));
					$countNoPacks = array_sum(explode(",",$NoPacks));
					
					if($imp_colorflag==1){
						$TotalSetQuantity = ($countNoPacks * $countration);
					}else{
						$TotalSetQuantity = ($countNoPacks * ($countration * $countcolor));
					}
					
					if($ArticleOpenFlag==0){
						$getratio = explode(",",$imp_ratioid);
						//return $ArticleColorData; exit;
						foreach($ArticleColorData as $vl){
							//return $vl;
							//return $vl['Id'];							exit;
							DB::table('articlecolor')->insertGetId(
								['ArticleId'=>$ArticleId, 'ArticleColorId' => $vl['Id'], 'ArticleColorName' => $vl['Name'],'CreatedDate' => date("Y-m-d H:i:s")]
							);
						}
						
						//return $ArticleSizeData; exit;
						foreach($ArticleSizeData as $key => $vl){
							$articlesize = DB::table('articlesize')->insertGetId(
								['ArticleId'=>$ArticleId, 'ArticleSize' => $vl['Id'],'ArticleSizeName' => $vl['Name'],'CreatedDate' => date("Y-m-d H:i:s")]
							);
							
							DB::table('articleratio')->insertGetId(
								['ArticleId'=>$ArticleId, 'ArticleSizeId' => $vl['Id'], 'ArticleRatio' => $getratio[$key],'CreatedDate' => date("Y-m-d H:i:s")]
							);
						}
						
						$articledata = DB::select("select count(*) as Total from articlerate where ArticleId = '".$ArticleId."'");
						if($articledata[0]->Total>0){
							DB::table('articlerate')
							->where('ArticleId', $ArticleId)
							->update(['ArticleRate' => $imp_rate, 'UpdatedDate'=>date("Y-m-d H:i:s")]);
						}else{
							DB::table('articlerate')->insertGetId(
								['ArticleId'=>$ArticleId, 'ArticleRate' => $imp_rate, 'CreatedDate' => date("Y-m-d H:i:s")]
							);
						}
					
					}else{
						$TotalSetQuantity = $countNoPacks;
					}
					
					$VendorId = 0;
					
					$GRN_Number = $generate_GRN['GRN_Number'];
					$GRN_Number_Financial_Id = $generate_GRN['GRN_Number_Financial_Id'];
					$inwardgrnid = DB::table('inwardgrn')->insertGetId(
					['GRN'=>$GRN_Number,"FinancialYearId"=>$GRN_Number_Financial_Id, 'InwardDate'=>date("Y-m-d"), 'Remark'=>'', 'VendorId'=>'', 'UserId'=>'','CreatedDate' => date('Y-m-d H:i:s')]);
				
					$inwardid = DB::table('inward')->insertGetId(
							['ArticleId'=>$ArticleId, 'NoPacks' => $NoPacks, 'SalesNoPacks' => $NoPacks, "InwardDate" => date("Y-m-d"),'GRN' => $inwardgrnid,"Weight" => $imp_weight,'TotalSetQuantity'=>$TotalSetQuantity, 'created_at' => date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')]
						);
					
					if($ArticleOpenFlag==1){
						$GetInwardArticleTotal = DB::select("SELECT count(*) as InwardArticleTotal, Id FROM `inwardarticle` where ArticleId ='".$ArticleId."' limit 0,1");
						DB::table('inwardarticle')->insertGetId(
								['ArticleId'=>$ArticleId, 'InwardId' => $inwardid, 'ArticleRate' => $imp_rate, 'ArticleColor' => $ArticleColor, 'ArticleSize' => $ArticleSize, 'ArticleRatio' => $imp_ratioid, 'CreatedDate' => date("Y-m-d H:i:s")]
							);
						
						$articledata = DB::select("select count(*) as Total from articlerate where ArticleId = '".$ArticleId."'");
						if($articledata[0]->Total>0){
							DB::table('articlerate')
							->where('ArticleId', $ArticleId)
							->update(['ArticleRate' => $imp_rate, 'UpdatedDate'=>date("Y-m-d H:i:s")]);
							
						}else{
							DB::table('articlerate')->insertGetId(
								['ArticleId'=>$ArticleId, 'ArticleRate' => $imp_rate, 'CreatedDate' => date("Y-m-d H:i:s")]
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
					
					//$import_data_array = array("ArticleId"=>$get_articleid, "ArticleNumber"=>$imp_articleid, "Category"=>$imp_category, "CategoryID"=>$imp_categoryid, "Colorflag"=>$imp_colorflag, "ArticleOpenFlag"=>$imp_articleopenflag, "Brand"=>$imp_brand, "BrandID"=>$imp_brandid, "StyleDescription"=>$imp_styledescription, "Color"=>$imp_color, "NoPacks"=>$NoPacks, "Rate"=>$imp_rate, "SizeId"=>$imp_sizeid, "RatioId"=>$imp_ratioid, "Weight"=>$imp_weight);
					//ImportTool::create($import_data_array);
				
				}
				
				
				
			}
		
		return response()->json(array("Message"=>"Success"), 200);
		
		//exit;
		/* $name=$file->getClientOriginalName();
		$randomstring = $this->generateRandomString();
		$name_extension = explode(".",$name);
		$Import_CSV = $randomstring.'.'.$name_extension[1];
		//echo "<pre>"; print_r($data); echo $name; exit;
		$file->move('uploads/importcsv',$Import_CSV);
		
		$file_import = public_path('uploads/importcsv'.$Import_CSV);
		
		Excel::load($file_import, function($reader) {
			// Getting all results
			$results = $reader->get();
			// ->all() is a wrapper for ->get() and will work the same
			$results = $reader->all();
			print_r($results);
			exit;
		}); */

		/* Excel::load(Input::file('file'), function ($reader) {

			foreach ($reader->toArray() as $row) {
				User::firstOrCreate($row);
			}
		}); */
		
		// return $Import_CSV;
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
	
	
	
	
	
	
	
	public function importoutletcsv(Request $request){
		$data = $request->all();
		
		$path = request()->file('Import_CSV')->getRealPath();
        $getSize = request()->file('Import_CSV')->getSize();
        $file = file($path);
        $data = array_slice($file, 1);
		
		try {
			$importData_arr = Excel::toCollection(new DatasImport, $request->file('Import_CSV'));
			//$importData_arr = Excel::toArray(new DatasImport, $request->file('Import_CSV'));
		} catch (NoTypeDetectedException $e) {
			return "Sorry you are using a wrong format to upload files.";
		}
		
		//return $importData_arr; exit;
		try {
			$countvl = 0;
            foreach ($importData_arr[0] as $row) {
				if($row->filter()->isNotEmpty()){
					$countvl++;
				}
			}
		} catch (Exception $e) {
            //dd($e->getMessage());
            return redirect()->back()->with(['err' => "22A file error has occurred while importing files."]);

        }
        $TotalRecord = $countvl;
		
		
		
		foreach ($importData_arr[0] as $row) {
				if($row->filter()->isNotEmpty()){
					//echo $row->articleid;
					//return $row;
					//{"articleid":1230,"category":1,"categoryid":1,"colorflag":0,"articleopenflag":0,"brand":1,"brandid":0,"styledescription":"asd","color":"1,1,2","nopacks":"10,20,20","noofqty":100,"rate":12,"sizeid":1,"ratioid":2,"weight":2,"":null}
					
					//$data = $request->all();
					$imp_articleid = $row["articleid"];
					$imp_category = $row["category"];
					$imp_categoryid = $row["categoryid"];
					$imp_colorflag = $row["colorflag"];
					$imp_articleopenflag = $row["articleopenflag"];
					$imp_brand = $row["brand"];
					$imp_brandid = $row["brandid"];
					$imp_styledescription = $row["styledescription"];
					$imp_color = $row["color"];
					$imp_nopacks = $row["nopacks"];
					// $imp_noofqty = $row["noofqty"];
					$imp_rate = $row["rate"];
					$imp_sizeid = $row["sizeid"];
					$imp_ratioid = $row["ratioid"];
					$imp_weight = $row["weight"];
					$imp_party = $row["party"];
					$ArticleStatus = 1;
						
					$Colorflag = $imp_colorflag;
					$ArticleOpenFlag = $imp_articleopenflag;
					$ArticleRatio = $imp_ratioid;
					$ArticleId = $imp_articleid;
					$NoPacks = $imp_nopacks;
					
					
					//XLS Color array
					$ArticleColorData = array();
					if( strpos($imp_color, ',') !== false ) {
						$arr_color = explode(',', $imp_color);
						foreach($arr_color as $vl){
							$col_replace = trim(str_replace("Col_","",$vl));
							//array_push($ArticleColorData, array($col_replace);
							$color_array = DB::select("SELECT Id, Name FROM `color` where Name = '".$col_replace."'");
							array_push($ArticleColorData, array("Id"=>$color_array[0]->Id, "Name"=>$color_array[0]->Name));
						}
					}else{
					    $col_replace = trim(str_replace("Col_","",$imp_color));
						$color_array = DB::select("SELECT Id, Name FROM `color` where Name = '".$col_replace."'");
						array_push($ArticleColorData, array("Id"=>$color_array[0]->Id, "Name"=>$color_array[0]->Name));
					}
					
					
					$ArticleSizeData = array();
					if( strpos($imp_sizeid, ',') !== false ) {
						$imp_sizeid = explode(',', $imp_sizeid);
						foreach($imp_sizeid as $vl){
							$size_replace = trim(str_replace("Size_","",$vl));
							
							$size_array = DB::select("SELECT Id, Name FROM `size` where Name = '".$size_replace."'");
							array_push($ArticleSizeData, array("Id"=>$size_array[0]->Id, "Name"=>$size_array[0]->Name));
						}
					}else{
					    
					    $size_replace = trim(str_replace("Size_","",$imp_sizeid));
						$size_array = DB::select("SELECT Id, Name FROM `size` where Name = '".$size_replace."'");
							array_push($ArticleSizeData, array("Id"=>$size_array[0]->Id, "Name"=>$size_array[0]->Name));
					}
					
					$ArticleColor = json_encode($ArticleColorData);
					$ArticleSize = json_encode($ArticleSizeData);
					
					
					$countcolor = count($ArticleColorData);
					$countration = array_sum(explode(",",$imp_ratioid));
					$countNoPacks = array_sum(explode(",",$NoPacks));
					
					$dataresult= DB::select('SELECT * FROM `article` WHERE `ArticleNumber` LIKE "'.$imp_articleid.'"');
//exit;					
					if($dataresult){
						if($ArticleOpenFlag==0){
							/* $inwarddata= DB::select('SELECT * FROM `inward` WHERE `ArticleId` = "'.$imp_articleid.'"');
							$nopck = $inwarddata[0]->NoPacks;
							$slnopck = $inwarddata[0]->SalesNoPacks;
							 */
							$getcolor = json_decode($dataresult[0]->ArticleColor);
							$getsize = json_decode($dataresult[0]->ArticleSize);
							//$ass = array();
							
							
							//XLS no packs
							$s_array_color = array();
							$getcolorv = array();
							foreach($getcolor as $v){
								array_push($s_array_color,0);
								array_push($getcolorv, $v->Id);
							}


							//$getcolor2 = json_encode(array(array("Id"=>17, "Name"=>"03"), array("Id"=>15, "Name"=>"01"), array("Id"=>18, "Name"=>"04")));
							$getcolor_2 = json_decode($ArticleColor);
							$getcolor_22 = array();
							foreach($getcolor_2 as $v){
								array_push($getcolor_22, $v->Id);
							}

							//$NoPacks =  "5";
							//$Colorflag = 1;
							//$NoPacks =  "5, 20, 30";

							if($Colorflag==1){
								if( strpos($NoPacks, ',') !== false ) {
									$stringnopacks = explode(',', $NoPacks);
								}else{
									$stringnopacks = array();
									foreach($getcolor_2 as $v){
										array_push($stringnopacks, $NoPacks);
									}
								}
							} else{
								$stringnopacks = array();
								foreach($getcolor_2 as $v){
									array_push($stringnopacks, $NoPacks);
								}
							}

							// print_r($stringnopacks);

							$a11 = array_combine($getcolorv,$s_array_color);
							$a22 = array_combine($getcolor_22,$stringnopacks);

							$sums1 = array();
							foreach (array_keys($a11 + $a22) as $key) {
								$sums1[$key] = @($a11[$key] + $a22[$key]);
							}

							//print_r($sums1);

							$color = array();
							foreach($sums1 as $k => $v){
								DB::table('transportoutwardpacks')->insertGetId(
										['ArticleId'=>$dataresult[0]->Id, 'ColorId' => $k, 'OutwardId' =>0, 'NoPacks'=>$v, 'PartyId'=>$imp_party,'CreatedDate' => date("Y-m-d H:i:s"),'UpdatedDate' => date("Y-m-d H:i:s")]
									);
									
								$color_array = DB::select("SELECT Id, Name FROM `color` where Id = '".$k."'");
								array_push($color, array("Id"=>$color_array[0]->Id, "Name"=>$color_array[0]->Name));
								//array_push($color, $k);
								/* if(!in_array($k, $getcolorv))
								{
									DB::table('transportoutwardpacks')->insertGetId(
										['ArticleId'=>$ArticleId, 'ColorId' => $k, 'OutwardId' =>0, 'NoPacks'=>$v, 'PartyId'=>$imp_party,'CreatedDate' => date("Y-m-d H:i:s"),'UpdatedDate' => date("Y-m-d H:i:s")]
									);
								
								}else{
									//echo "Found=>".$k;
								} */
							}
							
							
							$outletimport = DB::select("select count(*) as Total from outletimport where ArticleId = '".$dataresult[0]->Id."' and PartyId = '".$imp_party."'");
							if($outletimport[0]->Total>0){
								DB::table('outletimport')
								->where([['ArticleId', $dataresult[0]->Id],['PartyId', $imp_party]])
								->update(['ArticleColor' => json_encode($color), 'ArticleSize' =>json_encode($getsize),'ArticleRatio'=>$ArticleRatio,'PartyId'=>$imp_party]);
							} else{
								DB::table('outletimport')->insertGetId(
									['ArticleId'=>$dataresult[0]->Id, 'ArticleColor' => json_encode($color), 'ArticleSize' =>json_encode($getsize), 'ArticleRatio'=>$ArticleRatio, 'PartyId'=>$imp_party,'created_at' => date("Y-m-d H:i:s"),'updated_at' => date("Y-m-d H:i:s")]
								);
							}
							
							
							
						} else{
							
							foreach($ArticleColorData as $v){
								DB::table('transportoutwardpacks')->insertGetId(
									['ArticleId'=>$dataresult[0]->Id, 'ColorId' => $v['Id'], 'OutwardId' => 0, 'NoPacks'=>$NoPacks,'PartyId'=>$imp_party,'CreatedDate' => date("Y-m-d H:i:s")]
								);
							}
							
							
									
							/* DB::table('outletimport')->insertGetId(
								['ArticleId'=>$dataresult[0]->Id, 'ArticleColor' => json_encode($getcolor), 'ArticleSize' =>json_encode($getsize), 'ArticleRatio'=>$ArticleRatio,'PartyId' => 0, 'PartyId'=>$imp_party,'created_at' => date("Y-m-d H:i:s"),'updated_at' => date("Y-m-d H:i:s")]
							); */
							/* $mixno = DB::select('SELECT * FROM `mixnopacks` WHERE `ArticleId` = "'.$imp_articleid.'"');
							
							DB::table('mixnopacks')
								->where('ArticleId', $imp_articleid)
								->update(['NoPacks' => ($mixno[0]->NoPacks + $imp_nopacks)]); */
						}
						
						//return response()->json(array("Message"=>"Success"), 200);
						//return response()->json(array("Message"=>"Error", "Article"=>$imp_articleid), 200);
					}else{
						$article_array = array("ArticleNumber"=>$imp_articleid, "ArticleRate"=>$imp_rate, 'ArticleColor' => $ArticleColor, 'ArticleSize' => $ArticleSize, 'ArticleRatio' => $ArticleRatio, "CategoryId"=>$imp_categoryid, "ArticleOpenFlag"=> $ArticleOpenFlag, "StyleDescription"=>$imp_styledescription, 'ArticleStatus'=>$ArticleStatus, 'BrandId' =>$imp_brandid, "OpeningStock"=>1);
						$get_articleid = Article::create($article_array)->id;
						$generate_GRN = $this->GetGRNNumber();
					
						if($imp_colorflag==1){
							$TotalSetQuantity = ($countNoPacks * $countration);
						}else{
							$TotalSetQuantity = ($countNoPacks * ($countration * $countcolor));
						}
						
						if($ArticleOpenFlag==0){
							$getratio = explode(",",$imp_ratioid);
							//$NoPacks = 
							//return $ArticleColorData; exit;
							
							if($Colorflag==1){
								if( strpos($imp_nopacks, ',') !== false ) {
									$arrayNoPacks = explode(',', $imp_nopacks);
									$comma  = 1;
								}else{
									$arrayNoPacks = $imp_nopacks;
									$comma  = 0;
								}
							} else{
								$arrayNoPacks = $imp_nopacks;
								$comma  = 0;
							}
							
				
				
							foreach($ArticleColorData as $key => $vl){
								//return $vl;
								//return $vl['Id'];							exit;
								DB::table('articlecolor')->insertGetId(
									['ArticleId'=>$get_articleid, 'ArticleColorId' => $vl['Id'], 'ArticleColorName' => $vl['Name'],'CreatedDate' => date("Y-m-d H:i:s")]
								);
								
								if($Colorflag==1 && $comma==1){
									DB::table('transportoutwardpacks')->insertGetId(
										['ArticleId'=>$get_articleid, 'ColorId' => $vl['Id'], 'OutwardId' => 0, 'NoPacks'=>$arrayNoPacks[$key],'PartyId'=>$imp_party,'CreatedDate' => date("Y-m-d H:i:s")]
									);
								} else{
									DB::table('transportoutwardpacks')->insertGetId(
										['ArticleId'=>$get_articleid, 'ColorId' => $vl['Id'], 'OutwardId' => 0, 'NoPacks'=>$arrayNoPacks,'PartyId'=>$imp_party,'CreatedDate' => date("Y-m-d H:i:s")]
									);
								}
							}
							
							//return $ArticleSizeData; exit;
							foreach($ArticleSizeData as $key => $vl){
								$articlesize = DB::table('articlesize')->insertGetId(
									['ArticleId'=>$get_articleid, 'ArticleSize' => $vl['Id'],'ArticleSizeName' => $vl['Name'],'CreatedDate' => date("Y-m-d H:i:s")]
								);
								
								DB::table('articleratio')->insertGetId(
									['ArticleId'=>$get_articleid, 'ArticleSizeId' => $vl['Id'], 'ArticleRatio' => $getratio[$key],'CreatedDate' => date("Y-m-d H:i:s")]
								);
							}
							
							$articledata = DB::select("select count(*) as Total from articlerate where ArticleId = '".$get_articleid."'");
							if($articledata[0]->Total>0){
								DB::table('articlerate')
								->where('ArticleId', $get_articleid)
								->update(['ArticleRate' => $imp_rate, 'UpdatedDate'=>date("Y-m-d H:i:s")]);
							}else{
								DB::table('articlerate')->insertGetId(
									['ArticleId'=>$get_articleid, 'ArticleRate' => $imp_rate, 'CreatedDate' => date("Y-m-d H:i:s")]
								);
							}
						}else{
							$TotalSetQuantity = $countNoPacks;
						}
						
						$VendorId = 0;
						
						
						
						if($ArticleOpenFlag==1){
							$GRN_Number = $generate_GRN['GRN_Number'];
							$GRN_Number_Financial_Id = $generate_GRN['GRN_Number_Financial_Id'];
						
							$inwardgrnid = DB::table('inwardgrn')->insertGetId(
							['GRN'=>$GRN_Number,"FinancialYearId"=>$GRN_Number_Financial_Id, 'InwardDate'=>date("Y-m-d"), 'Remark'=>'', 'VendorId'=>'', 'UserId'=>'','CreatedDate' => date('Y-m-d H:i:s')]);
						
						
							$inwardid = DB::table('inward')->insertGetId(
								['ArticleId'=>$get_articleid, 'NoPacks' => $NoPacks, 'SalesNoPacks' => $NoPacks, "InwardDate" => date("Y-m-d"),'GRN' => $inwardgrnid,"Weight" => $imp_weight,'TotalSetQuantity'=>$TotalSetQuantity, 'created_at' => date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')]
							);
							
							DB::table('inwardarticle')->insertGetId(
								['ArticleId'=>$get_articleid, 'InwardId' => $inwardid, 'ArticleRate' => $imp_rate, 'ArticleColor' => $ArticleColor, 'ArticleSize' => $ArticleSize, 'ArticleRatio' => $imp_ratioid, 'CreatedDate' => date("Y-m-d H:i:s")]
							);
							
							DB::table('articlerate')->insertGetId(
								['ArticleId'=>$get_articleid, 'ArticleRate' => $imp_rate, 'CreatedDate' => date("Y-m-d H:i:s")]
							);
								
							DB::table('mixnopacks')->insertGetId(
								['ArticleId'=>$get_articleid, 'NoPacks' => $NoPacks, 'CreatedDate' => date("Y-m-d H:i:s"), 'UpdatedDate' => date("Y-m-d H:i:s")]
							);						
						}
					}
					
					//$import_data_array = array("ArticleId"=>$get_articleid, "ArticleNumber"=>$imp_articleid, "Category"=>$imp_category, "CategoryID"=>$imp_categoryid, "Colorflag"=>$imp_colorflag, "ArticleOpenFlag"=>$imp_articleopenflag, "Brand"=>$imp_brand, "BrandID"=>$imp_brandid, "StyleDescription"=>$imp_styledescription, "Color"=>$imp_color, "NoPacks"=>$NoPacks, "Rate"=>$imp_rate, "SizeId"=>$imp_sizeid, "RatioId"=>$imp_ratioid, "Weight"=>$imp_weight);
					//ImportTool::create($import_data_array);
				
				}
				
				
				
			}
		
		return response()->json(array("Message"=>"Success"), 200);
		
		//exit;
		/* $name=$file->getClientOriginalName();
		$randomstring = $this->generateRandomString();
		$name_extension = explode(".",$name);
		$Import_CSV = $randomstring.'.'.$name_extension[1];
		//echo "<pre>"; print_r($data); echo $name; exit;
		$file->move('uploads/importcsv',$Import_CSV);
		
		$file_import = public_path('uploads/importcsv'.$Import_CSV);
		
		Excel::load($file_import, function($reader) {
			// Getting all results
			$results = $reader->get();
			// ->all() is a wrapper for ->get() and will work the same
			$results = $reader->all();
			print_r($results);
			exit;
		}); */

		/* Excel::load(Input::file('file'), function ($reader) {

			foreach ($reader->toArray() as $row) {
				User::firstOrCreate($row);
			}
		}); */
		
		// return $Import_CSV;
	}
}

