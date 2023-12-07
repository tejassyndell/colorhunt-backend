<?php

namespace App\Http\Controllers;

use App\Financialyear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialyearController extends Controller
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Financialyear  $financialyear
     * @return \Illuminate\Http\Response
     */
    public function show(Financialyear $financialyear)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Financialyear  $financialyear
     * @return \Illuminate\Http\Response
     */
    public function edit(Financialyear $financialyear)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Financialyear  $financialyear
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Financialyear $financialyear)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Financialyear  $financialyear
     * @return \Illuminate\Http\Response
     */
    public function destroy(Financialyear $financialyear)
    {
        //
    }
	
	
	
	//Financial Year Module
    public function AddFinancialYear(Request $request)
    {
		$data = $request->all();
        $dataresult= DB::select('SELECT * FROM `financialyear` WHERE `StartYear` LIKE "'.$data['StartYear'].'"'); 
        if($dataresult){
            return response()->json('allreadyexits', 201);
        }else{
			$field = Financialyear::create($request->all());
			return response()->json($field, 201);
        }
    }
	
    public function GetFinancialYear()
    {
        return Financialyear::all();
    }
	
    public function UpdateFinancialYear(Request $request)
    {
        $data = $request->all();
		//echo "<pre>"; print_r($data); exit;
        Financialyear::where('Id', $data['id'])->update(array(
            'StartYear' => $data['StartYear'],
			'EndYear' => $data['EndYear']
        ));
        return response()->json("SUCCESS", 200);
    }
	
    public function DeleteFinancialYear($id)
    {
        return DB::table('financialyear')->where('Id', '=', $id)->delete();
    }
	
    public function GetFinancialYearIdWise($id)
    {
        return DB::select('SELECT * From financialyear WHERE Id = ' . $id . '');
    }
	
    public function CheckFinancialYearexits(Request $request)
    { 
        $data = $request->all();
        return  DB::select('SELECT * FROM `financialyear` WHERE `StartYear` LIKE "'.$data[0].'"'); 
    }
}
