<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Outward;
use App\Transportoutlet;
use App\OutwardNumber;
use App\Users;
use App\OutletNumber;
use App\Party;
use App\UserLogs;
use App\Article;

class OutwardController extends Controller
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


    private function isExpoPushToken($token)
    {
        // Add your validation logic here
        // You may need to refer to Expo's documentation for the format of valid tokens
        // Example: return preg_match('/^[0-9a-fA-F]{8,60}$/', $token);
        return true; // Placeholder, replace with actual validation
    }

    private function sendPushNotifications($messages)
    {
        $ch = curl_init();
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $postData = json_encode($messages);

        curl_setopt($ch, CURLOPT_URL, 'https://exp.host/--/api/v2/push/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($statusCode !== 200) {
            throw new \Exception('Error sending notification');
        }

        return json_decode($response, true);
    }

    public function GenerateOWNumber($UserId)
    {
        $array = array();
        $fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
        $ownumberdata = DB::select('SELECT Id, FinancialYearId, OutwardNumber From outwardnumber order by Id desc limit 0,1');
        if (count($ownumberdata) > 0) {
            if ($fin_yr[0]->Id > $ownumberdata[0]->FinancialYearId) {
                $array["OW_Number"] = 1;
                $array["OW_Number_Financial_Id"] = $fin_yr[0]->Id;
                $array["OW_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
                return $array;
            } else {
                $array["OW_Number"] = ($ownumberdata[0]->OutwardNumber) + 1;
                $array["OW_Number_Financial_Id"] = $fin_yr[0]->Id;
                $array["OW_Number_Financial"] = ($ownumberdata[0]->OutwardNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
                return $array;
            }
        } else {
            $array["OW_Number"] = 1;
            $array["OW_Number_Financial_Id"] = $fin_yr[0]->Id;
            $array["OW_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
            return $array;
        }
    }
    public function GenerateOutletNumber($partyid)
    {
        $array = array();
        $fin_yr = DB::select("SELECT Id, concat(StartYear,'-',EndYear) as CurrentFinancialYear FROM `financialyear` order by Id desc");
        $outletnumberdata = DB::select('SELECT Id, FinancialYearId, OutletNumber From outletnumber where PartyId="' . $partyid . '" order by Id desc limit 0,1');
        // $outletnumberdata = DB::select('SELECT Id, FinancialYearId, OutletNumber From outletnumber order by Id desc limit 0,1');
        if (count($outletnumberdata) > 0) {
            if ($fin_yr[0]->Id > $outletnumberdata[0]->FinancialYearId) {
                $array["Outlet_Number"] = 1;
                $array["Outlet_Number_Financial_Id"] = $fin_yr[0]->Id;
                $array["Outlet_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
                return $array;
            } else {
                $array["Outlet_Number"] = ($outletnumberdata[0]->OutletNumber) + 1;
                $array["Outlet_Number_Financial_Id"] = $fin_yr[0]->Id;
                $array["Outlet_Number_Financial"] = ($outletnumberdata[0]->OutletNumber) + 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
                return $array;
            }
        } else {
            $array["Outlet_Number"] = 1;
            $array["Outlet_Number_Financial_Id"] = $fin_yr[0]->Id;
            $array["Outlet_Number_Financial"] = 1 . "/" . $fin_yr[0]->CurrentFinancialYear;
            return $array;
        }
    }

    public function AddOutward(Request $request)
    {

        
        //SENDING NOTIFICATION...
        
        $partyid = $request->PartyId;
        $registrationToken = DB::select("SELECT token FROM `party` WHERE Id = " . $partyid);
        // return '[' . $registrationToken . ']' ;
        
        
        $registrationToken = [$registrationToken[0]->token];
        $art = DB::select("SELECT ArticleNumber FROM `article` WHERE Id = " . $request->ArticleId);
        
        // API endpoint
        $apiEndpoint = 'https://colorhunt-server.sincprojects.com/pushnotification';
        
        // API parameters
        $data = [
            "body" => "colorhunt",
            "registrationToken" => $registrationToken,
            "title" => "Article " . $art[0]->ArticleNumber . " is outwarded "
        ];
        
        // Make API call
        $response = Http::post($apiEndpoint, $data);
        
        // Check for success
        if ($response->successful()) {
            // API call successful
            $responseData = $response->json();
            // Process $responseData as needed
        } else {
            // API call failed
            $errorData = $response->json();
            // Handle the error
        }
        
        // NOTIFICATION COMPLETED


        $data = $request->all();

        //using for outletrepot yashvi

        $articleId = $data['ArticleId'];
        $result = DB::select("SELECT article.ArticleNumber,brand.Name,subcategory.Name,article.ArticleOpenFlag,category.Colorflag FROM article left JOIN brand ON article.BrandId = brand.Id left JOIN subcategory ON article.SubCategoryId = subcategory.Id left JOIN category ON article.ArticleOpenFlag = category.ArticleOpenFlag WHERE article.Id = '" . $articleId . "' ");
        if ($result[0]->ArticleOpenFlag == 0) {
            // Code 2
            $articleNumber = $result[0]->ArticleNumber;
            $name = $result[0]->Name;
            $colorflag = $result[0]->Colorflag;


            $existingRecord = DB::table('artstockstatus')
                ->where(['outletId' => $data['PartyId'], 'ArticleId' => $articleId])
                ->get();
            $getresult = DB::select("SELECT SalesNoPacks FROM `artstockstatus` WHERE outletId = '" . $data["PartyId"] . "' AND ArticleId = " . $articleId);

            if ($existingRecord && !empty($getresult)) {

                $GetNoPacksString = $getresult[0]->SalesNoPacks;
                $GetNoPacksArray = explode(',', $GetNoPacksString);
                $salesNoPacksData = [];
                $totalPieces = 0;


                foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                    $numberofpacks = $vl["Id"];
                    $noPacksNewKey = 'NoPacksNew_' . $numberofpacks;
                    $noPacksKey = 'NoPacks_' . $numberofpacks;

                    if (isset($data[$noPacksNewKey]) && isset($data[$noPacksKey])) {
                        $noPacksNewValue = (int) $data[$noPacksNewKey];
                        $noPacksValue = (int) $data[$noPacksKey];
                        $salesNoPacksData[] = abs($GetNoPacksArray[$key] + $noPacksNewValue);
                    } else {
                        $salesNoPacksData[] = 0;
                    }
                }

                $totalPieces = array_sum($salesNoPacksData);
                $salesNoPacksDataString = implode(',', $salesNoPacksData);

                // Update existing record
                DB::table('artstockstatus')
                    ->where(['outletId' => $data['PartyId'], 'ArticleId' => $articleId])
                    ->update(['SalesNoPacks' => $salesNoPacksDataString, 'TotalPieces' => $totalPieces]);
            } else {

                $salesNoPacksData = [];
                $totalPieces = 0;

                foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                    $numberofpacks = $vl["Id"];
                    $noPacksNewKey = 'NoPacksNew_' . $numberofpacks;
                    $noPacksKey = 'NoPacks_' . $numberofpacks;

                    if (isset($data[$noPacksNewKey]) && isset($data[$noPacksKey])) {
                        $noPacksNewValue = (int) $data[$noPacksNewKey];
                        $noPacksValue = (int) $data[$noPacksKey];
                        $salesNoPacksData[] = abs( $noPacksNewValue);
                    } else {
                        $salesNoPacksData[] = 0;
                    }
                }

                $totalPieces = array_sum($salesNoPacksData);
                $salesNoPacksDataString = implode(',', $salesNoPacksData);
                // Insert new record

                $isOutlet = DB::select("SELECT OutletAssign FROM `party` where Id ='" . $data['PartyId'] . "'");
                if ($isOutlet[0]->OutletAssign == 1) { 
                DB::table('artstockstatus')->insert([
                    'outletId' => $data['PartyId'],
                    'ArticleId' => $articleId,
                    'ArticleNumber' => $articleNumber,
                    'SalesNoPacks' => $salesNoPacksDataString,
                    'TotalPieces' => $totalPieces,
                    'ArticleColor' => $data['ArticleSelectedColor'][0]['Name'],
                    'ArticleSize' => implode(',', array_column($data['ArticleSelectedSize'], 'Name')),
                    'ArticleRatio' => $data['ArticleRatio'],
                    'ArticleOpenFlag' => $data['ArticleOpenFlag'],
                    'Title' => $data['Category'],
                    'Colorflag' => $colorflag,
                    'Subcategory' => $name,
                ]);
                }
            }
        } else {
            
            $existingRecord = DB::table('artstockstatus')
                ->where(['outletId' => $data['PartyId'], 'ArticleId' => $articleId])
                ->get();
            // $salesNoPacksData = [];
            // $totalPieces = 0;
            $articleNumber = $result[0]->ArticleNumber;
            $name = $result[0]->Name;
            $colorflag = $result[0]->Colorflag;

            // if ($existingRecord) {
            //     $getresult = DB::select("SELECT SalesNoPacks FROM `artstockstatus` WHERE outletId = '" . $data["PartyId"] . "' AND ArticleId = " . $articleId);
                
            //     if (!empty($getresult)) {
            //         $GetNoPacks = $getresult[0]->SalesNoPacks; // Use "SalesNoPacks" instead of "GetNoPacks"
            //         $dataupdate = $GetNoPacks + $data['NoPacksNew'];
            //         DB::table('artstockstatus')->where(['outletId' => $data['PartyId']])->where(['ArticleId' => $articleId])->update(['SalesNoPacks' => $dataupdate, 'TotalPieces' => $dataupdate]);
            //     } else {
            //         // Handle the case where $getresult is empty
            //     }
            // } else {
            //     $dataupdate = $data['NoPacksNew'];
                
            //     // DB::table('artstockstatus')->where(['outletId' => $data['PartyId']])->where(['ArticleId' => $articleId])->update(['SalesNoPacks' => $dataupdate, 'TotalPieces' => $dataupdate]);
                
            //     // Insert new record
            //     $isOutlet = DB::select("SELECT OutletAssign FROM `party` where Id ='" . $data['PartyId'] . "'");
                
            //     if (!empty($isOutlet) && $isOutlet[0]->OutletAssign == 1) {
            //         DB::table('artstockstatus')->insert([
            //             'outletId' => $data['PartyId'],
            //             'ArticleId' => $articleId,
            //             'ArticleNumber' => $articleNumber,
            //             'SalesNoPacks' => $dataupdate,
            //             'TotalPieces' => $dataupdate,
            //             'ArticleColor' => $data['ArticleSelectedColor'][0]['Name'],
            //             'ArticleSize' => implode(',', array_column($data['ArticleSelectedSize'], 'Name')),
            //             'ArticleRatio' => $data['ArticleRatio'],
            //             'ArticleOpenFlag' => $data['ArticleOpenFlag'],
            //             'Title' => $data['Category'],
            //             'Colorflag' => $colorflag,
            //             'Subcategory' => $name,
            //         ]);
            //     }
            // }

            $salesNoPacksData = []; // Initialize the variable as an empty array

if ($existingRecord) {
    $getresult = DB::select("SELECT SalesNoPacks FROM `artstockstatus` WHERE outletId = '" . $data["PartyId"] . "' AND ArticleId = " . $articleId);
    
    if (!empty($getresult)) {
        $GetNoPacks = $getresult[0]->SalesNoPacks;
        $dataupdate = $GetNoPacks + $data['NoPacksNew'];
        
        DB::table('artstockstatus')->where(['outletId' => $data['PartyId']])->where(['ArticleId' => $articleId])->update(['SalesNoPacks' => $dataupdate, 'TotalPieces' => $dataupdate]);
        
        $salesNoPacksData[] = $dataupdate; // Add the value to the array
    } else {
        // Handle the case where $getresult is empty
    }
} else {
    $dataupdate = $data['NoPacksNew'];
    
    // DB::table('artstockstatus')->where(['outletId' => $data['PartyId']])->where(['ArticleId' => $articleId])->update(['SalesNoPacks' => $dataupdate, 'TotalPieces' => $dataupdate]);
    
    // Insert new record
    $isOutlet = DB::select("SELECT OutletAssign FROM `party` where Id ='" . $data['PartyId'] . "'");
    
    if (!empty($isOutlet) && $isOutlet[0]->OutletAssign == 1) {
        DB::table('artstockstatus')->insert([
            'outletId' => $data['PartyId'],
            'ArticleId' => $articleId,
            'ArticleNumber' => $articleNumber,
            'SalesNoPacks' => $dataupdate,
            'TotalPieces' => $dataupdate,
            'ArticleColor' => $data['ArticleSelectedColor'][0]['Name'],
            'ArticleSize' => implode(',', array_column($data['ArticleSelectedSize'], 'Name')),
            'ArticleRatio' => $data['ArticleRatio'],
            'ArticleOpenFlag' => $data['ArticleOpenFlag'],
            'Title' => $data['Category'],
            'Colorflag' => $colorflag,
            'Subcategory' => $name,
        ]);

        $salesNoPacksData[] = $dataupdate; // Add the value to the array
    }
}

$totalPieces = array_sum($salesNoPacksData);
$salesNoPacksDataString = implode(',', $salesNoPacksData);

            
        }


        // $articleId = $data['ArticleId'];
        // $result = DB::select("SELECT ArticleNumber, brand.Name, subcategory.Name,ArticleOpenFlag FROM article INNER JOIN brand ON article.BrandId = brand.Id INNER JOIN subcategory ON article.SubCategoryId = subcategory.Id WHERE article.Id = '" .  $articleId . "' ");

        // $articleNumber = $result[0]->ArticleNumber;
        // $name = $result[0]->Name;

        // $salesNoPacksData = [];
        // $totalPieces = 0;

        // foreach ($data['ArticleSelectedColor'] as $key => $vl) {
        //     $numberofpacks = $vl["Id"];
        //     $noPacksNewKey = 'NoPacksNew_' . $numberofpacks;
        //     $noPacksKey = 'NoPacks_' . $numberofpacks;

        //     if (isset($data[$noPacksNewKey]) && isset($data[$noPacksKey])) {
        //         $noPacksNewValue = (int) $data[$noPacksNewKey];
        //         $noPacksValue = (int) $data[$noPacksKey];
        //         $salesNoPacksData[] = abs($noPacksValue + $noPacksNewValue);
        //     } else {
        //         $salesNoPacksData[] = 0;
        //     }
        // }

        $totalPieces = array_sum($salesNoPacksData);
        $salesNoPacksDataString = implode(',', $salesNoPacksData);

        $existingRecord = DB::table('artstockstatus')
            ->where(['outletId' => $data['PartyId'], 'ArticleId' => $articleId])
            ->first();

        if ($existingRecord) {
            // Update existing record
            DB::table('artstockstatus')
                ->where(['outletId' => $data['PartyId'], 'ArticleId' => $articleId])
                ->update(['SalesNoPacks' => $salesNoPacksDataString, 'TotalPieces' => $totalPieces]);
        } else {
            // Insert new record
            $isOutlet = DB::select("SELECT OutletAssign FROM `party` where Id ='" . $data['PartyId'] . "'");
            if ($isOutlet[0]->OutletAssign == 1) { 

                                
                if (isset($data['ArticleSelectedColor'][0]['Name'])) {
                    $color = $data['ArticleSelectedColor'][0]['Name'];
                } else {
                    $color = '';
                }
                

            DB::table('artstockstatus')->insert([
                'outletId' => $data['PartyId'],
                'ArticleId' => $articleId,
                'ArticleNumber' => $articleNumber,
                'SalesNoPacks' => $salesNoPacksDataString,
                'TotalPieces' => $totalPieces,
                'ArticleColor' => $color,
                'ArticleSize' => implode(',', array_column($data['ArticleSelectedSize'], 'Name')),
                'ArticleRatio' => $data['ArticleRatio'],
                'ArticleOpenFlag' => $data['ArticleOpenFlag'],
                'Title' => $data['Category'],
                'Subcategory' => $name,
            ]);
            }
        }


        //close

        $partyrecord = Party::where('Id', $data['PartyId'])->first();

        if ($partyrecord->Status == 1) {
            if ($partyrecord->UserId != null) {
                DB::beginTransaction();
                try {
                    $dataresult = DB::select('SELECT c.Colorflag FROM `article` a inner join category c on c.Id=a.CategoryId where a.Id="' . $data['ArticleId'] . '"');
                    $Colorflag = $dataresult[0]->Colorflag;
                    $datanopacks = DB::select('SELECT OutwardNoPacks FROM `so` where ArticleId="' . $data['ArticleId'] . '" and SoNumberId="' . $data['SoId'] . '"');
                    $search = $datanopacks[0]->OutwardNoPacks;
                    $outwardadd = array();
                    $searchString = ',';
                    if (strpos($search, $searchString) !== false) {
                        $string = explode(',', $search);
                        $stringcomma = 1;
                    } else {
                        $search;
                        $stringcomma = 0;
                    }
                    $NoPacks = "";
                    $SalesNoPacks = "";
                    if ($Colorflag == 1) {
                        foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                            $numberofpacks = $vl["Id"];
                            if ($data["NoPacksNew_" . $numberofpacks] != "") {
                                if ($stringcomma == 1) {
                                    if ($string[$key] < $data["NoPacksNew_" . $numberofpacks]) {
                                        return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                                    }
                                    $SalesNoPacks .= ($string[$key] - $data["NoPacksNew_" . $numberofpacks]) . ",";
                                } else {
                                    if ($search < $data["NoPacksNew_" . $numberofpacks]) {
                                        return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                                    }
                                    $SalesNoPacks .= ($search - $data["NoPacksNew_" . $numberofpacks]) . ",";
                                }
                                $NoPacks .= $data["NoPacksNew_" . $numberofpacks] . ",";
                            } else {
                                $NoPacks .= "0,";
                                $SalesNoPacks .= $string[$key] . ",";
                            }
                        }
                    } else {
                        if (isset($data['NoPacksNew'])) {
                            $NoPacks = $data['NoPacksNew'];
                            if ($search < $data['NoPacksNew']) {
                                return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                            }
                            $SalesNoPacks = ($search - $data['NoPacksNew']);
                        } else {
                            return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
                        }
                    }
                    $NoPacks = rtrim($NoPacks, ',');
                    $SalesNoPacks = rtrim($SalesNoPacks, ',');
                    $CheckSalesNoPacks = explode(',', $NoPacks);
                    $tmp = array_filter($CheckSalesNoPacks);
                    if (empty($tmp)) {
                        return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
                    }
                    if ($data['Discount'] == "") {
                        $Discount = 0;
                    } else {
                        $Discount = $data['Discount'];
                    }

                    if ($data['Discount_amount'] == "") {
                        $DiscountinAmount = 0;
                    } else {
                        $DiscountinAmount = $data['Discount_amount'];
                    }


                    if ($data['OutwardNumberId'] == "Add") {
                        $generate_OWNumber = $this->GenerateOWNumber($data['UserId']);
                        $OW_Number = $generate_OWNumber['OW_Number'];
                        $OW_Number_Financial_Id = $generate_OWNumber['OW_Number_Financial_Id'];
                        $OutwardNumberId = DB::table('outwardnumber')->insertGetId(
                            ['OutwardNumber' => $OW_Number, "FinancialYearId" => $OW_Number_Financial_Id, 'SoId' => $data['SoId'], 'UserId' => $data['UserId'], 'OutwardDate' => $data['OutwardDate'], 'GSTAmount' => $data['GST'], 'GSTPercentage' => $data['GST_Percentage'], 'GSTType' => $data['GSTType'], 'Discount' => $Discount, 'Discount_amount' => $DiscountinAmount, 'Remarks' => $data['Remarks'], 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
                        );
                        $sooutletpartyId = DB::select("select u.PartyId , partyuser.Id as UserIdToParty , partyuser.PartyId as UserPartyId from sonumber son left join users u on son.UserId=u.Id left join party p on son.PartyId=p.Id left join users partyuser on p.UserId=partyuser.Id where son.Id = " . $data['SoId'] . "");
                        if ($sooutletpartyId[0]->UserPartyId == null || $sooutletpartyId[0]->PartyId != 0 || $sooutletpartyId[0]->UserPartyId != 0) {
                            if (!is_null($sooutletpartyId[0]->UserPartyId)) {
                                if ($sooutletpartyId[0]->UserPartyId != 0) {
                                    $OUTpartyId = $sooutletpartyId[0]->UserPartyId;
                                    $generate_OutletNumber = $this->GenerateOutletNumber($OUTpartyId);
                                    $Outlet_Number = $generate_OutletNumber['Outlet_Number'];
                                    $Outlet_Number_Financial_Id = $generate_OutletNumber['Outlet_Number_Financial_Id'];
                                    $OutletNumberId = DB::table('outletnumber')->insertGetId(
                                        ['OutwardnumberId' => $OutwardNumberId, 'OutletNumber' => $Outlet_Number, "FinancialYearId" => $Outlet_Number_Financial_Id, 'UserId' => $data['UserId'], 'OutletPartyId' => $data['PartyId'], 'PartyId' => $OUTpartyId, 'OutletDate' => $data['OutwardDate'], 'GSTAmount' => $data['GST'], 'GSTPercentage' => $data['GST_Percentage'], 'Discount' => $Discount, 'Discount_amount' => $DiscountinAmount, 'Address' => null, 'Contact' => null, 'CreatedDate' => date('Y-m-d H:i:s')]
                                    );
                                }
                            }
                        }
                        $userName = Users::where('Id', $data['UserId'])->first();
                        $outwardRec = DB::select("select concat($OW_Number,'/', fn.StartYear,'-',fn.EndYear) as OutwardNumber from outwardnumber otn inner join financialyear fn on fn.Id=otn.FinancialYearId where otn.Id= '" . $OutwardNumberId . "'");
                        UserLogs::create([
                            'Module' => 'Outward',
                            'ModuleNumberId' => $OutwardNumberId,
                            'LogType' => 'Created',
                            'LogDescription' => $userName['Name'] . " " . 'created outward with Outward number' . " " . $outwardRec[0]->OutwardNumber,
                            'UserId' => $userName['Id'],
                            'updated_at' => null
                        ]);
                        $userName = Users::where('Id', $data['UserId'])->first();
                        $outwardRec = DB::select("select concat($OW_Number,'/', fn.StartYear,'-',fn.EndYear) as OutwardNumber from outwardnumber otn inner join financialyear fn on fn.Id=otn.FinancialYearId where otn.Id= '" . $OutwardNumberId . "'");
                        $artRateRecord = Article::where('Id', $data['ArticleId'])->first();
                        UserLogs::create([
                            'Module' => 'Outward',
                            'ModuleNumberId' => $OutwardNumberId,
                            'LogType' => 'Updated',
                            'LogDescription' => $userName['Name'] . " " . 'added article ' . $artRateRecord->ArticleNumber . ' in outward with Outward number' . " " . $outwardRec[0]->OutwardNumber,
                            'UserId' => $userName['Id'],
                            'updated_at' => null
                        ]);
                    } else {
                        $checksonumber = DB::select("SELECT OutwardNumber FROM `outwardnumber` where Id ='" . $data['OutwardNumberId'] . "'");
                        if (!empty($checksonumber)) {
                            $OW_Number = $checksonumber[0]->OutwardNumber;
                            $OutwardNumberId = $data['OutwardNumberId'];

                            DB::table('outwardnumber')
                                ->where('Id', $OutwardNumberId)
                                ->update(['OutwardDate' => $data['OutwardDate'], 'SoId' => $data['SoId'], 'UserId' => $data['UserId'], 'GSTAmount' => $data['GST'], 'GSTPercentage' => $data['GST_Percentage'], 'GSTType' => $data['GSTType'], 'Discount' => $Discount, 'Discount_amount' => $DiscountinAmount, 'Remarks' => $data['Remarks'], 'updated_at' => date('Y-m-d H:i:s')]);
                        }
                        $userName = Users::where('Id', $data['UserId'])->first();
                        $outwardRec = DB::select("select concat(otn.OutwardNumber,'/', fn.StartYear,'-',fn.EndYear) as OutwardNumber from outwardnumber otn inner join financialyear fn on fn.Id=otn.FinancialYearId where otn.Id= '" . $OutwardNumberId . "'");
                        $artRateRecord = Article::where('Id', $data['ArticleId'])->first();
                        UserLogs::create([
                            'Module' => 'Outward',
                            'ModuleNumberId' => $OutwardNumberId,
                            'LogType' => 'Updated',
                            'LogDescription' => $userName['Name'] . " " . 'added article ' . $artRateRecord->ArticleNumber . ' in outward with Outward number' . " " . $outwardRec[0]->OutwardNumber,
                            'UserId' => $userName['Id'],
                            'updated_at' => null
                        ]);
                    }
                    $sonumberdata = DB::select('SELECT count(*) as total, NoPacks  FROM `outward` where OutwardNumberId="' . $OutwardNumberId . '" and ArticleId="' . $data['ArticleId'] . '"');
                    $getnppacks = $sonumberdata[0]->NoPacks;
                    DB::table('so')
                        ->where('ArticleId', $data['ArticleId'])
                        ->where('SoNumberId', $data['SoId'])
                        ->update(['OutwardNoPacks' => $SalesNoPacks]);
                    $articleCheck = DB::select("select OutwardNoPacksCheck from (select OutwardNoPacksCheck(sn.Id, s.ArticleId) as OutwardNoPacksCheck from so s inner join article a on a.Id=s.ArticleId inner join sonumber sn on sn.Id=s.SoNumberId where s.SoNumberId='" . $data['SoId'] . "' and s.ArticleId='" . $data['ArticleId'] . "') as d where OutwardNoPacksCheck=1");
                    if (isset($articleCheck[0]->OutwardNoPacksCheck) == 1) {
                        DB::table('so')
                            ->where('ArticleId', $data['ArticleId'])
                            ->where('SoNumberId', $data['SoId'])
                            ->update(['Status' => 1]);
                    }
                    if (isset($data['OutwardWeight'])) {
                        $OutwardWeight = $data['OutwardWeight'];
                    } else {
                        $OutwardWeight = "";
                    }
                    if ($sonumberdata[0]->total > 0) {
                        $nopacksadded = "";
                        if (strpos($NoPacks, ',') !== false) {
                            $NoPacks1 = explode(',', $NoPacks);
                            $getnppacks = explode(',', $getnppacks);
                            foreach ($getnppacks as $key => $vl) {
                                $nopacksadded .= $NoPacks1[$key] + $vl . ",";
                            }
                        } else {
                            $nopacksadded .= $getnppacks + $NoPacks . ",";
                        }
                        $nopacksadded = rtrim($nopacksadded, ',');
                        DB::table('outward')
                            ->where('OutwardNumberId', $OutwardNumberId)
                            ->where('ArticleId', $data['ArticleId'])
                            ->update(['NoPacks' => $nopacksadded, 'OutwardBox' => $data["OutwardBox"], 'OutwardRate' => $data["OutwardRate"], 'OutwardWeight' => $OutwardWeight, 'updated_at' => date('Y-m-d H:i:s')]);
                        if ($data['ArticleOpenFlag'] == 0) {
                            if (strpos($nopacksadded, ',') !== false) {
                                $nopacksadded = explode(',', $nopacksadded);
                                foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                                    $numberofpacks = $vl["Id"];
                                    $res = DB::select("select Id from outwardpacks where ColorId='" . $numberofpacks . "' and ArticleId='" . $data['ArticleId'] . "'");
                                    DB::table('outwardpacks')
                                        ->where('Id', $res[0]->Id)
                                        ->update(['NoPacks' => $nopacksadded[$key], 'UpdatedDate' => date('Y-m-d H:i:s')]);
                                }
                            } else {
                                foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                                    $numberofpacks = $vl["Id"];
                                    $res = DB::select("select Id from outwardpacks where ColorId='" . $numberofpacks . "' and ArticleId='" . $data['ArticleId'] . "'");
                                    DB::table('outwardpacks')
                                        ->where('Id', $res[0]->Id)
                                        ->update(['NoPacks' => $nopacksadded, 'UpdatedDate' => date('Y-m-d H:i:s')]);
                                }
                            }
                        } else {
                            $datavl = DB::select('select Id from outward where OutwardNumberId = "' . $OutwardNumberId . '" and ArticleId = "' . $data['ArticleId'] . '"');
                            DB::table('outwardpacks')
                                ->where('Id', $datavl[0]->Id)
                                ->update(['NoPacks' => $nopacksadded, 'UpdatedDate' => date('Y-m-d H:i:s')]);
                        }
                    } else {
                        $outwardadd['OutwardNumberId'] = $OutwardNumberId;
                        $outwardadd["ArticleId"] = $data['ArticleId'];
                        $outwardadd["NoPacks"] = $NoPacks;
                        $outwardadd["OutwardBox"] = $data["OutwardBox"];
                        $outwardadd['OutwardRate'] = $data["OutwardRate"];
                        $outwardadd['OutwardWeight'] = $OutwardWeight;
                        $outwardadd["ArticleOpenFlag"] = $data["ArticleOpenFlag"];
                        $outwardadd["PartyId"] = $data["PartyId"];
                        $outwardadd["PartyDiscount"] = $data['PartyDiscount'];
                        $field = Outward::where('OutwardNumberId', $OutwardNumberId)->where('ArticleId', $data['ArticleId'])->first();
                        if (!$field) {
                            $field = Outward::create($outwardadd);
                        }
                        $outward_insertedid = $field->id;
                        $partydata = DB::select('SELECT count(*) as TotalRow FROM `party` where OutletAssign = 1 and Id = ' . $data["PartyId"]);
                        if ($partydata[0]->TotalRow > 0) {
                            $trandata = DB::select('SELECT count(*) as TotalRow FROM `transportoutlet` where OutwardNumberId="' . $OutwardNumberId . '"');
                            if ($trandata[0]->TotalRow == 0) {
                                Transportoutlet::create(array("OutwardNumberId" => $OutwardNumberId, "PartyId" => $data["PartyId"], "TransportStatus" => 0));
                            }
                        }
                        if ($data['ArticleOpenFlag'] == 0) {
                            if (strpos($NoPacks, ',') !== false) {
                                $NoPacks = explode(',', $NoPacks);
                                foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                                    $numberofpacks = $vl["Id"];
                                    DB::table('outwardpacks')->insertGetId(
                                        ['ArticleId' => $data['ArticleId'], 'ColorId' => $numberofpacks, 'OutwardId' => $outward_insertedid, 'NoPacks' => $NoPacks[$key], 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]
                                    );
                                }
                            } else {
                                foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                                    $numberofpacks = $vl["Id"];
                                    DB::table('outwardpacks')->insertGetId(
                                        ['ArticleId' => $data['ArticleId'], 'ColorId' => $numberofpacks, 'OutwardId' => $outward_insertedid, 'NoPacks' => $NoPacks, 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]
                                    );
                                }
                            }
                        } else {
                            $datavl = DB::select('select Id from outward where OutwardNumberId = "' . $OutwardNumberId . '" and ArticleId = "' . $data['ArticleId'] . '"');
                            DB::table('outwardpacks')->insertGetId(
                                ['ArticleId' => $data['ArticleId'], 'ColorId' => 0, 'OutwardId' => $outward_insertedid, 'NoPacks' => $NoPacks, 'PartyId' => $data["PartyId"], 'CreatedDate' => date('Y-m-d H:i:s'), 'UpdatedDate' => date('Y-m-d H:i:s')]
                            );
                        }
                    }
                    DB::commit();
                    return response()->json(array("OutwardNumberId" => $OutwardNumberId, "OW_Number" => $OW_Number), 200);
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json(['$e' => $e], 200);
                }
            } else {
                return response()->json(['errorpartysales' => 1], 200);
            }
        } else {
            return response()->json(['errorparty' => 1], 200);
        }
    }


    public function OutwardListFromOWNO($Id, Request $request)
    {

        $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select('select count(*) as Total from (SELECT o.Id , o.PartyDiscount, o.OutwardNumberId, o.OutwardBox, o.OutwardRate,o.OutwardWeight, owdn.OutwardNumber, owdn.OutwardDate, owdn.GSTAmount, owdn.GSTPercentage, owdn.GSTType, o.NoPacks, a.ArticleNumber, a.Id as ArticleId,c.Title as Category,   concat(owdn.OutwardNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as OW_Number_FinancialYear FROM `outward` o inner join outwardnumber owdn on o.OutwardNumberId=owdn.Id inner join article a on a.Id=o.ArticleId inner join category c on c.Id=a.CategoryId inner join financialyear fn on fn.Id=owdn.FinancialYearId where o.OutwardNumberId="' . $Id . '") as d');
        $vnTotal = $vnddataTotal[0]->Total;
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "where d.NoPacks like '%" . $search['value'] . "%' OR d.Category like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%' OR d.Category like '%" . $search['value'] . "%' ";
            $vnddataTotalFilter = DB::select('select count(*) as Total from (SELECT o.Id , o.PartyDiscount, o.OutwardNumberId, o.OutwardBox, o.OutwardRate,o.OutwardWeight, owdn.OutwardNumber, owdn.OutwardDate, owdn.GSTAmount, owdn.GSTPercentage, owdn.GSTType, o.NoPacks, a.ArticleNumber, a.Id as ArticleId,c.Title as Category,   concat(owdn.OutwardNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as OW_Number_FinancialYear FROM `outward` o inner join outwardnumber owdn on o.OutwardNumberId=owdn.Id inner join article a on a.Id=o.ArticleId inner join category c on c.Id=a.CategoryId inner join financialyear fn on fn.Id=owdn.FinancialYearId where o.OutwardNumberId="' . $Id . '") as d ' . $searchstring);
            $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
        } else {
            $searchstring = "";
            $vnddataTotalFilterValue = $vnTotal;
        }
        $column = $data["order"][0]["column"];
        switch ($column) {
            case 1:
                $ordercolumn = "d.ArticleNumber";
                break;
            case 2:
                $ordercolumn = "d.Category";
                break;
            case 3:
                $ordercolumn = "d.NoPacks";
                break;
            case 4:
                $ordercolumn = "d.OutwardBox";
                break;

            case 5:
                $ordercolumn = "d.OutwardRate";
                break;

            case 6:
                $ordercolumn = "d.PartyDiscount";
                break;

            default:
                $ordercolumn = "d.OutwardDate";
                break;
        }
        $order = "";
        if ($data["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
        }
        $vnddata = DB::select('select d.* from (SELECT o.Id , o.PartyDiscount, o.OutwardNumberId, o.OutwardBox, o.OutwardRate,o.OutwardWeight, owdn.OutwardNumber, owdn.OutwardDate, owdn.GSTAmount, owdn.GSTPercentage, owdn.GSTType, o.NoPacks, a.ArticleNumber, a.Id as ArticleId,c.Title as Category,   concat(owdn.OutwardNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as OW_Number_FinancialYear FROM `outward` o inner join outwardnumber owdn on o.OutwardNumberId=owdn.Id inner join article a on a.Id=o.ArticleId inner join category c on c.Id=a.CategoryId inner join financialyear fn on fn.Id=owdn.FinancialYearId where o.OutwardNumberId="' . $Id . '") as d ' . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length);



        return array(
            'datadraw' => $data["draw"],
            'recordsTotal' => $vnTotal,
            'recordsFiltered' => $vnddataTotalFilterValue,
            'response' => 'success',
            'startnumber' => $startnumber,
            'search' => count($vnddata),
            'data' => $vnddata,
        );


    }
    // public function OutwardListFromOWNO($Id)
    // {
    //     return DB::select('SELECT o.Id , o.PartyDiscount, o.OutwardNumberId, o.OutwardBox, o.OutwardRate,o.OutwardWeight, owdn.OutwardNumber, owdn.OutwardDate, owdn.GSTAmount, owdn.GSTPercentage, owdn.GSTType, o.NoPacks, a.ArticleNumber, a.Id as ArticleId,c.Title as Category,   concat(owdn.OutwardNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as OW_Number_FinancialYear FROM `outward` o inner join outwardnumber owdn on o.OutwardNumberId=owdn.Id inner join article a on a.Id=o.ArticleId inner join category c on c.Id=a.CategoryId inner join financialyear fn on fn.Id=owdn.FinancialYearId where o.OutwardNumberId="' . $Id . '"');
    // }

    public function GetOutwardIdWise($id)
    {
        return DB::select('SELECT o.*, p.Discount as PartyDiscount, concat(own.OutwardNumber, \'/\',fn.StartYear,\'-\',fn.EndYear) as OutwardNumber From outward o inner join outwardnumber own on own.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=own.FinancialYearId inner join party p on p.Id=o.PartyId WHERE o.Id = ' . $id . '');
    }

    public function GetArticle()
    {
        return DB::select('select * from (SELECT art.*, s.ArticleId, inw.NoPacks, inw.SalesNoPacks, SalesNoPacksCheck(inw.Id) as SalesNoPacksCheck From inward inw left join so s on s.ArticleId=inw.ArticleId left join article art on art.Id=inw.ArticleId group by inw.Id) as t where SalesNoPacksCheck!=1');
    }

    public function OutwardDateGstFromOWNO($id)
    {
        $getoutwarddata = DB::select("SELECT own.*, u.Name as UserName , p.UserId as PartyUserId ,  concat( sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumberGen, concat(own.OutwardNumber, '/',fn1.StartYear,'-',fn1.EndYear) as OW_Number_FinancialYear FROM `outwardnumber` own inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id =sn.PartyId  inner join financialyear fn on fn.Id=sn.FinancialYearId inner join financialyear fn1 on fn1.Id=own.FinancialYearId inner join users u on u.Id=sn.UserId where own.Id='" . $id . "'");
        foreach ($getoutwarddata as $getoutward) {
            if (!is_null($getoutward->PartyUserId)) {
                $user = Users::where('Id', $getoutward->PartyUserId)->first();
                $getoutward->SoNumber = str_replace(' ', '', $user->Name . $getoutward->SoNumberGen);
            } else {
                $getoutward->SoNumber = str_replace(' ', '', $getoutward->UserName . $getoutward->SoNumberGen);
            }
        }
        return $getoutwarddata;
    }

    public function GeOutward($UserId)
    {
        $userrole = DB::select("SELECT Role FROM `users`where Id='" . $UserId . "'");
        if ($userrole[0]->Role == 2) {
            $wherecustom = "";
        } else {
            $wherecustom = "where own.UserId='" . $UserId . "'";
        }
        return DB::select("SELECT GetTotalOutwardOrderPieces(own.Id) as TotalOutwardPieces, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber,  own.OutwardDate, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId " . $wherecustom . " group by o.OutwardNumberId order by o.Id desc");
    }

    public function PostOutward(Request $request)
    {
        $data = $request->all();
        $search = $data['dataTablesParameters']["search"];
        $startnumber = $data['dataTablesParameters']["start"];
        $vnddataTotal = DB::select("select count(*) as Total from (SELECT own.Id, p.Name, own.SoId, o.OutwardNumberId FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId group by o.OutwardNumberId order by o.Id desc) as d");
        $vntotal = $vnddataTotal[0]->Total;
        $length = $data['dataTablesParameters']["length"];
        $wherecustom = "";
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "where d.OutwardNumber like '%" . $search['value'] . "%' OR d.SoNumber like '%" . $search['value'] . "%' OR cast(d.OutwardDate as char) like '%" . $search['value'] . "%' OR d.Name like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%'";
            // $vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT sn.UserId, own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, DATE_FORMAT(own.OutwardDate, \"%d/%m/%Y\") as OutwardDate, concat(IFNULL(partyuser.Name,u.Name),sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId  left join users partyuser on partyuser.Id=p.UserId  inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId group by o.OutwardNumberId order by o.Id desc) as d " . $searchstring);
            
            $vnddataTotalFilter = DB::select("
    SELECT 
        COUNT(*) AS Total
    FROM (
        SELECT 
            sn.UserId, own.Id, p.Name, own.SoId, o.OutwardNumberId,
            GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber,
            CONCAT(own.OutwardNumber, '/', fn.StartYear,'-', fn.EndYear) as OutwardNumber,
            DATE_FORMAT(own.OutwardDate, \"%d/%m/%Y\") as OutwardDate,
            CONCAT(IFNULL(partyuser.Name, u.Name), sn.SoNumber, '/', fn.StartYear,'-', fn.EndYear) as SoNumber
        FROM 
            `outward` o
            INNER JOIN `article` a ON a.Id = o.ArticleId
            LEFT JOIN `outwardnumber` own ON o.OutwardNumberId = own.Id
            INNER JOIN `sonumber` sn ON sn.Id = own.SoId
            INNER JOIN `party` p ON p.Id = sn.PartyId
            LEFT JOIN `users` partyuser ON partyuser.Id = p.UserId
            INNER JOIN `users` u ON u.Id = sn.UserId
            INNER JOIN `financialyear` fn ON fn.Id = own.FinancialYearId
            INNER JOIN `financialyear` fn1 ON fn1.Id = sn.FinancialYearId
        GROUP BY o.OutwardNumberId
        ORDER BY o.Id DESC
    ) AS d $searchstring
");

            $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
        } else {
            $searchstring = "";
            $vnddataTotalFilterValue = $vntotal;
        }
        $column = $data['dataTablesParameters']["order"][0]["column"];
        switch ($column) {
            case 1:
                $ordercolumn = "d.OutwardNumberId";
                break;
            case 2:
                $ordercolumn = "d.SoNumber";
                break;
            case 3:
                $ordercolumn = "d.Name";
                break;
            case 5:
                $ordercolumn = "date(d.owdate)";
                break;
            default:
                $ordercolumn = "date(d.owdate)";
                break;
        }
        $order = "";
        if ($data['dataTablesParameters']["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data['dataTablesParameters']["order"][0]["dir"];
        }
        // $vnddata = DB::select("select d.* from (SELECT CountNoPacks(GROUP_CONCAT(CONCAT(o.NoPacks) ORDER BY a.Id SEPARATOR ',')) as TotalOutwardPieces,   own.Id, p.Name, own.SoId, o.OutwardNumberId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, DATE_FORMAT(own.OutwardDate, '%d/%m/%Y') as OutwardDate, concat(IFNULL(partyuser.Name,u.Name),sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `outward` o inner join article a on a.Id=o.ArticleId left join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber sn on sn.Id=own.SoId inner join party p on p.Id=sn.PartyId left join users partyuser on partyuser.Id=p.UserId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=own.FinancialYearId inner join financialyear fn1 on fn1.Id=sn.FinancialYearId group by o.OutwardNumberId) as d " . $wherecustom . " " . $searchstring . " " . $order . " limit " . $data['dataTablesParameters']["start"] . "," . $length);
        $rawQuery = "
        select * from ( SELECT
            CountNoPacks(GROUP_CONCAT(CONCAT(o.NoPacks) ORDER BY a.Id SEPARATOR ',')) as TotalOutwardPieces,
            own.Id,
            p.Name,
            own.SoId,
            o.OutwardNumberId,
            GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY own.Id SEPARATOR ',') as ArticleNumber,
            CONCAT(own.OutwardNumber, '/', fn.StartYear, '-', fn.EndYear) as OutwardNumber,
            DATE_FORMAT(own.OutwardDate, '%d/%m/%Y') as OutwardDate,
            CONCAT(IFNULL(partyuser.Name, u.Name), sn.SoNumber, '/', fn.StartYear, '-', fn.EndYear) as SoNumber
        FROM
            `outward` o
            INNER JOIN article a ON a.Id = o.ArticleId
            LEFT JOIN outwardnumber own ON o.OutwardNumberId = own.Id
            INNER JOIN sonumber sn ON sn.Id = own.SoId
            INNER JOIN party p ON p.Id = sn.PartyId
            LEFT JOIN users partyuser ON partyuser.Id = p.UserId
            INNER JOIN users u ON u.Id = sn.UserId
            INNER JOIN financialyear fn ON fn.Id = own.FinancialYearId
            INNER JOIN financialyear fn1 ON fn1.Id = sn.FinancialYearId
        GROUP BY
            o.OutwardNumberId ) as d
        " . $wherecustom . " " . $searchstring . " " . $order . " LIMIT " . $data['dataTablesParameters']["start"] . "," . $length;
    
    // Remove 'd.' from the ORDER BY clause
    $rawQuery = str_replace('order by d.OutwardNumberId', 'order by OutwardNumberId', $rawQuery);
    
    $vnddata = DB::select($rawQuery);
    
        $TotalAmount = 0;
        $totalPacks = 0;
        foreach ($vnddata as $vnd) {
            $vnd->TotalOutwardPieces = 0;
            $outwards = Outward::where('OutwardNumberId', $vnd->OutwardNumberId)->get();
            foreach ($outwards as $outward) {
                if (strpos($outward->NoPacks, ',') !== false) {
                    $packs = explode(",", $outward->NoPacks);
                } else {
                    $packs = [(int) $outward->NoPacks];
                }
                
                $totalPacks += array_sum($packs);
                
                foreach ($packs as $pack) {
                    $amountWithoutDiscount = $pack * $outward->OutwardRate;
                
                    if ($outward->PartyDiscount) {
                        $partyDiscountAmount = ($amountWithoutDiscount * $outward->PartyDiscount) / 100;
                        $AmountWithPartyDiscount = $amountWithoutDiscount - $partyDiscountAmount;
                        $TotalAmount += $AmountWithPartyDiscount;
                    } else {
                        $TotalAmount += $amountWithoutDiscount;
                    }
                }
                
            }

            $outwardData = OutwardNumber::where('Id', $vnd->OutwardNumberId)->first();
            $TotalAmount = $TotalAmount - $outwardData->Discount_amount;


            if (!is_null($outwardData->GSTPercentage)) {
                $GSTValue = (($TotalAmount * $outwardData->GSTPercentage) / 100);
                $TotalAmount = $TotalAmount + $GSTValue;
            }

            // if($outwardData->Discount_amount > 0 || $outwardData->Discount_amount != Null){
            //     if (!is_null($outwardData->GSTPercentage)) {
            //         $GSTValue = (($TotalAmount * $outwardData->GSTPercentage) / 100);
            //         $TotalAmount = $TotalAmount + $GSTValue;
            //     }
            // }else{
            //     if (!is_null($outwardData->GSTPercentage)) {
            //         $GSTValue = (($TotalAmount * $outwardData->GSTPercentage) / 100);
            //         $TotalAmount = $TotalAmount + $GSTValue;
            //     }
            // }

            if ($outwardData->Discount != 0) {
                $discountValue = (($TotalAmount * $outwardData->Discount) / 100);
                $TotalAmount = $TotalAmount - $discountValue;
            }
            if (!is_null($outwardData->GSTAmount)) {
                $TotalAmount = $TotalAmount + $outwardData->GSTAmount;
            }
            $totalRoundoffAmmount = $this->splitter(number_format($TotalAmount, 2, '.', ''));

            $vnds = $totalRoundoffAmmount['TotalRoundAmount'];
            $vnds = str_replace(',', '', $vnds); // Remove the comma from the string
            $vnds = floatval($vnds); // Convert the string to a float


            $vnd->TotalAmount = $vnds;

            // return $vnds - $outwardData->Discount_amount;

            $vnd->TotalOutwardPieces = $totalPacks;
            $TotalAmount = 0;
            $totalPacks = 0;
        }
        return array(
            'datadraw' => $data['dataTablesParameters']["draw"],
            'recordsTotal' => $vntotal,
            'recordsFiltered' => $vnddataTotalFilterValue,
            'response' => 'success',
            'startnumber' => $startnumber,
            'search' => count($vnddata),
            'data' => $vnddata,
        );
    }

    public function Deleteoutward($id, $ArticleId, $LoggedId)
    {


        //update artstockstatus yashvi
        $data = DB::table('outward')
            ->join('article', 'outward.ArticleId', '=', 'article.Id')
            ->where('outward.Id', '=', $id)
            ->select('outward.*', 'article.ArticleColor')
            ->first();

        if (!empty($data) && $data->ArticleOpenFlag == 0) {
            $salesNoPacksData = []; // Initialize outside the loop
            $data1 = DB::select("SELECT * FROM `artstockstatus` WHERE ArticleId = $data->ArticleId AND outletId = $data->PartyId");
            $totalPieces = 0;
            if (!empty($data1) && isset($data1[0]->SalesNoPacks)) {
                $articleColors = json_decode($data->ArticleColor, true);

                foreach ($articleColors as $key => $vl) {
                    $numberofpacks = $vl["Id"];
                    $articleId = $data->ArticleId;
                    $noPacksNewKey = $data1[0]->SalesNoPacks;
                    $noPacksKey = $data->NoPacks;

                    $noPacksNewKey = explode(',', $noPacksNewKey);
                    $noPacksKey = explode(',', $noPacksKey);

                    if (count($noPacksNewKey) === count($noPacksKey)) {
                        $salesNoPacksData = [];
                        for ($i = 0; $i < count($noPacksNewKey); $i++) {
                            $salesNoPacksData[] = $noPacksNewKey[$i] - $noPacksKey[$i];
                        }
                    } else {
                        $salesNoPacksData[] = 0;
                    }

                    $salesNoPacksDataString = implode(',', $salesNoPacksData);
                    $totalPieces = array_sum($salesNoPacksData);

                    DB::table('artstockstatus')
                        ->where(['outletId' => $data->PartyId, 'ArticleId' => $data->ArticleId])
                        ->update(['SalesNoPacks' => $salesNoPacksDataString, 'TotalPieces' => $totalPieces]);
                }
            }
        } else {
            $data1 = DB::table('artstockstatus')
            ->where(['outletId' => $data->PartyId])
            ->where(['ArticleId' => $data->ArticleId])
            ->value('SalesNoPacks');
            

            if ($data1 == null) {
                $dataupdate = $data->NoPacks;
            } else {
                 $dataupdate = $data->NoPacks + $data1;
            }
            

            DB::table('artstockstatus')->updateOrInsert(
                [
                    'outletId' => $data->PartyId,
                    'ArticleId' => $data->ArticleId
                ],
                [
                    'SalesNoPacks' => $dataupdate,
                    'TotalPieces' => $dataupdate
                ]
            );
            // DB::table('artstockstatus')->where(['outletId' => $data[0]->PartyId, 'ArticleId' => $data[0]->ArticleId])->update(['SalesNoPacks' => $dataupdate, 'TotalPieces' => $dataupdate]);
        }

        //close
        $datanopacks = DB::select('SELECT s.Id, s.NoPacks, s.OutwardNoPacks, own.SoId, o.OutwardNumberId, o.NoPacks, o.ArticleId, own.outwardnumber FROM `outward` o inner join outwardnumber own on own.Id=o.OutwardNumberId inner join so s on s.SoNumberId=own.SoId where o.Id="' . $id . '" and s.ArticleId="' . $ArticleId . '"');
        $search = $datanopacks[0]->NoPacks;
        $SoId = $datanopacks[0]->SoId;
        $OutwardNoPacks = $datanopacks[0]->OutwardNoPacks;
        $OutwardNumberId = $datanopacks[0]->OutwardNumberId;
        $searchString = ',';
        $AddSalesNoPacks = "";
        if (strpos($search, $searchString) !== false) {
            $string = explode(',', $search);
            $OutwardNoPacks = explode(',', $OutwardNoPacks);
            foreach ($string as $key => $vl) {
                $AddSalesNoPacks .= ($OutwardNoPacks[$key] + $vl) . ",";
            }
        } else {
            $AddSalesNoPacks .= ($search + $OutwardNoPacks) . ",";
        }
        $AddSalesNoPacks = rtrim($AddSalesNoPacks, ',');
        DB::beginTransaction();
        try {
            $userName = Users::where('Id', $LoggedId)->first();
            $outwardRec = DB::select("select a.ArticleNumber, otn.Id as OutwardNumberId, concat(otn.OutwardNumber,'/', fn.StartYear,'-',fn.EndYear) as Outwardnumber from outward o inner join article a on a.Id=o.ArticleId inner join outwardnumber otn on otn.Id=o.OutwardNumberId inner join financialyear fn on fn.Id=otn.FinancialYearId where o.Id= '" . $id . "'");
            UserLogs::create([
                'Module' => 'Outward',
                'ModuleNumberId' => $outwardRec[0]->OutwardNumberId,
                'LogType' => 'Deleted',
                'LogDescription' => $userName['Name'] . ' deleted article ' . $outwardRec[0]->ArticleNumber . ' from Outward Number ' . $outwardRec[0]->Outwardnumber,
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);
            DB::table('outward')
                ->where('Id', '=', $id)
                ->delete();
            DB::table('so')
                ->where('ArticleId', $ArticleId)
                ->where('SoNumberId', $SoId)
                ->update(['OutwardNoPacks' => $AddSalesNoPacks, 'Status' => 0]);
            DB::table('outwardpacks')
                ->where('OutwardId', '=', $id)
                ->delete();
            $ot = DB::select('select count(*) as Total from outward where OutwardNumberId=' . $OutwardNumberId);
            if ($ot[0]->Total == 0) {
                DB::table('transportoutlet')
                    ->where('OutwardNumberId', '=', $OutwardNumberId)
                    ->delete();
            }
            DB::commit();
            return response()->json("SUCCESS", 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json("", 200);
        }
    }

    public function DeleteOWNumber($OWNO, $SOID, $LoggedId)
    {
        $data = DB::table('outward')
            ->join('article', 'outward.ArticleId', '=', 'article.Id')
            ->where('outward.OutwardNumberId', '=', $OWNO)
            ->select('outward.*', 'article.ArticleColor')
            ->first();
            


        if (!empty($data) && $data->ArticleOpenFlag == 0) {
            $salesNoPacksData = []; // Initialize outside the loop
            foreach ($data as $d) {
                $data1 = DB::select("SELECT * FROM `artstockstatus` where ArticleId = $data->ArticleId and outletId = $data->PartyId ");
                $totalPieces = 0;

                if (!empty($data1) && isset($data1[0]->SalesNoPacks)) {
                    $salesNoPacksData = []; // Initialize outside the loop
                    $data1 = DB::select("SELECT * FROM `artstockstatus` where ArticleId =  $data->ArticleId and outletId =   $data->PartyId ");
                    $totalPieces = 0;
                    $articleColors = json_decode($data->ArticleColor, true);


                    $articleId = $data->ArticleId;
                    $noPacksNewKey = $data1[0]->SalesNoPacks;
                    $noPacksKey = $data->NoPacks;

                    $noPacksNewKey = explode(',', $noPacksNewKey);
                    $noPacksKey = explode(',', $noPacksKey);

                    if (count($noPacksNewKey) === count($noPacksKey)) {
                        for ($i = 0; $i < count($noPacksNewKey); $i++) {
                            $salesNoPacksData[] = $noPacksNewKey[$i] - $noPacksKey[$i];
                        }
                    } else {
                        $salesNoPacksData[] = 0;
                    }


                    $salesNoPacksDataString = implode(',', $salesNoPacksData);
                    $totalPieces = array_sum($salesNoPacksData);

                    DB::table('artstockstatus')
                        ->where(['outletId' => $data->PartyId, 'ArticleId' => $articleId])
                        ->update(['SalesNoPacks' => $salesNoPacksDataString, 'TotalPieces' => $totalPieces]);
                }
            }

        } else {
            foreach ($data as $d) {
                $data1 = DB::select("SELECT * FROM `artstockstatus` where ArticleId = $d->ArticleId and outletId =  $d->PartyId ");
                $dataupdate = $data->NoPacks - $data1[0]->SalesNoPacks;
                DB::table('artstockstatus')->where(['outletId' => $d->PartyId])->where(['ArticleId' => $d->ArticleId])->update(['SalesNoPacks' => $dataupdate, 'TotalPieces' => $dataupdate]);
            }

        }
        $checkdata = DB::select('SELECT count(*) as TotalRow FROM `outwardnumber` otn inner join outward o on o.OutwardNumberId=otn.Id inner join salesreturn s on s.OutwardId=o.Id where otn.Id="' . $OWNO . '"');
        if ($checkdata[0]->TotalRow > 0) {
            return response()->json(array("id" => "", "AssignSalseReturn" => "true"), 200);
        } else {
            DB::beginTransaction();
            try {
                $solist = DB::select('select * from (SELECT s.Id, s.SoNumberId, s.ArticleId, s.OutwardNoPacks, a.ArticleNumber, (select Id from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as OId, (select NoPacks from outward where OutwardNumberId=own.Id and ArticleId=s.ArticleId) as NoPacks FROM `so` s inner join outwardnumber own on own.SoId=s.SoNumberId inner join article a on a.Id=s.ArticleId where s.SoNumberId="' . $SOID . '" and s.ArticleId in (select ArticleId from outward where OutwardNumberId="' . $OWNO . '")) as d where OId IS NOT NULL group by Id');
                foreach ($solist as $vl) {
                    $ArticleId = $vl->ArticleId;
                    $search = $vl->NoPacks;
                    $SoId = $vl->SoNumberId;
                    $OId = $vl->OId;
                    $OutwardNoPacks = $vl->OutwardNoPacks;
                    $searchString = ',';
                    $AddSalesNoPacks = "";
                    if (strpos($search, $searchString) !== false) {
                        $string = explode(',', $search);
                        $OutwardNoPacks = explode(',', $OutwardNoPacks);
                        foreach ($string as $key => $vl) {
                            $AddSalesNoPacks .= ($OutwardNoPacks[$key] + $vl) . ",";
                        }
                    } else {
                        $AddSalesNoPacks .= ($search + $OutwardNoPacks) . ",";
                    }
                    $AddSalesNoPacks = rtrim($AddSalesNoPacks, ',');
                    DB::table('outward')
                        ->where('Id', '=', $OId)
                        ->delete();
                    DB::table('outwardpacks')
                        ->where('OutwardId', '=', $OId)
                        ->delete();
                    DB::table('so')
                        ->where('ArticleId', $ArticleId)
                        ->where('SoNumberId', $SoId)
                        ->update(['OutwardNoPacks' => $AddSalesNoPacks, 'Status' => 0]);
                }
                $outletRecord = OutletNumber::where('OutwardnumberId', $OWNO)->first();
                if ($outletRecord) {
                    OutletNumber::where('OutwardnumberId', $OWNO)->delete();
                }
                $userName = Users::where('Id', $LoggedId)->first();
                $outwardRec = DB::select("select concat(otn.OutwardNumber,'/', fn.StartYear,'-',fn.EndYear) as Outwardnumber from outwardnumber otn inner join financialyear fn on fn.Id=otn.FinancialYearId where otn.Id= '" . $OWNO . "'");
                DB::table('outwardnumber')
                    ->where('Id', '=', $OWNO)
                    ->delete();
                DB::table('transportoutlet')
                    ->where('OutwardNumberId', '=', $OWNO)
                    ->delete();
                UserLogs::create([
                    'Module' => 'Outward',
                    'ModuleNumberId' => $OWNO,
                    'LogType' => 'Deleted',
                    'LogDescription' => $userName['Name'] . " " . 'deleted outward with Outward number' . " " . $outwardRec[0]->Outwardnumber,
                    'UserId' => $userName['Id'],
                    'updated_at' => null
                ]);
                DB::commit();
                return response()->json(array("Id" => "SUCCESS"), 200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(array("Id" => ""), 200);
            }
        }
    }

    public function UpdateOutward(Request $request)
    {

        $partyid = $request->PartyId;
        

        // $q = DB::select("SELECT party.token , party.Name FROM party WHERE party.Id = ?", [$partyid]);
    
        
        // if (empty($q)) {
        //     return response()->json(['error' => 'Party not found'], 404);
        // }
        
        // $registrationToken = $registrationToken = $q[0]->token;;
        // $title = 'Outward';
        // $body = 'Your order is now outward';
        
        // if (!$this->isExpoPushToken($registrationToken)) {
        //     return response()->json(['error' => 'Invalid Expo Push Token'], 400);
        // }
        
        // $message = [
        //     'to' => $registrationToken,
        //     'sound' => 'default',
        //     'title' => $title ?: 'Notification Title',
        //     'body' => $body ?: 'Notification Body',
        //     'priority' => 'high',
        //     'data' => ['additionalData' => 'optional data'],
        // ];
        
        // try {
        //     $response = $this->sendPushNotifications([$message]);
        //     \Log::info("Notification sent successfully: " . json_encode($response));
        //     return response()->json(['message' => 'Notification sent successfully'], 200);
        // } catch (\Exception $e) {
        //     \Log::error("Error sending notification: " . $e->getMessage());
        //     return response()->json(['error' => 'Internal Server Error'], 500);
        // }

        
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $data = $request->all();
        // return $data;
        //yashvi

        $articleId = $data['ArticleId'];
        $result = DB::select("SELECT article.ArticleNumber,brand.Name,subcategory.Name,article.ArticleOpenFlag,category.Colorflag FROM article Left JOIN brand ON article.BrandId = brand.Id Left JOIN subcategory ON article.SubCategoryId = subcategory.Id Left JOIN category ON article.ArticleOpenFlag = category.ArticleOpenFlag  WHERE article.Id = '" . $articleId . "' ");
        if ($result[0]->ArticleOpenFlag == 0) {
            // Code 2
            $articleNumber = $result[0]->ArticleNumber;
            $name = $result[0]->Name;
            $colorflag = $result[0]->Colorflag;

            $existingRecord = DB::table('artstockstatus')
                ->where(['outletId' => $data['PartyId'], 'ArticleId' => $articleId])
                ->get();

                //working code yaashviii

                // if ($existingRecord) {
                //     $getresult = DB::select("SELECT SalesNoPacks FROM `artstockstatus` WHERE outletId = '" . $data["PartyId"] . "' AND ArticleId = " . $articleId);
                    
                //     if (!empty($getresult)) {
                //         $salesNoPacks = $getresult[0]->SalesNoPacks;
                //         $dataupdate = $salesNoPacks + $data['NoPacksNew'];
                //         DB::table('artstockstatus')
                //             ->where(['outletId' => $data['PartyId']])
                //             ->where(['ArticleId' => $articleId])
                //             ->update(['SalesNoPacks' => $dataupdate, 'TotalPieces' => $dataupdate]);
                //     } else {
                //         // Handle the case where no record was found for the given conditions
                //     }
                // } else {
                //     $dataupdate = $data['NoPacks'] + $data['NoPacksNew'];
                //     DB::table('artstockstatus')
                //         ->where(['outletId' => $data['PartyId']])
                //         ->where(['ArticleId' => $articleId])
                //         ->update(['SalesNoPacks' => $dataupdate, 'TotalPieces' => $dataupdate]);
                // }


                                    //working code yaashviii colorwise
            if ($existingRecord) {
                $getresult = DB::select("SELECT SalesNoPacks FROM `artstockstatus` WHERE outletId = '" . $data["PartyId"] . "' AND ArticleId = " . $articleId);
                if (!empty($getresult)) {
                    $GetNoPacksString = $getresult[0]->SalesNoPacks;
                    $GetNoPacksArray = explode(',', $GetNoPacksString);
                } else {
                    $GetNoPacksArray = '';
                }
                $salesNoPacksData = [];
                $totalPieces = 0;


                foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                    $numberofpacks = $vl["Id"];
                    $noPacksNewKey = 'NoPacksNew_' . $numberofpacks;
                    $noPacksKey = 'NoPacks_' . $numberofpacks;

                    if (isset($data[$noPacksNewKey]) && isset($data[$noPacksKey])) {
                        $noPacksNewValue = (int) $data[$noPacksNewKey];
                        $noPacksValue = (int) $data[$noPacksKey];
                        
                        $salesNoPacksData[] =   $noPacksNewValue;
                        
                    } else {
                        $salesNoPacksData[] = 0;
                    }
                }

                $totalPieces = array_sum($salesNoPacksData);
                $salesNoPacksDataString = implode(',', $salesNoPacksData);

                // Update existing record
                DB::table('artstockstatus')
                    ->where(['outletId' => $data['PartyId'], 'ArticleId' => $articleId])
                    ->update(['SalesNoPacks' => $salesNoPacksDataString, 'TotalPieces' => $totalPieces]);
            } else {

                $salesNoPacksData = [];
                $totalPieces = 0;

                foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                    $numberofpacks = $vl["Id"];
                    $noPacksNewKey = 'NoPacksNew_' . $numberofpacks;
                    $noPacksKey = 'NoPacks_' . $numberofpacks;

                    if (isset($data[$noPacksNewKey]) && isset($data[$noPacksKey])) {
                        $noPacksNewValue = (int) $data[$noPacksNewKey];
                        $noPacksValue = (int) $data[$noPacksKey];
                        $salesNoPacksData[] = abs($noPacksValue + $noPacksNewValue);
                    } else {
                        $salesNoPacksData[] = 0;
                    }
                }

                $totalPieces = array_sum($salesNoPacksData);
                $salesNoPacksDataString = implode(',', $salesNoPacksData);
                // Insert new record
                $isOutlet = DB::select("SELECT OutletAssign FROM `party` where Id ='" . $data['PartyId'] . "'");
                    if ($isOutlet[0]->OutletAssign == 1) { 
                DB::table('artstockstatus')->insert([
                    'outletId' => $data['PartyId'],
                    'ArticleId' => $articleId,
                    'ArticleNumber' => $articleNumber,
                    'SalesNoPacks' => $salesNoPacksDataString,
                    'TotalPieces' => $totalPieces,
                    'ArticleColor' => $data['ArticleSelectedColor'][0]['Name'],
                    'ArticleSize' => implode(',', array_column($data['ArticleSelectedSize'], 'Name')),
                    'ArticleRatio' => $data['ArticleRatio'],
                    'ArticleOpenFlag' => $data['ArticleOpenFlag'],
                    'Title' => $data['Category'],
                    'Colorflag' => $colorflag,
                    'Subcategory' => $name,
                ]);
            }
            }
        } else {
            $existingRecord = DB::table('artstockstatus')
                ->where(['outletId' => $data['PartyId'], 'ArticleId' => $articleId])
                ->get();
            // $salesNoPacksData = [];
            // $totalPieces = 0;


            if ($existingRecord) {
                $getresult = DB::select("SELECT SalesNoPacks FROM `artstockstatus` WHERE outletId = '" . $data["PartyId"] . "' AND ArticleId = " . $articleId);
            
                if (!empty($getresult)) {
                    $GetNoPacks = $getresult[0]->SalesNoPacks; // Assuming SalesNoPacks is the correct property name
                    $dataupdate = $GetNoPacks + $data['NoPacksNew'];
                    DB::table('artstockstatus')->where(['outletId' => $data['PartyId']])->where(['ArticleId' => $articleId])->update(['SalesNoPacks' => $dataupdate, 'TotalPieces' => $dataupdate]);
                } else {
                    $dataupdate = $data['NoPacks'] + $data['NoPacksNew'];
                    DB::table('artstockstatus')->where(['outletId' => $data['PartyId']])->where(['ArticleId' => $articleId])->update(['SalesNoPacks' => $dataupdate, 'TotalPieces' => $dataupdate]);
                }
            }
            
        }

        $dataresult = DB::select('SELECT c.Colorflag, o.NoPacks as OWNopacks, s.OutwardNoPacks FROM `outward` o inner join outwardnumber own on own.Id=o.OutwardNumberId inner join so s on s.SoNumberId=own.SoId left join po p on p.ArticleId=o.ArticleId left join article a on a.Id=o.ArticleId left join category c on c.Id=a.CategoryId where o.Id="' . $data['id'] . '" and s.ArticleId="' . $data['ArticleId'] . '"');
        $Colorflag = $dataresult[0]->Colorflag;
        $OutwardSalesNoPacks = $dataresult[0]->OutwardNoPacks;
        $OWNopacks = $dataresult[0]->OWNopacks;
        if (strpos($OWNopacks, ',') !== false) {
            $OutwardSalesNoPacks = explode(',', $OutwardSalesNoPacks);
            $OWNopacks = explode(',', $OWNopacks);
            $stringcomma = 1;
        } else {
            $stringcomma = 0;
        }
        $updateNoPacks = "";
        $UpdateInwardNoPacks = "";
        if ($Colorflag == 1) {
            foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                $numberofpacks = $vl["Id"];
                $sosale = $OutwardSalesNoPacks[$key];
                $outwardsale = $OWNopacks[$key];
                $getnopacks = $data["NoPacksNew_" . $numberofpacks];

                if ($data["NoPacksNew_" . $numberofpacks] != "") {
                    if ($stringcomma == 1) {
                        $total = ($sosale + $outwardsale);
                        if ($total < $getnopacks) {
                            return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                        }
                        $updateNoPacks .= $getnopacks . ",";
                        if ($outwardsale < $getnopacks) {
                            $UpdateInwardNoPacks .= ($sosale - ($getnopacks - $outwardsale)) . ",";
                        } else if ($outwardsale == $getnopacks) {
                            $UpdateInwardNoPacks .= $sosale . ",";
                        } else {
                            if ($sosale == 0) {
                                $UpdateInwardNoPacks .= ($outwardsale - $getnopacks) . ",";
                            } else {
                                $UpdateInwardNoPacks .= ($sosale + ($outwardsale - $getnopacks)) . ",";
                            }
                        }
                    } else {
                        $total = $OutwardSalesNoPacks + $OWNopacks;
                        if ($total < $getnopacks) {
                            return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
                        }
                        if ($OWNopacks < $getnopacks) {
                            $UpdateInwardNoPacks .= ($OutwardSalesNoPacks - ($getnopacks - $OWNopacks)) . ",";
                        } else if ($outwardsale == $getnopacks) {
                            $UpdateInwardNoPacks .= $OutwardSalesNoPacks . ",";
                        } else {
                            if ($sosale == 0) {
                                $UpdateInwardNoPacks .= ($OWNopacks - $getnopacks) . ",";
                            } else {
                                $UpdateInwardNoPacks .= ($OutwardSalesNoPacks + ($OWNopacks - $getnopacks)) . ",";
                            }
                        }
                        $updateNoPacks .= $getnopacks . ",";
                    }
                } else {
                    $updateNoPacks .= "0,";
                    $UpdateInwardNoPacks .= ($sosale + $outwardsale) . ",";
                }
            }
        } else {
            $updateNoPacks .= $data['NoPacksNew'] . ",";
            $total = $OutwardSalesNoPacks + $OWNopacks;
            if ($total < $data["NoPacksNew"]) {
                return response()->json(array("id" => "", "NoOfSetNotMatch" => "true"), 200);
            }
            if ($OWNopacks < $data['NoPacksNew']) {
                $UpdateInwardNoPacks .= ($OutwardSalesNoPacks - ($data['NoPacksNew'] - $OWNopacks)) . ",";
            } else if ($OWNopacks == $data['NoPacksNew']) {
                $UpdateInwardNoPacks .= $OutwardSalesNoPacks . ",";
            } else {
                if ($OutwardSalesNoPacks == 0) {
                    $UpdateInwardNoPacks .= ($OWNopacks - $data['NoPacksNew']) . ",";
                } else {
                    $UpdateInwardNoPacks .= ($OutwardSalesNoPacks + ($OWNopacks - $data['NoPacksNew'])) . ",";
                }
            }
        }
        $updateNoPacks = rtrim($updateNoPacks, ',');
        $UpdateInwardNoPacks = rtrim($UpdateInwardNoPacks, ',');
        $CheckupdateNoPacks = explode(',', $updateNoPacks);
        $tmp = array_filter($CheckupdateNoPacks);
        if (empty($tmp)) {
            return response()->json(array("id" => "", "ZeroNotAllow" => "true"), 200);
        }
        if (isset($data['OutwardWeight'])) {
            $OutwardWeight = $data['OutwardWeight'];
        } else {
            $OutwardWeight = "";
        }
        $ActiveOutward = DB::select("select otn.*, o.OutwardRate, o.PartyDiscount, o.OutwardBox, o.NoPacks from outward o inner join outwardnumber otn on otn.Id=o.OutwardNumberId where o.Id='" . $data['id'] . "'");
        $logDesc = "";
        if ($ActiveOutward[0]->GSTAmount != $data['GST']) {
            $logDesc = $logDesc . 'GSTAmount,';
        }
        if ($ActiveOutward[0]->GSTPercentage != $data['GST_Percentage']) {
            $logDesc = $logDesc . 'GST_Percentage,';
        }
        if ($ActiveOutward[0]->Discount != $data['Discount']) {
            $logDesc = $logDesc . 'Discount,';
        }
        if ($ActiveOutward[0]->OutwardRate != $data['OutwardRate']) {
            $logDesc = $logDesc . 'OutwardRate,';
        }
        if ($ActiveOutward[0]->Remarks != $data['Remarks']) {
            $logDesc = $logDesc . 'Remarks,';
        }
        if ($ActiveOutward[0]->PartyDiscount != $data['PartyDiscount']) {
            $logDesc = $logDesc . 'PartyDiscount,';
        }
        if ($ActiveOutward[0]->OutwardBox != $data['OutwardBox']) {
            $logDesc = $logDesc . 'OutwardBox,';
        }
        if ($ActiveOutward[0]->NoPacks != $updateNoPacks) {
            $logDesc = $logDesc . 'Pieces,';
        }
        $newLogDesc = rtrim($logDesc, ',');
        Outward::where('Id', (int) $data['id'])->update([
            'NoPacks' => $updateNoPacks,
            'PartyDiscount' => $data['PartyDiscount'],
            'OutwardBox' => $data['OutwardBox'],
            'OutwardRate' => $data['OutwardRate'],
            'OutwardWeight' => $OutwardWeight
        ]);
        DB::beginTransaction();
        try {



            DB::table('so')
                ->where('ArticleId', $data['ArticleId'])
                ->where('SoNumberId', $data['SoId'])
                ->update(['OutwardNoPacks' => $UpdateInwardNoPacks]);
            DB::table('outwardnumber')
                ->where('Id', $data['OutwardNumberId'])
                ->update(['OutwardDate' => $data['OutwardDate'], 'GSTAmount' => $data['GST'], 'GSTPercentage' => $data['GST_Percentage'], 'Discount' => $data['Discount'], 'Discount_amount' => $data['Discount_amount'], 'Remarks' => $data['Remarks']]);
            // return response()->json(['Rate' => (int)$data['id']] , 200);


            //     Outward::where('Id', (int)$data['id'])->update(array(
            //     'NoPacks' => $updateNoPacks,
            //     'PartyDiscount' => $data['PartyDiscount'],
            //     'OutwardBox' => $data['OutwardBox'],
            //     'OutwardRate' => $data['OutwardRate'],
            //     'OutwardWeight' => $OutwardWeight
            // ));
            $checknopacks = DB::select("select OutwardNoPacksCheck(" . $data['SoId'] . "," . $data['ArticleId'] . ") as OutwardNoPacksCheck");
            if ($checknopacks[0]->OutwardNoPacksCheck == 0) {
                DB::table('so')
                    ->where('ArticleId', $data['ArticleId'])
                    ->where('SoNumberId', $data['SoId'])
                    ->update(['Status' => 0]);
            } else {
                DB::table('so')
                    ->where('ArticleId', $data['ArticleId'])
                    ->where('SoNumberId', $data['SoId'])
                    ->update(['Status' => 1]);
            }
            if ($data['ArticleOpenFlag'] == 0) {
                if (strpos($updateNoPacks, ',') !== false) {
                    $updateNoPacks = explode(',', $updateNoPacks);
                    foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                        $numberofpacks = $vl["Id"];
                        $res = DB::select("select Id from outwardpacks where ColorId='" . $numberofpacks . "' and OutwardId='" . (int) $data['id'] . "' and ArticleId='" . $data['ArticleId'] . "'");
                        DB::table('outwardpacks')
                            ->where('Id', $res[0]->Id)
                            ->update(['NoPacks' => $updateNoPacks[$key], 'UpdatedDate' => date('Y-m-d H:i:s')]);
                    }
                } else {
                    foreach ($data['ArticleSelectedColor'] as $key => $vl) {
                        $numberofpacks = $vl["Id"];
                        $res = DB::select("select Id from outwardpacks where ColorId='" . $numberofpacks . "' and OutwardId='" . (int) $data['id'] . "' and ArticleId='" . $data['ArticleId'] . "'");
                        DB::table('outwardpacks')
                            ->where('Id', $res[0]->Id)
                            ->update(['NoPacks' => $updateNoPacks, 'UpdatedDate' => date('Y-m-d H:i:s')]);
                    }
                }
            } else {
                DB::table('outwardpacks')
                    ->where('OutwardId', (int) $data['id'])
                    ->where('ArticleId', $data['ArticleId'])
                    ->update(['NoPacks' => $updateNoPacks, 'UpdatedDate' => date('Y-m-d H:i:s')]);
            }
            $userName = Users::where('Id', $data['UserId'])->first();
            $outwardRec = DB::select("select concat(otn.OutwardNumber,'/', fn.StartYear,'-',fn.EndYear) as OutwardNumber from outwardnumber otn inner join financialyear fn on fn.Id=otn.FinancialYearId where otn.Id= '" . $data['OutwardNumberId'] . "'");
            $artRateRecord = Article::where('Id', $data['ArticleId'])->first();
            UserLogs::create([
                'Module' => 'Outward',
                'ModuleNumberId' => $data['OutwardNumberId'],
                'LogType' => 'Updated',
                'LogDescription' => $userName['Name'] . ' updated ' . $newLogDesc . ' of article ' . $artRateRecord->ArticleNumber . ' in Outward Number ' . $outwardRec[0]->OutwardNumber,
                'UserId' => $userName['Id'],
                'updated_at' => null
            ]);
            DB::commit();
            return response()->json("SUCCESS", 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json("", 200);
        }
    }

    public function RemainingSO($OutwardDate)
    {
        $remainingsolist = DB::select("SELECT u.Name as UserName  , s.SoNumberId, concat(IFNULL(partyuser.Name,u.Name),sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber ,s.ArticleId, s.Status, sn.PartyId, sn.Transporter, sn.UserId FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId inner join users u on u.Id=sn.UserId left join party p on p.Id=sn.PartyId left join users partyuser on partyuser.Id=p.UserId inner join financialyear fn on fn.Id=sn.FinancialYearId where (s.Status in (0)  AND sn.SoDate <= '$OutwardDate') group by SoNumberId order by SoNumber ASC");
        foreach ($remainingsolist as $remainingso) {
            $remainingso->SoNumber = str_replace(' ', '', $remainingso->SoNumber);
        }
        return $remainingsolist;
    }

    public function RemainingOutwardSO()
    {
        return DB::select("SELECT p.Name, s.SoNumberId, concat(FirstCharacterConcat(u.Name), sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber , s.ArticleId, s.Status, sn.PartyId, sn.Transporter, sn.UserId, sn.SoDate FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join party p on p.Id=sn.PartyId where s.status in (0) group by SoNumberId");
    }

    public function RemainingPostOutwardSO(Request $request)
    {
        $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select("select count(*) as Total from(SELECT p.Name, s.SoNumberId, concat(IFNULL(partyuser.Name,u.Name),sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber , s.ArticleId, s.Status, sn.PartyId, sn.Transporter, sn.UserId, sn.SoDate FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join party p on p.Id=sn.PartyId left join users partyuser on partyuser.Id=p.UserId where s.status in (0) group by SoNumberId)as d");
        $vntotal = $vnddataTotal[0]->Total;
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = " WHERE d.Name like '%" . $search['value'] . "%' Or d.SoNumber like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%' OR cast(d.SoDate as char) like '%" . $search['value'] . "%'";
            $vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNumber, p.Name, s.SoNumberId, concat(IFNULL(partyuser.Name,u.Name),sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber  , s.ArticleId, s.Status, sn.PartyId, sn.Transporter, sn.UserId, DATE_FORMAT(sn.SoDate, \"%d/%m/%Y\") as SoDate FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join party p on p.Id=sn.PartyId left join users partyuser on partyuser.Id=p.UserId inner join article a on a.Id=s.ArticleId where s.status in (0) group by SoNumberId) as d " . $searchstring);
            $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
        } else {
            $searchstring = "";
            $vnddataTotalFilterValue = $vntotal;
        }
        $column = $data["order"][0]["column"];
        switch ($column) {
            case 1:
                $ordercolumn = "d.Name";
                break;
            case 2:
                $ordercolumn = "d.SoNumber";
                break;
            case 6:
                $ordercolumn = "date(d.sdate)";
                break;
            default:
                $ordercolumn = "d.Name";
                break;
        }
        $order = "";
        if ($data["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
        }
        $vnddata = DB::select("select d.* from(SELECT  u.Name as UserName , p.UserId as PartyUserId, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNumber, GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY a.Id SEPARATOR ',') as AllArticleId, GROUP_CONCAT(CONCAT(s.Id) ORDER BY a.Id SEPARATOR ',') as sIds, CountNoPacks(GROUP_CONCAT(CONCAT(s.NoPacks) ORDER BY a.Id SEPARATOR ',')) as TotalNoPacks, p.Name, s.SoNumberId, concat(IFNULL(partyuser.Name,u.Name),sn.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber  , s.ArticleId, s.Status, sn.PartyId, sn.Transporter, sn.UserId, sn.SoDate as sdate, DATE_FORMAT(sn.SoDate, \"%d/%m/%Y\") as SoDate FROM so s inner join `sonumber` sn on sn.Id= s.SoNumberId inner join users u on u.Id=sn.UserId inner join financialyear fn on fn.Id=sn.FinancialYearId inner join party p on p.Id=sn.PartyId left join users partyuser on partyuser.Id=p.UserId inner join article a on a.Id=s.ArticleId where s.status in (0) group by SoNumberId) as d " . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length);
        foreach ($vnddata as $vl) {
            $TotalSendNoPacks = 0;
            $TotalRemainingNoPacks = 0;
            $TotalNoPacks = 0;
            $object = (object) $vl;
            $getsochallen = DB::select("select s.Id, u.Name as UserName, pt.Name, pt.Address, pt.PhoneNumber, pt.GSTNumber, son.SoDate, son.GSTAmount, son.GSTPercentage, son.GSTType, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber, son.Transporter, son.Destination, son.Remarks, s.NoPacks, art.Id as ArticleId, art.ArticleOpenFlag, art.ArticleNumber, c.Title, c.Colorflag, art.ArticleColor, art.ArticleSize, art.ArticleRate, s.ArticleRate as SoArticleRate, art.ArticleRatio from so s inner join sonumber son on son.Id=s.SoNumberId inner join users u on u.Id=son.UserId inner join financialyear fn on fn.Id=son.FinancialYearId inner join article art on art.Id=s.ArticleId left join po p on p.ArticleId=art.Id left join purchasenumber pur on pur.Id=p.PO_Number left join category c on c.Id=art.CategoryId inner join party pt on pt.Id=son.PartyId where s.SoNumberId='" . $object->SoNumberId . "' group by s.Id order by s.Id ASC");
            $TotalNoPacks = 0;
            $TotalSendNoPacks = 0;
            $TotalRemainingNoPacks = 0;
            foreach ($getsochallen as $vl) {
                $ArticleOpenFlag = $vl->ArticleOpenFlag;
                $NoPacks = $vl->NoPacks;
                $Colorflag = $vl->Colorflag;
                $ArticleId = $vl->ArticleId;
                $getart = $this->GetSOArticleData($object->SoNumberId, $ArticleId, 0);
                $RemainingNoPacks = $getart[0]->SalesNoPacks;
                $SendNoPacks = "";
                if (strpos($RemainingNoPacks, ',') !== false) {
                    $remnopack = explode(',', $RemainingNoPacks);
                    $nopackcheck = explode(',', $NoPacks);
                    foreach ($remnopack as $key => $rvl) {
                        $SendNoPacks .= ($nopackcheck[$key] - $rvl) . ',';
                    }
                    $SendNoPacks = rtrim($SendNoPacks, ',');
                    $TotalSendNoPacks += array_sum(explode(",", $SendNoPacks));
                    $TotalRemainingNoPacks += array_sum(explode(",", $RemainingNoPacks));
                } else {
                    $SendNoPacks = ($NoPacks - $RemainingNoPacks);
                    $TotalSendNoPacks += $SendNoPacks;
                    $TotalRemainingNoPacks += $RemainingNoPacks;
                }
                if ($Colorflag == 0) {
                    if ($ArticleOpenFlag == 0) {
                        $TotalNoPacks += $NoPacks;
                    } else {
                        $TotalNoPacks += $NoPacks;
                    }
                } else {
                    if ($ArticleOpenFlag == 0) {
                        $TotalNoPacks += array_sum(explode(",", $NoPacks));
                    } else {
                        $TotalNoPacks += $NoPacks;
                    }
                }
            }
            $object->TotalNoPacks = $TotalNoPacks;
            $object->TotalSendNoPacks = $TotalSendNoPacks;
            $object->TotalRemainingNoPacks = $TotalRemainingNoPacks;
        }
        return array(
            'datadraw' => $data["draw"],
            'recordsTotal' => $vntotal,
            'recordsFiltered' => $vnddataTotalFilterValue,
            'response' => 'success',
            'startnumber' => $startnumber,
            'search' => count($vnddata),
            'data' => $vnddata,
        );
    }

    public function GetSOData($OWNO, $Id)
    {
        $basicdetails = DB::select("SELECT  p.Name, s.PartyId, s.GSTAmount, s.GSTPercentage, p.Discount as PartyDiscount, p.GSTType, s.Transporter, s.Destination, s.Remarks FROM sonumber s inner join party p on p.Id=s.PartyId where s.Id = '" . $Id . "'");
        $allarticles = DB::select("select * from (select s.Id as SoId , s.ArticleId, a.ArticleNumber, OutwardNoPacksCheck('" . $Id . "', s.ArticleId) as OutwardNoPacksCheck from so s inner join article a on a.Id=s.ArticleId  where s.SoNumberId='" . $Id . "') as d where OutwardNoPacksCheck=0 group by d.SoId order by d.SoId ASC");
        $checkdata = DB::select('SELECT count(*) as TotalRow FROM `outwardnumber` otn inner join outward o on o.OutwardNumberId=otn.Id inner join salesreturn s on s.OutwardId=o.Id where otn.Id="' . $OWNO . '"');
        if ($checkdata[0]->TotalRow > 0) {
            $Outwardstatus = true;
        } else {
            $Outwardstatus = false;
        }
        $arraydata = array("BasicDetails" => $basicdetails, "Articles" => $allarticles, "Outwardstatus" => $Outwardstatus);
        return $arraydata;
    }


    public function SOGetSOArticleData($SOId, $Id, $OWID)
    {
        return DB::select("SELECT s.NoPacks, s.OutwardNoPacks as SalesNoPacks, CommaZeroValue(s.OutwardNoPacks) as CheckSalesNoPacks  FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=p.CategoryId inner join so s on s.ArticleId=art.Id inner join articlerate artr on artr.ArticleId=art.Id where art.Id='" . $Id . "' and s.SoNumberId='" . $SOId . "'");
    }

    public function GetSOArticleData($SOId, $Id, $OWID)
    {
        if ($OWID == 0) {
            return DB::select("SELECT art.Id as ArticleId, art.ArticleOpenFlag, art.ArticleNumber, s.ArticleRate, art.ArticleRatio, art.ArticleSize, art.ArticleColor, c.Title as Category ,  c.Colorflag, s.NoPacks, s.OutwardNoPacks as SalesNoPacks FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=art.CategoryId inner join so s on s.ArticleId=art.Id inner join articlerate artr on artr.ArticleId=art.Id where art.Id='" . $Id . "' and s.SoNumberId='" . $SOId . "'");
        } else {
            return DB::select("SELECT art.Id as ArticleId, art.ArticleOpenFlag, art.ArticleNumber, s.ArticleRate, art.ArticleRatio, art.ArticleSize, art.ArticleColor, c.Title as Category ,  c.Colorflag, s.NoPacks, s.OutwardNoPacks as SalesNoPacks, o.OutwardBox, o.OutwardRate, o.OutwardWeight, o.PartyDiscount FROM article art left join po p on p.ArticleId=art.Id left join category c on c.Id=art.CategoryId inner join outward o on o.Id=" . $OWID . " inner join so s on s.ArticleId=art.Id where art.Id='" . $Id . "' and s.SoNumberId='" . $SOId . "'");
        }
    }

    public function GetOutwardChallen($id)
    {
        $getoutwardchallen = DB::select("SELECT d.NAME AS username, p.NAME, p.Address, p.State , p.City , p.UserId as PartyUserId , p.PinCode , p.Country , p.PhoneNumber, p.gstnumber, d.outwarddate, d.outwardnumber, d.OTNOId, d.GSTType, d.SoNumberGen, d.transporter, d.gstamount, d.gstpercentage, d.discount, d.Remarks, d.articleopenflag, d.articlenumber, d.Discount_amount, c.title, c.colorflag, d.articlecolor, d.articlesize, d.articlerate, d.articleratio, d.OutwardBox, d.OutwardRate, o.PartyDiscount, o.OutwardWeight, d.PreparedUserName ,  d.nopacks, i.weight FROM (SELECT  prepareduser.Name as PreparedUserName ,  u.NAME, sn.partyid, sn.transporter, s.id, s.sonumberid, concat( sn.sonumber, '/', fn.startyear,'-',fn.endyear	) AS SoNumberGen, s.articleid, a.articleopenflag, a.articlenumber, a.articlerate, a.articlecolor, a.articlesize, a.articleratio, a.CategoryId, concat(own.outwardnumber, '/',fn1.startyear,'-',fn1.endyear) AS outwardnumber, own.Id as OTNOId, own.GSTType, own.outwarddate, own.gstamount, own.gstpercentage, own.discount, own.Discount_amount, own.Remarks, ( SELECT GROUP_CONCAT(id SEPARATOR ',') as id FROM outward WHERE outwardnumberid=own.Id and ArticleId=s.ArticleId) AS oid, ( SELECT GROUP_CONCAT(nopacks SEPARATOR '#')as nopacks FROM outward WHERE outwardnumberid=own.id AND articleid=s.articleid) AS nopacks, ( SELECT GROUP_CONCAT(OutwardBox SEPARATOR '#')as nopacks FROM outward WHERE outwardnumberid=own.id AND articleid=s.articleid) AS OutwardBox,( SELECT GROUP_CONCAT(OutwardRate SEPARATOR '#') as OutwardRate FROM outward WHERE outwardnumberid=own.id AND  articleid=s.articleid) AS OutwardRate FROM `so` s INNER JOIN outwardnumber own ON own.soid=s.sonumberid INNER JOIN article a ON a.id=s.articleid INNER JOIN users prepareduser on own.UserId=prepareduser.Id INNER JOIN sonumber sn ON sn.id=s.sonumberid INNER JOIN users u ON u.id=sn.userid INNER JOIN financialyear fn ON fn.id=sn.financialyearid INNER JOIN financialyear fn1 ON fn1.id=own.financialyearid WHERE s.articleid IN (SELECT articleid FROM outward WHERE OutwardNumberId='" . $id . "') and own.Id='" . $id . "') AS d INNER JOIN outward o ON o.id IN(oid) INNER JOIN inward i ON i.articleid=d.articleid left JOIN po po ON po.articleid=d.articleid INNER JOIN category c ON c.id=d.categoryid INNER JOIN party p ON p.id=d.partyid WHERE oid IS NOT NULL GROUP BY d.id ORDER BY c.Title ASC");
        $array = [];
        $PartyTotalDiscount = 0;
        foreach ($getoutwardchallen as $vl) {
            if (!is_null($vl->City)) {
                $fullAddress = $vl->Address . ', ' . $vl->City . ', ' . $vl->State . ', ' . $vl->Country . ' - ' . $vl->PinCode;
            } else {
                $fullAddress = $vl->Address;
            }
            if (!is_null($vl->PartyUserId)) {
                $user = Users::where('Id', $vl->PartyUserId)->first();
                $vl->sonumber = str_replace(' ', '', $user->Name . $vl->SoNumberGen);
                $username = $user->Name;
            } else {
                $vl->sonumber = str_replace(' ', '', $vl->username . $vl->SoNumberGen);
                $username = $vl->username;
            }

            $Name = $vl->NAME;
            $Discount_in_amount = $vl->Discount_amount;
            $PreparedUserName = $vl->PreparedUserName;
            $address = $fullAddress;
            $PhoneNumber = $vl->PhoneNumber;
            $gstnumber = $vl->gstnumber;
            $outwarddate = $vl->outwarddate;
            $outwardnumber = $vl->outwardnumber;
            $OutNumId = $vl->OTNOId;
            $sonumber = $vl->sonumber;
            $transporter = $vl->transporter;
            $gstamount = $vl->gstamount;
            $gstpercentage = $vl->gstpercentage;
            $GSTType = $vl->GSTType;
            $discount = $vl->discount;
            $Remarks = $vl->Remarks;
            $articleopenflag = $vl->articleopenflag;
            $articlenumber = $vl->articlenumber;
            $title = $vl->title;
            $colorflag = $vl->colorflag;
            $articlecolor = $vl->articlecolor;
            $articlesize = $vl->articlesize;
            $articlerate = $vl->articlerate;
            $articleratio = $vl->articleratio;
            $OutwardBox = $vl->OutwardBox;
            $OutwardRate = $vl->OutwardRate;
            $PartyDiscount = $vl->PartyDiscount;
            $OutwardWeight = $vl->OutwardWeight;
            $nopacks = $vl->nopacks;
            $weight = $vl->weight;
            if ($vl->PartyDiscount != 0) {
                $PartyTotalDiscount = $vl->PartyDiscount++;
            }
            if (strpos($OutwardBox, "#") !== false) {
                $OutwardBox1 = explode('#', $OutwardBox);
                $OutwardRate1 = explode('#', $vl->OutwardRate);
                $nopacks1 = explode('#', $vl->nopacks);


                foreach ($OutwardBox1 as $key => $outwarddata) {
                    $tmp["OutwardBox"] = $outwarddata;
                    $tmp["UserName"] = $username;
                    $tmp["Name"] = $Name;
                    $tmp["PreparedUserName"] = $PreparedUserName;
                    $tmp["Address"] = $address;
                    $tmp["PhoneNumber"] = $PhoneNumber;
                    $tmp["GSTNumber"] = $gstnumber;
                    $tmp["OutwardDate"] = $outwarddate;
                    $tmp["OutwardNumber"] = $outwardnumber;
                    $tmp['OutNumId'] = $OutNumId;
                    $tmp["SoNumber"] = $sonumber;
                    $tmp["Transporter"] = $transporter;
                    $tmp["GSTAmount"] = $gstamount;
                    $tmp["GSTPercentage"] = $gstpercentage;
                    $tmp["GSTType"] = $GSTType;
                    $tmp["Discount"] = $discount;
                    $tmp["Remarks"] = $Remarks;
                    $tmp["ArticleOpenFlag"] = $articleopenflag;
                    $tmp["ArticleNumber"] = $articlenumber;
                    $tmp["Title"] = $title;
                    $tmp["Colorflag"] = $colorflag;
                    $tmp["ArticleColor"] = $articlecolor;
                    $tmp["ArticleSize"] = $articlesize;
                    $tmp["ArticleRate"] = $articlerate;
                    $tmp["ArticleRatio"] = $articleratio;
                    $tmp["OutwardRate"] = $OutwardRate1[$key];
                    $tmp["PartyDiscount"] = $PartyDiscount;
                    $tmp["OutwardWeight"] = $OutwardWeight;
                    $tmp["NoPacks"] = $nopacks1[$key];
                    $tmp["Weight"] = $weight;
                    array_push($array, $tmp);
                    break;
                }
            } else {
                array_push($array, array("OutwardBox" => $OutwardBox, "PreparedUserName" => $PreparedUserName, "UserName" => $username, "Name" => $Name, "Address" => $address, "PhoneNumber" => $PhoneNumber, "GSTNumber" => $gstnumber, "OutwardDate" => $outwarddate, "OutwardNumber" => $outwardnumber, "OutwardNumberId" => $OutNumId, "SoNumber" => $sonumber, "Transporter" => $transporter, "GSTAmount" => $gstamount, "GSTPercentage" => $gstpercentage, "GSTType" => $GSTType, "Discount" => $discount, "Discount_in_amount" => $Discount_in_amount, "Remarks" => $Remarks, "ArticleOpenFlag" => $articleopenflag, "ArticleNumber" => $articlenumber, "Title" => $title, "Colorflag" => $colorflag, "ArticleColor" => $articlecolor, "ArticleSize" => $articlesize, "ArticleRate" => $articlerate, "ArticleRatio" => $articleratio, "OutwardRate" => $OutwardRate, "PartyDiscount" => $PartyDiscount, "OutwardWeight" => $OutwardWeight, "NoPacks" => $nopacks, "Weight" => $weight));
            }
        }
        // asort($array);
        $arrayorderdata = array_values($array);
        $challendata = [];
        $TotalNoPacks = 0;
        $TotalAmount = 0;
        $TotalWeight = 0;
        $TotalQuantityInSet = 0;
        $result = [];
        $arrayvl = array();
        $Weight1 = 0;
        $ch = 0;
        $i = 0;
        $SRNO = 1;
        foreach ($arrayorderdata as $vl) {
            $srno = $SRNO++;
            $Name = $vl["Name"];
            $Discount_in_amount = $vl["Discount_in_amount"];
            $UserName = $vl['UserName'];
            $Address = $vl['Address'];
            $PreparedUserName = $vl['PreparedUserName'];
            $PhoneNumber = $vl['PhoneNumber'];
            $GSTNumber = $vl['GSTNumber'];
            $OutwardDate = $vl['OutwardDate'];
            $OutwardNumber = $vl['OutwardNumber'];
            $OutwardNumberId = $vl['OutwardNumberId'];
            $SoNumber = $vl['SoNumber'];
            $Transporter = $vl['Transporter'];
            $Remarks = $vl['Remarks'];
            $ArticleOpenFlag = $vl['ArticleOpenFlag'];
            $ArticleNumber = $vl['ArticleNumber'];
            $Title = $vl['Title'];
            $NoPacks = $vl['NoPacks'];
            $ArticleRate = $vl['ArticleRate'];
            $ArticleRatio = $vl['ArticleRatio'];
            $OutwardBox = $vl['OutwardBox'];
            $OutwardRate = $vl['OutwardRate'];
            $OutwardWeight = $vl['OutwardWeight'];
            $PartyDiscount = $vl['PartyDiscount'];
            $Weight = $vl['Weight'];
            $Colorflag = $vl['Colorflag'];
            $GSTPercentage = $vl['GSTPercentage'];
            $GSTAmount = $vl['GSTAmount'];
            $Discount = $vl['Discount'];
            if ($Colorflag == 0) {
                if ($ArticleOpenFlag == 0) {
                    $ArticleRatio = $vl['ArticleRatio'];
                    $TotalNoPacks += $NoPacks;
                }
            } else {
                if ($ArticleOpenFlag == 0) {
                    $ArticleRatio = $vl['ArticleRatio'];
                    $countNoSet = array_sum(explode(",", $NoPacks));
                    $TotalNoPacks += array_sum(explode(",", $NoPacks));
                }
            }
            if ($ArticleOpenFlag == 0) {
                if (strpos($NoPacks, ',') !== false) {
                    $countNoSet = array_sum(explode(",", $NoPacks));
                } else {
                    if (strpos($ArticleRatio, ',') !== false) {
                        $ArticleRatio = array_sum(explode(",", $ArticleRatio));
                    }
                    $countNoSet = $NoPacks;
                }
                $Weight = $Weight * $countNoSet;
                $TotalWeight += $Weight;
                if ($PartyDiscount != 0) {
                    $Amount = $countNoSet * $OutwardRate;
                    $DiscountAmount = (($Amount * $PartyDiscount) / 100);
                    $Amount = $Amount - $DiscountAmount;
                } else {
                    $Amount = $countNoSet * $OutwardRate;
                }
                $TotalAmount += $Amount;





                //COLOR
                $colorsv = $vl['ArticleColor'];
                $decodedData = json_decode($colorsv, true);
                $results = [];
                foreach ($decodedData as $item) {
                    $results[] = $item['Name'];
                }
                $jsonResult = $results;
                //COLOR

                //PACKS
                $data = $NoPacks;
                $results = explode(",", $data);

                // Convert the results to JSON if needed
                $jsonResultP = $results;

                //PACKS


                $C = array_combine($jsonResult, $jsonResultP);

                $jsonData = json_encode($C);

                $arrayData = json_decode($jsonData, true);
                $TotalCQty = "";
                $allkey = "";
                $allvalue = "";
                foreach ($arrayData as $key => $value) {
                    if (!empty($key)) {
                        $TotalCQty .= '<b>' . $key . " : " . '</b>' . $value . ", ";
                    } else {
                        $TotalCQty .= '<b>' . '--' . " : " . '</b>' . $value . ', ';
                    }
                }

                // return $TotalCQty












                $getcolor = json_decode($vl['ArticleColor']);
                $getsize = json_decode($vl['ArticleSize']);
                $ArticleColor = "";
                foreach ($getcolor as $vl) {
                    $ArticleColor .= $vl->Name . ", ";
                }
                $ArticleColor = rtrim($ArticleColor, ', ');
                $ArticleSize = "";
                foreach ($getsize as $vl) {
                    $ArticleSize .= $vl->Name . ", ";
                }
                $ArticleSize = rtrim($ArticleSize, ', ');










            } else {
                $countNoSet = $NoPacks;
                $TotalCQty = "";
                $ArticleRatio = "";
                $ArticleRate = "";
                $ArticleColor = "";
                $ArticleSize = "";
                if ($OutwardWeight != "") {
                    $Weight = $OutwardWeight * $countNoSet;
                    $TotalWeight += $Weight;
                } else {
                    $Weight = "";
                }
                if ($PartyDiscount != 0) {
                    $Amount = $NoPacks * $OutwardRate;
                    $DiscountAmount = (($Amount * $PartyDiscount) / 100);
                    $Amount = $Amount - $DiscountAmount;
                } else {
                    $Amount = $NoPacks * $OutwardRate;
                }
                $TotalAmount += $Amount;
            }
            if ($Weight == "") {
                $weightset = 0;
            } else {
                $weightset = $Weight;
            }
            if ($ch == 0) {
                array_push($arrayvl, $OutwardBox);
                $ch = $OutwardBox;
                $Weight1 = "";
                $Weight1 = $weightset;
            } else {
                if ($ch != $OutwardBox) {
                    array_push($arrayvl, $OutwardBox);
                    $ch = $OutwardBox;
                    $Weight1 = $weightset;
                    $i++;
                } else {
                    $Weight1 += $weightset;
                }
            }
            $qtyTotal = array_sum(explode(",", $NoPacks)); //jignesh
            $TotalQuantityInSet += $qtyTotal;


            $numbers_array = explode(",", $NoPacks);
            // return $numbers_array;
            $numbers_array = array_sum($numbers_array);
            // return $numbers_array;

            // $valer = json_decode($numbers_array, true);

            // return $valer;  
            // $numbers_array = array_sum($numbers_array);
            // $TotalQty = $sum;
            // return $numbers_array;
            // return $sum;;
            // $arr = json_decode($numbers_array, true);
            // return $arr;
            $result[$i]['OutwardBox'] = $ch;
            $result[$i]['Weight'] = number_format($Weight1, 2);
            $result[$i]['ArticleNumber'][] = $ArticleNumber;
            $result[$i]['ArticleColor'][] = $ArticleColor;
            $result[$i]['TotalCQty'][] = $TotalCQty;
            $result[$i]['Srno'][] = $srno;
            $result[$i]['Title'][] = $Title;
            $result[$i]['ArticleRatio'][] = $ArticleRatio;
            $result[$i]['TotalQty'][] = $numbers_array;
            $result[$i]['NoPacks'][] = $NoPacks;
            $result[$i]['ArticleRate'][] = $ArticleRate;
            $result[$i]['Amount'][] = number_format($Amount, 2);
            $result[$i]['ArticleSize'][] = $ArticleSize;
            $result[$i]['OutwardRate'][] = number_format($OutwardRate, 2);
            $result[$i]['Transporter'][] = $Transporter;
            $result[$i]['PartyDiscount'][] = $PartyDiscount;


            $TotalQtyColor = $result[0]['TotalQty'][0];


            // $TotalColorsPack = $result[0]['ArticleColor'][0];
            // $TotalNumbersOfQty = $result[0]['NoPacks'][0];

            // $numbers_colorQty = explode(",", $TotalColorsPack);

            // $numbers_packQty = explode(",", $TotalNumbersOfQty);

            // $C=array_combine($numbers_colorQty,$numbers_packQty);

            // $jsonData = json_encode($C);

            // $arrayData = json_decode($jsonData, true);
            // $TotalCQty="";
            // $allkey="";
            // $allvalue="";
            // foreach ($arrayData as $key => $value) { 
            //     $TotalCQty .= $key . " - " . $value .", "; 
            //     $allkey .= $key;
            //     $allvalue .= $value; 
            // }




            $challendata[] = json_decode(json_encode(array("PreparedUserName" => $PreparedUserName, "UserName" => $UserName, "OutwardDate" => $OutwardDate, "OutwardNumber" => $OutwardNumber, "OutwardNumberId" => $OutwardNumberId, "Discount_in_amount" => $Discount_in_amount, "SoNumber" => $SoNumber, "Name" => $Name, "Address" => $Address, "PhoneNumber" => $PhoneNumber, "GSTNumber" => $GSTNumber, "Remarks" => $Remarks, "Srno" => $srno, "ArticleNumber" => $ArticleNumber, "Title" => $Title, "ArticleRatio" => $ArticleRatio, "TotalQty" => $TotalQtyColor, "QuantityInSet" => $NoPacks, "TotalQtyInSet" => $qtyTotal, "ArticleRate" => $ArticleRate, "Amount" => $Amount, "ArticleColor" => $ArticleColor, "TotalCQty" => $TotalCQty, "ArticleSize" => $ArticleSize, "OutwardBox" => $OutwardBox, "OutwardRate" => $OutwardRate, "Weight" => $Weight, "Transporter" => $Transporter, "PartyDiscount" => $PartyDiscount)), false);
            // return $challendata;    
        }
        $TotalFinalAmount = 0;
        $SubTotalAmount = 0;
        $TotalFinalAmountDiscount = 0;
        $GSTLabel = "";
        $GSTValue = 0;
        $CGSTValue = 0;
        $SGSTValue = 0;




                
        if ($Discount > 0 || $Discount != "") {
            $TotalFinalAmountDiscount = (($TotalAmount * $Discount) / 100);
            $SubTotalAmount = $TotalAmount - $TotalFinalAmountDiscount;
            $TotalFinalAmount = $SubTotalAmount;
        } 
        elseif($Discount_in_amount > 0 || $Discount_in_amount != ""){
            $SubTotalAmount = $TotalAmount - $Discount_in_amount;
            $TotalFinalAmount = $SubTotalAmount;
        }
        else {
            if ($TotalFinalAmount == 0) {
                $TotalFinalAmount = $TotalAmount;
            }
        }

        
        if ($Discount > 0 || $Discount != "") {
            $TotalFinalAmountDiscount = (($TotalAmount * $Discount) / 100);
            $SubTotalAmount = $TotalAmount - $TotalFinalAmountDiscount;
            $TotalFinalAmount = $SubTotalAmount;
        } else {
            if ($TotalFinalAmount == 0) {
                $TotalFinalAmount = $TotalAmount;
            }
        }

        if ($Discount_in_amount > 0 || $Discount_in_amount != "") {
            $SubTotalAmount = $TotalAmount - $Discount_in_amount;
            $TotalFinalAmount = $SubTotalAmount;
        } 

        // if ($GSTPercentage != "" || $GSTAmount != "") {
        //     if ($GSTPercentage > 0) {
        //         $GSTLabel = "GST " . $GSTPercentage . "%";
        //         $GSTValue = (($TotalFinalAmount * $GSTPercentage) / 100);
        //         $CGSTValue = ($GSTValue / 2);
        //         $SGSTValue = ($GSTValue / 2);
        //         $TotalGSTValue = round(($GSTValue / 2), 2) * 2;
        //         $TotalFinalAmount = ($TotalFinalAmount + $TotalGSTValue);
        //     } else {
        //         $GSTValue = number_format($GSTAmount, 2);
        //         $GSTValue1 = $GSTAmount;
        //         $TotalFinalAmount = ($TotalFinalAmount + $GSTValue1);
        //         $GSTLabel = "GST Amount";
        //     }
        // }

        $SubtotalStatus = 0;
        if (is_float($TotalFinalAmount)) {
            $SubtotalStatus = 1;
        }
        if ($TotalWeight != 0) {
            $TotalWeight = number_format($TotalWeight, 2);
        }
        $as = array($challendata, array("RoundOff" => $this->splitter(number_format($TotalFinalAmount, 2, '.', '')), "SubtotalStatus" => $SubtotalStatus, "PartyTotalDiscount" => $PartyTotalDiscount, "TotalNoPacks" => $TotalNoPacks, "TotalQuantityAllInSet" => $TotalQuantityInSet, "TotalAmount" => number_format($TotalAmount, 2), "TotalWeight" => $TotalWeight, "Discount_in_amount" => $Discount_in_amount, "ExtraDiscountpercentage" => $Discount, "Discount" => number_format($TotalFinalAmountDiscount, 2), "SubTotalAmount" => number_format($SubTotalAmount, 2), "TotalFinalAmount" => number_format($TotalFinalAmount, 2), "GSTLabel" => $GSTLabel, "GSTPercentage" => (int) $GSTPercentage, "GSTValue" => $GSTValue, "CGSTValue" => number_format($CGSTValue, 2), "SGSTValue" => number_format($SGSTValue, 2), "GSTType" => $GSTType), $result);
        return $as;
    }

    function truncate_number($number, $precision = 2)
    {
        if (0 == (int) $number) {
            return $number;
        }
        // Are we negative?
        $negative = $number / abs($number);
        // Cast the number to a positive to solve rounding
        $number = abs($number);
        // Calculate precision number for dividing / multiplying
        $precision = pow(10, $precision);
        // Run the math, re-applying the negative value to ensure
        // returns correctly negative / positive
        return floor($number * $precision) / $precision * $negative;
    }

    function splitter($val)
    {
        $totalroundamount = $val;
        $str = (string) $val;
        $splitted = explode(".", $str);
        $num = (int) $splitted[1];
        $number_var = "";
        $adjust_amount = "";
        if ($num != 0) {
            $roundoff_check = true;
            if ($num >= 50) {
                $number_var = "Up";
                $am_set = (100 - $num);
                if ($am_set > 10) {
                    $adjust_amount = "0." . $am_set;
                } else {
                    $adjust_amount = "0.0" . $am_set;
                }
            } else {
                $number_var = "Down";
                if ($num > 10) {
                    $adjust_amount = "0." . $num;
                } else {
                    $adjust_amount = "0.0" . $num;
                }
            }
            $totalroundamount = number_format(round($totalroundamount), 2);
        } else {
            $number_var = "Zero";
            $roundoff_check = false;
            $adjust_amount = 0;
        }
        return array("Roundoff" => $roundoff_check, 'RoundValueSign' => $number_var, 'TotalRoundAmount' => $totalroundamount, 'AdjustAmount' => $adjust_amount);
    }
    public function OutwardLogs($OWNOId)
    {
        return DB::select("select * from (select ul.Id as UserLogsId, ul.Module, ul.LogType, ul.ModuleNumberId, ul.LogDescription, DATE_FORMAT(ul.created_at ,'%d-%m-%Y') as CreatedDate, DATE_FORMAT(ul.created_at ,'%H:%i:%s') as CreatedTime, u.Name, u.Status, ur.Role from userlogs ul inner join users u on u.Id=ul.UserId inner join userrole ur on ur.RoleType=u.Role) as dd where dd.ModuleNumberId= '" . $OWNOId . "' and dd.Module='Outward' order by dd.UserLogsId desc ");
    }
}
