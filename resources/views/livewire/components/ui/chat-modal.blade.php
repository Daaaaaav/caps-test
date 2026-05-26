<div
    x-data="{ show: @entangle('isOpen') }" 
    x-show="show"
    x-transition:enter="ease-out duration-300 transform"
    x-transition:enter-start="opacity-0 translate-y-8 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="ease-in duration-200 transform"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-8 scale-95"
    class="fixed inset-0 z-[60]" 
    aria-labelledby="chat-modal-title"
    role="dialog"
    aria-modal="true"
    style="display: none;"
>
    {{-- Background overlay (Backdrop) --}}
    <div 
        x-on:click="show = false; $wire.closeModal()" 
        class="fixed inset-0 bg-black/60 backdrop-blur-md transition-opacity duration-300">
    </div>

    {{-- Chat Drawer Container --}}
    <div class="fixed bottom-[5rem] right-6 w-full max-w-sm h-[70vh] flex flex-col z-[70] transition-all duration-300"> 
        
        <div class="relative transform overflow-hidden rounded-2xl border border-border bg-card shadow-2xl w-full h-full flex flex-col">
            
            {{-- CHAT HEADER --}}
            <div class="flex items-center justify-between p-4.5 bg-sidebar text-sidebar-foreground border-b border-sidebar-border shadow-sm">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center border border-emerald-500/30">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold tracking-tight text-white" id="chat-modal-title">
                            Live Assistant
                        </h3>
                        <p class="text-[10px] text-emerald-400 font-semibold tracking-wide uppercase mt-0.5">Online Now</p>
                    </div>
                </div>
                <button
                    type="button"
                    x-on:click="show = false; $wire.closeModal()"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-sidebar-foreground/70 hover:text-white hover:bg-white/10 transition"
                >
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            {{-- CHAT MESSAGE HISTORY --}}
            <div class="flex-grow p-4 overflow-y-auto bg-muted/20 space-y-4">
                {{-- Assistant Message --}}
                <div class="flex justify-start">
                    <div class="bg-card border border-border px-3.5 py-2.5 rounded-2xl rounded-tl-none max-w-[85%] shadow-sm">
                        <p class="text-xs text-foreground leading-relaxed">Hello! I'm your virtual assistant. How can I help you today?</p>
                    </div>
                </div>

                {{-- User Message --}}
                <div class="flex justify-end">
                    <div class="bg-primary text-primary-foreground px-3.5 py-2.5 rounded-2xl rounded-tr-none max-w-[85%] shadow-sm">
                        <p class="text-xs leading-relaxed">I need help navigating the website sections.</p>
                    </div>
                </div>
            </div>
            
            {{-- CHAT INPUT AREA --}}
            <div class="p-4 border-t border-border bg-card">
                <form wire:submit.prevent="sendMessage"> 
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            wire:model.live="message"
                            placeholder="Type your message..."
                            class="flex-grow h-9 px-3.5 border border-input rounded-xl bg-background text-xs text-foreground placeholder:text-muted-foreground/60 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                        >
                        <button
                            type="submit"
                            class="w-9 h-9 shrink-0 flex items-center justify-center bg-primary hover:bg-primary/95 text-primary-foreground rounded-xl transition shadow-sm"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</div>