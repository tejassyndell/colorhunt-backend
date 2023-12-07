<?php
namespace App\Imports;

use App\ImportTool;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DatasImport implements ToModel,WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    /* private $data;

    public function __construct()
    {
        //$_month = $month;
    } */
	
    public function model(array $row)
    {
        //print_r($row);exit;
    }
}
