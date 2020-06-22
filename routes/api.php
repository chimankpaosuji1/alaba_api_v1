<?php

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
    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });


/*
|--------------------------------------------------------------------------
|Single routes without groups
|--------------------------------------------------------------------------
*/

//Route::group(['middleware' => ['checkLastLoginCountry']], function() {
    Route::get('/ip', 'LoginController@clientip');

//});

// Route::post('/order', 'OrderController@store');
// Route::get('/ip', 'LoginController@clientip');
Route::post('advice/', 'AdviceController@store');
Route::get('/slider', 'SliderController@index');
Route::get('/featured_product', 'ProductController@featuredproduct'); //list all featured product
Route::get('/popular_product', 'ProductController@popularproduct');   //list all popular products
Route::get('/recommended_product', 'ProductController@recommendedproduct');   //list all recommended products
Route::get('/activated', 'ProductController@activated'); // list all products to users
Route::get('product_details/{product_slug}', 'ProductController@show'); // products details to user
Route::get('product_detail/{id}', 'ProductController@detail'); // products details to user
Route::get('/country', 'CountryController@index');
Route::get('/parent', 'CategoryController@parent');
Route::get('/category/allcat', 'CategoryController@all');
Route::get('/subparent/{id}', 'CategoryController@subParent');
Route::get('/getcatdetail/{id}', 'CategoryController@getcatdetail');
Route::get('/faq', 'FaqController@index');
Route::post('/faqs', 'FaqController@index');
Route::get('/page/{page}', 'PageController@show');
Route::get('/page', 'PageController@index');
Route::post('/contact', 'ContactController@store');
Route::get('/getsellerprofile/{id}', 'SellerController@getsellerprofile');



Route::group(['middleware' => ['subaccount']], function(){
    Route::post('sub_add_product', "ProductController@sub_store");
});



/*
|--------------------------------------------------------------------------
|Admins/buyers and sellers group  routes
|--------------------------------------------------------------------------
*/

// Route::group(['middleware' => ['role:super_admin|admin|staff|seller|trustedseller|premiumseller|buyer|probuyer']], function(){
//     Route::get('getwalletbalance', 'WalletController@getWalletBalance');
//     Route::post('loadwallet/', 'WalletController@userFundWallet');
//     Route::get('confirmtransaction/{reference}', 'TransactionController@callback');
//     Route::get('transactionhistory', 'TransactionHistoryController@getUserTransaction');
// });

Route::group(['middleware' => ['role:super_admin|admin|staff|seller|trustedseller|premiumseller|buyer|probuyer']], function(){
    Route::get('/getsubscription','SubscriptionController@getUserSubscriptions');
    Route::post('/credit_user','TransactionController@creditUser');

});
Route::post('/wallet', 'WalletController@wallauth');
Route::group(['middleware' => ['checkwalletstatus', 'role:super_admin|admin|staff|seller|trustedseller|premiumseller|buyer|probuyer' ]], function(){
    Route::get('getwalletbalance', 'WalletController@getWalletBalance');
    Route::post('loadwallet/', 'WalletController@userFundWallet');
    Route::get('confirmtransaction/{reference}', 'TransactionController@callback');
    Route::get('transactionhistory', 'TransactionHistoryController@getUserTransaction');
    Route::post('changewalletpassword/', 'WalletController@changewalletpassword');
});

Route::group(['middleware' => ['role:super_admin|admin|staff|seller|trustedseller|premiumseller']], function(){
    Route::post('add_product/', 'ProductController@store');
    Route::post('update_product/{id}', 'ProductController@update');

    Route::post('approve_order/', 'OrderController@approveorder');
    Route::post('deactivate_product/{id}', 'ProductController@deactivate');
    Route::get('get_total_sales/', 'SaleController@getsellersales');
    Route::get('get_total_sales/{status}', 'SaleController@getsellersales');
    Route::get('get_total_salesbysaleno/{saleno}', 'SaleController@getsellersalesbysaleno');
});


Route::post('fullsearch/', 'ProductController@fullsearch');
Route::post('filter/', 'ProductController@advanced_filter');

//Route::get('sellerprod/', 'SellerController@sellerprod');

Route::get('cat/{id}', 'ProductController@cat');
Route::get('sellerprofile/{id}', 'SellerController@sellerprod');

/*
|--------------------------------------------------------------------------
|Buyer group  routes
|--------------------------------------------------------------------------
*/


Route::group(['middleware' => ['role:buyer|probuyer']], function(){
        Route::get('buyer/rfq', 'RfqController@list');
        Route::post('buyer/update', 'BuyerController@update');
        // Route::get('buyer/getsales', 'BuyerController@getBuyerSales');
        Route::get('buyer/getpendingsale', 'BuyerController@getPendingBuyerSales');
        Route::get('buyer/getapprovedsale', 'BuyerController@getApprovedBuyerSales');
        Route::post('message/send', 'MessagecenterController@sendmessage');
        Route::get('message/buyerunread', 'MessagecenterController@buyerGetUnreadMessage');
        Route::get('message/readmessage', 'MessagecenterController@buyerGetReadMessage');

});

Route::group(['middleware' => ['role:buyer|probuyer']], function () {
    Route::post('rfq/', 'RfqController@store');
    Route::post('/order', 'OrderController@store');
    Route::get('/getbuyerorder', 'SaleController@getbuyersales');
    Route::get('/getbuyerorder/{status}', 'SaleController@getbuyersales');
    Route::get('/getbuyerorderbysaleno/{saleno}', 'SaleController@getbuyersalesbysaleno');
    Route::get('/getbuyertotalorderdetails', 'SaleController@getBuyerTotalSaleDetails');
});


Route::group(['middleware' => ['role:buyer']], function () {
    Route::post('/upgradetopro', 'UpgradeController@toProbuyer');
});



/*
|--------------------------------------------------------------------------
|seller group  routes
|--------------------------------------------------------------------------

*/

Route::group(['middleware' => ['role:seller|trustedseller|premiumseller']], function(){
    Route::post('reply/{id}', 'RfqController@reply');
    Route::get('getRfq/', 'SellerController@getRfq');
    Route::get('tradeassurance/accept', 'SellerController@accept');
    Route::post('message/reply', 'MessagecenterController@reply');
    Route::get('message/read', 'MessagecenterController@sellerGetReadMessage');
    Route::get('message/unread', 'MessagecenterController@sellerGetUnReadMessage');
    Route::get('message/viewmessage/{id}', 'MessagecenterController@viewmessage');
});

Route::group(['middleware' => ['role:trustedseller|premiumseller']], function(){
    Route::post('add_subaccount/', 'SellerController@addSubaccount');
});


Route::group(['middleware' => ['role:seller|trustedseller|premiumseller']], function(){
    Route::get('/getPendingProduct', 'SellerController@getPendingProduct'); //seller get their pending products
    Route::delete('seller_prod/{id}', 'SellerController@delete'); //delete Prodcts from the db
    Route::get('/getProductId/{id}', 'SellerController@getProductId'); //seller get all their products details
    Route::get('/getProduct', 'SellerController@getProduct'); // seller get their products
    //SALES DETAILS
    Route::get('/getotalsales', 'SaleController@getTotalSaleDetails'); //seller get their total  products sales

});

Route::group(['middleware' => ['role:seller|trustedseller']], function () {
    Route::post('/upgradetopremium', 'UpgradeController@toPremiumSeller');
});

Route::group(['middleware' => ['role:seller|premiumseller|trustedseller']], function(){
    Route::post('/addbusinesstype', 'UpgradeController@addBusinessType');
    Route::post('/addfactory', 'UpgradeController@addFactoryDetails');
    Route::post('/addcompany', 'UpgradeController@addCompanyDetails');
    Route::post('/addcert', 'UpgradeController@addCertDetails');
});

Route::group(['middleware' => ['role:seller|premiumseller']], function(){
    Route::post('/upgradetotrusted', 'UpgradeController@upgradetoTrustedSeller');
});

Route::group(['middleware' => ['role:seller|trustedseller|premiumseller|super_admin']], function(){
    Route::get('/getseller', 'SellerController@getSeller');
});


/*
|--------------------------------------------------------------------------
|Admin and super admin routes
|--------------------------------------------------------------------------

*/

Route::group(['middleware'=> ['role:super_admin|admin']], function(){
    Route::get('/advice', 'AdviceController@index');
    Route::get('/getstat', 'AdminController@getAdminStats');
    Route::get('advice_details/{id}', 'AdviceController@show');
    Route::delete('advice/{id}', 'AdviceController@destroy');
    //SLIDER
    Route::post('/add_slider', 'SliderController@store');
    Route::post('update_slider/{id}', 'SliderController@update' );
    Route::delete('slider/{id}', 'SliderController@destroy');
    Route::post('activate_slider/{id}', 'SliderController@activate');
    Route::post('deactivate_slider/{id}', 'SliderController@deactivate');
    Route::get('/active_slider', 'SliderController@activateds');
    //Products
    Route::get('/pending_product', 'ProductController@pending');  //list all pending products for admins
    Route::get('activate_product/{id}', 'ProductController@activate'); //activate products
    Route::get('featured_product/{id}', 'ProductController@featured'); //activate featured products
    Route::get('deactivatefeatured_product/{id}', 'ProductController@deactivatefeatured'); // deactivate featured products
    Route::get('recommended_product/{id}', 'ProductController@recommended'); // activate recommended products
    Route::get('deactivaterecommended_product/{id}', 'ProductController@deactivaterecommended');// deactivate recommended products
    Route::get('popular_product/{id}', 'ProductController@popular'); // activate popular products
    Route::get('deactivatepopular_product/{id}', 'ProductController@deactivatepopular'); //deactivate popular products
    Route::delete('product/{id}', 'ProductController@destroy'); //delete Prodcts from the db
//    Route::get('product_details/{product_slug}', 'ProductController@show');
    //Category
    Route::post('/category', 'CategoryController@store');
    Route::post('category/{id}', 'CategoryController@update');
    Route::delete('category/{id}', 'CategoryController@destroy');
    Route::post('/addsubcategory', 'CategoryController@addSubCategory');
    Route::post('subcategory/{id}', 'CategoryController@updateSubCategory');
    // Route::post('deletesubcategory/{id}', 'CategoryController@SubDelete');
    //FAQ
    Route::post('faq', 'FaqController@store');
    Route::post('faq/{id}', 'FaqController@update');
    Route::delete('faq/{id}', 'FaqController@destroy');
    //PAGE
    Route::delete('page/{id}', 'PageController@destroy');
    Route::post('page/{id}', 'PageController@update');
    Route::post('page', 'PageController@store');
});

Route::group(['middleware' => ['role:super_admin']], function () {
    Route::get('rfq/', 'RfqController@index');
    Route::delete('rfq/{id}', 'RfqController@destroy');

});

Route::group(['middleware' => ['role:super_admin']], function () {
    Route::get('/adminupgradeToTrusted', 'UpgradeController@toTrustedSeller');
    Route::post('/funduserwallet', 'WalletController@fundUserWallet');
});


Route::group(['middleware' => ['role:super_admin']], function () {
    Route::get('/contact', 'ContactController@index');
    Route::get('/user/{id}', 'UsersController@users_details');
    Route::get('/usersbyrole/{role}/{roleid}', 'UsersController@usersbyrole');
    Route::delete('user/{id}', 'UsersController@destroy');
    Route::get('contact/{id}', 'ContactController@show');
    Route::delete('contact/{id}', 'ContactController@destroy');
});


/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------

*/



Route::get('/sendmail', 'SignupController@sendmail');
Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('/login', 'LoginController@login');
    Route::post('/islocked', 'IslockedController@islocked');
    Route::get('/getAuthUser', 'LoginController@getAuthUser');
    Route::get('/logout', 'LoginController@logout');
    Route::post('/signup', 'SignupController@store');
    Route::post('/resendmail', 'SignupController@resendmail');
    Route::get('/confirmation/{token}', 'AccountActivationController@update');
    Route::post('/buyer', 'BuyerController@store');
    Route::post('/seller', 'SellerController@store');
    Route::post('/reset', 'ResetPasswordController@sendmail');
    Route::post('/confirmreset/{token}', 'ResetPasswordController@confirmreset');
    Route::post('/change_password', 'LoginController@changepassword');
});
