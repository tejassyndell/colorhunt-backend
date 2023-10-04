<?php

namespace App\Exports;
  
use App\ImportSummary;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;


class UsersExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct(int $SummaryId)
    {
        $this->SummaryId = $SummaryId;
    }
    public function collection()
    { 
     
		$data = DB::table('insuranceimportdata')
        //->leftJoin('zillow_api_response', 'zillow_api_response.Insurance_Import_Id', '=', 'insuranceimportdata.Id')
        ->leftJoin('estated_api_response', 'estated_api_response.Insurance_Import_Id', '=', 'insuranceimportdata.Id')
		->where("SummaryId", $this->SummaryId)
        ->select('insuranceimportdata.ApplicationDate', 'insuranceimportdata.EstClosingDate', 'insuranceimportdata.BorrowerFName', 'insuranceimportdata.BorrowerMName', 'insuranceimportdata.BorrowerLName', 'insuranceimportdata.BorrDOB', 'insuranceimportdata.BorrMaritalStatus', 'insuranceimportdata.Co_BorrowerFName', 'insuranceimportdata.Co_BorrowerMName', 'insuranceimportdata.Co_BorrowerLName', 'insuranceimportdata.Co_BorrDOB', 'insuranceimportdata.BorrPresentAddr', 'insuranceimportdata.BorrPresentCity', 'insuranceimportdata.BorrPresentState', 'insuranceimportdata.BorrPresentZip', 'insuranceimportdata.BorrHomePhone', 'insuranceimportdata.BorrCell', 'insuranceimportdata.BorrBusinessPhone', 'insuranceimportdata.BorrEmail', 'insuranceimportdata.LoanPurpose', 'insuranceimportdata.BorrOwn_RentPresentAddr', 'insuranceimportdata.OccupancyType', 'insuranceimportdata.SubjectPropertyStreet', 'insuranceimportdata.SubjectPropertyCity', 'insuranceimportdata.SubjectPropertyState', 'insuranceimportdata.SubjectPropertyZip', 'insuranceimportdata.LoanOfficerName', 'insuranceimportdata.BranchID', 'insuranceimportdata.Loan', 'insuranceimportdata.LoanProcessorName', 'insuranceimportdata.LoanAmt', 'insuranceimportdata.BuyersAgentName','estated_api_response.EstatedJsonData','estated_api_response.Status')
        ->orderBy('insuranceimportdata.Id', 'ASC')
        ->get();
		
		//echo "<pre>"; print_r($data); exit;
		foreach ($data as $key => $row) {
			//$row->title = "testtitle";
			$apidata = json_decode($row->EstatedJsonData);
		
			if($row->Status == 'Success'){
			    unset($row->Status);
			//	$row->formatted_street_address = $apidata->data->address->formatted_street_address;
			//	$row->area_sq_ft = $apidata->data->parcel->area_sq_ft;
			//	$row->area_acres = $apidata->data->parcel->area_acres;
					
					
				if($apidata->data->structure===null){
					$row->year_built = 1990;
					$row->stories = 2;
					$row->baths = 2;
					$row->parking_type = "GARAGE";
					$row->roof_year = 2012;
					$row->pool_type = "N";
					$row->total_area_sq_ft = 2000;
					$row->Dwelling_Amount = (($row->total_area_sq_ft)*(125));
				// 	$row->effective_year_built = "";
				// 	$row->rooms_count = "";
				// 	$row->beds_count = "";
				// 	$row->fireplaces = "";
				// 	$row->air_conditioning_type = "";
				}else{
					if($apidata->data->structure->year_built==""){
						$row->year_built = 1990;
					}else{
						$row->year_built = $apidata->data->structure->year_built;
					}
					
					if($apidata->data->structure->stories==""){
						$row->stories = 2;
					}else{
						$row->stories = $apidata->data->structure->stories;
					}
					
					if($apidata->data->structure->baths==""){
						$row->baths = 2;
					}else{
						$row->baths = $apidata->data->structure->baths;
					}
					
					if($apidata->data->structure->parking_type==""){
						$row->parking_type = "GARAGE";
					}else{
						$row->parking_type = $apidata->data->structure->parking_type;
					}
					
					$row->roof_year = 2012;
					
					if($apidata->data->structure->pool_type==""){
						$row->pool_type = "N";
					}else{
						$row->pool_type = $apidata->data->structure->pool_type;
					}
					
					if($apidata->data->structure->total_area_sq_ft==""){
						$row->total_area_sq_ft = 2000;
					}else{
						$row->total_area_sq_ft = $apidata->data->structure->total_area_sq_ft;
					}
					
					
					$row->Dwelling_Amount = (($row->total_area_sq_ft)*(125));
				// 	$row->effective_year_built = $apidata->data->structure->effective_year_built;
				// 	$row->rooms_count = $apidata->data->structure->rooms_count;
				// 	$row->beds_count = $apidata->data->structure->beds_count;
				// 	$row->fireplaces = $apidata->data->structure->fireplaces;
				// 	$row->total_area_sq_ft = $apidata->data->structure->total_area_sq_ft;
				// 	$row->air_conditioning_type = $apidata->data->structure->air_conditioning_type;
				}
				
			}else{
				$row->year_built = 1990;
				$row->stories = 2;
				$row->baths = 2;
				$row->parking_type = "GARAGE";
				$row->roof_year = 2012;
				$row->pool_type = "N";
				$row->total_area_sq_ft = 2000;
				$row->Dwelling_Amount = (($row->total_area_sq_ft)*(125));
			}
			unset($row->EstatedJsonData);
			unset($row->Status);
			
			
		}
		//echo "<pre>"; print_r($data); exit;
		return $data;
		
        //return   DB::table('insuranceimportdata')->select('Id','ApplicationDate', 'BorrowerFName')->get();;
        /* return DB::table('insuranceimportdata')
        //->leftJoin('zillow_api_response', 'zillow_api_response.Insurance_Import_Id', '=', 'insuranceimportdata.Id')
        ->leftJoin('estated_api_response', 'estated_api_response.Insurance_Import_Id', '=', 'insuranceimportdata.Id')
		->where("SummaryId", $this->SummaryId)
        ->select('insuranceimportdata.ApplicationDate', 'insuranceimportdata.EstClosingDate', 'insuranceimportdata.BorrowerFName', 'insuranceimportdata.BorrowerMName', 'insuranceimportdata.BorrowerLName', 'insuranceimportdata.BorrDOB', 'insuranceimportdata.BorrMaritalStatus', 'insuranceimportdata.Co_BorrowerFName', 'insuranceimportdata.Co_BorrowerMName', 'insuranceimportdata.Co_BorrowerLName', 'insuranceimportdata.Co_BorrDOB', 'insuranceimportdata.BorrPresentAddr', 'insuranceimportdata.BorrPresentCity', 'insuranceimportdata.BorrPresentState', 'insuranceimportdata.BorrPresentZip', 'insuranceimportdata.BorrHomePhone', 'insuranceimportdata.BorrCell', 'insuranceimportdata.BorrBusinessPhone', 'insuranceimportdata.BorrEmail', 'insuranceimportdata.LoanPurpose', 'insuranceimportdata.BorrOwn_RentPresentAddr', 'insuranceimportdata.OccupancyType', 'insuranceimportdata.SubjectPropertyStreet', 'insuranceimportdata.SubjectPropertyCity', 'insuranceimportdata.SubjectPropertyState', 'insuranceimportdata.SubjectPropertyZip', 'insuranceimportdata.LoanOfficerName', 'insuranceimportdata.BranchID', 'insuranceimportdata.Loan', 'insuranceimportdata.LoanProcessorName', 'insuranceimportdata.LoanAmt', 'insuranceimportdata.BuyersAgentName','estated_api_response.EstatedJsonData','estated_api_response.Status','estated_api_response.Status','estated_api_response.Status','estated_api_response.Status','estated_api_response.Status')
        ->orderBy('insuranceimportdata.Id', 'ASC')
        ->get(); */
       // echo "<pre>";print_r($data);exit;

        //'insuranceimportdata.ApplicationDate' 'insuranceimportdata.EstClosingDate', 'insuranceimportdata.BorrowerFName', 'insuranceimportdata.BorrowerMName', 'insuranceimportdata.BorrowerLName', 'insuranceimportdata.BorrDOB', 'insuranceimportdata.BorrMaritalStatus', 'insuranceimportdata.Co_BorrowerFName', 'insuranceimportdata.Co_BorrowerMName', 'insuranceimportdata.Co_BorrowerLName', 'insuranceimportdata.Co_BorrDOB', 'insuranceimportdata.BorrPresentAddr', 'insuranceimportdata.BorrPresentCity', 'insuranceimportdata.BorrPresentState', 'insuranceimportdata.BorrPresentZip', 'insuranceimportdata.BorrHomePhone', 'insuranceimportdata.BorrCell', 'insuranceimportdata.BorrBusinessPhone', 'insuranceimportdata.BorrEmail', 'insuranceimportdata.LoanPurpose', 'insuranceimportdata.BorrOwn_RentPresentAddr', 'insuranceimportdata.OccupancyType', 'insuranceimportdata.SubjectPropertyStreet', 'insuranceimportdata.SubjectPropertyCity', 'insuranceimportdata.SubjectPropertyState', 'insuranceimportdata.SubjectPropertyZip', 'insuranceimportdata.LoanOfficerName', 'insuranceimportdata.BranchID', 'insuranceimportdata.Loan', 'insuranceimportdata.LoanProcessorName', 'insuranceimportdata.LoanAmt', 'insuranceimportdata.BuyersAgentName',

        //'zillow_api_response.zpid','zillow_api_response.FIPScounty','zillow_api_response.useCode','zillow_api_response.taxAssessment','zillow_api_response.lotSizeSqFt','zillow_api_response.finishedSqFt',
       
      
            // return DB::table('insuranceimportdata')
            // ->leftJoin('zillow_api_response', 'zillow_api_response.Insurance_Import_Id', '=', 'insuranceimportdata.Id')
            // ->where("SummaryId", $this->SummaryId)
            // ->select('insuranceimportdata.*', 'zillow_api_response.Id as ZillowId', 'zillow_api_response.Status')
            // ->orderBy('insuranceimportdata.Id', 'ASC')
            // ->get();

        //return User::all();
    }
   public function headings() : array
    {
        return [
            'Application Date', 
            'Est Closing Date',
            'Borrower First Name',
            'Borrower Middle Name',
            'Borrower Last Name',
            'Borr DOB',
            'Borr Marital Status',
            'Co-Borrower First Name',
            'Co-Borrower Middle Name',
            'Co-Borrower Last Name',
            'Co-Borr DOB',
            'Borr Present Addr',
            'Borr Present City',
            'Borr Present State',
            'Borr Present Zip',
            'Borr Home Phone',
            'Borr Cell',
            'Borr Business Phone',
            'Borr Email',
            'Loan Purpose',
            'Borr Own/Rent Present Addr',
            'Occupancy Type',
            'Subject Property Street',
            'Subject Property City',
            'Subject Property State',
            'Subject Property Zip',
            'Loan Officer Name',
            'Branch ID',
            'Loan #',
            'Loan Processor Name',
            'Loan Amt',
            'Buyers Agent Name',
            'Year Built',
            'Stories',
            'Baths',
            'Parking Type',
			'Roof Year',
            'Pool',
            'Total Area Square Footage',
            'Dwelling Amount',
// 			'API Status',
// 			'Formatted Street Address',
// 			'Area Sq. ft.',
//             'Area Acres',
//             'Year Built',
// 			'Effective Year Built',
// 			'Stories',
//             'Rooms Count',
// 			'Beds Count',
// 			'Baths',
// 			'Parking Type',
// 			'Fireplaces',
//             'Total Area Sq. ft.',
// 			'Air Conditioning Type'
        ];
    }

    /* public function test() : array
    {
        return [          
            'Application Date', 
            'Est Closing Date',
            'Borrower First Name',
            'Borrower Middle Name',
            'Borrower Last Name',
            'Borr DOB',
            'Borr Marital Status',
            'Co-Borrower First Name',
            'Co-Borrower Middle Name',
            'Co-Borrower Last Name',
            'Co-Borr DOB',
            'Borr Present Addr',
            'Borr Present City',
            'Borr Present State',
            'Borr Present Zip',
            'Borr Home Phone',
            'Borr Cell',
            'Borr Business Phone',
            'Borr Email',
            'Loan Purpose',
            'Borr Own/Rent Present Addr',
            'Occupancy Type',
            'Subject Property Street',
            'Subject Property City',
            'Subject Property State',
            'Subject Property Zip',
            'Loan Officer Name',
            'Branch ID',
            'Loan #',
            'Loan Processor Name',
            'Loan Amt',
            'Buyers Agent Name',
            'Area Sq. ft.',
            'Area Acres',
            'Year Built',
            'Effective Year Built',
            'Total Area Sq. ft.',
            'Parking Type'
        ];
    } */
}
