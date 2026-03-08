<div>
    @if($show)
        <div class="fixed inset-0 z-[100] flex items-center justify-center">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/50" wire:click="close"></div>

            {{-- Modal box (center) --}}
            <div class="relative z-10 w-full max-w-lg mx-4 bg-white rounded-xl border-2 border-black shadow-xl"
                wire:keydown.escape="close" tabindex="-1">
                <div class="p-5 border-b-2 border-black flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">New Ticket</h3>
                    <button class="p-2 rounded hover:bg-gray-100" wire:click="close">✕</button>
                </div>

                <div class="p-5 overflow-y-auto max-h-[80vh] text-black">
                    <form wire:submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Subject</label>
                            <input type="text" wire:model.defer="subject" placeholder="Enter ticket subject"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-black">
                            @error('subject') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Priority</label>
                                <select wire:model.defer="priority"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-black">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                                @error('priority') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Department</label>
                                <select wire:model.defer="department_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-black">
                                    <option value="">—</option>
                                    @foreach($departments as $d)
                                        <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('department_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Description</label>
                            <textarea rows="5" wire:model.defer="description" placeholder="Describe your issue in detail..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-black"></textarea>
                            @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="pt-2 flex gap-3">
                            <button type="button" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                                wire:click="close">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-black text-white rounded-md hover:bg-gray-800">
                                Submit Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>