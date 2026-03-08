<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// ========== Controllers ==========
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\VehicleAttachmentController;

// ========== Livewire Pages (User) ==========
use App\Livewire\Pages\User\Home as UserHome;
use App\Livewire\Pages\User\CreateTicket;
use App\Livewire\Pages\User\Bookroom;
use App\Livewire\Pages\User\Profile;
use App\Livewire\Pages\User\Package as UserPackage;
use App\Livewire\Pages\User\Ticketstatus;
use App\Livewire\Pages\User\BookingStatus;
use App\Livewire\Pages\User\Bookvehicle;
use App\Livewire\Pages\User\Meetonline;
use App\Livewire\Pages\User\Ticketqueue as Ticketqueue;
use App\Livewire\Pages\User\Vehiclestatus as Vehiclestatus;
use App\Livewire\Pages\User\Ticketshow as UserTicketshow;

// ========== Livewire Pages (Admin) ==========
use App\Livewire\Pages\Admin\Dashboard as AdminDashboard;
use App\Livewire\Pages\Admin\Ticket as AdminTicket;
use App\Livewire\Pages\Admin\Ticketshow as AdminTicketshow;
use App\Livewire\Pages\Admin\RoomMonitoring as RoomMonitoringPage;
use App\Livewire\Pages\Admin\Information as InformationPage;
use App\Livewire\Pages\Admin\Agentreport as AgentreportPage;
use App\Livewire\Pages\Admin\WifiManagement;

// ========== Livewire Pages (Superadmin) ==========
use App\Livewire\Pages\Superadmin\Dashboard as SuperadminDashboard;
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
| Root: arahkan sesuai status login & role
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();
    $roleName = $user->role->name ?? $user->role ?? null;

    return match ($roleName) {
        'Superadmin'    => redirect()->route('superadmin.dashboard'),
        'Admin'         => redirect()->route('admin.dashboard'),
        'Receptionist'  => redirect()->route('receptionist.dashboard'),
        default         => redirect()->route('user.home'),
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


    // ---------- Agent Routes ----------
    Route::middleware(['auth', 'is_agent'])->group(function () {
    Route::get('/ticket-queue', Ticketqueue::class)->name('user.ticket.queue');
});

    // ---------- User routes ----------
    Route::get('/dashboard', UserHome::class)->name('user.home');
    Route::get('/create-ticket', CreateTicket::class)->name('create-ticket');
    Route::get('/book-room', Bookroom::class)->name('book-room');
    Route::get('/bookingstatus', BookingStatus::class)->name('bookingstatus');
    Route::get('/book-online', Meetonline::class)->name('user.meetonline');
    Route::get('/book-vehicle', Bookvehicle::class)->name('book-vehicle');
    Route::get('/profile', Profile::class)->name('profile');
    Route::get('/package', UserPackage::class)->name('package');
    Route::get('/ticketstatus', Ticketstatus::class)->name('ticketstatus');
    Route::get('/vehiclestatus', Vehiclestatus::class)->name('vehiclestatus');
    Route::get('/tickets/{ticket:ulid}', UserTicketshow::class)->name('user.ticket.show');
    

    // ---------- Admin routes ----------
    Route::middleware('is.admin')->group(function () {
        Route::get('/admin-dashboard', AdminDashboard::class)->name('admin.dashboard');
        Route::get('/admin-ticket', AdminTicket::class)->name('admin.ticket');
        Route::get('/admin-roommonitoring', RoomMonitoringPage::class)->name('admin.room.monitoring');
        Route::get('/admin/tickets/{ticket:ulid}', AdminTicketshow::class)->name('admin.ticket.show'); // <— Pindah prefix admin
        Route::get('/admin-usermanagement', \App\Livewire\Pages\Admin\Usermanagement::class)->name('admin.usermanagement');
        Route::get('/admin/information', InformationPage::class)->name('admin.information');
        Route::get('/admin/agentreport', AgentreportPage::class)->name('admin.agentreport');
        Route::get('/admin/wifimanagement', WifiManagement::class)->name('admin.wifimanagement');
    });

    // ---------- Superadmin routes ----------
    Route::middleware('is.superadmin')->group(function () {
        Route::get('/superadmin-dashboard', SuperadminDashboard::class)->name('superadmin.dashboard');
        Route::get('/superadmin-announcement', Announcement::class)->name('superadmin.announcement');
        Route::get('/superadmin-information', Information::class)->name('superadmin.information');
        Route::get('/superadmin-user', UserManagement::class)->name('superadmin.user');
        Route::get('/superadmin-department', DepartmentPage::class)->name('superadmin.department');
        Route::get('/superadmin-bookingroom', SuperadminBookingroom::class)->name('superadmin.bookingroom');
        Route::get('/superadmin-ticketsupport', SuperadminTicketsupport::class)->name('superadmin.ticketsupport');
        Route::get('/superadmin-manageroom', Manageroom::class)->name('superadmin.manageroom');
        Route::get('/superadmin-managerequirements', Managerequirements::class)->name('superadmin.managerequirements');
        Route::get('/superadmin-storage', StoragePage::class)->name('superadmin.storage');
        Route::get('/superadmin-vehicle', VehiclePage::class)->name('superadmin.vehicle');
        Route::get('/superadmin/reports', Report::class)->name('superadmin.reports');
        Route::get('/superadmin-bookingvehicle', SuperadminBookingvehicle::class)->name('superadmin.bookingvehicle');
        Route::get('/superadmin-packagemanagement', Packagemanagement::class)->name('superadmin.packagemanagement');
        Route::get('/superadmin-documentsmanagement', Documentsmanagement::class)->name('superadmin.documentsmanagement');
        Route::get('/superadmin-guestbookmanagement', Guestbookmanagement::class)->name('superadmin.guestbookmanagement');
        Route::get('/superadmin-adminmanagement', AdminmanagementPage::class)->name('superadmin.adminmanagement');
        Route::get('/superadmin-wifimanagement', SuperadminWifiManagement::class)->name('superadmin.wifimanagement');
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
});

/*
|--------------------------------------------------------------------------
| Fallback 404
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    abort(404);
});