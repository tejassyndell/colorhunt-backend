<?php

namespace App\Http\Controllers;

use App\Productlounch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductlounchsController extends Controller
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
	
	public function launcharticlecheck($id){
			return DB::select("SELECT * FROM `productlounch` where ArticleId='".$id."'");
	}
	
	public function approvalproductlist(){
		//return DB::select("SELECT c.Title, a.*, DATE_FORMAT(a.UpdatedDate, '%d/%m/%Y') as approvaldate  FROM `article` a inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId WHERE a.ArticleStatus='1' and a.UpdatedDate >= DATE_SUB(CURDATE(), INTERVAL 1 Month) ORDER BY STR_TO_DATE(approvaldate ,'%d/%m/%Y') DESC");
		return DB::select("SELECT c.Title, a.*, DATE_FORMAT(a.UpdatedDate, '%Y-%m-%d') as approvaldate  FROM `article` a inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId WHERE a.ArticleStatus='1' and a.UpdatedDate >= DATE_SUB(CURDATE(), INTERVAL 1 Month) ORDER BY `a`.`UpdatedDate` DESC");
		//"SELECT *  FROM `article` WHERE ArticleStatus='1' and UpdatedDate >= DATE_SUB(CURDATE(), INTERVAL 1 Month)"
	}
	
	public function holdproductlist(){
		return DB::select("SELECT c.Title, pl.Remarks , a.*, DATE_FORMAT(a.UpdatedDate, '%Y-%m-%d') as approvaldate  FROM `article` a inner join productlounch pl on pl.ArticleId=a.Id inner join po p on p.ArticleId=a.Id inner join category c on c.Id=p.CategoryId WHERE a.ArticleStatus='2' ORDER BY `a`.`UpdatedDate` DESC");
		
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
		return	
		//var_dump($data);
		print_r($data); 
		exit;
		$pldata = DB::select("SELECT count(*) as total FROM `productlounch` where ArticleId='".$data["ArticleId"]["Id"]."'");
		/*
			Array
			(
				[ArticleId] => Array
					(
						[Id] => 5
						[ArticleNumber] => 15219
					)

				[QualityStatus] => 1
				[Remarks] => 
				[UserId] => 25
			)
		*/
		
		$data_new = array("ArticleId"=>$data["ArticleId"]["Id"], "ProductStatus"=>$data["ProductStatus"], "Remarks"=>$data["Remarks"], "UserId"=>$data["UserId"]);
				
				
		DB::beginTransaction();
		if($data['ProductStatus']==1 || $data['ProductStatus']==2){
			try {
				return print_r($data_new); exit; 
				if($pldata[0]->total>0){
					DB::table('article')
					->where('Id', $data_new["ArticleId"])
					->update(['ArticleStatus' => $data_new['ProductStatus']]);
					
					DB::table('productlounch')
					->where('ArticleId', $data_new["ArticleId"])
					->update(['ProductStatus' => $data_new['ProductStatus']]);
					
				} else{
					Productlounch::create($data_new);
				}
				DB::commit();
				return response()->json("SUCCESS", 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}
		} else{
			try {
				//return $data["ArticleId"]; exit;
				if($pldata[0]->total==0){
					Productlounch::create($data_new);
				}else{
					return print_r($data); exit;
					/* DB::table('productlounch')
						->where('ArticleId', $data_new["ArticleId"])
						->update(['ProductStatus' => $data_new['ProductStatus']]); */
						return "asdasd"; exit;
				}
				exit;
				$art = DB::select('select * from article where Id = "'.$data["ArticleId"].'"');
				DB::table('rejectionarticle')->insertGetId(
					['ArticleNumber'=>$art[0]->ArticleNumber, 'ArticleRate' => $art[0]->ArticleRate, 'ArticleColor' => $art[0]->ArticleColor,'ArticleSize' => $art[0]->ArticleSize,'ArticleRatio' => $art[0]->ArticleRatio,'ArticleOpenFlag' => $art[0]->ArticleOpenFlag,'StyleDescription' => $art[0]->StyleDescription,'ArticleStatus' => $art[0]->ArticleStatus,'UpdatedDate' => date("Y-m-d H:i:s")]
				);
					
				DB::table('article')
					->where('Id', $data["ArticleId"])
					->update(['ArticleRate' => '', 'ArticleColor' => '', 'ArticleSize' => '', 'ArticleRatio' => '', 'UpdatedDate' => date("Y-m-d H:i:s")]);
					
				DB::table('articlerate')
					->where('ArticleId', $data["ArticleId"])
					->update(['ArticleRate' => '']);
					
				//DB::table('inwardarticle')->where('InwardId', '=', $id)->delete();
				
				$arcolor = DB::select('select * from articlecolor where ArticleId = "'.$data["ArticleId"].'"');
				 foreach($arcolor as $data){
					 DB::table('rejectionarticlecolor')->insertGetId(
						['ArticleId'=>$data->ArticleId, 'ArticleColorId' => $data->ArticleColorId, 'ArticleColorName' => $data->ArticleColorName,'CreatedDate' => date("Y-m-d H:i:s")]
					);
				 }
				 
				 $arratio = DB::select('select * from articleratio where ArticleId = "'.$data["ArticleId"].'"');
				 foreach($arratio as $data){
					 DB::table('rejectionarticleratio')->insertGetId(
						['ArticleId'=>$data->ArticleId, 'ArticleSizeId' => $data->ArticleSizeId, 'ArticleRatio' => $data->ArticleRatio,'CreatedDate' => date("Y-m-d H:i:s")]
					);
				 }
				 
				 $arsize = DB::select('select * from articlesize where ArticleId = "'.$data["ArticleId"].'"');
				 foreach($arsize as $data){
					 DB::table('rejectionarticlesize')->insertGetId(
						['ArticleId'=>$data->ArticleId, 'ArticleSize' => $data->ArticleSize, 'ArticleSizeName' => $data->ArticleSizeName,'CreatedDate' => date("Y-m-d H:i:s")]
					);
				 }
				 
				DB::table('articlecolor')->where('ArticleId', '=', $data["ArticleId"])->delete();
				DB::table('articlesize')->where('ArticleId', '=', $data["ArticleId"])->delete();
				DB::table('articleratio')->where('ArticleId', '=', $data["ArticleId"])->delete();
				
				$inwardData = DB::select("select count(*) as total FROM `inward` where ArticleId='".$data["ArticleId"]."'");
				if($inwardData[0]->total>0){
					DB::table('inward')->where('ArticleId', '=', $data["ArticleId"])->delete();
				}
				DB::commit();
				return response()->json("SUCCESS", 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}

			
		}
			
		/* if($data['ProductStatus']==1){
			try {
				//return print_r($data); exit; 
				if($pldata[0]->total>0){
					DB::table('article')
					->where('Id', $data['ArticleId'])
					->update(['ArticleStatus' => $data['ProductStatus']]);
				} else{
					Productlounch::create($data);
				}
				DB::commit();
				return response()->json("SUCCESS", 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}
		} else if($data['ProductStatus']==2){
			try {
				//return print_r($data); exit; 
				
			 
			 if($pldata[0]->total>0){
				DB::table('article')
					->where('Id', $data['ArticleId'])
					->update(['ArticleStatus' => $data['ProductStatus']]);
				} else{
					Productlounch::create($data);
				}
				
				//Productlounch::create($data);
				DB::commit();
				return response()->json("SUCCESS", 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}
		} else{
			try {
				//return print_r($data); exit; 
				if($pldata[0]->total>0){
				DB::table('article')
					->where('Id', $data['ArticleId'])
					->update(['ArticleStatus' => $data['ProductStatus']]);
				} else{
					Productlounch::create($data);
				}
					
				DB::commit();
				return response()->json("SUCCESS", 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}
		} */
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Productlounch  $productlounch
     * @return \Illuminate\Http\Response
     */
    public function show(Productlounch $productlounch)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Productlounch  $productlounch
     * @return \Illuminate\Http\Response
     */
    public function edit(Productlounch $productlounch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Productlounch  $productlounch
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Productlounch $productlounch)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Productlounch  $productlounch
     * @return \Illuminate\Http\Response
     */
    public function destroy(Productlounch $productlounch)
    {
        //
    }
}
