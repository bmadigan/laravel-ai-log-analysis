<div wire:poll.10s="refreshData">
    <flux:main container class="space-y-6 max-w-[70rem]">
        <flux:heading size="xl" level="1" class="text-zinc-900">Log Analysis AI</flux:heading>

        <div class="flex items-center gap-2">
            <flux:button wire:click="refreshData" variant="outline" color="zinc" class="text-zinc-800">
                <span wire:loading.remove>Refresh Data</span>
                <span wire:loading><flux:icon name="loading" class="size-4 me-1" /> Loading...</span>
            </flux:button>
        </div>

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
