<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VoucherRedemption extends Component
{
    public $code = '';
    public $message = '';
    public $messageType = '';

    protected $rules = [
        'code' => 'required|string|min:3|max:20',
    ];

    public function redeemVoucher()
    {
        $this->validate();

        if (!Auth::check()) {
            $this->setMessage('You must be logged in to redeem vouchers.', 'error');
            return;
        }

        $user = Auth::user();
        
        if (!$user->hasLinkedAccount()) {
            $this->setMessage('You must have a linked game account to redeem vouchers.', 'error');
            return;
        }

        DB::beginTransaction();

        try {
            $voucher = DB::table('web_vouchers')
                ->where('code', strtoupper($this->code))
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->first();

            if (!$voucher) {
                $this->setMessage('Invalid or expired voucher code.', 'error');
                DB::rollBack();
                return;
            }

            // Check if already redeemed by this account
            $alreadyRedeemed = DB::table('web_voucher_redemptions')
                ->where('voucher_id', $voucher->id)
                ->where('account_id', $user->account_id)
                ->exists();

            if ($alreadyRedeemed) {
                $this->setMessage('You have already redeemed this voucher.', 'error');
                DB::rollBack();
                return;
            }

            // Check usage limit
            $usageCount = DB::table('web_voucher_redemptions')
                ->where('voucher_id', $voucher->id)
                ->count();

            if ($usageCount >= $voucher->max_uses) {
                $this->setMessage('This voucher has reached its usage limit.', 'error');
                DB::rollBack();
                return;
            }

            // Process reward
            $rewardData = json_decode($voucher->reward_data, true);
            $this->processReward($user->account_id, $rewardData);

            // Record redemption
            DB::table('web_voucher_redemptions')->insert([
                'voucher_id' => $voucher->id,
                'account_id' => $user->account_id,
                'ip_address' => request()->ip(),
                'redeemed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update usage count
            DB::table('web_vouchers')
                ->where('id', $voucher->id)
                ->increment('used_count');

            DB::commit();

            $this->setMessage('Voucher redeemed successfully!', 'success');
            $this->code = '';

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Voucher redemption failed', [
                'code' => $this->code,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            $this->setMessage('An error occurred while redeeming the voucher.', 'error');
        }
    }

    private function processReward($accountId, $rewardData)
    {
        if (isset($rewardData['coins']) && $rewardData['coins'] > 0) {
            DB::connection('mysql')->table('account')
                ->where('id', $accountId)
                ->increment('coins', $rewardData['coins']);
        }

        if (isset($rewardData['cash']) && $rewardData['cash'] > 0) {
            DB::connection('mysql')->table('account')
                ->where('id', $accountId)
                ->increment('cash', $rewardData['cash']);
        }

        if (isset($rewardData['gold']) && $rewardData['gold'] > 0) {
            // Add gold to all characters
            DB::connection('player')->table('player')
                ->where('account_id', $accountId)
                ->increment('gold', $rewardData['gold']);
        }

        // Handle items (would need more complex implementation)
        if (isset($rewardData['items']) && is_array($rewardData['items'])) {
            // Implementation for adding items to inventory would go here
            \Log::info('Items reward processing needed', [
                'account_id' => $accountId,
                'items' => $rewardData['items']
            ]);
        }
    }

    private function setMessage($message, $type)
    {
        $this->message = $message;
        $this->messageType = $type;
        
        $this->dispatch('voucher-message', [
            'message' => $message,
            'type' => $type
        ]);
    }

    public function render()
    {
        return view('livewire.voucher-redemption');
    }
}