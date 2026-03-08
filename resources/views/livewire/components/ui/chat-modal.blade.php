<div
    x-data="{ show: @entangle('isOpen') }" 
    x-show="show"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    {{-- This outer div's only job is the backdrop and managing the state. --}}
    class="fixed inset-0 z-[60]" 
    aria-labelledby="chat-modal-title"
    role="dialog"
    aria-modal="true"
    style="display: none;"
>
    {{-- Background overlay (Backdrop) --}}
    <div 
        x-on:click="show = false; $wire.closeModal()" 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity">
    </div>

    {{-- 
        *** FIX: This is the actual chat panel. We fix its position 
        relative to the screen here, ignoring the outer modal wrappers. 
        bottom-[4.5rem] places it above the button's bottom-6 (1.5rem) margin + button height (~3rem). 
    --}}
    <div class="fixed bottom-[4.5rem] right-6 w-full max-w-sm h-[70vh] flex flex-col z-[70] shadow-2xl"> 
        
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left w-full h-full flex flex-col">
            
            {{-- CHAT HEADER --}}
            <div class="flex items-center justify-between p-4 bg-black text-white rounded-t-lg shadow-md">
                <h3 class="text-lg font-semibold leading-6" id="chat-modal-title">
                    Live Chat Assistant 🤖
                </h3>
                <button
                    type="button"
                    x-on:click="show = false; $wire.closeModal()"
                    class="text-gray-300 hover:text-white transition"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            {{-- CHAT MESSAGE HISTORY (The Scrolling Area) --}}
            <div class="flex-grow p-4 overflow-y-auto bg-gray-50 space-y-4">
                {{-- Placeholder Messages --}}
                <div class="flex justify-start">
                    <div class="bg-gray-200 p-3 rounded-lg max-w-[80%]">
                        <p class="text-sm text-gray-800">Hello! I'm your virtual assistant. How can I help you today?</p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <div class="bg-black text-white p-3 rounded-lg max-w-[80%]">
                        <p class="text-sm">I need help navigating the website sections.</p>
                    </div>
                </div>
            </div>
            
            {{-- CHAT INPUT AREA (The Form) --}}
            <div class="p-4 border-t border-gray-200 bg-white">
                <form wire:submit.prevent="sendMessage"> 
                    <div class="flex items-center">
                        <input
                            type="text"
                            wire:model.live="message"
                            placeholder="Type your message..."
                            class="flex-grow border border-gray-300 rounded-lg p-2 focus:ring-black focus:border-black transition"
                        >
                        <button
                            type="submit"
                            class="ml-2 bg-black hover:bg-gray-800 text-white p-2 rounded-lg transition-colors"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</div>