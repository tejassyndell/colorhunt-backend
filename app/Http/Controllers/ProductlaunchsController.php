<?php

namespace App\Http\Controllers;

use App\Productlaunch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\UserLogs;
use App\Users;
use App\Article;

class ProductlaunchsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

	public function RemainingLaunchArticle(){
		return DB::select("SELECT a.Id, a.ArticleNumber, a.ArticleStatus FROM `inward` i left join article a on i.ArticleId=a.Id where a.ArticleOpenFlag=0 and (a.ArticleStatus=0 or a.ArticleStatus=2) group by i.ArticleId");
	}

	public function launcharticlecheck($id){
			return DB::select("SELECT * FROM `productlaunch` where ArticleId='".$id."' and ProductStatus=2");
	}

	public function approvalproductlist(){
		return DB::select("SELECT c.Title, a.*, DATE_FORMAT(a.UpdatedDate, '%d/%m/%Y') as approvaldate  FROM `article` a inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId WHERE a.ArticleStatus='1' and a.UpdatedDate >= DATE_SUB(CURDATE(), INTERVAL 1 Month) ORDER BY `a`.`UpdatedDate` DESC");
	}

	public function rejectedproductlist(){
		return DB::select("SELECT c.Title, a.*, DATE_FORMAT(a.UpdatedDate, '%d/%m/%Y') as approvaldate FROM `rejectionarticle` a inner join po p on p.ArticleId=a.PlArticleId inner join category c on c.Id=p.CategoryId ORDER BY `a`.`UpdatedDate` DESC");
	}

	public function holdproductlist(){
		return DB::select("SELECT c.Title, pl.Remarks , a.*, DATE_FORMAT(a.UpdatedDate, '%d/%m/%Y') as approvaldate  FROM `article` a inner join productlaunch pl on pl.ArticleId=a.Id inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId WHERE a.ArticleStatus='2' ORDER BY `a`.`UpdatedDate` DESC");

	}

	public function rejectionproductlist(){

	}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$data = $request->all();
		$pldata = DB::select("SELECT count(*) as total FROM `productlaunch` where ArticleId='" . $data["ArticleId"]["Id"] . "'");
		$data_new = array("ArticleId" => $data["ArticleId"]["Id"], "ProductStatus" => $data["ProductStatus"], "Remarks" => $data["Remarks"], "UserId" => $data["UserId"]);
		DB::beginTransaction();
		if ($data['ProductStatus'] == 1 || $data['ProductStatus'] == 2) {
			try {
				if ($pldata[0]->total > 0) {
					DB::table('productlaunch')
						->where('ArticleId', $data_new["ArticleId"])
						->update(['Remarks' => $data_new['Remarks'], 'ProductStatus' => $data_new['ProductStatus']]);
					$userName = Users::where('Id', $data['UserId'])->first();
					$proLaunch = Article::where('Id', $data_new["ArticleId"])->first();
					if ($data['ProductStatus'] == 1) {
						UserLogs::create([
							'Module' => 'Product Launch Status',
							'ModuleNumberId' => $proLaunch->Id,
							'LogType' => 'Updated',
							'LogDescription' => $userName['Name'] . " " . 'approved Article number' . " " . $proLaunch->ArticleNumber,
							'UserId' => $userName['Id'],
							'updated_at' => null
						]);
					} else if ($data['ProductStatus'] == 2) {
						UserLogs::create([
							'Module' => 'Product Launch Status',
							'ModuleNumberId' => $proLaunch->Id,
							'LogType' => 'Updated',
							'LogDescription' => $userName['Name'] . " " . 'put Article number ' . $proLaunch->ArticleNumber . " on hold",
							'UserId' => $userName['Id'],
							'updated_at' => null
						]);
					}
				} else {
					Productlaunch::create($data_new);
					$userName = Users::where('Id', $data['UserId'])->first();
					$proLaunch = Article::where('Id', $data_new["ArticleId"])->first();
					if ($data['ProductStatus'] == 1) {
						UserLogs::create([
							'Module' => 'Product Launch Status',
							'ModuleNumberId' => $proLaunch->Id,
							'LogType' => 'Created',
							'LogDescription' => $userName['Name'] . " " . 'approved Article number' . " " . $proLaunch->ArticleNumber,
							'UserId' => $userName['Id'],
							'updated_at' => null
						]);
					} elseif ($data['ProductStatus'] == 2) {
						UserLogs::create([
							'Module' => 'Product Launch Status',
							'ModuleNumberId' => $proLaunch->Id,
							'LogType' => 'Created',
							'LogDescription' => $userName['Name'] . " " . 'put Article number' . " " . $proLaunch->ArticleNumber . " " . "on hold",
							'UserId' => $userName['Id'],
							'updated_at' => null
						]);
					}
				}
				DB::table('article')
					->where('Id', $data_new["ArticleId"])
					->update(['ArticleStatus' => $data_new['ProductStatus'], 'UpdatedDate' => date("Y-m-d H:i:s")]);
				DB::table('productlaunchlogs')->insertGetId(
					['ArticleId' => $data_new["ArticleId"], 'ProductStatus' => $data_new['ProductStatus'], 'Remarks' => $data_new['Remarks'], 'UserId' => $data_new['UserId'], 'created_at' => date("Y-m-d H:i:s")]
				);
				DB::commit();
				return response()->json("SUCCESS", 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}
		} else {
			try {
				$userName = Users::where('Id', $data['UserId'])->first();
				$proLaunch = Article::where('Id', $data_new['ArticleId'])->first();
				if ($pldata[0]->total == 0) {
					Productlaunch::create($data_new);
					UserLogs::create([
						'Module' => 'Product Launch Status',
						'ModuleNumberId' => $proLaunch->Id,
						'LogType' => 'Updated',
						'LogDescription' => $userName['Name'] . " " . 'rejected Article number' . " " . $proLaunch->ArticleNumber,
						'UserId' => $userName['Id'],
						'updated_at' => null
					]);
				} else {
					DB::table('productlaunch')
						->where('ArticleId', $data_new["ArticleId"])
						->update(['Remarks' => $data_new['Remarks'], 'ProductStatus' => $data_new['ProductStatus']]);
					UserLogs::create([
						'Module' => 'Product Launch Status',
						'ModuleNumberId' => $proLaunch->Id,
						'LogType' => 'Created',
						'LogDescription' => $userName['Name'] . " " . 'rejected Article number' . " " . $proLaunch->ArticleNumber,
						'UserId' => $userName['Id'],
						'updated_at' => null
					]);
				}
				$art = DB::select('select * from article where Id = "' . $data_new["ArticleId"] . '"');
				$rejectartid = DB::table('rejectionarticle')->insertGetId(
					['ArticleNumber' => $art[0]->ArticleNumber, 'PlArticleId' => $data_new["ArticleId"], 'ArticleRate' => $art[0]->ArticleRate, 'ArticleColor' => $art[0]->ArticleColor, 'ArticleSize' => $art[0]->ArticleSize, 'ArticleRatio' => $art[0]->ArticleRatio, 'ArticleOpenFlag' => $art[0]->ArticleOpenFlag, 'StyleDescription' => $art[0]->StyleDescription, 'ArticleStatus' => $data_new["ProductStatus"], 'Remarks' => $data_new["Remarks"], 'UpdatedDate' => date("Y-m-d H:i:s")]
				);
				DB::table('productlaunchlogs')->insertGetId(
					['ArticleId' => $data_new["ArticleId"], 'ProductStatus' => $data_new['ProductStatus'], 'Remarks' => $data_new['Remarks'], 'UserId' => $data_new['UserId'], 'RejArticleId' => $rejectartid, 'created_at' => date("Y-m-d H:i:s")]
				);
				DB::table('article')
					->where('Id', $data_new["ArticleId"])
					->update(['ArticleRate' => '', 'ArticleColor' => '', 'ArticleSize' => '', 'ArticleRatio' => '', 'ArticleStatus' => $data_new["ProductStatus"], 'UpdatedDate' => date("Y-m-d H:i:s")]);
				DB::table('articlerate')
					->where('ArticleId', $data_new["ArticleId"])
					->update(['ArticleRate' => '']);
				$arcolor = DB::select('select * from articlecolor where ArticleId = "' . $data_new["ArticleId"] . '"');
				foreach ($arcolor as $data) {
					DB::table('rejectionarticlecolor')->insertGetId(
						['RejectedArticleId' => $rejectartid, 'PlArticleId' => $data->ArticleId, 'ArticleColorId' => $data->ArticleColorId, 'ArticleColorName' => $data->ArticleColorName, 'CreatedDate' => date("Y-m-d H:i:s")]
					);
				}

				$arratio = DB::select('select * from articleratio where ArticleId = "' . $data_new["ArticleId"] . '"');
				foreach ($arratio as $data) {
					DB::table('rejectionarticleratio')->insertGetId(
						['RejectedArticleId' => $rejectartid, 'PlArticleId' => $data->ArticleId, 'ArticleSizeId' => $data->ArticleSizeId, 'ArticleRatio' => $data->ArticleRatio, 'CreatedDate' => date("Y-m-d H:i:s")]
					);
				}

				$arsize = DB::select('select * from articlesize where ArticleId = "' . $data_new["ArticleId"] . '"');
				foreach ($arsize as $data) {
					DB::table('rejectionarticlesize')->insertGetId(
						['RejectedArticleId' => $rejectartid, 'PlArticleId' => $data->ArticleId, 'ArticleSize' => $data->ArticleSize, 'ArticleSizeName' => $data->ArticleSizeName, 'CreatedDate' => date("Y-m-d H:i:s")]
					);
				}
				DB::table('articlecolor')->where('ArticleId', '=', $data_new["ArticleId"])->delete();
				DB::table('articlesize')->where('ArticleId', '=', $data_new["ArticleId"])->delete();
				DB::table('articleratio')->where('ArticleId', '=', $data_new["ArticleId"])->delete();
				$inwardData = DB::select("select count(*) as total FROM `inward` where ArticleId='" . $data_new["ArticleId"] . "'");
				if ($inwardData[0]->total > 0) {
					DB::table('inward')->where('ArticleId', '=', $data_new["ArticleId"])->delete();
				}
				DB::commit();
				return response()->json("SUCCESS", 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}
		}
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Productlaunch  $productlaunch
     * @return \Illuminate\Http\Response
     */
    public function show(Productlaunch $productlaunch)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Productlaunch  $productlaunch
     * @return \Illuminate\Http\Response
     */
    public function edit(Productlaunch $productlaunch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Productlaunch  $productlaunch
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Productlaunch $productlaunch)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Productlaunch  $productlaunch
     * @return \Illuminate\Http\Response
     */
    public function destroy(Productlaunch $productlaunch)
    {
        //
    }
    public function ApproveArticlelogs($id)
	{
		// return $id;
		return DB::select("select * from (select ul.Id as UserLogsId, ul.Module, ul.LogType, ul.ModuleNumberId, ul.LogDescription, DATE_FORMAT(ul.created_at ,'%d-%m-%Y') as CreatedDate, DATE_FORMAT(ul.created_at ,'%H:%i:%s') as CreatedTime, u.Name, u.Status, ur.Role, pl.ProductStatus from userlogs ul inner join users u on u.Id=ul.UserId inner join userrole ur on ur.RoleType=u.Role inner join productlaunch pl on pl.ArticleId=ul.ModuleNumberId) as dd where dd.ModuleNumberId= '". $id ."' and dd.Module='Product Launch Status' and dd.ProductStatus=1 order by dd.UserLogsId desc ");
	}
	public function HoldArticlelogs($id)
	{
		return DB::select("select * from (select ul.Id as UserLogsId, ul.Module, ul.LogType, ul.ModuleNumberId, ul.LogDescription, DATE_FORMAT(ul.created_at ,'%d-%m-%Y') as CreatedDate, DATE_FORMAT(ul.created_at ,'%H:%i:%s') as CreatedTime, u.Name, u.Status, ur.Role, pl.ProductStatus from userlogs ul inner join users u on u.Id=ul.UserId inner join userrole ur on ur.RoleType=u.Role inner join productlaunch pl on pl.ArticleId=ul.ModuleNumberId) as dd where dd.ModuleNumberId= '". $id ."' and dd.Module='Product Launch Status' and dd.ProductStatus=2 order by dd.UserLogsId desc ");
	}
	public function RejectedArticlelogs($id)
	{
		return DB::select("select * from (select ul.Id as UserLogsId, ul.Module, ul.LogType, ul.ModuleNumberId, ul.LogDescription, DATE_FORMAT(ul.created_at ,'%d-%m-%Y') as CreatedDate, DATE_FORMAT(ul.created_at ,'%H:%i:%s') as CreatedTime, u.Name, u.Status, ur.Role, pl.ProductStatus from userlogs ul inner join users u on u.Id=ul.UserId inner join userrole ur on ur.RoleType=u.Role inner join productlaunch pl on pl.ArticleId=ul.ModuleNumberId) as dd where dd.ModuleNumberId= '". $id ."' and dd.Module='Product Launch Status' and dd.ProductStatus=3 order by dd.UserLogsId desc ");
	}
}
