<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// ========== Controllers ==========
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\VehicleAttachmentController;

// ========== Livewire Pages (Superadmin) ==========
use App\Livewire\Pages\Superadmin\Dashboard as SuperadminDashboard;
use App\Livewire\Pages\Superadmin\ReceptionistUsers as ReceptionistUsers;
use App\Livewire\Pages\Superadmin\RoomBookingStatistics as RoomBookingStatistics;
use App\Livewire\Pages\Superadmin\VehicleBookingStatistics as VehicleBookingStatistics;
use App\Livewire\Pages\Superadmin\DeliveryStatistics as DeliveryStatistics;
use App\Livewire\Pages\Superadmin\AISecurityReports as AISecurityReports;
use App\Livewire\Pages\Superadmin\Announcement;
use App\Livewire\Pages\Superadmin\Information;
use App\Livewire\Pages\Superadmin\Report;
use App\Livewire\Pages\Superadmin\Account as UserManagement;
use App\Livewire\Pages\Superadmin\Department as DepartmentPage;
use App\Livewire\Pages\Superadmin\Bookingroom as SuperadminBookingroom;
use App\Livewire\Pages\Superadmin\Ticketsupport as SuperadminTicketsupport;
use App\Livewire\Pages\Superadmin\Manageroom as Manageroom;
use App\Livewire\Pages\Superadmin\Managerequirement as Managerequirements;
use App\Livewire\Pages\Superadmin\Storage as StoragePage;
use App\Livewire\Pages\Superadmin\Vehicle as VehiclePage;
use App\Livewire\Pages\Superadmin\Packagemanagement as Packagemanagement;
use App\Livewire\Pages\Superadmin\Documentsmanagement as Documentsmanagement;
use App\Livewire\Pages\Superadmin\Guestbookmanagement as Guestbookmanagement;
use App\Livewire\Pages\Superadmin\Bookingvehicle as SuperadminBookingvehicle;
use App\Livewire\Pages\Superadmin\Adminmanagement as AdminmanagementPage;
use App\Livewire\Pages\Superadmin\WifiManagement as SuperadminWifiManagement;

// ========== Livewire Pages (Receptionist) ==========
use App\Livewire\Pages\Receptionist\Dashboard as ReceptionistDashboard;
use App\Livewire\Pages\Receptionist\Documents as Documents;
use App\Livewire\Pages\Receptionist\Package as ReceptionistPackage;
use App\Livewire\Pages\Receptionist\Guestbook as Guestbook;
use App\Livewire\Pages\Receptionist\MeetingSchedule as MeetingSchedule;
use App\Livewire\Pages\Receptionist\BookingsApproval;
use App\Livewire\Pages\Receptionist\RoomApproval;
use App\Livewire\Pages\Receptionist\BookingHistory;
use App\Livewire\Pages\Receptionist\GuestbookHistory;
use App\Livewire\Pages\Receptionist\DocPackHistory;
use App\Livewire\Pages\Receptionist\DocPackStatus;
use App\Livewire\Pages\Receptionist\DocPackForm;
use App\Livewire\Pages\Receptionist\Bookingvehicle;
use App\Livewire\Pages\Receptionist\Vehicleshistory;
use App\Livewire\Pages\Receptionist\Vehiclestatus as ReceptionistVehiclestatus;

// ========== Auth Pages ==========
use App\Livewire\Pages\Auth\Login as LoginPage;
use App\Livewire\Pages\Auth\Register as RegisterPage;

// ========== Error ==========
use App\Livewire\Pages\Errors\error404 as Error404;

use App\Services\GoogleMeetService;

/*
|--------------------------------------------------------------------------
| Root: redirect to login
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Home: redirect authenticated users to their dashboard
|--------------------------------------------------------------------------
*/
Route::get('/home', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();
    $roleName = $user->role->name ?? $user->role ?? null;

    return match ($roleName) {
        'Superadmin'    => redirect()->route('superadmin.dashboard'),
        'Receptionist'  => redirect()->route('receptionist.dashboard'),
    };
})->name('home');

/*
|--------------------------------------------------------------------------
| Google OAuth (contoh / debug)
|--------------------------------------------------------------------------
*/
Route::get('/google/oauth/init', function () {
    $credPath = base_path(env('GOOGLE_OAUTH_CREDENTIALS_JSON', 'storage/app/google_oauth/credentials.json'));
    $tokenPath = base_path(env('GOOGLE_OAUTH_TOKENS_PATH', 'storage/app/google_oauth/tokens.json'));

    $client = new Google\Client();
    $client->setApplicationName('KRBS Meet Creator');
    $client->setScopes([Google\Service\Calendar::CALENDAR, Google\Service\Calendar::CALENDAR_EVENTS]);
    $client->setAccessType('offline');
    $client->setAuthConfig($credPath);
    $client->setRedirectUri(url('/google/oauth/callback'));

    if (!request()->has('code')) {
        return redirect()->away($client->createAuthUrl());
    }

    $token = $client->fetchAccessTokenWithAuthCode(request('code'));
    if (!is_dir(dirname($tokenPath)))
        @mkdir(dirname($tokenPath), 0775, true);
    file_put_contents($tokenPath, json_encode($token));
    return 'Google OAuth tokens saved ✅';
});

Route::get('/google/oauth/callback', fn() => redirect('/google/oauth/init'));

/*
|--------------------------------------------------------------------------
| Guest only
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', LoginPage::class)->name('login');
    Route::get('/register', RegisterPage::class)->name('register');
});

/*
|--------------------------------------------------------------------------
| Auth only
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/google/connect', fn(GoogleMeetService $svc) => redirect($svc->getAuthUrl()))
        ->name('google.connect');

    Route::get('/oauth2callback', function (Request $request) {
        $code = $request->query('code');
        if (!$code) {
            abort(400, 'Missing authorization code');
        }
        app(GoogleMeetService::class)->handleCallback($code);
        return redirect()->route('dashboard')->with('success', 'Google connected!');
    })->name('google.callback');

    Route::get('/google/debug-auth-url', function (GoogleMeetService $svc) {
        return $svc->getAuthUrl();
    });

    // ---------- Attachments API (Local Storage) ----------
    Route::prefix('attachments')->middleware('auth')->group(function () {
        Route::post('/temp', [AttachmentController::class, 'tempUpload'])
            ->name('attachments.temp');
        Route::delete('/temp', [AttachmentController::class, 'deleteTemp'])
            ->name('attachments.temp.delete');
        Route::post('/finalize', [AttachmentController::class, 'finalizeTemp'])
            ->name('attachments.finalize');
    });
    
    });

    // ---------- Superadmin routes ----------
    Route::middleware('is.superadmin')->group(function () {
        Route::get('/superadmin-dashboard', SuperadminDashboard::class)->name('superadmin.dashboard');
        Route::get('/receptionists', ReceptionistUsers::class)->name('superadmin.receptionists');
        Route::get('/room-bookings', RoomBookingStatistics::class)->name('superadmin.room');
        Route::get('/vehicle-bookings', VehicleBookingStatistics::class)->name('superadmin.vehicle');
        Route::get('/deliveries', DeliveryStatistics::class)->name('superadmin.delivery');
        Route::get('/ai-security', AISecurityReports::class)->name('superadmin.ai-security');
    });

    // ---------- Receptionist routes ----------
    Route::middleware('is.receptionist')->group(function () {
        Route::get('/receptionist-dashboard', ReceptionistDashboard::class)->name('receptionist.dashboard');
        Route::get('/receptionist-guestbook', Guestbook::class)->name('receptionist.guestbook');
        Route::get('/receptionist-meetingschedule', MeetingSchedule::class)->name('receptionist.schedule');
        Route::get('/receptionist-document', Documents::class)->name('receptionist.documents');
        Route::get('/receptionist-package', ReceptionistPackage::class)->name('receptionist.package');
        Route::get('/receptionist-bookings', BookingsApproval::class)->name('receptionist.bookings');
        Route::get('/receptionist-roomapproval', RoomApproval::class)->name('receptionist.roomapproval');
        Route::get('/receptionist-bookinghistory', BookingHistory::class)->name('receptionist.bookinghistory');
        Route::get('/receptionist-guestbookhistory', GuestbookHistory::class)->name('receptionist.guestbookhistory');
        Route::get('/receptionist-docpackhistory', DocPackHistory::class)->name('receptionist.docpackhistory');
        Route::get('/receptionist-docpackstatus', DocPackStatus::class)->name('receptionist.docpackstatus');
        Route::get('/receptionist-docpackform', DocPackForm::class)->name('receptionist.docpackform');
        route::get('/receptionist-bookingvehicle', Bookingvehicle::class)->name('receptionist.bookingvehicle');
        Route::get('/receptionist-vehicleshistory', Vehicleshistory::class)->name('receptionist.vehicleshistory');
        Route::get('/receptionist-vehiclestatus', ReceptionistVehiclestatus::class)->name('receptionist.vehiclestatus');

    });

    // ---------- Logout ----------
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->forget('url.intended');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');

/*
|--------------------------------------------------------------------------
| Fallback 404
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    abort(404);
});