namespace App\Services;

class WazuhService
{
    public function startSimulation(): array
    {
        $script = base_path('scripts/start_wazuh_stack.sh');

        $output = [];
        $resultCode = 0;

        exec("bash {$script} 2>&1", $output, $resultCode);

        return [
            'success' => $resultCode === 0,
            'output' => implode("\n", $output),
        ];
    }
}