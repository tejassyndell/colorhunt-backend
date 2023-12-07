<?php

namespace App\Exports;

use App\Report;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class ReportAllStocksExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    /* 
	public function __construct(int $SummaryId)
    {
        $this->SummaryId = $SummaryId;
    }
	*/
	
	public function collection()
    {
        return $data = DB::select("select dd.ArticleNumber, dd.TotalNoPacksStocks, (CASE dd.ArticleOpenFlag When 0 then GetStockArticlePieces(dd.Id) else dd.TotalNoPacksStocks END) as TotalPieces from (SELECT a.Id, a.ArticleNumber, a.ArticleColor, a.ArticleOpenFlag, inw.SalesNoPacks, CountNoPacks(inw.SalesNoPacks) as TotalNoPacksStocks FROM `inward` inw inner join article a on a.Id=inw.ArticleId where a.ArticleOpenFlag=0 HAVING TotalNoPacksStocks > 0 Union SELECT a.Id, a.ArticleNumber, a.ArticleColor, a.ArticleOpenFlag, '', mxn.NoPacks as TotalNoPacksStocks FROM `mixnopacks` mxn inner join article a on a.Id=mxn.ArticleId where a.ArticleOpenFlag=1 HAVING TotalNoPacksStocks > 0) as dd");
    }
	
	public function headings() : array
    {
        return [
            'Id',
            'Article No',
            'Total NoPacks',
            'Total Pieces'    
        ];
    }
}
