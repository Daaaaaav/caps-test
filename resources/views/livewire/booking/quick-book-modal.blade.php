<div>
    @if($show)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40" wire:click="close"></div>

            <div class="relative z-10 w-full max-w-xl mx-4 bg-white rounded-xl border-2 border-black shadow-lg"
                wire:keydown.escape="close">
                <div class="border-b-2 border-black p-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Quick Book</h3>
                    <button class="px-2 py-1 text-gray-600 hover:text-gray-900" wire:click="close">✕</button>
                </div>

                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1">Room</label>
                            <input type="text" value="{{ $roomName ?? '' }}" disabled
                                class="w-full px-3 py-2 bg-gray-100 text-gray-900 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1">Date</label>
                            <input type="date" wire:model="date"
                                class="w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900">
                            @error('date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1">Start Time</label>
                            <input type="time" wire:model="start_time" min="{{ $minStart }}"
                                class="w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900">
                            @error('start_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1">End Time</label>
                            <input type="time" wire:model="end_time" min="{{ $start_time ?: $minStart }}"
                                class="w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900">
                            @error('end_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1">Meeting Title</label>
                            <input type="text" wire:model="meeting_title" placeholder="Enter meeting title"
                                class="w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900">
                            @error('meeting_title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1">Attendees</label>
                            <input type="number" wire:model="number_of_attendees" min="1" placeholder="0"
                                class="w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900">
                            @error('number_of_attendees') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Additional Requirements</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach (['projector', 'whiteboard', 'video_conference', 'catering', 'other'] as $req)
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model.live="requirements" value="{{ $req }}"
                                        class="rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                                    <span class="text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $req)) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    @if (in_array('other', $requirements ?? [], true))
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-1">Special Notes</label>
                            <textarea wire:model.defer="special_notes" rows="3"
                                placeholder="Please specify your other requirement…"
                                class="w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none"></textarea>
                            @error('special_notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                <div class="border-t border-gray-200 px-4 py-3 flex items-center justify-end gap-2">
                    <button wire:click="close"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="submit" class="px-4 py-2 bg-gray-900 text-white rounded-md hover:bg-gray-800">
                        Confirm Booking
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>