<div wire:poll.10s="refreshData">
    <flux:main container class="space-y-6 max-w-[70rem]">
        <div class="flex items-center justify-between mb-2">
            <flux:heading size="xl" level="1" class="text-zinc-900">Log Analysis AI</flux:heading>
            <div class="flex gap-2">
                <flux:button wire:click="generateTestErrors" icon="exclamation-triangle" variant="danger" wire:loading.attr="disabled" wire:target="generateTestErrors">
                    <span wire:loading.remove wire:target="generateTestErrors">Generate Test Errors</span>
                    <span wire:loading wire:target="generateTestErrors">Generating...</span>
                </flux:button>
                <flux:button wire:click="refreshData" icon="arrow-path" variant="primary">Refresh Data</flux:button>
            </div>
        </div>

        {{-- Flash Message Display --}}
        @if(session()->has('message'))
            <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded-r-lg">
                <div class="flex items-center gap-3">
                    <flux:icon.check-circle class="size-5 text-green-600" />
                    <p class="text-sm text-green-800">{{ session('message') }}</p>
                </div>
            </div>
        @endif

        {{-- Critical Alerts Section --}}
        @if(count($criticalAlerts) > 0)
            <div class="space-y-3">
                @foreach($criticalAlerts as $alert)
                    <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded-r-lg flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            <flux:icon.exclamation-circle class="size-6 text-red-600 flex-shrink-0 mt-0.5" />
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <flux:badge variant="danger" size="sm">CRITICAL</flux:badge>
                                    <span class="text-xs text-red-700">{{ $alert['created_at'] }}</span>
                                </div>
                                <p class="text-sm font-semibold text-red-900">{{ $alert['summary'] }}</p>
                                <p class="text-xs text-red-700 mt-1 font-mono">{{ $alert['log_preview'] }}</p>
                            </div>
                        </div>
                        <flux:button wire:click="dismissAlert({{ $alert['id'] }})" icon="x-mark" variant="ghost" size="sm" class="text-red-600 hover:text-red-800">
                        </flux:button>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Log Entries Panel --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-4">
                <flux:heading size="lg">Recent Log Entries</flux:heading>

                <flux:separator class="my-4" />

                <div wire:loading class="text-center py-8">
                    <flux:icon name="loading" class="size-6" />
                    <p class="mt-4 text-zinc-600">Loading logs...</p>
                </div>

                <div wire:loading.remove class="space-y-4">
                    @forelse ($logEntries as $entry)
                        <div class="border-l-4 border-zinc-300 pl-4 py-2">
                            <p class="text-sm font-mono text-zinc-700 break-all">{{ $entry['raw'] }}</p>
                            <p class="text-xs text-zinc-500 mt-1">{{ $entry['created_at'] }}</p>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <flux:icon.document-text variant="outline" class="mx-auto size-12 text-zinc-400" />
                            <p class="mt-2 text-zinc-500">No log entries yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Incidents Panel --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-4">
                <flux:heading size="lg">AI-Detected Incidents</flux:heading>

                <flux:separator class="my-4" />

                <div wire:loading class="text-center py-8">
                    <flux:icon name="loading" class="size-6" />
                    <p class="mt-4 text-zinc-600">Loading incidents...</p>
                </div>

                <div wire:loading.remove class="space-y-4">
                    @forelse ($incidents as $incident)
                        <div class="border-l-4 {{ $this->getSeverityBorderClass($incident['severity']) }} pl-4 py-2">
                            <div class="flex items-center gap-2 mb-2">
                                <flux:badge :variant="match($incident['severity']) {
                                    'critical' => 'danger',
                                    'high' => 'warning',
                                    'medium' => 'attention',
                                    'low' => 'success',
                                    default => 'neutral'
                                }" size="sm">
                                    {{ strtoupper($incident['severity']) }}
                                </flux:badge>
                                <span class="text-xs text-zinc-500">{{ $incident['created_at'] }}</span>
                            </div>
                            <p class="text-sm text-zinc-800 font-medium">{{ $incident['summary'] }}</p>
                            <p class="text-xs text-zinc-600 mt-1 font-mono">{{ $incident['log_preview'] }}</p>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <flux:icon.exclamation-triangle variant="outline" class="mx-auto size-12 text-zinc-400" />
                            <p class="mt-2 text-zinc-500">No incidents detected yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </flux:main>
</div>
