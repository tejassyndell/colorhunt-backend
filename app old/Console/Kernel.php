<?php

namespace App\Console;

use App\Article;
use App\Inward;
use App\Outward;
use App\OutwardNumber;
use App\Purchasereturns;
use App\Salesreturn;
use App\SO;
use App\Stockshortage;
use App\Stocktransfer;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->call(function () {
         Log::info('Start');
         $outerinwards  = Inward::get();
         $allArticlesIds = [];
         foreach ($outerinwards as $outerinward) {
             $ArticleId =  $outerinward->ArticleId;
             $inwards  = Inward::where('ArticleId', $ArticleId)->get();
             $outwards  = Outward::where('ArticleId', $ArticleId)->get();
             $salesreturns = Salesreturn::where('ArticleId', $ArticleId)->get();
             $purchasereturns = Purchasereturns::where('ArticleId', $ArticleId)->get();
             $consumestocktransfers = Stocktransfer::where('ConsumedArticleId', $ArticleId)->get();
             $transferstocktransfers = Stocktransfer::where('TransferArticleId', $ArticleId)->get();
             $shortedStocks = Stockshortage::where('ArticleId', $ArticleId)->get();
             $sorecords  = SO::where('ArticleId', $ArticleId)->where('Status', 0)->get();
             if (strpos($outerinward->NoPacks, ',') !== false) {
                 $SalesNoPacks = [];
                 foreach (explode(",", $outerinward->NoPacks) as $makearray) {
                     array_push($SalesNoPacks, 0);
                 }
                 for ($i = 0; $i < count(explode(",", $outerinward->NoPacks)); $i++) {
                     foreach ($inwards  as $inward) {
                         $noPacks = explode(",", $inward->NoPacks);
                         $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
                     }
                     foreach ($salesreturns  as $salesreturn) {
                         $noPacks = explode(",", $salesreturn->NoPacks);
                         $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
                     }
                     foreach ($outwards  as $outward) {
                         $noPacks = explode(",", $outward->NoPacks);
                         $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                     }
                     foreach ($purchasereturns  as $purchasereturn) {
                         $noPacks = explode(",", $purchasereturn->ReturnNoPacks);
                         $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                     }
                     foreach ($consumestocktransfers  as $stocktransfer) {
                         $noPacks = explode(",", $stocktransfer->ConsumedNoPacks);

                         $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                     }
                     foreach ($transferstocktransfers  as $stocktransfer) {
                         $noPacks = explode(",", $stocktransfer->TransferNoPacks);
                         $SalesNoPacks[$i] = $SalesNoPacks[$i] + $noPacks[$i];
                     }
                     foreach ($shortedStocks  as $shortedStock) {
                         $noPacks = explode(",", $shortedStock->NoPacks);
                         $SalesNoPacks[$i] = $SalesNoPacks[$i] - $noPacks[$i];
                     }
                     if (!empty($sorecords)) {
                         foreach ($sorecords  as $sorecord) {
                             $OutwardNoPacks = explode(",", $sorecord->OutwardNoPacks);
                             $SalesNoPacks[$i] = $SalesNoPacks[$i] - $OutwardNoPacks[$i];
                         }
                     }
                 }
                 $newimplodeSalesNoPacks = implode(",", $SalesNoPacks);
                 $inward = Inward::where('Id', $outerinward->Id)->first();
                 if ($newimplodeSalesNoPacks !== $inward->SalesNoPacks || $newimplodeSalesNoPacks < 0) {
                     array_push($allArticlesIds, $inward->ArticleId);
                 }
                 Inward::where('Id', $outerinward->Id)->update(['SalesNoPacks' => $newimplodeSalesNoPacks]);
             } else {
                 $articlerecord = Article::where('Id', $outerinward->ArticleId)->first();
                 if ($articlerecord) {
                     if ($articlerecord->ArticleOpenFlag == 0) {
                         $SalesNoPacks = 0;
                         foreach ($inwards  as $inward) {
                             $SalesNoPacks = $SalesNoPacks + $inward->NoPacks;
                         }
                         foreach ($salesreturns  as $salesreturn) {
                             $SalesNoPacks = $SalesNoPacks + $salesreturn->NoPacks;
                         }
                         foreach ($outwards  as $outward) {
                             $SalesNoPacks = $SalesNoPacks - $outward->NoPacks;
                         }
                         foreach ($purchasereturns  as $purchasereturn) {
                             $SalesNoPacks = $SalesNoPacks - $purchasereturn->ReturnNoPacks;
                         }
                         foreach ($consumestocktransfers  as $stocktransfer) {
                             $SalesNoPacks = $SalesNoPacks - $stocktransfer->ConsumedNoPacks;
                         }
                         foreach ($transferstocktransfers  as $stocktransfer) {
                             $SalesNoPacks = $SalesNoPacks + $stocktransfer->TransferNoPacks;
                         }
                         foreach ($shortedStocks  as $shortedStock) {
                             $SalesNoPacks = $SalesNoPacks - $shortedStock->NoPacks;
                         }
                         if (!empty($sorecords)) {
                             foreach ($sorecords  as $sorecord) {
                                 $SalesNoPacks = $SalesNoPacks - $sorecord->OutwardNoPacks;
                             }
                         }
                         $inward = Inward::where('Id', $outerinward->Id)->first();
                         if ($SalesNoPacks !== (int)$inward->SalesNoPacks || $SalesNoPacks < 0) {
                             array_push($allArticlesIds, $inward->ArticleId);
                         }
                         Inward::where('Id', $outerinward->Id)->update(['SalesNoPacks' => $SalesNoPacks]);
                     }
                 }
             }
         }
         

        $mixnopacksrecords =  DB::table('mixnopacks')->get();
        foreach ($mixnopacksrecords as $mixnopacksrecord) {
            $totalInward = 0;
            $totalOutwards = 0;
            $inwards  = Inward::where('ArticleId', $mixnopacksrecord->ArticleId)->get();
            $outwards  = Outward::where('ArticleId', $mixnopacksrecord->ArticleId)->get();
            $salesreturns = Salesreturn::where('ArticleId', $mixnopacksrecord->ArticleId)->get();
            $purchasereturns = Purchasereturns::where('ArticleId', $mixnopacksrecord->ArticleId)->get();
            $consumestocktransfers = Stocktransfer::where('ConsumedArticleId', $mixnopacksrecord->ArticleId)->get();
            $transferstocktransfers = Stocktransfer::where('TransferArticleId', $mixnopacksrecord->ArticleId)->get();
            $shortedStocks = Stockshortage::where('ArticleId', $mixnopacksrecord->ArticleId)->get();
            $sorecords  = SO::where('ArticleId', $mixnopacksrecord->ArticleId)->where('Status', 0)->get();
            foreach ($inwards  as $inward) {
                if (strpos($inward->NoPacks, ',') !== false) {
                    $totalInward = $totalInward + array_sum(explode(",", $inward->NoPacks));
                } else {
                    $totalInward = $totalInward + $inward->NoPacks;
                }
            }
            foreach ($salesreturns  as $salesreturn) {
                if (strpos($salesreturn->NoPacks, ',') !== false) {
                    $totalInward = $totalInward + array_sum(explode(",", $salesreturn->NoPacks));
                } else {
                    $totalInward = $totalInward + $salesreturn->NoPacks;
                }
            }
            foreach ($outwards  as $outward) {
                if (strpos($outward->NoPacks, ',') !== false) {
                    $totalOutwards = $totalOutwards + array_sum(explode(",", $outward->NoPacks));
                } else {
                    $totalOutwards = $totalOutwards + $outward->NoPacks;
                }
            }
            foreach ($purchasereturns  as $purchasereturn) {
                if (strpos($purchasereturn->ReturnNoPacks, ',') !== false) {
                    $totalOutwards = $totalOutwards + array_sum(explode(",", $purchasereturn->ReturnNoPacks));
                } else {
                    $totalOutwards = $totalOutwards + $purchasereturn->ReturnNoPacks;
                }
            }
            foreach ($consumestocktransfers  as $stocktransfer) {
                if (strpos($stocktransfer->ConsumedNoPacks, ',') !== false) {
                    $totalOutwards = $totalOutwards + array_sum(explode(",", $stocktransfer->ConsumedNoPacks));
                } else {
                    $totalOutwards = $totalOutwards + $stocktransfer->ConsumedNoPacks;
                }
            }
            foreach ($transferstocktransfers  as $stocktransfer) {
                if (strpos($stocktransfer->TransferNoPacks, ',') !== false) {
                    $totalInward = $totalInward + array_sum(explode(",", $stocktransfer->TransferNoPacks));
                } else {
                    $totalInward = $totalInward + $stocktransfer->TransferNoPacks;
                }
            }
            foreach ($shortedStocks  as $shortedStock) {
                if (strpos($shortedStock->NoPacks, ',') !== false) {
                    $totalOutwards = $totalOutwards + array_sum(explode(",", $shortedStock->NoPacks));
                } else {
                    $totalOutwards = $totalOutwards + $shortedStock->NoPacks;
                }
            }
            if (!empty($sorecords)) {
                foreach ($sorecords  as $sorecord) {
                    if (strpos($sorecord->NoPacks, ',') !== false) {
                        $totalOutwards = $totalOutwards + array_sum(explode(",", $sorecord->OutwardNoPacks));
                    } else {
                        $totalOutwards = $totalOutwards + $sorecord->OutwardNoPacks;
                    }
                }
            }
            $TotalRemaining =  $totalInward - $totalOutwards;
            if ($mixnopacksrecord->NoPacks != $TotalRemaining) {
                DB::table('mixnopacks')->where('ArticleId' , $mixnopacksrecord->ArticleId)->update(['NoPacks'=>$TotalRemaining]);
            }
          
        }
    
         
         
         
         
         
        Log::info('Working');
            
        
            
        })->dailyAt('01:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
