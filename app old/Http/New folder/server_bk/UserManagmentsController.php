<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class UserManagmentsController extends Controller
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

    public function GetAll()
    {
       return DB::select('SELECT u.*,ur.Role as RoleName FROM `users` u LEFT JOIN userrole ur ON u.Role=ur.RoleType');
    }

    public function store(Request $request)
    {
        //echo "<pre>";print_r($request->all());exit;
		if($request['Role']==7){
			$PartyId = $request['PartyId'];
		}else{
			$PartyId = 0;
		}
		
		$getdata = DB::select("SELECT count(*) as Total From users WHERE Email = '".$request['Email']."'");
		
		if($getdata[0]->Total>0){
			return response()->json(array("Result"=>"2"), 200);
		}else{
			$field =  Users::create([
				'Role' => $request['Role'],
				'Name' => $request['Name'],
				'Email' => $request['Email'],
				'Password' => Crypt::encryptString($request['Password']),
				'PartyId' => $PartyId,
			]);
			return response()->json(array("Result"=>"1"), 200);
		}
    }
    public function GetDelete($id)
    {
       return DB::table('users')->where('Id', '=', $id)->delete();
      
    }
    public function GetUserIdWise($id)
    {
        //echo "<pre>";print_r($id);exit;
        
		$getdata = DB::select('SELECT * From users WHERE Id = '.$id.'');
		$getdata[0]->Password = Crypt::decryptString($getdata[0]->Password);
		return $getdata;
      //  return UserRights::all();
    }
    public function UpdateUser(Request $request)
    {
		$data = $request->all();
		if($request['Role']==7){
			$PartyId = $data['PartyId'];
		}else{
			$PartyId = 0;
		}
		//echo $data['Password'];
		//echo "Decrypt".$decrypted = Crypt::decryptString("eyJpdiI6IkczbTZmYXRoOGtCazZJUndwWEpNRGc9PSIsInZhbHVlIjoiNGdiR21qMyszcDFnT0dvTU9IVU1mdz09IiwibWFjIjoiZmI5OGZjZjVlN2IzYjUxODIwMzMwYTM2OGJhZDA5YzNiZmY1YjJlOWUzODNmNTA3NmYzNTE4ODAxYzNjOTg1MCJ9"); exit;
		Users::where('id', $data['id'])->update(array(
				'Role' 	  =>  $data['Role'],
				'Name' =>  $data['Name'],
				'Email' =>  $data['Email'],
				'Password'	=> Crypt::encryptString($data['Password']),
				'PartyId' => $PartyId
			));
			
		//$inputall = Input::all();
		//print_r($field->Users($request->all()));
		//print_r($inputall);
        //exit;
        // $field->Users($request->all());

        return response()->json("SUCCESS", 200);
    }

    public function Login(Request $request)
    {	
		$data = $request->all();		
        //echo "<pre>"; print_r($data); exit;
        // $query = DB::select('SELECT * From users WHERE Email = "'.$data['email'].'" and  Password = "'.$data['password'].'"');
        // echo "<pre></pre>"; print_r($query); exit;
		//SELECT u.*, ur.Role From users u inner join userrole ur on ur.RoleType=u.Role WHERE u.Email = 'subhash@eoniansoftware.com'
		//$getuserdata = DB::select("SELECT * From users WHERE Email = '".$data['email']."'");
		$getuserdata = DB::select("SELECT u.*, ur.Role as RoleName From users u inner join userrole ur on ur.RoleType=u.Role WHERE u.Email = '".$data['email']."'");
        $array = array();
		//echo "<pre>"; print_r($getuserdata); exit;
        if($getuserdata){
            $password = Crypt::decryptString($getuserdata[0]->Password);
            //echo $password; exit;
            if($data['password']==$password){
               // return $getuserdata;
				return response()->json($getuserdata);
            }else {
                //return $array;
				return response()->json($array);
            }
        }else{
            //return $array;
			return response()->json($array);
        }
    }
    
}
