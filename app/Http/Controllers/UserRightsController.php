<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserRightsController extends Controller
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
    public function GetRoleWiseRights($roleid, $actid)
    {
        $actionpageId = explode('_', $actid);
        $response = DB::select('SELECT * FROM `userrights` WHERE RoleId=' . $roleid . ' and PageId=' . $actionpageId[1] . '');
        if ($actionpageId[0] == 'List') {
            $array = ['ListRights' => $actionpageId[2]];
            $insertarray = [
                'RoleId' => $roleid,
                'AddRights' => '0',
                'EditRights' => '0',
                'ListRights' => $actionpageId[2],
                'DeleteRights' => '0',
                'ViewRights' => '0',
                'PageId' => $actionpageId[1],
            ];
        } else if ($actionpageId[0] == 'Add') {
            $array = ['AddRights' => $actionpageId[2]];
            $insertarray = [
                'RoleId' => $roleid,
                'AddRights' => $actionpageId[2],
                'EditRights' => 0,
                'ListRights' => 0,
                'DeleteRights' => 0,
                'ViewRights' => 0,
                'PageId' => $actionpageId[1],
            ];
        } else if ($actionpageId[0] == 'Edit') {
            $array = ['EditRights' => $actionpageId[2]];
            $insertarray = [
                'RoleId' => $roleid,
                'AddRights' => '0',
                'EditRights' => $actionpageId[2],
                'ListRights' => '0',
                'DeleteRights' => '0',
                'ViewRights' => '0',
                'PageId' => $actionpageId[1],
            ];
        } else if ($actionpageId[0] == 'Delete') {
            $array = ['DeleteRights' => $actionpageId[2]];
            $insertarray = [
                'RoleId' => $roleid,
                'AddRights' => '0',
                'EditRights' => '0',
                'ListRights' => '0',
                'DeleteRights' => $actionpageId[2],
                'ViewRights' => '0',
                'PageId' => $actionpageId[1],
            ];
        }
        if (count($response) > 0) {
            return DB::table('userrights')
                ->where('Id', $response[0]->Id)
                ->update($array);
        } else {
            DB::table('userrights')
                ->insert($insertarray);
            return "true";

        }
    }
}