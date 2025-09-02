<div class="max-w-md mx-auto">
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Redeem Voucher</h3>
        
        @if($message)
            <div class="mb-4 p-4 rounded-md {{ $messageType === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                <p class="text-sm {{ $messageType === 'success' ? 'text-green-800' : 'text-red-800' }}">
                    {{ $message }}
                </p>
            </div>
        @endif

        <form wire:submit="redeemVoucher" class="space-y-4">
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                    Voucher Code
                </label>
                <input type="text" 
                       id="code"
                       wire:model="code"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 uppercase"
                       placeholder="Enter voucher code"
                       maxlength="20">
                @error('code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" 
                    class="w-full btn btn-primary"
                    wire:loading.attr="disabled">
                <span wire:loading.remove>Redeem Voucher</span>
                <span wire:loading class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </span>
            </button>
        </form>

        @auth
            @if(!auth()->user()->hasLinkedAccount())
                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                    <p class="text-sm text-yellow-800">
                        You need to have a linked game account to redeem vouchers.
                    </p>
                </div>
            @endif
        @else
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
                <p class="text-sm text-blue-800">
                    <a href="{{ route('login') }}" class="underline hover:no-underline">Login</a> 
                    to redeem vouchers.
                </p>
            </div>
        @endauth
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('voucher-message', (event) => {
            if (event.type === 'success') {
                window.showSuccess(event.message);
            } else {
                window.showError(event.message);
            }
        });
    });
</script>
@endpush