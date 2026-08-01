
<?php
use App\Http\Controllers\DeviceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TechnicianAuthController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Store\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminStatsController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Store\StoreCategoryController;

Route::get('/test-db', function () {
    return DB::connection()->getDatabaseName();
});
// 1. راوتات عامة (لا تحتاج لتسجيل دخول)
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->post('/send-verification-code', [AuthController::class, 'sendVerificationCode']);
Route::middleware('auth:sanctum')->post('/verify-account', [AuthController::class, 'verifyAccount']);
Route::middleware('auth:sanctum')->post('/complete-profile', [AuthController::class, 'completeProfile']);
//Route::post('/complete-profile', [AuthController::class, 'completeProfile']);
//Route::post('/verify-account', [AuthController::class, 'verifyAccount']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-reset-code', [AuthController::class,'verifyResetCode']);

// 2. راوتات محمية (تحتاج Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/update-avatar', [ProfileController::class, 'updateAvatar'])
    ->middleware('auth:sanctum');
    //Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });






//ادمن


});

Route::post('/technician/register', [TechnicianAuthController::class, 'register']);
Route::get('/users', [AuthController::class, 'users']);
Route::get('/technicians', [AuthController::class, 'technicians']);
Route::delete('/users/{id}', [AuthController::class, 'deleteUser']);
Route::delete('/technicians/{id}', [AuthController::class, 'deleteTechnician']);



Route::middleware('auth:sanctum')->post('/super-admin/create-technician',[AuthController::class, 'createTechnician']);

Route::get('/services', [ServiceController::class, 'mainServices']);

Route::get('/services/{id}/children', [ServiceController::class, 'subServices']);
Route::get(
    '/services/{serviceId}/devices',
    [ServiceController::class, 'getDevices']
);


Route::get('/devices', [DeviceController::class, 'index']);

Route::middleware('auth:sanctum')->delete(
    '/my/solar-installation-request/{id}',
    [DeviceController::class, 'deleteUserInstallationRequest']
);
Route::middleware('auth:sanctum')->post('/select-devices', [DeviceController::class, 'selectDevices']);
Route::middleware('auth:sanctum')
    ->delete('/delete-user-devices', [DeviceController::class, 'deleteUserDevices']);
    Route::middleware('auth:sanctum')
    ->put('/update-user-devices', [DeviceController::class, 'updateUserDevices']);
    Route::middleware('auth:sanctum')->get('/profile', [AuthController::class, 'showProfile']);
    Route::middleware('auth:sanctum')->put('/profile', [AuthController::class, 'updateProfile']);
Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('auth:sanctum')->get(
    '/solar-solutions/{installationRequestId}',
    [ServiceController::class, 'getSolarSolutions']
);
});
use App\Http\Controllers\AdminSolarController;
use App\Http\Controllers\GeneratorController;
use App\Http\Controllers\GeneratorMaintenanceRequestController;
use App\Http\Controllers\SolarRequestController;

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/admin/solar', [AdminSolarController::class, 'store']);
    Route::put('/admin/solar/{id}', [AdminSolarController::class, 'update']);
    Route::delete('/admin/solar/{id}', [AdminSolarController::class, 'destroy']);

}


);
Route::middleware('auth:sanctum')->get(
    '/super-admin/solar-packages',
    [ServiceController::class, 'getSolarPackages']
);

Route::middleware('auth:sanctum')->get(
    '/avatar',
    [ProfileController::class, 'getAvatar']
);
Route::middleware('auth:sanctum')->post('/solar-request', [SolarRequestController::class, 'store']);




// راوتات المتجر

Route::prefix('store')->group(function () {

    // عرض كل التصنيفات
    Route::get('/categories', [StoreCategoryController::class, 'index']);

    // عرض منتجات تصنيف معيّن
    Route::get('/categories/{id}', [StoreCategoryController::class, 'show']);

});

Route::prefix('store')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);

    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

});




Route::middleware('auth:sanctum')->group(function () {

    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'getCart']);
    Route::put('/cart/update/{id}', [CartController::class, 'updateQuantity']);
    Route::delete('/cart/remove/{id}', [CartController::class, 'removeItem']);

});


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/orders/{id}', [OrderController::class, 'getOrderDetails']);
    Route::post('/orders/create', [OrderController::class, 'createOrder']);
    Route::get('/orders', [OrderController::class, 'getOrders']);
    Route::delete('/orders/{id}', [OrderController::class, 'deleteOrder']);

});


Route::middleware('auth:sanctum')->group(function () {

    Route::post('/favorites/add', [FavoriteController::class, 'addFavorite']);
    Route::get('/favorites', [FavoriteController::class, 'getFavorites']);
    Route::delete('/favorites/{id}', [FavoriteController::class, 'removeFavorite']);

});



// راوتات الادمن


Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // Orders
    Route::get('/admin/orders', [AdminOrderController::class, 'index']);
    Route::get('/admin/orders/{id}', [AdminOrderController::class, 'show']);
    Route::put('/admin/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
    Route::put('/admin/orders/{id}/approve', [AdminOrderController::class, 'approve']);
    Route::put('/admin/orders/{id}/reject', [AdminOrderController::class, 'reject']);
    Route::delete('/admin/orders/{id}', [AdminOrderController::class, 'destroy']);

    // Products
    Route::post('/admin/products', [AdminProductController::class, 'store']);
    Route::get('/admin/products', [AdminProductController::class, 'index']);
    Route::get('/admin/products/{id}', [AdminProductController::class, 'show']);
    Route::put('/admin/products/{id}', [AdminProductController::class, 'update']);
    Route::put('/admin/products/{id}/stock', [AdminProductController::class, 'updateStock']);
    Route::put('/admin/products/{id}/approve', [AdminProductController::class, 'approve']);
    Route::put('/admin/products/{id}/reject', [AdminProductController::class, 'reject']);
    Route::delete('/admin/products/{id}', [AdminProductController::class, 'destroy']);

});


// احصائيات
Route::get('/admin/stats', [AdminStatsController::class, 'stats']);



// راوتات التصنيفات


Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // Categories
    Route::get('/admin/categories', [AdminCategoryController::class, 'index']);
    Route::put('/admin/categories/{id}', [AdminCategoryController::class, 'update']);
    Route::delete('/admin/categories/{id}', [AdminCategoryController::class, 'destroy']);
    Route::post('/admin/categories', [AdminCategoryController::class, 'store']);

});
Route::middleware('auth:sanctum')->put(
    '/super-admin/solar-request/{id}',
    [SolarRequestController::class, 'updateStatus']
);
Route::middleware('auth:sanctum')->get(
    '/technician/requests',
    [SolarRequestController::class, 'technicianRequests']
);
Route::middleware('auth:sanctum')->get(
    '/my/solar-requests',
    [SolarRequestController::class, 'show']
);
Route::middleware('auth:sanctum')->get(
    '/my-request/{id}/technician',
    [SolarRequestController::class, 'getTechnicianProfile']
);

Route::middleware('auth:sanctum')->put(
    '/technician/requests/{id}/complete',
    [SolarRequestController::class, 'completeRequest']
);
use App\Http\Controllers\MaintenanceRequestController;

Route::get('/maintenance-requests/{id}',
    [MaintenanceRequestController::class, 'show']
);
Route::get('/maintenance-items', [ServiceController::class, 'maintenanceItems']);
 Route::middleware('auth:sanctum')->post('/maintenance/request',
        [MaintenanceRequestController::class, 'store']
    );

Route::middleware('auth:sanctum')->group(function () {

    Route::put('/maintenance-requests/{id}/status',
        [MaintenanceRequestController::class, 'updateStatus']
    );

});


Route::middleware('auth:sanctum')->group(function () {
    Route::get(
        '/technician/maintenance/solar-panels',
        [MaintenanceRequestController::class, 'technicianSolarPanelRequests']
    );

    Route::put(
        '/technician/maintenance-requests/{id}/complete',
        [MaintenanceRequestController::class, 'completeRequest']
    );
});


Route::middleware('auth:sanctum')->group(function () {

    Route::get(
        '/admin/maintenance-requests/solar-panels',
        [MaintenanceRequestController::class, 'adminSolarPanelRequests']
    );

    Route::get(
        '/admin/solar-installation-requests',
        [SolarRequestController::class, 'adminSolarInstallationRequests']
    );

    Route::get(
        '/my/maintenance-requests/solar-panels/{id}',
        [MaintenanceRequestController::class, 'showMySolarPanelRequest']
    );
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post(
        '/maintenance-requests/battery',
        [MaintenanceRequestController::class, 'storeBatteryRequest']
    );
});
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/my/maintenance-requests/battery/{id}',
        [MaintenanceRequestController::class, 'showMyBatteryRequest']);

    Route::get('/my/maintenance-requests/solar-panels/{id}/technician',
        [MaintenanceRequestController::class, 'showSolarPanelTechnician']);

    Route::get('/my/maintenance-requests/battery/{id}/technician',
        [MaintenanceRequestController::class, 'showBatteryTechnician']);

    Route::get('/admin/maintenance-requests/battery',
        [MaintenanceRequestController::class, 'adminBatteryRequests']);

    Route::put('/admin/maintenance-requests/battery/{id}/status',
        [MaintenanceRequestController::class, 'updateBatteryRequestStatus']);

    Route::get('/technician/maintenance-requests/battery',
        [MaintenanceRequestController::class, 'technicianBatteryRequests']);

    Route::put('/technician/maintenance-requests/battery/{id}/complete',
        [MaintenanceRequestController::class, 'completeBatteryRequest']);
        Route::post('/maintenance-requests/inverter',
    [MaintenanceRequestController::class, 'storeInverterRequest']);

});
Route::middleware('auth:sanctum')->group(function () {

    // 🟢 المستخدم
    Route::post('/maintenance/inverter',
        [MaintenanceRequestController::class, 'storeInverterRequest']);

    Route::get('/my/maintenance/inverter/{id}',
        [MaintenanceRequestController::class, 'showMyInverterRequest']);

    Route::get('/my/maintenance/inverter/{id}/technician',
        [MaintenanceRequestController::class, 'showInverterTechnician']);

    // 🔵 الأدمن
    Route::get('/admin/maintenance/inverter',
        [MaintenanceRequestController::class, 'adminInverterRequests']);

    Route::put('/admin/maintenance/inverter/{id}/status',
        [MaintenanceRequestController::class, 'updateInverterRequestStatus']);

    // 🟣 الفني
    Route::get('/technician/maintenance/inverter',
        [MaintenanceRequestController::class, 'technicianInverterRequests']);

    Route::put('/technician/maintenance/inverter/{id}/complete',
        [MaintenanceRequestController::class, 'completeInverterRequest']);

});
Route::middleware('auth:sanctum')->group(function () {
// حذف طلب الألواح
Route::delete('/my/maintenance-requests/solar-panels/{id}', [MaintenanceRequestController::class, 'deleteMySolarPanelRequest']);
// حذف طلب الإنفيرتر
Route::delete('/my/maintenance-requests/inverter/{id}', [MaintenanceRequestController::class, 'deleteMyInverterRequest']);
// حذف طلب البطارية
Route::delete('/my/maintenance-requests/battery/{id}', [MaintenanceRequestController::class, 'deleteMyBatteryRequest']);

Route::post('/industrial-generator/devices', [DeviceController::class, 'addIndustrialGeneratorDevices']);
 Route::get('/solar-request/{id}', [SolarRequestController::class, 'showSolarRequestById']);
});
Route::get('/industrial-generator/services',
    [ServiceController::class, 'industrialGeneratorServices']);
    Route::get('/solar-panel/requests', [MaintenanceRequestController::class, 'showAllSolarPanelRequests']);
    Route::get('/battery/requests', [MaintenanceRequestController::class, 'showAllBatteryRequests']);
    Route::get('/inverter/requests', [MaintenanceRequestController::class, 'showAllInverterRequests']);
Route::middleware('auth:sanctum')->get(
    '/industrial-generators/{installationRequestId}',
    [GeneratorController::class, 'getSuitableGenerators']
);
Route::middleware('auth:sanctum')->post(
'/generator-requests',
[GeneratorController::class,'storeGeneratorRequest']
);
Route::middleware('auth:sanctum')->get(
    '/generator-requests/{id}',
    [GeneratorController::class, 'showGeneratorRequest']
);
Route::middleware('auth:sanctum')->get(
    '/my-generator-requests',
    [GeneratorController::class, 'myGeneratorRequests']
);
Route::middleware('auth:sanctum')->get(
    '/admin/generator-requests',
    [GeneratorController::class,'adminGeneratorRequests']
);
Route::middleware('auth:sanctum')->put(
    '/admin/generator-requests/{id}/status',
    [GeneratorController::class,'updateGeneratorRequestStatus']
);

Route::middleware('auth:sanctum')->get(
    '/generator-requests/{id}/technician',
    [GeneratorController::class,'generatorTechnicianProfile']
);
Route::middleware('auth:sanctum')->get(
    '/technician/generator-requests',
    [GeneratorController::class,'technicianGeneratorRequests']
);
Route::middleware('auth:sanctum')->put(
    '/technician/generator-requests/{id}/complete',
    [GeneratorController::class,'completeGeneratorRequest']
);
Route::middleware('auth:sanctum')->delete(
    '/generator-requests/{id}',
    [GeneratorController::class,'deleteGeneratorRequest']
);
Route::middleware('auth:sanctum')->post(
'/generator-maintenance-requests',
[GeneratorMaintenanceRequestController::class,'store']
);
Route::middleware('auth:sanctum')->get(
    '/my-generator-maintenance-requests/{id}',
    [GeneratorMaintenanceRequestController::class,'showMyGeneratorMaintenanceRequest']
);
Route::middleware('auth:sanctum')->get(
'/my-generator-maintenance-requests',
[GeneratorMaintenanceRequestController::class,'myGeneratorMaintenanceRequests']
);
Route::middleware('auth:sanctum')->put(
'/admin/generator-maintenance-requests/{id}/status',
[GeneratorMaintenanceRequestController::class,'updateGeneratorMaintenanceStatus']
);

Route::middleware('auth:sanctum')->get(
'/admin/generator-maintenance-requests',
[GeneratorMaintenanceRequestController::class,'adminGeneratorMaintenanceRequests']
);

Route::middleware('auth:sanctum')->put(
'/technician/generator-maintenance-requests/{id}/complete',
[GeneratorMaintenanceRequestController::class,'completeGeneratorMaintenanceRequest']
);
Route::middleware('auth:sanctum')->get(
'/technician/generator-maintenance-requests',
[GeneratorMaintenanceRequestController::class,'technicianGeneratorMaintenanceRequests']
);
Route::middleware('auth:sanctum')->get(
'/generator-maintenance-requests/{id}/technician',
[GeneratorMaintenanceRequestController::class,'getGeneratorMaintenanceTechnician']
);
Route::middleware('auth:sanctum')->delete(
'/generator-maintenance-requests/{id}',
[GeneratorMaintenanceRequestController::class,'deleteGeneratorMaintenanceRequest']
);
