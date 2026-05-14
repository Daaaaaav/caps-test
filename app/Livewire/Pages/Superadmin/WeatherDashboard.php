<!-- unsure of usability 

// namespace App\Livewire\Pages\Superadmin;

// use Livewire\Component;
// use Livewire\Attributes\Layout;
// use Livewire\Attributes\Title;
// use App\Services\WeatherService;

// #[Layout('layouts.superadmin')]
// #[Title('Weather Dashboard')]
// class WeatherDashboard extends Component
// {
//     public string $adm4 = WeatherService::DEFAULT_ADM4;
//     public bool $showHourly = false;
//     public int $selectedDay = 0;

//     public function selectDay(int $index): void
//     {
//         $this->selectedDay = $index;
//         $this->showHourly  = true;
//     }

//     public function closeHourly(): void
//     {
//         $this->showHourly = false;
//     }

//     public function refreshWeather(): void
//     {
//         // Bust the cache so next render fetches fresh data
//         \Illuminate\Support\Facades\Cache::forget("bmkg_weather_{$this->adm4}");
//         $this->dispatch('toast', type: 'success', title: 'Refreshed', message: 'Weather data updated.', duration: 2500);
//     }

//     public function render()
//     {
//         $service  = new WeatherService();
//         $weather  = $service->getForecast($this->adm4);

//         return view('livewire.pages.superadmin.weather-dashboard', [
//             'weather'     => $weather,
//             'selectedDay' => $this->selectedDay,
//         ]);
//     }
// } -->
