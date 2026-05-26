<div>
    @if($show)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity duration-300" wire:click="close"></div>

            {{-- Modal box (center) --}}
            <div class="relative z-10 w-full max-w-lg bg-card rounded-2xl border border-border shadow-2xl overflow-hidden transform transition-all duration-300 scale-100"
                wire:keydown.escape="close" tabindex="-1">
                
                {{-- Header --}}
                <div class="px-6 py-5 border-b border-border flex items-center justify-between bg-muted/10">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-foreground tracking-tight">New Support Ticket</h3>
                    </div>
                    <button class="w-8 h-8 flex items-center justify-center rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition" wire:click="close">✕</button>
                </div>

                {{-- Body --}}
                <div class="p-6 overflow-y-auto max-h-[75vh] space-y-4">
                    <form wire:submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">Subject</label>
                            <input type="text" wire:model.defer="subject" placeholder="Enter ticket subject"
                                class="w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground placeholder:text-muted-foreground/60 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            @error('subject') <p class="text-xs text-destructive mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">Priority</label>
                                <select wire:model.defer="priority"
                                    class="w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                                @error('priority') <p class="text-xs text-destructive mt-1.5 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">Department</label>
                                <select wire:model.defer="department_id"
                                    class="w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                                    <option value="">— Select Department —</option>
                                    @foreach($departments as $d)
                                        <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('department_id') <p class="text-xs text-destructive mt-1.5 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">Description</label>
                            <textarea rows="4" wire:model.defer="description" placeholder="Describe your issue in detail..."
                                class="w-full px-3.5 py-2.5 border border-input rounded-lg bg-background text-sm text-foreground placeholder:text-muted-foreground/60 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"></textarea>
                            @error('description') <p class="text-xs text-destructive mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- Footer Actions --}}
                        <div class="pt-3 border-t border-border flex items-center justify-end gap-3 bg-muted/5 -mx-6 -mb-6 p-4">
                            <button type="button" class="h-9 px-4 rounded-lg bg-secondary text-secondary-foreground text-xs font-semibold hover:bg-secondary/80 border border-border transition"
                                wire:click="close">Cancel</button>
                            <button type="submit" class="h-9 px-4 rounded-lg bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/95 transition shadow-sm">
                                Submit Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>