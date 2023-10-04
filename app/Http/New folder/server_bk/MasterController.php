<?php

namespace App\Http\Controllers;

use App\Brand;
use App\Category;
use App\Color;
use App\Subcategory;
use App\Rangeseries;
use App\Article;
use App\Pages;
use App\Rack;
use App\Ratio;
use App\Size;
use App\UserRole;
use App\Vendor;
use App\Party;
use App\SO;
use App\Imports\DataImport;
use App\ImportTool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Pbmedia\LaravelFFMpeg\FFMpeg;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Input;
use File;

class MasterController extends Controller
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
    public function GetRole()
    {
        return UserRole::all();
    }
    public function GetPage()
    {
        return Pages::all();
    }
	
	public function admingetuserId(){
			return DB::select("SELECT Id FROM `users` where Role = 2 limit 0,1");
	}
	
    public function GetUserRights($id)
    {
        //echo "<pre>";print_r($id);exit;
        return DB::select('select r.*,p.Name,p.Id as PId from pages p left join userrights r on (r.PageId = p.id and r.RoleId = ' . $id . ') left join userrole role on (r.RoleId = role.Id) order by OrderSet ASC');

        //  return UserRights::all();
    }

    public function GetUserRoleRightsSidebar($id)
    {
        /* $getdata = DB::select('SELECT ur.*,p.Name,p.Id as PageId FROM `pages` p LEFT JOIN userrights ur ON p.Id=ur.PageId WHERE ur.RoleId = ' . $id . '');
        $array = array();
        foreach ($getdata as $key => $value) {
            $array[$value->PageId] = $value;
        }
        return $array; */
		return DB::select('SELECT ur.*,p.Name,p.Id as PageId FROM `pages` p LEFT JOIN userrights ur ON p.Id=ur.PageId WHERE ur.RoleId = ' . $id . '');

    }

    public function GetUserRoleRights($id)
    {
        //echo "<pre>";print_r($id);exit;
        return DB::select('SELECT ur.*,p.Name,p.Id as PageId FROM `pages` p LEFT JOIN userrights ur ON p.Id=ur.PageId WHERE ur.RoleId = ' . $id . ' and ListRights= 1');

        //  return UserRights::all();
    }

    ///Color Module ///
    public function AddColor(Request $request)
    {
        $data = $request->all();
        $dataresult= DB::select('SELECT * FROM `color` WHERE `Name` LIKE "'.$data['Name'].'"'); 
        if($dataresult){
            return response()->json('allreadyexits', 201);
        }else{
            $field = Color::create($request->all());
            return response()->json($field, 201);
        }
     
      
    }

    public function Getcolor()
    {
        return Color::all();
    }
	
	public function Postcolor(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
	    $vnddataTotal = DB::select("SELECT count(*) as Total From color");
		$length = $data["length"];
			
		if($search['value'] != null && strlen($search['value']) > 2){
			$searchstring = "WHERE Name like '%".$search['value']."%'";
			$vnddataTotalFilter = DB::select("SELECT count(*) as Total From color ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "";
			$vnddataTotalFilterValue = $vnddataTotal[0]->Total;
		}
		
		$vnddata = DB::select("SELECT * From color ".$searchstring." limit ".$data["start"].",".$length);
		return array(
				'datadraw'=>$data["draw"],
				'recordsTotal'=>$vnddataTotal[0]->Total,
				'recordsFiltered'=>$vnddataTotalFilterValue,
				'response' => 'success',
				'startnumber' => $startnumber,
				'search'=>count($vnddata),
				'data' => $vnddata,
			);
	}
    public function UpdateColor(Request $request)
    {

        $data = $request->all();
        Color::where('id', $data['id'])->update(array(
            'Name' => $data['Name'],
        ));
        return response()->json("SUCCESS", 200);
    }
    public function DeleteColor($id)
    {
        return DB::table('color')->where('Id', '=', $id)->delete();

    }
    public function GetColorIdWise($id)
    {
        return DB::select('SELECT * From color WHERE Id = ' . $id . '');
    }

    ////Size Module
    public function Addsize(Request $request)
    {
        $data = $request->all();
        $dataresult= DB::select('SELECT * FROM `size` WHERE `Name` LIKE "'.$data['Name'].'"'); 
        if($dataresult){
            return response()->json('allreadyexits', 201);
        }else{
            $field = Size::create($request->all());
            return response()->json($field, 201);
        }
     
    }

    public function Getsize()
    {
        return Size::all();
    }
	
	public function Postsize(Request $request){
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
		$vnddataTotal = DB::select("SELECT count(*) as Total From size");
		$venTotal= $vnddataTotal[0]->Total;
		$length = $data["length"];
		
		if($search['value'] != null && strlen($search['value']) >= 2){
			$searchstring = "WHERE Name like '%".$search['value']."%'";
			$vnddataTotalFilter = DB::select("SELECT count(*) as Total From size ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "";
			$vnddataTotalFilterValue =$venTotal;
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
		
		$vnddata = DB::select("SELECT * From size ".$searchstring." ".$order."  limit ".$data["start"].",".$length);
		return array(
				'datadraw'=>$data["draw"],
				'recordsTotal'=>$venTotal,
				'recordsFiltered'=>$vnddataTotalFilterValue,
				'response' => 'success',
				'search'=>count($vnddata),
				'startnumber' => $startnumber,
				'length'=>$data["start"],
				'data' => $vnddata,
			);
	}
    public function UpdateSize(Request $request)
    {

        $data = $request->all();
        Size::where('id', $data['id'])->update(array(
            'Name' => $data['Name'],
        ));
        return response()->json("SUCCESS", 200);
    }

    public function Deletesize($id)
    {
        return DB::table('size')->where('Id', '=', $id)->delete();

    }
    public function GetSizeIdWise($id)
    {
        return DB::select('SELECT * From size WHERE Id = ' . $id . '');
    }


    ///Ratio Module

    public function Addratio(Request $request)
    {
        $data = $request->all();
        $dataresult= DB::select('SELECT * FROM `ratio` WHERE `Name` LIKE "'.$data['Name'].'"'); 
        if($dataresult){
            return response()->json('allreadyexits', 201);
        }else{
            $field = Ratio::create($request->all());
            return response()->json($field, 201);
        }
      
    }

    public function Getratio()
    {
        return Ratio::all();
    }
    public function UpdateRatio(Request $request)
    {

        $data = $request->all();
        Ratio::where('id', $data['id'])->update(array(
            'Name' => $data['Name'],
        ));
        return response()->json("SUCCESS", 200);
    }

    public function Deleteratio($id)
    {
        return DB::table('ratio')->where('Id', '=', $id)->delete();

    }
    public function GetRatioIdWise($id)
    {
        return DB::select('SELECT * From ratio WHERE Id = ' . $id . '');
    }

    ///Category Module
	public function AddCategory(Request $request)
    {
        //$field = Vendor::create($request->all());
        $data = $request->all();
        $array = array();
        // echo "<pre>";
        //print_r($data);
        //print_r($request->file('myfile'));
        // exit;
        $dataresult = DB::select('SELECT * FROM `category` WHERE `Title` LIKE "' . $data['Title'] . '"');
        if ($dataresult) {
            return response()->json('allreadyexits', 201);
        } else {
            $images = array();
            if ($request->file('myfile')) {
                if ($files = $data['myfile']) {
                    foreach ($files as $file) {
                        $dataimage = getimagesize($file->getRealPath());
                        $width = $dataimage[0];
                        $height = $dataimage[1];
                        // print_r($width);
                        //print_r($height);
                        //exit;
                        //  if ($width == 600 && $height == 800) {

                        $name = $file->getClientOriginalName();
                        $randomstring = $this->generateRandomString();
                        $name_extension = explode(".", $name);
                        $newname = $randomstring . '.' . $name_extension[1];
                        $file->move('uploads', $newname);
                        $images[] = $newname;
                        // } else {
                        //     return response()->json(array("NoMatch" => "true"), 200);
                        // }

                    }
                }
            }


			if($data['Colorflag'] == 'true') {
				$colorflag = '1';
			} else {
				$colorflag = '0';
			}
			
			if ($data['ArticleOpenFlag'] == 'true') {
				$articleopenflag = '1';
			} else {
				$articleopenflag = '0';
			}
			
			if($data['ArticleAutoGenerate']!=''){
				$ArticleAutoGenerate = $data['ArticleAutoGenerate'];
			}else{
				$ArticleAutoGenerate = 0;
			}
			
			if($data['ArticleSeriesAuto']!=''){
				$ArticleSeriesAuto = $data['ArticleSeriesAuto'];
			}else{
				$ArticleSeriesAuto = 0;
			}
				
            if (count($images) > 0) {
                
                foreach ($images as $img) {
                    DB::table('category')->insert(
                        [
                            'Title' => $data['Title'],
                            'Colorflag' => $colorflag,
							'ArticleOpenFlag' => $articleopenflag,
                            'Status' => $data['Status'],
                            'Image' => $img,
							'ArticleAutoGenerate' => $ArticleAutoGenerate,
							'ArticleSeriesAuto' => $ArticleSeriesAuto,
                            "created_at" => date("Y-m-d H:i:s"),
                        ]

                    );
                }

                return response()->json(array("result" => "true"), 200);
            } else {
                
                DB::table('category')->insert(
                    [
                        'Title' => $data['Title'],
                        'Colorflag' => $colorflag,
						'ArticleOpenFlag' => $articleopenflag,
                        'Status' => $data['Status'],
                        'Image' => "",
						'ArticleAutoGenerate' => $ArticleAutoGenerate,
						'ArticleSeriesAuto' => $ArticleSeriesAuto,
                        "created_at" => date("Y-m-d H:i:s"),
                    ]

                );

                return response()->json(array("result" => "true"), 200);
                // return response()->json(array("result" => "false"), 200);
            }
        }
        //echo "<pre>"; print_r($images);
        //echo "Upload";
        //exit;
    }
	
	public function UpdateCategory(Request $request)
    {

        $data = $request->all();
        //print_r($data);
        //  print_r($request->file('myfile'));
        //exit;
        $images = array();
        if ($request->file('myfile')) {
            if ($files = $data['myfile']) {
                foreach ($files as $file) {
                    $name = $file->getClientOriginalName();
                    $randomstring = $this->generateRandomString();
                    $name_extension = explode(".", $name);
                    $newname = $randomstring . '.' . $name_extension[1];
                    $file->move('uploads', $newname);
                    $images[] = $newname;
                }
            }
        }

		if($data['Colorflag'] == 'true') {
			$colorflag = '1';
		} else {
			$colorflag = '0';
		}
		
		if ($data['ArticleOpenFlag'] == 'true') {
			$articleopenflag = '1';
		} else {
			$articleopenflag = '0';
		}
		
		if($data['ArticleAutoGenerate']!=''){
				$ArticleAutoGenerate = $data['ArticleAutoGenerate'];
			}else{
				$ArticleAutoGenerate = 0;
			}
			
			if($data['ArticleSeriesAuto']!=''){
				$ArticleSeriesAuto = $data['ArticleSeriesAuto'];
			}else{
				$ArticleSeriesAuto = 0;
			}

        if (count($images) > 0) {
           
            foreach ($images as $img) {
                if ($request->file('myfile')) {
                    $img = $img;
                }
                // echo "<pre>";
                //print_r($data);
                // print_r($request->file('myfile'));
                //exit;
                Category::where('id', $data['id'])->update(array(
                    'Title' => $data['Title'],
                    'Colorflag' => $colorflag,
					'ArticleOpenFlag' => $articleopenflag,
                    'Status' => $data['Status'],
                    'Image' => $img,
					'ArticleAutoGenerate' => $ArticleAutoGenerate,
						'ArticleSeriesAuto' => $ArticleSeriesAuto,
                ));
            }

            return response()->json(array("result" => "true"), 200);
        } else {
            // echo "<pre>";
            
            // print_r($request->file('myfile'));
            
            
			if($data['hdnImg']!=="undefined"){
				$img = $data['hdnImg'];
			}else {
				$img = "";
			}
			
					
            Category::where('id', $data['id'])->update(array(
                'Title' => $data['Title'],
                'Colorflag' => $colorflag,
				'ArticleOpenFlag' => $articleopenflag,
                'Status' => $data['Status'],
				'ArticleAutoGenerate' => $ArticleAutoGenerate,
				'ArticleSeriesAuto' => $ArticleSeriesAuto,
                'Image' => $img,
            ));
            return response()->json(array("result" => "true"), 200);
            // return response()->json(array("result" => "false"), 200);
        }
        //ech
        // Category::where('id', $data['id'])->update(array(
        //     'Title' => $data['Title'],
        //     'Colorflag' => $data['Colorflag'],
        //     'Status' => $data['Status'],
        // ));
        // return response()->json("SUCCESS", 200);
    }
	
    /* public function AddCategory(Request $request)
    {
        $data = $request->all();
        $dataresult= DB::select('SELECT * FROM `category` WHERE `Title` LIKE "'.$data['Title'].'"'); 
        if($dataresult){
            return response()->json('allreadyexits', 201);
        }else{
            $field = Category::create($request->all());
            return response()->json($field, 201);
        }

        
    } */
    public function Getcategory()
    {
		//select * from (SELECT p.Id, c.Id as CategoryId, IF(c.id IS NULL, 'no selected', c.Title) as Title FROM `po` p right join category c on c.Id=p.CategoryId) as t group by CategoryId
        //return Category::all();
		return Category::orderBy('Title', 'ASC')->get();
    }
	//jignesh
	public function Postcategory(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
		
		$vnddataTotal = DB::select("SELECT count(*) as Total From category");
		$length = $data["length"];
		
		if($search['value'] != null && strlen($search['value']) > 2){
			$searchstring = "WHERE Title like '%".$search['value']."%'";
			$vnddataTotalFilter = DB::select("SELECT count(*) as Total From category ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "";
			$vnddataTotalFilterValue = $vnddataTotal[0]->Total;
		}
		
		//Filter Orderby value code
		$column = $data["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "Title";
				break;
			case 2:
				$ordercolumn = "Colorflag";
				break;
			case 3:
				$ordercolumn = "ArticleOpenFlag";
				break;
			case 4:
				$ordercolumn = "ArticleAutoGenerate";
				break;
			case 5:
				$ordercolumn = "ArticleSeriesAuto";
				break;
			default:
				$ordercolumn = "Title";
				break;
		}
		
		$order = "";	
		if($data["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data["order"][0]["dir"];
		}
		//end
		
		$vnddata = DB::select("SELECT * From category ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		return array(
				'datadraw'=>$data["draw"],
				'recordsTotal'=>$vnddataTotal[0]->Total,
				'recordsFiltered'=>$vnddataTotalFilterValue,
				'startnumber' => $startnumber,
				'response' => 'success',
				'search'=>count($vnddata),
				'data' => $vnddata,
			);
	}
    /* public function UpdateCategory(Request $request)
    {

        $data = $request->all();
        Category::where('id', $data['id'])->update(array(
            'Title' => $data['Title'],
            'Colorflag' => $data['Colorflag'],
            'Status' => $data['Status'],
        ));
        return response()->json("SUCCESS", 200);
    } */
	
	
	
    public function Deletecategory($id)
    {
        return DB::table('category')->where('Id', '=', $id)->delete();

    }
    public function GetcatIdWise($id)
    {
        //return DB::select('SELECT * From category WHERE Id = ' . $id . '');
		return DB::select("select * from (SELECT (CASE WHEN p.Id IS NULL THEN '0' ELSE '1' END) as POID, c.* FROM `po` p right join category c on c.Id=p.CategoryId) as t where Id = '" . $id . "' group by Id");
	}

    ///Brand Module
    public function AddBrand(Request $request)
    {
        $field = Brand::create($request->all());
        return response()->json($field, 201);
    }
    public function Getbrand()
    {
		
		return Brand::orderBy('Name', 'ASC')->get();

        //return Brand::all();
    }
	
	public function Postbrand(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
		$vnddataTotal = DB::select("SELECT count(*) as Total From brand");
		$vnTotal = $vnddataTotal[0]->Total;
		$length = $data["length"];
		
		if($search['value'] != null && strlen($search['value']) > 2){
			$searchstring = "WHERE Name like '%".$search['value']."%'";
			$vnddataTotalFilter = DB::select("SELECT count(*) as Total From brand ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "";
			$vnddataTotalFilterValue = $vnTotal;
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
		
		$vnddata = DB::select("SELECT * From brand ".$searchstring." ".$order." limit ".$data["start"].",".$length);
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
    public function UpdateBrand(Request $request)
    {

        $data = $request->all();
        Brand::where('id', $data['id'])->update(array(
            'Name' => $data['Name'],
            'Description' => $data['Description'],

        ));
        return response()->json("SUCCESS", 200);
    }
    public function Deletebrand($id)
    {
        return DB::table('brand')->where('Id', '=', $id)->delete();

    }
    public function GetBrandIdWise($id)
    {
		
        return DB::select('SELECT * From brand WHERE Id = ' . $id . '');
    }

    ///Rack Module
    public function AddRack(Request $request)
    {
		$data = $request->all();
        $dataresult= DB::select('SELECT * FROM `rack` WHERE `Number` LIKE "'.$data['Number'].'"'); 
        if($dataresult){
            return response()->json('allreadyexits', 201);
        }else{
			$field = Rack::create($request->all());
        return response()->json($field, 201);
        }
		
        
    }
    public function Getrack()
    {
        return Rack::all();
    }
    public function UpdateRack(Request $request)
    {

        $data = $request->all();
        Rack::where('id', $data['id'])->update(array(
            'Number' => $data['Number']
        ));
        return response()->json("SUCCESS", 200);
    }
    public function Deleterack($id)
    {
        return DB::table('rack')->where('Id', '=', $id)->delete();

    }
    public function GetRackIdWise($id)
    {
        return DB::select('SELECT * From rack WHERE Id = ' . $id . '');
    }
    public function ChekRackexits(Request $request)
    { 
        $data = $request->all();
       //echo"<pre>"; print_r($data[0]);exit;
       return  DB::select('SELECT * FROM `rack` WHERE `Number` LIKE "'.$data[0].'"'); 
     
    }

	
	///User Role Module
    public function AddUserRole(Request $request)
    {
		$data = $request->all();
        $dataresult= DB::select('SELECT * FROM `userrole` WHERE `Role` LIKE "'.$data['Role'].'"'); 
        if($dataresult){
            return response()->json('allreadyexits', 201);
        }else{
			
			$lastroletype= DB::select('SELECT RoleType FROM `userrole` order by Id desc'); 
			$data["RoleType"] = $lastroletype[0]->RoleType + 1;
			$data["IsActive"] = 1;
			$field = UserRole::create($data);
        return response()->json($field, 201);
        }
		
        
    }
    public function GetUserRole()
    {
        return UserRole::all();
    }
    public function UpdateUserRole(Request $request)
    {

        $data = $request->all();
        UserRole::where('id', $data['id'])->update(array(
            'Role' => $data['Role']
        ));
        return response()->json("SUCCESS", 200);
    }
    public function DeleteUserRole($id)
    {
		$userrole = DB::select("SELECT count(*) as Total FROM `userrole` ur inner join users u on u.Role=ur.RoleType where ur.Id=".$id);
        if($userrole[0]->Total==0){
			return DB::table('userrole')->where('Id', '=', $id)->delete();
		} else{
			return response()->json('allreadyassign', 201);
		}
    }
	
    public function GetUserRoleIdWise($id)
    {
        return DB::select('SELECT * From userrole WHERE Id = ' . $id . '');
    }
	
    ///Vendor Module
    public function AddVendor(Request $request)
    {
        $field = Vendor::create($request->all());
        return response()->json($field, 201);
    }
    public function GeVendor()
    {
        //return Vendor::all()->orderBy('Name', 'DESC');
		return Vendor::orderBy('Name', 'ASC')->get();
    }
	//jignesh
	public function PostVendor(Request $request)
    {
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
		$vnddataTotal = DB::select("SELECT count(*) as Total From vendor");
	    $vnTotal = $vnddataTotal[0]->Total;
		$length = $data["length"];
			
		if($search['value'] != null && strlen($search['value']) > 2){ 
			$searchstring = "WHERE PhoneNumber like '%".$search['value']."%' OR Name like '%".$search['value']."%' OR GSTNumber like '%".$search['value']."%' OR ContactPerson like '%".$search['value']."%'";
			$vnddataTotalFilter = DB::select("SELECT count(*) as Total From vendor ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "";
			$vnddataTotalFilterValue = $vnTotal;
		}
		
	   //Filter Orderby value code
		$column = $data["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "Name";
			break;	
			case 2:
				$ordercolumn = "PhoneNumber";
			break;	
			case 3:
				$ordercolumn = "ContactPerson";
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
		
		$vnddata = DB::select("SELECT * From vendor ".$searchstring." ".$order." limit ".$data["start"].",".$length);
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
    public function UpdateVendor(Request $request)
    {
        $data = $request->all();
        Vendor::where('id', $data['id'])->update(array(
            'Name' => $data['Name'],
            'Address' => $data['Address'],
            'PhoneNumber' => $data['PhoneNumber'],
            'ContactPerson' => $data['ContactPerson'],
            'GSTNumber' => $data['GSTNumber']
        ));
        return response()->json("SUCCESS", 200);
    }
    public function Deletevendor($id)
    {
        return DB::table('vendor')->where('Id', '=', $id)->delete();

    }
    public function GetVendorIdWise($id)
    {
        return DB::select('SELECT * From vendor WHERE Id = ' . $id . '');
    }


    ///Party Module
    public function AddParty(Request $request)
    {
		$data = $request->all();
		$party = array();
		
		
		if(isset($data['SoAddParty'])){
			$party['Name'] = $data['PartyName'];
			$party['PhoneNumber'] = $data['PartyContact'];
			$party['Address'] = $data['PartyAddress'];
			$party['OutletAssign'] = 0;
			
			$dataresult= DB::select('SELECT * FROM `party` WHERE `Name` LIKE "'.$data['PartyName'].'"'); 
    		if($dataresult){
    			return response()->json('allreadyexits', 201);
    		}else{
    			$field = Party::create($party);
    			return response()->json($field, 201);
    		}
			
		}else{
			$party['Name'] = $data['Name'];
			$party['Address'] = $data['Address'];
			$party['PhoneNumber'] = $data['PhoneNumber'];
			$party['ContactPerson'] = $data['ContactPerson'];
			$party['GSTNumber'] = $data['GSTNumber'];
			$party['GSTType'] = $data['GSTType'];
			$party['Discount'] = $data['Discount'];
			if($data['OutletAssign']==""){
				$party['OutletAssign'] = 0;
			}else{
				$party['OutletAssign'] = $data['OutletAssign'];
			}
			
			$dataresult= DB::select('SELECT * FROM `party` WHERE `Name` LIKE "'.$data['Name'].'"'); 
			if($dataresult){
				return response()->json('allreadyexits', 201);
			}else{
				$field = Party::create($party);
				return response()->json($field, 201);
			}
		}
		
		

    }
    public function GeParty()
    {
		//return Party::all();
		return Party::orderBy('Name', 'ASC')->get();
		//$file = Storage::put( 'myfile.txt', $user);
    }
	
	public function PostParty(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
		
		$vnddataTotal = DB::select("SELECT count(*) as Total From party");
		$vnTotal = $vnddataTotal[0]->Total;
		$length = $data["length"];
		
		if($search['value'] != null && strlen($search['value']) > 2){
			$searchstring = "WHERE Name like '%".$search['value']."%' or PhoneNumber like '%".$search['value']."%' or GSTNumber like '%".$search['value']."%' OR ContactPerson like '%".$search['value']."%'";
			$vnddataTotalFilter = DB::select("SELECT count(*) as Total From party ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "";
			$vnddataTotalFilterValue = $vnTotal;
		}
		
		//Filter Orderby value code
		$column = $data["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "Name";
				break;	
			case 2:
				$ordercolumn = "PhoneNumber";
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
		
		$vnddata = DB::select("SELECT * From party ".$searchstring." ".$order." limit ".$data["start"].",".$length);
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
	
	function createresult($data){
			switch(strlen($data)){
				case 1:
					return "A274";
					break;
					
				case 2:
					return "A282";
					break;
					
				case 3:
					return "A290";
					break;
					
				case 4:
					return "A298";
					break;
				
				case 5:
					return "A306";
					break;
					
				case 6:
					return "A314";
					break;
				
				case 7:
					return "A322";
					break;
					
				case 8:
					return "A330";
					break;
					
				case 9:
					return "A338";
					break;
					
				case 10:
					return "A346";
					break;
				
				case 11:
					return "A354";
					break;
					
				case 12:
					return "A362";
					break;
					
				case 13:
					return "A370";
					break;
					
				case 14:
					return "A378";
					break;
				
				case 15:
					return "A386";
					break;
					
				case 16:
					return "A394";
					break;
				
				case 17:
					return "A402";
					break;
					
				case 18:
					return "A410";
					break;
					
				case 19:
					return "A418";
					break;
					
				case 20:
					return "A426";
					break;
				
				case 21:
					return "A434";
					break;
					
				case 22:
					return "A442";
					break;
					
				case 23:
					return "A450";
					break;
					
				case 24:
					return "A458";
					break;
				
				case 25:
					return "A466";
					break;
					
				case 26:
					return "A474";
					break;
				
				case 27:
					return "A482";
					break;
					
				case 28:
					return "A490";
					break;
					
				case 29:
					return "A498";
					break;
					
				case 30:
					return "A506";
					break;
					
				case 31:
					return "A514";
					break;
					
				case 32:
					return "A522";
					break;
					
				default:
					return "A274";
					break;
			}
		}
		
	public function generateprnfile($id, $colorId){
		//$data = DB::select("SELECT a.ArticleNumber, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, a.StyleDescription, iw.Nopacks, c.Colorflag, c.Title FROM `inward` iw inner join article a on a.Id=iw.ArticleId inner join po p on p.ArticleId=a.Id inner join category c on p.CategoryId=c.Id where iw.Id = '".$id."'");
		//$data = DB::select("SELECT a.Id, a.ArticleNumber, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, (case a.ArticleOpenFlag when 1 then (select ia.ArticleColor from inwardarticle ia where ia.ArticleId = a.Id) else 0 end) as ArticleColorData, (case a.ArticleOpenFlag when 1 then (select ia.ArticleSize from inwardarticle ia where ia.ArticleId = a.Id) else 0 end) as ArticleSizeData, a.StyleDescription, iw.Nopacks, c.Colorflag, c.Title FROM `inward` iw inner join article a on a.Id=iw.ArticleId inner join po p on p.ArticleId=a.Id inner join category c on p.CategoryId=c.Id where iw.Id = '".$id."'");
		$data = DB::select("SELECT a.Id, a.ArticleNumber, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, (case a.ArticleOpenFlag when 1 then (select ia.ArticleColor from inwardarticle ia where ia.ArticleId = a.Id group by a.Id) else 0 end) as ArticleColorData, (case a.ArticleOpenFlag when 1 then (select ia.ArticleSize from inwardarticle ia where ia.ArticleId = a.Id group by a.Id) else 0 end) as ArticleSizeData, a.StyleDescription, iw.Nopacks, c.Colorflag, c.Title FROM `inward` iw inner join article a on a.Id=iw.ArticleId left join po p on p.ArticleId=a.Id inner join category c on a.CategoryId=c.Id where iw.Id = '".$id."'");
		if(!empty($data)){
			$ArticleOpenFlag = $data[0]->ArticleOpenFlag;
			$Colorflag = $data[0]->Colorflag;
			$Title = $data[0]->Title;
			$ArticleColor = "";
			$ArticleSize = "";
			$Pieces = "";
			$ArticleRatio = "";
			$TotalArticleRatio =  "";
			if($data[0]->ArticleColor!=""){
				$ArticleRatio = $data[0]->ArticleRatio;
				$getcolor = json_decode($data[0]->ArticleColor);
				$getsize = json_decode($data[0]->ArticleSize);
				$TotalArticleRatio = array_sum(explode(",",$ArticleRatio));
				
				//echo $TotalArticleRatio;
				//exit;
				$countcolor = count($getcolor);
				$countsize = count($getsize);
				
				
				foreach($getcolor as $vl){
					if($colorId!=0){
						if($colorId==$vl->Id){
							$ArticleColor = "";
							$ArticleColor = $vl->Name.", ";
							continue;
						}					
					}else{
						$ArticleColor .= $vl->Name.", ";
					}
				}
				$ArticleColor = rtrim($ArticleColor,', ');
				
				
				foreach($getsize as $vl){
					$ArticleSize .= $vl->Name.", ";
				}
				$ArticleSize = rtrim($ArticleSize,', ');
				
				if($ArticleOpenFlag==0){
					/* if($Colorflag==1){
						
					} */
					if($ArticleRatio!=""){
						//$Pieces = $countcolor * $TotalArticleRatio;
						$Pieces = $TotalArticleRatio;
					}
				}
			}else{
				$Pieces = $ArticleRatio;
				
				if($data[0]->ArticleOpenFlag==1){
					$getcolordata = json_decode($data[0]->ArticleColorData);
					$getsizedata = json_decode($data[0]->ArticleSizeData);
					//$Pieces = $data[0]->Nopacks;
					
					$countcolor = count($getcolordata);
					$countsize = count($getsizedata);
					
					foreach($getcolordata as $vl){
						$ArticleColor .= $vl->Name.", ";
					}
					$ArticleColor = rtrim($ArticleColor,', ');
					
					
					foreach($getsizedata as $vl){
						$ArticleSize .= $vl->Name.", ";
					}
					$ArticleSize = rtrim($ArticleSize,', ');
				}
			}
			
			$articleprn = "";
			$articleprn .= "I8,1,001\r\n";
			$articleprn .= "ZN\r\n";
			$articleprn .= "q779\r\n";
			$articleprn .= "S20\r\n";
			$articleprn .= "O\r\n";
			$articleprn .= "*D5T\r\n";
			$articleprn .= "JF\r\n";
			$articleprn .= "H13\r\n";
			$articleprn .= "ZT\r\n";
			$articleprn .= "Q600,25\r\n";
			$articleprn .= "N\r\n";
			$articleprn .= 'A715,460,2,4,1,1,N,"ARTICLE"'."\r\n";
			$articleprn .= "LO1,406,777,3\r\n";
			$articleprn .= 'A691,240,2,4,1,1,N,"SIZE"'."\r\n";
			$articleprn .= "LO1,335,777,3\r\n";
			$articleprn .= 'A699,172,2,4,1,1,N,"COLOR"'."\r\n";
			$articleprn .= "LO1,191,778,3\r\n";
			$articleprn .= 'A699,109,2,4,1,1,N,"RATIO"'."\r\n";
			$articleprn .= 'A683,48,2,4,1,1,N,"QTY"'."\r\n";
			$articleprn .= "LO538,0,3,600\r\n";
			$articleprn .= "LO1,63,777,3\r\n";
			$articleprn .= $this->createresult($data[0]->ArticleNumber).',460,2,4,1,1,N,"'.$data[0]->ArticleNumber.'"'."\r\n";
			$articleprn .= $this->createresult($ArticleSize).',240,2,4,1,1,N,"'.$ArticleSize.'"'."\r\n";
			$articleprn .= $this->createresult($ArticleColor).',174,2,4,1,1,N,"'.$ArticleColor.'"'."\r\n";
			$articleprn .= $this->createresult($ArticleRatio).',109,2,4,1,1,N,"'.$ArticleRatio.'"'."\r\n";
			$articleprn .= $this->createresult($Pieces).',44,2,4,1,1,N,"'.$Pieces.'"'."\r\n";
			$articleprn .= "LO1,127,778,3\r\n";
			$articleprn .= 'A723,388,2,4,1,1,N,"CATEGORY"'."\r\n";
			$articleprn .= $this->createresult($Title).',388,2,4,1,1,N,"'.$Title.'"'."\r\n";
			$articleprn .= 'B427,575,2,1,3,6,72,N,"'.$data[0]->ArticleNumber.'"'."\r\n";
			$articleprn .= "LO1,478,779,3\r\n";
			$articleprn .= 'A707,312,2,4,1,1,N,"DESCR."'."\r\n";
			$articleprn .= (strlen($data[0]->StyleDescription)!="") ? $this->createresult($data[0]->StyleDescription).',312,2,4,1,1,N,"'.$data[0]->StyleDescription.'"'."\r\n" : '';
			$articleprn .= "LO1,263,777,3\r\n";
			$articleprn .= "W1\r\n";
			$articleprn .= "Print 1\r\n";
			$file = Storage::put( 'colorhunt.prn', $articleprn);
		}
		
	}
	
	public function getstorage($id, $ColorId){
		//echo asset('storage/article.prn');
		//Storage::url('article.prn');
		$this->generateprnfile($id, $ColorId);
		$data = Storage::disk('local')->get('colorhunt.prn');
		return response()->json($data, 200);
		//return response()->json(array("data"=>$data), 200);
		//echo "http://localhost/colorHuntApi/storage/app/article.prn";
	}
	
	
	
	public function generateprnfilesingle($id, $colorId){
		$data = DB::select("SELECT a.Id, a.ArticleNumber, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.ArticleOpenFlag, (case a.ArticleOpenFlag when 1 then (select ia.ArticleColor from inwardarticle ia where ia.ArticleId = a.Id group by a.Id) else 0 end) as ArticleColorData, (case a.ArticleOpenFlag when 1 then (select ia.ArticleSize from inwardarticle ia where ia.ArticleId = a.Id group by a.Id) else 0 end) as ArticleSizeData, a.StyleDescription, iw.Nopacks, c.Colorflag, c.Title FROM `inward` iw inner join article a on a.Id=iw.ArticleId left join po p on p.ArticleId=a.Id inner join category c on a.CategoryId=c.Id where iw.Id = '".$id."'");
		if(!empty($data)){
			$ArticleOpenFlag = $data[0]->ArticleOpenFlag;
			$Colorflag = $data[0]->Colorflag;
			$Title = $data[0]->Title;
			$ArticleColor = "";
			$ArticleSize = "";
			$Pieces = "";
			$ArticleRatio = "";
			$TotalArticleRatio =  "";
			if($data[0]->ArticleColor!=""){
				$ArticleRatio = $data[0]->ArticleRatio;
				$getcolor = json_decode($data[0]->ArticleColor);
				$getsize = json_decode($data[0]->ArticleSize);
				$TotalArticleRatio = array_sum(explode(",",$ArticleRatio));
				
				//echo $TotalArticleRatio;
				//exit;
				$countcolor = count($getcolor);
				$countsize = count($getsize);
				
				
				foreach($getcolor as $vl){
					if($colorId!=0){
						if($colorId==$vl->Id){
							$ArticleColor = "";
							$ArticleColor = $vl->Name.", ";
							continue;
						}					
					}else{
						$ArticleColor .= $vl->Name.", ";
					}
				}
				$ArticleColor = rtrim($ArticleColor,', ');
				
				
				foreach($getsize as $vl){
					$ArticleSize .= $vl->Name.", ";
				}
				$ArticleSize = rtrim($ArticleSize,', ');
				
				if($ArticleOpenFlag==0){
					/* if($Colorflag==1){
						
					} */
					if($ArticleRatio!=""){
						//$Pieces = $countcolor * $TotalArticleRatio;
						$Pieces = $TotalArticleRatio;
					}
				}
			}else{
				$Pieces = $ArticleRatio;
				
				if($data[0]->ArticleOpenFlag==1){
					$getcolordata = json_decode($data[0]->ArticleColorData);
					$getsizedata = json_decode($data[0]->ArticleSizeData);
					//$Pieces = $data[0]->Nopacks;
					
					$countcolor = count($getcolordata);
					$countsize = count($getsizedata);
					
					foreach($getcolordata as $vl){
						$ArticleColor .= $vl->Name.", ";
					}
					$ArticleColor = rtrim($ArticleColor,', ');
					
					
					foreach($getsizedata as $vl){
						$ArticleSize .= $vl->Name.", ";
					}
					$ArticleSize = rtrim($ArticleSize,', ');
				}
			}
			
			$articleprn = "";
			$articleprn .= "I8,1,001\r\n";
			$articleprn .= "ZN\r\n";
			$articleprn .= "q540\r\n";
			$articleprn .= "O\r\n";
			$articleprn .= "*D5T\r\n";
			$articleprn .= "JF\r\n";
			$articleprn .= "H10\r\n";
			$articleprn .= "ZT\r\n";
			$articleprn .= "Q240,25\r\n";
			$articleprn .= "N\r\n";
			$articleprn .= 'B371,202,2,1C,3,6,102,N,"'.$data[0]->ArticleNumber.'"'."\r\n";
			$articleprn .= 'A317,98,2,4,1,1,N,"'.$data[0]->ArticleNumber.'"'."\r\n";
			$articleprn .= 'A357,57,2,4,1,1,N,"COLOR : '.$ArticleColor.'"'."\r\n";
			$articleprn .= "W1\r\n";
			$articleprn .= "Print 1\r\n";
			$file = Storage::put( 'colorhunt.prn', $articleprn);
		}
		
	}
	
	public function getsinglestorage($id, $ColorId){
		$this->generateprnfilesingle($id, $ColorId);
		$data = Storage::disk('local')->get('colorhunt.prn');
		return response()->json($data, 200);
		//return response()->json(array("data"=>$data), 200);
		//echo "http://localhost/colorHuntApi/storage/app/article.prn";
	}
	
	
	public function GeOutletParty($id)
    {
		if($id==0){
			return DB::select("select * from party where OutletAssign=1 order by Name ASC");
		} else{
			return DB::select("select * from party where OutletAssign=1 and Id=".$id." order by Name ASC");
		}
        
    }
    public function UpdateParty(Request $request)
    {
        $data = $request->all();
        Party::where('id', $data['id'])->update(array(
            'Name' => $data['Name'],
            'Address' => $data['Address'],
            'PhoneNumber' => $data['PhoneNumber'],
            'ContactPerson' => $data['ContactPerson'],
            'GSTNumber' => $data['GSTNumber'],
			'GSTType' => $data['GSTType'],
			'Discount' => $data['Discount'],
			'OutletAssign' => $data['OutletAssign'],
			'OutletArticleRate' => $data['OutletArticleRate'],
        ));
        return response()->json("SUCCESS", 200);
    }
    public function Deleteparty($id)
    {
        return DB::table('party')->where('Id', '=', $id)->delete();

    }
    public function GetPartyIdWise($id)
    {
        return DB::select('SELECT * From party WHERE Id = ' . $id . '');
    }
	public function getoutletviewparty($id)
    {
		if($id==0){
			return DB::select("select * from party where OutletAssign=1");
		}else{
			return DB::select('SELECT * From party WHERE Id = ' . $id . '');
		}
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


	public function DeleteArticlePhotos($id)
    {
		$getdata = DB::select('SELECT * From articlephotos WHERE Id = ' . $id . '');
		//echo "<pre>"; print_r($getdata[0]->Name); exit;
		$array = array();
        if($getdata){
			$destinationPath = 'uploads';
			File::delete($destinationPath.'/'.$getdata[0]->Name);
			return DB::table('articlephotos')->where('Id', '=', $id)->delete();
		}else{
			return $array;
		}
    }
	
    public function GetArticlePhotos()
    {
        return DB::select('SELECT ap.*, a.ArticleNumber From articlephotos ap left join article a on a.Id = ap.ArticlesId');
    }
	
    public function ArticlePhotos(Request $request)
    {
        //$field = Vendor::create($request->all());
        $data = $request->all();
        $array = array();
        //echo "<pre>"; print_r($data);  exit;
        //print_r($request->file('myfile')); 
        //exit;
        $images=array();
        if($files=$data['myfile']){
            foreach($files as $file){
				$dataimage = getimagesize($file->getRealPath());
				$width = $dataimage[0];
				$height = $dataimage[1];
				//exit;
				//if($width==600 && $height==800){
					$name=$file->getClientOriginalName();
					$randomstring = $this->generateRandomString();
					$name_extension = explode(".",$name);
					$newname = $randomstring.'.'.$name_extension[1];
					$file->move('uploads',$newname);
					$images[]=$newname;
				/* }else{
					return response()->json(array("NoMatch"=>"true"), 200);
				} */
				
            }
        }
        
        if(count($images)>0){
            foreach($images as $img){
                DB::table('articlephotos')->insert(
                    ['ArticlesId' => $data['ArticleId'], 'Name' => $img, "CreatedDate"=>date("Y-m-d H:i:s")]
                );
            }
            return response()->json(array("result"=>"true"), 200);
        }
        else{
            return response()->json(array("result"=>"false"), 200);
        }
        //echo "<pre>"; print_r($images);
        //echo "Upload";
        //exit;
    }
	
	public function GetDashboard(){
		$array = array();
		$podata = DB::select("select count(*) as Total from (SELECT p.Id, pn.Id as POId, pn.PurchaseNumber, v.Name, c.Title, p.ArticleId, p.NumPacks, ar.ArticleNumber, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd where InwardArticleId IS NULL");
		$sodata = DB::select("select count(*) as Total from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join users u on u.Id=son.UserId group by s.SoNumberId) as ddd where ddd.OWID=0");
		return array("Open_PO"=>$podata[0]->Total, "Open_SO"=>$sodata[0]->Total);
	}
	
	public function FrontSearchResult(Request $request)
    {
        $data = $request->all();
		//echo "<pre>"; print_r($data); echo $data["Name"]; exit;
		//return DB::select("SELECT a.Id, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck,substring_index(GROUP_CONCAT(DISTINCT CONCAT(ap.Name) ORDER BY ap.Id SEPARATOR ','), ',', 2) as Images, a.ArticleNumber, c.Title, ar.ArticleRate FROM `article` a left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId inner join articlerate ar on ar.ArticleId=a.Id left join articlephotos ap on ap.ArticlesId=a.Id inner join inward inw on inw.ArticleId=a.Id where a.ArticleStatus = 1 and a.ArticleNumber LIKE '%".$data["Name"]."%' group by a.Id HAVING SalesNoPacksCheck = 0");
		return DB::select("SELECT a.Id, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck,substring_index(GROUP_CONCAT(DISTINCT CONCAT(ap.Name) ORDER BY ap.Id SEPARATOR ','), ',', 2) as Images, a.ArticleNumber, c.Title, ar.ArticleRate FROM `article` a left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId inner join articlerate ar on ar.ArticleId=a.Id inner join articlephotos ap on ap.ArticlesId=a.Id inner join inward inw on inw.ArticleId=a.Id where a.ArticleStatus = 1 and a.ArticleNumber LIKE '%".$data["Name"]."%' group by a.Id HAVING SalesNoPacksCheck = 0");
	}
	
	public function CartNopacksCheck(Request $request)
	{
		$data = $request->all();
		$dataresult= DB::select('SELECT c.Colorflag FROM `article` a inner join category c on c.Id=a.CategoryId where a.Id="'.$data['ArticleId'].'"'); 
		$Colorflag = $dataresult[0]->Colorflag;
		
		
		//echo $Amount; exit;
		if($data["ArticleOpenFlag"]==1){
			$mixnopacks = DB::select('SELECT * FROM `mixnopacks` where ArticleId="'.$data['ArticleId'].'"'); 
			//gmp_sign(-5003450);
			if(isset($data['NoPacksNew'])){
				$NoPacks = $data['NoPacksNew'];
				if($mixnopacks[0]->NoPacks<$data['NoPacksNew']){
					return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true"), 200);
				}
				//$SalesNoPacks = ($mixnopacks[0]->NoPacks - $data['NoPacksNew']);
				//return response()->json(array("RequiredSet"=>$NoPacks), 200);
			}else{
				return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
			}
		}else{
			$ArticleSelectedColor = json_decode($data['ArticleSelectedColor']);
			$datanopacks= DB::select('SELECT SalesNoPacks FROM `inward` where ArticleId="'.$data['ArticleId'].'"');
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
				foreach($ArticleSelectedColor as $key => $vl){
					$numberofpacks = $vl->Id;
					if($data["NoPacksNew_".$numberofpacks]!=""){
						if($stringcomma==1){
							if($string[$key]<$data["NoPacksNew_".$numberofpacks]){
								return response()->json(array("NoOfSetNotMatch"=>"true"), 200);
							}
							
							$SalesNoPacks .= ($string[$key] - $data["NoPacksNew_".$numberofpacks]).",";
						}else{
							if($search<$data["NoPacksNew_".$numberofpacks]){
								return response()->json(array("NoOfSetNotMatch"=>"true"), 200);
							}
							
							$SalesNoPacks .= ($search - $data["NoPacksNew_".$numberofpacks]).",";
						}
						$NoPacks .= $data["NoPacksNew_".$numberofpacks].",";
							
						
					}
					else{
						$NoPacks .= "0,";
						$SalesNoPacks .= $search.",";
					}
				}
			} else{
				if(isset($data['NoPacksNew'])){
					$NoPacks .= $data['NoPacksNew'];
					if($search<$data['NoPacksNew']){
						return response()->json(array("NoOfSetNotMatch"=>"true"), 200);
					}
					$SalesNoPacks .= ($search - $data['NoPacksNew']);
				}else{
					return response()->json(array("ZeroNotAllow"=>"true"), 200);
				}
			}
			
			$NoPacks = rtrim($NoPacks,',');
			$SalesNoPacks = rtrim($SalesNoPacks,',');
			$CheckSalesNoPacks = explode(',', $NoPacks);
			
			$tmp = array_filter($CheckSalesNoPacks);
			if (empty($tmp)) {
				//echo "All zeros!";
				return response()->json(array("ZeroNotAllow"=>"true"), 200);
			}
			
			/* echo "Colorflag: ".$Colorflag."\n";
			echo "ArticleOpenFlag: ".$data["ArticleOpenFlag"]."\n";
			echo "<pre>"; print_r($data['ArticleSelectedColor'])."\n";
			echo "ArticleRatio: ".$data['ArticleRatio']."\n";
			echo "ArticleRate: ".$data['ArticleRate']."\n"; 
			 */
			
		}
		
		
		if($Colorflag==0){
				if($data["ArticleOpenFlag"]==0){
					$task_array = json_decode($data['ArticleSelectedColor']);
					$countcolor = count($task_array);
					$ArticleRatio = $data['ArticleRatio'];
					if( strpos($ArticleRatio, ',') !== false ) {
						$TotalArticleRatio = array_sum(explode(",",$ArticleRatio));
					}else{
						$TotalArticleRatio = $ArticleRatio;
					}
						//$QuantityPic = $NoPacks * $countcolor * $TotalArticleRatio;
						$QuantityPic = $NoPacks;
				}else{
					$QuantityPic = $NoPacks;
				}
			} else{
				if($data["ArticleOpenFlag"]==0){
					$ArticleRatio = $data['ArticleRatio'];
					$TotalArticleRatio = array_sum(explode(",",$ArticleRatio));
					$countNoSet = array_sum(explode(",",$NoPacks));
					$QuantityPic = $countNoSet;
				}else{
					$QuantityPic = $NoPacks;
				}
			}
			$Amount = $QuantityPic * $data['ArticleRate'];
			//echo "<pre>"; print_r($data); echo "Amount: ".$Amount; exit;
			//return $NoPacks;
			return response()->json(array("RequiredSet"=>$NoPacks, "TotalNoPacks"=>$QuantityPic, "Amount"=>$Amount), 200);	 
			
			
			
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
    }
	
	public function cartplaceorder(Request $request)
	{
		$data = $request->all();
		//echo "<pre>"; print_r($data); exit;
		$soDate =  $data["Carddata"]["Date"];
		$soPartyId = $data["Carddata"]["PartyId"];
		$soDestination = $data["Carddata"]["Destination"];
		$soRemarks = $data["Carddata"]["Remarks"];
		$soTransporter = $data["Carddata"]["Transporter"];
		$soGST = $data["Carddata"]["GST"];
		$soGSTType = $data["Carddata"]["GSTType"];
		$soGSTPercentage = $data["Carddata"]["GST_Percentage"];
		$UserId = $data["UserId"];
		DB::beginTransaction();
		try { 
			$generate_SONUMBER = $this->GenerateSoNumber($UserId);
			$SO_Number = $generate_SONUMBER['SO_Number'];
			$SO_Number_Financial_Id = $generate_SONUMBER['SO_Number_Financial_Id'];
			//$generate_SONUMBER['SO_Number'];
			//$generate_SONUMBER['SO_Number_Financial'];
			//$generate_SONUMBER['SO_Number_Financial_Id'];
			$SoNumberId = DB::table('sonumber')->insertGetId(
				['SoNumber' =>  $SO_Number,"FinancialYearId"=>$SO_Number_Financial_Id,'UserId'=>$UserId, 'PartyId' => $soPartyId, 'SoDate'=>$soDate, 'Destination'=>$soDestination,'Transporter'=>$soTransporter,'Remarks'=>$soRemarks, 'OrderView'=>'Frontend', 'GSTAmount'=>$soGST, 'GSTPercentage'=>$soGSTPercentage, 'GSTType'=>$soGSTType, 'CreatedDate' => date('Y-m-d H:i:s')]
			);
			
			foreach($data["ArticleData"] as $key => $val){
					
					/* $val["ArticleData"][0]["Images"];
					$val["ArticleData"][0]["ArticleNumber"];
					$val["ArticleData"][0]["Title"];
					
					$val["ArticleData"][0]["ArticleSize"];
					$val["ArticleData"][0]["ArticleRatio"];
					$val["ArticleData"][0]["SalesNoPacks"]; */
					$ArticleId = $val["ArticleData"][0]["Id"];
					$ArticleNumber = $val["ArticleData"][0]["ArticleNumber"];
					$ArticleRate = $val["ArticleData"][0]["ArticleRate"]; 
					
					//$Colorflag = $val["ArticleData"][0]["Colorflag"];
					$ArticleOpenFlag = $val["ArticleData"][0]["ArticleOpenFlag"];
					
					if($ArticleOpenFlag==1){
						$mixnopacks = DB::select('SELECT * FROM `mixnopacks` where ArticleId="'.$ArticleId.'"');
						$NoPacks = "";
						$SalesNoPacks = "";
						if(isset($val["Carddata"]['NoPacksNew'])){
							$NoPacks .= $val["Carddata"]['NoPacksNew'];
							if($mixnopacks[0]->NoPacks<$val["Carddata"]['NoPacksNew']){
								return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true", "ArticleNumber"=>$ArticleNumber), 200);
							}
							$SalesNoPacks .= ($mixnopacks[0]->NoPacks - $val["Carddata"]['NoPacksNew']);
						}else{
							return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
						}
						
						$sonumberdata = DB::select('SELECT count(*) as total, NoPacks  FROM `so` where SoNumberId="'.$SoNumberId.'" and ArticleId="'.$ArticleId.'"');
						$getnppacks = $sonumberdata[0]->NoPacks;
						
						
						$updated = DB::table('mixnopacks')
							->where('ArticleId', $ArticleId)
							->update(['NoPacks' => $SalesNoPacks]);
							
							
						if($sonumberdata[0]->total>0){
							$nopacksadded = $getnppacks + $NoPacks;
							
							DB::table('so')
								->where('SoNumberId', $SoNumberId)
								->where('ArticleId', $ArticleId)
								->update(['NoPacks'=>$nopacksadded, 'OutwardNoPacks'=>$nopacksadded, 'ArticleRate'=>$ArticleRate]);
						}else{
						   $soadd['SoNumberId'] = $SoNumberId;
						   $soadd["ArticleId"] = $ArticleId;
						   $soadd["NoPacks"] = $NoPacks;
						   $soadd["OutwardNoPacks"] = $NoPacks;
						   $soadd["ArticleRate"] = $ArticleRate;
						   SO::create($soadd);
						   
						   /* DB::table('so')->insertGetId(
								['SoNumber' =>  $SoNumberId,'ArticleId'=>$ArticleId, 'NoPacks' => $NoPacks, 'OutwardNoPacks'=>$OutwardNoPacks, 'ArticleRate'=>$ArticleRate, 'created_at' => date('Y-m-d H:i:s'), 'updated_at'=> date('Y-m-d H:i:s')]
							); */
						}
				
					}else{
						$ArticleColor = json_decode($val["ArticleData"][0]["ArticleColor"]);
						$soadd = array();
				
						$dataresult= DB::select('SELECT c.Colorflag FROM `article` a inner join category c on c.Id=a.CategoryId where a.Id="'.$ArticleId.'"'); 
						$Colorflag = $dataresult[0]->Colorflag;
						
						$datanopacks= DB::select('SELECT SalesNoPacks FROM `inward` where ArticleId="'.$ArticleId.'"');
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
							foreach($ArticleColor as $key => $vl){
								$numberofpacks = $vl->Id;
								if($val["Carddata"]["NoPacksNew_".$numberofpacks]!=""){
									if($stringcomma==1){
										if($string[$key]<$val["Carddata"]["NoPacksNew_".$numberofpacks]){
											return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true", "ArticleNumber"=>$ArticleNumber), 200);
										}
										
										$SalesNoPacks .= ($string[$key] - $val["Carddata"]["NoPacksNew_".$numberofpacks]).",";
									}else{
										if($search<$val["Carddata"]["NoPacksNew_".$numberofpacks]){
											return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true", "ArticleNumber"=>$ArticleNumber), 200);
										}
										
										$SalesNoPacks .= ($search - $val["Carddata"]["NoPacksNew_".$numberofpacks]).",";
									}
									$NoPacks .= $val["Carddata"]["NoPacksNew_".$numberofpacks].",";
										
									
								}
								else{
									$NoPacks .= "0,";
									$SalesNoPacks .= $string[$key].",";
								}
								
							}
							
							
						} else{
							if(isset($val["Carddata"]['NoPacksNew'])){
								$NoPacks .= $val["Carddata"]['NoPacksNew'];
								if($search<$val["Carddata"]['NoPacksNew']){
									return response()->json(array("id"=>"", "NoOfSetNotMatch"=>"true", "ArticleNumber"=>$ArticleNumber), 200);
								}
								$SalesNoPacks .= ($search - $val["Carddata"]['NoPacksNew']);
							}else{
								return response()->json(array("id"=>"", "ZeroNotAllow"=>"true"), 200);
							}
						}
						
						$NoPacks = rtrim($NoPacks,',');
						$SalesNoPacks = rtrim($SalesNoPacks,',');
						
						
						$sonumberdata = DB::select('SELECT count(*) as total, NoPacks  FROM `so` where SoNumberId="'.$SoNumberId.'" and ArticleId="'.$ArticleId.'"');
						$getnppacks = $sonumberdata[0]->NoPacks;
						
						//echo "<pre>"; 
						//print_r($nopacksadded);
						//print_r($sonumberdata);
						//echo $SalesNoPacks."\n\n"; 
						//echo $NoPacks."\n\n";
						//echo "<pre>"; print_r($data); exit;
						
						$updated = DB::table('inward')
							->where('ArticleId', $ArticleId)
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
								->where('ArticleId', $ArticleId)
								->update(['NoPacks'=>$nopacksadded, 'OutwardNoPacks'=>$nopacksadded, 'ArticleRate'=>$ArticleRate]);
						}else{
							$soadd['SoNumberId'] = $SoNumberId;
						   $soadd["ArticleId"] = $ArticleId;
						   $soadd["NoPacks"] = $NoPacks;
						   $soadd["OutwardNoPacks"] = $NoPacks;
						   $soadd["ArticleRate"] = $ArticleRate;
						   SO::create($soadd);
						}
					}
					//echo "<pre>"; print_r($val["ArticleData"]);
					//echo "<pre>"; print_r($val);
			}
			DB::commit();
			return response()->json(array("SONO"=>$SoNumberId, "Result"=>"SUCCESS"), 200);
		}catch (\Exception $e) {
			DB::rollback();
			
			return response()->json("", 200);
		}
		//echo "<pre>"; print_r($data); exit;
	}
	
	public function AddRackNew(Request $request)
    {
        $data = $request->all();
		return print_r($data); exit;
		
	}
	public function AddProductLaunch(Request $request)
	{
		//$data = $request->all();
		//return response()->json($data); exit;
		//return $data; exit;
		/* if($data['Status']){
			DB::beginTransaction();
			try {
				DB::table('article')
					->where('Id', $data['ArticleId'])
					->update(['ArticleStatus' => $data['Status']]);
				
				DB::commit();
				return response()->json("SUCCESS", 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json("", 200);
			}
		} */
	}
	
	public function store1111(Request $request)
    {
        $data = $request->all();
		return	
		//var_dump($data);
		print_r($data); 
		exit;
		$pldata = DB::select("SELECT count(*) as total FROM `productlaunch` where ArticleId='".$data["ArticleId"]["Id"]."'");
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
					
					DB::table('productlaunch')
					->where('ArticleId', $data_new["ArticleId"])
					->update(['ProductStatus' => $data_new['ProductStatus']]);
					
				} else{
					Productlaunch::create($data_new);
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
					Productlaunch::create($data_new);
				}else{
					return print_r($data); exit;
					/* DB::table('Productlaunch')
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
    }
	
	
	//SELECT * FROM `sonumber` sn inner join outwardnumber own on own.SoId = sn.Id inner join party p on p.Id=sn.PartyId where p.OutletAssign=1 and p.Id=4
	//SELECT * FROM `sonumber` sn inner join outwardnumber own on own.SoId = sn.Id inner join party p on p.Id=sn.PartyId left join outward ow on ow.OutwardNumberId=own.OutwardNumber where p.OutletAssign=1 and p.Id=4
	//SELECT ow.Id, ow.OutwardNumberId, ow.ArticleId, ow.NoPacks, ow.OutwardRate FROM `sonumber` sn inner join outwardnumber own on own.SoId = sn.Id inner join party p on p.Id=sn.PartyId left join outward ow on ow.OutwardNumberId=own.OutwardNumber where p.OutletAssign=1 and p.Id=4
	//https://www.periscopedata.com/blog/splitting-comma-separated-values-in-mysql
	//SELECT ow.Id, ow.OutwardNumberId, ow.ArticleId, substring_index(substring_index(ow.NoPacks, ',', 1), ',', -1) as email , ow.NoPacks, ow.OutwardRate FROM `sonumber` sn inner join outwardnumber own on own.SoId = sn.Id inner join party p on p.Id=sn.PartyId left join outward ow on ow.OutwardNumberId=own.OutwardNumber where p.OutletAssign=1

	//SELECT outward.ArticleId, SUBSTRING_INDEX(SUBSTRING_INDEX(outward.NoPacks, ',', numbers.n), ',', -1) NoPacks FROM (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12) numbers INNER JOIN outward ON CHAR_LENGTH(outward.NoPacks) -CHAR_LENGTH(REPLACE(outward.NoPacks, ',', ''))>=numbers.n-1 ORDER BY id, n

	//select ArticleId,id,NoPacks,cast(NoPacks as int) from ( SELECT outward.ArticleId, n as id, SUBSTRING_INDEX(SUBSTRING_INDEX(outward.NoPacks, ',', numbers.n), ',', -1) NoPacks FROM (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12) numbers INNER JOIN outward ON CHAR_LENGTH(outward.NoPacks) -CHAR_LENGTH(REPLACE(outward.NoPacks, ',', ''))>=numbers.n-1 ORDER BY id, n )t order by ArticleId
	
	//select ArticleId,id, dd, NoPacks,sum(cast(NoPacks as int)), cast(NoPacks as int) from ( SELECT outward.ArticleId, n as id, numbers.n as dd, SUBSTRING_INDEX(SUBSTRING_INDEX(outward.NoPacks, ',', numbers.n), ',', -1) NoPacks FROM (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12) numbers INNER JOIN outward ON CHAR_LENGTH(outward.NoPacks) -CHAR_LENGTH(REPLACE(outward.NoPacks, ',', ''))>=numbers.n-1 ORDER BY id, n )t group by ArticleId, id order by ArticleId
	//select ArticleId,sum(cast(NoPacks as int)) as TotalNoPacks from ( SELECT outward.ArticleId, n as id, numbers.n as dd, SUBSTRING_INDEX(SUBSTRING_INDEX(outward.NoPacks, ',', numbers.n), ',', -1) NoPacks FROM (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12) numbers INNER JOIN outward ON CHAR_LENGTH(outward.NoPacks) -CHAR_LENGTH(REPLACE(outward.NoPacks, ',', ''))>=numbers.n-1 ORDER BY id, n )t group by ArticleId, id order by ArticleId
	//Select ArticleId,sum(cast(NoPacks as int)) as TotalNoPacks from ( SELECT outward.ArticleId, n as id, numbers.n as dd, SUBSTRING_INDEX(SUBSTRING_INDEX(outward.NoPacks, ',', numbers.n), ',', -1) NoPacks FROM (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24 UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL SELECT 30 UNION ALL SELECT 31 UNION ALL SELECT 32 UNION ALL SELECT 33 UNION ALL SELECT 34 UNION ALL SELECT 35 UNION ALL SELECT 36 UNION ALL SELECT 37 UNION ALL SELECT 38 UNION ALL SELECT 39 UNION ALL SELECT 40) numbers INNER JOIN outward ON CHAR_LENGTH(outward.NoPacks) -CHAR_LENGTH(REPLACE(outward.NoPacks, ',', ''))>=numbers.n-1 ORDER BY id, n )t group by ArticleId, id order by ArticleId
	
	//select * from (select ArticleId,sum(cast(NoPacks as int)) as TotalNoPacks from ( SELECT outward.ArticleId, n as id, numbers.n as dd, SUBSTRING_INDEX(SUBSTRING_INDEX(outward.NoPacks, ',', numbers.n), ',', -1) NoPacks FROM (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12) numbers INNER JOIN outward ON CHAR_LENGTH(outward.NoPacks) -CHAR_LENGTH(REPLACE(outward.NoPacks, ',', ''))>=numbers.n-1 ORDER BY id, n ) t group by ArticleId, id order by ArticleId) as ddd right join `articlecolor` ac on ac.ArticleId = ddd.ArticleId where ac.ArticleId in (3,5,6,15,16)

	//SELECT p.Id, p.ArticleId, ac.ArticleColorName, c.Colorflag FROM `articlecolor` ac inner join `po` p on p.ArticleId=ac.ArticleId inner join article a on a.Id=p.ArticleId inner join category c on c.Id=p.CategoryId where ac.ArticleId in (3,5,6,15,16)

	//select *, GROUP_CONCAT(CONCAT(TotalNoPacks) SEPARATOR ',') as TotalNoPacks1 from (Select ArticleId, sum(cast(NoPacks as int)) as TotalNoPacks from ( SELECT outward.ArticleId, n as id, numbers.n as dd, SUBSTRING_INDEX(SUBSTRING_INDEX(outward.NoPacks, ',', numbers.n), ',', -1) NoPacks FROM (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24 UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL SELECT 30 UNION ALL SELECT 31 UNION ALL SELECT 32 UNION ALL SELECT 33 UNION ALL SELECT 34 UNION ALL SELECT 35 UNION ALL SELECT 36 UNION ALL SELECT 37 UNION ALL SELECT 38 UNION ALL SELECT 39 UNION ALL SELECT 40) numbers INNER JOIN outward ON CHAR_LENGTH(outward.NoPacks) -CHAR_LENGTH(REPLACE(outward.NoPacks, ',', ''))>=numbers.n-1 ORDER BY id, n )t group by ArticleId, id order by ArticleId) as ff group by ff.ArticleId

	//select ArticleId, GROUP_CONCAT(CONCAT(TotalNoPacks) SEPARATOR ',') as TotalNoPacks from (Select ArticleId, sum(cast(NoPacks as int)) as TotalNoPacks from ( SELECT outward.ArticleId, n as id, numbers.n as dd, SUBSTRING_INDEX(SUBSTRING_INDEX(outward.NoPacks, ',', numbers.n), ',', -1) NoPacks FROM (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24 UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL SELECT 30 UNION ALL SELECT 31 UNION ALL SELECT 32 UNION ALL SELECT 33 UNION ALL SELECT 34 UNION ALL SELECT 35 UNION ALL SELECT 36 UNION ALL SELECT 37 UNION ALL SELECT 38 UNION ALL SELECT 39 UNION ALL SELECT 40) numbers INNER JOIN outward ON CHAR_LENGTH(outward.NoPacks) -CHAR_LENGTH(REPLACE(outward.NoPacks, ',', ''))>=numbers.n-1 ORDER BY id, n )t group by ArticleId, id order by ArticleId) as ff group by ff.ArticleId

	
	//select ddd.*, ac.ArticleColorName from (SELECT owp.ArticleId,  owp.NoPacks, owp.ColorId FROM `outwardpacks` owp where owp.OutwardId = 15 group by owp.ColorId) as ddd inner join articlecolor ac on ac.ArticleId=ddd.ArticleId where ac.ArticleColorId=(ddd.ColorId)
		
	public function videowatermark(){	
		FFMpeg::fromDisk('videos')
		->open('test.mp4')
		->addFilter(function ($filters) {
			$filters->resize(new \FFMpeg\Coordinate\Dimension(640, 480));
		})
		->export()
		->toDisk('converted_videos')
		->inFormat(new \FFMpeg\Format\Video\X264)
		->save('small_steve.mkv');	
	}
	
	public function AddStudent(Request $request)
    {
        $data = $request->all();
		DB::table('student')->insertGetId(
					['FirstName' =>  $data['FirstName'], 'LastName' => $data['LastName'], 'Gender' => $data['Gender'], 'MobileNumber' => $data['MobileNumber'], 'Email' => $data['Email']]
				);
		return response()->json("SUCCESS", 200);
	}
	
	public function UpdateStudent(Request $request)
    {
        $data = $request->all();
		
		DB::table('student')
				->where('Id', $data['Id'])
				->update(['FirstName' => $data['FirstName'], 'LastName' => $data['LastName'], 'Gender' => $data['Gender'], 'MobileNumber' => $data['MobileNumber'], 'Email' => $data['Email']]);
		return response()->json("SUCCESS", 200);
	}
	
	public function DeleteStudent($id){
		DB::table('student')
					->where('Id', '=', $id)
					->delete();
		return response()->json("SUCCESS", 200);
	}
	
	public function GetStudent(){
		return DB::select("select * from student");
	}
	
	public function GetStudentIdWise($id){
		return DB::select("select * from student where Id='".$id."'");
	}
	
	public function articlecolorcheck(Request $request){
		$ArticleName = $request->ArticleName;
		
		$colorflag = DB::select("SELECT c.Colorflag, a.Id, a.ArticleColor FROM `article` a left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId WHERE a.`ArticleNumber` = '".$ArticleName."'");
		$colorflag_data = 0;
		$color = '';
		$color1 = '';
		if($colorflag){
			$article_exist = 1;
			
			$colorflag_data = $colorflag[0]->Colorflag;
			if($colorflag_data==1){
				
				$inwardcheck = DB::select("SELECT * FROM `inward` where ArticleId='".$colorflag[0]->Id."'");
				
				if($inwardcheck){
					$ArticleColor = json_decode($colorflag[0]->ArticleColor, true);
					$color1 = [];
					foreach($ArticleColor as $key => $vl){
						$color1[] = array("ArticleColorId"=>$vl["Id"], "ArticleColorName"=>$vl["Name"]);
					}
				} else{
					$article_exist = 2;
				}
				
				//return $color1;
				//$color = DB::select("SELECT ArticleColorId, ArticleColorName FROM `articlecolor` where ArticleId=".$colorflag[0]->Id);
				//return $color;
			}
		}else{
			$article_exist = 0;
		}
		
		
		
		return response()->json(array("ArticleExist"=>$article_exist, "colorflag"=>$colorflag_data, "color"=>$color1), 200);
	}
	
	function date_sort($a, $b) {
		return strtotime($a->date) - strtotime($b->date);
	}
	
	function comparator($object1, $object2) {
		return strtotime($object1->date) > strtotime($object2->date);
	}
	
	public function articlesearch(Request $request){
		$ArticleName = $request->ArticleName;
		$ColorId = $request->ColorId;
		$UserId = $request->UserId;
		$getuserrole = DB::select("select Role from users where Id=".$UserId);
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
					
					
					//usort($history_newarray, "date_sort");
					//usort($history_newarray, 'comparator');
					
					//return $grandtotaloutwardquantity; exit;
					
/* $arr = array('11/01/2012', '01/01/2014', '01/01/2015', '09/02/2013', '01/01/2013');    
function date_sort($a, $b) {
    return strtotime($a) - strtotime($b);
}
usort($arr, "date_sort");
print_r($arr); */

/* function date_sort($a['date'], $b['date']) {
    return strtotime($a) - strtotime($b);
}
usort($history_newarray, "date_sort"); */

/* function date_sort($a, $b) {
	return strtotime($a['date']) - strtotime($b['date']);
}
usort($historyofsale, "date_sort"); */
//print_r($arr);
					//return $historyofsale; 
					//return $innersalesclosingquantity;
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
		$data  = array("ArticleExist"=>$article_exist, "grandtotalinwardquantity"=>$grandtotalinwardquantity, "grandtotaloutwardquantity"=>$grandtotaloutwardquantity, "PurchaseOrder"=>$Podata,  "InwardData"=>array("InwardExist"=>$inward_exist,"InwardOrder" => $inwarddata, "NoPcks" => $ColorNoPacks, "ArticleSizeSet"=>$ArticleSizeSet, "Colorflag"=>$Colorflag, "ArticleColorSet"=>$ArticleColorSet), "SalesOrderHistory"=>array("SalesExist"=>$sales_exist,"TotalStock"=>$total_stock, "UserRoleFlag"=>$UserRoleFlag, "TotalOutwardQuantity" => $totaloutwardquantity, "SalesOrderHistory"=>$history_newarray), "ArticleRejected"=>$articleRejected, "ArticleRejExist"=>$articlerej_exist, "ArticleCancelled"=>$articleCancelled, "ArticleCanExist"=>$articlecan_exist);
		return $data;
	}
	
	
	function date_compare($element1, $element2) { 
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
					
	
	///Subcategory Module ///
    public function AddSubcategory(Request $request)
    {
        $data = $request->all();
		$field = Subcategory::create($request->all());
        return response()->json($field, 201);
		/* $dataresult= DB::select('SELECT * FROM `subcategory` WHERE `Name` LIKE "'.$data['Name'].'"'); 
        if($dataresult){
            return response()->json('allreadyexits', 201);
        }else{
            $field = Subcategory::create($request->all());
            return response()->json($field, 201);
        } */
    }

    public function GetSubcategory()
    {
        return Subcategory::all();
    }
	
	public function PostSubcatgegory(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
	    $vnddataTotal = DB::select("SELECT count(*) as Total From (SELECT c.Title as CategoryName, sc.Name, sc.Id From subcategory sc left join category c on c.Id=sc.CategoryId) as d");
		$length = $data["length"];
			
		if($search['value'] != null && strlen($search['value']) > 2){
			$searchstring = "WHERE d.CategoryName like '%".$search['value']."%' OR d.Name like '%".$search['value']."%'";
			$vnddataTotalFilter = DB::select("SELECT count(*) as Total From (SELECT c.Title as CategoryName, sc.Name, sc.Id From subcategory sc left join category c on c.Id=sc.CategoryId) as d ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "";
			$vnddataTotalFilterValue = $vnddataTotal[0]->Total;
		}
		
		
		$column = $data["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "d.CategoryName";
				break;
			case 2:
				$ordercolumn = "d.Name";
				break;
			default:
				$ordercolumn = "d.Name";
				break;
		}
		
		$order = "";	
		if($data["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data["order"][0]["dir"];
		}
		
		$vnddata = DB::select("select d.* from (SELECT c.Title as CategoryName, sc.Name, sc.Id From subcategory sc left join category c on c.Id=sc.CategoryId) as d ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		return array(
				'datadraw'=>$data["draw"],
				'recordsTotal'=>$vnddataTotal[0]->Total,
				'recordsFiltered'=>$vnddataTotalFilterValue,
				'response' => 'success',
				'startnumber' => $startnumber,
				'search'=>count($vnddata),
				'data' => $vnddata,
			);
	}
    public function UpdateSubcategory(Request $request)
    {

        $data = $request->all();
        Subcategory::where('id', $data['id'])->update(array(
            'Name' => $data['Name'],
			'CategoryId' => $data['CategoryId'],
        ));
        return response()->json("SUCCESS", 200);
    }
    public function DeleteSubcategory($id)
    {
        return DB::table('subcategory')->where('Id', '=', $id)->delete();
    }
	
	public function GetSubcategoryIdWise($id)
    {
        return DB::select('SELECT * From subcategory WHERE Id = ' . $id . '');
    }
	
	public function GetcategoryIdWise($id)
    {
        return DB::select('SELECT Id, Name FROM `subcategory` where CategoryId = ' . $id . '');
    }
	
	
	//Range series Module //
	public function AddRangeseries(Request $request)
    {
        $data = $request->all();
		$dataresult= DB::select('SELECT * FROM `rangeseries` WHERE `Series` LIKE "'.$data['Series'].'"'); 
        if($dataresult){
            return response()->json('allreadyexits', 201);
        }else{
            $field = Rangeseries::create($request->all());
            return response()->json($field, 201);
        }
    }

    public function GetRangeseries()
    {
        return Rangeseries::all();
    }
	
	public function Postrangeseries(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
	    $vnddataTotal = DB::select("select count(*) as Total from (SELECT c.Title as CategoryName, sc.Name as SubCategory, r.SeriesName, r.Series, r.Id From rangeseries r left join subcategory sc on sc.Id=r.SubCategoryId left join category c on c.Id=sc.CategoryId) as d");
		$length = $data["length"];
			
		if($search['value'] != null && strlen($search['value']) > 2){
			$searchstring = "WHERE d.SubCategory like '%".$search['value']."%' OR d.CategoryName like '%".$search['value']."%' OR d.SeriesName like '%".$search['value']."%' OR d.Series like '%".$search['value']."%'";
			$vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT c.Title as CategoryName, sc.Name as SubCategory, r.SeriesName, r.Series, r.Id From rangeseries r left join subcategory sc on sc.Id=r.SubCategoryId left join category c on c.Id=sc.CategoryId) as d ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "";
			$vnddataTotalFilterValue = $vnddataTotal[0]->Total;
		}
		
		
		$column = $data["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "d.CategoryName";
				break;
			case 2:
				$ordercolumn = "d.SubCategory";
				break;	
			case 3:
				$ordercolumn = "d.SeriesName";
				break;	
			case 5:
				$ordercolumn = "d.Series";
				break;
			default:
				$ordercolumn = "d.Id";
				break;
		}
		
		$order = "";	
		if($data["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data["order"][0]["dir"];
		}
		
		$vnddata = DB::select("select d.* from (SELECT c.Title as CategoryName, sc.Name as SubCategory, r.SeriesName, r.Series, r.Id From rangeseries r left join subcategory sc on sc.Id=r.SubCategoryId left join category c on c.Id=sc.CategoryId) as d ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		return array(
				'datadraw'=>$data["draw"],
				'recordsTotal'=>$vnddataTotal[0]->Total,
				'recordsFiltered'=>$vnddataTotalFilterValue,
				'response' => 'success',
				'startnumber' => $startnumber,
				'search'=>count($vnddata),
				'data' => $vnddata,
			);
	}
    public function UpdateRangeseries(Request $request)
    {
        $data = $request->all();
        Rangeseries::where('id', $data['id'])->update(array(
            'CategoryId' => $data['CategoryId'],
			'SubCategoryId' => $data['SubCategoryId'],
			'SeriesName' => $data['SeriesName'],
			'Series' => $data['Series'],
        ));
        return response()->json("SUCCESS", 200);
    }
    public function DeleteRangeseries($id)
    {
        return DB::table('rangeseries')->where('Id', '=', $id)->delete();
    }
	
	public function GetRangeseriesIdWise($id)
    {
        return DB::select('SELECT * From rangeseries WHERE Id = ' . $id . '');
    }
	public function getrangeseriesarticle($catId, $subcatId){
		return DB::select("SELECT * FROM `rangeseries` where CategoryId = '".$catId."' and SubCategoryId= ".$subcatId);
	}
	
	
	
	//Article Module//
	
    public function AddArticle(Request $request)
    {
        $data = $request->all();
		$dataresult= DB::select('SELECT * FROM `article` WHERE `ArticleNumber` LIKE "'.$data['ArticleNumber'].'"'); 
        if($dataresult){
            return response()->json('allreadyexits', 201);
        }else{
			$cat = DB::select('SELECT ArticleOpenFlag FROM `category` WHERE `Id`="'.$data['CategoryId'].'"');
			$data["ArticleOpenFlag"] = $cat[0]->ArticleOpenFlag;
			if($cat[0]->ArticleOpenFlag==1){
				$data["ArticleStatus"] = 1;
			}
            $field = Article::create($data);
            return response()->json($field, 201);
        }
    }

    public function GetArticle()
    {
        return Article::all();
    }
	
	public function Postarticle(Request $request)
	{
		//echo "asd"; exit;
		$data = $request->all();	
		$search = $data["search"];
		$startnumber = $data["start"];
		
	    $vnddataTotal = DB::select("SELECT count(*) as Total From article");
		$length = $data["length"];
			
		if($search['value'] != null && strlen($search['value']) > 2){
			$searchstring = "WHERE ArticleNumber like '%".$search['value']."%'";
			$vnddataTotalFilter = DB::select("SELECT count(*) as Total From article ".$searchstring);
			$vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
		}else{
			$searchstring = "";
			$vnddataTotalFilterValue = $vnddataTotal[0]->Total;
		}
		
		$column = $data["order"][0]["column"];
		switch ($column) {
			case 1:
				$ordercolumn = "Id";
				break;	
			case 2:
				$ordercolumn = "CategoryName";
				break;
			case 3:
				$ordercolumn = "SubCategory";
				break;
			case 4:
				$ordercolumn = "Series";
				break;
			default:
				$ordercolumn = "Id";
				break;
		}
		
		$order = "";	
		
		if($data["order"][0]["dir"]){
			$order = "order by ".$ordercolumn." ".$data["order"][0]["dir"];
		}
		
		$vnddata = DB::select("SELECT a.ArticleNumber, a.StyleDescription, c.Title as CategoryName, sc.Name as SubCategory, b.Name as BrandName, r.Series, a.Id From article a left join category c on c.Id=a.CategoryId left join rangeseries r on r.Id=a.SeriesId left join subcategory sc on sc.Id=r.SubCategoryId left join brand b on b.Id=a.BrandId ".$searchstring." ".$order." limit ".$data["start"].",".$length);
		return array(
				'datadraw'=>$data["draw"],
				'recordsTotal'=>$vnddataTotal[0]->Total,
				'recordsFiltered'=>$vnddataTotalFilterValue,
				'response' => 'success',
				'startnumber' => $startnumber,
				'search'=>count($vnddata),
				'data' => $vnddata,
			);
	}
    public function UpdateArticle(Request $request)
    {
        $data = $request->all();
        Article::where('id', $data['id'])->update(array(
			'ArticleNumber' => $data['ArticleNumber'],
            'CategoryId' => $data['CategoryId'],
			'SubCategoryId' => $data['SubCategoryId'],
			'SeriesId' => $data['SeriesId'],
			'StyleDescription'  => $data['StyleDescription'],
			//'CreatedDate' => date("Y-m-d h:i:s")
        ));
        return response()->json("SUCCESS", 200);
    }
	
	public function getarticlepopending()
    {
        return DB::select('SELECT Id, ArticleNumber FROM article a WHERE a.Id NOT IN (SELECT ArticleId FROM po)');
    }
	
	public function getarticle_catscatserial($id)
    {
        return DB::select('SELECT a.ArticleNumber, a.Id, CASE WHEN c.Id IS NOT NULL THEN c.Id ELSE 0 END AS CategoryId, c.Title as CategoryName, CASE WHEN s.Id IS NOT NULL THEN s.Id ELSE 0 END AS SubCategoryId, s.Name as SubCategoryName, CASE WHEN r.Id IS NOT NULL THEN r.Id ELSE 0 END AS RangeSeriesId, r.Series,CASE WHEN b.Id IS NOT NULL THEN b.Id ELSE 0 END AS BrandId, b.Name as BrandName, a.StyleDescription  FROM `article` a left join category c on c.Id=a.CategoryId left join subcategory s on s.Id=a.SubCategoryId left join rangeseries r on r.Id=a.SeriesId left join brand b on b.Id=a.BrandId where a.Id =  '.$id);
    }
	
	public function getartautogenerate($id)
    {
        return DB::select('SELECT ArticleAutoGenerate, ArticleSeriesAuto FROM category where Id='.$id);
    }
	
    public function DeleteArticle($id)
    {
		//DB::table('article')->where('Id', '=', $ArtId)->delete();
			DB::table('articlerate')->where('ArticleId', '=', $id)->delete();
			DB::table('articlephotos')->where('ArticlesId', '=', $id)->delete();
        return DB::table('article')->where('Id', '=', $id)->delete();
    }
	
	public function GetArticleIdWise($id)
    {
        return DB::select('SELECT * From article WHERE Id = ' . $id . '');
    }
	
	public function getArticleSerial($id, $seriesflag, $categoryId){
		if($id==0){
			if($seriesflag==1){
				$data = DB::select('SELECT DATE_FORMAT(created_at, "%m") as Month, DATE_FORMAT(created_at, "%y") as Year, Orderset FROM `article` WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) and CategoryId="'.$categoryId.'" order by Id DESC limit 0,1');
			
				if($data){
					return $response = array("Month"=>$data[0]->Month, "Year"=>$data[0]->Year, "Orderset"=>$data[0]->Orderset+1);
				}else{
					return $response = array("Month"=>date("m"), "Year"=>date("y"), "Orderset"=>1);
				}
			} else{
				$data = DB::select('SELECT DATE_FORMAT(created_at, "%m") as Month, DATE_FORMAT(created_at, "%y") as Year, max(Orderset) as Orderset FROM `article` WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) and CategoryId in (SELECT Id FROM `category` where ArticleSeriesAuto = 0) order by Id DESC limit 0,1');
			
				if (!empty($data[0]->Month)){
					return $response = array("Month"=>$data[0]->Month, "Year"=>$data[0]->Year, "Orderset"=>$data[0]->Orderset+1);
				}else{
					return $response = array("Month"=>date("m"), "Year"=>date("y"), "Orderset"=>1);
				}
			}
			
		}else{
			$data = DB::select('SELECT DATE_FORMAT(created_at, "%m") as Month, DATE_FORMAT(created_at, "%y") as Year, Orderset FROM `article` WHERE Id = '.$id);
			return $response = array("Month"=>$data[0]->Month, "Year"=>$data[0]->Year, "Orderset"=>$data[0]->Orderset);
		}
		//return $data; exit;
		//return response()->json(array("SONO"=>$data, "Result"=>"SUCCESS"), 200);
	}
	
	public function GetSubcatRangeseriesWise($id)
    {
        return DB::select('SELECT * From rangeseries WHERE SubCategoryId  = ' . $id . '');
    }
	
	
	
	
	/* public function GetcategoryIdWise($id)
    {
        return DB::select('SELECT Id, Name FROM `subcategory` where CategoryId = ' . $id . '');
    } */
	
	public function importcsv(Request $request){
		//return "asdas"; exit;
		$data = $request->all();
		
		/* foreach ($data as $obj) {
			//$obj->
		} */
		
		/* $path = request()->file('Import_CSV')->getRealPath();
        $getSize = request()->file('Import_CSV')->getSize();
        $file = file($path);
        // $TotalRecord = count($file);
        $data = array_slice($file, 1); */
        //return $file; exit; 
		//print_r($data); exit;
		
		
		$file = $data['Import_CSV'];
			$path = $file->getRealPath();
        $getSize = $file->getSize();
        $file11 = file($path);
        // $TotalRecord = count($file);
        $data = array_slice($file11, 1);
		
		$importData_arr = Excel::toCollection(new DataImport, $file11);
		return $importData_arr; exit;
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
		
		return $TotalRecord; exit;
		
			$name=$file->getClientOriginalName();
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
});

			/* Excel::load(Input::file('file'), function ($reader) {

                foreach ($reader->toArray() as $row) {
                    User::firstOrCreate($row);
                }
            }); */
			
			return $Import_CSV;
		/* $file_tmp = $_FILES['file']['tmp_name'];
		print_r($file_tmp); exit;
		
		$file_ext = strtolower(end(explode('.',$_FILES['file']['name'])));
		$file = $folderPath . uniqid() . '.'.$file_ext;
		move_uploaded_file($file_tmp, $file); */
	}
	
	function resetarticlecolor(){
		//$articledata = DB::select("SELECT * FROM `article` where OpeningStock=1");
		$articledata = DB::select("SELECT c.Title, c.Colorflag, c.ArticleOpenFlag, a.*FROM `article` a inner join category c on c.Id=a.CategoryId");
		foreach($articledata as $vl){
			if($vl->ArticleOpenFlag==0){
				$ArticleId = $vl->Id;
				
				if($vl->ArticleColor){
					$ArticleColor = json_decode($vl->ArticleColor);
					$ArticleSize = json_decode($vl->ArticleSize);
					$ArticleRatio = $vl->ArticleRatio;
					$getratio = explode(",",$ArticleRatio);
					
					foreach($ArticleColor as $vl){
						$allreadycolor = DB::select("SELECT * FROM `articlecolor` where ArticleColorId='".$vl->Id."' and ArticleId='".$ArticleId."'");
						if(empty($allreadycolor)){
							DB::table('articlecolor')->insertGetId(
								['ArticleId'=>$ArticleId, 'ArticleColorId' => $vl->Id, 'ArticleColorName' => $vl->Name,'CreatedDate' => date("Y-m-d H:i:s")]
							);
						}
					}
					
					foreach($ArticleSize as $key => $vl){
						$allreadysize = DB::select("SELECT * FROM `articlesize` where ArticleSize='".$vl->Id."' and ArticleId='".$ArticleId."'");
						if(empty($allreadysize)){
							DB::table('articlesize')->insertGetId(
								['ArticleId'=>$ArticleId, 'ArticleSize' => $vl->Id,'ArticleSizeName' => $vl->Name,'CreatedDate' => date("Y-m-d H:i:s")]
							);
						}
						
						$allreadyratio = DB::select("SELECT * FROM `articleratio` where ArticleSizeId='".$vl->Id."' and ArticleId='".$ArticleId."'");
						if(empty($allreadyratio)){
							DB::table('articleratio')->insertGetId(
								['ArticleId'=>$ArticleId, 'ArticleSizeId' => $vl->Id, 'ArticleRatio' => $getratio[$key],'CreatedDate' => date("Y-m-d H:i:s")]
							);
						}
					}	
					echo "Article: ".$ArticleId;
					echo "<br />\n\r";
				}
				
			}else{
				echo "Openflag Article: ".$vl->Id;
				echo "<br />\n\r";
			}
		}
		
	}
}
