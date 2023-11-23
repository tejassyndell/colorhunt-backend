<?php

//use Symfony\Component\Routing\Route;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

//Party API
Route::get('bshow', 'BackupController@index');
Route::get('bcreate', 'BackupController@create');
Route::get('bdownload', 'BackupController@download');
Route::get('videowatermark', 'MasterController@videowatermark');
//Master API Routes
Route::get('master', 'MasterController@index');
Route::get('userrole', 'MasterController@GetRole');
//Dashboard API
Route::get('getdashboard', 'MasterController@GetDashboard');
//Login API
Route::post('dologin', 'UserManagmentsController@Login');
//Assign Rights API
Route::get('pages', 'MasterController@GetPage');
Route::get('userrights/{id}', 'MasterController@GetUserRights');
Route::get('userrolerights/{id}', 'MasterController@GetUserRoleRights');
Route::get('userrolerightssidebar/{id}', 'MasterController@GetUserRoleRightsSidebar');
Route::get('rolewiseaddupdate/{roleid}/{actid}', 'UserRightsController@GetRoleWiseRights');
//User Managment API
Route::post('adduser', 'UserManagmentsController@store');
Route::get('userlist', 'UserManagmentsController@GetAll');
Route::get('getuseridwise/{id}', 'UserManagmentsController@GetUserIdWise');
Route::delete('deleteuser/{id}/{LoggedId}', 'UserManagmentsController@GetDelete');
Route::put('updateuser/{field}', 'UserManagmentsController@UpdateUser');
// Api Added By Kts
Route::put('updateuserstatus/{id}/{LoggedId}', 'UserManagmentsController@updateUserStatus');
// Api Added By Kts End
//Category API
Route::post('addcategory', 'MasterController@AddCategory');
Route::get('categorylist', 'MasterController@Getcategory');
Route::post('categorypostlist', 'MasterController@Postcategory');
Route::delete('deletecategory/{id}', 'MasterController@Deletecategory');
Route::get('getcatidwise/{id}', 'MasterController@GetcatIdWise');
Route::post('updatecategory', 'MasterController@UpdateCategory');
Route::get('updatecategorystatus/{catid}', 'MasterController@updatecatStatus');

//Beaner API
Route::post('addbeaner', 'MasterController@AddBeaner');
Route::get('beanarlist', 'MasterController@GetBeaner');
Route::post('beanerpostlist', 'MasterController@PostBeaner');
Route::delete('deletebeaner/{id}', 'MasterController@DeleteBeaner');
Route::get('getbeaneridwise/{id}', 'MasterController@GetBeanerIdWise');
Route::post('updatebeaner', 'MasterController@UpdateBeaner');

//Subcategory API

Route::post('addsubcategory', 'MasterController@AddSubcategory');
Route::get('subcategorylist', 'MasterController@GetSubcategory');
Route::post('subcategorypostlist', 'MasterController@PostSubcatgegory');
Route::delete('deletesubcategory/{id}', 'MasterController@DeleteSubcategory');
Route::get('getsubcategoryidwise/{id}', 'MasterController@GetSubcategoryIdWise');
Route::get('getcategoryidwise/{id}', 'MasterController@GetcategoryIdWise');
Route::put('updatesubcategory/{field}', 'MasterController@UpdateSubcategory');
//Range Series API
Route::post('addrangeseries', 'MasterController@AddRangeseries');
Route::get('rangeserieslist', 'MasterController@GetRangeseries');
Route::post('rangeseriespostlist', 'MasterController@Postrangeseries');
Route::delete('deleterangeseries/{id}', 'MasterController@DeleteRangeseries');
Route::get('getrangeseriesidwise/{id}', 'MasterController@GetRangeseriesIdWise');
Route::put('updaterangeseries/{field}', 'MasterController@UpdateRangeseries');
Route::get('getsubcatrangeserieswise/{id}', 'MasterController@GetSubcatRangeseriesWise');
Route::get('getrangeseriesarticle/{catid}/{subcatid}', 'MasterController@getrangeseriesarticle');
//Article API
Route::post('addarticle', 'MasterController@AddArticle');
//Route::get('articlelist', 'MasterController@articlelist');
Route::post('articlepostlist', 'MasterController@Postarticle');
Route::delete('deletearticle/{id}/{LoggedId}', 'MasterController@DeleteArticle');
Route::delete('deletelaunchedarticle/{id}/{LoggedId}', 'MasterController@DeleteLaunchedArticle');
Route::get('getarticleidwise/{id}', 'MasterController@GetArticleIdWise');
Route::put('updatearticle/{field}', 'MasterController@UpdateArticle');
Route::get('getarticleserial/{id}/{seriesflag}/{categoryId}', 'MasterController@getArticleSerial');
Route::get('getarticlepopending', 'MasterController@getarticlepopending');
Route::get('getarticle_catscatserial/{id}', 'MasterController@getarticle_catscatserial');
Route::get('getartautogenerate/{id}', 'MasterController@getartautogenerate');
//Code Added By Kts
Route::get('getpartialarticle', 'MasterController@getPartialArticle');
Route::get('getpartialoutletarticle', 'MasterController@getPartialOutletArticle');
//Code Added By Kts End
//Artical Color API
Route::post('addcolor', 'MasterController@AddColor');
Route::get('colorlist', 'MasterController@Getcolor');
Route::post('colorpostlist', 'MasterController@Postcolor');
Route::delete('deletecolor/{id}', 'MasterController@DeleteColor');
Route::get('getcoloridwise/{id}', 'MasterController@GetColorIdWise');
Route::put('updatecolor/{field}', 'MasterController@UpdateColor');
//Artical Size API
Route::post('addsize', 'MasterController@Addsize');
Route::get('sizelist', 'MasterController@Getsize');
Route::post('sizepostlist', 'MasterController@Postsize');
Route::delete('deletesize/{id}', 'MasterController@Deletesize');
Route::get('getsizeidwise/{id}', 'MasterController@GetSizeIdWise');
Route::put('updatesize/{field}', 'MasterController@UpdateSize');
//Artical Ratio API
Route::post('addratio', 'MasterController@Addratio');
Route::get('ratiolist', 'MasterController@Getratio');
Route::delete('deleteratio/{id}', 'MasterController@Deleteratio');
Route::get('getratioidwise/{id}', 'MasterController@GetRatioIdWise');
Route::put('updateratio/{field}', 'MasterController@UpdateRatio');
//Brand API
Route::post('addbrand', 'MasterController@AddBrand');
Route::get('brandlist', 'MasterController@Getbrand');
Route::post('brandpostlist', 'MasterController@Postbrand');
Route::delete('deletebrand/{id}', 'MasterController@Deletebrand');
Route::get('getbrandidwise/{id}', 'MasterController@GetBrandIdWise');
Route::put('updatebrand/{field}', 'MasterController@UpdateBrand');

//added aditional code
//added transportaton apis
//Transportation API
Route::post('addtransportation', 'MasterController@AddTransportation');
Route::get('transportationlist', 'MasterController@GetTransportation');
Route::post('transportationpostlist', 'MasterController@PostTransportation');
Route::delete('deletetransportation/{id}', 'MasterController@DeleteTransportation');
Route::get('gettransportationidwise/{id}', 'MasterController@GetTransportationIdWise');
Route::put('updatetransportation/{field}', 'MasterController@UpdateTransportation');

//Rack API
Route::post('addrack', 'MasterController@AddRack');
Route::get('racklist', 'MasterController@Getrack');
Route::delete('deleterack/{id}', 'MasterController@Deleterack');
Route::get('getrackidwise/{id}', 'MasterController@GetRackIdWise');
Route::put('updaterack/{field}', 'MasterController@UpdateRack');
Route::post('checkrackexit', 'MasterController@ChekRackexits');
//User Role API
Route::post('adduserrole', 'MasterController@AddUserRole');
Route::get('userrolelist', 'MasterController@GetUserRole');
Route::delete('deleteuserrole/{id}', 'MasterController@DeleteUserRole');
Route::get('getuserroleidwise/{id}', 'MasterController@GetUserRoleIdWise');
Route::put('updateuserrole/{field}', 'MasterController@UpdateUserRole');
//Vendor API
Route::post('addvendor', 'MasterController@AddVendor');
Route::get('vendorlist', 'MasterController@GeVendor');
Route::post('vendorpostlist', 'MasterController@PostVendor');
Route::delete('deletevendor/{id}', 'MasterController@Deletevendor');
Route::get('getvendoridwise/{id}', 'MasterController@GetVendorIdWise');
Route::put('updatevendor/{field}', 'MasterController@UpdateVendor');
//Party API
Route::post('addparty', 'MasterController@AddParty');
Route::get('partylist', 'MasterController@GeParty');
Route::get('outwardpartylist', 'MasterController@GeOutwardParty');
Route::post('partypostlist', 'MasterController@PostParty');
Route::get('getstorage/{id}/{ColorId}', 'MasterController@getstorage');
Route::get('getsinglestorage/{id}/{ColorId}', 'MasterController@getsinglestorage');
Route::get('getoutletparty/{id}', 'MasterController@GeOutletParty');
Route::get('getoutletpartyoutletreport/{id}', 'MasterController@getoutletpartyoutletreport'); // Nitin
Route::get('getoutletpartyinstockstransfer/{id}', 'MasterController@GeOutletPartyinstocktransfer');//jaimin
Route::get('getoutletpartyforarticalrate/{id}', 'MasterController@GeOutletPartyarticleratechange'); //Jaimin
Route::get('getoutletviewparty/{id}', 'MasterController@getoutletviewparty');
Route::delete('deleteparty/{id}', 'MasterController@Deleteparty');
Route::get('getpartyidwise/{id}', 'MasterController@GetPartyIdWise');
Route::put('updateparty/{field}', 'MasterController@UpdateParty');
Route::get('gstin-verification/{gstNumber}', 'MasterController@verify');
// Api Added By Kts
Route::get('getgeoofparties', 'MasterController@getGeoOfParties');
Route::get('getsalespersons', 'MasterController@getSalesPersons');
Route::get('getallparty', 'MasterController@getAllParty');
Route::get('updatepartystatus/{partyid}', 'MasterController@updatePartyStatus');

// Api Added By Kts End
//Outlet API
Route::get('getarticleofoutlet/{id}', 'OutletsController@GetArticleofOutlet');
Route::get('getoutletsinglearticle/{PartyId}/{ArtId}/{OutletPartyId}', 'OutletsController@GetOutletSingleArticle');
Route::post('addoutlet', 'OutletsController@AddOutlet');
Route::get('outletpartyarticle/{PartyId}/{ArtId}', 'OutletsController@outletpartyarticle');
Route::get('outletarticle/{PartyId}/{ArtId}', 'OutletsController@outletarticle');
Route::get('outletlist/{UserId}', 'OutletsController@OutletList');
Route::post('postoutletlist', 'OutletsController@PostOutletList');
Route::post('outletlistfromotlno/{id}', 'OutletsController@OutletListFromOTLNO');
Route::get('outletdatepartyfromotlno/{id}', 'OutletsController@OutletDatePartyfromOTLNO');
Route::get('getoutletidwise/{id}', 'OutletsController@GetOutletIdWise');
Route::put('updateoutlet/{field}', 'OutletsController@UpdateOutlet');
Route::delete('deleteoutlet/{id}/{ArticleOpenFlag}/{LoggedId}', 'OutletsController@DeleteOutlet');
Route::delete('deleteoutletnumber/{OTLNO}/{LoggedId}', 'OutletsController@DeleteOutleNumber');
Route::get('getoutletchallen/{OTLNO}', 'OutletsController@GetOutletChallen');
Route::put('updatetransportstatus/{OutwardId}', 'OutletsController@UpdateTransportStatus');
Route::get('getoutwardtransport/{PARTYID}', 'OutletsController@getoutwardtransport');
Route::get('intransitlist/{PARTYID}', 'OutletsController@intransitlist');
Route::get('getoutwardpieces/{outwardid}', 'OutletsController@getoutwardpieces');
//Code Added By KTS
Route::post('intransitlistpost', 'OutletsController@intransitlistpost');
Route::post('addoutletsalesreturn', 'OutletsController@AddOutletSalesReturn');
Route::get('getoutletsalesreturn/{id}', 'OutletsController@GetOutletSalesReturn');
Route::post('postoutletsalesreturn', 'OutletsController@PostOutletSalesReturn');
Route::delete('deleteoutletsalesreturn/{id}/{LoggedId}', 'OutletsController@DeleteOutletSalesReturn');
Route::get('getoutletsalesreturnchallan/{id}', 'OutletsController@GetOutletSalesReturnChallan');
//Inward API
Route::post('addinward', 'InwardController@AddInward');
Route::get('inwardlist', 'InwardController@GeInward');
Route::post('inwardpostlist', 'InwardController@PostInward');
Route::post('inwardlistfromgrn/{id}', 'InwardController@InwardListFromGRN');
Route::get('inwarddateremarkfromGRN/{id}', 'InwardController@InwardDateRemarkFromGRN');
Route::delete('deleteinward/{id}/{ArticleId}/{LoggedId}', 'InwardController@Deleteinward');
Route::delete('deleteinwardgrn/{GRN}/{LoggedId}', 'InwardController@DeleteinwardGRN');
Route::post('cancellationinwardgrn', 'InwardController@Cancellationinwardgrn');
Route::get('getinwardidwise/{id}', 'InwardController@GetInwardIdWise');
Route::put('updatinward/{field}', 'InwardController@UpdateInward');
Route::get('getinwardgrn', 'InwardController@GetInwardGRN');
Route::get('geteditarticalidwise/{id}', 'InwardController@GetEditArticalIdWise');
Route::get('getinwardchallen/{id}/{type}', 'InwardController@GetInwardChallen');
Route::get('inwardcolorcheck', 'InwardController@inwardcolorcheck');
//Finanical Year API
Route::post('addfy', 'FinancialyearController@AddFinancialYear');
Route::get('fylist', 'FinancialyearController@GetFinancialYear');
Route::delete('deleteFY/{id}', 'FinancialyearController@DeleteFinancialYear');
Route::get('getFYidwise/{id}', 'FinancialyearController@GetFinancialYearIdWise');
Route::put('updateFY/{field}', 'FinancialyearController@UpdateFinancialYear');
Route::post('checkFYexit', 'FinancialyearController@CheckFinancialYearexits');
//Stock Transfer API
Route::post('addstocktransfer', 'StocktransferController@AddStocktransfer');
Route::get('stocktransferlist/{userid}', 'StocktransferController@Getstocktransfer');
Route::put('updatestocktransfer/{field}', 'StocktransferController@Updatestocktransfer');
Route::post('stocktransferpostlist', 'StocktransferController@PostStocktransfer');
Route::post('stocktransferlistfromstno/{id}', 'StocktransferController@StocktransferListFromSTNO');
Route::get('stockshortagelistfromstno/{id}', 'StocktransferController@StockshortageListFromSTNO');
Route::get('stocktransferfromstno/{id}', 'StocktransferController@StocktransferDateFromSTNO');
Route::delete('deletestocktransfer/{id}/{type}/{LoggedId}', 'StocktransferController@Deletestocktransfer');
//Ourward API
Route::post('addoutward', 'OutwardController@AddOutward');
Route::get('outwardlist/{userid}', 'OutwardController@GeOutward');
Route::post('outwardpostlist', 'OutwardController@PostOutward');
Route::post('outwardlistfromowno/{id}', 'OutwardController@OutwardListFromOWNO');
Route::get('outwarddategstfromowno/{id}', 'OutwardController@OutwardDateGstFromOWNO');
Route::delete('deleteoutward/{id}/{ArticleId}/{LoggedId}', 'OutwardController@Deleteoutward');
Route::delete('deleteoutwardnumber/{OWNO}/{SOID}/{LoggedId}', 'OutwardController@DeleteOWNumber');
Route::get('getoutwardidwise/{id}', 'OutwardController@GetOutwardIdWise');
Route::get('remainingso/{outwarddate}', 'OutwardController@RemainingSO');
Route::get('remainingoutwardso', 'OutwardController@RemainingOutwardSO');
Route::post('remainingpostoutwardso', 'OutwardController@RemainingPostOutwardSO');
Route::get('getsodata/{OWNO}/{id}', 'OutwardController@GetSOData');
Route::put('updateoutward/{field}', 'OutwardController@UpdateOutward');
Route::get('getsoarticledata/{SOId}/{articleld}/{OWID}', 'OutwardController@GetSOArticleData');
Route::get('getoutwardchallen/{id}', 'OutwardController@GetOutwardChallen');
//Stocks
Route::get('getsallstocks', 'ReportController@GetAllStocks');
Route::get('getsrangewiseallstocks/{start}/{end}', 'ReportController@GetRangeWiseAllStocks');
Route::get('getdailystocks/{start}', 'ReportController@GetDailyStocks');
Route::post('postallstocks', 'ReportController@PostAllStocks');
Route::get('exportallstocks', 'ReportController@exportallstocks');
Route::get('reportgetpolist', 'ReportController@GetPolist');
Route::post('reportpostpolist', 'ReportController@PostPoReport');
Route::get('teststock', 'ReportController@teststock');
Route::get('teststockopenflag', 'ReportController@teststockopenflag');
Route::get('reportgetinwardlist', 'ReportController@GetInwardList');
Route::post('reportpostinwardlist', 'ReportController@PostReportInwardList');
Route::get('reportsolist/{id}', 'ReportController@GetSolist');
Route::post('reportpostsolist', 'ReportController@PostReportSolist');
Route::get('getoutletstocks/{PartyId}', 'ReportController@GetOutletStocks');
Route::get('getoutletstocksRefil/{PartyId}', 'ReportController@GetOutletStocksRefil');
//SO API
Route::post('addso', 'SOController@AddSO');
Route::get('solist/{UserId}', 'SOController@GetSO');
Route::post('postsolist', 'SOController@PostSolist');
//Sales return - start
Route::post('srlistfromsronumber/{id}', 'SOController@SrListFromSRONO');
Route::get('srdateremarkfromsrono/{id}', 'SOController@SrDateRemarkFromSRONO');
Route::post('srolistfromsronumber/{id}', 'SOController@SroListFromSRONO');
Route::get('srodateremarkfromsrono/{id}', 'SOController@SroDateRemarkFromSRONO');
//Sales return - end
//Purchase return - start
Route::post('prlistfrompronumber/{id}', 'SOController@PrListFromPRONO');
Route::get('prdateremarkfromprono/{id}', 'SOController@PrDateRemarkFromPRONO');
//Purchase return - end
Route::post('solistfromsonumber/{id}', 'SOController@SoListFromSONO');
Route::get('sodateremarkfromsono/{id}', 'SOController@SoDateRemarkFromSONO');
Route::delete('deleteso/{id}/{ArticleOpenFlag}/{LoggedId}', 'SOController@DeleteSO');
Route::delete('deletesonumber/{SONO}/{LoggedId}', 'SOController@DeleteSONumber');
Route::get('getsoidwise/{id}', 'SOController@GetSoIdWise');
Route::put('updateso/{field}', 'SOController@UpdateSo');
Route::get('remainingarticlelist', 'SOController@GetArticle');
Route::get('remainingarticlelistforoutlet/{id}', 'SOController@GetArticlesOfOutlet'); //Nitin
Route::get('remainingarticlelistsyn', 'SOController@GetArticleSyn'); //Nitin
Route::get('getsonumber/{id}', 'SOController@GenerateSoNumber');
Route::get('getinwardarticledata/{articleld}', 'SOController@GetInwardArticleData');
Route::get('getinwardarticledataso/{userid}/{partyid}/{articleld}', 'SOController@GetInwardArticleDataSO');
Route::get('getsochallen/{id}', 'SOController@GetSoChallen');
Route::get('sodatacheckuserwise/{UserId}/{SONO}', 'SOController@SODataCheckUserWise');
//SO Front API
Route::get('categoryarticlelist/{CategoryId}', 'SOController@CategoryArticleList');
Route::get('articledetails/{ArtId}', 'SOController@ArticleDetails');
Route::post('frontsearchresult', 'MasterController@FrontSearchResult');
Route::post('cartnopackscheck', 'MasterController@CartNopacksCheck');
Route::post('cartplaceorder', 'MasterController@cartplaceorder');
Route::get('admingetuserId', 'MasterController@admingetuserId');
//Article Color reset table
Route::get('resetarticlecolor', 'MasterController@resetarticlecolor');
//Article Search API
Route::post('articlecolorcheck', 'MasterController@articlecolorcheck');
Route::post('articlesearch', 'MasterController@articlesearch');
//Code Added By KTS For Outlet Article Search
Route::post('outletarticlecolorcheck', 'MasterController@outletarticlecolorcheck');
Route::post('outletarticlesearch', 'MasterController@outletarticlesearch');
Route::get('getoutlets/{logged_id}', 'MasterController@getOutlets');
//Code Added By KTS For Outlet Article Search End
//Import CSV
Route::post('importcsv', 'ImportToolController@importcsv');
Route::post('importoutletcsv', 'ImportToolController@importoutletcsv');
//Productlaunch API
Route::post('addproductlaunch', 'ProductlaunchsController@store');
Route::get('approvalproductlist', 'ProductlaunchsController@approvalproductlist');
Route::get('rejectedproductlist', 'ProductlaunchsController@rejectedproductlist');
Route::get('holdproductlist', 'ProductlaunchsController@holdproductlist');
Route::get('rejectionproductlist', 'ProductlaunchsController@rejectionproductlist');
Route::get('launcharticlecheck/{id}', 'ProductlaunchsController@launcharticlecheck');
Route::get('remaininglauncharticle', 'ProductlaunchsController@RemainingLaunchArticle');
//Route::delete('deleteproductlounch/{id}', 'MasterController@DeleteProductLounch');
//Route::post('addproductlaunchnew', 'MasterController@AddProductLaunch');
//SO Status API
Route::get('getparty', 'SOController@getParty');
Route::get('remainingsowithparty/{id}', 'SOController@RemainingSOWithParty');
Route::post('addsostatus', 'SOController@AddSOStatus');
Route::get('sostatuslist/{id}', 'SOController@SOStatusList');
Route::get('getsostatus/{id}', 'SOController@GetSoStatus');
Route::delete('deletesostatus/{id}', 'SOController@DeleteSOStatus');
//Purchase Return API
Route::get('purchasereturnarticle/{id}', 'SOController@PurchaseReturnArticle');
Route::get('purchasereturngetinwardnumber/{VendorId}/{ArticleId}', 'SOController@PurchaseReturn_GetInwardNumber');
Route::get('purcahsereturninwardgetdata/{VendorId}/{ArticleId}/{InwardNumberId}', 'SOController@PurcahseReturn_InwardGetData');
Route::get('getpurchasereturn/{id}', 'SOController@GetPurchaseReturn');
Route::post('postpurchasereturn', 'SOController@PostPurchaseReturn');
Route::get('getsalesreturnchallan/{id}', 'SOController@GetSalesReturnChallan');
Route::get('getpurchasereturnchallan/{id}', 'SOController@GetPurchaseReturnChallan');
Route::post('addpurchasereturn', 'SOController@AddPurchaseReturn');
Route::delete('deletepurchasereturn/{id}/{LoggedId}', 'SOController@DeletePurchaseReturn');
Route::delete('deletepurchasereturnrecord/{id}/{LoggedId}', 'SOController@DeletePurchaseReturnRecord');


//Sales Return API
Route::get('salesreturnoutlet/{id}', 'SOController@SalesReturnOutlet');
Route::get('salesreturnarticle/{id}', 'SOController@SalesReturnArticle');
Route::get('salesreturn_outletarticle/{id}', 'SOController@salesreturn_outletarticle');
Route::get('salesreturngetoutwardnumber/{PartyId}/{ArticleId}/{Type}', 'SOController@SalesReturn_ArticletoOutward');
Route::get('salesreturnoutwardgetdata/{PartyId}/{ArticleId}/{OutwardNumberId}', 'SOController@SalesReturn_OutwardGetData');
Route::get('salesreturnoutletgetdata/{PartyId}/{ArticleId}/{OutwardNumberId}', 'SOController@SalesReturn_OutletGetData');
Route::get('salesreturngetarticledata/{id}', 'SOController@SalesReturnGetArticleData');
Route::post('addsalesreturn', 'SOController@AddSalesReturn');
Route::get('getsalesreturn/{id}', 'SOController@GetSalesReturn');
Route::post('postsalesreturn', 'SOController@PostSalesReturn');
Route::delete('deletesalesreturn/{id}/{LoggedId}', 'SOController@DeleteSalesReturn');
Route::delete('deletesalesreturnrecord/{id}/{LoggedId}', 'SOController@deleteSalesReturnRecord');


//PO
Route::get('checkimage/{id}', 'POController@checkimage');
Route::post('addpo', 'POController@AddPO');
Route::get('polist', 'POController@GePO');
Route::post('popostlist', 'POController@PostPo');
Route::get('getponumber', 'POController@GetPONumber');
Route::delete('deletepo/{id}/{POId}/{ArtId}/{LoggedId}', 'POController@DeletePO');
Route::get('getpoidwise/{id}', 'POController@GetPoIdWise');
Route::post('updatepo', 'POController@UpdatePo');
Route::get('inwardgetpolist/{grn}', 'POController@InwardGetPOList');
Route::get('getpochallen/{id}', 'POController@GetPOChallen');
Route::post('polistfrompon/{id}', 'POController@polistfrompon');
Route::get('podateremarkfromPO/{id}', 'POController@podateremarkfromPO');
//Route::delete('deletepo/{id}/{ArticleId}', 'POController@deletepo');
Route::delete('deletepopon/{PON}/{LoggedId}', 'POController@deletepopon');
//Article Photos API
Route::post('articlephotos', 'MasterController@ArticlePhotos');
Route::get('getarticlephotoslist', 'MasterController@GetArticlePhotos');
Route::delete('deletearticlephoto/{id}/{LoggedId}', 'MasterController@DeleteArticlePhotos');
Route::get('articallist', 'POController@GetArtical');
Route::get('articallistoutlet/{id}', 'POController@GetArticalForOutlet'); //Nitin
//adding aditional code
//adding 2 more apis
Route::delete('/images/{url}', 'MasterController@destroy')->name('images.destroy');

Route::post('update-primary-image', 'MasterController@updatePrimaryImage');
 

//Article Launch
Route::get('withoutopenflagarticallist', 'POController@GetWithoutOpenFlagArtical'); //Nitin-Jaimin
Route::get('getarticaleditdata/{id}', 'POController@getArticalEditData'); //Nitin-Jaimin
Route::get('getslaunchreport', 'ReportController@getslaunchreport'); //Nitin-Jaimin
Route::get('getarticallaunchdata/{id}', 'ReportController@getarticallaunchdata'); //Nitin-Jaimin
Route::post('addarticlelaunch', 'POController@addArticleLaunch'); //Nitin-Jaimin
Route::post('editarticlelaunch', 'POController@editArticleLaunch'); //Nitin-Jaimin
// Route::get('getlaunchreport', 'ReportController@GetlaunchReport'); //Nitin-Jaimin

Route::post('articlelaunchlist', 'POController@articleLaunchList'); //Nitin-Jaimin
//Article Launch



Route::get('approvedarticallist/{id}', 'POController@approvedarticallist');
Route::get('articlerateassignso', 'POController@ArticleRateAssignSO');
Route::get('getarticalidwise/{id}', 'POController@GetArticalIdWise');
Route::get('getarticaldata/{id}', 'POController@GetArticaldata');
Route::get('getanarticaldata/{id}', 'POController@GetAnArticaldata');
Route::post('addarticleratechange', 'POController@AddArticleRateChange');
//Work Order API
Route::post('addworkOrder', 'POController@AddworkOrder');
Route::get('workOrderlist', 'POController@GetworkOrderlist');
Route::post('workOrderPostlist', 'POController@PostworkOrderlist');
Route::delete('deleteworkOrder/{id}', 'POController@DeleteworkOrder');
Route::get('getworkOrderidwise/{id}', 'POController@GetworkOrderidwise');
Route::put('updateworkOrder/{field}', 'POController@UpdateworkOrder');
//Testing API
Route::post('addstudent', 'MasterController@AddStudent');
Route::put('updatestudent/{field}', 'MasterController@UpdateStudent');
Route::delete('deletestudent/{id}', 'MasterController@DeleteStudent');
Route::get('studentlist', 'MasterController@GetStudent');
Route::get('getstudentidwise/{id}', 'MasterController@GetStudentIdWise');

//Rejections API
Route::get('rejectionlist', 'MasterController@GetRejectionList');
Route::post('addrejection', 'MasterController@addRejection');
Route::put('updaterejection/{field}', 'MasterController@UpdateRejection');
Route::get('getrejectidwise/{rej_id}', 'MasterController@GetRejectionIdwise');
Route::delete('deleterejection/{id}', 'MasterController@DeleteRejection');
Route::get('getrejection', 'MasterController@getRejections');


//Bugslisting API
Route::get('bugslist', 'BugsController@getBugs');
Route::get('checksalesretunrduplication', 'BugsController@salesReturnDuplication');
Route::get('checksoduplication', 'BugsController@soDuplication');
Route::get('checkoutwardduplication', 'BugsController@outwardDuplication');
Route::delete('deletesoduplication/{sonumberid}/{articleid}', 'BugsController@deleteSoDuplication');
Route::delete('deleteoutwardduplication/{outwadnumberid}/{articleid}', 'BugsController@deleteOutwardDuplication');
Route::delete('deletesalesreturnduplication/{salesreturnnumberid}/{articleid}/{CreatedDate}', 'BugsController@deleteSalesreturnDuplication');
Route::get('outwardsoremaining', 'BugsController@outwardSoRemaining');
Route::get('fixoutwardsalesremaining/{soid}/{OutwardNoPacksActual}', 'BugsController@fixOutwardSalesremaining');
Route::get('soremaining', 'BugsController@soRemaining');
Route::get('fixsoremaining/{inwardid}/{newsalespacks}', 'BugsController@fixSoremaining');
Route::get('allRemaining', 'BugsController@allRemaining');
Route::get('fixallremaining/{mixid}/{newpacks}', 'BugsController@fixAllremaining');
Route::get('fixallremainingbyonce', 'BugsController@fixAllremainingByOnce');
Route::get('fixsoremainingbyonce', 'BugsController@fixSoRemainingByOnce');
Route::get('fixoutwardsalesremainingbyonce', 'BugsController@fixOutwardSalesremainingByOnce');
Route::get('checkstocktransferduplication', 'BugsController@stocktransferDuplication');
Route::delete('deletestocktaduplication/{stocktransfernumberid}/{consumedarticleid}/{transferarticleid}', 'BugsController@deleteStockTraDuplication');
Route::delete('deletepoduplication/{ponumberid}/{articleid}', 'BugsController@deletePODuplication');
Route::delete('deleteinwardduplication/{GRNId}/{articleid}', 'BugsController@deleteInwardDuplication');


Route::get('checkpoduplication', 'BugsController@poDuplication');
Route::get('checkinwardduplication', 'BugsController@inwardDuplication');

//User Logs
Route::get('userview/{id}', 'UserManagmentsController@ViewUsers');
Route::get('userlogs/{id}/{value}', 'UserManagmentsController@ViewUserLogs');
Route::delete('deleteuserlogs/{id}', 'UserManagmentsController@UserLogsDelete');

//Edit Purchase return id wise
Route::get('getpurchasereturnidwise/{id}', 'POController@getPurchaseReturnIdWise');
Route::post('updatepurchasereturn', 'POController@updatePurchaseReturn');

//Edit Sales Return
Route::get('getsalesreturnidwise/{id}', 'SOController@getsalesreturnIdwise');
Route::post('updatesalesreturn', 'SOController@updateSalesReturn');

//Stock Transfer Challan
Route::get('getstocktransferchallen/{id}', 'StocktransferController@GetStockTransferChallen');
Route::get('stocktransferdatacheckuserwise/{UserId}/{STNO}', 'StocktransferController@StockTransferDataCheckUserWise');

//Edit Stock Transfer
Route::get('getstocktransferidwise/{id}', 'StocktransferController@GetStocktransferIdWise');
Route::post('updateStockTransfer', 'StocktransferController@updateStockTransfer');
Route::delete('deletestocktransfernumber/{id}/{LoggedId}', 'StocktransferController@DeletestocktransferNumber');

//Outlet stock range wise
Route::get('getoutletrangewiseallstockts/{start}/{end}/{OutletPartyId}', 'ReportController@GetOutletRangeStocks');
Route::get('outletpartylist', 'MasterController@GetOutletParty');

//Edit Outlet Sales return

Route::get('getoutletsalesreturnidwise/{id}', 'SOController@getOutletsalesreturnidwise');
Route::post('updateOutletSalesReturnForm', 'SOController@updateOutletSalesReturnForm');

//Outward Report
// Route::post('outwardreportlist', 'ReportController@outwardReport');
Route::get('outwardoutletreportlist/{startdate}/{enddate}/{OutletPartyId}', 'ReportController@outwardOutletReport');
Route::get('outwardreportlist/{startdate}/{enddate}', 'ReportController@outwardReport');


Route::get('getsrangewisedailyreport/{RangeDate}', 'ReportController@GetDailyReports');
Route::get('getsrangewiseoutletdailyreport/{RangeDate}/{OutletPartyId}', 'ReportController@GetOutletDailyReports');
Route::get('getsoutletreport/{RangeDate}/{OutletPartyId}', 'ReportController@getsoutletreport'); //Nitin
Route::post('getoutwardreport', 'ReportController@getOutwardAlldata');
Route::post('saveoutwardchallan', 'ReportController@saveOutwardChallan');


//Logs
// ----------------------------------------------------------
Route::get('articlelogs/{id}', 'MasterController@ArticleLogs');
Route::get('articlelaunchlogs/{id}', 'MasterController@ArticlelaunchLogs'); //nitin
Route::get('pologs/{NumberId}', 'POController@POLogs');
Route::get('inwardlogs/{GRNId}', 'InwardController@InwardLogs');
Route::get('approvearticlelogs/{id}', 'ProductlaunchsController@ApproveArticlelogs');
Route::get('holdarticlelogs/{id}', 'ProductlaunchsController@HoldArticlelogs');
Route::get('rejectedarticlelogs/{id}', 'ProductlaunchsController@RejectedArticlelogs');
Route::get('sologs/{SONOId}', 'SOController@SOLogs');
Route::get('outwardlogs/{OWNOId}', 'OutwardController@OutwardLogs');
Route::get('salesreturnlogs/{SRONOId}', 'SOController@SalesReturnLogs');
Route::get('purchasereturnlogs/{PRONOId}', 'SOController@PurchaseReturnLogs');
Route::get('outletsalesreturnlogs/{OSRONOId}', 'OutletsController@OutletSalesReturnLogs');
Route::get('articlephotoslogs/{id}', 'MasterController@ArticlePhotosLogs');
Route::get('outletlogs/{OTLNOId}', 'OutletsController@OutletLogs');
Route::get('outlettransportlogs/{id}', 'OutletsController@OutletTransportLogs');
Route::get('stocktransferlogs/{STNOId}', 'StocktransferController@StocktransferLogs');
