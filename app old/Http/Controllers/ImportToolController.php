<?php

namespace App\Http\Controllers;

use Exception;
use App\Article;
use App\Imports\DatasImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;
use App\Outletimport;
use App\TransportOutwardpacks;

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

	public function importcsv(Request $request)
	{
		try {
			$importData_arr = Excel::toCollection(new DatasImport, $request->file('Import_CSV'));
		} catch (NoTypeDetectedException $e) {
			return "Sorry you are using a wrong format to upload files.";
		}
		try {
			$countvl = 0;
			foreach ($importData_arr[0] as $row) {
				if ($row->filter()->isNotEmpty()) {
					$countvl++;
				}
			}
		} catch (Exception $e) {
			return redirect()->back()->with(['err' => "22A file error has occurred while importing files."]);
		}
		foreach ($importData_arr[0] as $row) {
			if ($row->filter()->isNotEmpty()) {
				$imp_articleid = $row["articleid"];
				$imp_categoryid = $row["categoryid"];
				$imp_colorflag = $row["colorflag"];
				$imp_articleopenflag = $row["articleopenflag"];
				$imp_brand = $row["brand"];
				$imp_brandid = $row["brandid"];
				$imp_styledescription = $row["styledescription"];
				$imp_color = $row["color"];
				$imp_nopacks = $row["nopacks"];
				$imp_rate = $row["rate"];
				$imp_sizeid = $row["sizeid"];
				$imp_ratioid = $row["ratioid"];
				$imp_weight = $row["weight"];
				$ArticleStatus = 1;
				$ArticleColorData = array();
				if (strpos($imp_color, ',') !== false) {
					$arr_color = explode(',', $imp_color);
					foreach ($arr_color as $vl) {
						$col_replace = trim(str_replace("Col_", "", $vl));
						$color_array = DB::select("SELECT Id, Name FROM `color` where Name = '" . $col_replace . "'");
						array_push($ArticleColorData, array("Id" => $color_array[0]->Id, "Name" => $color_array[0]->Name));
					}
				} else {
					$col_replace = trim(str_replace("Col_", "", $imp_color));
					$color_array = DB::select("SELECT Id, Name FROM `color` where Name = '" . $col_replace . "'");
					array_push($ArticleColorData, array("Id" => $color_array[0]->Id, "Name" => $color_array[0]->Name));
				}
				$ArticleSizeData = array();
				if (strpos($imp_sizeid, ',') !== false) {
					$imp_sizeid = explode(',', $imp_sizeid);
					foreach ($imp_sizeid as $vl) {
						$size_replace = trim(str_replace("Size_", "", $vl));
						$size_array = DB::select("SELECT Id, Name FROM `size` where Name = '" . $size_replace . "'");
						array_push($ArticleSizeData, array("Id" => $size_array[0]->Id, "Name" => $size_array[0]->Name));
					}
				} else {
					$size_replace = trim(str_replace("Size_", "", $imp_sizeid));
					$size_array = DB::select("SELECT Id, Name FROM `size` where Name = '" . $size_replace . "'");
					array_push($ArticleSizeData, array("Id" => $size_array[0]->Id, "Name" => $size_array[0]->Name));
				}
				$ArticleOpenFlag = $imp_articleopenflag;
				$ArticleRatio = $imp_ratioid;
				$ArticleColor = json_encode($ArticleColorData);
				$ArticleSize = json_encode($ArticleSizeData);
				$dataresult = DB::select('SELECT * FROM `article` WHERE `ArticleNumber` LIKE "' . $imp_articleid . '"');			
				if ($dataresult) {
					return response()->json(array("Message" => "Error", "Article" => $imp_articleid), 200);
				} else {
					$article_array = array("ArticleNumber" => $imp_articleid, "ArticleRate" => $imp_rate, 'ArticleColor' => $ArticleColor, 'ArticleSize' => $ArticleSize, 'ArticleRatio' => $ArticleRatio, "CategoryId" => $imp_categoryid, "ArticleOpenFlag" => $ArticleOpenFlag, "StyleDescription" => $imp_styledescription, 'ArticleStatus' => $ArticleStatus, 'BrandId' => $imp_brandid, "OpeningStock" => 1);
					$get_articleid = Article::create($article_array)->id;
					$generate_GRN = $this->GetGRNNumber();
				}
				$ArticleId = $get_articleid;
				$NoPacks = $imp_nopacks;
				$countcolor = count($ArticleColorData);
				$countration = array_sum(explode(",", $imp_ratioid));
				$countNoPacks = array_sum(explode(",", $NoPacks));

				if ($imp_colorflag == 1) {
					$TotalSetQuantity = ($countNoPacks * $countration);
				} else {
					$TotalSetQuantity = ($countNoPacks * ($countration * $countcolor));
				}
				if ($ArticleOpenFlag == 0) {
					$getratio = explode(",", $imp_ratioid);
					foreach ($ArticleColorData as $vl) {
						DB::table('articlecolor')->insertGetId(
							['ArticleId' => $ArticleId, 'ArticleColorId' => $vl['Id'], 'ArticleColorName' => $vl['Name'], 'CreatedDate' => date("Y-m-d H:i:s")]
						);
					}
					foreach ($ArticleSizeData as $key => $vl) {
						DB::table('articlesize')->insertGetId(
							['ArticleId' => $ArticleId, 'ArticleSize' => $vl['Id'], 'ArticleSizeName' => $vl['Name'], 'CreatedDate' => date("Y-m-d H:i:s")]
						);
						DB::table('articleratio')->insertGetId(
							['ArticleId' => $ArticleId, 'ArticleSizeId' => $vl['Id'], 'ArticleRatio' => $getratio[$key], 'CreatedDate' => date("Y-m-d H:i:s")]
						);
					}
					$articledata = DB::select("select count(*) as Total from articlerate where ArticleId = '" . $ArticleId . "'");
					if ($articledata[0]->Total > 0) {
						DB::table('articlerate')
							->where('ArticleId', $ArticleId)
							->update(['ArticleRate' => $imp_rate, 'UpdatedDate' => date("Y-m-d H:i:s")]);
					} else {
						DB::table('articlerate')->insertGetId(
							['ArticleId' => $ArticleId, 'ArticleRate' => $imp_rate, 'CreatedDate' => date("Y-m-d H:i:s")]
						);
					}
				} else {
					$TotalSetQuantity = $countNoPacks;
				}
				$GRN_Number = $generate_GRN['GRN_Number'];
				$GRN_Number_Financial_Id = $generate_GRN['GRN_Number_Financial_Id'];
				$inwardgrnid = DB::table('inwardgrn')->insertGetId(
					['GRN' => $GRN_Number, "FinancialYearId" => $GRN_Number_Financial_Id, 'InwardDate' => date("Y-m-d"), 'Remark' => '', 'VendorId' => '', 'UserId' => '', 'CreatedDate' => date('Y-m-d H:i:s')]
				);
				$inwardid = DB::table('inward')->insertGetId(
					['ArticleId' => $ArticleId, 'NoPacks' => $NoPacks, 'SalesNoPacks' => $NoPacks, "InwardDate" => date("Y-m-d"), 'GRN' => $inwardgrnid, "Weight" => $imp_weight, 'TotalSetQuantity' => $TotalSetQuantity, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
				);
				if ($ArticleOpenFlag == 1) {
					DB::table('inwardarticle')->insertGetId(
						['ArticleId' => $ArticleId, 'InwardId' => $inwardid, 'ArticleRate' => $imp_rate, 'ArticleColor' => $ArticleColor, 'ArticleSize' => $ArticleSize, 'ArticleRatio' => $imp_ratioid, 'CreatedDate' => date("Y-m-d H:i:s")]
					);
					$articledata = DB::select("select count(*) as Total from articlerate where ArticleId = '" . $ArticleId . "'");
					if ($articledata[0]->Total > 0) {
						DB::table('articlerate')
							->where('ArticleId', $ArticleId)
							->update(['ArticleRate' => $imp_rate, 'UpdatedDate' => date("Y-m-d H:i:s")]);
					} else {
						DB::table('articlerate')->insertGetId(
							['ArticleId' => $ArticleId, 'ArticleRate' => $imp_rate, 'CreatedDate' => date("Y-m-d H:i:s")]
						);
					}
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
			}
		}
		return response()->json(array("Message" => "Success"), 200);
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

	public function importoutletcsv(Request $request)
	{
		try {
			$importData_arr = Excel::toCollection(new DatasImport, $request->file('Import_CSV'));
		} catch (NoTypeDetectedException $e) {
			return "Sorry you are using a wrong format to upload files.";
		}
		try {
			$countvl = 0;
			foreach ($importData_arr[0] as $row) {
				if ($row->filter()->isNotEmpty()) {
					$countvl++;
				}
			}
		} catch (Exception $e) {
			return redirect()->back()->with(['err' => "22A file error has occurred while importing files."]);
		}
		foreach ($importData_arr[0] as $row) {
			if ($row->filter()->isNotEmpty()) {
				$imp_articleid = $row["articleid"];
				$imp_categoryid = $row["categoryid"];
				$imp_colorflag = $row["colorflag"];
				$imp_articleopenflag = $row["articleopenflag"];
				$imp_brand = $row["brand"];
				$imp_brandid = $row["brandid"];
				$imp_styledescription = $row["styledescription"];
				$imp_color = $row["color"];
				$imp_nopacks = $row["nopacks"];
				$imp_rate = $row["rate"];
				$imp_sizeid = $row["sizeid"];
				$imp_ratioid = $row["ratioid"];
				$imp_weight = $row["weight"];
				$imp_party = $row["party"];
				$ArticleStatus = 1;
				$Colorflag = $imp_colorflag;
				$ArticleOpenFlag = $imp_articleopenflag;
				$ArticleRatio = $imp_ratioid;
				$NoPacks = $imp_nopacks;
				if ($imp_color) {
					$ArticleColorData = array();
					if (strpos($imp_color, ',') !== false) {
						$arr_color = explode(',', $imp_color);
						foreach ($arr_color as $vl) {
							$col_replace = trim(str_replace("Col_", "", $vl));
							$color_array = DB::select("SELECT Id, Name FROM `color` where Name = '" . $col_replace . "'");
							array_push($ArticleColorData, array("Id" => $color_array[0]->Id, "Name" => $color_array[0]->Name));
						}
					} else {
						$col_replace = trim(str_replace("Col_", "", $imp_color));
						$color_array = DB::select("SELECT Id, Name FROM `color` where Name = '" . $col_replace . "'");
						array_push($ArticleColorData, array("Id" => $color_array[0]->Id, "Name" => $color_array[0]->Name));
					}
				}
				if ($imp_sizeid) {
					$ArticleSizeData = array();
					if (strpos($imp_sizeid, ',') !== false) {
						$imp_sizeid = explode(',', $imp_sizeid);
						foreach ($imp_sizeid as $vl) {
							$size_replace = trim(str_replace("Size_", "", $vl));
							$size_array = DB::select("SELECT Id, Name FROM `size` where Name = '" . $size_replace . "'");
							array_push($ArticleSizeData, array("Id" => $size_array[0]->Id, "Name" => $size_array[0]->Name));
						}
					} else {
						$size_replace = trim(str_replace("Size_", "", $imp_sizeid));
						$size_array = DB::select("SELECT Id, Name FROM `size` where Name = '" . $size_replace . "'");
						array_push($ArticleSizeData, array("Id" => $size_array[0]->Id, "Name" => $size_array[0]->Name));
					}
				}
                if ($imp_color) {
                    $ArticleColor = json_encode($ArticleColorData);
					$countcolor = count($ArticleColorData);
                }
                if ($imp_sizeid) {
                    $ArticleSize = json_encode($ArticleSizeData);
                }
				if($imp_ratioid){
					$countration = array_sum(explode(",", $imp_ratioid));
				}
					
				$countNoPacks = array_sum(explode(",", $NoPacks));
				$dataresult = DB::select('SELECT * FROM `article` WHERE `ArticleNumber` LIKE "' . $imp_articleid . '"');			
				if ($dataresult) {
					if ($ArticleOpenFlag == 0) {
						if ($imp_color) {
							$getcolor = json_decode($dataresult[0]->ArticleColor);
							$s_array_color = array();
							$getcolorv = array();
							foreach ($getcolor as $v) {
								array_push($s_array_color, 0);
								array_push($getcolorv, $v->Id);
							}
							$getcolor_2 = json_decode($ArticleColor);
							$getcolor_22 = array();
							foreach ($getcolor_2 as $v) {
								array_push($getcolor_22, $v->Id);
							}
							if ($Colorflag == 1) {
								if (strpos($NoPacks, ',') !== false) {
									$stringnopacks = explode(',', $NoPacks);
								} else {
									$stringnopacks = array();
									foreach ($getcolor_2 as $v) {
										array_push($stringnopacks, $NoPacks);
									}
								}
							} else {
								$stringnopacks = array();
								foreach ($getcolor_2 as $v) {
									array_push($stringnopacks, $NoPacks);
								}
							}
							$a11 = array_combine($getcolorv, $s_array_color);
							$a22 = array_combine($getcolor_22, $stringnopacks);
							$sums1 = array();
							foreach (array_keys($a11 + $a22) as $key) {
								$sums1[$key] = @($a11[$key] + $a22[$key]);
							}
						}
						if ($imp_sizeid) {
							$getsize = json_decode($dataresult[0]->ArticleSize);
						}
						if ($imp_color) {
							$color = array();
							foreach ($sums1 as $k => $v) {

								DB::table('transportoutwardpacks')->insertGetId(
									['ArticleId' => $dataresult[0]->Id, 'ColorId' => $k, 'OutwardId' => 0, 'NoPacks' => $v, 'PartyId' => $imp_party, 'CreatedDate' => date("Y-m-d H:i:s"), 'UpdatedDate' => date("Y-m-d H:i:s")]
								);
								$color_array = DB::select("SELECT Id, Name FROM `color` where Id = '" . $k . "'");
								array_push($color, array("Id" => $color_array[0]->Id, "Name" => $color_array[0]->Name));
							}
							$outletimport = DB::select("select count(*) as Total from outletimport where ArticleId = '" . $dataresult[0]->Id . "' and PartyId = '" . $imp_party . "'");
							if (!isset($ArticleSize)) {
								$ArticleSize = "";
							}
							if (!isset($ArticleRatio)) {
								$ArticleRatio = "";
							}
							if ($outletimport[0]->Total > 0) {
								DB::table('outletimport')
									->where([['ArticleId', $dataresult[0]->Id], ['PartyId', $imp_party]])
									->update(['ArticleColor' => json_encode($color), 'ArticleSize' => json_encode($getsize), 'ArticleRatio' => $ArticleRatio, 'PartyId' => $imp_party]);
							} else {
								DB::table('outletimport')->insertGetId(
									['ArticleId' => $dataresult[0]->Id, 'ArticleColor' => json_encode($color), 'ArticleSize' => json_encode($getsize), 'ArticleRatio' => $ArticleRatio, 'PartyId' => $imp_party, 'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")]
								);
							}
						} else {
							DB::table('transportoutwardpacks')->insertGetId(
								['ArticleId' => $dataresult[0]->Id, 'ColorId' => "", 'OutwardId' => 0, 'NoPacks' => $NoPacks, 'PartyId' => $imp_party, 'CreatedDate' => date("Y-m-d H:i:s"), 'UpdatedDate' => date("Y-m-d H:i:s")]
							);
							$outletimport = DB::select("select count(*) as Total from outletimport where ArticleId = '" . $dataresult[0]->Id . "' and PartyId = '" . $imp_party . "'");
							if (!isset($ArticleSize)) {
								$ArticleSize = "";
							}
							if (!isset($ArticleRatio)) {
								$ArticleRatio = "";
							}
							if ($outletimport[0]->Total > 0) {
								DB::table('outletimport')
									->where([['ArticleId', $dataresult[0]->Id], ['PartyId', $imp_party]])
									->update(['ArticleColor' => "", 'ArticleSize' => $ArticleSize, 'ArticleRatio' => $ArticleRatio, 'PartyId' => $imp_party]);
							} else {
								DB::table('outletimport')->insertGetId(
									['ArticleId' => $dataresult[0]->Id, 'ArticleColor' => "", 'ArticleSize' => $ArticleSize, 'ArticleRatio' => $ArticleRatio, 'PartyId' => $imp_party, 'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")]
								);
							}
						}
					} else {
						if ($imp_color) {
							foreach ($ArticleColorData as $v) {
								DB::table('transportoutwardpacks')->insertGetId(
									['ArticleId' => $dataresult[0]->Id, 'ColorId' => $v['Id'], 'OutwardId' => 0, 'NoPacks' => $NoPacks, 'PartyId' => $imp_party, 'CreatedDate' => date("Y-m-d H:i:s")]
								);
							}
						} else {
							DB::table('transportoutwardpacks')->insertGetId(
								['ArticleId' => $dataresult[0]->Id, 'ColorId' => "", 'OutwardId' => 0, 'NoPacks' => $NoPacks, 'PartyId' => $imp_party, 'CreatedDate' => date("Y-m-d H:i:s")]
							);
						}
					}
				} else {
					if (!isset($ArticleColor)) {
						$ArticleColor = "";
					}
					if (!isset($ArticleSize)) {
						$ArticleSize = "";
					}
					if (!isset($ArticleRatio)) {
						$ArticleRatio = "";
					}
					$article_array = array("ArticleNumber" => $imp_articleid, "ArticleRate" => $imp_rate, 'ArticleColor' => $ArticleColor, 'ArticleSize' => $ArticleSize, 'ArticleRatio' => $ArticleRatio, "CategoryId" => $imp_categoryid, "ArticleOpenFlag" => $ArticleOpenFlag, "StyleDescription" => $imp_styledescription, 'ArticleStatus' => $ArticleStatus, 'BrandId' => $imp_brandid, "OpeningStock" => 1);
					$get_articleid = Article::create($article_array)->id;
					Outletimport::create([
						"ArticleId" => $get_articleid,
						"ArticleColor" => $ArticleColor,
						"ArticleSize" => $ArticleSize,
						"ArticleRatio" => $ArticleRatio,
						"PartyId" => $imp_party
					]);
					if ($Colorflag == 1) {
						if (strpos($imp_nopacks, ',') !== false) {
							$arrayNoPacks = explode(',', $imp_nopacks);
							$articlesColorArray = json_decode($ArticleColor);
							for($i=0 ; $i < count($arrayNoPacks) ; $i++ ){
								TransportOutwardpacks::create([
									'ArticleId'=>$get_articleid,
									'ColorId'=>$articlesColorArray[$i]->Id,
									'OutwardId'=>0,
									'NoPacks'=>$imp_nopacks,
									'PartyId'=>$imp_party,
									'CreatedDate'=>date("Y-m-d H:i:s"),
									'UpdatedDate'=>date("Y-m-d H:i:s") 
								]);
							}
						} else {
							TransportOutwardpacks::create([
								'ArticleId'=>$get_articleid,
								'ColorId'=>'',
								'OutwardId'=>0,
								'NoPacks'=>$imp_nopacks,
								'PartyId'=>$imp_party,
								'CreatedDate'=>date("Y-m-d H:i:s"),
								'UpdatedDate'=>date("Y-m-d H:i:s") 
							]);
						}
					} else {
						TransportOutwardpacks::create([
							'ArticleId'=>$get_articleid,
							'ColorId'=>'',
							'OutwardId'=>0,
							'NoPacks'=>$imp_nopacks,
							'PartyId'=>$imp_party,
							'CreatedDate'=>date("Y-m-d H:i:s"),
							'UpdatedDate'=>date("Y-m-d H:i:s") 
						]);
					}
					$generate_GRN = $this->GetGRNNumber();
					if ($imp_colorflag == 1) {
						if ($imp_ratioid) {
							$TotalSetQuantity = ($countNoPacks * ($countration * $countcolor));
						} else {
							$TotalSetQuantity = $countNoPacks;
						}
					} else {
						if ($imp_ratioid) {
							$TotalSetQuantity = ($countNoPacks * $countration);
						} else {
							$TotalSetQuantity = $countNoPacks;
						}
					}

					if ($ArticleOpenFlag == 0) {
						$getratio = explode(",", $imp_ratioid);
						if ($Colorflag == 1) {
							if (strpos($imp_nopacks, ',') !== false) {
								$arrayNoPacks = explode(',', $imp_nopacks);
								$comma  = 1;
							} else {
								$arrayNoPacks = $imp_nopacks;
								$comma  = 0;
							}
						} else {
							$arrayNoPacks = $imp_nopacks;
							$comma  = 0;
						}
						foreach ($ArticleColorData as $key => $vl) {
							DB::table('articlecolor')->insertGetId(
								['ArticleId' => $get_articleid, 'ArticleColorId' => $vl['Id'], 'ArticleColorName' => $vl['Name'], 'CreatedDate' => date("Y-m-d H:i:s")]
							);
							if ($Colorflag == 1 && $comma == 1) {
								DB::table('transportoutwardpacks')->insertGetId(
									['ArticleId' => $get_articleid, 'ColorId' => $vl['Id'], 'OutwardId' => 0, 'NoPacks' => $arrayNoPacks[$key], 'PartyId' => $imp_party, 'CreatedDate' => date("Y-m-d H:i:s")]
								);
							} else {
								DB::table('transportoutwardpacks')->insertGetId(
									['ArticleId' => $get_articleid, 'ColorId' => "", 'OutwardId' => 0, 'NoPacks' => $arrayNoPacks, 'PartyId' => $imp_party, 'CreatedDate' => date("Y-m-d H:i:s")]
								);
							}
						}
						foreach ($ArticleSizeData as $key => $vl) {
							if ($imp_sizeid) {
								 DB::table('articlesize')->insertGetId(
									['ArticleId' => $get_articleid, 'ArticleSize' => $vl['Id'], 'ArticleSizeName' => $vl['Name'], 'CreatedDate' => date("Y-m-d H:i:s")]
								);
							}
							if ($imp_ratioid) {
								DB::table('articleratio')->insertGetId(
									['ArticleId' => $get_articleid, 'ArticleSizeId' => $vl['Id'], 'ArticleRatio' => $getratio[$key], 'CreatedDate' => date("Y-m-d H:i:s")]
								);
							}
						}
						$articledata = DB::select("select count(*) as Total from articlerate where ArticleId = '" . $get_articleid . "'");
						if ($articledata[0]->Total > 0) {
							DB::table('articlerate')
								->where('ArticleId', $get_articleid)
								->update(['ArticleRate' => $imp_rate, 'UpdatedDate' => date("Y-m-d H:i:s")]);
						} else {
							DB::table('articlerate')->insertGetId(
								['ArticleId' => $get_articleid, 'ArticleRate' => $imp_rate, 'CreatedDate' => date("Y-m-d H:i:s")]
							);
						}
					} else {
						$TotalSetQuantity = $countNoPacks;
					}
					if ($ArticleOpenFlag == 1) {
						$GRN_Number = $generate_GRN['GRN_Number'];
						$GRN_Number_Financial_Id = $generate_GRN['GRN_Number_Financial_Id'];

						$inwardgrnid = DB::table('inwardgrn')->insertGetId(
							['GRN' => $GRN_Number, "FinancialYearId" => $GRN_Number_Financial_Id, 'InwardDate' => date("Y-m-d"), 'Remark' => '', 'VendorId' => '', 'UserId' => '', 'CreatedDate' => date('Y-m-d H:i:s')]
						);
						if ($imp_color) {
							$ArticleColor = $ArticleColor;
						} else {
							$ArticleColor = "";
						}
						if ($imp_sizeid) {
							$ArticleSize = $ArticleSize;
						} else {
							$ArticleSize = "";
						}
						if ($imp_ratioid) {
							$imp_ratioid = $imp_ratioid;
						} else {
							$imp_ratioid = "";
						}
						$inwardid = DB::table('inward')->insertGetId(
							['ArticleId' => $get_articleid, 'NoPacks' => $NoPacks, 'SalesNoPacks' => $NoPacks, "InwardDate" => date("Y-m-d"), 'GRN' => $inwardgrnid, "Weight" => $imp_weight, 'TotalSetQuantity' => $TotalSetQuantity, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
						);
						DB::table('inwardarticle')->insertGetId(
							['ArticleId' => $get_articleid, 'InwardId' => $inwardid, 'ArticleRate' => $imp_rate, 'ArticleColor' => $ArticleColor, 'ArticleSize' => $ArticleSize, 'ArticleRatio' => $imp_ratioid, 'CreatedDate' => date("Y-m-d H:i:s")]
						);
						DB::table('articlerate')->insertGetId(
							['ArticleId' => $get_articleid, 'ArticleRate' => $imp_rate, 'CreatedDate' => date("Y-m-d H:i:s")]
						);
						DB::table('mixnopacks')->insertGetId(
							['ArticleId' => $get_articleid, 'NoPacks' => $NoPacks, 'CreatedDate' => date("Y-m-d H:i:s"), 'UpdatedDate' => date("Y-m-d H:i:s")]
						);
					}
				}
			}
		}
		return response()->json(array("Message" => "Success"), 200);
	}
}
