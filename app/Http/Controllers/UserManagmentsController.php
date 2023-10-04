<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\UserLogs;

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
        if ($request['Role'] == 7) {
            $PartyId = $request['PartyId'];
        } else {
            $PartyId = 0;
        }
        $getdata = DB::select("SELECT count(*) as Total From users WHERE Email = '" . $request['Email'] . "'");
        if ($getdata[0]->Total > 0) {
            return response()->json(array("Result" => "2"), 200);
        } else {
            $userId = DB::table('users')->insertGetId([
                'Role' => $request['Role'],
                'Name' => $request['Name'],
                'Email' => $request['Email'],
                'Status' => 1,
                'Password' => Crypt::encryptString($request['Password']),
                'PartyId' => $PartyId,
            ]);
            $userName = Users::where('Id', $request['LoggedId'])->first();
            UserLogs::create([
                'Module' => 'User',
                'ModuleNumberId' => (int)$userId,
                'LogType' => 'Created',
                'LogDescription' => $userName['Name'] . ' created user ' . $request['Name'],
                'UserId' => $request['LoggedId'],
                'updated_at' => null
            ]);
            return response()->json(array("Result" => "1"), 200);
        }
    }
    public function GetDelete($id, $LoggedId)
    {
        $user = Users::where('Id', $LoggedId)->first();
        $activeuser = Users::where('Id', $id)->first();
        DB::table('users')->where('Id', '=', $id)->delete();
        UserLogs::create([
            'Module' => 'User',
            'ModuleNumberId' => (int)$activeuser->Id,
            'LogType' => 'Deleted',
            'LogDescription' => $user['Name'] . " " . 'deleted user' . " " . $activeuser['Name'],
            'UserId' => $LoggedId,
            'updated_at' => null
        ]);
        return $user;
    }
    public function GetUserIdWise($id)
    {
        $getdata = DB::select('SELECT * From users WHERE Id = ' . $id . '');
        $getdata[0]->Password = Crypt::decryptString($getdata[0]->Password);
        return $getdata;
    }
    public function UpdateUser(Request $request)
    {
        // return $request;
        $data = $request->all();
        if ($request['Role'] == 7) {
            $PartyId = $data['PartyId'];
        } else {
            $PartyId = 0;
        }
        $user = Users::where('Id', (int)$data['LoggedId'])->first();
        $activeuser  = Users::where('Id', (int)$data['id'])->first();
        $logDesc = "";
        if ($activeuser->Role != (int)$data['Role']) {
            $logDesc = $logDesc . 'Role,';
        }
        if ($activeuser->Name != $data['Name']) {
            $logDesc = $logDesc . 'Name,';
        }
        if ($activeuser->Email != $data['Email']) {
            $logDesc = $logDesc . 'Email,';
        }
        if ($activeuser->PartyId != $data['PartyId']) {
            $logDesc = $logDesc . 'Party,';
        }
        $newLogDesc = rtrim($logDesc, ',');
        // $dsgdfgdfg = (int)$data['id'];
        // return ['dfdfgdfgdfg'=>$dsgdfgdfg];
        Users::where('Id', (int)$data['id'])->update(array(
            'Role' =>  $data['Role'],
            'Name' =>  $data['Name'],
            'Email' =>  $data['Email'],
            'Password'    => Crypt::encryptString($data['Password']),
            'PartyId' => $PartyId
        ));
        // $dfdsfdsfd=$data['id'];

        UserLogs::create([
            'Module' => 'User',
            'ModuleNumberId' => (int)$data['id'],
            'LogType' => 'Updated',
            'LogDescription' => $user->Name . " " . "Updated " . $newLogDesc . " of user " . $data['Name'],
            'UserId' => $user->Id,
            'updated_at' => null
        ]);

        return response()->json(["SUCCESS" => 200, 'user' => $activeuser]);
    }

    public function Login(Request $request)
    {
        $data = $request->all();
        $getuserdata = DB::select("SELECT u.*, ur.Role as RoleName From users u inner join userrole ur on ur.RoleType=u.Role WHERE u.Email = '" . $data['email'] . "'");
        $array = array();
        if ($getuserdata) {
            if ($getuserdata[0]->Status == 0) {
                return response()->json("DEACTIVE");
            } else {
                if ($getuserdata) {
                    $password = Crypt::decryptString($getuserdata[0]->Password);
                    if ($data['password'] == $password) {
                        return response()->json($getuserdata);
                    } else {
                        return response()->json($array);
                    }
                } else {
                    return response()->json($array);
                }
            }
        } else {
            return response()->json("NOTFOUND");
        }
    }

    public function updateUserStatus($id, $LoggedId)
    {
        $user = Users::where('Id', $id)->first();
        $userName = Users::where('Id', $LoggedId)->first();
        if ($user->Status == 1) {
            Users::where('Id', $id)->update(['Status' => 0]);
            UserLogs::create([
                'Module' => 'User',
                'ModuleNumberId' => $user->Id,
                'LogType' => 'Status Updated',
                'LogDescription' => $userName['Name'] . " " . 'has activated user' . " " . $user['Name'],
                'UserId' => $LoggedId,
                'updated_at' => null
            ]);
            return response()->json(['User' => $user, 'status' => 'Deactive'], 200);
        } else {
            Users::where('Id', $id)->update(['Status' => 1]);
            UserLogs::create([
                'Module' => 'User',
                'ModuleNumberId' => $user->Id,
                'LogType' => 'Status Updated',
                'LogDescription' => $userName['Name'] . " " . 'has deactivated user' . " " . $user['Name'],
                'UserId' => $LoggedId,
                'updated_at' => null
            ]);
            return response()->json(['User' => $user, 'status' => 'Active'], 200);
        }
    }
    public function ViewUsers($id)
    {
        return DB::select("select u.Id, u.Name, u.Email, ur.Role, u.Status from users u inner join userrole ur on u.Role = ur.RoleType where u.Id = '".$id."' ");
        // return User::where('Id', $id)->first();
        // return $user;
    }
    public function ViewUserLogs($id , $value)
    {
        if ($id == 0) {
            if ($value != 'null') {
                $data = DB::select("select dd.* from (select DATE_FORMAT(ul.created_at ,'%d-%m-%Y') as Date, DATE_FORMAT(ul.created_at ,'%H:%i:%s') as Time, ul.Id as LogsId, ul.Module, u.Name, ur.Role, u.Status, ul.LogType, ul.LogDescription from userlogs ul inner join users u on u.Id=ul.UserId inner join userrole ur on ur.RoleType=u.Role) as dd where dd.Module='" . $value . "' order by dd.LogsId DESC ");
            } else {
                $data = DB::select("select dd.* from (select DATE_FORMAT(ul.created_at ,'%d-%m-%Y') as Date, DATE_FORMAT(ul.created_at ,'%H:%i:%s') as Time, ul.Id as LogsId, ul.Module, u.Name, ur.Role, u.Status, ul.LogType, ul.LogDescription from userlogs ul inner join users u on u.Id=ul.UserId inner join userrole ur on ur.RoleType=u.Role) as dd order by dd.LogsId DESC");
                // return $data;
            }
        } else {
            $data = DB::select("select dd.* from (select DATE_FORMAT(ul.created_at ,'%d-%m-%Y') as Date, DATE_FORMAT(ul.created_at ,'%H:%i:%s') as Time, ul.Id as LogsId,u.Id as UserId, ul.Module, u.Name, ur.Role, u.Status, ul.LogType, ul.LogDescription from userlogs ul inner join users u on u.Id=ul.UserId inner join userrole ur on ur.RoleType=u.Role) as dd where dd.UserId='" . $id . "' order by dd.LogsId DESC");
        }
        return $data;
    }
    public function UserLogsDelete($id)
    {
        // return DB::delete("delete from userlogs where UserId = '".$id."'");
        DB::table('userlogs')->where('Id', $id)->delete();
        // return UserLogs::where('UserId', $id)->delete();
        return response()->json(['status' => 'success'] , 200);
    }
}
