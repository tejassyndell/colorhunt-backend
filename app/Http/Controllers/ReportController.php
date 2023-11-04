<?php

namespace App\Http\Controllers;

use App\Artstockstatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportAllStocksExport;
use App\Article;
use App\Category;
use App\Inward;
use App\TransportOutwardpacks;
use App\Outletimport;
use App\Outward;
use App\OutwardNumber;
use App\Party;
use App\SO;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function GetRangeWiseAllStocks($startdate, $enddate)
    {
        $articles = DB::select("select * from (select a.ArticleNumber, a.Id, a.ArticleStatus, c.Title, sc.Name, rs.Series, DATE_FORMAT(i.created_at,'%Y-%m-%d') as InwardDate from article a inner join inward i on i.ArticleId=a.Id left join category c on c.Id=a.CategoryId left join subcategory sc on sc.Id=a.SubCategoryId left join rangeseries rs on rs.Id=a.SeriesId) as dd where dd.ArticleStatus != '3' and dd.InwardDate <= '" . $enddate . "' GROUP BY dd.ArticleNumber ");
        foreach ($articles as $key => $article) {
            // foreach ($articles as $article) {
            //openinig stock
            $article->openStock = 0;
            //addition open
            $openInwards = DB::select("select * from (select i.NoPacks, DATE_FORMAT(i.created_at,'%Y-%m-%d') as CreatedDate, DATE_FORMAT(i.created_at, '%h:%m:%S') as CreatedTime, i.ArticleId from inward i inner join article a on a.Id=i.ArticleId) as dd where dd.CreatedDate < '" . $startdate . "' and dd.ArticleId='" . $article->Id . "'");
            $openSalesReturns = DB::select("select * from (select sr.NoPacks, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, sr.ArticleId from salesreturn sr) as dd where dd.CreatedDate < '" . $startdate . "' and dd.ArticleId='" . $article->Id . "'");
            $openPros = DB::select("select * from (select st.TransferNoPacks as NoPacks, DATE_FORMAT(st.created_at,'%Y-%m-%d') as CreatedDate, st.TransferArticleId as ArticleId from stocktransfer st) as dd where dd.CreatedDate < '" . $startdate . "' and dd.ArticleId='" . $article->Id . "'");
            foreach ($openInwards as $openInward) {
                $openInwardPacks = 0;
                if (strpos($openInward->NoPacks, ',') != false) {
                    $openInwardPacks = $openInwardPacks + array_sum(explode(',', $openInward->NoPacks));
                } else {
                    $openInwardPacks = $openInwardPacks + (int) $openInward->NoPacks;
                }
                $article->openStock = $article->openStock + $openInwardPacks;
            }
            foreach ($openSalesReturns as $openSalesReturn) {
                $openSalesReturnPacks = 0;
                if (strpos($openSalesReturn->NoPacks, ',') != false) {
                    $openSalesReturnPacks = $openSalesReturnPacks + array_sum(explode(',', $openSalesReturn->NoPacks));
                } else {
                    $openSalesReturnPacks = $openSalesReturnPacks + (int) $openSalesReturn->NoPacks;
                }
                $article->openStock = $article->openStock + $openSalesReturnPacks;
            }
            foreach ($openPros as $openPro) {
                $openProPacks = 0;
                if (strpos($openPro->NoPacks, ',') != false) {
                    $openProPacks = $openProPacks + array_sum(explode(',', $openPro->NoPacks));
                } else {
                    $openProPacks = $openProPacks + (int) $openPro->NoPacks;
                }
                $article->openStock = $article->openStock + $openProPacks;
            }
            //end eddition open
            // subtraction open
            $openOutwards = DB::select("select * from (select o.NoPacks, DATE_FORMAT(o.created_at,'%Y-%m-%d') as CreatedDate, o.ArticleId from outward o) as dd where dd.CreatedDate < '" . $startdate . "' and dd.ArticleId='" . $article->Id . "'");
            $openPurchaseReturns = DB::select("select * from (select pr.ReturnNoPacks as NoPacks, DATE_FORMAT(pr.CreatedDate,'%Y-%m-%d') as CreatedDate, pr.ArticleId from purchasereturn pr) as dd where dd.CreatedDate < '" . $startdate . "' and dd.ArticleId='" . $article->Id . "'");
            $openCons = DB::select("select * from (select st.ConsumedNoPacks as NoPacks, DATE_FORMAT(st.created_at,'%Y-%m-%d') as CreatedDate, st.ConsumedArticleId as ArticleId from stocktransfer st) as dd where dd.CreatedDate < '" . $startdate . "' and dd.ArticleId='" . $article->Id . "'");
            $openShortages = DB::select("select * from (select ss.NoPacks, DATE_FORMAT(ss.created_at,'%Y-%m-%d') as CreatedDate, ss.ArticleId from stockshortage ss) as dd where dd.CreatedDate < '" . $startdate . "' and dd.ArticleId='" . $article->Id . "'");
            foreach ($openOutwards as $openOutward) {
                $openOutwardPacks = 0;
                if (strpos($openOutward->NoPacks, ',') != false) {
                    $openOutwardPacks = $openOutwardPacks + array_sum(explode(',', $openOutward->NoPacks));
                } else {
                    $openOutwardPacks = $openOutwardPacks + (int) $openOutward->NoPacks;
                }
                $article->openStock = $article->openStock - $openOutwardPacks;
            }
            foreach ($openPurchaseReturns as $openPurchaseReturn) {
                $openPurchaseReturnPacks = 0;
                if (strpos($openPurchaseReturn->NoPacks, ',') != false) {
                    $openPurchaseReturnPacks = $openPurchaseReturnPacks + array_sum(explode(',', $openPurchaseReturn->NoPacks));
                } else {
                    $openPurchaseReturnPacks = $openPurchaseReturnPacks + (int) $openPurchaseReturn->NoPacks;
                }
                $article->openStock = $article->openStock - $openPurchaseReturnPacks;
            }
            foreach ($openCons as $openCon) {
                $openConPacks = 0;
                if (strpos($openCon->NoPacks, ',') != false) {
                    $openConPacks = $openConPacks + array_sum(explode(',', $openCon->NoPacks));
                } else {
                    $openConPacks = $openConPacks + (int) $openCon->NoPacks;
                }
                $article->openStock = $article->openStock - $openConPacks;
            }
            foreach ($openShortages as $openShortage) {
                $openShortagePacks = 0;
                if (strpos($openShortage->NoPacks, ',') != false) {
                    $openShortagePacks = $openShortagePacks + array_sum(explode(',', $openShortage->NoPacks));
                } else {
                    $openShortagePacks = $openShortagePacks + (int) $openShortage->NoPacks;
                }
                $article->openStock = $article->openStock - $openShortagePacks;
            }
            // end subtraction open
            // end open stock
            // start range stock
            $article->inwardStock = 0;
            $article->salesReturnStock = 0;
            $article->proStock = 0;
            $article->domesticOutwardStock = 0;
            $article->exportOutwardStock = 0;
            $article->totalOutwardStock = 0;
            $article->purchaseReturnStock = 0;
            $article->consStock = 0;
            $article->shortageStock = 0;
            //addition range stock
            $rangeInwards = DB::select("select * from (select i.NoPacks, DATE_FORMAT(i.created_at,'%Y-%m-%d') as CreatedDate, DATE_FORMAT(i.created_at, '%h:%m:%S') as CreatedTime, i.ArticleId from inward i inner join article a on a.Id=i.ArticleId) as dd where dd.CreatedDate >= '" . $startdate . "' and dd.CreatedDate <= '" . $enddate . "' and dd.ArticleId='" . $article->Id . "'");
            $rangeSalesReturns = DB::select("select * from (select sr.NoPacks, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, sr.ArticleId from salesreturn sr) as dd where dd.CreatedDate >= '" . $startdate . "' and dd.CreatedDate <= '" . $enddate . "' and dd.ArticleId='" . $article->Id . "'");
            $rangePros = DB::select("select * from (select st.TransferNoPacks as NoPacks, DATE_FORMAT(st.created_at,'%Y-%m-%d') as CreatedDate, st.TransferArticleId as ArticleId from stocktransfer st) as dd where dd.CreatedDate >= '" . $startdate . "' and dd.CreatedDate <= '" . $enddate . "' and dd.ArticleId='" . $article->Id . "'");
            foreach ($rangeInwards as $rangeInward) {
                $rangeInwardPacks = 0;
                if (strpos($rangeInward->NoPacks, ',') != false) {
                    $rangeInwardPacks = $rangeInwardPacks + array_sum(explode(',', $rangeInward->NoPacks));
                } else {
                    $rangeInwardPacks = $rangeInwardPacks + (int) $rangeInward->NoPacks;
                }
                $article->inwardStock = $article->inwardStock + $rangeInwardPacks;
            }
            foreach ($rangeSalesReturns as $rangeSalesReturn) {
                $rangeSalesReturnPacks = 0;
                if (strpos($rangeSalesReturn->NoPacks, ',') != false) {
                    $rangeSalesReturnPacks = $rangeSalesReturnPacks + array_sum(explode(',', $rangeSalesReturn->NoPacks));
                } else {
                    $rangeSalesReturnPacks = $rangeSalesReturnPacks + (int) $rangeSalesReturn->NoPacks;
                }
                $article->salesReturnStock = $article->salesReturnStock + $rangeSalesReturnPacks;
            }
            foreach ($rangePros as $rangePro) {
                $rangeProPacks = 0;
                if (strpos($rangePro->NoPacks, ',') != false) {
                    $rangeProPacks = $rangeProPacks + array_sum(explode(',', $rangePro->NoPacks));
                } else {
                    $rangeProPacks = $rangeProPacks + (int) $rangePro->NoPacks;
                }
                $article->proStock = $article->proStock + $rangeProPacks;
            }
            //end eddition range stock
            // subtraction range stock
            // $rangeDomesticOutwards = DB::select("select * from (select o.NoPacks, DATE_FORMAT(o.created_at,'%Y-%m-%d') as CreatedDate, o.ArticleId, p.Country from outward o inner join party p on p.Id=o.PartyId) as dd where dd.CreatedDate >= '" . $startdate . "' and dd.CreatedDate <= '" . $enddate . "' and dd.ArticleId='" . $article->Id . "' and (dd.Country = 'india' OR dd.Country = '.' OR dd.Country IS NULL) ");
            $rangeDomesticOutwards = DB::select("select * from (select o.NoPacks, DATE_FORMAT(o.created_at,'%Y-%m-%d') as CreatedDate, o.ArticleId, p.Id as PartyId from outward o inner join party p on p.Id=o.PartyId) as dd where dd.CreatedDate >= '" . $startdate . "' and dd.CreatedDate <= '" . $enddate . "' and dd.ArticleId='" . $article->Id . "' and dd.PartyId != '45' ");
            // $rangeExportOutwards = DB::select("select * from (select o.NoPacks, DATE_FORMAT(o.created_at,'%Y-%m-%d') as CreatedDate, o.ArticleId, p.Country from outward o inner join party p on p.Id=o.PartyId) as dd where dd.CreatedDate >= '" . $startdate . "' and dd.CreatedDate <= '" . $enddate . "' and dd.ArticleId='" . $article->Id . "' and dd.Country NOT IN ('india', '.') ");
            $rangeExportOutwards = DB::select("select * from (select o.NoPacks, DATE_FORMAT(o.created_at,'%Y-%m-%d') as CreatedDate, o.ArticleId, p.Id as PartyId from outward o inner join party p on p.Id=o.PartyId) as dd where dd.CreatedDate >= '" . $startdate . "' and dd.CreatedDate <= '" . $enddate . "' and dd.ArticleId='" . $article->Id . "' and dd.PartyId = '45' ");
            $rangePurchaseReturns = DB::select("select * from (select pr.ReturnNoPacks as NoPacks, DATE_FORMAT(pr.CreatedDate,'%Y-%m-%d') as CreatedDate, pr.ArticleId from purchasereturn pr) as dd where dd.CreatedDate >= '" . $startdate . "' and dd.CreatedDate <= '" . $enddate . "' and dd.ArticleId='" . $article->Id . "'");
            $rangeCons = DB::select("select * from (select st.ConsumedNoPacks as NoPacks, DATE_FORMAT(st.created_at,'%Y-%m-%d') as CreatedDate, st.ConsumedArticleId as ArticleId from stocktransfer st) as dd where dd.CreatedDate >= '" . $startdate . "' and dd.CreatedDate <= '" . $enddate . "' and dd.ArticleId='" . $article->Id . "'");
            $rangeShortages = DB::select("select * from (select ss.NoPacks, DATE_FORMAT(ss.created_at,'%Y-%m-%d') as CreatedDate, ss.ArticleId from stockshortage ss) as dd where dd.CreatedDate >= '" . $startdate . "' and dd.CreatedDate <= '" . $enddate . "' and dd.ArticleId='" . $article->Id . "'");
            foreach ($rangeDomesticOutwards as $rangeDomesticOutward) {
                $domesticOutwardStock = 0;
                if (strpos($rangeDomesticOutward->NoPacks, ',') != false) {
                    $domesticOutwardStock = $domesticOutwardStock + array_sum(explode(',', $rangeDomesticOutward->NoPacks));
                } else {
                    $domesticOutwardStock = $domesticOutwardStock + (int) $rangeDomesticOutward->NoPacks;
                }
                $article->domesticOutwardStock = $article->domesticOutwardStock + $domesticOutwardStock;
            }
            foreach ($rangeExportOutwards as $rangeExportOutward) {
                $exportOutwardStock = 0;
                if (strpos($rangeExportOutward->NoPacks, ',') != false) {
                    $exportOutwardStock = $exportOutwardStock + array_sum(explode(',', $rangeExportOutward->NoPacks));
                } else {
                    $exportOutwardStock = $exportOutwardStock + (int) $rangeExportOutward->NoPacks;
                }
                $article->exportOutwardStock = $article->exportOutwardStock + $exportOutwardStock;
            }
            foreach ($rangePurchaseReturns as $rangePurchaseReturn) {
                $rangePurchaseReturnPacks = 0;
                if (strpos($rangePurchaseReturn->NoPacks, ',') != false) {
                    $rangePurchaseReturnPacks = $rangePurchaseReturnPacks + array_sum(explode(',', $rangePurchaseReturn->NoPacks));
                } else {
                    $rangePurchaseReturnPacks = $rangePurchaseReturnPacks + (int) $rangePurchaseReturn->NoPacks;
                }
                $article->purchaseReturnStock = $article->purchaseReturnStock + $rangePurchaseReturnPacks;
            }
            foreach ($rangeCons as $rangeCon) {
                $rangeConPacks = 0;
                if (strpos($rangeCon->NoPacks, ',') != false) {
                    $rangeConPacks = $rangeConPacks + array_sum(explode(',', $rangeCon->NoPacks));
                } else {
                    $rangeConPacks = $rangeConPacks + (int) $rangeCon->NoPacks;
                }
                $article->consStock = $article->consStock + $rangeConPacks;
            }
            foreach ($rangeShortages as $rangeShortage) {
                $rangeShortagePacks = 0;
                if (strpos($rangeShortage->NoPacks, ',') != false) {
                    $rangeShortagePacks = $rangeShortagePacks + array_sum(explode(',', $rangeShortage->NoPacks));
                } else {
                    $rangeShortagePacks = $rangeShortagePacks + (int) $rangeShortage->NoPacks;
                }
                $article->shortageStock = $article->shortageStock + $rangeShortagePacks;
            }
            // end subtraction range
            // end range stock
            //start close stock
            $article->closeStock = ($article->openStock + $article->inwardStock + $article->salesReturnStock + $article->proStock) - ($article->domesticOutwardStock + $article->exportOutwardStock + $article->consStock + $article->purchaseReturnStock
                + $article->shortageStock);
            //end close stock
            $InwardDate = DB::table('inward')->select("inward.*", DB::raw("DATE_FORMAT(InwardDate, '%d-%m-%Y') as formatted_dob"))->where('ArticleId', $article->Id)->first();
            if ($InwardDate) {
                $article->InwardDate = $InwardDate->formatted_dob;
            } else {
                $article->InwardDate = "-";
            }
            $article->totalInwardStock = $article->inwardStock + $article->salesReturnStock + $article->proStock;
            $article->totalOutwardStock = $article->domesticOutwardStock + $article->exportOutwardStock + $article->consStock + $article->purchaseReturnStock
                + $article->shortageStock;
            if ($article->openStock == 0 && $article->closeStock == 0 && $article->totalInwardStock == 0 && $article->totalOutwardStock == 0) {
                unset($articles[$key]);
            }
        }
        return array("startdate" => $startdate, "enddate" => $enddate, "data" => array_values($articles));
    }


    public function getarticallaunchdata($id)
    {
        return DB::select('SELECT atr.ArticleRate (SELECT * From FROM article a INNER JOIN articlerate atr, articlelaunch WHERE ArticleId = ' . $id . '');
    }


    public function getslaunchreport()
    {
        // $data = DB::select("SELECT i.SalesNoPacks, a.ArticleNumber, atr.ArticleRate,c.Title as CategoryName,b.Name as BrandName,i.InwardDate, al.LaunchDate , a.Id From article a INNER JOIN articlerate atr ON atr.ArticleId = a.Id left join category c on c.Id=a.CategoryId left join rangeseries r on r.Id=a.SeriesId left join subcategory sc on sc.Id=r.SubCategoryId left join brand b on b.Id=a.BrandId INNER JOIN articlelaunch al ON al.ArticleId = a.Id INNER JOIN inward i ON i.ArticleId = al.ArticleId");

        $data = DB::select("SELECT al.PartyId as partyId, i.SalesNoPacks, a.ArticleNumber, atr.ArticleRate, c.Title AS CategoryName, b.Name AS BrandName, i.InwardDate, al.LaunchDate, a.Id, SUM(i.SalesNoPacks) AS TotalPieces FROM article a INNER JOIN articlerate atr ON atr.ArticleId = a.Id LEFT JOIN category c ON c.Id = a.CategoryId LEFT JOIN rangeseries r ON r.Id = a.SeriesId LEFT JOIN subcategory sc ON sc.Id = r.SubCategoryId LEFT JOIN brand b ON b.Id = a.BrandId INNER JOIN articlelaunch al ON al.ArticleId = a.Id INNER JOIN inward i ON i.ArticleId = al.ArticleId GROUP BY a.ArticleNumber, atr.ArticleRate, c.Title, b.Name, i.InwardDate, al.LaunchDate, a.Id");
        // $data = DB::select("SELECT i.SalesNoPacks, a.ArticleNumber, atr.ArticleRate, c.Title as CategoryName, b.Name as BrandName, i.InwardDate, al.LaunchDate, a.Id, SUM(i.SalesNoPacks) as TotalPieces FROM article a INNER JOIN articlerate atr ON atr.ArticleId = a.Id LEFT JOIN category c ON c.Id = a.CategoryId LEFT JOIN rangeseries r ON r.Id = a.SeriesId LEFT JOIN subcategory sc ON sc.Id = r.SubCategoryId LEFT JOIN brand b ON b.Id = a.BrandId INNER JOIN articlelaunch al ON al.ArticleId = a.Id INNER JOIN inward i ON i.ArticleId = al.ArticleId GROUP BY a.ArticleNumber, atr.ArticleRate, c.Title, b.Name, i.InwardDate, al.LaunchDate, a.Id UNION SELECT i.SalesNoPacks, a.ArticleNumber, atr.ArticleRate, c.Title as CategoryName, b.Name as BrandName, i.InwardDate, '' as LaunchDate, a.Id, SUM(i.SalesNoPacks) as TotalPieces FROM article a INNER JOIN articlerate atr ON atr.ArticleId = a.Id LEFT JOIN category c ON c.Id = a.CategoryId LEFT JOIN rangeseries r ON r.Id = a.SeriesId LEFT JOIN subcategory sc ON sc.Id = r.SubCategoryId LEFT JOIN brand b ON b.Id = a.BrandId INNER JOIN inward i ON i.ArticleId = a.Id WHERE a.ArticleStatus = 1 and a.ArticleOpenFlag = 0 GROUP BY a.ArticleNumber, atr.ArticleRate, c.Title, b.Name, i.InwardDate, a.Id");

        $sum = 0;




        foreach ($data as $vl) {
            $object = (object) $vl;


            // Split the comma-separated values into an array
            // $values = explode(',', $object->SalesNoPacks);
            // Loop through the values and add them to the sum




            $dataString = $object->SalesNoPacks;
            $dataArray = explode(",", $dataString);
            $dataArray = array_map('intval', $dataArray);
            $sum = array_sum($dataArray);

            // foreach ($values as $value) {
            //     $sum += intval($value);
            // }

            $object->TotalPieces = $sum;


            $date = $vl->InwardDate;
            $carbonDate = Carbon::parse($date);
            $formattedDate = $carbonDate->format('d-m-Y');

            if ($vl->LaunchDate != null) {
                $date = $vl->LaunchDate;
                $carbonLDate = Carbon::parse($date);
                $formattedLDate = $carbonLDate->format('d-m-Y');
            }

            if ($vl->partyId == 0) {
                $object->LaunchDate = $formattedLDate;
            } else {
                $object->LaunchDate = '-';
                //REMOVE
                //  $object->LaunchDate = $formattedLDate;
                //REMOVE
            }


            $object->InwardDate = $formattedDate;



            if ($vl->ArticleOpenFlag = 0) {
                // $object->TotalPieces = $NoPacks;
                $article = Article::select('category.Title', 'article.StyleDescription', 'subcategory.Name as Subcategory', 'brand.Name as BrandName')
                    ->join('category', 'category.Id', '=', 'article.CategoryId')
                    ->leftjoin('subcategory', 'subcategory.Id', '=', 'article.SubCategoryId')
                    ->join('brand', 'brand.Id', '=', 'article.BrandId')
                    ->where('article.Id', $vl->Id)->first();


                // Initialize a variable to hold the sum



                $object->StyleDescription = $article->StyleDescription;
                $object->Subcategory = $article->Subcategory;
                $object->BrandName = $article->BrandName;
            }



            $sorecords = Outward::where('ArticleId', $vl->Id)->get();

            if (count($sorecords) > 0) {
                if (strpos($vl->SalesNoPacks, ',') !== false) {
                    $SalesNoPacks = [];
                    foreach (explode(",", $vl->SalesNoPacks) as $makearray) {
                        array_push($SalesNoPacks, 0);
                    }
                    foreach ($sorecords as $sorecord) {

                        for ($i = 0; $i < count(explode(",", $vl->SalesNoPacks)); $i++) {
                            $OutwardNoPacks = explode(",", $sorecord->NoPacks);
                            $SalesNoPacks[$i] = (int) $SalesNoPacks[$i] - (int) $OutwardNoPacks[$i];
                        }
                        if (array_sum($SalesNoPacks) > 0) {
                            $object->SoColorwise = implode(',', $SalesNoPacks);
                            $object->TotalPieces = array_sum($SalesNoPacks);
                        } else {
                            $object->SoColorwise = "";
                            $object->SoTotalQuantity = "";
                        }
                    }
                } else {
                    $SalesNoPacks = 0;
                    foreach ($sorecords as $sorecord) {
                        $SalesNoPacks = $SalesNoPacks - (int) $sorecord->NoPacks;
                    }
                    if ($SalesNoPacks > 0) {
                        $object->SoColorwise = $SalesNoPacks;
                        $object->SoTotalQuantity = $SalesNoPacks;
                    } else {
                        $object->SoColorwise = "";
                        $object->SoTotalQuantity = "";
                    }
                }
            } else {
                $object->SoColorwise = "";
                $object->SoTotalQuantity = "";
            }
        }



        $collection = collect($data);

        $filteredCollection = $collection->filter(function ($item) {
            return $item->TotalPieces != 0;
        });

        $filteredData = $filteredCollection->values()->all();

        $filteredData = collect($filteredData)->map(function ($item, $key) {
            $item->SerialNo = $key + 1;
            return $item;
        })->all();

        return array("data" => $filteredData);
    }

    // public function GetlaunchReport()
    // {
    //     $data = 
    // }   

    public function GetAllStocks()
    {

        //ORIGINAL
        // $data = DB::select("select dv.Id, dv.ArticleNumber, dv.ArticleOpenFlag, dv.SalesNoPacks, dv.TotalPieces, dv.ArticleColor, dv.ArticleSize, dv.ArticleRatio, dv.Colorflag, dv.Title, dv.BrandName, dv.Subcategory,  dv.SeriesName, dv.Series, dv.StyleDescription, CountNoPacks(dv.ArticleRatio) as TotalArticleRatio, DATE_FORMAT(dv.created_at, '%d-%m-%Y') as InwardDate  from (select * from (SELECT (case when pl.ProductStatus IS NULL then 1 else pl.ProductStatus end)  as ProductStatusData, a.Id, a.ArticleNumber, a.ArticleOpenFlag, inw.created_at, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalPieces, GROUP_CONCAT(DISTINCT CONCAT(ac.ArticleColorName) ORDER BY ac.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleRatio, cat.Colorflag, cat.Title, bn.Name as BrandName, subc.Name as Subcategory, rs.SeriesName, rs.Series, a.StyleDescription FROM `inward` inw inner join article a on a.Id=inw.ArticleId left join articlecolor ac on ac.ArticleId=a.Id left join articlesize asz on asz.ArticleId=a.Id left join po p on p.ArticleId=a.Id inner join category cat on cat.Id=a.CategoryId left join productlaunch pl on pl.ArticleId=a.Id left join brand bn on bn.Id= a.BrandId left join subcategory subc on subc.Id=a.SubCategoryId left join rangeseries rs on rs.Id=a.SeriesId group by a.Id) as ddd where ddd.ProductStatusData= 1 and ddd.ArticleOpenFlag=0 HAVING ddd.TotalPieces > 0 Union SELECT 1, a.Id, a.ArticleNumber, a.ArticleOpenFlag, '-', mxn.NoPacks as TotalPieces, '-', '-', '-', '-',c.Title, '-', '-', '-', '-', '-','-' FROM `mixnopacks` mxn inner join article a on a.Id=mxn.ArticleId left join po p on p.ArticleId=a.Id left join category c on c.Id=a.CategoryId where a.ArticleOpenFlag=1 HAVING TotalPieces > 0) as dv");

        // $data1 = DB::select("SELECT dv.Id, dv.ArticleNumber, dv.ArticleOpenFlag, dv.SalesNoPacks, dv.TotalPieces, dv.ArticleColor, dv.ArticleSize, dv.ArticleRatio, dv.Colorflag, dv.Title, dv.BrandName, dv.Subcategory, dv.SeriesName, dv.Series, dv.StyleDescription, TotalArticleRatio, DATE_FORMAT(dv.created_at, '%d-%m-%Y') as InwardDate FROM ( SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, inw.created_at, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalPieces, GROUP_CONCAT(DISTINCT CONCAT(ac.ArticleColorName) ORDER BY ac.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleRatio, cat.Colorflag, cat.Title, bn.Name as BrandName, subc.Name as Subcategory, rs.SeriesName, rs.Series, a.StyleDescription, CountNoPacks(a.ArticleRatio) as TotalArticleRatio FROM inward inw INNER JOIN article a ON a.Id = inw.ArticleId LEFT JOIN articlecolor ac ON ac.ArticleId = a.Id LEFT JOIN articlesize asz ON asz.ArticleId = a.Id INNER JOIN category cat ON cat.Id = a.CategoryId LEFT JOIN brand bn ON bn.Id = a.BrandId LEFT JOIN subcategory subc ON subc.Id = a.SubCategoryId LEFT JOIN rangeseries rs ON rs.Id = a.SeriesId WHERE a.ArticleOpenFlag = 0 AND inw.SalesNoPacks > 0 GROUP BY a.Id ) dv");
        // $data2 = DB::select("SELECT 1, a.Id, a.ArticleNumber, a.ArticleOpenFlag, '-', mxn.NoPacks as TotalPieces, '-', '-', '-', '-',c.Title, '-', '-', '-', '-', '-','-' FROM `mixnopacks` mxn inner join article a on a.Id=mxn.ArticleId left join po p on p.ArticleId=a.Id left join category c on c.Id=a.CategoryId where a.ArticleOpenFlag=1 HAVING TotalPieces > 0");


        // $data = array_merge($data1 );




        $array1 = DB::select("SELECT * from (SELECT dv.Id, dv.ArticleNumber, dv.ArticleOpenFlag, dv.SalesNoPacks, dv.TotalPieces, dv.ArticleColor, dv.ArticleSize, dv.ArticleRatio, dv.Colorflag, dv.Title, dv.BrandName, dv.Subcategory, dv.SeriesName, dv.Series, dv.StyleDescription, TotalArticleRatio, DATE_FORMAT(dv.created_at, '%d-%m-%Y') as InwardDate FROM ( SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, inw.created_at, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalPieces, GROUP_CONCAT(DISTINCT CONCAT(ac.ArticleColorName) ORDER BY ac.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleRatio, cat.Colorflag, cat.Title, bn.Name as BrandName, subc.Name as Subcategory, rs.SeriesName, rs.Series, a.StyleDescription, CountNoPacks(a.ArticleRatio) as TotalArticleRatio FROM inward inw INNER JOIN article a ON a.Id = inw.ArticleId LEFT JOIN articlecolor ac ON ac.ArticleId = a.Id LEFT JOIN articlesize asz ON asz.ArticleId = a.Id INNER JOIN category cat ON cat.Id = a.CategoryId LEFT JOIN brand bn ON bn.Id = a.BrandId LEFT JOIN subcategory subc ON subc.Id = a.SubCategoryId LEFT JOIN rangeseries rs ON rs.Id = a.SeriesId WHERE a.ArticleOpenFlag = 0 GROUP BY a.Id ) dv where dv.ArticleOpenFlag = 0)v where v.TotalPieces > 0");

        $array2 = DB::select("SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag,  '' AS ArticleColor, '' AS ArticleSize, '' AS ArticleRatio, '' as Colorflag, '' as BrandName, '' as Subcategory, '' as SeriesName, '' as Series, '' as StyleDescription, '' as TotalArticleRatio, '' as InwardDate, mxn.NoPacks as SalesNoPacks, mxn.NoPacks as TotalPieces, c.Title FROM `mixnopacks` mxn INNER JOIN article a ON a.Id = mxn.ArticleId LEFT JOIN po p ON p.ArticleId = a.Id LEFT JOIN category c ON c.Id = a.CategoryId WHERE a.ArticleOpenFlag = 1 AND mxn.NoPacks > 0 GROUP BY a.ArticleNumber");
        $result = array_merge($array1, $array2);

        // // Modify the values of the merged array
        // foreach ($result as &$item) {
        //     $item->SalesNoPacks = $item->ArticleOpenFlag ? "-" : $item->SalesNoPacks;
        //     $item->ArticleColor = $item->ArticleOpenFlag ? "-" : $item->ArticleColor;
        //     $item->ArticleSize = $item->ArticleOpenFlag ? "-" : $item->ArticleSize;
        //     $item->ArticleRatio = $item->ArticleOpenFlag ? "-" : $item->ArticleRatio;
        //     $item->Colorflag = $item->ArticleOpenFlag ? "-" : $item->Colorflag;
        //     $item->BrandName = $item->ArticleOpenFlag ? "-" : $item->BrandName;
        //     $item->Subcategory = $item->ArticleOpenFlag ? "-" : $item->Subcategory;
        //     $item->SeriesName = $item->ArticleOpenFlag ? "-" : $item->SeriesName;
        //     $item->Series = $item->ArticleOpenFlag ? "-" : $item->Series;
        //     $item->StyleDescription = $item->ArticleOpenFlag ? "-" : $item->StyleDescription;
        //     $item->TotalArticleRatio = $item->ArticleOpenFlag ? "-" : $item->TotalArticleRatio;
        //     $item->InwardDate = $item->ArticleOpenFlag ? "-" : $item->InwardDate;
        // }

        // unset($item); // Unset the reference to the last item


        // $mergedArray = $result;


        // return $result;


        // foreach ($result as $vl) {

        //     $object = (object) $vl;

        //     $NoPacks = $vl->SalesNoPacks;

        //     if ($vl->ArticleOpenFlag != 0) {

        //         $object->TotalPieces = $NoPacks;

        //         $article = Article::select('category.Title', 'article.StyleDescription', 'subcategory.Name as Subcategory', 'brand.Name as BrandName')

        //             ->join('category', 'category.Id', '=', 'article.CategoryId')

        //             ->leftjoin('subcategory', 'subcategory.Id', '=', 'article.SubCategoryId')

        //             ->join('brand', 'brand.Id', '=', 'article.BrandId')

        //             ->where('article.Id', $vl->Id)->first();

        //         $object->Title = $article->Title;

        //         $object->StyleDescription = $article->StyleDescription;

        //         $object->Subcategory = $article->Subcategory;

        //         $object->BrandName = $article->BrandName;

        //     }

        //     $sorecords = SO::where('ArticleId', $vl->Id)->where('Status', 0)->get();

        //     if (count($sorecords) > 0) {

        //         if (strpos($vl->SalesNoPacks, ',') !== false) {

        //             $SalesNoPacks = [];

        //             foreach (explode(",", $vl->SalesNoPacks) as $makearray) {

        //                 array_push($SalesNoPacks, 0);

        //             }

        //             foreach ($sorecords as $sorecord) {



        //                 for ($i = 0; $i < count(explode(",", $vl->SalesNoPacks)); $i++) {

        //                     $OutwardNoPacks = explode(",", $sorecord->OutwardNoPacks);

        //                     $SalesNoPacks[$i] = (int) $SalesNoPacks[$i] + (int) $OutwardNoPacks[$i];

        //                 }

        //                 if (array_sum($SalesNoPacks) > 0) {

        //                     $object->SoColorwise = implode(',', $SalesNoPacks);

        //                     $object->SoTotalQuantity = array_sum($SalesNoPacks);

        //                 } else {

        //                     $object->SoColorwise = "";

        //                     $object->SoTotalQuantity = "";

        //                 }

        //             }

        //         } else {

        //             $SalesNoPacks = 0;

        //             foreach ($sorecords as $sorecord) {

        //                 $SalesNoPacks = $SalesNoPacks + (int) $sorecord->OutwardNoPacks;

        //             }

        //             if ($SalesNoPacks > 0) {

        //                 $object->SoColorwise = $SalesNoPacks;

        //                 $object->SoTotalQuantity = $SalesNoPacks;

        //             } else {

        //                 $object->SoColorwise = "";

        //                 $object->SoTotalQuantity = "";

        //             }

        //         }

        //     } else {

        //         $object->SoColorwise = "";

        //         $object->SoTotalQuantity = "";
        //     }

        // }

        return array("data" => $result);
    }


    public function PostAllStocks(Request $request)
    {
        $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select("select count(*) as Total from (select dd.*, CountNoPacks(dd.ArticleRatio) as TotalArticleRatio  from (SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalPieces, GROUP_CONCAT(DISTINCT CONCAT(ac.ArticleColorName) ORDER BY ac.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleRatio, cat.Colorflag, cat.Title FROM `inward` inw inner join article a on a.Id=inw.ArticleId inner join articlecolor ac on ac.ArticleId=a.Id inner join articlesize asz on asz.ArticleId=a.Id left join po p on p.ArticleId=a.Id inner join category cat on cat.Id=a.CategoryId where a.ArticleOpenFlag=0 group by a.Id HAVING TotalPieces > 0 Union SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, '-', mxn.NoPacks as TotalPieces, '-', '-', '-', '-',c.Title FROM `mixnopacks` mxn inner join article a on a.Id=mxn.ArticleId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where a.ArticleOpenFlag=1 HAVING TotalPieces > 0) as dd) as ddd");
        $vnTotal = $vnddataTotal[0]->Total;
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "where ddd.PurchaseNumber like '%" . $search['value'] . "%' OR ddd.ArticleNumber like '%" . $search['value'] . "%' OR ddd.Name like '%" . $search['value'] . "%' OR ddd.Title like '%" . $search['value'] . "%'";
            $vnddataTotalFilter = DB::select("select count(*) as Total from (select dd.*, CountNoPacks(dd.ArticleRatio) as TotalArticleRatio  from (SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalPieces, GROUP_CONCAT(DISTINCT CONCAT(ac.ArticleColorName) ORDER BY ac.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleRatio, cat.Colorflag, cat.Title FROM `inward` inw inner join article a on a.Id=inw.ArticleId inner join articlecolor ac on ac.ArticleId=a.Id inner join articlesize asz on asz.ArticleId=a.Id left join po p on p.ArticleId=a.Id inner join category cat on cat.Id=a.CategoryId where a.ArticleOpenFlag=0 group by a.Id HAVING TotalPieces > 0 Union SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, '-', mxn.NoPacks as TotalPieces, '-', '-', '-', '-',c.Title FROM `mixnopacks` mxn inner join article a on a.Id=mxn.ArticleId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where a.ArticleOpenFlag=1 HAVING TotalPieces > 0) as dd) as ddd " . $searchstring);
            $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
        } else {
            $searchstring = "";
            $vnddataTotalFilterValue = $vnTotal;
        }
        $column = $data["order"][0]["column"];
        switch ($column) {
            case 1:
                $ordercolumn = "ddd.ArticleNumber";
                break;
            case 2:
                $ordercolumn = "ddd.Title";
                break;
            default:
                $ordercolumn = "ddd.ArticleNumber";
                break;
        }
        $order = "";
        if ($data["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
        }
        $vnddata = DB::select("select ddd.* from (select dd.*, CountNoPacks(dd.ArticleRatio) as TotalArticleRatio  from (SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalPieces, GROUP_CONCAT(DISTINCT CONCAT(ac.ArticleColorName) ORDER BY ac.Id SEPARATOR ',') as ArticleColor, GROUP_CONCAT(DISTINCT CONCAT(asz.ArticleSizeName) ORDER BY asz.Id SEPARATOR ',') as ArticleSize, a.ArticleRatio, cat.Colorflag, cat.Title FROM `inward` inw inner join article a on a.Id=inw.ArticleId inner join articlecolor ac on ac.ArticleId=a.Id inner join articlesize asz on asz.ArticleId=a.Id left join po p on p.ArticleId=a.Id inner join category cat on cat.Id=a.CategoryId where a.ArticleOpenFlag=0 group by a.Id HAVING TotalPieces > 0 Union SELECT a.Id, a.ArticleNumber, a.ArticleOpenFlag, '-', mxn.NoPacks as TotalPieces, '-', '-', '-', '-',c.Title FROM `mixnopacks` mxn inner join article a on a.Id=mxn.ArticleId left join po p on p.ArticleId=a.Id inner join category c on c.Id=a.CategoryId where a.ArticleOpenFlag=1 HAVING TotalPieces > 0) as dd) as ddd " . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length);
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

    public function exportallstocks()
    {
        return Excel::download(new ReportAllStocksExport(), 'Export-All-Stocks.xls');
    }

    public function GetPolist()
    {
        return DB::select('SELECT p.Id, pn.Id as POId, pn.PurchaseNumber, concat(fn.StartYear,\'-\',fn.EndYear) as FinancialYear, v.Name, c.Title, p.ArticleId, p.NumPacks, ar.ArticleNumber, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number left join inward inw on inw.ArticleId = p.ArticleId inner join financialyear fn on fn.Id=pn.FinancialYearId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id');
    }

    public function PostPoReport(Request $request)
    {
        $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select("SELECT count(*) as Total from (select p.Id From po p left join purchasenumber pn on pn.Id=p.PO_Number left join  inward inw on inw.ArticleId = p.ArticleId group by pn.Id) as f");
        $vnTotal = $vnddataTotal[0]->Total;
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "where ddd.PurchaseNumber like '%" . $search['value'] . "%' OR cast(ddd.PoDate as char) like '%" . $search['value'] . "%' OR ddd.ArticleNumber like '%" . $search['value'] . "%' OR ddd.Title like '%" . $search['value'] . "%' OR ddd.Name like '%" . $search['value'] . "%' OR ddd.Title like '%" . $search['value'] . "%' OR ddd.TotalPieces like '%" . $search['value'] . "%'";
            $vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT p.Id, pn.Id as POId, p.PoDate as pdate, DATE_FORMAT(p.PoDate, '%d-%m-%Y') as PoDate, GROUP_CONCAT(DISTINCT CONCAT(ar.ArticleNumber) ORDER BY ar.Id SEPARATOR ',') as ArticleNumber, concat(pn.PurchaseNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber, v.Name, c.Title, p.PO_Number, GetTotalPOPieces(p.PO_Number) as TotalPieces, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd " . $searchstring);
            $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
        } else {
            $searchstring = "";
            $vnddataTotalFilterValue = $vnTotal;
        }
        $column = $data["order"][0]["column"];
        switch ($column) {
            case 1:
                $ordercolumn = "date(ddd.pdate)";
                break;
            case 2:
                $ordercolumn = "CAST(ddd.PurchaseNumber as SIGNED INTEGER)";
                break;
            case 3:
                $ordercolumn = "ddd.Name";
                break;
            case 4:
                $ordercolumn = "CAST(ddd.TotalPieces as SIGNED INTEGER)";
                break;
            default:
                $ordercolumn = "ddd.Id";
                break;
        }

        $order = "";
        if ($data["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
        }
        $vnddata = DB::select("select ddd.* from (SELECT p.Id, pn.Id as POId, p.PoDate as pdate, DATE_FORMAT(p.PoDate, '%d-%m-%Y') as PoDate, GROUP_CONCAT(DISTINCT CONCAT(ar.ArticleNumber) ORDER BY ar.Id SEPARATOR ',') as ArticleNumber, concat(pn.PurchaseNumber,'/', fn.StartYear,'-',fn.EndYear) as PurchaseNumber, v.Name, c.Title, p.PO_Number, GetTotalPOPieces(p.PO_Number) as TotalPieces, inw.ArticleId as InwardArticleId, (Case ws.Name When NULL Then 0 else ws.Name END) as WorkStatusName From po p left join article ar on ar.Id=p.ArticleId left join vendor v on v.Id=p.VendorId left join category c on c.Id=p.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number  inner join financialyear fn on fn.Id=pn.FinancialYearId left join inward inw on inw.ArticleId = p.ArticleId left join workorderstatus ws on ws.Id=p.WorkOrderStatusId group by pn.Id) as ddd " . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length);
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

    public function GetInwardList()
    {
        return DB::select("SELECT VendorName(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as Name , GetTotalInwardPieces(igrn.GRN) as TotalInwardPieces, SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, inw.GRN,igrn.GRN as GRN_Number,igrn.InwardDate,GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY inw.Id SEPARATOR ',') as ArticleNumber, (CASE WHEN s.Id IS NULL THEN '0' ELSE '1' END) as SOID FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join article a on a.Id=inw.ArticleId left join po p on p.ArticleId=a.Id left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId group by inw.GRN  order by igrn.Id DESC");
    }

    public function PostReportInwardList(Request $request)
    {
        $data = $request->all();
        $search = $data["search"];
        $startnumber = $data["start"];
        $vnddataTotal = DB::select("select count(*) as Total from (select inw.GRN FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId left join po p on p.ArticleId=a.Id left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId group by GRN) as f");
        $vnTotal = $vnddataTotal[0]->Total;
        $length = $data["length"];
        if ($search['value'] != null && strlen($search['value']) > 2) {
            $searchstring = "where dd.GRN_Number like '%" . $search['value'] . "%' OR cast(dd.InwardDate as char) like '%" . $search['value'] . "%' OR  dd.Name like '%" . $search['value'] . "%' OR  dd.TotalInwardPieces like '%" . $search['value'] . "%' OR dd.Title like '%" . $search['value'] . "%' OR dd.PurchaseNumber like '%" . $search['value'] . "%' OR dd.ArticleNo like '%" . $search['value'] . "%'";
            $vnddataTotalFilter = DB::select("select count(*) as Total  from (select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, VendorName(a.Id) as Name, GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber, DATE_FORMAT(igrn.InwardDate, '%d-%m-%Y') as InwardDate, c.Title FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId left join po p on p.ArticleId=a.Id left join vendor v on v.Id=p.VendorId left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=a.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number left join financialyear fy1 on fy1.Id=pn.FinancialYearId  group by GRN order by GRN asc) as dd " . $searchstring . " order by dd.GRN DESC");
            $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
        } else {
            $searchstring = "";
            $vnddataTotalFilterValue = $vnTotal;
        }
        $column = $data["order"][0]["column"];
        switch ($column) {
            case 1:
                $ordercolumn = "dd.GRN";
                break;
            case 2:
                $ordercolumn = "dd.Name";
                break;
            case 3:
                $ordercolumn = "dd.Title";
                break;
            case 5:
                $ordercolumn = "date(dd.inwdate)";
                break;
            case 6:
                $ordercolumn = "dd.PurchaseNumber";
                break;
            default:
                $ordercolumn = "dd.GRN";
                break;
        }
        $order = "";
        if ($data["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data["order"][0]["dir"];
        }
        $vnddata = DB::select("select dd.* from (select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, inwc.Notes,'Cancellation',VendorName(a.Id) as Name, igrn.Id, GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inwcl.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d-%m-%Y') as InwardDate, 'SODataCheck', c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM inwardgrn igrn inner join `inwardcancellationlogs` inwcl on igrn.Id=inwcl.GRN inner join `inwardcancellation` inwc on igrn.Id=inwc.GRN inner join financialyear fn on fn.Id=igrn.FinancialYearId left join article a on a.Id=inwcl.ArticleId left join po p on p.ArticleId=a.Id left join vendor v on v.Id=p.VendorId left join category c on c.Id=a.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number left join financialyear fy1 on fy1.Id=pn.FinancialYearId group by inwc.GRN UNION ALL select GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY a.Id SEPARATOR ',') as ArticleNo, '',0,VendorName(a.Id) as Name,igrn.Id,GetTotalInwardPieces(igrn.Id) as TotalInwardPieces, inw.GRN,concat(igrn.GRN, '/',fn.StartYear,'-',fn.EndYear) as GRN_Number,igrn.InwardDate as inwdate,DATE_FORMAT(igrn.InwardDate, '%d-%m-%Y') as InwardDate,SoInwardList(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY inw.Id SEPARATOR ',')) as SODataCheck, c.Title, concat(pn.PurchaseNumber,'/' ,fy1.StartYear,'-',fy1.EndYear) as PurchaseNumber FROM `inward` inw inner join inwardgrn igrn on igrn.Id=inw.GRN   inner join financialyear fn on fn.Id=igrn.FinancialYearId inner join article a on a.Id=inw.ArticleId left join po p on p.ArticleId=a.Id left join vendor v on v.Id=p.VendorId  left join so s on s.ArticleId=inw.ArticleId inner join category c on c.Id=a.CategoryId left join purchasenumber pn on pn.Id=p.PO_Number inner join financialyear fy1 on fy1.Id=pn.FinancialYearId  where a.Id not in (SELECT Id FROM `article` where ArticleOpenFlag = 1) group by inw.GRN) as dd " . $searchstring . " " . $order . " limit " . $data["start"] . "," . $length);
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

    public function GetSolist($UserId)
    {
        return DB::select("SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate, son.Destination, son.Transporter, son.UserId, concat(FirstCharacterConcat(u.Name), son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId inner join users u on u.Id=son.UserId inner join financialyear fn on fn.Id=son.FinancialYearId group by s.SoNumberId");
    }

    public function PostReportSolist(Request $request)
    {
        $data = $request->all();
        $search = $data['dataTablesParameters']["search"];
        $UserId = $data['UserID'];
        $startnumber = $data['dataTablesParameters']["start"];
        $vnddataTotal = DB::select("select count(*) as Total from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate, son.Destination, son.Transporter, son.UserId, concat(IFNULL(partyuser.Name,u.Name),son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId left join users partyuser on partyuser.Id=p.UserId inner join users u on u.Id=son.UserId inner join financialyear fn on fn.Id=son.FinancialYearId group by s.SoNumberId) as d");
        $vntotal = $vnddataTotal[0]->Total;
        $length = $data['dataTablesParameters']["length"];
        $userrole = DB::select("SELECT Role FROM `users`where Id='" . $UserId . "'");
        if ($userrole[0]->Role == 2) {
            $wherecustom = "";
            if ($search['value'] != null && strlen($search['value']) > 2) {
                $searchstring = " where d.SoNumber like '%" . $search['value'] . "%' OR d.Name like '%" . $search['value'] . "%' OR CAST(d.TotalSoPieces as SIGNED INTEGER) like '%" . $search['value'] . "%'   OR cast(d.cdate as char) like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%'";
                $vnddataTotalFilter = DB::select("select count(*) as Total from (SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate,DATE_FORMAT(son.SoDate, \"%d-%m-%Y\") as cdate, son.Destination, son.Transporter, son.UserId, concat(IFNULL(partyuser.Name,u.Name),son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId left join users partyuser on partyuser.Id=p.UserId  inner join users u on u.Id=son.UserId inner join financialyear fn on fn.Id=son.FinancialYearId group by s.SoNumberId) as d " . $searchstring);
                $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
            } else {
                $searchstring = "";
                $vnddataTotalFilterValue = $vntotal;
            }
        } else {
            $wherecustom = "where d.UserId='" . $UserId . "'";
            if ($search['value'] != null && strlen($search['value']) > 2) {
                $searchstring = " where d.SoNumber like '%" . $search['value'] . "%' OR d.Name like '%" . $search['value'] . "%' OR CAST(d.TotalSoPieces as SIGNED INTEGER) like '%" . $search['value'] . "%'   OR cast(d.cdate as char) like '%" . $search['value'] . "%' OR d.ArticleNumber like '%" . $search['value'] . "%'";
                $vnddataTotalFilter = DB::select("SELECT GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate,DATE_FORMAT(son.SoDate, \"%d-%m-%Y\") as cdate, son.Destination, son.Transporter, son.UserId, concat(IFNULL(partyuser.Name,u.Name),son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId left join users partyuser on partyuser.Id=p.UserId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId " . $wherecustom . " group by s.SoNumberId) as d " . $searchstring);
                $vnddataTotalFilterValue = $vnddataTotalFilter[0]->Total;
            } else {
                $searchstring = "";
                $vnddataTotalFilterValue = $vntotal;
            }
        }
        $column = $data['dataTablesParameters']["order"][0]["column"];
        switch ($column) {
            case 1:
                $ordercolumn = "d.SoNumber";
                break;
            case 2:
                $ordercolumn = "d.Name";
                break;
            case 3:
                $ordercolumn = "CAST(d.TotalSoPieces as SIGNED INTEGER)";
                break;
            case 4:
                $ordercolumn = "date(d.SoDate)";
                break;
            default:
                $ordercolumn = "d.SoDate";
                break;
        }
        $order = "";
        if ($data['dataTablesParameters']["order"][0]["dir"]) {
            $order = "order by " . $ordercolumn . " " . $data['dataTablesParameters']["order"][0]["dir"];
        }
        $vnddata = DB::select("select d.* from (SELECT u.Name as UserName ,p.UserId as PartyUserId , GetTotalSOOrderPieces(son.Id) as TotalSoPieces, son.Id, p.Name, OutwardSoList(son.Id) as OWID, SalesReturnArticle(GROUP_CONCAT(DISTINCT CONCAT(a.Id) ORDER BY son.Id SEPARATOR ',')) as SalesRetrunAssign, GROUP_CONCAT(DISTINCT CONCAT(a.ArticleNumber) ORDER BY son.Id SEPARATOR ',') as ArticleNumber, son.SoDate,DATE_FORMAT(son.SoDate, \"%d-%m-%Y\") as cdate, son.Destination, son.Transporter, son.UserId, concat(IFNULL(partyuser.Name,u.Name),son.SoNumber, '/',fn.StartYear,'-',fn.EndYear) as SoNumber FROM `so` s inner join article a on a.Id=s.ArticleId left join sonumber son on s.SoNumberId=son.Id inner join party p on p.Id=son.PartyId left join users partyuser on partyuser.Id=p.UserId inner join financialyear fn on fn.Id=son.FinancialYearId inner join users u on u.Id=son.UserId group by s.SoNumberId) as d " . $wherecustom . " " . $searchstring . " " . $order . " limit " . $data['dataTablesParameters']["start"] . "," . $length);
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


    public function GetOutletStocksRefil($PartyId)
    {

        if ($PartyId == 4) {

            $articles = $this->GetAllStocks();

            foreach ($articles['data'] as &$item) {
                $item->OLSTOCKS = '-';
                $item->OLTotalPieces = '-';
                $item->outwd = '-';
            }

            return $articles;

        } else {


            $articles = $this->GetAllStocks();
            $array1 = $articles['data'];


            $articlesArray = DB::select('(select `article`.`ArticleNumber`, `article`.`Id` as `ArticleId` from `transportoutlet` right join `outward` on `transportoutlet`.`OutwardNumberId` = `outward`.`OutwardNumberId` inner join `article` on `article`.`Id` = `outward`.`ArticleId`  where `transportoutlet`.`TransportStatus` = 1 AND `transportoutlet`.`PartyId` = ' . $PartyId . ' )  union (select `article`.`ArticleNumber`, `article`.`Id` as `ArticleId` from `transportoutwardpacks` inner join `article` on `article`.`Id` = `transportoutwardpacks`.`ArticleId` where `transportoutwardpacks`.`OutwardId` = 0 AND `transportoutwardpacks`.`PartyId` = ' . $PartyId . ') order by `ArticleId` asc');
            $collectionArticles = collect($articlesArray);
            $articles = $collectionArticles->unique()->values()->all();
            foreach ($articles as $key => $article) {
                $objectArticle = $article;
                $articleArray = (array) $article;
                $articleData = DB::select('select `category`.`Colorflag`, `article`.`ArticleRatio`,`article`.`ArticleOpenFlag`, `category`.`Title`, `brand`.`Name` as `BrandName`, `subcategory`.`Name` as `Subcategory`, `rangeseries`.`SeriesName`, `rangeseries`.`Series`, `article`.`StyleDescription` from `article` inner join `category` on `article`.`CategoryId` = `category`.`Id` left join `brand` on `brand`.`Id` = `article`.`BrandId` left join `subcategory` on `subcategory`.`Id` = `article`.`SubCategoryId` left join `rangeseries` on `rangeseries`.`Id` = `article`.`SeriesId` where `article`.`Id` = ' . $articleArray['ArticleId']);
                $articlesColors = DB::select("select   GROUP_CONCAT(DISTINCT CONCAT(articlesize.ArticleSizeName) ORDER BY articlesize.Id SEPARATOR ',') as ArticleSize , GROUP_CONCAT(DISTINCT CONCAT(articlecolor.ArticleColorName) ORDER BY articlecolor.Id SEPARATOR ',') as ArticleColor from article left join articlecolor on articlecolor.ArticleId=article.Id left join articlesize on articlesize.ArticleId=article.Id  where article.Id=" . $articleArray['ArticleId']);
                $articleData = (array) $articleData[0];
                $objectArticle->Colorflag = $articleData['Colorflag'];
                $objectArticle->ArticleRatio = $articleData['ArticleRatio'];
                if ($articleData['ArticleRatio']) {
                    $objectArticle->TotalArticleRatio = array_sum(explode(",", $articleData['ArticleRatio']));
                } else {
                    $objectArticle->TotalArticleRatio = 0;
                }
                $objectArticle->Title = $articleData['Title'];
                $objectArticle->BrandName = $articleData['BrandName'];
                $objectArticle->Subcategory = $articleData['Subcategory'];
                $objectArticle->SeriesName = $articleData['SeriesName'];
                $objectArticle->Series = $articleData['Series'];
                $objectArticle->StyleDescription = $articleData['StyleDescription'];
                $outletArticleColor = Outletimport::where('ArticleId', $articleArray['ArticleId'])->where('PartyId', $PartyId)->first();
                if ($outletArticleColor) {
                    if (json_decode($outletArticleColor->ArticleColor)) {
                        $objectArticle->ArticleColor = implode(',', array_column(json_decode($outletArticleColor->ArticleColor), 'Name'));
                    } else {
                        $objectArticle->ArticleColor = "";
                    }
                } else {
                    $articleouter = Article::select('ArticleColor')->where('Id', $articleArray['ArticleId'])->first();
                    if (json_decode($articleouter['ArticleColor'])) {
                        $objectArticle->ArticleColor = implode(',', array_column(json_decode($articleouter['ArticleColor']), 'Name'));
                    } else {
                        $objectArticle->ArticleColor = "";
                    }
                }
                $objectArticle->ArticleSize = $articlesColors[0]->ArticleSize;
                // if ($PartyId == 1) {
                // $allRecords = DB::select("(select outletsalesreturn.NoPacks as NoPacks, 2 as type, outletsalesreturnnumber.CreatedDate as SortDate, from outletsalesreturn inner join outletsalesreturnnumber on outletsalesreturn.SalesReturnNumber = outletsalesreturnnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletsalesreturn.OutletPartyId = '" . $PartyId . "')) union (select outlet.NoPacks as NoPacks, 1 as type, outletnumber.CreatedDate as SortDate from outlet inner join outletnumber on outlet.OutletNumberId = outletnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletnumber.PartyId = '" . $PartyId . "')) union (select outward.NoPacks as NoPacks, 0 as type, outwardnumber.created_at as SortDate from outward inner join transportoutlet on outward.OutwardNumberId = transportoutlet.OutwardNumberId inner join outwardnumber on outward.OutwardNumberId = outwardnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and transportoutlet.TransportStatus = 1 and outward.PartyId = '" . $PartyId . "')) union (select salesreturn.NoPacks as NoPacks, 3 as type ,salesreturnnumber.CreatedDate as SortDate from outward inner join salesreturn on salesreturn.OutwardId = outward.Id inner join salesreturnnumber on salesreturnnumber.Id = salesreturn.SalesReturnNumber where (outward.PartyId = '" . $PartyId . "' and outward.ArticleId = '" . $articleArray['ArticleId'] . "')) order by SortDate asc");
                $allRecords = DB::select("select * from (select outletsalesreturn.NoPacks as NoPacks, 2 as type, outletsalesreturnnumber.CreatedDate as SortDate from outletsalesreturn inner join outletsalesreturnnumber on outletsalesreturn.SalesReturnNumber = outletsalesreturnnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletsalesreturn.OutletPartyId = '" . $PartyId . "') union (select outlet.NoPacks as NoPacks, 1 as type, outletnumber.CreatedDate as SortDate from outlet inner join outletnumber on outlet.OutletNumberId = outletnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletnumber.PartyId = '" . $PartyId . "')) union (select outward.NoPacks as NoPacks, 0 as type, transportoutlet.ReceivedDate as SortDate from outward inner join transportoutlet on outward.OutwardNumberId = transportoutlet.OutwardNumberId inner join outwardnumber on outward.OutwardNumberId = outwardnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and transportoutlet.TransportStatus = 1 and outward.PartyId = '" . $PartyId . "')) union (select salesreturn.NoPacks as NoPacks, 3 as type, salesreturnnumber.CreatedDate as SortDate from outward inner join salesreturn on salesreturn.OutwardId = outward.Id inner join salesreturnnumber on salesreturnnumber.Id = salesreturn.SalesReturnNumber where (outward.PartyId = '" . $PartyId . "' and outward.ArticleId = '" . $articleArray['ArticleId'] . "')) ) as dd where SortDate > '2021-12-31' order by dd.SortDate asc");
                // } else {
                // $allRecords = DB::select('(select `outletsalesreturn`.`NoPacks` as `NoPacks`, 2 as type, `outletsalesreturnnumber`.`CreatedDate` as `SortDate` from `outletsalesreturn` inner join `outletsalesreturnnumber` on `outletsalesreturn`.`SalesReturnNumber` = `outletsalesreturnnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletsalesreturn`.`OutletPartyId` = ' . $PartyId . ')) union (select `outlet`.`NoPacks` as `NoPacks`, 1 as type, `outletnumber`.`CreatedDate` as `SortDate` from `outlet` inner join `outletnumber` on `outlet`.`OutletNumberId` = `outletnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletnumber`.`PartyId` = ' . $PartyId . ')) union (select `outward`.`NoPacks` as `NoPacks`, 0 as type, `outwardnumber`.`created_at` as `SortDate` from `outward` inner join `transportoutlet` on `outward`.`OutwardNumberId` = `transportoutlet`.`OutwardNumberId` inner join `outwardnumber` on `outward`.`OutwardNumberId` = `outwardnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `transportoutlet`.`TransportStatus` = 1 and `outward`.`PartyId` = ' . $PartyId . ')) union (select `salesreturn`.`NoPacks` as `NoPacks`, 3 as type ,`salesreturnnumber`.`CreatedDate` as `SortDate` from `outward` inner join `salesreturn` on `salesreturn`.`OutwardId` = `outward`.`Id` inner join `salesreturnnumber` on `salesreturnnumber`.`Id` = `salesreturn`.`SalesReturnNumber` where (`outward`.`PartyId` = ' . $PartyId . ' and `outward`.`ArticleId` = ' . $articleArray['ArticleId'] . ')) order by `SortDate` asc');
                // $allRecords = DB::select("select * from (select outletsalesreturn.NoPacks as NoPacks, 2 as type, outletsalesreturnnumber.CreatedDate as SortDate from outletsalesreturn inner join outletsalesreturnnumber on outletsalesreturn.SalesReturnNumber = outletsalesreturnnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletsalesreturn.OutletPartyId = '" . $PartyId . "') union (select outlet.NoPacks as NoPacks, 1 as type, outletnumber.CreatedDate as SortDate from outlet inner join outletnumber on outlet.OutletNumberId = outletnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletnumber.PartyId = '" . $PartyId . "')) union (select outward.NoPacks as NoPacks, 0 as type, transportoutlet.ReceivedDate as SortDate from outward inner join transportoutlet on outward.OutwardNumberId = transportoutlet.OutwardNumberId inner join outwardnumber on outward.OutwardNumberId = outwardnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and transportoutlet.TransportStatus = 1 and outward.PartyId = '" . $PartyId . "')) union (select salesreturn.NoPacks as NoPacks, 3 as type, salesreturnnumber.CreatedDate as SortDate from outward inner join salesreturn on salesreturn.OutwardId = outward.Id inner join salesreturnnumber on salesreturnnumber.Id = salesreturn.SalesReturnNumber where (outward.PartyId = '" . $PartyId . "' and outward.ArticleId = '" . $articleArray['ArticleId'] . "')) ) as dd");
                // }
                if (!isset($allRecords[0])) {
                    $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->where('PartyId', $PartyId)->first();
                    if ($outletArticle) {
                        $outletArticleColors = json_decode($outletArticle->ArticleColor);
                    } else {
                        $outletArticleColors = json_decode($articlesColors[0]->ArticleColor);
                    }
                    $outletArticleColors = (array) $outletArticleColors;
                    if (count($outletArticleColors) > 0) {
                        $SalesNoPacks = [];
                        foreach ($outletArticleColors as $makearray) {
                            array_push($SalesNoPacks, 0);
                        }
                        $getTransportOutwardpacks = TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
                        if (count($getTransportOutwardpacks) != 0) {
                            // Retrieve outlet article information based on ArticleId and PartyId
                            $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])
                                ->where('PartyId', $PartyId)
                                ->first();

                            if ($outletArticle) {
                                // If outlet article exists, decode ArticleColor JSON
                                $outletArticleColors = json_decode($outletArticle->ArticleColor, true);
                            } else {
                                // If outlet article doesn't exist, retrieve ArticleColor from Article table
                                $article = Article::select('ArticleColor')->where('Id',  $articleArray['ArticleId'])->first();
                                $outletArticleColors = json_decode($article['ArticleColor'], true);
                            }

                            // Initialize SalesNoPacks array with 0 for each color ID
                            foreach ($outletArticleColors as $outletArticleColor) {
                                $SalesNoPacks[$outletArticleColor['Id']] = 0;
                            }

                            // Iterate through the getTransportOutwardpacks
                            foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                                $colorId = $getTransportOutwardpack->ColorId;
                                // Check if the colorId is set in $SalesNoPacks
                                if (isset($SalesNoPacks[$colorId])) {
                                    // Subtract NoPacks from SalesNoPacks for the corresponding color ID
                                    $SalesNoPacks[$colorId] = $getTransportOutwardpack->NoPacks;
                                }
                            }
                        }
                        $newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
                        $objectArticle->STOCKS = $newimplodeSalesNoPacks;
                        if (array_sum($SalesNoPacks) <= 0) {
                            unset($articles[$key]);
                        } else {
                            $objectArticle->TotalPieces = array_sum($SalesNoPacks);
                            $objectArticle->salespacks = array_sum($SalesNoPacks);
                        }
                        // return $objectArticle->TotalPieces;
                    } else {
                        $transportOutwardpacks = TransportOutwardpacks::select('NoPacks')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
                        $TotalTransportOutwardpacks = 0;
                        if (count($transportOutwardpacks) != 0) {
                            $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                            $getTransportOutwardpacks = $collectionTransportOutwardpacks->unique()->values()->all();
                            foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                                $TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
                            }
                        }
                        $TotalInwardPacks = $TotalTransportOutwardpacks;
                        $TotalOutwardPacks = 0;
                        $totalStock = $TotalInwardPacks - $TotalOutwardPacks;
                        $objectArticle->STOCKS = $totalStock;
                        if ($totalStock <= 0) {
                            unset($articles[$key]);
                        } else {
                            $objectArticle->TotalPieces = $totalStock;
                        }
                    }
                } else {
                    if (strpos($allRecords[0]->NoPacks, ',')) {
                        $SalesNoPacks = [];
                        foreach (explode(",", $allRecords[0]->NoPacks) as $makearray) {
                            array_push($SalesNoPacks, 0);
                        }
                        $transportOutwardpacks = TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
                        $TotalTransportOutwardpacks = 0;
                        if (count($transportOutwardpacks) != 0) {
                            $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                            $getTransportOutwardpacks = $collectionTransportOutwardpacks->unique()->values()->all();
                            foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                                $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->where('PartyId', $PartyId)->first();
                                if ($outletArticle) {
                                    $articleColors = json_decode($outletArticle->ArticleColor);
                                } else {
                                    $article = Article::select('ArticleColor')->where('Id', $articleArray['ArticleId'])->first();
                                    $articleColors = json_decode($article['ArticleColor']);
                                }
                                $count = 0;
                                foreach ($articleColors as $articlecolor) {
                                    if ($articlecolor->Id == $getTransportOutwardpack->ColorId) {
                                        if (!isset($SalesNoPacks[$count])) {
                                            array_push($SalesNoPacks, 0);
                                        }
                                        $SalesNoPacks[$count] = $SalesNoPacks[$count] + $getTransportOutwardpack->NoPacks;
                                    }
                                    $count = $count + 1;
                                }
                            }
                        }
                        $SalesNoPacks = []; // Initialize the array

                        foreach ($allRecords as $allRecord) {
                            $noPacks = explode(",", $allRecord->NoPacks);
                            
                            for ($i = 0; $i < count($noPacks); $i++) {
                                if (!isset($SalesNoPacks[$i])) {
                                    $SalesNoPacks[$i] = 0; // Initialize with zero if index is not set
                                }
                                
                                if ($allRecord->type == 0 || $allRecord->type == 2) {
                                    $SalesNoPacks[$i] += $noPacks[$i];
                                } elseif ($allRecord->type == 1 || $allRecord->type == 3) {
                                    $SalesNoPacks[$i] -= $noPacks[$i];
                                }
                            }
                        }
                        $newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
                        $objectArticle->STOCKS = $newimplodeSalesNoPacks;
                        if (array_sum($SalesNoPacks) <= 0) {
                            unset($articles[$key]);
                        } else {
                            $objectArticle->TotalPieces = array_sum($SalesNoPacks);
                        }
                    } else {
                        $transportOutwardpacks = TransportOutwardpacks::select('NoPacks')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
                        $TotalTransportOutwardpacks = 0;
                        if (count($transportOutwardpacks) != 0) {
                            $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                            $getTransportOutwardpacks = $collectionTransportOutwardpacks->unique()->values()->all();
                            foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                                $TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
                            }
                        }

                        $TotalInwardPacks = $TotalTransportOutwardpacks;
                        $TotalOutwardPacks = 0;
                        foreach ($allRecords as $allRecord) {
                            if ($allRecord->type == 0) {
                                $TotalInwardPacks = $TotalInwardPacks + (int) $allRecord->NoPacks;
                            } elseif ($allRecord->type == 1) {
                                $TotalOutwardPacks = $TotalOutwardPacks + (int) $allRecord->NoPacks;
                            } elseif ($allRecord->type == 2) {
                                $TotalInwardPacks = $TotalInwardPacks + (int) $allRecord->NoPacks;
                            } elseif ($allRecord->type == 3) {
                                $TotalOutwardPacks = $TotalOutwardPacks + (int) $allRecord->NoPacks;
                            }
                        }
                        $totalStock = $TotalInwardPacks - $TotalOutwardPacks;
                        $objectArticle->STOCKS = $totalStock;
                        if ($totalStock <= 0) {
                            unset($articles[$key]);
                        } else {
                            $objectArticle->TotalPieces = $totalStock;
                        }
                    }
                }
            }
            $partyName = Party::select('Name')->where('Id', $PartyId)->first();


            $array2 = array("data" => array_values($articles));


            $array2 = $array2['data'];

            // return $array1;


            $mergedArray = [];
            foreach ($array1 as $item1) {
                $found = false;
                foreach ($array2 as $item2) {
                    if ($item2->ArticleNumber === $item1->ArticleNumber) {
                        $item1->OLSTOCKS = $item2->STOCKS;
                        $item1->OLTotalPieces = $item2->TotalPieces;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $item1->OLSTOCKS = '-';
                    $item1->OLTotalPieces = '-';
                }
                $mergedArray[] = $item1;
            }


            $currentDate = Carbon::now()->toDateString();
            $fromd = '2021-07-04';
            $tod = $currentDate;
            $id = $PartyId;
            $Outward_for_outlet = $this->outwardOutletReport($fromd, $tod, $id);

            $Outward_for_outlet = $Outward_for_outlet['data'];



            $mergedArray2 = [];
            foreach ($mergedArray as $item1) {
                $found = false;
                foreach ($Outward_for_outlet as $item2) {
                    if ($item2->ArticleNumber === $item1->ArticleNumber) {
                        $item1->outwd = $item2->Quantity;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $item1->outwd = '-';
                }
                $mergedArray2[] = $item1;
            }


            // return $Outward_for_outlet;



            return array("data" => array_values($mergedArray2), 'PartyName' => $partyName->Name);
        }

    }

    //New Function Create

    public function GetOutletStocks($PartyId)
    {

        if($PartyId == 4){
           
            $articlesArray = DB::select('(select `article`.`ArticleNumber`, `article`.`Id` as `ArticleId` from `transportoutlet` right join `outward` on `transportoutlet`.`OutwardNumberId` = `outward`.`OutwardNumberId` inner join `article` on `article`.`Id` = `outward`.`ArticleId`  where `transportoutlet`.`TransportStatus` = 1 )  union (select `article`.`ArticleNumber`, `article`.`Id` as `ArticleId` from `transportoutwardpacks` inner join `article` on `article`.`Id` = `transportoutwardpacks`.`ArticleId` where `transportoutwardpacks`.`OutwardId` = 0 ) order by `ArticleId` asc');
            $collectionArticles = collect($articlesArray);
            $articles  = $collectionArticles->unique()->values()->all();
            foreach ($articles as $key => $article) {
                $objectArticle = $article;
                $articleArray = (array)$article;
                $articleData = DB::select('select `category`.`Colorflag`, `article`.`ArticleRatio`,`article`.`ArticleOpenFlag`, `category`.`Title`, `brand`.`Name` as `BrandName`, `subcategory`.`Name` as `Subcategory`, `rangeseries`.`SeriesName`, `rangeseries`.`Series`, `article`.`StyleDescription` from `article` inner join `category` on `article`.`CategoryId` = `category`.`Id` left join `brand` on `brand`.`Id` = `article`.`BrandId` left join `subcategory` on `subcategory`.`Id` = `article`.`SubCategoryId` left join `rangeseries` on `rangeseries`.`Id` = `article`.`SeriesId` where `article`.`Id` = ' . $articleArray['ArticleId']);
                $articlesColors = DB::select("select   GROUP_CONCAT(DISTINCT CONCAT(articlesize.ArticleSizeName) ORDER BY articlesize.Id SEPARATOR ',') as ArticleSize , GROUP_CONCAT(DISTINCT CONCAT(articlecolor.ArticleColorName) ORDER BY articlecolor.Id SEPARATOR ',') as ArticleColor from article left join articlecolor on articlecolor.ArticleId=article.Id left join articlesize on articlesize.ArticleId=article.Id  where article.Id=" . $articleArray['ArticleId']);
                $articleData = (array)$articleData[0];
                $objectArticle->Colorflag = $articleData['Colorflag'];
                $objectArticle->ArticleRatio = $articleData['ArticleRatio'];
                if ($articleData['ArticleRatio']) {
                    $objectArticle->TotalArticleRatio = array_sum(explode(",", $articleData['ArticleRatio']));
                } else {
                    $objectArticle->TotalArticleRatio = 0;
                }
                $objectArticle->Title = $articleData['Title'];
                $objectArticle->BrandName = $articleData['BrandName'];
                $objectArticle->Subcategory = $articleData['Subcategory'];
                $objectArticle->SeriesName = $articleData['SeriesName'];
                $objectArticle->Series = $articleData['Series'];
                $objectArticle->StyleDescription = $articleData['StyleDescription'];
                $outletArticleColor = Outletimport::where('ArticleId', $articleArray['ArticleId'])->first();
                if ($outletArticleColor) {
                    if (json_decode($outletArticleColor->ArticleColor)) {
                        $objectArticle->ArticleColor = implode(',', array_column(json_decode($outletArticleColor->ArticleColor), 'Name'));
                    } else {
                        $objectArticle->ArticleColor = "";
                    }
                } else {
                    $articleouter = Article::select('ArticleColor')->where('Id',  $articleArray['ArticleId'])->first();
                    if (json_decode($articleouter['ArticleColor'])) {
                        $objectArticle->ArticleColor  = implode(',', array_column(json_decode($articleouter['ArticleColor']), 'Name'));
                    } else {
                        $objectArticle->ArticleColor  = "";
                    }
                }
                $objectArticle->ArticleSize = $articlesColors[0]->ArticleSize;
                // if ($PartyId == 1) {
                // $allRecords = DB::select("(select outletsalesreturn.NoPacks as NoPacks, 2 as type, outletsalesreturnnumber.CreatedDate as SortDate, from outletsalesreturn inner join outletsalesreturnnumber on outletsalesreturn.SalesReturnNumber = outletsalesreturnnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletsalesreturn.OutletPartyId = '" . $PartyId . "')) union (select outlet.NoPacks as NoPacks, 1 as type, outletnumber.CreatedDate as SortDate from outlet inner join outletnumber on outlet.OutletNumberId = outletnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletnumber.PartyId = '" . $PartyId . "')) union (select outward.NoPacks as NoPacks, 0 as type, outwardnumber.created_at as SortDate from outward inner join transportoutlet on outward.OutwardNumberId = transportoutlet.OutwardNumberId inner join outwardnumber on outward.OutwardNumberId = outwardnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and transportoutlet.TransportStatus = 1 and outward.PartyId = '" . $PartyId . "')) union (select salesreturn.NoPacks as NoPacks, 3 as type ,salesreturnnumber.CreatedDate as SortDate from outward inner join salesreturn on salesreturn.OutwardId = outward.Id inner join salesreturnnumber on salesreturnnumber.Id = salesreturn.SalesReturnNumber where (outward.PartyId = '" . $PartyId . "' and outward.ArticleId = '" . $articleArray['ArticleId'] . "')) order by SortDate asc");
                $allRecords = DB::select("select * from (select outletsalesreturn.NoPacks as NoPacks, 2 as type, outletsalesreturnnumber.CreatedDate as SortDate from outletsalesreturn inner join outletsalesreturnnumber on outletsalesreturn.SalesReturnNumber = outletsalesreturnnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' ) union (select outlet.NoPacks as NoPacks, 1 as type, outletnumber.CreatedDate as SortDate from outlet inner join outletnumber on outlet.OutletNumberId = outletnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' )) union (select outward.NoPacks as NoPacks, 0 as type, transportoutlet.ReceivedDate as SortDate from outward inner join transportoutlet on outward.OutwardNumberId = transportoutlet.OutwardNumberId inner join outwardnumber on outward.OutwardNumberId = outwardnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and transportoutlet.TransportStatus = 1 )) union (select salesreturn.NoPacks as NoPacks, 3 as type, salesreturnnumber.CreatedDate as SortDate from outward inner join salesreturn on salesreturn.OutwardId = outward.Id inner join salesreturnnumber on salesreturnnumber.Id = salesreturn.SalesReturnNumber where ( outward.ArticleId = '" . $articleArray['ArticleId'] . "')) ) as dd where SortDate > '2021-12-31' order by dd.SortDate asc");
                // } else {
                // $allRecords = DB::select('(select `outletsalesreturn`.`NoPacks` as `NoPacks`, 2 as type, `outletsalesreturnnumber`.`CreatedDate` as `SortDate` from `outletsalesreturn` inner join `outletsalesreturnnumber` on `outletsalesreturn`.`SalesReturnNumber` = `outletsalesreturnnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletsalesreturn`.`OutletPartyId` = ' . $PartyId . ')) union (select `outlet`.`NoPacks` as `NoPacks`, 1 as type, `outletnumber`.`CreatedDate` as `SortDate` from `outlet` inner join `outletnumber` on `outlet`.`OutletNumberId` = `outletnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletnumber`.`PartyId` = ' . $PartyId . ')) union (select `outward`.`NoPacks` as `NoPacks`, 0 as type, `outwardnumber`.`created_at` as `SortDate` from `outward` inner join `transportoutlet` on `outward`.`OutwardNumberId` = `transportoutlet`.`OutwardNumberId` inner join `outwardnumber` on `outward`.`OutwardNumberId` = `outwardnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `transportoutlet`.`TransportStatus` = 1 and `outward`.`PartyId` = ' . $PartyId . ')) union (select `salesreturn`.`NoPacks` as `NoPacks`, 3 as type ,`salesreturnnumber`.`CreatedDate` as `SortDate` from `outward` inner join `salesreturn` on `salesreturn`.`OutwardId` = `outward`.`Id` inner join `salesreturnnumber` on `salesreturnnumber`.`Id` = `salesreturn`.`SalesReturnNumber` where (`outward`.`PartyId` = ' . $PartyId . ' and `outward`.`ArticleId` = ' . $articleArray['ArticleId'] . ')) order by `SortDate` asc');
                // $allRecords = DB::select("select * from (select outletsalesreturn.NoPacks as NoPacks, 2 as type, outletsalesreturnnumber.CreatedDate as SortDate from outletsalesreturn inner join outletsalesreturnnumber on outletsalesreturn.SalesReturnNumber = outletsalesreturnnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletsalesreturn.OutletPartyId = '" . $PartyId . "') union (select outlet.NoPacks as NoPacks, 1 as type, outletnumber.CreatedDate as SortDate from outlet inner join outletnumber on outlet.OutletNumberId = outletnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletnumber.PartyId = '" . $PartyId . "')) union (select outward.NoPacks as NoPacks, 0 as type, transportoutlet.ReceivedDate as SortDate from outward inner join transportoutlet on outward.OutwardNumberId = transportoutlet.OutwardNumberId inner join outwardnumber on outward.OutwardNumberId = outwardnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and transportoutlet.TransportStatus = 1 and outward.PartyId = '" . $PartyId . "')) union (select salesreturn.NoPacks as NoPacks, 3 as type, salesreturnnumber.CreatedDate as SortDate from outward inner join salesreturn on salesreturn.OutwardId = outward.Id inner join salesreturnnumber on salesreturnnumber.Id = salesreturn.SalesReturnNumber where (outward.PartyId = '" . $PartyId . "' and outward.ArticleId = '" . $articleArray['ArticleId'] . "')) ) as dd");
                // }
                if (!isset($allRecords[0])) {
                    $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->first();
                    if ($outletArticle) {
                        $outletArticleColors = json_decode($outletArticle->ArticleColor);
                    } else {
                        $outletArticleColors  = json_decode($articlesColors[0]->ArticleColor);
                    }
                    $outletArticleColors =  (array)$outletArticleColors;
                    if (count($outletArticleColors) > 0) {
                        $SalesNoPacks = [];
                        foreach ($outletArticleColors as $makearray) {
                            array_push($SalesNoPacks, 0);
                        }
                        $getTransportOutwardpacks =  TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->get();
                        if (count($getTransportOutwardpacks) != 0) {
                            // $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                            // $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
                            foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                                $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->first();
                                if ($outletArticle) {
                                    $outletArticleColors = json_decode($outletArticle->ArticleColor);
                                } else {
                                    $article = Article::select('ArticleColor')->where('Id',  $articleArray['ArticleId'])->first();
                                    $outletArticleColors = json_decode($article['ArticleColor']);
                                }
                                $count = 0;
                                foreach ($outletArticleColors as $outletArticleColor) {
                                    if ($outletArticleColor->Id == $getTransportOutwardpack->ColorId) {
                                        if (!isset($SalesNoPacks[$count])) {
                                            array_push($SalesNoPacks, 0);
                                        }
                                        $SalesNoPacks[$count] = $SalesNoPacks[$count] + $getTransportOutwardpack->NoPacks;
                                    }
                                    $count = $count + 1;
                                }
                            }
                        }
                        $newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
                        $objectArticle->STOCKS =  $newimplodeSalesNoPacks;
                        if (array_sum($SalesNoPacks) <= 0) {
                            unset($articles[$key]);
                        } else {
                            $objectArticle->TotalPieces = array_sum($SalesNoPacks);
                            $objectArticle->salespacks = array_sum($SalesNoPacks);
                        }
                        // return $objectArticle->TotalPieces;
                    } else {
                        $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->get();
                        $TotalTransportOutwardpacks = 0;
                        if (count($transportOutwardpacks) != 0) {
                            $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                            $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
                            foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                                $TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
                            }
                        }
                        $TotalInwardPacks = $TotalTransportOutwardpacks;
                        $TotalOutwardPacks = 0;
                        $totalStock = $TotalInwardPacks - $TotalOutwardPacks;
                        $objectArticle->STOCKS = $totalStock;
                        if ($totalStock <= 0) {
                            unset($articles[$key]);
                        } else {
                            $objectArticle->TotalPieces = $totalStock;
                        }
                    }
                } else {
                    if (strpos($allRecords[0]->NoPacks, ',')) {
                        $SalesNoPacks = [];
                        foreach (explode(",", $allRecords[0]->NoPacks) as $makearray) {
                            array_push($SalesNoPacks, 0);
                        }
                        $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->get();
                        $TotalTransportOutwardpacks = 0;
                        if (count($transportOutwardpacks) != 0) {
                            $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                            $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
                            foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                                $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->first();
                                if ($outletArticle) {
                                    $articleColors = json_decode($outletArticle->ArticleColor);
                                } else {
                                    $article = Article::select('ArticleColor')->where('Id', $articleArray['ArticleId'])->first();
                                    $articleColors = json_decode($article['ArticleColor']);
                                }
                                $count = 0;
                                foreach ($articleColors as $articlecolor) {
                                    if ($articlecolor->Id == $getTransportOutwardpack->ColorId) {
                                        if (!isset($SalesNoPacks[$count])) {
                                            array_push($SalesNoPacks, 0);
                                        }
                                        $SalesNoPacks[$count] = $SalesNoPacks[$count] + $getTransportOutwardpack->NoPacks;
                                    }
                                    $count = $count + 1;
                                }
                            }
                        }
                        foreach ($allRecords as  $allRecord) {
                            for ($i = 0; $i < count(explode(",", $allRecord->NoPacks)); $i++) {
                                $noPacks = explode(",", $allRecord->NoPacks);
                                // if ($allRecord->type == 0) {
                                    // $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
                                // }
                            }
                        }
                        $newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
                        $objectArticle->STOCKS =  $newimplodeSalesNoPacks;
                        if (array_sum($SalesNoPacks) <= 0) {
                            unset($articles[$key]);
                        } else {
                            $objectArticle->TotalPieces = array_sum($SalesNoPacks);
                        }
                    } else {
                        $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->get();
                        $TotalTransportOutwardpacks = 0;
                        if (count($transportOutwardpacks) != 0) {
                            $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                            $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
                            foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                                $TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
                            }
                        }
                        $TotalInwardPacks = $TotalTransportOutwardpacks;
                        $TotalOutwardPacks = 0;
                        foreach ($allRecords as  $allRecord) {
                            if ($allRecord->type == 0) {
                                $TotalInwardPacks = $TotalInwardPacks + (int)$allRecord->NoPacks;
                            } elseif ($allRecord->type == 1) {
                                $TotalOutwardPacks = $TotalOutwardPacks + (int)$allRecord->NoPacks;
                            } elseif ($allRecord->type == 2) {
                                $TotalInwardPacks = $TotalInwardPacks + (int)$allRecord->NoPacks;
                            } elseif ($allRecord->type == 3) {
                                $TotalOutwardPacks = $TotalOutwardPacks + (int)$allRecord->NoPacks;
                            }
                        }
                        $totalStock = $TotalInwardPacks - $TotalOutwardPacks;
                        $objectArticle->STOCKS = $totalStock;
                        if ($totalStock <= 0) {
                            unset($articles[$key]);
                        } else {
                            $objectArticle->TotalPieces = $totalStock;
                        }
                    }
                }
            }
            return array("data" => array_values($articles), 'PartyName' => '$partyName->Name');


        }else{
              $article = Artstockstatus::select('artstockstatus.*', 'artstockstatus.SalesNoPacks as STOCKS')->where('outletId', $PartyId)->get();
            // return $article;
            $partyName = Party::select('Name')->where('Id', $PartyId)->first();
            return array("data" => $article, 'PartyName' => $partyName->Name);


            
            $articlesArray = DB::select('(select `article`.`ArticleNumber`, `article`.`Id` as `ArticleId` from `transportoutlet` right join `outward` on `transportoutlet`.`OutwardNumberId` = `outward`.`OutwardNumberId` inner join `article` on `article`.`Id` = `outward`.`ArticleId`  where `transportoutlet`.`TransportStatus` = 1 AND `transportoutlet`.`PartyId` = ' . $PartyId . ' )  union (select `article`.`ArticleNumber`, `article`.`Id` as `ArticleId` from `transportoutwardpacks` inner join `article` on `article`.`Id` = `transportoutwardpacks`.`ArticleId` where `transportoutwardpacks`.`OutwardId` = 0 AND `transportoutwardpacks`.`PartyId` = ' . $PartyId . ') order by `ArticleId` asc');
            $collectionArticles = collect($articlesArray);
            $articles  = $collectionArticles->unique()->values()->all();
            foreach ($articles as $key => $article) {
                $objectArticle = $article;
                $articleArray = (array)$article; 
                $articleData = DB::select('select `category`.`Colorflag`, `article`.`ArticleRatio`,`article`.`ArticleOpenFlag`, `category`.`Title`, `brand`.`Name` as `BrandName`, `subcategory`.`Name` as `Subcategory`, `rangeseries`.`SeriesName`, `rangeseries`.`Series`, `article`.`StyleDescription` from `article` inner join `category` on `article`.`CategoryId` = `category`.`Id` left join `brand` on `brand`.`Id` = `article`.`BrandId` left join `subcategory` on `subcategory`.`Id` = `article`.`SubCategoryId` left join `rangeseries` on `rangeseries`.`Id` = `article`.`SeriesId` where `article`.`Id` = ' . $articleArray['ArticleId']);
                $articlesColors = DB::select("select   GROUP_CONCAT(DISTINCT CONCAT(articlesize.ArticleSizeName) ORDER BY articlesize.Id SEPARATOR ',') as ArticleSize , GROUP_CONCAT(DISTINCT CONCAT(articlecolor.ArticleColorName) ORDER BY articlecolor.Id SEPARATOR ',') as ArticleColor from article left join articlecolor on articlecolor.ArticleId=article.Id left join articlesize on articlesize.ArticleId=article.Id  where article.Id=" . $articleArray['ArticleId']);
                $articleData = (array)$articleData[0];
                $objectArticle->Colorflag = $articleData['Colorflag'];
                $objectArticle->ArticleRatio = $articleData['ArticleRatio'];
                if ($articleData['ArticleRatio']) {
                    $objectArticle->TotalArticleRatio = array_sum(explode(",", $articleData['ArticleRatio']));
                } else {
                    $objectArticle->TotalArticleRatio = 0;
                }
                $objectArticle->Title = $articleData['Title'];
                $objectArticle->BrandName = $articleData['BrandName'];
                $objectArticle->Subcategory = $articleData['Subcategory'];
                $objectArticle->SeriesName = $articleData['SeriesName'];
                $objectArticle->Series = $articleData['Series'];
                $objectArticle->StyleDescription = $articleData['StyleDescription'];
                $outletArticleColor = Outletimport::where('ArticleId', $articleArray['ArticleId'])->where('PartyId', $PartyId)->first();
                if ($outletArticleColor) {
                    if (json_decode($outletArticleColor->ArticleColor)) {
                        $objectArticle->ArticleColor = implode(',', array_column(json_decode($outletArticleColor->ArticleColor), 'Name'));
                    } else {
                        $objectArticle->ArticleColor = "";
                    }
                } else {
                    $articleouter = Article::select('ArticleColor')->where('Id',  $articleArray['ArticleId'])->first();
                    if (json_decode($articleouter['ArticleColor'])) {
                        $objectArticle->ArticleColor  = implode(',', array_column(json_decode($articleouter['ArticleColor']), 'Name'));
                    } else {
                        $objectArticle->ArticleColor  = "";
                    }
                }
                $objectArticle->ArticleSize = $articlesColors[0]->ArticleSize;
                // if ($PartyId == 1) {
                // $allRecords = DB::select("(select outletsalesreturn.NoPacks as NoPacks, 2 as type, outletsalesreturnnumber.CreatedDate as SortDate, from outletsalesreturn inner join outletsalesreturnnumber on outletsalesreturn.SalesReturnNumber = outletsalesreturnnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletsalesreturn.OutletPartyId = '" . $PartyId . "')) union (select outlet.NoPacks as NoPacks, 1 as type, outletnumber.CreatedDate as SortDate from outlet inner join outletnumber on outlet.OutletNumberId = outletnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletnumber.PartyId = '" . $PartyId . "')) union (select outward.NoPacks as NoPacks, 0 as type, outwardnumber.created_at as SortDate from outward inner join transportoutlet on outward.OutwardNumberId = transportoutlet.OutwardNumberId inner join outwardnumber on outward.OutwardNumberId = outwardnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and transportoutlet.TransportStatus = 1 and outward.PartyId = '" . $PartyId . "')) union (select salesreturn.NoPacks as NoPacks, 3 as type ,salesreturnnumber.CreatedDate as SortDate from outward inner join salesreturn on salesreturn.OutwardId = outward.Id inner join salesreturnnumber on salesreturnnumber.Id = salesreturn.SalesReturnNumber where (outward.PartyId = '" . $PartyId . "' and outward.ArticleId = '" . $articleArray['ArticleId'] . "')) order by SortDate asc");
                $allRecords = DB::select("select * from (select outletsalesreturn.NoPacks as NoPacks, 2 as type, outletsalesreturnnumber.CreatedDate as SortDate from outletsalesreturn inner join outletsalesreturnnumber on outletsalesreturn.SalesReturnNumber = outletsalesreturnnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletsalesreturn.OutletPartyId = '" . $PartyId . "') union (select outlet.NoPacks as NoPacks, 1 as type, outletnumber.CreatedDate as SortDate from outlet inner join outletnumber on outlet.OutletNumberId = outletnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletnumber.PartyId = '" . $PartyId . "')) union (select outward.NoPacks as NoPacks, 0 as type, transportoutlet.ReceivedDate as SortDate from outward inner join transportoutlet on outward.OutwardNumberId = transportoutlet.OutwardNumberId inner join outwardnumber on outward.OutwardNumberId = outwardnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and transportoutlet.TransportStatus = 1 and outward.PartyId = '" . $PartyId . "')) union (select salesreturn.NoPacks as NoPacks, 3 as type, salesreturnnumber.CreatedDate as SortDate from outward inner join salesreturn on salesreturn.OutwardId = outward.Id inner join salesreturnnumber on salesreturnnumber.Id = salesreturn.SalesReturnNumber where (outward.PartyId = '" . $PartyId . "' and outward.ArticleId = '" . $articleArray['ArticleId'] . "')) ) as dd where SortDate > '2021-12-31' order by dd.SortDate asc");
                // } else {
                // $allRecords = DB::select('(select `outletsalesreturn`.`NoPacks` as `NoPacks`, 2 as type, `outletsalesreturnnumber`.`CreatedDate` as `SortDate` from `outletsalesreturn` inner join `outletsalesreturnnumber` on `outletsalesreturn`.`SalesReturnNumber` = `outletsalesreturnnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletsalesreturn`.`OutletPartyId` = ' . $PartyId . ')) union (select `outlet`.`NoPacks` as `NoPacks`, 1 as type, `outletnumber`.`CreatedDate` as `SortDate` from `outlet` inner join `outletnumber` on `outlet`.`OutletNumberId` = `outletnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletnumber`.`PartyId` = ' . $PartyId . ')) union (select `outward`.`NoPacks` as `NoPacks`, 0 as type, `outwardnumber`.`created_at` as `SortDate` from `outward` inner join `transportoutlet` on `outward`.`OutwardNumberId` = `transportoutlet`.`OutwardNumberId` inner join `outwardnumber` on `outward`.`OutwardNumberId` = `outwardnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `transportoutlet`.`TransportStatus` = 1 and `outward`.`PartyId` = ' . $PartyId . ')) union (select `salesreturn`.`NoPacks` as `NoPacks`, 3 as type ,`salesreturnnumber`.`CreatedDate` as `SortDate` from `outward` inner join `salesreturn` on `salesreturn`.`OutwardId` = `outward`.`Id` inner join `salesreturnnumber` on `salesreturnnumber`.`Id` = `salesreturn`.`SalesReturnNumber` where (`outward`.`PartyId` = ' . $PartyId . ' and `outward`.`ArticleId` = ' . $articleArray['ArticleId'] . ')) order by `SortDate` asc');
                // $allRecords = DB::select("select * from (select outletsalesreturn.NoPacks as NoPacks, 2 as type, outletsalesreturnnumber.CreatedDate as SortDate from outletsalesreturn inner join outletsalesreturnnumber on outletsalesreturn.SalesReturnNumber = outletsalesreturnnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletsalesreturn.OutletPartyId = '" . $PartyId . "') union (select outlet.NoPacks as NoPacks, 1 as type, outletnumber.CreatedDate as SortDate from outlet inner join outletnumber on outlet.OutletNumberId = outletnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletnumber.PartyId = '" . $PartyId . "')) union (select outward.NoPacks as NoPacks, 0 as type, transportoutlet.ReceivedDate as SortDate from outward inner join transportoutlet on outward.OutwardNumberId = transportoutlet.OutwardNumberId inner join outwardnumber on outward.OutwardNumberId = outwardnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and transportoutlet.TransportStatus = 1 and outward.PartyId = '" . $PartyId . "')) union (select salesreturn.NoPacks as NoPacks, 3 as type, salesreturnnumber.CreatedDate as SortDate from outward inner join salesreturn on salesreturn.OutwardId = outward.Id inner join salesreturnnumber on salesreturnnumber.Id = salesreturn.SalesReturnNumber where (outward.PartyId = '" . $PartyId . "' and outward.ArticleId = '" . $articleArray['ArticleId'] . "')) ) as dd");
                // }
                if (!isset($allRecords[0])) {
                    $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->where('PartyId', $PartyId)->first();
                    if ($outletArticle) {
                        $outletArticleColors = json_decode($outletArticle->ArticleColor);
                    } else {
                        $outletArticleColors  = json_decode($articlesColors[0]->ArticleColor);
                    }
                    $outletArticleColors =  (array)$outletArticleColors;
                    if (count($outletArticleColors) > 0) {
                        $SalesNoPacks = [];
                        foreach ($outletArticleColors as $makearray) {
                            array_push($SalesNoPacks, 0);
                        }
                        // Initialize the array to hold sales packs
                        $SalesNoPacks = [];

                        $getTransportOutwardpacks = TransportOutwardpacks::select('NoPacks', 'ColorId')
                            ->where('ArticleId', $articleArray['ArticleId'])
                            ->where('OutwardId', 0)
                            ->where('PartyId', $PartyId)
                            ->get();

                        // Check if there are any results
                        if (count($getTransportOutwardpacks) != 0) {
                            // Retrieve outlet article information based on ArticleId and PartyId
                            $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])
                                ->where('PartyId', $PartyId)
                                ->first();

                            if ($outletArticle) {
                                // If outlet article exists, decode ArticleColor JSON
                                $outletArticleColors = json_decode($outletArticle->ArticleColor, true);
                            } else {
                                // If outlet article doesn't exist, retrieve ArticleColor from Article table
                                $article = Article::select('ArticleColor')->where('Id',  $articleArray['ArticleId'])->first();
                                $outletArticleColors = json_decode($article['ArticleColor'], true);
                            }

                            // Initialize SalesNoPacks array with 0 for each color ID
                            foreach ($outletArticleColors as $outletArticleColor) {
                                $SalesNoPacks[$outletArticleColor['Id']] = 0;
                            }

                            // Iterate through the getTransportOutwardpacks
                            foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                                $colorId = $getTransportOutwardpack->ColorId;
                                // Check if the colorId is set in $SalesNoPacks
                                if (isset($SalesNoPacks[$colorId])) {
                                    // Subtract NoPacks from SalesNoPacks for the corresponding color ID
                                    $SalesNoPacks[$colorId] = $getTransportOutwardpack->NoPacks;
                                }
                            }
                        }


                        $newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
                        $objectArticle->STOCKS =  $newimplodeSalesNoPacks;

                        if (array_sum($SalesNoPacks) <= 0) {
                            $objectArticle->TotalPieces = 0;
                            $objectArticle->salespacks = 0;
                        } else {
                            $objectArticle->TotalPieces = array_sum($SalesNoPacks);
                            $objectArticle->salespacks = array_sum($SalesNoPacks);
                        }

// Now you can use the $objectArticle with the corrected values.

                       
                    } else {
                        $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
                        $TotalTransportOutwardpacks = 0;
                        if (count($transportOutwardpacks) != 0) {
                            $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                            $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
                            foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                                $TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
                            }
                        }
                        $TotalInwardPacks = $TotalTransportOutwardpacks;
                        $TotalOutwardPacks = 0;
                        $totalStock = $TotalInwardPacks - $TotalOutwardPacks;
                        $objectArticle->STOCKS = $totalStock;
                        if ($totalStock <= 0) {
                            unset($articles[$key]);
                        } else {
                            $objectArticle->TotalPieces = $totalStock;
                        }
                    }
                } 
                else {
                    if (strpos($allRecords[0]->NoPacks, ',')) {
                        $SalesNoPacks = [];
                        foreach (explode(",", $allRecords[0]->NoPacks) as $makearray) {
                            array_push($SalesNoPacks, 0);
                        }
                        $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
                        $TotalTransportOutwardpacks = 0;
                        if (count($transportOutwardpacks) != 0) {
                            $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                            $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
                            foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                                $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->where('PartyId', $PartyId)->first();
                                if ($outletArticle) {
                                    $articleColors = json_decode($outletArticle->ArticleColor);
                                } else {
                                    $article = Article::select('ArticleColor')->where('Id', $articleArray['ArticleId'])->first();
                                    $articleColors = json_decode($article['ArticleColor']);
                                }
                                $count = 0;
                                foreach ($articleColors as $articlecolor) {
                                    if ($articlecolor->Id == $getTransportOutwardpack->ColorId) {
                                        if (!isset($SalesNoPacks[$count])) {
                                            array_push($SalesNoPacks, 0);
                                        }
                                        $SalesNoPacks[$count] = $SalesNoPacks[$count] + $getTransportOutwardpack->NoPacks;
                                    }
                                    $count = $count + 1;
                                }
                            }
                        }
                        $SalesNoPacks = []; // Initialize the array

                        foreach ($allRecords as $allRecord) {
                            $noPacks = explode(",", $allRecord->NoPacks);
                            
                            for ($i = 0; $i < count($noPacks); $i++) {
                                if (!isset($SalesNoPacks[$i])) {
                                    $SalesNoPacks[$i] = 0; // Initialize with zero if index is not set
                                }
                                
                                if ($allRecord->type == 0 || $allRecord->type == 2) {
                                    $SalesNoPacks[$i] += $noPacks[$i];
                                } elseif ($allRecord->type == 1 || $allRecord->type == 3) {
                                    $SalesNoPacks[$i] -= $noPacks[$i];
                                }
                            }
                        }
                        

                        $newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
                        $objectArticle->STOCKS =  $newimplodeSalesNoPacks;
                        if (array_sum($SalesNoPacks) <= 0) {
                            unset($articles[$key]);
                        } else {
                            $objectArticle->TotalPieces = array_sum($SalesNoPacks);
                        }
                    } else {
                        $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
                        $TotalTransportOutwardpacks = 0;
                        if (count($transportOutwardpacks) != 0) {
                            $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                            $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
                            foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                                $TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
                            }
                        }
                        $TotalInwardPacks = $TotalTransportOutwardpacks;
                        $TotalOutwardPacks = 0;
                        foreach ($allRecords as  $allRecord) {
                            if ($allRecord->type == 0) {
                                $TotalInwardPacks = $TotalInwardPacks + (int)$allRecord->NoPacks;
                            } elseif ($allRecord->type == 1) {
                                $TotalOutwardPacks = $TotalOutwardPacks + (int)$allRecord->NoPacks;
                            } elseif ($allRecord->type == 2) {
                                $TotalInwardPacks = $TotalInwardPacks + (int)$allRecord->NoPacks;
                            } elseif ($allRecord->type == 3) {
                                $TotalOutwardPacks = $TotalOutwardPacks + (int)$allRecord->NoPacks;
                            }
                        }
                        $totalStock = $TotalInwardPacks - $TotalOutwardPacks;
                        $objectArticle->STOCKS = $totalStock;
                        if ($totalStock <= 0) {
                            unset($articles[$key]);
                        } else {
                            $objectArticle->TotalPieces = $totalStock;
                        }
                    }
                }
            }
            $partyName = Party::select('Name')->where('Id', $PartyId)->first();
            // foreach ($articles as $item) {
            //     Artstockstatus::create(
            //         [
            //             'outletId' => '1',
            //             'ArticleId' => $item->ArticleId,
            //             'ArticleNumber' => $item->ArticleNumber,
            //             'ArticleOpenFlag' => '',
            //             'SalesNoPacks' => $item->STOCKS , 
            //             'TotalPieces' => $item->TotalPieces ,
            //             'ArticleColor'=> $item->ArticleColor,
            //             'ArticleSize' => $item->ArticleSize,
            //             'ArticleRatio' => $item->ArticleRatio,
            //             'Colorflag' => $item->Colorflag,
            //             'Title' => $item->Title,
            //             'BrandName' => $item->BrandName,
            //             'Subcategory' => $item->Subcategory,
            //             'SeriesName' => $item->SeriesName,
            //             'Series' => $item->Series,
            //             'StyleDescription' => $item->StyleDescription,
            //             'TotalArticleRatio' => NULL,
            //             'InwardDate' => NULL
            //         ]
            //     );
            // }
            
            return array("data" => array_values($articles), 'PartyName' => $partyName->Name);
        }

    }

    // public function GetOutletStocks($PartyId)
    // {
    //     $articlesArray = DB::select('SELECT article.ArticleNumber, article.Id AS ArticleId FROM transportoutlet JOIN outward ON transportoutlet.OutwardNumberId = outward.OutwardNumberId JOIN article ON article.Id = outward.ArticleId WHERE transportoutlet.TransportStatus = 1 AND transportoutlet.PartyId = ' . $PartyId . ' UNION SELECT article.ArticleNumber, article.Id AS ArticleId FROM transportoutwardpacks JOIN article ON article.Id = transportoutwardpacks.ArticleId WHERE transportoutwardpacks.OutwardId = 0 AND transportoutwardpacks.PartyId = ' . $PartyId . ' ORDER BY ArticleId ASC');
    //     $collectionArticles = collect($articlesArray);
    //     $articles = $collectionArticles->unique()->values()->all();
    //     foreach ($articles as $key => $article) {
    //         $objectArticle = $article;
    //         $articleArray = (array) $article;
    //         $articleId = $articleArray['ArticleId'];
    //         // Logic 1
    //         $articleData = DB::select("SELECT DISTINCT a.ArticleRatio, a.ArticleOpenFlag, c.Title, b.Name AS BrandName, sc.Name AS Subcategory, a.StyleDescription, (SELECT GROUP_CONCAT(DISTINCT articlesize.ArticleSizeName ORDER BY articlesize.Id SEPARATOR ',') FROM articlesize  WHERE articlesize.ArticleId = a.Id) as ArticleSize, (SELECT GROUP_CONCAT(DISTINCT articlecolor.ArticleColorName ORDER BY articlecolor.Id SEPARATOR ',') FROM articlecolor WHERE articlecolor.ArticleId = a.Id) as ArticleColor FROM article a INNER JOIN category c ON a.CategoryId = c.Id LEFT JOIN brand b ON b.Id = a.BrandId LEFT JOIN subcategory sc ON sc.Id = a.SubCategoryId WHERE a.Id = :articleId", ['articleId' => $articleId]);
    //         $articleData = (array) $articleData[0];
    //         $properties = ['ArticleRatio', 'ArticleOpenFlag', 'Title', 'BrandName', 'Subcategory', 'StyleDescription', 'ArticleSize', 'ArticleColor'];
    //         foreach ($properties as $property) {
    //             $objectArticle->$property = $articleData[$property] ?? null;
    //         }           
    //             //Logic 2
    //         $allRecords = DB::select('(select `outletsalesreturn`.`NoPacks` as `NoPacks`, 1 as type, `outletsalesreturnnumber`.`CreatedDate` as `SortDate` from `outletsalesreturn` inner join `outletsalesreturnnumber` on `outletsalesreturn`.`SalesReturnNumber` = `outletsalesreturnnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletsalesreturn`.`OutletPartyId` = ' . $PartyId . ')) union (select `outlet`.`NoPacks` as `NoPacks`, 1 as type, `outletnumber`.`CreatedDate` as `SortDate` from `outlet` inner join `outletnumber` on `outlet`.`OutletNumberId` = `outletnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletnumber`.`PartyId` = ' . $PartyId . ')) union (select `outward`.`NoPacks` as `NoPacks`, 0 as type, `outwardnumber`.`created_at` as `SortDate` from `outward` inner join `transportoutlet` on `outward`.`OutwardNumberId` = `transportoutlet`.`OutwardNumberId` inner join `outwardnumber` on `outward`.`OutwardNumberId` = `outwardnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `transportoutlet`.`TransportStatus` = 1 and `outward`.`PartyId` = ' . $PartyId . ')) order by `SortDate` asc');
    //         if (!isset($allRecords[0])) {
    //             $outletArticle = Outletimport::where([
    //                 ['ArticleId', $articleArray['ArticleId']],
    //                 ['PartyId', $PartyId]
    //             ])->first();
            
    //             $outletArticleColors = null;
            
    //             if (!$outletArticle && !empty($articlesColors[0])) {
    //                 $outletArticleColors = json_decode($articleData['ArticleColor'], true);
    //             } elseif ($outletArticle) {
    //                 $outletArticleColors = json_decode($outletArticle->ArticleColor, true);
    //             }
            
    //             $SalesNoPacks = [];
    //             if ($outletArticleColors) {
    //                 $outletArticleColors = array_column($outletArticleColors, null, 'Id');
    //                 $SalesNoPacks = array_fill_keys(array_keys($outletArticleColors), 0);
            
    //                 $getTransportOutwardpacks = TransportOutwardpacks::select('NoPacks', 'ColorId')
    //                     ->where(['ArticleId' => $articleArray['ArticleId'], 'OutwardId' => 0, 'PartyId' => $PartyId])
    //                     ->get();
            
    //                 foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
    //                     $colorId = $getTransportOutwardpack->ColorId;
    //                     if (isset($SalesNoPacks[$colorId])) {
    //                         $SalesNoPacks[$colorId] -= $getTransportOutwardpack->NoPacks;
    //                     }
    //                 }
    //             } else {
    //                 $TotalTransportOutwardpacks = TransportOutwardpacks::where('ArticleId', $articleArray['ArticleId'])
    //                     ->where('OutwardId', 0)
    //                     ->where('PartyId', $PartyId)
    //                     ->sum('NoPacks');
            
    //                 $totalStock = max($TotalTransportOutwardpacks, 0);
    //                 $SalesNoPacks = $totalStock;
            
    //                 if ($totalStock <= 0) {
    //                     unset($articles[$key]);
    //                 }
    //             }
            
    //             $objectArticle->STOCKS = is_array($SalesNoPacks) ? implode(",", $SalesNoPacks) : $SalesNoPacks;
    //             $objectArticle->TotalPieces = is_array($SalesNoPacks) ? array_sum($SalesNoPacks) : $SalesNoPacks;
    //             $objectArticle->salespacks = $objectArticle->TotalPieces;
    //         }      else {
    //             if (strpos($allRecords[0]->NoPacks, ',')) {
    //                 $NoPacksArray = explode(",", $allRecords[0]->NoPacks);
    //                 $SalesNoPacks = array_fill(0, count($NoPacksArray), 0);
    //                 //optimize new query
    //                 $transportOutwardpacks = TransportOutwardpacks::select('NoPacks', 'ColorId')
    //                     ->where([
    //                         ['ArticleId', '=', $articleArray['ArticleId']],
    //                         ['OutwardId', '=', 0],
    //                         ['PartyId', '=', $PartyId]
    //                     ])->get();
    //                 $TotalTransportOutwardpacks = 0;
    //                 if (count($transportOutwardpacks) != 0) {
    //                     $collectionTransportOutwardpacks = collect($transportOutwardpacks);
    //                     $getTransportOutwardpacks = $collectionTransportOutwardpacks->unique()->values()->all();
    //                     foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
    //                         $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->where('PartyId', $PartyId)->first();
    //                         if ($outletArticle) {
    //                             $articleColors = json_decode($outletArticle->ArticleColor);
    //                         } else {
    //                             $article = Article::select('ArticleColor')->where('Id', $articleArray['ArticleId'])->first();
    //                             $articleColors = json_decode($article['ArticleColor']);
    //                         }
    //                         $count = 0;
    //                         foreach ($articleColors as $articlecolor) {
    //                             if ($articlecolor->Id == $getTransportOutwardpack->ColorId) {
    //                                 if (!isset($SalesNoPacks[$count])) {
    //                                     array_push($SalesNoPacks, 0);
    //                                 }
    //                                 $SalesNoPacks[$count] = $SalesNoPacks[$count] + $getTransportOutwardpack->NoPacks;
    //                             }
    //                             $count = $count + 1;
    //                         }
    //                     }
    //                 }

    //                 foreach ($allRecords as $allRecord) {
    //                     $noPacks = explode(",", $allRecord->NoPacks);

    //                     for ($i = 0; $i < count($noPacks); $i++) {
    //                         switch ($allRecord->type) {
    //                             case 0:
    //                             case 2:
    //                                 $SalesNoPacks[$i] += $noPacks[$i];
    //                                 break;
    //                             case 1:
    //                             case 3:
    //                                 $SalesNoPacks[$i] -= $noPacks[$i];
    //                                 break;
    //                         }
    //                     }
    //                 }

    //                 $newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
    //                 if (array_sum($SalesNoPacks) <= 0) {
    //                     unset($articles[$key]);
    //                 } else {
    //                     $objectArticle->TotalPieces = array_sum($SalesNoPacks);
    //                 }

    //                 $objectArticle->STOCKS = $newimplodeSalesNoPacks;
    //             } else {
    //                 $transportOutwardpacks = TransportOutwardpacks::select('NoPacks')
    //                     ->where([
    //                         ['ArticleId', '=', $articleArray['ArticleId']],
    //                         ['OutwardId', '=', 0],
    //                         ['PartyId', '=', $PartyId]
    //                     ])->get();
                
    //                 $TotalTransportOutwardpacks = $transportOutwardpacks->sum('NoPacks');
                
    //                 $TotalInwardPacks = $TotalTransportOutwardpacks;
    //                 $TotalOutwardPacks = 0;
                
    //                 foreach ($allRecords as $allRecord) {
    //                     switch ($allRecord->type) {
    //                         case 0:
    //                         case 2:
    //                             $TotalInwardPacks += (int) $allRecord->NoPacks;
    //                             break;
    //                         case 1:
    //                         case 3:
    //                             $TotalOutwardPacks += (int) $allRecord->NoPacks;
    //                             break;
    //                     }
    //                 }
    //                 $totalStock = $TotalInwardPacks - $TotalOutwardPacks;
    //                 if ($totalStock > 0) {
    //                     $objectArticle->STOCKS = $totalStock;
    //                     $objectArticle->TotalPieces = $totalStock;
    //                 } else {
    //                     unset($articles[$key]);
    //                 }
    //             }
                
    //         }

    //     }
    //     $partyName = Party::select('Name')->where('Id', $PartyId)->first();
    //     $filteredData = array_filter(array_values($articles), function ($item) {
    //         return $item->TotalPieces > 0;
    //     });

    //     foreach ($filteredData as $item) {
    //         Artstockstatus::create(
    //             [
    //                 'outletId' => '1',
    //                 'ArticleId' => $item->ArticleId,
    //                 'ArticleNumber' => $item->ArticleNumber,
    //                 'ArticleOpenFlag' => $item->ArticleOpenFlag,
    //                 'SalesNoPacks' => $item->STOCKS , 
    //                 'TotalPieces' => $item->TotalPieces ,
    //                 'ArticleColor'=> $item->ArticleColor,
    //                 'ArticleSize' => $item->ArticleSize,
    //                 'ArticleRatio' => $item->ArticleRatio,
    //                 'Colorflag' => '',
    //                 'Title' => $item->Title,
    //                 'BrandName' => $item->BrandName,
    //                 'Subcategory' => $item->Subcategory,
    //                 'SeriesName' => '',
    //                 'Series' => '',
    //                 'StyleDescription' => $item->StyleDescription,
    //                 'TotalArticleRatio' => NULL,
    //                 'InwardDate' => NULL
    //             ]
    //         );
    //     }

    //     return array("data" => array_values($filteredData), 'PartyName' => $partyName->Name);
    // }


    public function GetOutletStocksold($PartyId)
    {
        // $articlesArray = DB::select('(select `article`.`ArticleNumber`, `article`.`Id` as `ArticleId` from `transportoutlet` right join `outward` on `transportoutlet`.`OutwardNumberId` = `outward`.`OutwardNumberId` inner join `article` on `article`.`Id` = `outward`.`ArticleId`  where `transportoutlet`.`TransportStatus` = 1 AND `transportoutlet`.`PartyId` = ' . $PartyId . ' )  union (select `article`.`ArticleNumber`, `article`.`Id` as `ArticleId` from `transportoutwardpacks` inner join `article` on `article`.`Id` = `transportoutwardpacks`.`ArticleId` where `transportoutwardpacks`.`OutwardId` = 0 AND `transportoutwardpacks`.`PartyId` = ' . $PartyId . ') order by `ArticleId` asc');

        $articlesArray = DB::select('
    SELECT 
        article.ArticleNumber, 
        article.Id AS ArticleId 
    FROM 
        transportoutlet 
    JOIN 
        outward ON transportoutlet.OutwardNumberId = outward.OutwardNumberId
    JOIN 
        article ON article.Id = outward.ArticleId
    WHERE 
        transportoutlet.TransportStatus = 1 
        AND transportoutlet.PartyId = ' . $PartyId . '
    UNION 
    SELECT 
        article.ArticleNumber, 
        article.Id AS ArticleId 
    FROM 
        transportoutwardpacks 
    JOIN 
        article ON article.Id = transportoutwardpacks.ArticleId
    WHERE 
        transportoutwardpacks.OutwardId = 0 
        AND transportoutwardpacks.PartyId = ' . $PartyId . '
    ORDER BY 
        ArticleId ASC
');


        // $articlesArray = DB::table('article')
        // ->select('article.ArticleNumber', 'article.Id as ArticleId')
        // ->leftJoin('outward', 'outward.ArticleId', '=', 'article.Id')
        // ->leftJoin('transportoutlet', function($join) use ($PartyId) {
        //     $join->on('transportoutlet.OutwardNumberId', '=', 'outward.OutwardNumberId')
        //         ->where('transportoutlet.TransportStatus', 1)
        //         ->where('transportoutlet.PartyId', $PartyId);
        // })
        // ->leftJoin('transportoutwardpacks', 'transportoutwardpacks.ArticleId', '=', 'article.Id')
        // ->where('transportoutwardpacks.OutwardId', 0)
        // ->where('transportoutwardpacks.PartyId', $PartyId)
        // ->orderBy('ArticleId', 'asc')
        // ->distinct()
        // ->union(DB::table('article')
        //     ->select('article.ArticleNumber', 'article.Id as ArticleId')
        //     ->leftJoin('transportoutwardpacks', 'transportoutwardpacks.ArticleId', '=', 'article.Id')
        //     ->where('transportoutwardpacks.OutwardId', 0)
        //     ->where('transportoutwardpacks.PartyId', $PartyId)
        //     ->orderBy('ArticleId', 'asc')
        //     ->distinct())
        // ->get();  


        $collectionArticles = collect($articlesArray);
        $articles = $collectionArticles->unique()->values()->all();
        foreach ($articles as $key => $article) {
            $objectArticle = $article;
            $articleArray = (array) $article;
            $articleId = $articleArray['ArticleId'];
            // $articlesColors = [];
            // $articleData = DB::select("SELECT c.Title, b.Name AS BrandName FROM article a INNER JOIN category c ON a.CategoryId = c.Id LEFT JOIN brand b ON b.Id = a.BrandId WHERE a.Id = :articleId", ['articleId' => $articleId]);
            // $articleData = (array) $articleData[0];
            // $objectArticle->Title = $articleData['Title'];
            // $objectArticle->BrandName = $articleData['BrandName'];           

            //     $articleData = DB::select("SELECT DISTINCT c.Colorflag,a.ArticleRatio, a.ArticleOpenFlag, c.Title, b.Name AS BrandName, sc.Name AS Subcategory, 
            //         a.StyleDescription FROM article a INNER JOIN category c ON a.CategoryId = c.Id LEFT JOIN brand b ON b.Id = a.BrandId LEFT JOIN subcategory sc ON sc.Id = a.SubCategoryId WHERE a.Id = :articleId", ['articleId' => $articleId]);

            // $articlesColors = DB::select("SELECT GROUP_CONCAT(DISTINCT articlesize.ArticleSizeName ORDER BY articlesize.Id SEPARATOR ',') as ArticleSize , 
            //     GROUP_CONCAT(DISTINCT articlecolor.ArticleColorName ORDER BY articlecolor.Id SEPARATOR ',') as ArticleColor 
            // FROM article
            // INNER JOIN articlecolor ON articlecolor.ArticleId = article.Id
            // INNER JOIN  articlesize ON articlesize.ArticleId = article.Id  
            // WHERE  article.Id = :articleId", ['articleId' => $articleId]);
            $articleData = DB::select("
            SELECT DISTINCT 
                c.Colorflag,
                a.ArticleRatio, 
                a.ArticleOpenFlag, 
                c.Title, 
                b.Name AS BrandName, 
                sc.Name AS Subcategory, 
                a.StyleDescription,
                (SELECT GROUP_CONCAT(DISTINCT articlesize.ArticleSizeName ORDER BY articlesize.Id SEPARATOR ',') 
                 FROM articlesize 
                 WHERE articlesize.ArticleId = a.Id) as ArticleSize,
                (SELECT GROUP_CONCAT(DISTINCT articlecolor.ArticleColorName ORDER BY articlecolor.Id SEPARATOR ',') 
                 FROM articlecolor 
                 WHERE articlecolor.ArticleId = a.Id) as ArticleColor
            FROM article a 
            INNER JOIN category c ON a.CategoryId = c.Id 
            LEFT JOIN brand b ON b.Id = a.BrandId 
            LEFT JOIN subcategory sc ON sc.Id = a.SubCategoryId 
            WHERE a.Id = :articleId", ['articleId' => $articleId]);

            if (!empty($articleData)) {
                $articleData = (array) $articleData[0];
                $objectArticle->Colorflag = $articleData['Colorflag'];
                $objectArticle->ArticleRatio = $articleData['ArticleRatio'];
                $objectArticle->Title = $articleData['Title'];
                $objectArticle->BrandName = $articleData['BrandName'];
                $objectArticle->Subcategory = $articleData['Subcategory'];
                $objectArticle->StyleDescription = $articleData['StyleDescription'];

                // Extract ArticleSize and ArticleColor
                $objectArticle->ArticleSize = $articleData['ArticleSize'];
                $objectArticle->ArticleColor = $articleData['ArticleColor'];

                // Handle ArticleColor logic
                $outletArticleColor = Outletimport::where('ArticleId', $articleArray['ArticleId'])
                    ->where('PartyId', $PartyId)
                    ->first();

                if ($outletArticleColor) {
                    $articleColors = json_decode($outletArticleColor->ArticleColor);
                } else {
                    $articleouter = Article::select('ArticleColor')->where('Id', $articleArray['ArticleId'])->first();
                    $articleColors = json_decode($articleouter['ArticleColor']);
                }

                $objectArticle->ArticleColor = $articleColors ? implode(',', array_column($articleColors, 'Name')) : "";
            }

            //             $allRecords = DB::table('outletsalesreturn')
            //     ->select('outletsalesreturn.NoPacks as NoPacks', DB::raw('1 as type'), 'outletsalesreturnnumber.CreatedDate as SortDate')
            //     ->join('outletsalesreturnnumber', 'outletsalesreturn.SalesReturnNumber', '=', 'outletsalesreturnnumber.Id')
            //     ->where('ArticleId', $articleArray['ArticleId'])
            //     ->where('outletsalesreturn.OutletPartyId', $PartyId);

            // $allRecords->union(DB::table('outlet')
            //     ->select('outlet.NoPacks as NoPacks', DB::raw('1 as type'), 'outletnumber.CreatedDate as SortDate')
            //     ->join('outletnumber', 'outlet.OutletNumberId', '=', 'outletnumber.Id')
            //     ->where('ArticleId', $articleArray['ArticleId'])
            //     ->where('outletnumber.PartyId', $PartyId));

            // $allRecords->union(DB::table('outward')
            //     ->select('outward.NoPacks as NoPacks', DB::raw('0 as type'), 'outwardnumber.created_at as SortDate')
            //     ->join('transportoutlet', 'outward.OutwardNumberId', '=', 'transportoutlet.OutwardNumberId')
            //     ->join('outwardnumber', 'outward.OutwardNumberId', '=', 'outwardnumber.Id')
            //     ->where('ArticleId', $articleArray['ArticleId'])
            //     ->where('transportoutlet.TransportStatus', 1)
            //     ->where('outward.PartyId', $PartyId));

            // $allRecords->orderBy('SortDate', 'asc');
            // $allRecords = $allRecords->get();



            $allRecords = DB::select('(select `outletsalesreturn`.`NoPacks` as `NoPacks`, 1 as type, `outletsalesreturnnumber`.`CreatedDate` as `SortDate` from `outletsalesreturn` inner join `outletsalesreturnnumber` on `outletsalesreturn`.`SalesReturnNumber` = `outletsalesreturnnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletsalesreturn`.`OutletPartyId` = ' . $PartyId . ')) union (select `outlet`.`NoPacks` as `NoPacks`, 1 as type, `outletnumber`.`CreatedDate` as `SortDate` from `outlet` inner join `outletnumber` on `outlet`.`OutletNumberId` = `outletnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletnumber`.`PartyId` = ' . $PartyId . ')) union (select `outward`.`NoPacks` as `NoPacks`, 0 as type, `outwardnumber`.`created_at` as `SortDate` from `outward` inner join `transportoutlet` on `outward`.`OutwardNumberId` = `transportoutlet`.`OutwardNumberId` inner join `outwardnumber` on `outward`.`OutwardNumberId` = `outwardnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `transportoutlet`.`TransportStatus` = 1 and `outward`.`PartyId` = ' . $PartyId . ')) order by `SortDate` asc');
            // $allRecords = DB::select("select * from (select outletsalesreturn.NoPacks as NoPacks, 2 as type, outletsalesreturnnumber.CreatedDate as SortDate from outletsalesreturn inner join outletsalesreturnnumber on outletsalesreturn.SalesReturnNumber = outletsalesreturnnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletsalesreturn.OutletPartyId = '" . $PartyId . "') union (select outlet.NoPacks as NoPacks, 1 as type, outletnumber.CreatedDate as SortDate from outlet inner join outletnumber on outlet.OutletNumberId = outletnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletnumber.PartyId = '" . $PartyId . "')) union (select outward.NoPacks as NoPacks, 0 as type, transportoutlet.ReceivedDate as SortDate from outward inner join transportoutlet on outward.OutwardNumberId = transportoutlet.OutwardNumberId inner join outwardnumber on outward.OutwardNumberId = outwardnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and transportoutlet.TransportStatus = 1 and outward.PartyId = '" . $PartyId . "')) union (select salesreturn.NoPacks as NoPacks, 3 as type, salesreturnnumber.CreatedDate as SortDate from outward inner join salesreturn on salesreturn.OutwardId = outward.Id inner join salesreturnnumber on salesreturnnumber.Id = salesreturn.SalesReturnNumber where (outward.PartyId = '" . $PartyId . "' and outward.ArticleId = '" . $articleArray['ArticleId'] . "')) ) as dd where SortDate > '2021-12-31' order by dd.SortDate asc");
            if (!isset($allRecords[0])) {
                $outletArticle = Outletimport::where([
                    ['ArticleId', $articleArray['ArticleId']],
                    ['PartyId', $PartyId]
                ])->first();

                if (!$outletArticle && !empty($articlesColors[0])) {
                    $outletArticleColors = json_decode($articleData['ArticleColor'], true);
                } elseif ($outletArticle) {
                    $outletArticleColors = json_decode($outletArticle->ArticleColor, true);
                } else {
                    $outletArticleColors = null;
                }

                if ($outletArticleColors) {
                    $SalesNoPacks = [];
                    $outletArticleColors = array_column($outletArticleColors, null, 'Id');
                    $SalesNoPacks = array_fill_keys(array_keys($outletArticleColors), 0);

                    $getTransportOutwardpacks = TransportOutwardpacks::select('NoPacks', 'ColorId')
                        ->where(['ArticleId' => $articleArray['ArticleId'], 'OutwardId' => 0, 'PartyId' => $PartyId])
                        ->get();

                    foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                        $colorId = $getTransportOutwardpack->ColorId;
                        if (isset($SalesNoPacks[$colorId])) {
                            $SalesNoPacks[$colorId] -= $getTransportOutwardpack->NoPacks;
                        }
                    }

                    $objectArticle->STOCKS = implode(",", $SalesNoPacks);
                    $objectArticle->TotalPieces = array_sum($SalesNoPacks);
                    $objectArticle->salespacks = $objectArticle->TotalPieces;


                } else {
                    $TotalTransportOutwardpacks = TransportOutwardpacks::where('ArticleId', $articleArray['ArticleId'])
                        ->where('OutwardId', 0)
                        ->where('PartyId', $PartyId)
                        ->sum('NoPacks');

                    $totalStock = max($TotalTransportOutwardpacks, 0);
                    $objectArticle->STOCKS = $totalStock;

                    if ($totalStock <= 0) {
                        unset($articles[$key]);
                    } else {
                        $objectArticle->TotalPieces = $totalStock;
                    }
                }
            } else {
                if (strpos($allRecords[0]->NoPacks, ',')) {
                    $NoPacksArray = explode(",", $allRecords[0]->NoPacks);
                    $SalesNoPacks = array_fill(0, count($NoPacksArray), 0);
                    //optimize new query
                    $transportOutwardpacks = TransportOutwardpacks::select('NoPacks', 'ColorId')
                        ->where([
                            ['ArticleId', '=', $articleArray['ArticleId']],
                            ['OutwardId', '=', 0],
                            ['PartyId', '=', $PartyId]
                        ])->get();
                    $TotalTransportOutwardpacks = 0;
                    if (count($transportOutwardpacks) != 0) {
                        $collectionTransportOutwardpacks = collect($transportOutwardpacks);
                        $getTransportOutwardpacks = $collectionTransportOutwardpacks->unique()->values()->all();
                        foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
                            $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->where('PartyId', $PartyId)->first();
                            if ($outletArticle) {
                                $articleColors = json_decode($outletArticle->ArticleColor);
                            } else {
                                $article = Article::select('ArticleColor')->where('Id', $articleArray['ArticleId'])->first();
                                $articleColors = json_decode($article['ArticleColor']);
                            }
                            $count = 0;
                            foreach ($articleColors as $articlecolor) {
                                if ($articlecolor->Id == $getTransportOutwardpack->ColorId) {
                                    if (!isset($SalesNoPacks[$count])) {
                                        array_push($SalesNoPacks, 0);
                                    }
                                    $SalesNoPacks[$count] = $SalesNoPacks[$count] + $getTransportOutwardpack->NoPacks;
                                }
                                $count = $count + 1;
                            }
                        }
                    }


                    foreach ($allRecords as $allRecord) {
                        for ($i = 0; $i < count(explode(",", $allRecord->NoPacks)); $i++) {
                            $noPacks = explode(",", $allRecord->NoPacks);
                            if ($allRecord->type == 0) {
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
                            } elseif ($allRecord->type == 1) {
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                            } elseif ($allRecord->type == 2) {
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
                            } elseif ($allRecord->type == 3) {
                                $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                            }
                        }
                    }
                    $newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
                    $objectArticle->STOCKS = $newimplodeSalesNoPacks;
                    if (array_sum($SalesNoPacks) <= 0) {
                        unset($articles[$key]);
                    } else {
                        $objectArticle->TotalPieces = array_sum($SalesNoPacks);
                    }
                } else {
                    $transportOutwardpacks = TransportOutwardpacks::select('NoPacks')
                        ->where('ArticleId', $articleArray['ArticleId'])
                        ->where('OutwardId', 0)
                        ->where('PartyId', $PartyId)
                        ->get();

                    $TotalTransportOutwardpacks = $transportOutwardpacks->sum('NoPacks');

                    $TotalInwardPacks = $TotalTransportOutwardpacks;
                    $TotalOutwardPacks = 0;

                    foreach ($allRecords as $allRecord) {
                        switch ($allRecord->type) {
                            case 0:
                            case 2:
                                $TotalInwardPacks += (int) $allRecord->NoPacks;
                                break;
                            case 1:
                            case 3:
                                $TotalOutwardPacks += (int) $allRecord->NoPacks;
                                break;
                        }
                    }
                    $totalStock = $TotalInwardPacks - $TotalOutwardPacks;
                    if ($totalStock > 0) {
                        $objectArticle->STOCKS = $totalStock;
                        $objectArticle->TotalPieces = $totalStock;
                    } else {
                        unset($articles[$key]);
                    }
                }
            }
        }
        $partyName = Party::select('Name')->where('Id', $PartyId)->first();
        $filteredData = array_filter(array_values($articles), function ($item) {
            return $item->TotalPieces > 0;
        });
        return array("data" => array_values($filteredData), 'PartyName' => $partyName->Name);

    }
    // public function GetOutletStocks($PartyId)
    // {

    //     if($PartyId == 4){

    //         $articlesArray = DB::select('(select `article`.`ArticleNumber`, `article`.`Id` as `ArticleId` from `transportoutlet` right join `outward` on `transportoutlet`.`OutwardNumberId` = `outward`.`OutwardNumberId` inner join `article` on `article`.`Id` = `outward`.`ArticleId`  where `transportoutlet`.`TransportStatus` = 1 )  union (select `article`.`ArticleNumber`, `article`.`Id` as `ArticleId` from `transportoutwardpacks` inner join `article` on `article`.`Id` = `transportoutwardpacks`.`ArticleId` where `transportoutwardpacks`.`OutwardId` = 0 ) order by `ArticleId` asc');
    //         $collectionArticles = collect($articlesArray);
    //         $articles  = $collectionArticles->unique()->values()->all();
    //         foreach ($articles as $key => $article) {
    //             $objectArticle = $article;
    //             $articleArray = (array)$article;
    //             $articleData = DB::select('select `category`.`Colorflag`, `article`.`ArticleRatio`,`article`.`ArticleOpenFlag`, `category`.`Title`, `brand`.`Name` as `BrandName`, `subcategory`.`Name` as `Subcategory`, `rangeseries`.`SeriesName`, `rangeseries`.`Series`, `article`.`StyleDescription` from `article` inner join `category` on `article`.`CategoryId` = `category`.`Id` left join `brand` on `brand`.`Id` = `article`.`BrandId` left join `subcategory` on `subcategory`.`Id` = `article`.`SubCategoryId` left join `rangeseries` on `rangeseries`.`Id` = `article`.`SeriesId` where `article`.`Id` = ' . $articleArray['ArticleId']);
    //             $articlesColors = DB::select("select   GROUP_CONCAT(DISTINCT CONCAT(articlesize.ArticleSizeName) ORDER BY articlesize.Id SEPARATOR ',') as ArticleSize , GROUP_CONCAT(DISTINCT CONCAT(articlecolor.ArticleColorName) ORDER BY articlecolor.Id SEPARATOR ',') as ArticleColor from article left join articlecolor on articlecolor.ArticleId=article.Id left join articlesize on articlesize.ArticleId=article.Id  where article.Id=" . $articleArray['ArticleId']);
    //             $articleData = (array)$articleData[0];
    //             $objectArticle->Colorflag = $articleData['Colorflag'];
    //             $objectArticle->ArticleRatio = $articleData['ArticleRatio'];
    //             if ($articleData['ArticleRatio']) {
    //                 $objectArticle->TotalArticleRatio = array_sum(explode(",", $articleData['ArticleRatio']));
    //             } else {
    //                 $objectArticle->TotalArticleRatio = 0;
    //             }
    //             $objectArticle->Title = $articleData['Title'];
    //             $objectArticle->BrandName = $articleData['BrandName'];
    //             $objectArticle->Subcategory = $articleData['Subcategory'];
    //             $objectArticle->SeriesName = $articleData['SeriesName'];
    //             $objectArticle->Series = $articleData['Series'];
    //             $objectArticle->StyleDescription = $articleData['StyleDescription'];
    //             $outletArticleColor = Outletimport::where('ArticleId', $articleArray['ArticleId'])->first();
    //             if ($outletArticleColor) {
    //                 if (json_decode($outletArticleColor->ArticleColor)) {
    //                     $objectArticle->ArticleColor = implode(',', array_column(json_decode($outletArticleColor->ArticleColor), 'Name'));
    //                 } else {
    //                     $objectArticle->ArticleColor = "";
    //                 }
    //             } else {
    //                 $articleouter = Article::select('ArticleColor')->where('Id',  $articleArray['ArticleId'])->first();
    //                 if (json_decode($articleouter['ArticleColor'])) {
    //                     $objectArticle->ArticleColor  = implode(',', array_column(json_decode($articleouter['ArticleColor']), 'Name'));
    //                 } else {
    //                     $objectArticle->ArticleColor  = "";
    //                 }
    //             }
    //             $objectArticle->ArticleSize = $articlesColors[0]->ArticleSize;
    //             // if ($PartyId == 1) {
    //             // $allRecords = DB::select("(select outletsalesreturn.NoPacks as NoPacks, 2 as type, outletsalesreturnnumber.CreatedDate as SortDate, from outletsalesreturn inner join outletsalesreturnnumber on outletsalesreturn.SalesReturnNumber = outletsalesreturnnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletsalesreturn.OutletPartyId = '" . $PartyId . "')) union (select outlet.NoPacks as NoPacks, 1 as type, outletnumber.CreatedDate as SortDate from outlet inner join outletnumber on outlet.OutletNumberId = outletnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletnumber.PartyId = '" . $PartyId . "')) union (select outward.NoPacks as NoPacks, 0 as type, outwardnumber.created_at as SortDate from outward inner join transportoutlet on outward.OutwardNumberId = transportoutlet.OutwardNumberId inner join outwardnumber on outward.OutwardNumberId = outwardnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and transportoutlet.TransportStatus = 1 and outward.PartyId = '" . $PartyId . "')) union (select salesreturn.NoPacks as NoPacks, 3 as type ,salesreturnnumber.CreatedDate as SortDate from outward inner join salesreturn on salesreturn.OutwardId = outward.Id inner join salesreturnnumber on salesreturnnumber.Id = salesreturn.SalesReturnNumber where (outward.PartyId = '" . $PartyId . "' and outward.ArticleId = '" . $articleArray['ArticleId'] . "')) order by SortDate asc");
    //             $allRecords = DB::select("select * from (select outletsalesreturn.NoPacks as NoPacks, 2 as type, outletsalesreturnnumber.CreatedDate as SortDate from outletsalesreturn inner join outletsalesreturnnumber on outletsalesreturn.SalesReturnNumber = outletsalesreturnnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' ) union (select outlet.NoPacks as NoPacks, 1 as type, outletnumber.CreatedDate as SortDate from outlet inner join outletnumber on outlet.OutletNumberId = outletnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' )) union (select outward.NoPacks as NoPacks, 0 as type, transportoutlet.ReceivedDate as SortDate from outward inner join transportoutlet on outward.OutwardNumberId = transportoutlet.OutwardNumberId inner join outwardnumber on outward.OutwardNumberId = outwardnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and transportoutlet.TransportStatus = 1 )) union (select salesreturn.NoPacks as NoPacks, 3 as type, salesreturnnumber.CreatedDate as SortDate from outward inner join salesreturn on salesreturn.OutwardId = outward.Id inner join salesreturnnumber on salesreturnnumber.Id = salesreturn.SalesReturnNumber where ( outward.ArticleId = '" . $articleArray['ArticleId'] . "')) ) as dd where SortDate > '2021-12-31' order by dd.SortDate asc");
    //             // } else {
    //             // $allRecords = DB::select('(select `outletsalesreturn`.`NoPacks` as `NoPacks`, 2 as type, `outletsalesreturnnumber`.`CreatedDate` as `SortDate` from `outletsalesreturn` inner join `outletsalesreturnnumber` on `outletsalesreturn`.`SalesReturnNumber` = `outletsalesreturnnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletsalesreturn`.`OutletPartyId` = ' . $PartyId . ')) union (select `outlet`.`NoPacks` as `NoPacks`, 1 as type, `outletnumber`.`CreatedDate` as `SortDate` from `outlet` inner join `outletnumber` on `outlet`.`OutletNumberId` = `outletnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletnumber`.`PartyId` = ' . $PartyId . ')) union (select `outward`.`NoPacks` as `NoPacks`, 0 as type, `outwardnumber`.`created_at` as `SortDate` from `outward` inner join `transportoutlet` on `outward`.`OutwardNumberId` = `transportoutlet`.`OutwardNumberId` inner join `outwardnumber` on `outward`.`OutwardNumberId` = `outwardnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `transportoutlet`.`TransportStatus` = 1 and `outward`.`PartyId` = ' . $PartyId . ')) union (select `salesreturn`.`NoPacks` as `NoPacks`, 3 as type ,`salesreturnnumber`.`CreatedDate` as `SortDate` from `outward` inner join `salesreturn` on `salesreturn`.`OutwardId` = `outward`.`Id` inner join `salesreturnnumber` on `salesreturnnumber`.`Id` = `salesreturn`.`SalesReturnNumber` where (`outward`.`PartyId` = ' . $PartyId . ' and `outward`.`ArticleId` = ' . $articleArray['ArticleId'] . ')) order by `SortDate` asc');
    //             // $allRecords = DB::select("select * from (select outletsalesreturn.NoPacks as NoPacks, 2 as type, outletsalesreturnnumber.CreatedDate as SortDate from outletsalesreturn inner join outletsalesreturnnumber on outletsalesreturn.SalesReturnNumber = outletsalesreturnnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletsalesreturn.OutletPartyId = '" . $PartyId . "') union (select outlet.NoPacks as NoPacks, 1 as type, outletnumber.CreatedDate as SortDate from outlet inner join outletnumber on outlet.OutletNumberId = outletnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletnumber.PartyId = '" . $PartyId . "')) union (select outward.NoPacks as NoPacks, 0 as type, transportoutlet.ReceivedDate as SortDate from outward inner join transportoutlet on outward.OutwardNumberId = transportoutlet.OutwardNumberId inner join outwardnumber on outward.OutwardNumberId = outwardnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and transportoutlet.TransportStatus = 1 and outward.PartyId = '" . $PartyId . "')) union (select salesreturn.NoPacks as NoPacks, 3 as type, salesreturnnumber.CreatedDate as SortDate from outward inner join salesreturn on salesreturn.OutwardId = outward.Id inner join salesreturnnumber on salesreturnnumber.Id = salesreturn.SalesReturnNumber where (outward.PartyId = '" . $PartyId . "' and outward.ArticleId = '" . $articleArray['ArticleId'] . "')) ) as dd");
    //             // }
    //             if (!isset($allRecords[0])) {
    //                 $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->first();
    //                 if ($outletArticle) {
    //                     $outletArticleColors = json_decode($outletArticle->ArticleColor);
    //                 } else {
    //                     $outletArticleColors  = json_decode($articlesColors[0]->ArticleColor);
    //                 }
    //                 $outletArticleColors =  (array)$outletArticleColors;
    //                 if (count($outletArticleColors) > 0) {
    //                     $SalesNoPacks = [];
    //                     foreach ($outletArticleColors as $makearray) {
    //                         array_push($SalesNoPacks, 0);
    //                     }
    //                     $getTransportOutwardpacks =  TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->get();
    //                     if (count($getTransportOutwardpacks) != 0) {
    //                         // $collectionTransportOutwardpacks = collect($transportOutwardpacks);
    //                         // $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
    //                         foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
    //                             $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->first();
    //                             if ($outletArticle) {
    //                                 $outletArticleColors = json_decode($outletArticle->ArticleColor);
    //                             } else {
    //                                 $article = Article::select('ArticleColor')->where('Id',  $articleArray['ArticleId'])->first();
    //                                 $outletArticleColors = json_decode($article['ArticleColor']);
    //                             }
    //                             $count = 0;
    //                             foreach ($outletArticleColors as $outletArticleColor) {
    //                                 if ($outletArticleColor->Id == $getTransportOutwardpack->ColorId) {
    //                                     if (!isset($SalesNoPacks[$count])) {
    //                                         array_push($SalesNoPacks, 0);
    //                                     }
    //                                     $SalesNoPacks[$count] = $SalesNoPacks[$count] + $getTransportOutwardpack->NoPacks;
    //                                 }
    //                                 $count = $count + 1;
    //                             }
    //                         }
    //                     }
    //                     $newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
    //                     $objectArticle->STOCKS =  $newimplodeSalesNoPacks;
    //                     if (array_sum($SalesNoPacks) <= 0) {
    //                         unset($articles[$key]);
    //                     } else {
    //                         $objectArticle->TotalPieces = array_sum($SalesNoPacks);
    //                         $objectArticle->salespacks = array_sum($SalesNoPacks);
    //                     }
    //                     // return $objectArticle->TotalPieces;
    //                 } else {
    //                     $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->get();
    //                     $TotalTransportOutwardpacks = 0;
    //                     if (count($transportOutwardpacks) != 0) {
    //                         $collectionTransportOutwardpacks = collect($transportOutwardpacks);
    //                         $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
    //                         foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
    //                             $TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
    //                         }
    //                     }
    //                     $TotalInwardPacks = $TotalTransportOutwardpacks;
    //                     $TotalOutwardPacks = 0;
    //                     $totalStock = $TotalInwardPacks - $TotalOutwardPacks;
    //                     $objectArticle->STOCKS = $totalStock;
    //                     if ($totalStock <= 0) {
    //                         unset($articles[$key]);
    //                     } else {
    //                         $objectArticle->TotalPieces = $totalStock;
    //                     }
    //                 }
    //             } else {
    //                 if (strpos($allRecords[0]->NoPacks, ',')) {
    //                     $SalesNoPacks = [];
    //                     foreach (explode(",", $allRecords[0]->NoPacks) as $makearray) {
    //                         array_push($SalesNoPacks, 0);
    //                     }
    //                     $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->get();
    //                     $TotalTransportOutwardpacks = 0;
    //                     if (count($transportOutwardpacks) != 0) {
    //                         $collectionTransportOutwardpacks = collect($transportOutwardpacks);
    //                         $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
    //                         foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
    //                             $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->first();
    //                             if ($outletArticle) {
    //                                 $articleColors = json_decode($outletArticle->ArticleColor);
    //                             } else {
    //                                 $article = Article::select('ArticleColor')->where('Id', $articleArray['ArticleId'])->first();
    //                                 $articleColors = json_decode($article['ArticleColor']);
    //                             }
    //                             $count = 0;
    //                             foreach ($articleColors as $articlecolor) {
    //                                 if ($articlecolor->Id == $getTransportOutwardpack->ColorId) {
    //                                     if (!isset($SalesNoPacks[$count])) {
    //                                         array_push($SalesNoPacks, 0);
    //                                     }
    //                                     $SalesNoPacks[$count] = $SalesNoPacks[$count] + $getTransportOutwardpack->NoPacks;
    //                                 }
    //                                 $count = $count + 1;
    //                             }
    //                         }
    //                     }
    //                     foreach ($allRecords as  $allRecord) {
    //                         for ($i = 0; $i < count(explode(",", $allRecord->NoPacks)); $i++) {
    //                             $noPacks = explode(",", $allRecord->NoPacks);
    //                             // if ($allRecord->type == 0) {
    //                                 // $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
    //                             // }
    //                         }
    //                     }
    //                     $newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
    //                     $objectArticle->STOCKS =  $newimplodeSalesNoPacks;
    //                     if (array_sum($SalesNoPacks) <= 0) {
    //                         unset($articles[$key]);
    //                     } else {
    //                         $objectArticle->TotalPieces = array_sum($SalesNoPacks);
    //                     }
    //                 } else {
    //                     $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->get();
    //                     $TotalTransportOutwardpacks = 0;
    //                     if (count($transportOutwardpacks) != 0) {
    //                         $collectionTransportOutwardpacks = collect($transportOutwardpacks);
    //                         $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
    //                         foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
    //                             $TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
    //                         }
    //                     }
    //                     $TotalInwardPacks = $TotalTransportOutwardpacks;
    //                     $TotalOutwardPacks = 0;
    //                     foreach ($allRecords as  $allRecord) {
    //                         if ($allRecord->type == 0) {
    //                             $TotalInwardPacks = $TotalInwardPacks + (int)$allRecord->NoPacks;
    //                         } elseif ($allRecord->type == 1) {
    //                             $TotalOutwardPacks = $TotalOutwardPacks + (int)$allRecord->NoPacks;
    //                         } elseif ($allRecord->type == 2) {
    //                             $TotalInwardPacks = $TotalInwardPacks + (int)$allRecord->NoPacks;
    //                         } elseif ($allRecord->type == 3) {
    //                             $TotalOutwardPacks = $TotalOutwardPacks + (int)$allRecord->NoPacks;
    //                         }
    //                     }
    //                     $totalStock = $TotalInwardPacks - $TotalOutwardPacks;
    //                     $objectArticle->STOCKS = $totalStock;
    //                     if ($totalStock <= 0) {
    //                         unset($articles[$key]);
    //                     } else {
    //                         $objectArticle->TotalPieces = $totalStock;
    //                     }
    //                 }
    //             }
    //         }
    //         return array("data" => array_values($articles), 'PartyName' => '$partyName->Name');


    //     }else{
    //         $articlesArray = DB::select('(select `article`.`ArticleNumber`, `article`.`Id` as `ArticleId` from `transportoutlet` right join `outward` on `transportoutlet`.`OutwardNumberId` = `outward`.`OutwardNumberId` inner join `article` on `article`.`Id` = `outward`.`ArticleId`  where `transportoutlet`.`TransportStatus` = 1 AND `transportoutlet`.`PartyId` = ' . $PartyId . ' )  union (select `article`.`ArticleNumber`, `article`.`Id` as `ArticleId` from `transportoutwardpacks` inner join `article` on `article`.`Id` = `transportoutwardpacks`.`ArticleId` where `transportoutwardpacks`.`OutwardId` = 0 AND `transportoutwardpacks`.`PartyId` = ' . $PartyId . ') order by `ArticleId` asc');
    //         $collectionArticles = collect($articlesArray);
    //         $articles  = $collectionArticles->unique()->values()->all();
    //         foreach ($articles as $key => $article) {
    //             $objectArticle = $article;
    //             $articleArray = (array)$article; 
    //             $articleData = DB::select('select `category`.`Colorflag`, `article`.`ArticleRatio`,`article`.`ArticleOpenFlag`, `category`.`Title`, `brand`.`Name` as `BrandName`, `subcategory`.`Name` as `Subcategory`, `rangeseries`.`SeriesName`, `rangeseries`.`Series`, `article`.`StyleDescription` from `article` inner join `category` on `article`.`CategoryId` = `category`.`Id` left join `brand` on `brand`.`Id` = `article`.`BrandId` left join `subcategory` on `subcategory`.`Id` = `article`.`SubCategoryId` left join `rangeseries` on `rangeseries`.`Id` = `article`.`SeriesId` where `article`.`Id` = ' . $articleArray['ArticleId']);
    //             $articlesColors = DB::select("select   GROUP_CONCAT(DISTINCT CONCAT(articlesize.ArticleSizeName) ORDER BY articlesize.Id SEPARATOR ',') as ArticleSize , GROUP_CONCAT(DISTINCT CONCAT(articlecolor.ArticleColorName) ORDER BY articlecolor.Id SEPARATOR ',') as ArticleColor from article left join articlecolor on articlecolor.ArticleId=article.Id left join articlesize on articlesize.ArticleId=article.Id  where article.Id=" . $articleArray['ArticleId']);
    //             $articleData = (array)$articleData[0];
    //             $objectArticle->Colorflag = $articleData['Colorflag'];
    //             $objectArticle->ArticleRatio = $articleData['ArticleRatio'];
    //             if ($articleData['ArticleRatio']) {
    //                 $objectArticle->TotalArticleRatio = array_sum(explode(",", $articleData['ArticleRatio']));
    //             } else {
    //                 $objectArticle->TotalArticleRatio = 0;
    //             }
    //             $objectArticle->Title = $articleData['Title'];
    //             $objectArticle->BrandName = $articleData['BrandName'];
    //             $objectArticle->Subcategory = $articleData['Subcategory'];
    //             $objectArticle->SeriesName = $articleData['SeriesName'];
    //             $objectArticle->Series = $articleData['Series'];
    //             $objectArticle->StyleDescription = $articleData['StyleDescription'];
    //             $outletArticleColor = Outletimport::where('ArticleId', $articleArray['ArticleId'])->where('PartyId', $PartyId)->first();
    //             if ($outletArticleColor) {
    //                 if (json_decode($outletArticleColor->ArticleColor)) {
    //                     $objectArticle->ArticleColor = implode(',', array_column(json_decode($outletArticleColor->ArticleColor), 'Name'));
    //                 } else {
    //                     $objectArticle->ArticleColor = "";
    //                 }
    //             } else {
    //                 $articleouter = Article::select('ArticleColor')->where('Id',  $articleArray['ArticleId'])->first();
    //                 if (json_decode($articleouter['ArticleColor'])) {
    //                     $objectArticle->ArticleColor  = implode(',', array_column(json_decode($articleouter['ArticleColor']), 'Name'));
    //                 } else {
    //                     $objectArticle->ArticleColor  = "";
    //                 }
    //             }
    //             $objectArticle->ArticleSize = $articlesColors[0]->ArticleSize;
    //             // if ($PartyId == 1) {
    //             // $allRecords = DB::select("(select outletsalesreturn.NoPacks as NoPacks, 2 as type, outletsalesreturnnumber.CreatedDate as SortDate, from outletsalesreturn inner join outletsalesreturnnumber on outletsalesreturn.SalesReturnNumber = outletsalesreturnnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletsalesreturn.OutletPartyId = '" . $PartyId . "')) union (select outlet.NoPacks as NoPacks, 1 as type, outletnumber.CreatedDate as SortDate from outlet inner join outletnumber on outlet.OutletNumberId = outletnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletnumber.PartyId = '" . $PartyId . "')) union (select outward.NoPacks as NoPacks, 0 as type, outwardnumber.created_at as SortDate from outward inner join transportoutlet on outward.OutwardNumberId = transportoutlet.OutwardNumberId inner join outwardnumber on outward.OutwardNumberId = outwardnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and transportoutlet.TransportStatus = 1 and outward.PartyId = '" . $PartyId . "')) union (select salesreturn.NoPacks as NoPacks, 3 as type ,salesreturnnumber.CreatedDate as SortDate from outward inner join salesreturn on salesreturn.OutwardId = outward.Id inner join salesreturnnumber on salesreturnnumber.Id = salesreturn.SalesReturnNumber where (outward.PartyId = '" . $PartyId . "' and outward.ArticleId = '" . $articleArray['ArticleId'] . "')) order by SortDate asc");
    //             $allRecords = DB::select("select * from (select outletsalesreturn.NoPacks as NoPacks, 2 as type, outletsalesreturnnumber.CreatedDate as SortDate from outletsalesreturn inner join outletsalesreturnnumber on outletsalesreturn.SalesReturnNumber = outletsalesreturnnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletsalesreturn.OutletPartyId = '" . $PartyId . "') union (select outlet.NoPacks as NoPacks, 1 as type, outletnumber.CreatedDate as SortDate from outlet inner join outletnumber on outlet.OutletNumberId = outletnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletnumber.PartyId = '" . $PartyId . "')) union (select outward.NoPacks as NoPacks, 0 as type, transportoutlet.ReceivedDate as SortDate from outward inner join transportoutlet on outward.OutwardNumberId = transportoutlet.OutwardNumberId inner join outwardnumber on outward.OutwardNumberId = outwardnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and transportoutlet.TransportStatus = 1 and outward.PartyId = '" . $PartyId . "')) union (select salesreturn.NoPacks as NoPacks, 3 as type, salesreturnnumber.CreatedDate as SortDate from outward inner join salesreturn on salesreturn.OutwardId = outward.Id inner join salesreturnnumber on salesreturnnumber.Id = salesreturn.SalesReturnNumber where (outward.PartyId = '" . $PartyId . "' and outward.ArticleId = '" . $articleArray['ArticleId'] . "')) ) as dd where SortDate > '2021-12-31' order by dd.SortDate asc");
    //             // } else {
    //             // $allRecords = DB::select('(select `outletsalesreturn`.`NoPacks` as `NoPacks`, 2 as type, `outletsalesreturnnumber`.`CreatedDate` as `SortDate` from `outletsalesreturn` inner join `outletsalesreturnnumber` on `outletsalesreturn`.`SalesReturnNumber` = `outletsalesreturnnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletsalesreturn`.`OutletPartyId` = ' . $PartyId . ')) union (select `outlet`.`NoPacks` as `NoPacks`, 1 as type, `outletnumber`.`CreatedDate` as `SortDate` from `outlet` inner join `outletnumber` on `outlet`.`OutletNumberId` = `outletnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `outletnumber`.`PartyId` = ' . $PartyId . ')) union (select `outward`.`NoPacks` as `NoPacks`, 0 as type, `outwardnumber`.`created_at` as `SortDate` from `outward` inner join `transportoutlet` on `outward`.`OutwardNumberId` = `transportoutlet`.`OutwardNumberId` inner join `outwardnumber` on `outward`.`OutwardNumberId` = `outwardnumber`.`Id` where (`ArticleId` = ' . $articleArray['ArticleId'] . ' and `transportoutlet`.`TransportStatus` = 1 and `outward`.`PartyId` = ' . $PartyId . ')) union (select `salesreturn`.`NoPacks` as `NoPacks`, 3 as type ,`salesreturnnumber`.`CreatedDate` as `SortDate` from `outward` inner join `salesreturn` on `salesreturn`.`OutwardId` = `outward`.`Id` inner join `salesreturnnumber` on `salesreturnnumber`.`Id` = `salesreturn`.`SalesReturnNumber` where (`outward`.`PartyId` = ' . $PartyId . ' and `outward`.`ArticleId` = ' . $articleArray['ArticleId'] . ')) order by `SortDate` asc');
    //             // $allRecords = DB::select("select * from (select outletsalesreturn.NoPacks as NoPacks, 2 as type, outletsalesreturnnumber.CreatedDate as SortDate from outletsalesreturn inner join outletsalesreturnnumber on outletsalesreturn.SalesReturnNumber = outletsalesreturnnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletsalesreturn.OutletPartyId = '" . $PartyId . "') union (select outlet.NoPacks as NoPacks, 1 as type, outletnumber.CreatedDate as SortDate from outlet inner join outletnumber on outlet.OutletNumberId = outletnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and outletnumber.PartyId = '" . $PartyId . "')) union (select outward.NoPacks as NoPacks, 0 as type, transportoutlet.ReceivedDate as SortDate from outward inner join transportoutlet on outward.OutwardNumberId = transportoutlet.OutwardNumberId inner join outwardnumber on outward.OutwardNumberId = outwardnumber.Id where (ArticleId = '" . $articleArray['ArticleId'] . "' and transportoutlet.TransportStatus = 1 and outward.PartyId = '" . $PartyId . "')) union (select salesreturn.NoPacks as NoPacks, 3 as type, salesreturnnumber.CreatedDate as SortDate from outward inner join salesreturn on salesreturn.OutwardId = outward.Id inner join salesreturnnumber on salesreturnnumber.Id = salesreturn.SalesReturnNumber where (outward.PartyId = '" . $PartyId . "' and outward.ArticleId = '" . $articleArray['ArticleId'] . "')) ) as dd");
    //             // }
    //             if (!isset($allRecords[0])) {
    //                 $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->where('PartyId', $PartyId)->first();
    //                 if ($outletArticle) {
    //                     $outletArticleColors = json_decode($outletArticle->ArticleColor);
    //                 } else {
    //                     $outletArticleColors  = json_decode($articlesColors[0]->ArticleColor);
    //                 }
    //                 $outletArticleColors =  (array)$outletArticleColors;
    //                 if (count($outletArticleColors) > 0) {
    //                     $SalesNoPacks = [];
    //                     foreach ($outletArticleColors as $makearray) {
    //                         array_push($SalesNoPacks, 0);
    //                     }
    //                     $getTransportOutwardpacks =  TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
    //                     if (count($getTransportOutwardpacks) != 0) {
    //                         // $collectionTransportOutwardpacks = collect($transportOutwardpacks);
    //                         // $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
    //                         foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
    //                             $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->where('PartyId', $PartyId)->first();
    //                             if ($outletArticle) {
    //                                 $outletArticleColors = json_decode($outletArticle->ArticleColor);
    //                             } else {
    //                                 $article = Article::select('ArticleColor')->where('Id',  $articleArray['ArticleId'])->first();
    //                                 $outletArticleColors = json_decode($article['ArticleColor']);
    //                             }
    //                             $count = 0;
    //                             foreach ($outletArticleColors as $outletArticleColor) {
    //                                 if ($outletArticleColor->Id == $getTransportOutwardpack->ColorId) {
    //                                     if (!isset($SalesNoPacks[$count])) {
    //                                         array_push($SalesNoPacks, 0);
    //                                     }
    //                                     $SalesNoPacks[$count] = $SalesNoPacks[$count] + $getTransportOutwardpack->NoPacks;
    //                                 }
    //                                 $count = $count + 1;
    //                             }
    //                         }
    //                     }
    //                     $newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
    //                     $objectArticle->STOCKS =  $newimplodeSalesNoPacks;
    //                     if (array_sum($SalesNoPacks) <= 0) {
    //                         unset($articles[$key]);
    //                     } else {
    //                         $objectArticle->TotalPieces = array_sum($SalesNoPacks);
    //                         $objectArticle->salespacks = array_sum($SalesNoPacks);
    //                     }
    //                     // return $objectArticle->TotalPieces;
    //                 } else {
    //                     $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
    //                     $TotalTransportOutwardpacks = 0;
    //                     if (count($transportOutwardpacks) != 0) {
    //                         $collectionTransportOutwardpacks = collect($transportOutwardpacks);
    //                         $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
    //                         foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
    //                             $TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
    //                         }
    //                     }
    //                     $TotalInwardPacks = $TotalTransportOutwardpacks;
    //                     $TotalOutwardPacks = 0;
    //                     $totalStock = $TotalInwardPacks - $TotalOutwardPacks;
    //                     $objectArticle->STOCKS = $totalStock;
    //                     if ($totalStock <= 0) {
    //                         unset($articles[$key]);
    //                     } else {
    //                         $objectArticle->TotalPieces = $totalStock;
    //                     }
    //                 }
    //             } else {
    //                 if (strpos($allRecords[0]->NoPacks, ',')) {
    //                     $SalesNoPacks = [];
    //                     foreach (explode(",", $allRecords[0]->NoPacks) as $makearray) {
    //                         array_push($SalesNoPacks, 0);
    //                     }
    //                     $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks', 'ColorId')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
    //                     $TotalTransportOutwardpacks = 0;
    //                     if (count($transportOutwardpacks) != 0) {
    //                         $collectionTransportOutwardpacks = collect($transportOutwardpacks);
    //                         $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
    //                         foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
    //                             $outletArticle = Outletimport::where('ArticleId', $articleArray['ArticleId'])->where('PartyId', $PartyId)->first();
    //                             if ($outletArticle) {
    //                                 $articleColors = json_decode($outletArticle->ArticleColor);
    //                             } else {
    //                                 $article = Article::select('ArticleColor')->where('Id', $articleArray['ArticleId'])->first();
    //                                 $articleColors = json_decode($article['ArticleColor']);
    //                             }
    //                             $count = 0;
    //                             foreach ($articleColors as $articlecolor) {
    //                                 if ($articlecolor->Id == $getTransportOutwardpack->ColorId) {
    //                                     if (!isset($SalesNoPacks[$count])) {
    //                                         array_push($SalesNoPacks, 0);
    //                                     }
    //                                     $SalesNoPacks[$count] = $SalesNoPacks[$count] + $getTransportOutwardpack->NoPacks;
    //                                 }
    //                                 $count = $count + 1;
    //                             }
    //                         }
    //                     }
    //                     foreach ($allRecords as  $allRecord) {
    //                         for ($i = 0; $i < count(explode(",", $allRecord->NoPacks)); $i++) {
    //                             $noPacks = explode(",", $allRecord->NoPacks);
    //                             if ($allRecord->type == 0) {
    //                                 $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
    //                             } elseif ($allRecord->type == 1) {
    //                                 $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
    //                             } elseif ($allRecord->type == 2) {
    //                                 $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
    //                             } elseif ($allRecord->type == 3) {
    //                                 $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
    //                             }
    //                         }
    //                     }
    //                     $newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
    //                     $objectArticle->STOCKS =  $newimplodeSalesNoPacks;
    //                     if (array_sum($SalesNoPacks) <= 0) {
    //                         unset($articles[$key]);
    //                     } else {
    //                         $objectArticle->TotalPieces = array_sum($SalesNoPacks);
    //                     }
    //                 } else {
    //                     $transportOutwardpacks =  TransportOutwardpacks::select('NoPacks')->where('ArticleId', $articleArray['ArticleId'])->where('OutwardId', 0)->where('PartyId', $PartyId)->get();
    //                     $TotalTransportOutwardpacks = 0;
    //                     if (count($transportOutwardpacks) != 0) {
    //                         $collectionTransportOutwardpacks = collect($transportOutwardpacks);
    //                         $getTransportOutwardpacks  = $collectionTransportOutwardpacks->unique()->values()->all();
    //                         foreach ($getTransportOutwardpacks as $getTransportOutwardpack) {
    //                             $TotalTransportOutwardpacks = $TotalTransportOutwardpacks + $getTransportOutwardpack->NoPacks;
    //                         }
    //                     }
    //                     $TotalInwardPacks = $TotalTransportOutwardpacks;
    //                     $TotalOutwardPacks = 0;
    //                     foreach ($allRecords as  $allRecord) {
    //                         if ($allRecord->type == 0) {
    //                             $TotalInwardPacks = $TotalInwardPacks + (int)$allRecord->NoPacks;
    //                         } elseif ($allRecord->type == 1) {
    //                             $TotalOutwardPacks = $TotalOutwardPacks + (int)$allRecord->NoPacks;
    //                         } elseif ($allRecord->type == 2) {
    //                             $TotalInwardPacks = $TotalInwardPacks + (int)$allRecord->NoPacks;
    //                         } elseif ($allRecord->type == 3) {
    //                             $TotalOutwardPacks = $TotalOutwardPacks + (int)$allRecord->NoPacks;
    //                         }
    //                     }
    //                     $totalStock = $TotalInwardPacks - $TotalOutwardPacks;
    //                     $objectArticle->STOCKS = $totalStock;
    //                     if ($totalStock <= 0) {
    //                         unset($articles[$key]);
    //                     } else {
    //                         $objectArticle->TotalPieces = $totalStock;
    //                     }
    //                 }
    //             }
    //         }
    //         $partyName = Party::select('Name')->where('Id', $PartyId)->first();

    //         return array("data" => array_values($articles), 'PartyName' => $partyName->Name);
    //     }

    // }

    public function teststockopenflag()
    {
        $colorflag = DB::select("SELECT a.Id, a.ArticleNumber, mn.NoPacks as SalesNoPacks FROM mixnopacks mn left join `article` a on mn.ArticleId=a.Id where a.ArticleOpenFlag='1'");
        if ($colorflag) {
            $UserId = 53;
            foreach ($colorflag as $vl) {
                echo $this->articlesearch($UserId, 0, $vl->ArticleNumber, $vl->SalesNoPacks);
            }
        }
    }
    public function teststock()
    {
        $data = DB::select("SELECT c.Colorflag, a.Id, a.ArticleNumber, a.ArticleColor, i.SalesNoPacks FROM `article` a inner join category c on c.Id=a.CategoryId  inner join inward i on i.ArticleId=a.Id where a.articleStatus = 1 and a.ArticleOpenFlag = 0 group by i.ArticleId");
        foreach ($data as $vl) {
            $getcolor = json_decode($vl->ArticleColor);
            $ArticleName = $vl->ArticleNumber;
            $SalesNoPacks = $vl->SalesNoPacks;
            $Colorflag = $vl->Colorflag;
            $UserId = 53;
            foreach ($getcolor as $key1 => $vl) {
                $ColorId = $vl->Id;
                if ($Colorflag == 1) {
                    if (strpos($SalesNoPacks, ',') !== true) {
                        $NoPacks = explode(",", $SalesNoPacks);
                        $np = $NoPacks[$key1];
                    } else {
                        $np = $SalesNoPacks;
                    }
                } else {
                    $np = $SalesNoPacks;
                }
                echo $this->articlesearch($UserId, $ColorId, $ArticleName, $np);
            }
        }
    }
    public function articlesearch($UserId, $ColorId, $ArticleName, $SalesNoPacks)
    {
        $getuserrole = DB::select("select Role from users where Id=" . $UserId);
        $UserRoleFlag = 1;
        $UserRole = $getuserrole[0]->Role;
        if ($UserRole == 3 || $UserRole == 5 || $UserRole == 6 || $UserRole == 7) {
            $UserRoleFlag = 0;
        } else {
            $UserRoleFlag = 1;
        }
        $inwarddata = "";
        $ColorNoPacks = "";
        $ArticleSizeSet = "";
        $ArticleColorSet = "";
        $historyofsale = [];
        $articleRejected = "";
        $articleCancelled = "";
        $history_newarray = [];
        $total_stock = 0;
        $grandtotalinwardquantity = 0;
        $grandtotaloutwardquantity = 0;
        $Podata = DB::select("SELECT concat(p.PO_Number,'/' ,f.StartYear,'-',f.EndYear) as PurchaseOrderNumber, b.Name as BrandName, v.Name as VendorName, c.Title, c.Colorflag, c.ArticleOpenFlag, DATE_FORMAT(pn.PoDate, '%d-%m-%Y') as PoDate, pn.Id as PNID, p.NumPacks as PO_Peace, p.PO_Image, a.Id, a.ArticleStatus FROM `article` a left join po p on p.ArticleId=a.Id left join purchasenumber pn on pn.Id=p.PO_Number left join financialyear f on f.Id = pn.FinancialYearId left join vendor v on v.Id=p.VendorId inner join category c on c.Id=a.CategoryId left join brand b on b.Id=a.BrandId where a.ArticleNumber = '" . $ArticleName . "'");
        if ($Podata) {
            $articleRejected = DB::select("SELECT DATE_FORMAT(UpdatedDate, '%d-%m-%Y') as RejectDate, ArticleColor, ArticleSize, ArticleRatio, ArticleRate, StyleDescription, Remarks as Reason  FROM `rejectionarticle` where ArticleNumber = '" . $ArticleName . "'");
            $articleCancelled = DB::select("SELECT DATE_FORMAT(ic.CreatedDate, '%d-%m-%Y') as CancelledDate, a.ArticleNumber, a.ArticleRate, a.ArticleColor, a.ArticleSize, a.ArticleRatio, icl.GRN as Id, icl.NoPacks, icl.InwardDate, ic.Notes, ic.GRN FROM `inwardcancellationlogs` icl inner join article a on a.Id=icl.ArticleId inner join inwardcancellation ic on ic.GRN=icl.GRN where ArticleId = '" . $Podata[0]->Id . "'");
            $inwardcheck = DB::select("SELECT count(*) as Total FROM `inward` i inner join article a on a.Id=i.ArticleId where a.ArticleNumber= '" . $ArticleName . "'");
            if ($inwardcheck[0]->Total > 0) {
                $inwarddata = DB::select("SELECT a.Id as ArticlelId, i.Id as InwardId, ig.Id as InwardgrnId, concat(ig.GRN,'/' ,f.StartYear,'-',f.EndYear) as Grnorder, DATE_FORMAT(i.InwardDate, '%d-%m-%Y') as InwardDate, v.Name as VendorName, i.NoPacks, i.SalesNoPacks, a.ArticleColor, a.ArticleSize, a.ArticleRatio, a.StyleDescription,a.ArticleStatus, a.OpeningStock, ar.ArticleRate FROM `article` a inner join inward i on i.ArticleId=a.Id inner join inwardgrn ig on ig.Id=i.GRN inner join financialyear f on f.Id=ig.FinancialYearId left join po p on p.ArticleId=a.Id left join vendor v on v.Id=p.VendorId left join articlerate ar on ar.ArticleId=a.Id where a.ArticleNumber='" . $ArticleName . "' group by i.Id");
                if ($Podata[0]->ArticleOpenFlag == 0) {
                    $ArticleColor = json_decode($inwarddata[0]->ArticleColor, true);
                    $ArticleSize = json_decode($inwarddata[0]->ArticleSize, true);
                    if ($Podata[0]->Colorflag == 1) {
                        foreach ($inwarddata as $key => $vl) {
                            $OpeningStockP = $vl->OpeningStock;
                            if (strpos($vl->NoPacks, ',') !== true) {
                                $NoPacks = explode(",", $inwarddata[$key]->NoPacks);
                                foreach ($ArticleColor as $key1 => $vl1) {
                                    $ArticleColorSet .= $vl1["Name"] . ",";
                                    if ($ColorId == $vl1["Id"]) {
                                        $ColorNoPacks .= $NoPacks[$key1];
                                        $NoPacks = $NoPacks[$key1];
                                    }
                                }
                            } else {
                                $ColorNoPacks .= $vl->NoPacks;
                                $NoPacks = $vl->NoPacks;
                            }
                            if ($OpeningStockP == 1) {
                                $historyofsale[] = array("date" => $vl->InwardDate, "particulars" => array("status" => 0, "partyname" => $vl->VendorName, "type" => "Opening Stock"), "ordertype" => "Opening Stock", "orderno" => $vl->Grnorder, "challanno" => $vl->InwardgrnId, "quantity" => $NoPacks, "rate" => $vl->ArticleRate, "amount" => ($NoPacks * $vl->ArticleRate), "closingquantity" => $ColorNoPacks);
                            } else {
                                $historyofsale[] = array("date" => $vl->InwardDate, "particulars" => array("status" => 0, "partyname" => $vl->VendorName, "type" => "V222"), "ordertype" => "Purchase", "orderno" => $vl->Grnorder, "challanno" => $vl->InwardgrnId, "quantity" => $NoPacks, "rate" => $vl->ArticleRate, "amount" => ($NoPacks * $vl->ArticleRate), "closingquantity" => $ColorNoPacks);
                            }
                        }
                    } else {
                        foreach ($inwarddata as $key => $vl) {
                            if (strpos($vl->NoPacks, ',') !== true) {
                                $NoPacks = explode(",", $inwarddata[$key]->NoPacks);
                                foreach ($ArticleColor as $key1 => $vl1) {
                                    $ArticleColorSet .= $vl1["Name"] . ",";
                                    if ($ColorId == $vl1["Id"]) {
                                        $ColorNoPacks .= $NoPacks[$key1];
                                        $NoPacks = $NoPacks[$key1];
                                    }
                                }
                            } else {
                                $ColorNoPacks .= $vl->NoPacks;
                                $NoPacks = $vl->NoPacks;
                            }
                            if ($vl->OpeningStock == 1) {
                                $historyofsale[] = array("date" => $vl->InwardDate, "particulars" => array("status" => 0, "partyname" => $vl->VendorName, "type" => "Opening Stock"), "ordertype" => "Opening Stock", "orderno" => $vl->Grnorder, "challanno" => $vl->InwardgrnId, "quantity" => $NoPacks, "rate" => $vl->ArticleRate, "amount" => ($NoPacks * $vl->ArticleRate), "closingquantity" => $ColorNoPacks);
                            } else {
                                $historyofsale[] = array("date" => $vl->InwardDate, "particulars" => array("status" => 0, "partyname" => $vl->VendorName, "type" => "V33"), "ordertype" => "Purchase", "orderno" => $vl->Grnorder, "challanno" => $vl->InwardgrnId, "quantity" => $NoPacks, "rate" => $vl->ArticleRate, "amount" => ($NoPacks * $vl->ArticleRate), "closingquantity" => $ColorNoPacks);
                            }
                        }
                    }
                } else {
                    if ($inwarddata) {
                        foreach ($inwarddata as $key => $vl) {
                            if ($vl->OpeningStock == 1) {
                                $ColorNoPacks .= $vl->NoPacks;
                                $historyofsale[] = array("date" => $vl->InwardDate, "particulars" => array("status" => 0, "partyname" => $vl->VendorName, "type" => "Opening Stock"), "ordertype" => "Opening Stock", "orderno" => $vl->Grnorder, "challanno" => $vl->InwardgrnId, "quantity" => $vl->NoPacks, "rate" => $vl->ArticleRate, "amount" => "", "closingquantity" => $vl->NoPacks);
                            } else {
                                $historyofsale[] = array("date" => $vl->InwardDate, "particulars" => array("status" => 0, "partyname" => $vl->VendorName, "type" => "V111"), "ordertype" => "Purchase", "orderno" => $vl->Grnorder, "challanno" => $vl->InwardgrnId, "quantity" => $vl->NoPacks, "rate" => $vl->ArticleRate, "amount" => "", "closingquantity" => "");
                            }
                        }
                    }
                    if ($vl->OpeningStock == 0) {
                        $ColorNoPacks .= 0;
                    }
                }
                $salesorderpending = DB::select("SELECT s.Id as SOID, sn.Id as SoNumberId, DATE_FORMAT(sn.SoDate, '%d-%m-%Y') as SoDate, concat(sn.SoNumber, '/',f.StartYear,'-',f.EndYear) as SoNumber, p.Name as PartyName, s.ArticleRate, s.NoPacks, s.OutwardNoPacks,  sn.Transporter, sn.Destination, sn.Remarks, sn.UserId FROM `so` s inner join sonumber sn on sn.Id=s.SoNumberId inner join financialyear f on f.Id=sn.FinancialYearId inner join party p on p.Id=sn.PartyId where s.ArticleId = '" . $Podata[0]->Id . "' and s.Status=0");
                $outwardorder = DB::select("SELECT o.Id as OutwardId, own.Id as OutwardNoId, DATE_FORMAT(own.OutwardDate, '%d-%m-%Y') as OutwardDate, p.Name as PartyName, concat(own.OutwardNumber, '/',f.StartYear,'-',f.EndYear) as OutwardNumber, o.NoPacks, o.OutwardRate, o.OutwardBox, o.PartyDiscount, own.GSTAmount, own.GSTPercentage, own.GSTType, own.Discount FROM `outward` o inner join outwardnumber own on o.OutwardNumberId=own.Id inner join financialyear f on f.Id=own.FinancialYearId inner join party p on p.Id=o.PartyId where o.ArticleId = '" . $Podata[0]->Id . "'");
                $salesreturnorder = DB::select("SELECT sr.Id as SalesReturnId, DATE_FORMAT(srn.CreatedDate, '%d-%m-%Y') as SalesReturnDate, srn.Id as SalesReturnNumberId, sr.NoPacks, concat(srn.SalesReturnNumber, '/',f.StartYear,'-',f.EndYear) as SalesReturnNumber, p.Name as PartyName FROM `salesreturn` sr inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber inner join party p on p.Id=srn.PartyId inner join financialyear f on f.Id=srn.FinancialYearId where sr.ArticleId='" . $Podata[0]->Id . "'");
                $purchasereturnorder = DB::select("SELECT pr.Id as PurchaseReturnId, prn.Id as PurchaseReturnNumberId, DATE_FORMAT(prn.CreatedDate, '%d-%m-%Y') as PurchaseReturnDate, pr.ReturnNoPacks as NoPacks, concat(prn.PurchaseReturnNumber, '/',f.StartYear,'-',f.EndYear) as PurchaseReturnNumber, v.Name as VendorName FROM `purchasereturn` pr inner join purchasereturnnumber prn on prn.Id=pr.PurchaseReturnNumber inner join vendor v on v.Id=prn.VendorId inner join financialyear f on f.Id=prn.FinancialYearId where pr.ArticleId='" . $Podata[0]->Id . "'");
                $stocktransfer_cons = DB::select("SELECT st.Id, st.StocktransferNumberId, st.ConsumedNoPacks, DATE_FORMAT(stn.StocktransferDate, '%d-%m-%Y') as StocktransferDate, concat(stn.StocktransferNumber, '/',f.StartYear,'-',f.EndYear) as StocktransferNumber FROM `stocktransfer` st inner join article a on a.Id=st.ConsumedArticleId inner join stocktransfernumber stn on stn.Id=st.StocktransferNumberId inner join financialyear f on f.Id=stn.FinancialYearId where st.ConsumedArticleId = '" . $Podata[0]->Id . "'");
                $stocktransfer_prod = DB::select("SELECT st.Id, st.StocktransferNumberId, st.TransferNoPacks, DATE_FORMAT(stn.StocktransferDate, '%d-%m-%Y') as StocktransferDate, concat(stn.StocktransferNumber, '/',f.StartYear,'-',f.EndYear) as StocktransferNumber FROM `stocktransfer` st inner join article a on a.Id=st.ConsumedArticleId inner join stocktransfernumber stn on stn.Id=st.StocktransferNumberId inner join financialyear f on f.Id=stn.FinancialYearId where st.TransferArticleId = '" . $Podata[0]->Id . "'");
                $stocktransfer_shortage = DB::select("SELECT st.Id, st.StocktransferNumberId, st.NoPacks, DATE_FORMAT(stn.StocktransferDate, '%d-%m-%Y') as StocktransferDate, concat(stn.StocktransferNumber, '/',f.StartYear,'-',f.EndYear) as StocktransferNumber FROM `stockshortage` st inner join article a on a.Id=st.ArticleId inner join stocktransfernumber stn on stn.Id=st.StocktransferNumberId inner join financialyear f on f.Id=stn.FinancialYearId where st.ArticleId = '" . $Podata[0]->Id . "'");
                if ($Podata[0]->Colorflag == 1) {
                    if ($outwardorder) {
                        foreach ($outwardorder as $key => $vl) {
                            $OwNoPacksPCS = 0;
                            $OwNoPacks = explode(",", $vl->NoPacks);
                            $object = (object) $vl;
                            foreach ($ArticleColor as $key => $vl) {
                                if ($ColorId == $vl["Id"]) {
                                    $object->OwNoPacks = $OwNoPacks[$key];
                                    $OwNoPacksPCS = $OwNoPacks[$key];
                                }
                            }
                            if ($OwNoPacksPCS != 0) {
                                $historyofsale[] = array("date" => $object->OutwardDate, "particulars" => array("status" => 2, "partyname" => $object->PartyName, "type" => "P"), "ordertype" => "Outward", "orderno" => $object->OutwardNumber, "challanno" => $object->OutwardNoId, "quantity" => $OwNoPacksPCS, "rate" => $object->OutwardRate, "amount" => ($OwNoPacksPCS * $object->OutwardRate), "closingquantity" => "");
                            }
                        }
                    }
                    if ($stocktransfer_shortage) {
                        foreach ($stocktransfer_shortage as $key => $vl) {
                            $STShortageNoPacksPCS = 0;
                            $STShortageNoPacks = explode(",", $vl->NoPacks);
                            $object = (object) $vl;
                            foreach ($ArticleColor as $key => $vl) {
                                if ($ColorId == $vl["Id"]) {
                                    $object->STShortageNoPacks = $STShortageNoPacks[$key];
                                    $STShortageNoPacksPCS = $STShortageNoPacks[$key];
                                }
                            }
                            if ($STShortageNoPacksPCS != 0) {
                                $historyofsale[] = array("date" => $object->StocktransferDate, "particulars" => array("status" => 7, "partyname" => '', "type" => "Shortage"), "ordertype" => "Shortage", "orderno" => $object->StocktransferNumber, "challanno" => $object->StocktransferNumberId, "quantity" => $STShortageNoPacksPCS, "rate" => "", "amount" => "", "closingquantity" => "");
                            }
                        }
                    }
                    if ($stocktransfer_cons) {
                        foreach ($stocktransfer_cons as $key => $vl) {
                            $STConNoPacksPCS = 0;
                            $STConNoPacks = explode(",", $vl->ConsumedNoPacks);
                            $object = (object) $vl;
                            foreach ($ArticleColor as $key => $vl) {
                                if ($ColorId == $vl["Id"]) {
                                    $object->STConNoPacks = $STConNoPacks[$key];
                                    $STConNoPacksPCS = $STConNoPacks[$key];
                                }
                            }
                            if ($STConNoPacksPCS != 0) {
                                $historyofsale[] = array("date" => $object->StocktransferDate, "particulars" => array("status" => 5, "partyname" => '', "type" => "Stocktransfer"), "ordertype" => "Stocktransfer Consumed", "orderno" => $object->StocktransferNumber, "challanno" => $object->StocktransferNumberId, "quantity" => $STConNoPacksPCS, "rate" => "", "amount" => "", "closingquantity" => "");
                            }
                        }
                    }
                    if ($stocktransfer_prod) {
                        foreach ($stocktransfer_prod as $key => $vl) {
                            $STProNoPacksPCS = 0;
                            $STProNoPacks = explode(",", $vl->TransferNoPacks);
                            $object = (object) $vl;
                            foreach ($ArticleColor as $key => $vl) {
                                if ($ColorId == $vl["Id"]) {
                                    $object->STProNoPacks = $STProNoPacks[$key];
                                    $STProNoPacksPCS = $STProNoPacks[$key];
                                }
                            }
                            if ($STProNoPacksPCS != 0) {
                                $historyofsale[] = array("date" => $object->StocktransferDate, "particulars" => array("status" => 6, "partyname" => '', "type" => "Stocktransfer"), "ordertype" => "Stocktransfer Production", "orderno" => $object->StocktransferNumber, "challanno" => $object->StocktransferNumberId, "quantity" => $STProNoPacksPCS, "rate" => "", "amount" => "", "closingquantity" => "");
                            }
                        }
                    }
                    if ($salesreturnorder) {
                        foreach ($salesreturnorder as $key => $vl) {
                            $SRNoPacksPCS = 0;
                            $SRNoPacks = explode(",", $vl->NoPacks);
                            $object = (object) $vl;
                            foreach ($ArticleColor as $key => $vl) {
                                if ($ColorId == $vl["Id"]) {
                                    $object->SRNoPacks = $SRNoPacks[$key];
                                    $SRNoPacksPCS = $SRNoPacks[$key];
                                }
                            }
                            if ($SRNoPacksPCS != 0) {
                                $historyofsale[] = array("date" => $object->SalesReturnDate, "particulars" => array("status" => 3, "partyname" => $object->PartyName, "type" => "P"), "ordertype" => "SalesReturn", "orderno" => $object->SalesReturnNumber, "challanno" => $object->SalesReturnNumberId, "quantity" => $SRNoPacksPCS, "rate" => "", "amount" => "", "closingquantity" => "");
                            }
                        }
                    }
                    if ($purchasereturnorder) {
                        foreach ($purchasereturnorder as $key => $vl) {
                            $PRNoPacksPCS = 0;
                            $PRNoPacks = explode(",", $vl->NoPacks);

                            $object = (object) $vl;
                            foreach ($ArticleColor as $key => $vl) {
                                if ($ColorId == $vl["Id"]) {
                                    $object->PRNoPacks = $PRNoPacks[$key];
                                    $PRNoPacksPCS = $PRNoPacks[$key];
                                }
                            }
                            if ($PRNoPacksPCS != 0) {
                                $historyofsale[] = array("date" => $object->PurchaseReturnDate, "particulars" => array("status" => 4, "partyname" => $object->VendorName, "type" => "V"), "ordertype" => "PurchaseReturn", "orderno" => $object->PurchaseReturnNumber, "challanno" => $object->PurchaseReturnNumberId, "quantity" => $PRNoPacksPCS, "rate" => "", "amount" => "", "closingquantity" => "");
                            }
                        }
                    }
                    if ($salesorderpending) {
                        $salespending = array();
                        foreach ($salesorderpending as $key => $vl) {
                            $SoNoPacksPCS = 0;
                            $SoOutwardNoPacksPCS = "";
                            $SoNoPacks = explode(",", $vl->NoPacks);
                            $SoOutwardNoPacks = explode(",", $vl->OutwardNoPacks);
                            $object = (object) $vl;
                            foreach ($ArticleColor as $key => $vl) {
                                if ($ColorId == $vl["Id"]) {
                                    $object->SoNoPacks = $SoNoPacks[$key];
                                    $object->SoOutwardNoPacks = $SoOutwardNoPacks[$key];
                                    $SoNoPacksPCS = $SoNoPacks[$key];
                                    $SoOutwardNoPacksPCS = $SoOutwardNoPacks[$key];
                                }
                            }
                            if ($SoNoPacksPCS != 0 && $SoOutwardNoPacksPCS != 0) {
                                $salespending[] = array("date" => $object->SoDate, "particulars" => array("salesdate" => $object->SoDate, "partyname" => $object->PartyName, "type" => "P", "rate" => $object->ArticleRate, "quantity" => $SoOutwardNoPacksPCS, "orderno" => $object->SoNumber, "challanno" => $object->SoNumberId, "challantype" => 1), "ordertype" => "Sales", "orderno" => $object->SoNumber, "quantity" => "", "rate" => "", "amount" => "", "closingquantity" => "");
                            }
                        }
                        if (!empty($salespending)) {
                            $historyofsale[] = array("ordertype" => "Sales", "particulars" => array("status" => 1, "type" => "P", "TotelSalesPending" => 0, "salespending" => $salespending));
                        }
                    }
                } else {
                    if ($outwardorder) {
                        foreach ($outwardorder as $key => $vl) {
                            $object = (object) $vl;
                            $object->OwNoPacks = $vl->NoPacks;
                            $OwNoPacksPCS = $vl->NoPacks;
                            if ($OwNoPacksPCS != 0) {
                                $historyofsale[] = array("date" => $object->OutwardDate, "particulars" => array("status" => 2, "partyname" => $object->PartyName, "type" => "P"), "ordertype" => "Outward", "orderno" => $object->OutwardNumber, "challanno" => $object->OutwardNoId, "quantity" => $OwNoPacksPCS, "rate" => $object->OutwardRate, "amount" => ($OwNoPacksPCS * $object->OutwardRate), "closingquantity" => "");
                            }
                        }
                    }
                    if ($stocktransfer_shortage) {
                        foreach ($stocktransfer_shortage as $key => $vl) {
                            $object = (object) $vl;
                            $STShortageNoPacksPCS = $vl->NoPacks;
                            if ($STShortageNoPacksPCS != 0) {
                                $historyofsale[] = array("date" => $object->StocktransferDate, "particulars" => array("status" => 7, "partyname" => '', "type" => "ST"), "ordertype" => "Shortage", "orderno" => $object->StocktransferNumber, "challanno" => $object->StocktransferNumberId, "quantity" => $STShortageNoPacksPCS, "rate" => "", "amount" => "", "closingquantity" => "");
                            }
                        }
                    }
                    if ($stocktransfer_cons) {
                        foreach ($stocktransfer_cons as $key => $vl) {
                            $object = (object) $vl;
                            $STConNoPacksPCS = $vl->ConsumedNoPacks;
                            if ($STConNoPacksPCS != 0) {
                                $historyofsale[] = array("date" => $object->StocktransferDate, "particulars" => array("status" => 5, "partyname" => '', "type" => "S"), "ordertype" => "Stocktransfer Consumed", "orderno" => $object->StocktransferNumber, "challanno" => $object->StocktransferNumberId, "quantity" => $STConNoPacksPCS, "rate" => "", "amount" => "", "closingquantity" => "");
                            }
                        }
                    }
                    if ($stocktransfer_prod) {
                        foreach ($stocktransfer_prod as $key => $vl) {
                            $object = (object) $vl;
                            $STProNoPacksPCS = $vl->TransferNoPacks;
                            if ($STProNoPacksPCS != 0) {
                                $historyofsale[] = array("date" => $object->StocktransferDate, "particulars" => array("status" => 6, "partyname" => '', "type" => "S"), "ordertype" => "Stocktransfer Production", "orderno" => $object->StocktransferNumber, "challanno" => $object->StocktransferNumberId, "quantity" => $STProNoPacksPCS, "rate" => "", "amount" => "", "closingquantity" => "");
                            }
                        }
                    }
                    if ($salesreturnorder) {
                        foreach ($salesreturnorder as $key => $vl) {
                            $object = (object) $vl;
                            $SRNoPacksPCS = $vl->NoPacks;
                            if ($SRNoPacksPCS != 0) {
                                $historyofsale[] = array("date" => $object->SalesReturnDate, "particulars" => array("status" => 3, "partyname" => $object->PartyName, "type" => "P"), "ordertype" => "SalesReturn", "orderno" => $object->SalesReturnNumber, "challanno" => $object->SalesReturnNumberId, "quantity" => $SRNoPacksPCS, "rate" => "", "amount" => "", "closingquantity" => "");
                            }
                        }
                    }
                    if ($purchasereturnorder) {
                        foreach ($purchasereturnorder as $key => $vl) {
                            $object = (object) $vl;
                            $PRNoPacksPCS = $vl->NoPacks;

                            if ($PRNoPacksPCS != 0) {
                                $historyofsale[] = array("date" => $object->PurchaseReturnDate, "particulars" => array("status" => 4, "partyname" => $object->VendorName, "type" => "V"), "ordertype" => "PurchaseReturn", "orderno" => $object->PurchaseReturnNumber, "challanno" => $object->PurchaseReturnNumberId, "quantity" => $PRNoPacksPCS, "rate" => "", "amount" => "", "closingquantity" => "");
                            }
                        }
                    }
                    if ($salesorderpending) {
                        $salespending = array();
                        foreach ($salesorderpending as $key => $vl) {
                            $object = (object) $vl;
                            $object->SoNoPacks = $vl->NoPacks;
                            $object->SoOutwardNoPacks = $vl->OutwardNoPacks;

                            $SoNoPacksPCS = $vl->NoPacks;
                            $SoOutwardNoPacksPCS = $vl->OutwardNoPacks;

                            if ($SoNoPacksPCS != 0 && $SoOutwardNoPacksPCS != 0) {
                                $salespending[] = array("date" => $object->SoDate, "particulars" => array("salesdate" => $object->SoDate, "partyname" => $object->PartyName, "type" => "P", "rate" => $object->ArticleRate, "quantity" => $SoOutwardNoPacksPCS, "orderno" => $object->SoNumber, "challanno" => $object->SoNumberId, "challantype" => 1), "ordertype" => "Sales", "orderno" => $object->SoNumber, "quantity" => "", "rate" => "", "amount" => "", "closingquantity" => "");
                            }
                        }
                        if (!empty($salespending)) {
                            $historyofsale[] = array("ordertype" => "Sales", "particulars" => array("status" => 1, "type" => "P", "TotelSalesPending" => 0, "salespending" => $salespending));
                        }
                    }
                    if ($Podata[0]->ArticleOpenFlag == 0) {
                        foreach ($ArticleColor as $key => $vl) {
                            $ArticleColorSet .= $vl["Name"] . ",";
                        }
                        $ArticleColorSet = rtrim($ArticleColorSet, ',');
                    } else {
                        $ArticleColorSet = 0;
                        $ArticleSizeSet .= 0;
                    }
                    $ColorNoPacks .= $inwarddata[0]->NoPacks;
                }
                if ($Podata[0]->ArticleOpenFlag == 0) {
                    foreach ($ArticleSize as $vl) {
                        $ArticleSizeSet .= $vl["Name"] . ",";
                    }
                    $ArticleSizeSet = rtrim($ArticleSizeSet, ',');
                }
                if ($historyofsale) {
                    usort($historyofsale, function ($element1, $element2) {
                        $ordertype = $element1['ordertype'];
                        $ordertype2 = $element2['ordertype'];
                        if ($ordertype != "Sales" && $ordertype2 != "Sales") {
                            $var = $element1['date'];
                            $date = str_replace('/', '-', $var);
                            $f1 = date('Y-m-d', strtotime($date));

                            $var2 = $element2['date'];
                            $date2 = str_replace('/', '-', $var2);
                            $f2 = date('Y-m-d', strtotime($date2));

                            $datetime1 = strtotime($f1);
                            $datetime2 = strtotime($f2);
                            return $datetime1 - $datetime2;
                        }
                    });
                    $count = 0;
                    $innersalesclosingquantity = 0;
                    foreach ($historyofsale as $key => $vl) {
                        $quantityval = "";
                        $quantity = 0;
                        $ordertype = $vl['ordertype'];
                        $object = (object) $vl;
                        if ($ordertype == "Purchase" || $ordertype == "Opening Stock") {
                            $quantityval = $object->quantity;
                            $count = $count + $quantityval;
                            $grandtotalinwardquantity = $grandtotalinwardquantity + $quantityval;
                            $object->closingquantity = $count;
                            if ($object->rate) {
                                $object->rate = number_format($object->rate, 2);
                            }
                        } else if ($ordertype == "Outward") {
                            $quantity = (int) $object->quantity;
                            $count = $count - $quantity;
                            $grandtotaloutwardquantity = $grandtotaloutwardquantity + $quantity;
                            $object->closingquantity = $count;
                            $object->rate = number_format($object->rate, 2);
                        } else if ($ordertype == "Shortage") {
                            $quantity = (int) $object->quantity;
                            $count = $count - $quantity;
                            $object->closingquantity = $count;
                            if ($object->rate) {
                                $object->rate = number_format($object->rate, 2);
                            }
                        } else if ($ordertype == "Stocktransfer Production") {
                            $quantityval = $object->quantity;
                            $count = $count + $quantityval;
                            $grandtotalinwardquantity = $grandtotalinwardquantity + $quantityval;
                            $object->closingquantity = $count;
                            if ($object->rate) {
                                $object->rate = number_format($object->rate, 2);
                            }
                        } else if ($ordertype == "Stocktransfer Consumed") {
                            $quantity = (int) $object->quantity;
                            $count = $count - $quantity;
                            $grandtotaloutwardquantity = $grandtotaloutwardquantity + $quantity;
                            $object->closingquantity = $count;
                            if ($object->rate) {
                                $object->rate = number_format($object->rate, 2);
                            }
                        } else if ($ordertype == "Opening Stock") {
                            $count = $count + (int) $object->quantity;
                            $object->closingquantity = $count;
                        } else if ($ordertype == "SalesReturn") {
                            $count = $count + (int) $object->quantity;
                            $grandtotalinwardquantity = $grandtotalinwardquantity + (int) $object->quantity;
                            $object->closingquantity = $count;
                        } else if ($ordertype == "PurchaseReturn") {
                            $count = $count - (int) $object->quantity;
                            $grandtotaloutwardquantity = $grandtotaloutwardquantity + (int) $object->quantity;
                            $object->closingquantity = $count;
                        } else if ($ordertype == "Sales") {
                            foreach ($object->particulars['salespending'] as &$val) {
                                if ($innersalesclosingquantity == 0) {
                                    $innersalesclosingquantity = $val["particulars"]["quantity"];
                                } else {
                                    $innersalesclosingquantity = $innersalesclosingquantity + $val["particulars"]["quantity"];
                                }
                                $grandtotaloutwardquantity = $grandtotaloutwardquantity + $val["particulars"]["quantity"];
                                $val["closingquantity"] = $innersalesclosingquantity;
                            }
                            $object->quantity = $innersalesclosingquantity;
                            $object->TotelSalesPending = $count - $innersalesclosingquantity;
                        } else {
                        }
                        $history_newarray[] = $object;
                    }
                    $total_stock = $count - $innersalesclosingquantity;
                }
            }
            if ($articleRejected) {
                if ($Podata[0]->ArticleOpenFlag == 0) {
                    $RejectedArticleColor = json_decode($articleRejected[0]->ArticleColor, true);
                    $RejectedArticleSize = json_decode($articleRejected[0]->ArticleSize, true);
                    $RejectedArticleColorSet = "";
                    $RejectedArticleSizeSet = "";
                    foreach ($RejectedArticleColor as $key => $vl) {
                        $RejectedArticleColorSet .= $vl["Name"] . ",";
                    }
                    foreach ($RejectedArticleSize as $key => $vl) {
                        $RejectedArticleSizeSet .= $vl["Name"] . ",";
                    }
                    $RejectedArticleColorSet = rtrim($RejectedArticleColorSet, ',');
                    $RejectedArticleSizeSet = rtrim($RejectedArticleSizeSet, ',');
                } else {
                    $RejectedArticleColorSet = "";
                    $RejectedArticleSizeSet = "";
                }
                foreach ($articleRejected as $key => $vl) {
                    $object = (object) $vl;
                    $object->ArticleColor = $RejectedArticleColorSet;
                    $object->ArticleSize = $RejectedArticleSizeSet;
                }
            }
            if ($articleCancelled) {
                if ($Podata[0]->ArticleOpenFlag == 0) {
                    $CancelledArticleColor = json_decode($articleCancelled[0]->ArticleColor, true);
                    $CancelledArticleSize = json_decode($articleCancelled[0]->ArticleSize, true);
                    $CancelledArticleColorSet = "";
                    $CancelledArticleSizeSet = "";
                    foreach ($CancelledArticleColor as $key => $vl) {
                        $CancelledArticleColorSet .= $vl["Name"] . ",";
                    }
                    foreach ($CancelledArticleSize as $key => $vl) {
                        $CancelledArticleSizeSet .= $vl["Name"] . ",";
                    }
                    $CancelledArticleColorSet = rtrim($CancelledArticleColorSet, ',');
                    $CancelledArticleSizeSet = rtrim($CancelledArticleSizeSet, ',');
                } else {
                    $CancelledArticleColorSet = "";
                    $CancelledArticleSizeSet = "";
                }
                foreach ($articleCancelled as $key => $vl) {
                    $acNoPacks = explode(",", $vl->NoPacks);
                    $object = (object) $vl;
                    $object->ArticleSize = $CancelledArticleSizeSet;
                    $object->ArticleColor = $CancelledArticleColorSet;
                    if ($Podata[0]->Colorflag == 1) {
                        foreach ($CancelledArticleColor as $key => $vl) {
                            if ($ColorId == $vl["Id"]) {
                                $object->NoPacks = $acNoPacks[$key];
                            }
                        }
                    }
                }
            }
        }
        if ($UserRoleFlag == 0) {
            $history_newarray = [];
        }
        if ($SalesNoPacks != $total_stock) {
            return "ArticleName: " . $ArticleName . " | ColorId:" . $ColorId . " | <span style='background:red'>TotalStock: " . $total_stock . "</span> | SalesNoPacks: " . $SalesNoPacks . " | Total inward: " . $grandtotalinwardquantity . " | Total Outward: " . $grandtotaloutwardquantity;
        }
    }
    public function GetOutletRangeStocks($startdate, $enddate, $PartyId)
    {
        $TotalarticleOpeningStock = 0;
        $articlesArray = DB::select("select * from ((select article.ArticleNumber, article.Id as ArticleId , DATE_FORMAT(transportoutlet.created_at, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(transportoutlet.ReceivedDate, '%d-%m-%Y') as ReceivedDate from transportoutlet right join outward on transportoutlet.OutwardNumberId = outward.OutwardNumberId inner join article on article.Id = outward.ArticleId where transportoutlet.TransportStatus = 1 AND transportoutlet.PartyId = " . $PartyId . ") union (select article.ArticleNumber, article.Id as ArticleId ,DATE_FORMAT(transportoutwardpacks.CreatedDate, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(transportoutwardpacks.CreatedDate, '%d-%m-%Y') as ReceivedDate from transportoutwardpacks inner join article on article.Id = transportoutwardpacks.ArticleId where transportoutwardpacks.OutwardId = 0 AND transportoutwardpacks.PartyId = " . $PartyId . " )) as aa ORDER BY aa.ArticleNumber ASC");
        foreach ($articlesArray as $article) {
            unset($article->CreatedAt);
            unset($article->ReceivedDate);
        }
        $collectionArticles = collect($articlesArray);
        $articles = $collectionArticles->unique()->values()->all();
        foreach ($articles as $key => $article) {
            // opening stock
            $beforeInwardss = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join outward o on o.Id=trop.OutwardId) as dd where dd.CreatedDate < '" . $startdate . "' and dd.ArticleId='" . $article->ArticleId . "' and dd.PartyId='" . $PartyId . "'");
            $beforeImports = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, trop.OutwardId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop) as dd where dd.OutwardId=0 and dd.CreatedDate < '" . $startdate . "' and dd.ArticleId='" . $article->ArticleId . "' and dd.PartyId='" . $PartyId . "'");
            $beforeOutwards = DB::select("select * from (select o.NoPacks, o.ArticleId, otn.PartyId, DATE_FORMAT(o.created_at,'%Y-%m-%d') as OutletDate from outlet o inner join outletnumber otn on otn.Id=o.OutletNumberId) as dd where dd.OutletDate < '" . $startdate . "' and dd.ArticleId='" . $article->ArticleId . "' and dd.PartyId='" . $PartyId . "'");
            $beforePurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate < '" . $startdate . "' and dd.CreatedDate > '2021-12-31' and dd.ArticleId='" . $article->ArticleId . "' and dd.PartyId='" . $PartyId . "'");
            $beforeSalesReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, sr.OutletPartyId as PartyId from outletsalesreturn sr inner join outletsalesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate < '" . $startdate . "' and dd.ArticleId='" . $article->ArticleId . "' and dd.PartyId='" . $PartyId . "'");
            $articleOpeningStock = 0;
            foreach ($beforeInwardss as $beforeInward) {
                $inwardPacks = 0;
                if (strpos($beforeInward->NoPacks, ',') != false) {
                    $inwardPacks = $inwardPacks + array_sum(explode(',', $beforeInward->NoPacks));
                } else {
                    $inwardPacks = $inwardPacks + (int) $beforeInward->NoPacks;
                }
                $articleOpeningStock = $articleOpeningStock + $inwardPacks;
            }
            foreach ($beforeImports as $beforeImport) {
                $importPacks = 0;
                if (strpos($beforeImport->NoPacks, ',') != false) {
                    $importPacks = $importPacks + array_sum(explode(',', $beforeImport->NoPacks));
                } else {
                    $importPacks = $importPacks + (int) $beforeImport->NoPacks;
                }
                $articleOpeningStock = $articleOpeningStock + $importPacks;
            }
            $TotalarticleOpeningStock = $TotalarticleOpeningStock + $articleOpeningStock;
            foreach ($beforeOutwards as $beforeOutward) {
                $outwardPacks = 0;
                if (strpos($beforeOutward->NoPacks, ',') != false) {
                    $outwardPacks = $outwardPacks + array_sum(explode(',', $beforeOutward->NoPacks));
                } else {
                    $outwardPacks = $outwardPacks + (int) $beforeOutward->NoPacks;
                }
                $articleOpeningStock = $articleOpeningStock - $outwardPacks;
            }
            foreach ($beforePurchaseReturns as $beforePurchaseReturn) {
                $PRPacks = 0;
                if (strpos($beforePurchaseReturn->NoPacks, ',') != false) {
                    $PRPacks = $PRPacks + array_sum(explode(',', $beforePurchaseReturn->NoPacks));
                } else {
                    $PRPacks = $PRPacks + (int) $beforePurchaseReturn->NoPacks;
                }
                $articleOpeningStock = $articleOpeningStock - $PRPacks;
            }
            foreach ($beforeSalesReturns as $beforeSalesReturn) {
                $SRPacks = 0;
                if (strpos($beforeSalesReturn->NoPacks, ',') != false) {
                    $SRPacks = $SRPacks + array_sum(explode(',', $beforeSalesReturn->NoPacks));
                } else {
                    $SRPacks = $SRPacks + (int) $beforeSalesReturn->NoPacks;
                }
                $articleOpeningStock = $articleOpeningStock + $SRPacks;
            }
            // end opening stock
            $objectArticle = $article;
            $articleArray = (array) $article;
            // start range stock
            $articleDetailss = DB::select("select a.ArticleNumber, c.Title as Category, rs.SeriesName as SeriesName, sc.Name as SubCategory from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId inner join category c on c.Id=a.CategoryId left join subcategory sc on sc.Id=a.SubCategoryId left join rangeseries rs on rs.Id=a.SeriesId where trop.ArticleId='" . $article->ArticleId . "' and trop.PartyId='" . $PartyId . "'");
            $collectionArticle = collect($articleDetailss);
            $articleDetails = $collectionArticle->unique()->values()->all();
            foreach ($articleDetails as $articleDetail) {
                $objectArticle->ArticleNumber = $articleDetail->ArticleNumber;
                $objectArticle->Title = $articleDetail->Category;
                $objectArticle->Subcategory = $articleDetail->SubCategory;
                $objectArticle->SeriesName = $articleDetail->SeriesName;
            }
            $inwardPacks = 0;
            $importPacks = 0;
            $outwardPacks = 0;
            $salesreturnPacks = 0;
            $purchasereturnPacks = 0;
            $Inwardss = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join outward o on o.Id=trop.OutwardId) as dd where dd.CreatedDate >= '" . $startdate . "' and dd.CreatedDate <= '" . $enddate . "' and dd.ArticleId='" . $article->ArticleId . "' and dd.PartyId='" . $PartyId . "'");
            $Imports = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, trop.OutwardId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop) as dd where dd.OutwardId=0 and dd.CreatedDate >= '" . $startdate . "' and dd.CreatedDate <= '" . $enddate . "' and dd.ArticleId='" . $article->ArticleId . "' and dd.PartyId='" . $PartyId . "'");
            $Outwards = DB::select("select * from (select o.NoPacks, o.ArticleId, otn.PartyId, DATE_FORMAT(o.created_at,'%Y-%m-%d') as OutletDate from outlet o inner join outletnumber otn on otn.Id=o.OutletNumberId) as dd where dd.OutletDate >= '" . $startdate . "' and dd.OutletDate <= '" . $enddate . "' and dd.ArticleId='" . $article->ArticleId . "' and dd.PartyId='" . $PartyId . "'");
            $PurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate >= '" . $startdate . "' and dd.CreatedDate <= '" . $enddate . "' and dd.CreatedDate > '2021-12-31' and dd.ArticleId='" . $article->ArticleId . "' and dd.PartyId='" . $PartyId . "'");
            $SalesReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, sr.OutletPartyId as PartyId from outletsalesreturn sr inner join outletsalesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate >= '" . $startdate . "' and dd.CreatedDate <= '" . $enddate . "' and dd.ArticleId='" . $article->ArticleId . "' and dd.PartyId='" . $PartyId . "'");
            foreach ($Inwardss as $Inward) {
                if (strpos($Inward->NoPacks, ',') != false) {
                    $inwardPacks = $inwardPacks + array_sum(explode(',', $Inward->NoPacks));
                } else {
                    $inwardPacks = $inwardPacks + (int) $Inward->NoPacks;
                }
            }
            foreach ($Imports as $Import) {
                if (strpos($Import->NoPacks, ',') != false) {
                    $importPacks = $importPacks + array_sum(explode(',', $Import->NoPacks));
                } else {
                    $importPacks = $importPacks + (int) $Import->NoPacks;
                }
            }
            foreach ($Outwards as $Outward) {
                if (strpos($Outward->NoPacks, ',') != false) {
                    $outwardPacks = $outwardPacks + array_sum(explode(',', $Outward->NoPacks));
                } else {
                    $outwardPacks = $outwardPacks + (int) $Outward->NoPacks;
                }
            }
            foreach ($PurchaseReturns as $PurchaseReturn) {
                if (strpos($PurchaseReturn->NoPacks, ',') != false) {
                    $purchasereturnPacks = $purchasereturnPacks + array_sum(explode(',', $PurchaseReturn->NoPacks));
                } else {
                    $purchasereturnPacks = $purchasereturnPacks + (int) $PurchaseReturn->NoPacks;
                }
            }
            foreach ($SalesReturns as $SalesReturn) {
                if (strpos($SalesReturn->NoPacks, ',') != false) {
                    $salesreturnPacks = $salesreturnPacks + array_sum(explode(',', $SalesReturn->NoPacks));
                } else {
                    $salesreturnPacks = $salesreturnPacks + (int) $SalesReturn->NoPacks;
                }
            }
            $objectArticle->OpeningStock = $articleOpeningStock;
            $objectArticle->totalInward = $inwardPacks + $importPacks + $salesreturnPacks;
            $objectArticle->totalPurchase = $inwardPacks;
            $objectArticle->TransportOutwardpacks = $importPacks;
            $objectArticle->totalOutward = $outwardPacks + $purchasereturnPacks;
            $objectArticle->totalSalesReturn = $salesreturnPacks;
            $objectArticle->totalPurchaseReturn = $purchasereturnPacks;
            $objectArticle->closing = ($objectArticle->OpeningStock + $objectArticle->totalInward) - ($objectArticle->totalOutward);
            // end range stock
            $ReceivedDate = DB::table('transportoutwardpacks')->select("transportoutwardpacks.*", DB::raw("DATE_FORMAT(CreatedDate, '%d-%m-%Y') as formatted_dob"))->where('ArticleId', $articleArray['ArticleId'])->first();
            $objectArticle->ReceivedDate = $ReceivedDate->formatted_dob;
        }
        return array("data" => array_values($articles), "startdate" => $startdate, "enddate" => $enddate);
    }
    public function outwardReport($startdate, $enddate)
    {
        $vnddataTotal = DB::select("SELECT count(*) as Total from (SELECT o.Id , DATE_FORMAT(o.created_at , '%Y-%m-%d') as CheckDate , concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber ,concat(IFNULL(spuser.Name,suser.Name),son.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber , c.Title as Category ,sc.Name as SubCategory ,rs.Series as Series ,a.ArticleNumber ,p.Name as PartyName,o.OutwardRate as Rate , own.Discount ,own.Remarks ,p.Country ,p.State,p.City , p.PinCode ,spuser.Name as SalesPerson FROM outward o inner join article a on a.Id=o.ArticleId inner join party p on p.Id=o.PartyId inner join category c on c.Id=a.CategoryId left join  subcategory sc on sc.Id=a.SubCategoryId left join rangeseries rs on rs.Id=a.SeriesId inner join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber son on son.Id=own.SoId left join party ps on ps.Id=son.PartyId left join users spuser on spuser.Id=ps.UserId inner join financialyear fn1 on fn1.Id=son.FinancialYearId left join users suser on suser.Id=son.UserId inner join financialyear fn on fn.Id=own.FinancialYearId) as d where d.CheckDate >= '" . $startdate . "' and  d.CheckDate <= '" . $enddate . "'");
        $vntotal = $vnddataTotal[0]->Total;
        $vnddata = DB::select("SELECT d.* from (SELECT o.Id ,DATE_FORMAT(o.created_at , '%Y-%m-%d') as CheckDate, DATE_FORMAT(own.OutwardDate , '%d-%m-%Y') as OutwardDate ,spuser.PartyId ,spuser.Name as OutletName ,  o.NoPacks , own.Id as OutwardNumberId , concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber ,concat(IFNULL(spuser.Name,suser.Name),son.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber , c.Title as Category ,sc.Name as SubCategory ,rs.Series as Series ,a.ArticleNumber ,p.Name as PartyName,o.OutwardRate as Rate , own.Discount ,own.Remarks ,p.Country ,p.State,p.City , p.PinCode ,spuser.Name as SalesPerson FROM outward o inner join article a on a.Id=o.ArticleId inner join party p on p.Id=o.PartyId inner join category c on c.Id=a.CategoryId left join  subcategory sc on sc.Id=a.SubCategoryId left join rangeseries rs on rs.Id=a.SeriesId inner join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber son on son.Id=own.SoId left join party ps on ps.Id=son.PartyId left join users spuser on spuser.Id=ps.UserId inner join financialyear fn1 on fn1.Id=son.FinancialYearId left join users suser on suser.Id=son.UserId inner join financialyear fn on fn.Id=own.FinancialYearId) as d where d.CheckDate >= '" . $startdate . "' and  d.CheckDate <= '" . $enddate . "' ");
        foreach ($vnddata as $vnd) {
            if (strpos($vnd->NoPacks, ',') !== false) {
                $totalpacks = array_sum(explode(',', $vnd->NoPacks));
                $vnd->Quantity = $totalpacks;
                $vnd->BillAmount = $totalpacks * (int) $vnd->Rate;
            } else {
                $vnd->Quantity = $vnd->NoPacks;
                $vnd->BillAmount = (int) $vnd->NoPacks * (int) $vnd->Rate;
            }
            if (!is_null($vnd->PartyId)) {
                if ($vnd->PartyId != 0) {
                    $vnd->Outlet = $vnd->OutletName;
                } else {
                    $vnd->Outlet = 'DIRECT';
                }
            } else {
                $vnd->Outlet = 'DIRECT';
            }
        }
        return array(
            'startdate' => $startdate,
            'enddate' => $enddate,
            'data' => $vnddata,
        );
    }
    public function outwardOutletReport($startdate, $enddate, $OutletPartyId)
    {
        $vnddatas = DB::select("select * from (select otn.PartyId, o.ArticleRate as Rate, DATE_FORMAT(otn.OutletDate,'%Y-%m-%d') as CreatedAt, DATE_FORMAT(otn.OutletDate,'%d-%m-%Y') as OutwardDate, a.ArticleNumber, c.Title as Category, o.NoPacks, concat(otn.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, spuser.Name as SalesPerson, p.Name as PartyName, sc.Name as SubCategory, oop.Name as Outlet, rs.SeriesName, otn.Discount, '' as Remarks, p.Country, p.State, p.City, p.PinCode from outlet o inner join outletnumber otn on otn.Id=o.OutletNumberId inner join article a on a.Id=o.ArticleId inner join category c on c.Id=a.CategoryId inner join financialyear fn on fn.Id=otn.FinancialYearId left join outwardnumber own on own.Id=otn.OutwardnumberId left join so so on so.Id=own.SoId left join sonumber son on son.Id=so.SoNumberId left join party op on op.Id=son.PartyId left join users spuser on spuser.Id=op.UserId left join party p on p.Id=otn.OutletPartyId left join party oop on oop.Id=otn.PartyId left join subcategory sc on sc.Id=a.SubCategoryId left join rangeseries rs on rs.Id=a.SeriesId) as dd where dd.CreatedAt>='" . $startdate . "' and dd.CreatedAt<='" . $enddate . "' and dd.PartyId='" . $OutletPartyId . "'");
        $collection = collect($vnddatas);
        $vnddata = $collection->unique()->values()->all();
        foreach ($vnddata as $vnd) {
            if (strpos($vnd->NoPacks, ',') !== false) {
                $totalpacks = array_sum(explode(',', $vnd->NoPacks));
                $vnd->Quantity = $totalpacks;
                $vnd->BillAmount = $totalpacks * (int) $vnd->Rate;
            } else {
                $vnd->Quantity = (int) $vnd->NoPacks;
                $vnd->BillAmount = (int) $vnd->NoPacks * (int) $vnd->Rate;
            }
        }
        return array(
            'response' => 'success',
            'search' => count($vnddata),
            'data' => $vnddata,
        );
    }
    public function GetDailyReports($RangeDate)
    {
        $categories = Category::get();
        $CatOpeningStock = [];
        $CatClosingStock = [];
        foreach ($categories as $category) {
            //start open stock
            $catOpenStock = 0;
            $openStock = 0;
            //start adition open stock
            $inwardOpenStock = 0;
            $salesReturnOpenStock = 0;
            $proOpenStock = 0;
            $outwardOpenStock = 0;
            $purchaseReturnOpenStock = 0;
            $conOpenStock = 0;
            $shortageOpenStock = 0;
            $openInwards = DB::select("select * from (select i.NoPacks, DATE_FORMAT(i.created_at,'%Y-%m-%d') as CreatedDate, DATE_FORMAT(i.created_at, '%h:%m:%S') as CreatedTime, a.CategoryId, a.ArticleStatus from inward i inner join article a on a.Id=i.ArticleId) as dd where dd.ArticleStatus != '3' and dd.CreatedDate < '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "'");
            $openSalesReturns = DB::select("select * from (select sr.NoPacks, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, a.CategoryId, a.ArticleStatus from salesreturn sr inner join article a on a.Id=sr.ArticleId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.ArticleStatus !='3' and dd.CategoryId='" . $category->Id . "'");
            $openPros = DB::select("select * from (select st.TransferNoPacks as NoPacks, DATE_FORMAT(st.created_at,'%Y-%m-%d') as CreatedDate, st.TransferArticleId as ArticleId, a.CategoryId, a.ArticleStatus from stocktransfer st inner join article a on a.Id=st.TransferArticleId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.ArticleStatus !='3' and dd.CategoryId='" . $category->Id . "'");
            foreach ($openInwards as $openInward) {
                if (strpos($openInward->NoPacks, ',') != false) {
                    $inwardOpenStock = $inwardOpenStock + array_sum(explode(',', $openInward->NoPacks));
                } else {
                    $inwardOpenStock = $inwardOpenStock + (int) $openInward->NoPacks;
                }
            }
            foreach ($openSalesReturns as $openSalesReturn) {
                if (strpos($openSalesReturn->NoPacks, ',') != false) {
                    $salesReturnOpenStock = $salesReturnOpenStock + array_sum(explode(',', $openSalesReturn->NoPacks));
                } else {
                    $salesReturnOpenStock = $salesReturnOpenStock + (int) $openSalesReturn->NoPacks;
                }
            }
            foreach ($openPros as $openPro) {
                if (strpos($openPro->NoPacks, ',') != false) {
                    $proOpenStock = $proOpenStock + array_sum(explode(',', $openPro->NoPacks));
                } else {
                    $proOpenStock = $proOpenStock + (int) $openPro->NoPacks;
                }
            }
            //end adition open stock
            //start subtraction open stock
            $openOutwards = DB::select("select * from (select o.NoPacks, DATE_FORMAT(o.created_at,'%Y-%m-%d') as CreatedDate, a.ArticleStatus, a.CategoryId from outward o inner join article a on a.Id=o.ArticleId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.ArticleStatus != '3' and dd.CategoryId='" . $category->Id . "'");
            $openPurchaseReturns = DB::select("select * from (select pr.ReturnNoPacks as NoPacks, DATE_FORMAT(pr.CreatedDate,'%Y-%m-%d') as CreatedDate, a.CategoryId, a.ArticleStatus from purchasereturn pr inner join article a on a.Id=pr.ArticleId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.ArticleStatus != '3' and dd.CategoryId='" . $category->Id . "'");
            $openCons = DB::select("select * from (select st.ConsumedNoPacks as NoPacks, DATE_FORMAT(st.created_at,'%Y-%m-%d') as CreatedDate, a.CategoryId, a.ArticleStatus from stocktransfer st inner join article a on a.Id=st.ConsumedArticleId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.ArticleStatus != '3' and dd.CategoryId='" . $category->Id . "'");
            $openShortages = DB::select("select * from (select ss.NoPacks, DATE_FORMAT(ss.created_at,'%Y-%m-%d') as CreatedDate, a.CategoryId, a.ArticleStatus from stockshortage ss inner join article a on a.Id=ss.ArticleId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.ArticleStatus != '3' and dd.CategoryId='" . $category->Id . "'");
            foreach ($openOutwards as $openOutward) {
                if (strpos($openOutward->NoPacks, ',') != false) {
                    $outwardOpenStock = $outwardOpenStock + array_sum(explode(',', $openOutward->NoPacks));
                } else {
                    $outwardOpenStock = $outwardOpenStock + (int) $openOutward->NoPacks;
                }
            }
            foreach ($openPurchaseReturns as $openPurchaseReturn) {
                if (strpos($openPurchaseReturn->NoPacks, ',') != false) {
                    $purchaseReturnOpenStock = $purchaseReturnOpenStock + array_sum(explode(',', $openPurchaseReturn->NoPacks));
                } else {
                    $purchaseReturnOpenStock = $purchaseReturnOpenStock + (int) $openPurchaseReturn->NoPacks;
                }
            }
            foreach ($openCons as $openCon) {
                if (strpos($openCon->NoPacks, ',') != false) {
                    $conOpenStock = $conOpenStock + array_sum(explode(',', $openCon->NoPacks));
                } else {
                    $conOpenStock = $conOpenStock + (int) $openCon->NoPacks;
                }
            }
            foreach ($openShortages as $openShortage) {
                if (strpos($openShortage->NoPacks, ',') != false) {
                    $shortageOpenStock = $shortageOpenStock + array_sum(explode(',', $openShortage->NoPacks));
                } else {
                    $shortageOpenStock = $shortageOpenStock + (int) $openShortage->NoPacks;
                }
            }
            //end subtraction open stock
            $catOpenStock = ($catOpenStock + $inwardOpenStock + $proOpenStock + $salesReturnOpenStock) - ($outwardOpenStock + $purchaseReturnOpenStock + $conOpenStock + $shortageOpenStock);
            array_push($CatOpeningStock, [$category->Title => $catOpenStock]);
            //end open stock

            //start close stock
            $catCloseStock = 0;
            $closeStock = 0;
            //start adition open stock
            $inwardCloseStock = 0;
            $salesReturnCloseStock = 0;
            $proCloseStock = 0;
            $outwardCloseStock = 0;
            $purchaseReturnCloseStock = 0;
            $conCloseStock = 0;
            $shortageCloseStock = 0;
            $closeInwards = DB::select("select * from (select i.NoPacks, DATE_FORMAT(i.created_at,'%Y-%m-%d') as CreatedDate, DATE_FORMAT(i.created_at, '%h:%m:%S') as CreatedTime, a.CategoryId, a.ArticleStatus from inward i inner join article a on a.Id=i.ArticleId) as dd where dd.ArticleStatus != '3' and dd.CreatedDate <= '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "'");
            $closeSalesReturns = DB::select("select * from (select sr.NoPacks, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, a.CategoryId, a.ArticleStatus from salesreturn sr inner join article a on a.Id=sr.ArticleId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.ArticleStatus !='3' and dd.CategoryId='" . $category->Id . "'");
            $closePros = DB::select("select * from (select st.TransferNoPacks as NoPacks, DATE_FORMAT(st.created_at,'%Y-%m-%d') as CreatedDate, st.TransferArticleId as ArticleId, a.CategoryId, a.ArticleStatus from stocktransfer st inner join article a on a.Id=st.TransferArticleId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.ArticleStatus !='3' and dd.CategoryId='" . $category->Id . "'");
            foreach ($closeInwards as $closeInward) {
                if (strpos($closeInward->NoPacks, ',') != false) {
                    $inwardCloseStock = $inwardCloseStock + array_sum(explode(',', $closeInward->NoPacks));
                } else {
                    $inwardCloseStock = $inwardCloseStock + (int) $closeInward->NoPacks;
                }
            }
            foreach ($closeSalesReturns as $closeSalesReturn) {
                if (strpos($closeSalesReturn->NoPacks, ',') != false) {
                    $salesReturnCloseStock = $salesReturnCloseStock + array_sum(explode(',', $closeSalesReturn->NoPacks));
                } else {
                    $salesReturnCloseStock = $salesReturnCloseStock + (int) $closeSalesReturn->NoPacks;
                }
            }
            foreach ($closePros as $closePro) {
                if (strpos($closePro->NoPacks, ',') != false) {
                    $proCloseStock = $proCloseStock + array_sum(explode(',', $closePro->NoPacks));
                } else {
                    $proCloseStock = $proCloseStock + (int) $closePro->NoPacks;
                }
            }
            //end adition close stock
            //start subtraction close stock
            $closeOutwards = DB::select("select * from (select o.NoPacks, DATE_FORMAT(o.created_at,'%Y-%m-%d') as CreatedDate, a.ArticleStatus, a.CategoryId from outward o inner join article a on a.Id=o.ArticleId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.ArticleStatus != '3' and dd.CategoryId='" . $category->Id . "'");
            $closePurchaseReturns = DB::select("select * from (select pr.ReturnNoPacks as NoPacks, DATE_FORMAT(pr.CreatedDate,'%Y-%m-%d') as CreatedDate, a.CategoryId, a.ArticleStatus from purchasereturn pr inner join article a on a.Id=pr.ArticleId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.ArticleStatus != '3' and dd.CategoryId='" . $category->Id . "'");
            $closeCons = DB::select("select * from (select st.ConsumedNoPacks as NoPacks, DATE_FORMAT(st.created_at,'%Y-%m-%d') as CreatedDate, a.CategoryId, a.ArticleStatus from stocktransfer st inner join article a on a.Id=st.ConsumedArticleId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.ArticleStatus != '3' and dd.CategoryId='" . $category->Id . "'");
            $closeShortages = DB::select("select * from (select ss.NoPacks, DATE_FORMAT(ss.created_at,'%Y-%m-%d') as CreatedDate, a.CategoryId, a.ArticleStatus from stockshortage ss inner join article a on a.Id=ss.ArticleId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.ArticleStatus != '3' and dd.CategoryId='" . $category->Id . "'");
            foreach ($closeOutwards as $closeOutward) {
                if (strpos($closeOutward->NoPacks, ',') != false) {
                    $outwardCloseStock = $outwardCloseStock + array_sum(explode(',', $closeOutward->NoPacks));
                } else {
                    $outwardCloseStock = $outwardCloseStock + (int) $closeOutward->NoPacks;
                }
            }
            foreach ($closePurchaseReturns as $closePurchaseReturn) {
                if (strpos($closePurchaseReturn->NoPacks, ',') != false) {
                    $purchaseReturnCloseStock = $purchaseReturnCloseStock + array_sum(explode(',', $closePurchaseReturn->NoPacks));
                } else {
                    $purchaseReturnCloseStock = $purchaseReturnCloseStock + (int) $closePurchaseReturn->NoPacks;
                }
            }
            foreach ($closeCons as $closeCon) {
                if (strpos($closeCon->NoPacks, ',') != false) {
                    $conCloseStock = $conCloseStock + array_sum(explode(',', $closeCon->NoPacks));
                } else {
                    $conCloseStock = $conCloseStock + (int) $closeCon->NoPacks;
                }
            }
            foreach ($closeShortages as $closeShortage) {
                if (strpos($closeShortage->NoPacks, ',') != false) {
                    $shortageCloseStock = $shortageCloseStock + array_sum(explode(',', $closeShortage->NoPacks));
                } else {
                    $shortageCloseStock = $shortageCloseStock + (int) $closeShortage->NoPacks;
                }
            }
            //end subtraction close stock
            $catCloseStock = ($inwardCloseStock + $proCloseStock + $salesReturnCloseStock) - ($outwardCloseStock + $purchaseReturnCloseStock + $conCloseStock + $shortageCloseStock);
            array_push($CatClosingStock, [$category->Title => $catCloseStock]);
            //end close stock
        }
        $catCount = Category::count();
        for ($i = 0; $i <= (int) $catCount - 1; $i++) {
            foreach ($CatOpeningStock[$i] as $key => $value) {
                $openStock = $openStock + $value;
            }
        }
        for ($i = 0; $i <= (int) $catCount - 1; $i++) {
            foreach ($CatClosingStock[$i] as $key => $value) {
                $closeStock = $closeStock + $value;
            }
        }


        //start daily stock
        $maininwardRecordss = DB::select("select * from (select ig.Id as GRNId, concat(ig.GRN,'/', fn.StartYear,'-',fn.EndYear) as GRNnumber, DATE_FORMAT(i.created_at ,'%Y-%m-%d') as CreatedAt, DATE_FORMAT(i.created_at ,'%d-%m-%Y') as CreatedDate from inwardgrn ig inner join inward i on ig.Id=i.GRN inner join financialyear fn on fn.Id=ig.FinancialYearId) as dd where dd.CreatedAt = '" . $RangeDate . "'");
        $collectioncateInwa = collect($maininwardRecordss);
        $maininwardRecords = $collectioncateInwa->unique()->values()->all();
        $maintotalInwardSalesNoPacks = 0;
        // foreach ($maininwardRecords as $maininwardRecord) {
        foreach ($maininwardRecords as $key => $maininwardRecord) {
            $inwards = DB::select("select i.NoPacks, v.Name from inward i inner join po on po.ArticleId=i.ArticleId inner join vendor v on v.Id=po.VendorId where i.GRN='" . $maininwardRecord->GRNId . "' and i.InwardDate = '" . $RangeDate . "'");
            $grnPacks = 0;
            $inwardVendor = Inward::select('vendor.Name')->join('po', 'po.ArticleId', 'inward.ArticleId')->join('vendor', 'vendor.Id', 'po.VendorId')->where('inward.GRN', $maininwardRecord->GRNId)->where('inward.InwardDate', $RangeDate)->first();
            $grnPacks = 0;
            if (isset($inwardVendor)) {
                $name = $inwardVendor->Name;
            } else {
                $name = "";
            }
            foreach ($inwards as $inward) {
                if (strpos($inward->NoPacks, ',') != false) {
                    $grnPacks = $grnPacks + array_sum(explode(',', $inward->NoPacks));
                } else {
                    $grnPacks = $grnPacks + (int) $inward->NoPacks;
                }
            }
            $maintotalInwardSalesNoPacks = $maintotalInwardSalesNoPacks + $grnPacks;
            $maininwardRecord->SalesNoPacks = $grnPacks;
            $maininwardRecord->Name = $name;
            if ($maininwardRecord->SalesNoPacks == 0) {
                unset($maininwardRecords[$key]);
            }
        }
        $mainpurchaseReturnRecords = DB::select("select * from (select prn.Id as PrnId , concat(prn.PurchaseReturnNumber,'/', fn.StartYear, '-', fn.EndYear) as PRNumber, DATE_FORMAT(prn.CreatedDate ,'%Y-%m-%d') as CreatedAt, DATE_FORMAT(prn.CreatedDate ,'%d-%m-%Y') as CreatedDate, v.Name as Vendor_Name from purchasereturnnumber prn left join vendor v on prn.VendorId=v.Id inner join financialyear fn on fn.Id=prn.FinancialYearId) as sart where sart.CreatedAt='" . $RangeDate . "'");
        $maintotalPurchaseReturnNoPacks = 0;
        // foreach ($mainpurchaseReturnRecords as $mainpurchaseReturnRecord) {
        foreach ($mainpurchaseReturnRecords as $key => $mainpurchaseReturnRecord) {

            $purchaseReturns = DB::select("select ReturnNoPacks from purchasereturn where PurchaseReturnNumber=" . $mainpurchaseReturnRecord->PrnId);
            $prPacks = 0;
            foreach ($purchaseReturns as $purchaseReturn) {
                if (strpos($purchaseReturn->ReturnNoPacks, ',') != false) {
                    $prPacks = $prPacks + array_sum(explode(',', $purchaseReturn->ReturnNoPacks));
                } else {
                    $prPacks = $prPacks + (int) $purchaseReturn->ReturnNoPacks;
                }
            }
            $maintotalPurchaseReturnNoPacks = $maintotalPurchaseReturnNoPacks + $prPacks;
            $mainpurchaseReturnRecord->ReturnNoPacks = $prPacks;
            if ($mainpurchaseReturnRecord->ReturnNoPacks == 0) {
                unset($mainpurchaseReturnRecords[$key]);
            }
        }
        $mainoutwardRecords = DB::select("select * from (select onn.Id as OutwardNumberId , concat(onn.OutwardNumber,'/', fn.StartYear, '-', fn.EndYear) as OutwardNumber, concat(son.SoNumber,'/',fn.StartYear,'-',fn.EndYear) as SoNumber, DATE_FORMAT(onn.OutwardDate, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(onn.OutwardDate, '%d-%m-%Y') as CreatedDate, p.Name as Party_Name, pp.Name as Outlet, pp.OutletAssign from outwardnumber onn inner join sonumber son on son.Id=onn.SoId inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=onn.FinancialYearId left join users usr on usr.Id=p.UserId left join party pp on pp.Id=usr.PartyId) as o where o.CreatedAt='" . $RangeDate . "' order by o.OutwardNumberId ");
        $maintotalOutwardNoPacks = 0;
        // foreach ($mainoutwardRecords as $mainoutwardRecord) {
        foreach ($mainoutwardRecords as $key => $mainoutwardRecord) {
            if ($mainoutwardRecord->OutletAssign = 1) {
                $mainoutwardRecord->Outlet = $mainoutwardRecord->Outlet;
            } else {
                $mainoutwardRecord->Outlet = 'DIRECT';
            }
            $outwards = DB::select("select NoPacks from outward where OutwardNumberId=" . $mainoutwardRecord->OutwardNumberId);
            $outwardPacks = 0;
            foreach ($outwards as $outward) {
                if (strpos($outward->NoPacks, ',') != false) {
                    $outwardPacks = $outwardPacks + array_sum(explode(',', $outward->NoPacks));
                } else {
                    $outwardPacks = $outwardPacks + (int) $outward->NoPacks;
                }
            }
            $maintotalOutwardNoPacks = $maintotalOutwardNoPacks + $outwardPacks;
            $mainoutwardRecord->NoPacks = $outwardPacks;
            if ($mainoutwardRecord->NoPacks == 0) {
                unset($mainoutwardRecords[$key]);
            }
        }
        $mainsalesReturnRecords = DB::select("select * from (select srn.Id as SalesReturnNumberId ,concat(srn.SalesReturnNumber,'/', fn.StartYear, '-', fn.EndYear) as SRNumber, DATE_FORMAT(srn.CreatedDate ,'%Y-%m-%d') as CreatedAt , DATE_FORMAT(srn.CreatedDate ,'%d/%m%/%Y') as CreatedDate, p.Name as PartyName from salesreturnnumber srn inner join party p on srn.PartyId=p.Id inner join financialyear fn on fn.Id=srn.FinancialYearId) as dd where dd.CreatedAt ='" . $RangeDate . "'");
        $maintotalSalesReturnNoPacks = 0;
        // foreach ($mainsalesReturnRecords as $mainsalesReturnRecord) {
        foreach ($mainsalesReturnRecords as $key => $mainsalesReturnRecord) {
            $salesReturns = DB::select("select NoPacks from salesreturn where SalesReturnNumber=" . $mainsalesReturnRecord->SalesReturnNumberId);
            $salesreturnPacks = 0;
            foreach ($salesReturns as $salesReturn) {
                if (strpos($salesReturn->NoPacks, ',') != false) {
                    $salesreturnPacks = $salesreturnPacks + array_sum(explode(',', $salesReturn->NoPacks));
                } else {
                    $salesreturnPacks = $salesreturnPacks + (int) $salesReturn->NoPacks;
                }
            }
            $maintotalSalesReturnNoPacks = $maintotalSalesReturnNoPacks + $salesreturnPacks;
            $mainsalesReturnRecord->NoPacks = $salesreturnPacks;
            if ($mainsalesReturnRecord->NoPacks == 0) {
                unset($mainsalesReturnRecords[$key]);
            }
        }
        //start daily cat wise stock
        $inwardCategoryWise = [];
        $totalInwardPacks = 0;
        $salesReturnCategoryWise = [];
        $totalSalesReturnNoPacks = 0;
        $outwardCategoryWise = [];
        $totaloutwardNoPacks = 0;
        $purchaseReturnCategoryWise = [];
        $totalReturnNoPacks = 0;
        foreach ($categories as $category) {
            $inwardRecords = DB::select("select * from (select c.Id as CatId ,i.NoPacks as SalesNoPacks, DATE_FORMAT(i.InwardDate ,'%Y-%m-%d') as CreatedAt from inwardgrn ig inner join inward i on ig.Id=i.GRN left join vendor v on v.Id=ig.VendorId inner join article a on a.Id=i.ArticleId inner join category c on c.Id=a.CategoryId inner join financialyear fn on fn.Id=ig.FinancialYearId) as dd where dd.CreatedAt ='" . $RangeDate . "' and dd.CatId=" . $category->Id);
            $InwardPacks = 0;
            foreach ($inwardRecords as $inwardRecord) {
                if (strpos($inwardRecord->SalesNoPacks, ',')) {
                    $InwardPacks = $InwardPacks + array_sum(explode(',', $inwardRecord->SalesNoPacks));
                } else {
                    $InwardPacks = $InwardPacks + (int) $inwardRecord->SalesNoPacks;
                }
            }
            if (strpos($InwardPacks, ',') != false) {
                $totalInwardPacks = $totalInwardPacks + array_sum(explode(',', $InwardPacks));
            } else {
                $totalInwardPacks = $totalInwardPacks + $InwardPacks;
            }
            array_push($inwardCategoryWise, [$category->Title => $InwardPacks]);
            $purchaseReturnRecords = DB::select("select * from (select DATE_FORMAT(pr.CreatedDate ,'%Y-%m-%d') as CreatedAt,c.Id as CatId, pr.ReturnNoPacks from purchasereturn pr inner join article a on a.Id=pr.ArticleId inner join category c on c.Id=a.CategoryId) as sart where sart.CreatedAt='" . $RangeDate . "' and sart.CatId='" . $category->Id . "'");
            $ReturnNoPacks = 0;
            foreach ($purchaseReturnRecords as $purchaseReturnRecord) {
                if (strpos($purchaseReturnRecord->ReturnNoPacks, ',') != false) {
                    $ReturnNoPacks = $ReturnNoPacks + array_sum(explode(',', $purchaseReturnRecord->ReturnNoPacks));
                } else {
                    $ReturnNoPacks = $ReturnNoPacks + $purchaseReturnRecord->ReturnNoPacks;
                }
            }
            if (strpos($ReturnNoPacks, ',') != false) {
                $totalReturnNoPacks = $totalReturnNoPacks + array_sum(explode(',', $ReturnNoPacks));
            } else {
                $totalReturnNoPacks = $totalReturnNoPacks + $ReturnNoPacks;
            }
            array_push($purchaseReturnCategoryWise, [$category->Title => $ReturnNoPacks]);
            $salesReturnRecords = DB::select("select * from (select DATE_FORMAT(sr.CreatedDate ,'%Y-%m-%d') as CreatedAt, sr.NoPacks, c.Id as CatId from salesreturn sr inner join article a on a.Id=sr.ArticleId inner join category c on c.Id=a.CategoryId) as dd where dd.CreatedAt ='" . $RangeDate . "' and dd.CatId='" . $category->Id . "'");
            $salesReturnNoPacks = 0;
            foreach ($salesReturnRecords as $salesReturnRecord) {
                if (strpos($salesReturnRecord->NoPacks, ',') != false) {
                    $salesReturnNoPacks = $salesReturnNoPacks + array_sum(explode(',', $salesReturnRecord->NoPacks));
                } else {
                    $salesReturnNoPacks = $salesReturnNoPacks + $salesReturnRecord->NoPacks;
                }
            }
            if (strpos($salesReturnNoPacks, ',') != false) {
                $totalSalesReturnNoPacks = $totalSalesReturnNoPacks + array_sum(explode(',', $salesReturnNoPacks));
            } else {
                $totalSalesReturnNoPacks = $totalSalesReturnNoPacks + $salesReturnNoPacks;
            }
            array_push($salesReturnCategoryWise, [$category->Title => $salesReturnNoPacks]);
            $outwardRecords = DB::select("select *from (select DATE_FORMAT(onn.OutwardDate, '%Y-%m-%d') as CreatedAt, o.NoPacks, c.Id as CatId from outward o inner join outwardnumber onn on o.OutwardNumberId=onn.Id inner join article a on a.Id=o.ArticleId inner join category c on c.Id=a.CategoryId) as dd where dd.CreatedAt= '" . $RangeDate . "' and dd.CatId='" . $category->Id . "'");
            $outwardNoPacks = 0;
            foreach ($outwardRecords as $outwardRecord) {
                if (strpos($outwardRecord->NoPacks, ',') != false) {
                    $outwardNoPacks = $outwardNoPacks + array_sum(explode(',', $outwardRecord->NoPacks));
                } else {
                    $outwardNoPacks = $outwardNoPacks + $outwardRecord->NoPacks;
                }
            }
            if (strpos($outwardNoPacks, ',') != false) {
                $totaloutwardNoPacks = $totaloutwardNoPacks + array_sum(explode(',', $outwardNoPacks));
            } else {
                $totaloutwardNoPacks = $totaloutwardNoPacks + $outwardNoPacks;
            }
            array_push($outwardCategoryWise, [$category->Title => $outwardNoPacks]);
        }
        //end daily cat wise stock
        //end daily stock
        return [
            'OpeningStock' => $openStock,
            'ClosingStock' => $closeStock,
            'openingCatWise' => $CatOpeningStock,
            'closingCatWise' => $CatClosingStock,
            'Categories' => $categories,
            'AllData' => ['inwardRecords' => array_values($maininwardRecords), 'totalInwardSalesNoPacks' => $maintotalInwardSalesNoPacks, 'outwardRecords' => array_values($mainoutwardRecords), 'totalOutwardNoPacks' => $maintotalOutwardNoPacks, 'purchaseReturnRecords' => array_values($mainpurchaseReturnRecords), 'totalPurchaseReturnNoPacks' => $maintotalPurchaseReturnNoPacks, 'salesReturnRecords' => array_values($mainsalesReturnRecords), 'totalSalesReturnNoPacks' => $maintotalSalesReturnNoPacks],
            'AllDataCat' => ['inwardCategoryWise' => $inwardCategoryWise, 'outwardCategoryWise' => $outwardCategoryWise, 'purchaseReturnCategoryWise' => $purchaseReturnCategoryWise, 'salesReturnCategoryWise' => $salesReturnCategoryWise]
        ];
    }
    public function GetOutletDailyReports($RangeDate, $PartyId)
    {

        $orgOutlet = Party::select('Name')->where('Id', $PartyId)->first();

        $orgOutletName = $orgOutlet->Name;

        // opening stock

        $beforeInwards = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, a.CategoryId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId inner join outward o on o.Id=trop.OutwardId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");

        $beforeImports = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, a.CategoryId, trop.OutwardId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId) as dd where dd.OutwardId=0 and dd.CreatedDate < '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");

        $beforeOutwards = DB::select("select * from (select o.NoPacks, o.ArticleId, otn.PartyId, a.CategoryId, DATE_FORMAT(o.created_at,'%Y-%m-%d') as OutletDate from outlet o inner join article a on a.Id=o.ArticleId inner join outletnumber otn on otn.Id=o.OutletNumberId) as dd where dd.OutletDate < '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");

        $beforePurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.CreatedDate > '2021-12-31' and dd.PartyId='" . $PartyId . "'");

        $beforeSalesReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, sr.OutletPartyId as PartyId from outletsalesreturn sr inner join article a on a.Id=sr.ArticleId inner join outletsalesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");

        $openingStock = 0;

        $totalInwardPacks = 0;

        $totalImportPacks = 0;

        $totalOutwardPacks = 0;

        $totalSalesReturnPacks = 0;

        $totalPurchaseReturnPacks = 0;

        foreach ($beforeInwards as $beforeInward) {

            if (strpos($beforeInward->NoPacks, ',') != false) {

                $totalInwardPacks = $totalInwardPacks + array_sum(explode(',', $beforeInward->NoPacks));

            } else {

                $totalInwardPacks = $totalInwardPacks + (int) $beforeInward->NoPacks;

            }

        }

        foreach ($beforeImports as $beforeImport) {

            if (strpos($beforeImport->NoPacks, ',') != false) {

                $totalImportPacks = $totalImportPacks + array_sum(explode(',', $beforeImport->NoPacks));

            } else {

                $totalImportPacks = $totalImportPacks + (int) $beforeImport->NoPacks;

            }

        }

        foreach ($beforeOutwards as $beforeOutward) {

            if (strpos($beforeOutward->NoPacks, ',') != false) {

                $totalOutwardPacks = $totalOutwardPacks + array_sum(explode(',', $beforeOutward->NoPacks));

            } else {

                $totalOutwardPacks = $totalOutwardPacks + (int) $beforeOutward->NoPacks;

            }

        }

        foreach ($beforeSalesReturns as $beforeSalesReturn) {

            if (strpos($beforeSalesReturn->NoPacks, ',') != false) {

                $totalSalesReturnPacks = $totalSalesReturnPacks + array_sum(explode(',', $beforeSalesReturn->NoPacks));

            } else {

                $totalSalesReturnPacks = $totalSalesReturnPacks + (int) $beforeSalesReturn->NoPacks;

            }

        }

        foreach ($beforePurchaseReturns as $beforePurchaseReturn) {

            if (strpos($beforePurchaseReturn->NoPacks, ',') != false) {

                $totalPurchaseReturnPacks = $totalPurchaseReturnPacks + array_sum(explode(',', $beforePurchaseReturn->NoPacks));

            } else {

                $totalPurchaseReturnPacks = $totalPurchaseReturnPacks + (int) $beforePurchaseReturn->NoPacks;

            }

        }

        $openingStock = $openingStock + $totalInwardPacks + $totalImportPacks + $totalSalesReturnPacks - $totalOutwardPacks - $totalPurchaseReturnPacks;

        // end opening stock

        // category wise opening stock

        $categories = Category::get();

        $CatOpeningStock = [];

        foreach ($categories as $category) {

            $beforeInwardss = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, a.CategoryId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId inner join outward o on o.Id=trop.OutwardId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "' and dd.CategoryId='" . $category->Id . "'");

            $beforeImports = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, a.CategoryId, trop.OutwardId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId) as dd where dd.OutwardId=0 and dd.CreatedDate < '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "' and dd.PartyId='" . $PartyId . "'");

            $beforeOutwards = DB::select("select * from (select o.NoPacks, o.ArticleId, otn.PartyId, a.CategoryId, DATE_FORMAT(o.created_at,'%Y-%m-%d') as OutletDate from outlet o inner join article a on a.Id=o.ArticleId inner join outletnumber otn on otn.Id=o.OutletNumberId) as dd where dd.OutletDate < '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "' and dd.PartyId='" . $PartyId . "'");

            $beforePurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber inner join article a on a.Id=sr.ArticleId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.CreatedDate > '2021-12-31' and dd.PartyId='" . $PartyId . "' and dd.CategoryId='" . $category->Id . "'");

            $beforeSalesReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, sr.OutletPartyId as PartyId from outletsalesreturn sr inner join article a on a.Id=sr.ArticleId inner join outletsalesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "' and dd.PartyId='" . $PartyId . "'");

            $innwardOpeningStock = 0;

            $totalInnwardOpeningStock = 0;

            $importOpeningStock = 0;

            $outwardOpeningStock = 0;

            $purchaseReturnOpeningStock = 0;

            $salesReturnOpeningStock = 0;

            foreach ($beforeInwardss as $CatWisebeforeInward) {

                if (strpos($CatWisebeforeInward->NoPacks, ',') != false) {

                    $innwardOpeningStock = $innwardOpeningStock + array_sum(explode(',', $CatWisebeforeInward->NoPacks));

                } else {

                    $innwardOpeningStock = $innwardOpeningStock + (int) $CatWisebeforeInward->NoPacks;

                }

            }

            if (strpos($innwardOpeningStock, ',') != false) {

                $totalInnwardOpeningStock = $totalInnwardOpeningStock + array_sum(explode(',', $innwardOpeningStock));

            } else {

                $totalInnwardOpeningStock = $totalInnwardOpeningStock + $innwardOpeningStock;

            }

            foreach ($beforeImports as $beforeImport) {

                if (strpos($beforeImport->NoPacks, ',') != false) {

                    $importOpeningStock = $importOpeningStock + array_sum(explode(',', $beforeImport->NoPacks));

                } else {

                    $importOpeningStock = $importOpeningStock + (int) $beforeImport->NoPacks;

                }

            }

            foreach ($beforeOutwards as $beforeOutward) {

                if (strpos($beforeOutward->NoPacks, ',') != false) {

                    $outwardOpeningStock = $outwardOpeningStock + array_sum(explode(',', $beforeOutward->NoPacks));

                } else {

                    $outwardOpeningStock = $outwardOpeningStock + (int) $beforeOutward->NoPacks;

                }

            }

            foreach ($beforePurchaseReturns as $beforePurchaseReturn) {

                if (strpos($beforePurchaseReturn->NoPacks, ',') != false) {

                    $purchaseReturnOpeningStock = $purchaseReturnOpeningStock + array_sum(explode(',', $beforePurchaseReturn->NoPacks));

                } else {

                    $purchaseReturnOpeningStock = $purchaseReturnOpeningStock + (int) $beforePurchaseReturn->NoPacks;

                }

            }

            foreach ($beforeSalesReturns as $beforeSalesReturn) {

                if (strpos($beforeSalesReturn->NoPacks, ',') != false) {

                    $salesReturnOpeningStock = $salesReturnOpeningStock + array_sum(explode(',', $beforeSalesReturn->NoPacks));

                } else {

                    $salesReturnOpeningStock = $salesReturnOpeningStock + (int) $beforeSalesReturn->NoPacks;

                }

            }

            $mainOpening = 0;

            $mainOpening = $mainOpening + $innwardOpeningStock + $importOpeningStock + $salesReturnOpeningStock - $outwardOpeningStock - $purchaseReturnOpeningStock;

            array_push($CatOpeningStock, [$category->Title => $mainOpening]);

        }

        // end category wise opening stock 

        // start one day record cat wise

        $TotalarticleOpeningStock = 0;

        $categories = DB::select("select * from category");

        $outletInwardCategoryWise = [];

        $outletImportCategoryWise = [];

        $outletOutwardCategoryWise = [];

        $outletPurchaseReturnCategoryWise = [];

        $outletSalesReturnCategoryWise = [];

        $totalCatInwardPacks = 0;

        $totalCatImportPacks = 0;

        $totalCatOutwardPacks = 0;

        $totalCatJhcplOutwardPacks = 0;

        $totalCatPRPacks = 0;

        $totalCatSRPacks = 0;

        foreach ($categories as $category) {

            // JHCPL Outward

            $jhcplOutwards = DB::select("select * from (select p.Name as Party, 'DIRECT' as Outlet, o.NoPacks, a.CategoryId, otn.Id as OutletNumberId, otn.OutwardNumberId, otn.PartyId, concat(otn.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber, DATE_FORMAT(otn.OutletDate, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(otn.OutletDate, '%d-%m-%Y') as OutletDate from outward o inner join outletnumber otn on o.OutwardNumberId=otn.OutwardNumberId inner join party p on p.Id=otn.OutletPartyId inner join financialyear fn on fn.Id=otn.FinancialYearId inner join article a on a.Id=o.ArticleId) as dd where dd.PartyId='" . $PartyId . "' and dd.CreatedAt='" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "'");

            $outwardJhcplPacks = 0;

            foreach ($jhcplOutwards as $jhcplOutward) {

                if (strpos($jhcplOutward->NoPacks, ',') != false) {

                    $outwardJhcplPacks = $outwardJhcplPacks + array_sum(explode(',', $jhcplOutward->NoPacks));

                } else {

                    $outwardJhcplPacks = $outwardJhcplPacks + (int) $jhcplOutward->NoPacks;

                }

            }

            if (strpos($outwardJhcplPacks, ',') != false) {

                $totalCatJhcplOutwardPacks = $totalCatJhcplOutwardPacks + array_sum(explode(',', $outwardJhcplPacks));

            } else {

                $totalCatJhcplOutwardPacks = $totalCatJhcplOutwardPacks + $outwardJhcplPacks;

            }

            // end

            $catInwardRec = DB::select("select * from (select c.Id as CategoryId, trop.NoPacks, trop.PartyId, DATE_FORMAT(trop.CreatedDate, '%Y-%m-%d') as ReceivedAt, DATE_FORMAT(trop.CreatedDate, '%d-%m-%Y') as ReceivedDate from outwardnumber own inner join outward o on o.OutwardNumberId=own.Id inner join transportoutwardpacks trop on trop.OutwardId=o.Id left join article a on a.Id=trop.ArticleId inner join category c on c.Id=a.CategoryId) as dd where dd.ReceivedAt='" . $RangeDate . "' and dd.PartyId='" . $PartyId . "' and dd.CategoryId='" . $category->Id . "' ");

            $catInwardPacks = 0;

            foreach ($catInwardRec as $inwardRecord) {

                if (strpos($inwardRecord->NoPacks, ',')) {

                    $catInwardPacks = $catInwardPacks + array_sum(explode(',', $inwardRecord->NoPacks));

                } else {

                    $catInwardPacks = $catInwardPacks + (int) $inwardRecord->NoPacks;

                }

            }

            if (strpos($catInwardPacks, ',') != false) {

                $totalCatInwardPacks = $totalCatInwardPacks + array_sum(explode(',', $catInwardPacks));

            } else {

                $totalCatInwardPacks = $totalCatInwardPacks + $catInwardPacks;

            }

            array_push($outletInwardCategoryWise, [$category->Title => $catInwardPacks + $outwardJhcplPacks]);

            $catImportRec = DB::select("select * from (select trop.NoPacks, trop.Id, c.Id as CategoryId, trop.ArticleId, trop.PartyId, trop.OutwardId, DATE_FORMAT(trop.CreatedDate, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(trop.CreatedDate, '%d-%m-%Y') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId inner join category c on c.Id=a.CategoryId) as dd where dd.CreatedAt='" . $RangeDate . "' and dd.PartyId='" . $PartyId . "' and dd.OutwardId=0 and dd.CategoryId='" . $category->Id . "' ");

            $collectioncateImports = collect($catImportRec);

            $catImportRecords = $collectioncateImports->unique()->values()->all();

            $catImportPacks = 0;

            foreach ($catImportRecords as $catImportRecord) {

                if (strpos($catImportRecord->NoPacks, ',')) {

                    $catImportPacks = $catImportPacks + array_sum(explode(',', $catImportRecord->NoPacks));

                } else {

                    $catImportPacks = $catImportPacks + (int) $catImportRecord->NoPacks;

                }

            }

            if (strpos($catImportPacks, ',') != false) {

                $totalCatImportPacks = $totalCatImportPacks + array_sum(explode(',', $catImportPacks));

            } else {

                $totalCatImportPacks = $totalCatImportPacks + $catImportPacks;

            }

            array_push($outletImportCategoryWise, [$category->Title => $catImportPacks]);





            $catOutwardRec = DB::select("select dd.* from (select otn.Id as OutletNumberId, ot.NoPacks, c.Id as CategoryId, otn.PartyId, concat(otn.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber, DATE_FORMAT(ot.created_at, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(ot.created_at, '%d-%m-%Y') as OutletDate from outlet ot inner join outletnumber otn on otn.Id=ot.OutletNumberId inner join article a on a.Id=ot.ArticleId inner join category c on c.Id=a.CategoryId inner join financialyear fn on fn.Id=otn.FinancialYearId) as dd where dd.PartyId='" . $PartyId . "' and dd.CreatedAt='" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "' ");

            $catOutwardPacks = 0;

            foreach ($catOutwardRec as $catOutwardRecord) {

                if (strpos($catOutwardRecord->NoPacks, ',')) {

                    $catOutwardPacks = $catOutwardPacks + array_sum(explode(',', $catOutwardRecord->NoPacks));

                } else {

                    $catOutwardPacks = $catOutwardPacks + (int) $catOutwardRecord->NoPacks;

                }

            }

            if (strpos($catOutwardPacks, ',') != false) {

                $totalCatOutwardPacks = $totalCatOutwardPacks + array_sum(explode(',', $catOutwardPacks));

            } else {

                $totalCatOutwardPacks = $totalCatOutwardPacks + $catOutwardPacks;

            }

            array_push($outletOutwardCategoryWise, [$category->Title => $catOutwardPacks + $outwardJhcplPacks]);

            $catpurchasereturnRecordsss = DB::select("select * from (select srn.Id as SalesReturnNumberId, sr.NoPacks, c.Id as CategoryId, p.Name as PartyName, srn.PartyId, DATE_FORMAT(srn.CreatedDate, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(sr.CreatedDate, '%d-%m-%Y') as CreatedDate from salesreturnnumber srn inner join salesreturn sr on sr.SalesReturnNumber=srn.Id inner join article a on a.Id=sr.ArticleId inner join category c on c.Id=a.CategoryId inner join party p on p.Id=srn.PartyId inner join financialyear fn on fn.Id=srn.FinancialYearId) as dd where dd.CreatedAt='" . $RangeDate . "' and dd.PartyId='" . $PartyId . "' and dd.CategoryId='" . $category->Id . "' ");

            $catPurchaseReturnNoPacks = 0;

            $carpurchasereturnRecordsssssss = collect($catpurchasereturnRecordsss);

            $catPurchaseReturnRecords = $carpurchasereturnRecordsssssss->unique()->values()->all();

            foreach ($catPurchaseReturnRecords as $catPurchaseReturnRecord) {

                if (strpos($catPurchaseReturnRecord->NoPacks, ',')) {

                    $catPurchaseReturnNoPacks = $catPurchaseReturnNoPacks + array_sum(explode(',', $catPurchaseReturnRecord->NoPacks));

                } else {

                    $catPurchaseReturnNoPacks = $catPurchaseReturnNoPacks + (int) $catPurchaseReturnRecord->NoPacks;

                }

            }

            if (strpos($catPurchaseReturnNoPacks, ',') != false) {

                $totalCatPRPacks = $totalCatPRPacks + array_sum(explode(',', $catPurchaseReturnNoPacks));

            } else {

                $totalCatPRPacks = $totalCatPRPacks + $catPurchaseReturnNoPacks;

            }

            array_push($outletPurchaseReturnCategoryWise, [$category->Title => $catPurchaseReturnNoPacks]);



            $catSalesreturnRecords = DB::select("select * from (select srn.Id as SalesReturnNumberId, c.Id as CategoryId, sr.NoPacks, concat(srn.SalesReturnNumber,'/', fn.StartYear, '-', fn.EndYear) as SalesReturnNumber, srn.OutletPartyId, DATE_FORMAT(srn.CreatedDate ,'%Y-%m-%d') as CreatedAt , DATE_FORMAT(srn.CreatedDate ,'%d/%m%/%Y') as CreatedDate, p.Name as PartyName from outletsalesreturnnumber srn inner join outletsalesreturn sr on sr.SalesReturnNumber=srn.Id inner join article a on a.Id=sr.ArticleId inner join category c on c.Id=a.CategoryId inner join party p on srn.PartyId=p.Id inner join financialyear fn on fn.Id=srn.FinancialYearId) as dd where dd.CreatedAt ='" . $RangeDate . "' and dd.OutletPartyId='" . $PartyId . "' and dd.CategoryId='" . $category->Id . "' ");

            $catSalesReturnNoPacks = 0;

            foreach ($catSalesreturnRecords as $catSalesreturnRecord) {

                if (strpos($catSalesreturnRecord->NoPacks, ',')) {

                    $catSalesReturnNoPacks = $catSalesReturnNoPacks + array_sum(explode(',', $catSalesreturnRecord->NoPacks));

                } else {

                    $catSalesReturnNoPacks = $catSalesReturnNoPacks + (int) $catSalesreturnRecord->NoPacks;

                }

            }

            if (strpos($catSalesReturnNoPacks, ',') != false) {

                $totalCatSRPacks = $totalCatSRPacks + array_sum(explode(',', $catSalesReturnNoPacks));

            } else {

                $totalCatSRPacks = $totalCatSRPacks + $catSalesReturnNoPacks;

            }

            array_push($outletSalesReturnCategoryWise, [$category->Title => $catSalesReturnNoPacks]);

        }

        // end one day record cat wise

        // start one day stock

        // JHCPL Outward

        $jhcplOutwards = DB::select("select * from (select p.Name as Party, 'DIRECT' as Outlet, otn.Id as OutletNumberId, otn.OutwardNumberId, otn.PartyId, concat(otn.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, DATE_FORMAT(otn.OutletDate, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(otn.OutletDate, '%d-%m-%Y') as OutletDate from outletnumber otn inner join outward o on o.OutwardNumberId=otn.OutwardNumberId inner join outwardnumber own on own.Id=o.OutwardNumberId inner join party p on p.Id=otn.OutletPartyId inner join financialyear fn on fn.Id=otn.FinancialYearId) as dd where dd.PartyId='" . $PartyId . "' and dd.CreatedAt='" . $RangeDate . "'");

        $collectionJscplOutwards = collect($jhcplOutwards);

        $mainJscplOutwardRecords = $collectionJscplOutwards->unique()->values()->all();

        $mainTotalJscplOutwardNoPacks = 0;

        foreach ($mainJscplOutwardRecords as $mainJscplOutwardRecord) {

            $jhcploutwards = DB::select("select *  from (select o.NoPacks, DATE_FORMAT(o.created_at,'%Y-%m-%d') as CreatedAt, o.OutwardNumberId from outward o) as dd where dd.CreatedAt='" . $RangeDate . "' and dd.OutwardNumberId='" . $mainJscplOutwardRecord->OutwardNumberId . "'");

            $outwardJhcplPacks = 0;

            foreach ($jhcploutwards as $jhcploutward) {

                if (strpos($jhcploutward->NoPacks, ',') != false) {

                    $outwardJhcplPacks = $outwardJhcplPacks + array_sum(explode(',', $jhcploutward->NoPacks));

                } else {

                    $outwardJhcplPacks = $outwardJhcplPacks + (int) $jhcploutward->NoPacks;

                }

            }

            $mainTotalJscplOutwardNoPacks = $mainTotalJscplOutwardNoPacks + $outwardJhcplPacks;

            $mainJscplOutwardRecord->NoPacks = $outwardJhcplPacks;

        }

        // end

        $mainOutletInwardRecordss = DB::select("select dd.* from ( SELECT own.Id as OutwardNumberId, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, trop.PartyId, p.Name as Outlet, DATE_FORMAT(own.created_at ,'%Y-%m-%d') as CreatedAt, DATE_FORMAT(own.created_at,'%d-%m-%Y') as ReceivedDate from outwardnumber own inner join outward o on o.OutwardNumberId=own.Id inner join transportoutwardpacks trop on trop.OutwardId=o.Id inner join financialyear fn on fn.Id=own.FinancialYearId inner join party p on p.Id=trop.PartyId) as dd WHERE dd.CreatedAt='" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");

        $collectionInwards = collect($mainOutletInwardRecordss);

        $mainOutletInwardRecords = $collectionInwards->unique()->values()->all();

        $maintotalInwardNoPacks = 0;

        $maintotalInwardNoPackss = 0;

        foreach ($mainOutletInwardRecords as $mainOutletInwardRecord) {

            $outwards = DB::select("select NoPacks from outward where OutwardNumberId = " . $mainOutletInwardRecord->OutwardNumberId);

            $InPacks = 0;

            foreach ($outwards as $outward) {

                if (strpos($outward->NoPacks, ',') != false) {

                    $InPacks = $InPacks + array_sum(explode(',', $outward->NoPacks));

                } else {

                    $InPacks = $InPacks + (int) $outward->NoPacks;

                }

            }

            $maintotalInwardNoPackss = $maintotalInwardNoPackss + $InPacks;

            $mainOutletInwardRecord->NoPacks = $InPacks;

        }

        $maintotalInwardNoPacks = $maintotalInwardNoPacks + $maintotalInwardNoPackss + $mainTotalJscplOutwardNoPacks;

        $ImportRec = DB::select("select * from (select trop.Id, a.ArticleNumber, p.Name as Outlet, trop.ArticleId, trop.PartyId, trop.OutwardId, DATE_FORMAT(trop.CreatedDate, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(trop.CreatedDate, '%d-%m-%Y') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId inner join party p on p.Id=trop.PartyId) as dd where dd.CreatedAt='" . $RangeDate . "' and dd.PartyId='" . $PartyId . "' and dd.OutwardId=0");

        $collectionImports = collect($ImportRec);

        $mainImportRecords = $collectionImports->unique()->values()->all();

        $mainTotalImportNoPacks = 0;

        foreach ($mainImportRecords as $mainImportRecord) {

            $imports = DB::select("select NoPacks from transportoutwardpacks where Id=" . $mainImportRecord->Id);

            $importNoPacks = 0;

            foreach ($imports as $import) {

                if (strpos($import->NoPacks, ',') != false) {

                    $importNoPacks = $importNoPacks + array_sum(explode(',', $import->NoPacks));

                } else {

                    $importNoPacks = $importNoPacks + (int) $import->NoPacks;

                }

            }

            $mainTotalImportNoPacks = $mainTotalImportNoPacks + $importNoPacks;

            $mainImportRecord->NoPacks = $importNoPacks;

        }

        $OutwardRec = DB::select("select dd.* from (select p.Name as Outlet, pp.Name as Party, otn.Id as OutletNumberId, otn.PartyId, concat(otn.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber, DATE_FORMAT(ot.created_at, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(ot.created_at, '%d-%m-%Y') as OutletDate from outletnumber otn inner join outlet ot on ot.OutletNumberId=otn.Id inner join financialyear fn on fn.Id=otn.FinancialYearId inner join party p on p.Id=otn.PartyId inner join party pp on pp.Id=otn.OutletPartyId) as dd where dd.PartyId='" . $PartyId . "' and dd.CreatedAt='" . $RangeDate . "' ");

        $collectionOutwards = collect($OutwardRec);

        $mainOutwardRecords = $collectionOutwards->unique()->values()->all();

        $mainTotalOutwardNoPacks = 0;

        $mainTotalOutwardNoPackss = 0;

        foreach ($mainOutwardRecords as $mainOutwardRecord) {

            $outwards = DB::select("select *  from (select o.NoPacks, DATE_FORMAT(o.created_at,'%Y-%m-%d') as CreatedAt, o.OutletNumberId from outlet o) as dd where dd.CreatedAt='" . $RangeDate . "' and dd.OutletNumberId='" . $mainOutwardRecord->OutletNumberId . "'");

            $outwardPacks = 0;

            foreach ($outwards as $outward) {

                if (strpos($outward->NoPacks, ',') != false) {

                    $outwardPacks = $outwardPacks + array_sum(explode(',', $outward->NoPacks));

                } else {

                    $outwardPacks = $outwardPacks + (int) $outward->NoPacks;

                }

            }

            $mainTotalOutwardNoPackss = $mainTotalOutwardNoPackss + $outwardPacks;

            $mainOutwardRecord->NoPacks = $outwardPacks;

        }

        $mainTotalOutwardNoPacks = $mainTotalOutwardNoPackss + $mainTotalJscplOutwardNoPacks;

        $purchasereturnRecordsss = DB::select("select * from (select srn.Id as SalesReturnNumberId,p.Name as Outlet, srn.PartyId,concat(srn.SalesReturnNumber,'/', fn.StartYear, '-', fn.EndYear) as SalesReturnNumber, DATE_FORMAT(srn.CreatedDate, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(srn.CreatedDate, '%d-%m-%Y') as CreatedDate from salesreturnnumber srn inner join party p on p.Id=srn.PartyId inner join financialyear fn on fn.Id=srn.FinancialYearId) as dd where dd.CreatedAt='" . $RangeDate . "' and dd.PartyId='" . $PartyId . "' ");

        $mainTotalPurchaseReturnNoPacks = 0;

        $purchasereturnRecordsssssss = collect($purchasereturnRecordsss);

        $purchasereturnRecords = $purchasereturnRecordsssssss->unique()->values()->all();

        foreach ($purchasereturnRecords as $purchasereturnRecord) {

            $purchaseReturns = DB::select("select NoPacks from salesreturn where SalesReturnNumber=" . $purchasereturnRecord->SalesReturnNumberId);

            $purchasereturnPacks = 0;

            foreach ($purchaseReturns as $purchaseReturn) {

                if (strpos($purchaseReturn->NoPacks, ',') != false) {

                    $purchasereturnPacks = $purchasereturnPacks + array_sum(explode(',', $purchaseReturn->NoPacks));

                } else {

                    $purchasereturnPacks = $purchasereturnPacks + (int) $purchaseReturn->NoPacks;

                }

            }

            $mainTotalPurchaseReturnNoPacks = $mainTotalPurchaseReturnNoPacks + $purchasereturnPacks;

            $purchasereturnRecord->NoPacks = $purchasereturnPacks;

        }

        $salesReturnRecords = DB::select("select * from (select srn.Id as SalesReturnNumberId ,concat(srn.SalesReturnNumber,'/', fn.StartYear, '-', fn.EndYear) as SalesReturnNumber, srn.OutletPartyId, DATE_FORMAT(srn.CreatedDate ,'%Y-%m-%d') as CreatedAt , DATE_FORMAT(srn.CreatedDate ,'%d/%m%/%Y') as CreatedDate, p.Name as Outlet, pp.Name as Party from outletsalesreturnnumber srn inner join party p on srn.OutletPartyId=p.Id inner join party pp on pp.Id=srn.PartyId inner join financialyear fn on fn.Id=srn.FinancialYearId) as dd where dd.CreatedAt ='" . $RangeDate . "' and dd.OutletPartyId='" . $PartyId . "' ");

        $mainTotalSalesReturnNoPacks = 0;

        foreach ($salesReturnRecords as $salesReturnRecord) {

            $salesReturns = DB::select("select NoPacks from outletsalesreturn where SalesReturnNumber=" . $salesReturnRecord->SalesReturnNumberId);

            $salesreturnPacks = 0;

            foreach ($salesReturns as $salesReturn) {

                if (strpos($salesReturn->NoPacks, ',') != false) {

                    $salesreturnPacks = $salesreturnPacks + array_sum(explode(',', $salesReturn->NoPacks));

                } else {

                    $salesreturnPacks = $salesreturnPacks + (int) $salesReturn->NoPacks;

                }

            }

            $mainTotalSalesReturnNoPacks = $mainTotalSalesReturnNoPacks + $salesreturnPacks;

            $salesReturnRecord->NoPacks = $salesreturnPacks;

        }

        // end one day stock

        // start closing stock

        $afterInwards = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, a.CategoryId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId inner join outward o on o.Id=trop.OutwardId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");

        $afterImports = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, a.CategoryId, trop.OutwardId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId) as dd where dd.OutwardId=0 and dd.CreatedDate <= '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");

        $afterOutwards = DB::select("select * from (select o.NoPacks, o.ArticleId, otn.PartyId, a.CategoryId, DATE_FORMAT(o.created_at,'%Y-%m-%d') as OutletDate from outlet o inner join article a on a.Id=o.ArticleId inner join outletnumber otn on otn.Id=o.OutletNumberId) as dd where dd.OutletDate <= '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");

        //  if ($PartyId == 3) {

        //  $afterPurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join article a on a.Id=sr.ArticleId inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.CreatedDate >= '2022-01-01' and dd.PartyId='" . $PartyId . "'");

        //  } else {

        //      $afterPurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join article a on a.Id=sr.ArticleId inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");

        //  }

        $afterPurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.CreatedDate > '2021-12-31' and dd.PartyId='" . $PartyId . "' ");



        // $afterPurchaseReturns = DB::select("SELECT salesreturn.ArticleId, article.CategoryId, salesreturnnumber.PartyId, salesreturn.NoPacks, DATE_FORMAT(salesreturn.CreatedDate, '%Y-%m-%d') as CreatedDate

        //     FROM salesreturn inner join salesreturnnumber on salesreturnnumber.Id=salesreturn.SalesReturnNumber inner join article on article.Id=salesreturn.ArticleId

        //     WHERE EXISTS

        //       (SELECT *

        //        FROM   transportoutwardpacks

        //        WHERE  transportoutwardpacks.ArticleId = salesreturn.ArticleId and salesreturnnumber.PartyId='" . $PartyId . "' and CreatedDate <= '" . $RangeDate . "')");

        $afterSalesReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, sr.OutletPartyId as PartyId from outletsalesreturn sr inner join article a on a.Id=sr.ArticleId inner join outletsalesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");

        $closingStock = 0;

        $closingInwardPacks = 0;

        $closingImportPacks = 0;

        $closingOutwardPacks = 0;

        $closingSalesReturnPacks = 0;

        $closingPurchaseReturnPacks = 0;

        foreach ($afterInwards as $afterInward) {

            if (strpos($afterInward->NoPacks, ',') != false) {

                $closingInwardPacks = $closingInwardPacks + array_sum(explode(',', $afterInward->NoPacks));

            } else {

                $closingInwardPacks = $closingInwardPacks + (int) $afterInward->NoPacks;

            }

        }



        foreach ($afterImports as $afterImport) {

            if (strpos($afterImport->NoPacks, ',') != false) {

                $closingImportPacks = $closingImportPacks + array_sum(explode(',', $afterImport->NoPacks));

            } else {

                $closingImportPacks = $closingImportPacks + (int) $afterImport->NoPacks;

            }

        }

        foreach ($afterOutwards as $afterOutward) {

            if (strpos($afterOutward->NoPacks, ',') != false) {

                $closingOutwardPacks = $closingOutwardPacks + array_sum(explode(',', $afterOutward->NoPacks));

            } else {

                $closingOutwardPacks = $closingOutwardPacks + (int) $afterOutward->NoPacks;

            }

        }

        foreach ($afterSalesReturns as $afterSalesReturn) {

            if (strpos($afterSalesReturn->NoPacks, ',') != false) {

                $closingSalesReturnPacks = $closingSalesReturnPacks + array_sum(explode(',', $afterSalesReturn->NoPacks));

            } else {

                $closingSalesReturnPacks = $closingSalesReturnPacks + (int) $afterSalesReturn->NoPacks;

            }

        }

        foreach ($afterPurchaseReturns as $afterPurchaseReturn) {

            if (strpos($afterPurchaseReturn->NoPacks, ',') != false) {

                $closingPurchaseReturnPacks = $closingPurchaseReturnPacks + array_sum(explode(',', $afterPurchaseReturn->NoPacks));

            } else {

                $closingPurchaseReturnPacks = $closingPurchaseReturnPacks + (int) $afterPurchaseReturn->NoPacks;

            }

        }

        $closingStock = $closingStock + $closingInwardPacks + $closingImportPacks + $closingSalesReturnPacks - $closingOutwardPacks - $closingPurchaseReturnPacks;

        // end closing stock

        // category wise opening stock

        $categories = Category::get();

        $CatClosingStock = [];

        foreach ($categories as $category) {

            $afterInwardss = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, a.CategoryId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId inner join outward o on o.Id=trop.OutwardId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "' and dd.CategoryId='" . $category->Id . "'");

            $afterImports = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, a.CategoryId, trop.OutwardId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId) as dd where dd.OutwardId=0 and dd.CreatedDate <= '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "' and dd.PartyId='" . $PartyId . "'");

            $afterOutwards = DB::select("select * from (select o.NoPacks, o.ArticleId, otn.PartyId, a.CategoryId, DATE_FORMAT(o.created_at,'%Y-%m-%d') as OutletDate from outlet o inner join article a on a.Id=o.ArticleId inner join outletnumber otn on otn.Id=o.OutletNumberId) as dd where dd.OutletDate <= '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "' and dd.PartyId='" . $PartyId . "'");

            // if ($PartyId == 3) {

            // $afterPurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join article a on a.Id=sr.ArticleId inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.CreatedDate >= '2022-01-01' and dd.CategoryId='" . $category->Id . "' and dd.PartyId='" . $PartyId . "'");

            // } else {

            //     $afterPurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join article a on a.Id=sr.ArticleId inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "' and dd.PartyId='" . $PartyId . "'");

            // }

            $afterPurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber inner join article a on a.Id=sr.ArticleId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.CreatedDate > '2021-12-31' and dd.PartyId='" . $PartyId . "' and dd.CategoryId='" . $category->Id . "'");



            // $afterPurchaseReturns = DB::select("SELECT salesreturn.ArticleId, article.CategoryId, salesreturnnumber.PartyId, salesreturn.NoPacks, DATE_FORMAT(salesreturn.CreatedDate, '%Y-%m-%d') as CreatedDate

            // FROM salesreturn inner join salesreturnnumber on salesreturnnumber.Id=salesreturn.SalesReturnNumber inner join article on article.Id=salesreturn.ArticleId

            // WHERE EXISTS

            //   (SELECT *

            //    FROM   transportoutwardpacks

            //    WHERE  transportoutwardpacks.ArticleId = salesreturn.ArticleId and salesreturnnumber.PartyId='" . $PartyId . "' and CreatedDate <= '" . $RangeDate . "' and article.CategoryId='" . $category->Id . "')");

            $afterSalesReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, sr.OutletPartyId as PartyId from outletsalesreturn sr inner join article a on a.Id=sr.ArticleId inner join outletsalesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "' and dd.PartyId='" . $PartyId . "'");

            $innwardClosingStock = 0;

            $totalInnwardClosingStock = 0;

            $importClosingStock = 0;

            $outwardClosingStock = 0;

            $purchaseReturnClosingStock = 0;

            $salesReturnClosingStock = 0;

            foreach ($afterInwardss as $CatWiseafterInward) {

                if (strpos($CatWiseafterInward->NoPacks, ',') != false) {

                    $innwardClosingStock = $innwardClosingStock + array_sum(explode(',', $CatWiseafterInward->NoPacks));

                } else {

                    $innwardClosingStock = $innwardClosingStock + (int) $CatWiseafterInward->NoPacks;

                }

            }

            foreach ($afterImports as $afterImport) {

                if (strpos($afterImport->NoPacks, ',') != false) {

                    $importClosingStock = $importClosingStock + array_sum(explode(',', $afterImport->NoPacks));

                } else {

                    $importClosingStock = $importClosingStock + (int) $afterImport->NoPacks;

                }

            }

            foreach ($afterOutwards as $afterOutward) {

                if (strpos($afterOutward->NoPacks, ',') != false) {

                    $outwardClosingStock = $outwardClosingStock + array_sum(explode(',', $afterOutward->NoPacks));

                } else {

                    $outwardClosingStock = $outwardClosingStock + (int) $afterOutward->NoPacks;

                }

            }

            foreach ($afterPurchaseReturns as $afterPurchaseReturn) {

                if (strpos($afterPurchaseReturn->NoPacks, ',') != false) {

                    $purchaseReturnClosingStock = $purchaseReturnClosingStock + array_sum(explode(',', $afterPurchaseReturn->NoPacks));

                } else {

                    $purchaseReturnClosingStock = $purchaseReturnClosingStock + (int) $afterPurchaseReturn->NoPacks;

                }

            }

            foreach ($afterSalesReturns as $afterSalesReturn) {

                if (strpos($afterSalesReturn->NoPacks, ',') != false) {

                    $salesReturnClosingStock = $salesReturnClosingStock + array_sum(explode(',', $afterSalesReturn->NoPacks));

                } else {

                    $salesReturnClosingStock = $salesReturnClosingStock + (int) $afterSalesReturn->NoPacks;

                }

            }

            $mainClosing = 0;

            $mainClosing = $mainClosing + $innwardClosingStock + $importClosingStock + $salesReturnClosingStock - $outwardClosingStock - $purchaseReturnClosingStock;

            array_push($CatClosingStock, [$category->Title => $mainClosing]);

        }

        // end category wise opening stock 

        return [

            'orgOutletName' => $orgOutletName,

            'outletOpeningCategorywise' => $CatOpeningStock,
            'outletClosingCategorywise' => $CatClosingStock,

            'Categories' => $categories,

            'outletOpeningStock' => $openingStock,
            'outletClosingStock' => $closingStock,

            'allOutletData' => [

                'inwardData' => $mainOutletInwardRecords,
                'totalInwardPacks' => $maintotalInwardNoPacks,

                'importData' => $mainImportRecords,
                'totalImportPacks' => $mainTotalImportNoPacks,

                'outwardData' => $mainOutwardRecords,
                'totalOutwardPacks' => $mainTotalOutwardNoPacks,

                'jhsploutwardData' => $mainJscplOutwardRecords,

                'salesReturnData' => $salesReturnRecords,
                'totalSalesReturnPacks' => $mainTotalSalesReturnNoPacks,

                'purchaseReturnData' => $purchasereturnRecords,
                'totalPurchaseReturnPacks' => $mainTotalPurchaseReturnNoPacks

            ],

            'allOutletDataCat' => [

                'outletInwardCategoryWise' => $outletInwardCategoryWise,
                'totalCatInwardPacks' => $totalCatInwardPacks + $totalCatJhcplOutwardPacks,

                'outletImportCategoryWise' => $outletImportCategoryWise,
                'totalCatImportPacks' => $totalCatImportPacks,

                'outletOutwardCategoryWise' => $outletOutwardCategoryWise,
                'totalCatOutwardPacks' => $totalCatOutwardPacks + $totalCatJhcplOutwardPacks,

                'outletSalesReturnCategoryWise' => $outletSalesReturnCategoryWise,
                'totalCatSRPacks' => $totalCatSRPacks,

                'outletPurchaseReturnCategoryWise' => $outletPurchaseReturnCategoryWise,
                'totalCatPRPacks' => $totalCatPRPacks,

            ]

        ];

    }

    public function getsoutletreport($RangeDate, $PartyId)
    {

        if ($PartyId == '4') {
            // return 'DSFDSF';
            $categories = Category::get();
            $CatOpeningStock = [];
            $CatClosingStock = [];

            $CatOpeningStocks = [];
            $CatClosingStocks = [];
            foreach ($categories as $category) {
                //start open stock
                $catOpenStock = 0;
                $openStock = 0;
                //start adition open stock
                $inwardOpenStock = 0;
                $salesReturnOpenStock = 0;
                $proOpenStock = 0;
                $outwardOpenStock = 0;
                $purchaseReturnOpenStock = 0;
                $conOpenStock = 0;
                $shortageOpenStock = 0;
                $openInwards = DB::select("select * from (select i.NoPacks, DATE_FORMAT(i.created_at,'%Y-%m-%d') as CreatedDate, DATE_FORMAT(i.created_at, '%h:%m:%S') as CreatedTime, a.CategoryId, a.ArticleStatus from inward i inner join article a on a.Id=i.ArticleId) as dd where dd.ArticleStatus != '3' and dd.CreatedDate < '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "'");
                $openSalesReturns = DB::select("select * from (select sr.NoPacks, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, a.CategoryId, a.ArticleStatus from salesreturn sr inner join article a on a.Id=sr.ArticleId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.ArticleStatus !='3' and dd.CategoryId='" . $category->Id . "'");
                $openPros = DB::select("select * from (select st.TransferNoPacks as NoPacks, DATE_FORMAT(st.created_at,'%Y-%m-%d') as CreatedDate, st.TransferArticleId as ArticleId, a.CategoryId, a.ArticleStatus from stocktransfer st inner join article a on a.Id=st.TransferArticleId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.ArticleStatus !='3' and dd.CategoryId='" . $category->Id . "'");
                foreach ($openInwards as $openInward) {
                    if (strpos($openInward->NoPacks, ',') != false) {
                        $inwardOpenStock = $inwardOpenStock + array_sum(explode(',', $openInward->NoPacks));
                    } else {
                        $inwardOpenStock = $inwardOpenStock + (int) $openInward->NoPacks;
                    }
                }
                foreach ($openSalesReturns as $openSalesReturn) {
                    if (strpos($openSalesReturn->NoPacks, ',') != false) {
                        $salesReturnOpenStock = $salesReturnOpenStock + array_sum(explode(',', $openSalesReturn->NoPacks));
                    } else {
                        $salesReturnOpenStock = $salesReturnOpenStock + (int) $openSalesReturn->NoPacks;
                    }
                }
                foreach ($openPros as $openPro) {
                    if (strpos($openPro->NoPacks, ',') != false) {
                        $proOpenStock = $proOpenStock + array_sum(explode(',', $openPro->NoPacks));
                    } else {
                        $proOpenStock = $proOpenStock + (int) $openPro->NoPacks;
                    }
                }
                //end adition open stock
                //start subtraction open stock
                $openOutwards = DB::select("select * from (select o.NoPacks, DATE_FORMAT(o.created_at,'%Y-%m-%d') as CreatedDate, a.ArticleStatus, a.CategoryId from outward o inner join article a on a.Id=o.ArticleId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.ArticleStatus != '3' and dd.CategoryId='" . $category->Id . "'");
                $openPurchaseReturns = DB::select("select * from (select pr.ReturnNoPacks as NoPacks, DATE_FORMAT(pr.CreatedDate,'%Y-%m-%d') as CreatedDate, a.CategoryId, a.ArticleStatus from purchasereturn pr inner join article a on a.Id=pr.ArticleId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.ArticleStatus != '3' and dd.CategoryId='" . $category->Id . "'");
                $openCons = DB::select("select * from (select st.ConsumedNoPacks as NoPacks, DATE_FORMAT(st.created_at,'%Y-%m-%d') as CreatedDate, a.CategoryId, a.ArticleStatus from stocktransfer st inner join article a on a.Id=st.ConsumedArticleId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.ArticleStatus != '3' and dd.CategoryId='" . $category->Id . "'");
                $openShortages = DB::select("select * from (select ss.NoPacks, DATE_FORMAT(ss.created_at,'%Y-%m-%d') as CreatedDate, a.CategoryId, a.ArticleStatus from stockshortage ss inner join article a on a.Id=ss.ArticleId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.ArticleStatus != '3' and dd.CategoryId='" . $category->Id . "'");
                foreach ($openOutwards as $openOutward) {
                    if (strpos($openOutward->NoPacks, ',') != false) {
                        $outwardOpenStock = $outwardOpenStock + array_sum(explode(',', $openOutward->NoPacks));
                    } else {
                        $outwardOpenStock = $outwardOpenStock + (int) $openOutward->NoPacks;
                    }
                }
                foreach ($openPurchaseReturns as $openPurchaseReturn) {
                    if (strpos($openPurchaseReturn->NoPacks, ',') != false) {
                        $purchaseReturnOpenStock = $purchaseReturnOpenStock + array_sum(explode(',', $openPurchaseReturn->NoPacks));
                    } else {
                        $purchaseReturnOpenStock = $purchaseReturnOpenStock + (int) $openPurchaseReturn->NoPacks;
                    }
                }
                foreach ($openCons as $openCon) {
                    if (strpos($openCon->NoPacks, ',') != false) {
                        $conOpenStock = $conOpenStock + array_sum(explode(',', $openCon->NoPacks));
                    } else {
                        $conOpenStock = $conOpenStock + (int) $openCon->NoPacks;
                    }
                }
                foreach ($openShortages as $openShortage) {
                    if (strpos($openShortage->NoPacks, ',') != false) {
                        $shortageOpenStock = $shortageOpenStock + array_sum(explode(',', $openShortage->NoPacks));
                    } else {
                        $shortageOpenStock = $shortageOpenStock + (int) $openShortage->NoPacks;
                    }
                }
                //end subtraction open stock
                $catOpenStock = ($catOpenStock + $inwardOpenStock + $proOpenStock + $salesReturnOpenStock) - ($outwardOpenStock + $purchaseReturnOpenStock + $conOpenStock + $shortageOpenStock);
                array_push($CatOpeningStocks, [$category->Title => $catOpenStock]);


                $mainOpeningd = '0';
                $styles = DB::select("SELECT COUNT(`Id`) AS total_sum FROM article WHERE CategoryId = '" . $category->Id . "'");
                if ($styles > 0) {
                    $mainOpeningd = $styles[0]->total_sum;
                }

                array_push($CatOpeningStock, ["title" => $category->Title, "stock" => $catOpenStock, "style" => $mainOpeningd]);
                $styleSum = array_sum(array_column($CatOpeningStock, 'style'));

                //end open stock

                //start close stock
                $catCloseStock = 0;
                $closeStock = 0;
                //start adition open stock
                $inwardCloseStock = 0;
                $salesReturnCloseStock = 0;
                $proCloseStock = 0;
                $outwardCloseStock = 0;
                $purchaseReturnCloseStock = 0;
                $conCloseStock = 0;
                $shortageCloseStock = 0;
                $closeInwards = DB::select("select * from (select i.NoPacks, DATE_FORMAT(i.created_at,'%Y-%m-%d') as CreatedDate, DATE_FORMAT(i.created_at, '%h:%m:%S') as CreatedTime, a.CategoryId, a.ArticleStatus from inward i inner join article a on a.Id=i.ArticleId) as dd where dd.ArticleStatus != '3' and dd.CreatedDate <= '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "'");
                $closeSalesReturns = DB::select("select * from (select sr.NoPacks, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, a.CategoryId, a.ArticleStatus from salesreturn sr inner join article a on a.Id=sr.ArticleId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.ArticleStatus !='3' and dd.CategoryId='" . $category->Id . "'");
                $closePros = DB::select("select * from (select st.TransferNoPacks as NoPacks, DATE_FORMAT(st.created_at,'%Y-%m-%d') as CreatedDate, st.TransferArticleId as ArticleId, a.CategoryId, a.ArticleStatus from stocktransfer st inner join article a on a.Id=st.TransferArticleId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.ArticleStatus !='3' and dd.CategoryId='" . $category->Id . "'");
                foreach ($closeInwards as $closeInward) {
                    if (strpos($closeInward->NoPacks, ',') != false) {
                        $inwardCloseStock = $inwardCloseStock + array_sum(explode(',', $closeInward->NoPacks));
                    } else {
                        $inwardCloseStock = $inwardCloseStock + (int) $closeInward->NoPacks;
                    }
                }
                foreach ($closeSalesReturns as $closeSalesReturn) {
                    if (strpos($closeSalesReturn->NoPacks, ',') != false) {
                        $salesReturnCloseStock = $salesReturnCloseStock + array_sum(explode(',', $closeSalesReturn->NoPacks));
                    } else {
                        $salesReturnCloseStock = $salesReturnCloseStock + (int) $closeSalesReturn->NoPacks;
                    }
                }
                foreach ($closePros as $closePro) {
                    if (strpos($closePro->NoPacks, ',') != false) {
                        $proCloseStock = $proCloseStock + array_sum(explode(',', $closePro->NoPacks));
                    } else {
                        $proCloseStock = $proCloseStock + (int) $closePro->NoPacks;
                    }
                }
                //end adition close stock
                //start subtraction close stock
                $closeOutwards = DB::select("select * from (select o.NoPacks, DATE_FORMAT(o.created_at,'%Y-%m-%d') as CreatedDate, a.ArticleStatus, a.CategoryId from outward o inner join article a on a.Id=o.ArticleId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.ArticleStatus != '3' and dd.CategoryId='" . $category->Id . "'");
                $closePurchaseReturns = DB::select("select * from (select pr.ReturnNoPacks as NoPacks, DATE_FORMAT(pr.CreatedDate,'%Y-%m-%d') as CreatedDate, a.CategoryId, a.ArticleStatus from purchasereturn pr inner join article a on a.Id=pr.ArticleId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.ArticleStatus != '3' and dd.CategoryId='" . $category->Id . "'");
                $closeCons = DB::select("select * from (select st.ConsumedNoPacks as NoPacks, DATE_FORMAT(st.created_at,'%Y-%m-%d') as CreatedDate, a.CategoryId, a.ArticleStatus from stocktransfer st inner join article a on a.Id=st.ConsumedArticleId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.ArticleStatus != '3' and dd.CategoryId='" . $category->Id . "'");
                $closeShortages = DB::select("select * from (select ss.NoPacks, DATE_FORMAT(ss.created_at,'%Y-%m-%d') as CreatedDate, a.CategoryId, a.ArticleStatus from stockshortage ss inner join article a on a.Id=ss.ArticleId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.ArticleStatus != '3' and dd.CategoryId='" . $category->Id . "'");
                foreach ($closeOutwards as $closeOutward) {
                    if (strpos($closeOutward->NoPacks, ',') != false) {
                        $outwardCloseStock = $outwardCloseStock + array_sum(explode(',', $closeOutward->NoPacks));
                    } else {
                        $outwardCloseStock = $outwardCloseStock + (int) $closeOutward->NoPacks;
                    }
                }
                foreach ($closePurchaseReturns as $closePurchaseReturn) {
                    if (strpos($closePurchaseReturn->NoPacks, ',') != false) {
                        $purchaseReturnCloseStock = $purchaseReturnCloseStock + array_sum(explode(',', $closePurchaseReturn->NoPacks));
                    } else {
                        $purchaseReturnCloseStock = $purchaseReturnCloseStock + (int) $closePurchaseReturn->NoPacks;
                    }
                }
                foreach ($closeCons as $closeCon) {
                    if (strpos($closeCon->NoPacks, ',') != false) {
                        $conCloseStock = $conCloseStock + array_sum(explode(',', $closeCon->NoPacks));
                    } else {
                        $conCloseStock = $conCloseStock + (int) $closeCon->NoPacks;
                    }
                }
                foreach ($closeShortages as $closeShortage) {
                    if (strpos($closeShortage->NoPacks, ',') != false) {
                        $shortageCloseStock = $shortageCloseStock + array_sum(explode(',', $closeShortage->NoPacks));
                    } else {
                        $shortageCloseStock = $shortageCloseStock + (int) $closeShortage->NoPacks;
                    }
                }
                //end subtraction close stock
                $catCloseStock = ($inwardCloseStock + $proCloseStock + $salesReturnCloseStock) - ($outwardCloseStock + $purchaseReturnCloseStock + $conCloseStock + $shortageCloseStock);



                $mainClosingd = 0;
                $styles = DB::select("SELECT COUNT(`Id`) AS total_sum FROM article WHERE CategoryId = '" . $category->Id . "'");
                if ($styles > 0) {
                    $mainClosingd = $styles[0]->total_sum;
                } else {
                    $mainClosingd = '0';
                }

                array_push($CatClosingStock, ["title" => $category->Title, "stock" => $catCloseStock, "style" => $mainClosingd]);

                array_push($CatClosingStocks, [$category->Title => $catCloseStock]);
                //end close stock
            }
            $catCount = Category::count();
            for ($i = 0; $i <= (int) $catCount - 1; $i++) {
                foreach ($CatOpeningStocks[$i] as $key => $value) {
                    $openStock = $openStock + $value;
                }
            }
            for ($i = 0; $i <= (int) $catCount - 1; $i++) {
                foreach ($CatClosingStocks[$i] as $key => $value) {
                    $closeStock = $closeStock + $value;
                }
            }


            //start daily stock
            $maininwardRecordss = DB::select("select * from (select ig.Id as GRNId, concat(ig.GRN,'/', fn.StartYear,'-',fn.EndYear) as GRNnumber, DATE_FORMAT(i.created_at ,'%Y-%m-%d') as CreatedAt, DATE_FORMAT(i.created_at ,'%d-%m-%Y') as CreatedDate from inwardgrn ig inner join inward i on ig.Id=i.GRN inner join financialyear fn on fn.Id=ig.FinancialYearId) as dd where dd.CreatedAt = '" . $RangeDate . "'");
            $collectioncateInwa = collect($maininwardRecordss);
            $maininwardRecords = $collectioncateInwa->unique()->values()->all();
            $maintotalInwardSalesNoPacks = 0;
            // foreach ($maininwardRecords as $maininwardRecord) {
            foreach ($maininwardRecords as $key => $maininwardRecord) {
                $inwards = DB::select("select i.NoPacks, v.Name from inward i inner join po on po.ArticleId=i.ArticleId inner join vendor v on v.Id=po.VendorId where i.GRN='" . $maininwardRecord->GRNId . "' and i.InwardDate = '" . $RangeDate . "'");
                $grnPacks = 0;
                $inwardVendor = Inward::select('vendor.Name')->join('po', 'po.ArticleId', 'inward.ArticleId')->join('vendor', 'vendor.Id', 'po.VendorId')->where('inward.GRN', $maininwardRecord->GRNId)->where('inward.InwardDate', $RangeDate)->first();
                $grnPacks = 0;
                if (isset($inwardVendor)) {
                    $name = $inwardVendor->Name;
                } else {
                    $name = "";
                }
                foreach ($inwards as $inward) {
                    if (strpos($inward->NoPacks, ',') != false) {
                        $grnPacks = $grnPacks + array_sum(explode(',', $inward->NoPacks));
                    } else {
                        $grnPacks = $grnPacks + (int) $inward->NoPacks;
                    }
                }
                $maintotalInwardSalesNoPacks = $maintotalInwardSalesNoPacks + $grnPacks;
                $maininwardRecord->SalesNoPacks = $grnPacks;
                $maininwardRecord->Name = $name;
                if ($maininwardRecord->SalesNoPacks == 0) {
                    unset($maininwardRecords[$key]);
                }
            }
            $mainpurchaseReturnRecords = DB::select("select * from (select prn.Id as PrnId , concat(prn.PurchaseReturnNumber,'/', fn.StartYear, '-', fn.EndYear) as PRNumber, DATE_FORMAT(prn.CreatedDate ,'%Y-%m-%d') as CreatedAt, DATE_FORMAT(prn.CreatedDate ,'%d-%m-%Y') as CreatedDate, v.Name as Vendor_Name from purchasereturnnumber prn left join vendor v on prn.VendorId=v.Id inner join financialyear fn on fn.Id=prn.FinancialYearId) as sart where sart.CreatedAt='" . $RangeDate . "'");
            $maintotalPurchaseReturnNoPacks = 0;
            // foreach ($mainpurchaseReturnRecords as $mainpurchaseReturnRecord) {
            foreach ($mainpurchaseReturnRecords as $key => $mainpurchaseReturnRecord) {

                $purchaseReturns = DB::select("select ReturnNoPacks from purchasereturn where PurchaseReturnNumber=" . $mainpurchaseReturnRecord->PrnId);
                $prPacks = 0;
                foreach ($purchaseReturns as $purchaseReturn) {
                    if (strpos($purchaseReturn->ReturnNoPacks, ',') != false) {
                        $prPacks = $prPacks + array_sum(explode(',', $purchaseReturn->ReturnNoPacks));
                    } else {
                        $prPacks = $prPacks + (int) $purchaseReturn->ReturnNoPacks;
                    }
                }
                $maintotalPurchaseReturnNoPacks = $maintotalPurchaseReturnNoPacks + $prPacks;
                $mainpurchaseReturnRecord->ReturnNoPacks = $prPacks;
                if ($mainpurchaseReturnRecord->ReturnNoPacks == 0) {
                    unset($mainpurchaseReturnRecords[$key]);
                }
            }
            $mainoutwardRecords = DB::select("select * from (select onn.Id as OutwardNumberId , concat(onn.OutwardNumber,'/', fn.StartYear, '-', fn.EndYear) as OutwardNumber, concat(son.SoNumber,'/',fn.StartYear,'-',fn.EndYear) as SoNumber, DATE_FORMAT(onn.OutwardDate, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(onn.OutwardDate, '%d-%m-%Y') as CreatedDate, p.Name as Party_Name, pp.Name as Outlet, pp.OutletAssign from outwardnumber onn inner join sonumber son on son.Id=onn.SoId inner join party p on p.Id=son.PartyId inner join financialyear fn on fn.Id=onn.FinancialYearId left join users usr on usr.Id=p.UserId left join party pp on pp.Id=usr.PartyId) as o where o.CreatedAt='" . $RangeDate . "' order by o.OutwardNumberId ");
            $maintotalOutwardNoPacks = 0;
            // foreach ($mainoutwardRecords as $mainoutwardRecord) {
            foreach ($mainoutwardRecords as $key => $mainoutwardRecord) {
                if ($mainoutwardRecord->OutletAssign = 1) {
                    $mainoutwardRecord->Outlet = $mainoutwardRecord->Outlet;
                } else {
                    $mainoutwardRecord->Outlet = 'DIRECT';
                }
                $outwards = DB::select("select NoPacks from outward where OutwardNumberId=" . $mainoutwardRecord->OutwardNumberId);
                $outwardPacks = 0;
                foreach ($outwards as $outward) {
                    if (strpos($outward->NoPacks, ',') != false) {
                        $outwardPacks = $outwardPacks + array_sum(explode(',', $outward->NoPacks));
                    } else {
                        $outwardPacks = $outwardPacks + (int) $outward->NoPacks;
                    }
                }
                $maintotalOutwardNoPacks = $maintotalOutwardNoPacks + $outwardPacks;
                $mainoutwardRecord->NoPacks = $outwardPacks;
                if ($mainoutwardRecord->NoPacks == 0) {
                    unset($mainoutwardRecords[$key]);
                }
            }
            $mainsalesReturnRecords = DB::select("select * from (select srn.Id as SalesReturnNumberId ,concat(srn.SalesReturnNumber,'/', fn.StartYear, '-', fn.EndYear) as SRNumber, DATE_FORMAT(srn.CreatedDate ,'%Y-%m-%d') as CreatedAt , DATE_FORMAT(srn.CreatedDate ,'%d/%m%/%Y') as CreatedDate, p.Name as PartyName from salesreturnnumber srn inner join party p on srn.PartyId=p.Id inner join financialyear fn on fn.Id=srn.FinancialYearId) as dd where dd.CreatedAt ='" . $RangeDate . "'");
            $maintotalSalesReturnNoPacks = 0;
            // foreach ($mainsalesReturnRecords as $mainsalesReturnRecord) {
            foreach ($mainsalesReturnRecords as $key => $mainsalesReturnRecord) {
                $salesReturns = DB::select("select NoPacks from salesreturn where SalesReturnNumber=" . $mainsalesReturnRecord->SalesReturnNumberId);
                $salesreturnPacks = 0;
                foreach ($salesReturns as $salesReturn) {
                    if (strpos($salesReturn->NoPacks, ',') != false) {
                        $salesreturnPacks = $salesreturnPacks + array_sum(explode(',', $salesReturn->NoPacks));
                    } else {
                        $salesreturnPacks = $salesreturnPacks + (int) $salesReturn->NoPacks;
                    }
                }
                $maintotalSalesReturnNoPacks = $maintotalSalesReturnNoPacks + $salesreturnPacks;
                $mainsalesReturnRecord->NoPacks = $salesreturnPacks;
                if ($mainsalesReturnRecord->NoPacks == 0) {
                    unset($mainsalesReturnRecords[$key]);
                }
            }
            //start daily cat wise stock
            $inwardCategoryWise = [];
            $totalInwardPacks = 0;
            $salesReturnCategoryWise = [];
            $totalSalesReturnNoPacks = 0;
            $outwardCategoryWise = [];
            $totaloutwardNoPacks = 0;
            $purchaseReturnCategoryWise = [];
            $totalReturnNoPacks = 0;
            foreach ($categories as $category) {
                $inwardRecords = DB::select("select * from (select c.Id as CatId ,i.NoPacks as SalesNoPacks, DATE_FORMAT(i.InwardDate ,'%Y-%m-%d') as CreatedAt from inwardgrn ig inner join inward i on ig.Id=i.GRN left join vendor v on v.Id=ig.VendorId inner join article a on a.Id=i.ArticleId inner join category c on c.Id=a.CategoryId inner join financialyear fn on fn.Id=ig.FinancialYearId) as dd where dd.CreatedAt ='" . $RangeDate . "' and dd.CatId=" . $category->Id);
                $InwardPacks = 0;
                foreach ($inwardRecords as $inwardRecord) {
                    if (strpos($inwardRecord->SalesNoPacks, ',')) {
                        $InwardPacks = $InwardPacks + array_sum(explode(',', $inwardRecord->SalesNoPacks));
                    } else {
                        $InwardPacks = $InwardPacks + (int) $inwardRecord->SalesNoPacks;
                    }
                }
                if (strpos($InwardPacks, ',') != false) {
                    $totalInwardPacks = $totalInwardPacks + array_sum(explode(',', $InwardPacks));
                } else {
                    $totalInwardPacks = $totalInwardPacks + $InwardPacks;
                }
                array_push($inwardCategoryWise, [$category->Title => $InwardPacks]);
                $purchaseReturnRecords = DB::select("select * from (select DATE_FORMAT(pr.CreatedDate ,'%Y-%m-%d') as CreatedAt,c.Id as CatId, pr.ReturnNoPacks from purchasereturn pr inner join article a on a.Id=pr.ArticleId inner join category c on c.Id=a.CategoryId) as sart where sart.CreatedAt='" . $RangeDate . "' and sart.CatId='" . $category->Id . "'");
                $ReturnNoPacks = 0;
                foreach ($purchaseReturnRecords as $purchaseReturnRecord) {
                    if (strpos($purchaseReturnRecord->ReturnNoPacks, ',') != false) {
                        $ReturnNoPacks = $ReturnNoPacks + array_sum(explode(',', $purchaseReturnRecord->ReturnNoPacks));
                    } else {
                        $ReturnNoPacks = $ReturnNoPacks + $purchaseReturnRecord->ReturnNoPacks;
                    }
                }
                if (strpos($ReturnNoPacks, ',') != false) {
                    $totalReturnNoPacks = $totalReturnNoPacks + array_sum(explode(',', $ReturnNoPacks));
                } else {
                    $totalReturnNoPacks = $totalReturnNoPacks + $ReturnNoPacks;
                }
                array_push($purchaseReturnCategoryWise, [$category->Title => $ReturnNoPacks]);
                $salesReturnRecords = DB::select("select * from (select DATE_FORMAT(sr.CreatedDate ,'%Y-%m-%d') as CreatedAt, sr.NoPacks, c.Id as CatId from salesreturn sr inner join article a on a.Id=sr.ArticleId inner join category c on c.Id=a.CategoryId) as dd where dd.CreatedAt ='" . $RangeDate . "' and dd.CatId='" . $category->Id . "'");
                $salesReturnNoPacks = 0;
                foreach ($salesReturnRecords as $salesReturnRecord) {
                    if (strpos($salesReturnRecord->NoPacks, ',') != false) {
                        $salesReturnNoPacks = $salesReturnNoPacks + array_sum(explode(',', $salesReturnRecord->NoPacks));
                    } else {
                        $salesReturnNoPacks = $salesReturnNoPacks + $salesReturnRecord->NoPacks;
                    }
                }
                if (strpos($salesReturnNoPacks, ',') != false) {
                    $totalSalesReturnNoPacks = $totalSalesReturnNoPacks + array_sum(explode(',', $salesReturnNoPacks));
                } else {
                    $totalSalesReturnNoPacks = $totalSalesReturnNoPacks + $salesReturnNoPacks;
                }
                array_push($salesReturnCategoryWise, [$category->Title => $salesReturnNoPacks]);
                $outwardRecords = DB::select("select *from (select DATE_FORMAT(onn.OutwardDate, '%Y-%m-%d') as CreatedAt, o.NoPacks, c.Id as CatId from outward o inner join outwardnumber onn on o.OutwardNumberId=onn.Id inner join article a on a.Id=o.ArticleId inner join category c on c.Id=a.CategoryId) as dd where dd.CreatedAt= '" . $RangeDate . "' and dd.CatId='" . $category->Id . "'");
                $outwardNoPacks = 0;
                foreach ($outwardRecords as $outwardRecord) {
                    if (strpos($outwardRecord->NoPacks, ',') != false) {
                        $outwardNoPacks = $outwardNoPacks + array_sum(explode(',', $outwardRecord->NoPacks));
                    } else {
                        $outwardNoPacks = $outwardNoPacks + $outwardRecord->NoPacks;
                    }
                }
                if (strpos($outwardNoPacks, ',') != false) {
                    $totaloutwardNoPacks = $totaloutwardNoPacks + array_sum(explode(',', $outwardNoPacks));
                } else {
                    $totaloutwardNoPacks = $totaloutwardNoPacks + $outwardNoPacks;
                }
                array_push($outwardCategoryWise, [$category->Title => $outwardNoPacks]);
            }
            //end daily cat wise stock
            //end daily stock
            return [
                'orgOutletName' => 'Fectory',
                'outletOpeningStock' => $openStock,
                'outletClosingStock' => $closeStock,
                'outletOpeningCategorywise' => $CatOpeningStock,
                'outletClosingCategorywise' => $CatClosingStock,
                'Categories' => $categories,
                'TotalStyle' => $styleSum,
                'AllData' => ['inwardRecords' => array_values($maininwardRecords), 'totalInwardSalesNoPacks' => $maintotalInwardSalesNoPacks, 'outwardRecords' => array_values($mainoutwardRecords), 'totalOutwardNoPacks' => $maintotalOutwardNoPacks, 'purchaseReturnRecords' => array_values($mainpurchaseReturnRecords), 'totalPurchaseReturnNoPacks' => $maintotalPurchaseReturnNoPacks, 'salesReturnRecords' => array_values($mainsalesReturnRecords), 'totalSalesReturnNoPacks' => $maintotalSalesReturnNoPacks],
                'AllDataCat' => ['inwardCategoryWise' => $inwardCategoryWise, 'outwardCategoryWise' => $outwardCategoryWise, 'purchaseReturnCategoryWise' => $purchaseReturnCategoryWise, 'salesReturnCategoryWise' => $salesReturnCategoryWise]
            ];
        } else {

            $orgOutlet = Party::select('Name')->where('Id', $PartyId)->first();
            $orgOutletName = $orgOutlet->Name;
            // opening stock
            $beforeInwards = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, a.CategoryId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId inner join outward o on o.Id=trop.OutwardId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");
            $beforeImports = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, a.CategoryId, trop.OutwardId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId) as dd where dd.OutwardId=0 and dd.CreatedDate < '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");
            $beforeOutwards = DB::select("select * from (select o.NoPacks, o.ArticleId, otn.PartyId, a.CategoryId, DATE_FORMAT(o.created_at,'%Y-%m-%d') as OutletDate from outlet o inner join article a on a.Id=o.ArticleId inner join outletnumber otn on otn.Id=o.OutletNumberId) as dd where dd.OutletDate < '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");
            $beforePurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.CreatedDate > '2021-12-31' and dd.PartyId='" . $PartyId . "'");
            $beforeSalesReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, sr.OutletPartyId as PartyId from outletsalesreturn sr inner join article a on a.Id=sr.ArticleId inner join outletsalesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");


            $openingStock = 0;
            $totalInwardPacks = 0;
            $totalImportPacks = 0;
            $totalOutwardPacks = 0;
            $totalSalesReturnPacks = 0;
            $totalPurchaseReturnPacks = 0;
            foreach ($beforeInwards as $beforeInward) {
                if (strpos($beforeInward->NoPacks, ',') != false) {
                    $totalInwardPacks = $totalInwardPacks + array_sum(explode(',', $beforeInward->NoPacks));
                } else {
                    $totalInwardPacks = $totalInwardPacks + (int) $beforeInward->NoPacks;
                }
            }
            foreach ($beforeImports as $beforeImport) {
                if (strpos($beforeImport->NoPacks, ',') != false) {
                    $totalImportPacks = $totalImportPacks + array_sum(explode(',', $beforeImport->NoPacks));
                } else {
                    $totalImportPacks = $totalImportPacks + (int) $beforeImport->NoPacks;
                }
            }
            foreach ($beforeOutwards as $beforeOutward) {
                if (strpos($beforeOutward->NoPacks, ',') != false) {
                    $totalOutwardPacks = $totalOutwardPacks + array_sum(explode(',', $beforeOutward->NoPacks));
                } else {
                    $totalOutwardPacks = $totalOutwardPacks + (int) $beforeOutward->NoPacks;
                }
            }
            foreach ($beforeSalesReturns as $beforeSalesReturn) {
                if (strpos($beforeSalesReturn->NoPacks, ',') != false) {
                    $totalSalesReturnPacks = $totalSalesReturnPacks + array_sum(explode(',', $beforeSalesReturn->NoPacks));
                } else {
                    $totalSalesReturnPacks = $totalSalesReturnPacks + (int) $beforeSalesReturn->NoPacks;
                }
            }
            foreach ($beforePurchaseReturns as $beforePurchaseReturn) {
                if (strpos($beforePurchaseReturn->NoPacks, ',') != false) {
                    $totalPurchaseReturnPacks = $totalPurchaseReturnPacks + array_sum(explode(',', $beforePurchaseReturn->NoPacks));
                } else {
                    $totalPurchaseReturnPacks = $totalPurchaseReturnPacks + (int) $beforePurchaseReturn->NoPacks;
                }
            }
            $openingStock = $openingStock + $totalInwardPacks + $totalImportPacks + $totalSalesReturnPacks - $totalOutwardPacks - $totalPurchaseReturnPacks;
            // end opening stock
            // category wise opening stock
            $categories = Category::get();
            $CatOpeningStock = [];
            foreach ($categories as $category) {
                $beforeInwardss = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, a.CategoryId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId inner join outward o on o.Id=trop.OutwardId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "' and dd.CategoryId='" . $category->Id . "'");
                $beforeImports = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, a.CategoryId, trop.OutwardId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId) as dd where dd.OutwardId=0 and dd.CreatedDate < '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "' and dd.PartyId='" . $PartyId . "'");
                $beforeOutwards = DB::select("select * from (select o.NoPacks, o.ArticleId, otn.PartyId, a.CategoryId, DATE_FORMAT(o.created_at,'%Y-%m-%d') as OutletDate from outlet o inner join article a on a.Id=o.ArticleId inner join outletnumber otn on otn.Id=o.OutletNumberId) as dd where dd.OutletDate < '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "' and dd.PartyId='" . $PartyId . "'");
                $beforePurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber inner join article a on a.Id=sr.ArticleId) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.CreatedDate > '2021-12-31' and dd.PartyId='" . $PartyId . "' and dd.CategoryId='" . $category->Id . "'");
                $beforeSalesReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, sr.OutletPartyId as PartyId from outletsalesreturn sr inner join article a on a.Id=sr.ArticleId inner join outletsalesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate < '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "' and dd.PartyId='" . $PartyId . "'");



                $innwardOpeningStock = 0;
                $totalInnwardOpeningStock = 0;
                $importOpeningStock = 0;
                $outwardOpeningStock = 0;
                $purchaseReturnOpeningStock = 0;
                $salesReturnOpeningStock = 0;
                foreach ($beforeInwardss as $CatWisebeforeInward) {
                    if (strpos($CatWisebeforeInward->NoPacks, ',') != false) {
                        $innwardOpeningStock = $innwardOpeningStock + array_sum(explode(',', $CatWisebeforeInward->NoPacks));
                    } else {
                        $innwardOpeningStock = $innwardOpeningStock + (int) $CatWisebeforeInward->NoPacks;
                    }
                }
                if (strpos($innwardOpeningStock, ',') != false) {
                    $totalInnwardOpeningStock = $totalInnwardOpeningStock + array_sum(explode(',', $innwardOpeningStock));
                } else {
                    $totalInnwardOpeningStock = $totalInnwardOpeningStock + $innwardOpeningStock;
                }
                foreach ($beforeImports as $beforeImport) {
                    if (strpos($beforeImport->NoPacks, ',') != false) {
                        $importOpeningStock = $importOpeningStock + array_sum(explode(',', $beforeImport->NoPacks));
                    } else {
                        $importOpeningStock = $importOpeningStock + (int) $beforeImport->NoPacks;
                    }
                }
                foreach ($beforeOutwards as $beforeOutward) {
                    if (strpos($beforeOutward->NoPacks, ',') != false) {
                        $outwardOpeningStock = $outwardOpeningStock + array_sum(explode(',', $beforeOutward->NoPacks));
                    } else {
                        $outwardOpeningStock = $outwardOpeningStock + (int) $beforeOutward->NoPacks;
                    }
                }
                foreach ($beforePurchaseReturns as $beforePurchaseReturn) {
                    if (strpos($beforePurchaseReturn->NoPacks, ',') != false) {
                        $purchaseReturnOpeningStock = $purchaseReturnOpeningStock + array_sum(explode(',', $beforePurchaseReturn->NoPacks));
                    } else {
                        $purchaseReturnOpeningStock = $purchaseReturnOpeningStock + (int) $beforePurchaseReturn->NoPacks;
                    }
                }
                foreach ($beforeSalesReturns as $beforeSalesReturn) {
                    if (strpos($beforeSalesReturn->NoPacks, ',') != false) {
                        $salesReturnOpeningStock = $salesReturnOpeningStock + array_sum(explode(',', $beforeSalesReturn->NoPacks));
                    } else {
                        $salesReturnOpeningStock = $salesReturnOpeningStock + (int) $beforeSalesReturn->NoPacks;
                    }
                }
                $mainOpening = 0;
                $mainOpening = $mainOpening + $innwardOpeningStock + $importOpeningStock + $salesReturnOpeningStock - $outwardOpeningStock - $purchaseReturnOpeningStock;



                $mainOpeningd = '0';
                $styles = DB::select("SELECT COUNT(`Id`) AS total_sum FROM article WHERE CategoryId = '" . $category->Id . "'");
                if ($styles > 0) {
                    $mainOpeningd = $styles[0]->total_sum;
                }
                array_push($CatOpeningStock, ["title" => $category->Title, "stock" => $mainOpening, "style" => $mainOpeningd]);
                $styleSum = array_sum(array_column($CatOpeningStock, 'style'));
                // array_push($CatOpeningStock, [$category->Title => $mainOpening]);
            }
            // end category wise opening stock 
            // start one day record cat wise
            $TotalarticleOpeningStock = 0;
            $categories = DB::select("select * from category");
            $outletInwardCategoryWise = [];
            $outletImportCategoryWise = [];
            $outletOutwardCategoryWise = [];
            $outletPurchaseReturnCategoryWise = [];
            $outletSalesReturnCategoryWise = [];
            $totalCatInwardPacks = 0;
            $totalCatImportPacks = 0;
            $totalCatOutwardPacks = 0;
            $totalCatJhcplOutwardPacks = 0;
            $totalCatPRPacks = 0;
            $totalCatSRPacks = 0;
            foreach ($categories as $category) {
                // JHCPL Outward
                $jhcplOutwards = DB::select("select * from (select p.Name as Party, 'DIRECT' as Outlet, o.NoPacks, a.CategoryId, otn.Id as OutletNumberId, otn.OutwardNumberId, otn.PartyId, concat(otn.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber, DATE_FORMAT(otn.OutletDate, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(otn.OutletDate, '%d-%m-%Y') as OutletDate from outward o inner join outletnumber otn on o.OutwardNumberId=otn.OutwardNumberId inner join party p on p.Id=otn.OutletPartyId inner join financialyear fn on fn.Id=otn.FinancialYearId inner join article a on a.Id=o.ArticleId) as dd where dd.PartyId='" . $PartyId . "' and dd.CreatedAt='" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "'");
                $outwardJhcplPacks = 0;
                foreach ($jhcplOutwards as $jhcplOutward) {
                    if (strpos($jhcplOutward->NoPacks, ',') != false) {
                        $outwardJhcplPacks = $outwardJhcplPacks + array_sum(explode(',', $jhcplOutward->NoPacks));
                    } else {
                        $outwardJhcplPacks = $outwardJhcplPacks + (int) $jhcplOutward->NoPacks;
                    }
                }
                if (strpos($outwardJhcplPacks, ',') != false) {
                    $totalCatJhcplOutwardPacks = $totalCatJhcplOutwardPacks + array_sum(explode(',', $outwardJhcplPacks));
                } else {
                    $totalCatJhcplOutwardPacks = $totalCatJhcplOutwardPacks + $outwardJhcplPacks;
                }
                // end
                $catInwardRec = DB::select("select * from (select c.Id as CategoryId, trop.NoPacks, trop.PartyId, DATE_FORMAT(trop.CreatedDate, '%Y-%m-%d') as ReceivedAt, DATE_FORMAT(trop.CreatedDate, '%d-%m-%Y') as ReceivedDate from outwardnumber own inner join outward o on o.OutwardNumberId=own.Id inner join transportoutwardpacks trop on trop.OutwardId=o.Id left join article a on a.Id=trop.ArticleId inner join category c on c.Id=a.CategoryId) as dd where dd.ReceivedAt='" . $RangeDate . "' and dd.PartyId='" . $PartyId . "' and dd.CategoryId='" . $category->Id . "' ");
                $catInwardPacks = 0;
                foreach ($catInwardRec as $inwardRecord) {
                    if (strpos($inwardRecord->NoPacks, ',')) {
                        $catInwardPacks = $catInwardPacks + array_sum(explode(',', $inwardRecord->NoPacks));
                    } else {
                        $catInwardPacks = $catInwardPacks + (int) $inwardRecord->NoPacks;
                    }
                }
                if (strpos($catInwardPacks, ',') != false) {
                    $totalCatInwardPacks = $totalCatInwardPacks + array_sum(explode(',', $catInwardPacks));
                } else {
                    $totalCatInwardPacks = $totalCatInwardPacks + $catInwardPacks;
                }
                array_push($outletInwardCategoryWise, [$category->Title => $catInwardPacks + $outwardJhcplPacks]);
                $catImportRec = DB::select("select * from (select trop.NoPacks, trop.Id, c.Id as CategoryId, trop.ArticleId, trop.PartyId, trop.OutwardId, DATE_FORMAT(trop.CreatedDate, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(trop.CreatedDate, '%d-%m-%Y') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId inner join category c on c.Id=a.CategoryId) as dd where dd.CreatedAt='" . $RangeDate . "' and dd.PartyId='" . $PartyId . "' and dd.OutwardId=0 and dd.CategoryId='" . $category->Id . "' ");
                $collectioncateImports = collect($catImportRec);
                $catImportRecords = $collectioncateImports->unique()->values()->all();
                $catImportPacks = 0;
                foreach ($catImportRecords as $catImportRecord) {
                    if (strpos($catImportRecord->NoPacks, ',')) {
                        $catImportPacks = $catImportPacks + array_sum(explode(',', $catImportRecord->NoPacks));
                    } else {
                        $catImportPacks = $catImportPacks + (int) $catImportRecord->NoPacks;
                    }
                }
                if (strpos($catImportPacks, ',') != false) {
                    $totalCatImportPacks = $totalCatImportPacks + array_sum(explode(',', $catImportPacks));
                } else {
                    $totalCatImportPacks = $totalCatImportPacks + $catImportPacks;
                }
                array_push($outletImportCategoryWise, [$category->Title => $catImportPacks]);


                $catOutwardRec = DB::select("select dd.* from (select otn.Id as OutletNumberId, ot.NoPacks, c.Id as CategoryId, otn.PartyId, concat(otn.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber, DATE_FORMAT(ot.created_at, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(ot.created_at, '%d-%m-%Y') as OutletDate from outlet ot inner join outletnumber otn on otn.Id=ot.OutletNumberId inner join article a on a.Id=ot.ArticleId inner join category c on c.Id=a.CategoryId inner join financialyear fn on fn.Id=otn.FinancialYearId) as dd where dd.PartyId='" . $PartyId . "' and dd.CreatedAt='" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "' ");
                $catOutwardPacks = 0;
                foreach ($catOutwardRec as $catOutwardRecord) {
                    if (strpos($catOutwardRecord->NoPacks, ',')) {
                        $catOutwardPacks = $catOutwardPacks + array_sum(explode(',', $catOutwardRecord->NoPacks));
                    } else {
                        $catOutwardPacks = $catOutwardPacks + (int) $catOutwardRecord->NoPacks;
                    }
                }
                if (strpos($catOutwardPacks, ',') != false) {
                    $totalCatOutwardPacks = $totalCatOutwardPacks + array_sum(explode(',', $catOutwardPacks));
                } else {
                    $totalCatOutwardPacks = $totalCatOutwardPacks + $catOutwardPacks;
                }
                array_push($outletOutwardCategoryWise, [$category->Title => $catOutwardPacks + $outwardJhcplPacks]);
                $catpurchasereturnRecordsss = DB::select("select * from (select srn.Id as SalesReturnNumberId, sr.NoPacks, c.Id as CategoryId, p.Name as PartyName, srn.PartyId, DATE_FORMAT(srn.CreatedDate, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(sr.CreatedDate, '%d-%m-%Y') as CreatedDate from salesreturnnumber srn inner join salesreturn sr on sr.SalesReturnNumber=srn.Id inner join article a on a.Id=sr.ArticleId inner join category c on c.Id=a.CategoryId inner join party p on p.Id=srn.PartyId inner join financialyear fn on fn.Id=srn.FinancialYearId) as dd where dd.CreatedAt='" . $RangeDate . "' and dd.PartyId='" . $PartyId . "' and dd.CategoryId='" . $category->Id . "' ");
                $catPurchaseReturnNoPacks = 0;
                $carpurchasereturnRecordsssssss = collect($catpurchasereturnRecordsss);
                $catPurchaseReturnRecords = $carpurchasereturnRecordsssssss->unique()->values()->all();
                foreach ($catPurchaseReturnRecords as $catPurchaseReturnRecord) {
                    if (strpos($catPurchaseReturnRecord->NoPacks, ',')) {
                        $catPurchaseReturnNoPacks = $catPurchaseReturnNoPacks + array_sum(explode(',', $catPurchaseReturnRecord->NoPacks));
                    } else {
                        $catPurchaseReturnNoPacks = $catPurchaseReturnNoPacks + (int) $catPurchaseReturnRecord->NoPacks;
                    }
                }
                if (strpos($catPurchaseReturnNoPacks, ',') != false) {
                    $totalCatPRPacks = $totalCatPRPacks + array_sum(explode(',', $catPurchaseReturnNoPacks));
                } else {
                    $totalCatPRPacks = $totalCatPRPacks + $catPurchaseReturnNoPacks;
                }
                array_push($outletPurchaseReturnCategoryWise, [$category->Title => $catPurchaseReturnNoPacks]);

                $catSalesreturnRecords = DB::select("select * from (select srn.Id as SalesReturnNumberId, c.Id as CategoryId, sr.NoPacks, concat(srn.SalesReturnNumber,'/', fn.StartYear, '-', fn.EndYear) as SalesReturnNumber, srn.OutletPartyId, DATE_FORMAT(srn.CreatedDate ,'%Y-%m-%d') as CreatedAt , DATE_FORMAT(srn.CreatedDate ,'%d/%m%/%Y') as CreatedDate, p.Name as PartyName from outletsalesreturnnumber srn inner join outletsalesreturn sr on sr.SalesReturnNumber=srn.Id inner join article a on a.Id=sr.ArticleId inner join category c on c.Id=a.CategoryId inner join party p on srn.PartyId=p.Id inner join financialyear fn on fn.Id=srn.FinancialYearId) as dd where dd.CreatedAt ='" . $RangeDate . "' and dd.OutletPartyId='" . $PartyId . "' and dd.CategoryId='" . $category->Id . "' ");
                $catSalesReturnNoPacks = 0;
                foreach ($catSalesreturnRecords as $catSalesreturnRecord) {
                    if (strpos($catSalesreturnRecord->NoPacks, ',')) {
                        $catSalesReturnNoPacks = $catSalesReturnNoPacks + array_sum(explode(',', $catSalesreturnRecord->NoPacks));
                    } else {
                        $catSalesReturnNoPacks = $catSalesReturnNoPacks + (int) $catSalesreturnRecord->NoPacks;
                    }
                }
                if (strpos($catSalesReturnNoPacks, ',') != false) {
                    $totalCatSRPacks = $totalCatSRPacks + array_sum(explode(',', $catSalesReturnNoPacks));
                } else {
                    $totalCatSRPacks = $totalCatSRPacks + $catSalesReturnNoPacks;
                }
                array_push($outletSalesReturnCategoryWise, [$category->Title => $catSalesReturnNoPacks]);
            }
            // end one day record cat wise
            // start one day stock
            // JHCPL Outward
            $jhcplOutwards = DB::select("select * from (select p.Name as Party, 'DIRECT' as Outlet, otn.Id as OutletNumberId, otn.OutwardNumberId, otn.PartyId, concat(otn.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, DATE_FORMAT(otn.OutletDate, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(otn.OutletDate, '%d-%m-%Y') as OutletDate from outletnumber otn inner join outward o on o.OutwardNumberId=otn.OutwardNumberId inner join outwardnumber own on own.Id=o.OutwardNumberId inner join party p on p.Id=otn.OutletPartyId inner join financialyear fn on fn.Id=otn.FinancialYearId) as dd where dd.PartyId='" . $PartyId . "' and dd.CreatedAt='" . $RangeDate . "'");
            $collectionJscplOutwards = collect($jhcplOutwards);
            $mainJscplOutwardRecords = $collectionJscplOutwards->unique()->values()->all();
            $mainTotalJscplOutwardNoPacks = 0;
            foreach ($mainJscplOutwardRecords as $mainJscplOutwardRecord) {
                $jhcploutwards = DB::select("select *  from (select o.NoPacks, DATE_FORMAT(o.created_at,'%Y-%m-%d') as CreatedAt, o.OutwardNumberId from outward o) as dd where dd.CreatedAt='" . $RangeDate . "' and dd.OutwardNumberId='" . $mainJscplOutwardRecord->OutwardNumberId . "'");
                $outwardJhcplPacks = 0;
                foreach ($jhcploutwards as $jhcploutward) {
                    if (strpos($jhcploutward->NoPacks, ',') != false) {
                        $outwardJhcplPacks = $outwardJhcplPacks + array_sum(explode(',', $jhcploutward->NoPacks));
                    } else {
                        $outwardJhcplPacks = $outwardJhcplPacks + (int) $jhcploutward->NoPacks;
                    }
                }
                $mainTotalJscplOutwardNoPacks = $mainTotalJscplOutwardNoPacks + $outwardJhcplPacks;
                $mainJscplOutwardRecord->NoPacks = $outwardJhcplPacks;
            }
            // end
            $mainOutletInwardRecordss = DB::select("select dd.* from ( SELECT own.Id as OutwardNumberId, concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber, trop.PartyId, p.Name as Outlet, DATE_FORMAT(own.created_at ,'%Y-%m-%d') as CreatedAt, DATE_FORMAT(own.created_at,'%d-%m-%Y') as ReceivedDate from outwardnumber own inner join outward o on o.OutwardNumberId=own.Id inner join transportoutwardpacks trop on trop.OutwardId=o.Id inner join financialyear fn on fn.Id=own.FinancialYearId inner join party p on p.Id=trop.PartyId) as dd WHERE dd.CreatedAt='" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");
            $collectionInwards = collect($mainOutletInwardRecordss);
            $mainOutletInwardRecords = $collectionInwards->unique()->values()->all();
            $maintotalInwardNoPacks = 0;
            $maintotalInwardNoPackss = 0;
            foreach ($mainOutletInwardRecords as $mainOutletInwardRecord) {
                $outwards = DB::select("select NoPacks from outward where OutwardNumberId = " . $mainOutletInwardRecord->OutwardNumberId);
                $InPacks = 0;
                foreach ($outwards as $outward) {
                    if (strpos($outward->NoPacks, ',') != false) {
                        $InPacks = $InPacks + array_sum(explode(',', $outward->NoPacks));
                    } else {
                        $InPacks = $InPacks + (int) $outward->NoPacks;
                    }
                }
                $maintotalInwardNoPackss = $maintotalInwardNoPackss + $InPacks;
                $mainOutletInwardRecord->NoPacks = $InPacks;
            }
            $maintotalInwardNoPacks = $maintotalInwardNoPacks + $maintotalInwardNoPackss + $mainTotalJscplOutwardNoPacks;
            $ImportRec = DB::select("select * from (select trop.Id, a.ArticleNumber, p.Name as Outlet, trop.ArticleId, trop.PartyId, trop.OutwardId, DATE_FORMAT(trop.CreatedDate, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(trop.CreatedDate, '%d-%m-%Y') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId inner join party p on p.Id=trop.PartyId) as dd where dd.CreatedAt='" . $RangeDate . "' and dd.PartyId='" . $PartyId . "' and dd.OutwardId=0");
            $collectionImports = collect($ImportRec);
            $mainImportRecords = $collectionImports->unique()->values()->all();
            $mainTotalImportNoPacks = 0;
            foreach ($mainImportRecords as $mainImportRecord) {
                $imports = DB::select("select NoPacks from transportoutwardpacks where Id=" . $mainImportRecord->Id);
                $importNoPacks = 0;
                foreach ($imports as $import) {
                    if (strpos($import->NoPacks, ',') != false) {
                        $importNoPacks = $importNoPacks + array_sum(explode(',', $import->NoPacks));
                    } else {
                        $importNoPacks = $importNoPacks + (int) $import->NoPacks;
                    }
                }
                $mainTotalImportNoPacks = $mainTotalImportNoPacks + $importNoPacks;
                $mainImportRecord->NoPacks = $importNoPacks;
            }
            $OutwardRec = DB::select("select dd.* from (select p.Name as Outlet, pp.Name as Party, otn.Id as OutletNumberId, otn.PartyId, concat(otn.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutletNumber, DATE_FORMAT(ot.created_at, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(ot.created_at, '%d-%m-%Y') as OutletDate from outletnumber otn inner join outlet ot on ot.OutletNumberId=otn.Id inner join financialyear fn on fn.Id=otn.FinancialYearId inner join party p on p.Id=otn.PartyId inner join party pp on pp.Id=otn.OutletPartyId) as dd where dd.PartyId='" . $PartyId . "' and dd.CreatedAt='" . $RangeDate . "' ");
            $collectionOutwards = collect($OutwardRec);
            $mainOutwardRecords = $collectionOutwards->unique()->values()->all();
            $mainTotalOutwardNoPacks = 0;
            $mainTotalOutwardNoPackss = 0;
            foreach ($mainOutwardRecords as $mainOutwardRecord) {
                $outwards = DB::select("select *  from (select o.NoPacks, DATE_FORMAT(o.created_at,'%Y-%m-%d') as CreatedAt, o.OutletNumberId from outlet o) as dd where dd.CreatedAt='" . $RangeDate . "' and dd.OutletNumberId='" . $mainOutwardRecord->OutletNumberId . "'");
                $outwardPacks = 0;
                foreach ($outwards as $outward) {
                    if (strpos($outward->NoPacks, ',') != false) {
                        $outwardPacks = $outwardPacks + array_sum(explode(',', $outward->NoPacks));
                    } else {
                        $outwardPacks = $outwardPacks + (int) $outward->NoPacks;
                    }
                }
                $mainTotalOutwardNoPackss = $mainTotalOutwardNoPackss + $outwardPacks;
                $mainOutwardRecord->NoPacks = $outwardPacks;
            }
            $mainTotalOutwardNoPacks = $mainTotalOutwardNoPackss + $mainTotalJscplOutwardNoPacks;
            $purchasereturnRecordsss = DB::select("select * from (select srn.Id as SalesReturnNumberId,p.Name as Outlet, srn.PartyId,concat(srn.SalesReturnNumber,'/', fn.StartYear, '-', fn.EndYear) as SalesReturnNumber, DATE_FORMAT(srn.CreatedDate, '%Y-%m-%d') as CreatedAt, DATE_FORMAT(srn.CreatedDate, '%d-%m-%Y') as CreatedDate from salesreturnnumber srn inner join party p on p.Id=srn.PartyId inner join financialyear fn on fn.Id=srn.FinancialYearId) as dd where dd.CreatedAt='" . $RangeDate . "' and dd.PartyId='" . $PartyId . "' ");
            $mainTotalPurchaseReturnNoPacks = 0;
            $purchasereturnRecordsssssss = collect($purchasereturnRecordsss);
            $purchasereturnRecords = $purchasereturnRecordsssssss->unique()->values()->all();
            foreach ($purchasereturnRecords as $purchasereturnRecord) {
                $purchaseReturns = DB::select("select NoPacks from salesreturn where SalesReturnNumber=" . $purchasereturnRecord->SalesReturnNumberId);
                $purchasereturnPacks = 0;
                foreach ($purchaseReturns as $purchaseReturn) {
                    if (strpos($purchaseReturn->NoPacks, ',') != false) {
                        $purchasereturnPacks = $purchasereturnPacks + array_sum(explode(',', $purchaseReturn->NoPacks));
                    } else {
                        $purchasereturnPacks = $purchasereturnPacks + (int) $purchaseReturn->NoPacks;
                    }
                }
                $mainTotalPurchaseReturnNoPacks = $mainTotalPurchaseReturnNoPacks + $purchasereturnPacks;
                $purchasereturnRecord->NoPacks = $purchasereturnPacks;
            }
            $salesReturnRecords = DB::select("select * from (select srn.Id as SalesReturnNumberId ,concat(srn.SalesReturnNumber,'/', fn.StartYear, '-', fn.EndYear) as SalesReturnNumber, srn.OutletPartyId, DATE_FORMAT(srn.CreatedDate ,'%Y-%m-%d') as CreatedAt , DATE_FORMAT(srn.CreatedDate ,'%d/%m%/%Y') as CreatedDate, p.Name as Outlet, pp.Name as Party from outletsalesreturnnumber srn inner join party p on srn.OutletPartyId=p.Id inner join party pp on pp.Id=srn.PartyId inner join financialyear fn on fn.Id=srn.FinancialYearId) as dd where dd.CreatedAt ='" . $RangeDate . "' and dd.OutletPartyId='" . $PartyId . "' ");
            $mainTotalSalesReturnNoPacks = 0;
            foreach ($salesReturnRecords as $salesReturnRecord) {
                $salesReturns = DB::select("select NoPacks from outletsalesreturn where SalesReturnNumber=" . $salesReturnRecord->SalesReturnNumberId);
                $salesreturnPacks = 0;
                foreach ($salesReturns as $salesReturn) {
                    if (strpos($salesReturn->NoPacks, ',') != false) {
                        $salesreturnPacks = $salesreturnPacks + array_sum(explode(',', $salesReturn->NoPacks));
                    } else {
                        $salesreturnPacks = $salesreturnPacks + (int) $salesReturn->NoPacks;
                    }
                }
                $mainTotalSalesReturnNoPacks = $mainTotalSalesReturnNoPacks + $salesreturnPacks;
                $salesReturnRecord->NoPacks = $salesreturnPacks;
            }
            // end one day stock
            // start closing stock
            $afterInwards = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, a.CategoryId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId inner join outward o on o.Id=trop.OutwardId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");
            $afterImports = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, a.CategoryId, trop.OutwardId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId) as dd where dd.OutwardId=0 and dd.CreatedDate <= '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");
            $afterOutwards = DB::select("select * from (select o.NoPacks, o.ArticleId, otn.PartyId, a.CategoryId, DATE_FORMAT(o.created_at,'%Y-%m-%d') as OutletDate from outlet o inner join article a on a.Id=o.ArticleId inner join outletnumber otn on otn.Id=o.OutletNumberId) as dd where dd.OutletDate <= '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");
            //  if ($PartyId == 3) {
            //  $afterPurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join article a on a.Id=sr.ArticleId inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.CreatedDate >= '2022-01-01' and dd.PartyId='" . $PartyId . "'");
            //  } else {
            //      $afterPurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join article a on a.Id=sr.ArticleId inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");
            //  }
            $afterPurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.CreatedDate > '2021-12-31' and dd.PartyId='" . $PartyId . "' ");

            // $afterPurchaseReturns = DB::select("SELECT salesreturn.ArticleId, article.CategoryId, salesreturnnumber.PartyId, salesreturn.NoPacks, DATE_FORMAT(salesreturn.CreatedDate, '%Y-%m-%d') as CreatedDate
            //     FROM salesreturn inner join salesreturnnumber on salesreturnnumber.Id=salesreturn.SalesReturnNumber inner join article on article.Id=salesreturn.ArticleId
            //     WHERE EXISTS
            //       (SELECT *
            //        FROM   transportoutwardpacks
            //        WHERE  transportoutwardpacks.ArticleId = salesreturn.ArticleId and salesreturnnumber.PartyId='" . $PartyId . "' and CreatedDate <= '" . $RangeDate . "')");
            $afterSalesReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, sr.OutletPartyId as PartyId from outletsalesreturn sr inner join article a on a.Id=sr.ArticleId inner join outletsalesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "'");
            $closingStock = 0;
            $closingInwardPacks = 0;
            $closingImportPacks = 0;
            $closingOutwardPacks = 0;
            $closingSalesReturnPacks = 0;
            $closingPurchaseReturnPacks = 0;
            foreach ($afterInwards as $afterInward) {
                if (strpos($afterInward->NoPacks, ',') != false) {
                    $closingInwardPacks = $closingInwardPacks + array_sum(explode(',', $afterInward->NoPacks));
                } else {
                    $closingInwardPacks = $closingInwardPacks + (int) $afterInward->NoPacks;
                }
            }

            foreach ($afterImports as $afterImport) {
                if (strpos($afterImport->NoPacks, ',') != false) {
                    $closingImportPacks = $closingImportPacks + array_sum(explode(',', $afterImport->NoPacks));
                } else {
                    $closingImportPacks = $closingImportPacks + (int) $afterImport->NoPacks;
                }
            }
            foreach ($afterOutwards as $afterOutward) {
                if (strpos($afterOutward->NoPacks, ',') != false) {
                    $closingOutwardPacks = $closingOutwardPacks + array_sum(explode(',', $afterOutward->NoPacks));
                } else {
                    $closingOutwardPacks = $closingOutwardPacks + (int) $afterOutward->NoPacks;
                }
            }
            foreach ($afterSalesReturns as $afterSalesReturn) {
                if (strpos($afterSalesReturn->NoPacks, ',') != false) {
                    $closingSalesReturnPacks = $closingSalesReturnPacks + array_sum(explode(',', $afterSalesReturn->NoPacks));
                } else {
                    $closingSalesReturnPacks = $closingSalesReturnPacks + (int) $afterSalesReturn->NoPacks;
                }
            }
            foreach ($afterPurchaseReturns as $afterPurchaseReturn) {
                if (strpos($afterPurchaseReturn->NoPacks, ',') != false) {
                    $closingPurchaseReturnPacks = $closingPurchaseReturnPacks + array_sum(explode(',', $afterPurchaseReturn->NoPacks));
                } else {
                    $closingPurchaseReturnPacks = $closingPurchaseReturnPacks + (int) $afterPurchaseReturn->NoPacks;
                }
            }
            $closingStock = $closingStock + $closingInwardPacks + $closingImportPacks + $closingSalesReturnPacks - $closingOutwardPacks - $closingPurchaseReturnPacks;
            // end closing stock
            // category wise opening stock
            $categories = Category::get();
            $CatClosingStock = [];
            foreach ($categories as $category) {
                $afterInwardss = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, a.CategoryId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId inner join outward o on o.Id=trop.OutwardId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.PartyId='" . $PartyId . "' and dd.CategoryId='" . $category->Id . "'");
                $afterImports = DB::select("select * from (select trop.NoPacks, trop.PartyId, trop.ArticleId, a.CategoryId, trop.OutwardId, DATE_FORMAT(trop.CreatedDate,'%Y-%m-%d') as CreatedDate from transportoutwardpacks trop inner join article a on a.Id=trop.ArticleId) as dd where dd.OutwardId=0 and dd.CreatedDate <= '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "' and dd.PartyId='" . $PartyId . "'");
                $afterOutwards = DB::select("select * from (select o.NoPacks, o.ArticleId, otn.PartyId, a.CategoryId, DATE_FORMAT(o.created_at,'%Y-%m-%d') as OutletDate from outlet o inner join article a on a.Id=o.ArticleId inner join outletnumber otn on otn.Id=o.OutletNumberId) as dd where dd.OutletDate <= '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "' and dd.PartyId='" . $PartyId . "'");
                // if ($PartyId == 3) {
                // $afterPurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join article a on a.Id=sr.ArticleId inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.CreatedDate >= '2022-01-01' and dd.CategoryId='" . $category->Id . "' and dd.PartyId='" . $PartyId . "'");
                // } else {
                //     $afterPurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join article a on a.Id=sr.ArticleId inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "' and dd.PartyId='" . $PartyId . "'");
                // }
                $afterPurchaseReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, srn.PartyId from salesreturn sr inner join salesreturnnumber srn on srn.Id=sr.SalesReturnNumber inner join article a on a.Id=sr.ArticleId) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.CreatedDate > '2021-12-31' and dd.PartyId='" . $PartyId . "' and dd.CategoryId='" . $category->Id . "'");

                // $afterPurchaseReturns = DB::select("SELECT salesreturn.ArticleId, article.CategoryId, salesreturnnumber.PartyId, salesreturn.NoPacks, DATE_FORMAT(salesreturn.CreatedDate, '%Y-%m-%d') as CreatedDate
                // FROM salesreturn inner join salesreturnnumber on salesreturnnumber.Id=salesreturn.SalesReturnNumber inner join article on article.Id=salesreturn.ArticleId
                // WHERE EXISTS
                //   (SELECT *
                //    FROM   transportoutwardpacks
                //    WHERE  transportoutwardpacks.ArticleId = salesreturn.ArticleId and salesreturnnumber.PartyId='" . $PartyId . "' and CreatedDate <= '" . $RangeDate . "' and article.CategoryId='" . $category->Id . "')");
                $afterSalesReturns = DB::select("select * from (select sr.NoPacks, sr.ArticleId, a.CategoryId, DATE_FORMAT(sr.CreatedDate,'%Y-%m-%d') as CreatedDate, sr.OutletPartyId as PartyId from outletsalesreturn sr inner join article a on a.Id=sr.ArticleId inner join outletsalesreturnnumber srn on srn.Id=sr.SalesReturnNumber) as dd where dd.CreatedDate <= '" . $RangeDate . "' and dd.CategoryId='" . $category->Id . "' and dd.PartyId='" . $PartyId . "'");
                $innwardClosingStock = 0;
                $totalInnwardClosingStock = 0;
                $importClosingStock = 0;
                $outwardClosingStock = 0;
                $purchaseReturnClosingStock = 0;
                $salesReturnClosingStock = 0;
                foreach ($afterInwardss as $CatWiseafterInward) {
                    if (strpos($CatWiseafterInward->NoPacks, ',') != false) {
                        $innwardClosingStock = $innwardClosingStock + array_sum(explode(',', $CatWiseafterInward->NoPacks));
                    } else {
                        $innwardClosingStock = $innwardClosingStock + (int) $CatWiseafterInward->NoPacks;
                    }
                }
                foreach ($afterImports as $afterImport) {
                    if (strpos($afterImport->NoPacks, ',') != false) {
                        $importClosingStock = $importClosingStock + array_sum(explode(',', $afterImport->NoPacks));
                    } else {
                        $importClosingStock = $importClosingStock + (int) $afterImport->NoPacks;
                    }
                }
                foreach ($afterOutwards as $afterOutward) {
                    if (strpos($afterOutward->NoPacks, ',') != false) {
                        $outwardClosingStock = $outwardClosingStock + array_sum(explode(',', $afterOutward->NoPacks));
                    } else {
                        $outwardClosingStock = $outwardClosingStock + (int) $afterOutward->NoPacks;
                    }
                }
                foreach ($afterPurchaseReturns as $afterPurchaseReturn) {
                    if (strpos($afterPurchaseReturn->NoPacks, ',') != false) {
                        $purchaseReturnClosingStock = $purchaseReturnClosingStock + array_sum(explode(',', $afterPurchaseReturn->NoPacks));
                    } else {
                        $purchaseReturnClosingStock = $purchaseReturnClosingStock + (int) $afterPurchaseReturn->NoPacks;
                    }
                }
                foreach ($afterSalesReturns as $afterSalesReturn) {
                    if (strpos($afterSalesReturn->NoPacks, ',') != false) {
                        $salesReturnClosingStock = $salesReturnClosingStock + array_sum(explode(',', $afterSalesReturn->NoPacks));
                    } else {
                        $salesReturnClosingStock = $salesReturnClosingStock + (int) $afterSalesReturn->NoPacks;
                    }
                }
                $mainClosing = 0;
                $mainClosing = $mainClosing + $innwardClosingStock + $importClosingStock + $salesReturnClosingStock - $outwardClosingStock - $purchaseReturnClosingStock;




                $mainClosingd = 0;
                $styles = DB::select("SELECT COUNT(`Id`) AS total_sum FROM article WHERE CategoryId = '" . $category->Id . "'");
                if ($styles > 0) {
                    $mainClosingd = $styles[0]->total_sum;
                } else {
                    $mainClosingd = '0';
                }

                array_push($CatClosingStock, ["title" => $category->Title, "stock" => $mainClosing, "style" => $mainClosingd]);
                // array_push($CatClosingStock, [$category->Title => $mainClosing]);
            }
            // end category wise opening stock 
            return [
                'orgOutletName' => $orgOutletName,
                'outletOpeningCategorywise' => $CatOpeningStock,
                'outletClosingCategorywise' => $CatClosingStock,
                'Categories' => $categories,
                'outletOpeningStock' => $openingStock,
                'TotalStyle' => $styleSum,
                'outletClosingStock' => $closingStock,
                'allOutletData' => [
                    'inwardData' => $mainOutletInwardRecords,
                    'totalInwardPacks' => $maintotalInwardNoPacks,
                    'importData' => $mainImportRecords,
                    'totalImportPacks' => $mainTotalImportNoPacks,
                    'outwardData' => $mainOutwardRecords,
                    'totalOutwardPacks' => $mainTotalOutwardNoPacks,
                    'jhsploutwardData' => $mainJscplOutwardRecords,
                    'salesReturnData' => $salesReturnRecords,
                    'totalSalesReturnPacks' => $mainTotalSalesReturnNoPacks,
                    'purchaseReturnData' => $purchasereturnRecords,
                    'totalPurchaseReturnPacks' => $mainTotalPurchaseReturnNoPacks
                ],
                'allOutletDataCat' => [
                    'outletInwardCategoryWise' => $outletInwardCategoryWise,
                    'totalCatInwardPacks' => $totalCatInwardPacks + $totalCatJhcplOutwardPacks,
                    'outletImportCategoryWise' => $outletImportCategoryWise,
                    'totalCatImportPacks' => $totalCatImportPacks,
                    'outletOutwardCategoryWise' => $outletOutwardCategoryWise,
                    'totalCatOutwardPacks' => $totalCatOutwardPacks + $totalCatJhcplOutwardPacks,
                    'outletSalesReturnCategoryWise' => $outletSalesReturnCategoryWise,
                    'totalCatSRPacks' => $totalCatSRPacks,
                    'outletPurchaseReturnCategoryWise' => $outletPurchaseReturnCategoryWise,
                    'totalCatPRPacks' => $totalCatPRPacks,
                ]
            ];
        }
    }

    public function getOutwardAlldata(Request $request)
    {
        $data = $request->all();
        $outlets = $data['OutletRec'];
        $idsearch = "";
        $count = 0;
        $countOutlets = count($outlets);
        foreach ($outlets as $outlet) {
            if ($count + 1 == $countOutlets) {
                $idsearch = $idsearch . "d.OPartyId=" . $outlet['Id'];
            } else {
                $idsearch = $idsearch . "d.OPartyId=" . $outlet['Id'] . " " . "or ";
            }
            $count = $count + 1;
        }
        if (count($data['OutletRec']) == 0) {
            $vnddata = DB::select("SELECT d.* from (SELECT o.Id ,DATE_FORMAT(o.created_at , '%Y-%m-%d') as CheckDate ,spuser.PartyId ,spuser.Name as OutletName ,  o.NoPacks , own.Id as OutwardNumberId , concat(own.OutwardNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber ,concat(IFNULL(spuser.Name,suser.Name),son.SoNumber, '/',fn1.StartYear,'-',fn1.EndYear) as SoNumber , c.Title as Category ,sc.Name as SubCategory ,rs.SeriesName as Series ,a.ArticleNumber ,p.Name as PartyName,o.OutwardRate as Rate , own.Discount ,own.Remarks ,p.Country ,p.State,p.City , p.PinCode ,spuser.Name as SalesPerson FROM outward o inner join article a on a.Id=o.ArticleId inner join party p on p.Id=o.PartyId inner join category c on c.Id=a.CategoryId inner join  subcategory sc on sc.Id=a.SubCategoryId inner join rangeseries rs on rs.Id=a.SeriesId inner join outwardnumber own on o.OutwardNumberId=own.Id inner join sonumber son on son.Id=own.SoId left join party ps on ps.Id=son.PartyId left join users spuser on spuser.Id=ps.UserId inner join financialyear fn1 on fn1.Id=son.FinancialYearId inner join users suser on suser.Id=son.UserId inner join financialyear fn on fn.Id=own.FinancialYearId) as d where d.CheckDate >= '" . $data['RangeStartDate'] . "' and  d.CheckDate <= '" . $data['RangeEndDate'] . "'");
        } else {
            $vnddata = DB::select("SELECT d.* from ((SELECT o.Id ,op.Id as OPartyId ,op.Name as Outlet , DATE_FORMAT(o.created_at , '%Y-%m-%d') as CheckDate ,op.Name as OutletName ,o.NoPacks ,otn.Id as OutletNumberId ,concat(otn.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber ,'' as SoNumber ,c.Title as Category, sc.Name as SubCategory ,rs.SeriesName as Series, a.ArticleNumber ,p.Name as PartyName ,o.ArticleRate as Rate ,otn.Discount,'' as Remarks,p.Country ,p.State ,p.City ,p.PinCode FROM outlet o inner join outletnumber otn on otn.Id=o.OutletNumberId left join party p on p.Id=otn.OutletPartyId left join party op on op.Id=otn.PartyId inner join financialyear fn on fn.Id=otn.FinancialYearId inner join article a on a.Id=o.ArticleId inner join category c on c.Id=a.CategoryId inner join subcategory sc on sc.Id=a.SubCategoryId inner join rangeseries rs on rs.Id=a.SeriesId) UNION (SELECT outward.Id ,p.Id as OPartyId ,p.Name as Outlet , DATE_FORMAT(oln.CreatedDate , '%Y-%m-%d') as  CheckDate ,p.Name as OutletName ,outward.NoPacks, oln.Id as OutletNumberId, concat(oln.OutletNumber, '/',fn.StartYear,'-',fn.EndYear) as OutwardNumber ,'' as SoNumber ,c.Title as Category, sc.Name as SubCategory ,rs.SeriesName as Series, a.ArticleNumber ,pp.Name as PartyName ,outward.OutwardRate as Rate ,outwardnumber.Discount ,outwardnumber.Remarks,pp.Country ,pp.State ,pp.City ,pp.PinCode FROM outletnumber oln left join outwardnumber on oln.OutwardnumberId=outwardnumber.Id left join outward on outwardnumber.Id=outward.OutwardNumberId inner join article a on a.Id=outward.ArticleId  left join party p on p.Id=oln.PartyId left join party pp on pp.Id=oln.OutletPartyId inner join financialyear fn on fn.Id=oln.FinancialYearId inner join category c on c.Id=a.CategoryId inner join subcategory sc on sc.Id=a.SubCategoryId inner join rangeseries rs on rs.Id=a.SeriesId where oln.OutwardnumberId IS NOT NULL)) as d where (d.CheckDate >= '" . $data['RangeStartDate'] . "' and  d.CheckDate <= '" . $data['RangeEndDate'] . "') and " . $idsearch);
        }
        foreach ($vnddata as $vnd) {
            if (strpos($vnd->NoPacks, ',') !== false) {
                $totalpacks = array_sum(explode(',', $vnd->NoPacks));
                $vnd->Quantity = $totalpacks;
                $vnd->BillAmount = $totalpacks * (int) $vnd->Rate;
            } else {
                $vnd->Quantity = $vnd->NoPacks;
                $vnd->BillAmount = (int) $vnd->NoPacks * (int) $vnd->Rate;
            }
            if (count($data['OutletRec']) == 0) {
                if (!is_null($vnd->PartyId)) {
                    if ($vnd->PartyId != 0) {
                        $vnd->Outlet = $vnd->OutletName;
                    } else {
                        $vnd->Outlet = 'DIRECT';
                    }
                } else {
                    $vnd->Outlet = 'DIRECT';
                }
            }
            unset($vnd->Id);
            unset($vnd->PartyId);
            unset($vnd->OutwardNumberId);
            unset($vnd->OutletName);
            unset($vnd->CheckDate);
        }
        return $vnddata;
    }

    // public function saveOutwardChallan(Request $request)
    // {
    //     $string = Str::random(10);
    //     $file = $request->file('fileoc');
    //     $outwardNumId = $request->outwardNumId;
    //     // OutwardNumber ::where('Id')
    //     $outwardNumberRec =  DB::table('outwardnumber')->where('Id', $outwardNumId)->first();
    //     if($outwardNumberRec->PDF === null){
    //         $file->move("uploads/challans/", $string."-outward-". $outwardNumberRec->OutwardNumber. ".pdf");
    //     }
    //     // $string = (string) Str::uuid();

    //     return ["string" => $string, "url" => "uploads/challans/", $string."-outward-". $outwardNumberRec->OutwardNumber. ".pdf", "status" => "success"];
    // }
    public function saveOutwardChallan(Request $request)
    {
        $todayDate = Carbon::now()->format('d-m-Y');
        $file = $request->file('fileoc');
        $outwardNumId = $request->outwardNumId;
        // OutwardNumber ::where('Id')
        $outwardNumberRec = DB::table('outwardnumber')->where('Id', $outwardNumId)->first();
        $string = Str::random(16);
        if ($outwardNumberRec->Pdf === null) {

            OutwardNumber::where('Id', $outwardNumberRec->Id)->update([
                'Pdf' => $string . "_outward_" . $outwardNumberRec->OutwardNumber . ".pdf"
            ]);
            $file->move("uploads/" . $todayDate . "/", "$string" . "_outward_" . "$outwardNumberRec->OutwardNumber" . ".pdf");
            return ["url" => "uploads/" . $todayDate . "/" . "$string" . "_outward_" . "$outwardNumberRec->OutwardNumber" . ".pdf", "status" => "success"];
        } else {
            if (file_exists(public_path() . '/uploads' . '/' . $todayDate . '/' . $outwardNumberRec->Pdf)) {
                return ["url" => "uploads/" . $todayDate . "/" . $outwardNumberRec->Pdf, "status" => "success"];
            } else {
                OutwardNumber::where('Id', $outwardNumberRec->Id)->update([
                    'Pdf' => $string . "_outward_" . $outwardNumberRec->OutwardNumber . ".pdf"
                ]);
                $file->move("uploads/" . $todayDate . "/", "$string" . "_outward_" . "$outwardNumberRec->OutwardNumber" . ".pdf");
                return ["url" => "uploads/" . $todayDate . "/" . "$string" . "_outward_" . "$outwardNumberRec->OutwardNumber" . ".pdf", "status" => "success"];
            }
        }
    }
}