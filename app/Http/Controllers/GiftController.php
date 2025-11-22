<?php
// app/Http/Controllers/GiftController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MobileConfig;
use App\Models\CustomerTTHDetail;
use App\Models\CustomerTTH;

class GiftController extends Controller
{
    // Get total hadiah rekapan (tidak terpengaruh filter toko)
    public function getGiftSummary()
    {
        // Ambil config hadiah dari MobileConfig
        $giftConfigs = DB::table(MobileConfig::getTableWithSchema('m'))
            ->where(function($query) {
                $query->where('m.Name', 'like', '%SUMMARY TTH%')
                      ->orWhere('m.Description', 'like', '%SUMMARY TTH%');
            })
            ->get();
        
        $giftSummary = [];
        
        foreach ($giftConfigs as $config) {
            $gifts = explode('|', $config->Value);
            
            foreach ($gifts as $gift) {
                $gift = trim($gift);
                if (!empty($gift)) {
                    // Hitung total per jenis hadiah
                    $total = DB::table(CustomerTTHDetail::getTableWithSchema('d'))
                        ->where('d.Jenis', 'like', '%' . $gift . '%')
                        ->sum('Qty');

                    $unit = $this->getGiftUnit($gift);
                    
                    $giftSummary[] = [
                        'name' => $gift,
                        'total' => $total,
                        'unit' => $unit,
                        'description' => $config->Description
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $giftSummary,
            'total_hadiah' => count($giftSummary)
        ]);
    }

    // Konfirmasi terima/tolak hadiah
    public function confirmGift(Request $request)
    {
        $request->validate([
            'cust_id' => 'required',
            'action' => 'required|in:terima,tolak',
            'failed_reason' => 'required_if:action,tolak',
            'tth_numbers' => 'required|array'
        ]);

        DB::beginTransaction();
        try {
            $currentDate = now()->toDateString();
            $action = $request->action;
            $custId = $request->cust_id;
            $tthNumbers = $request->tth_numbers;
            $failedReason = $request->failed_reason;

            foreach ($tthNumbers as $tthNo) {
                // Cek apakah data TTH exists
                $tthExists = DB::table(CustomerTTH::getTableWithSchema('t'))
                    ->where('TTHNo', $tthNo)
                    ->where('CustID', $custId)
                    ->exists();

                if ($tthExists) {
                    if ($action === 'terima') {
                        // Jika terima, update received = 1 dan received date
                        DB::table(CustomerTTH::getTableWithSchema('t'))
                            ->where('TTHNo', $tthNo)
                            ->where('CustID', $custId)
                            ->update([
                                'Received' => 1,
                                'ReceivedDate' => $currentDate,
                                'FailedReason' => null
                            ]);
                    } else {
                        // Jika tolak, update received = 0 dan failed reason
                        DB::table(CustomerTTH::getTableWithSchema('t'))
                            ->where('TTHNo', $tthNo)
                            ->where('CustID', $custId)
                            ->update([
                                'Received' => 0,
                                'ReceivedDate' => $currentDate,
                                'FailedReason' => $failedReason
                            ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Hadiah berhasil di{$action}",
                'action' => $action
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateGiftStatus(Request $request, $id)
{
    $request->validate([
        'action' => 'required|in:terima'
    ]);

    try {
        DB::beginTransaction();

        // Lock row to avoid race condition
        $tth = DB::table(CustomerTTH::getTableWithSchema('t'))
            ->where('ID', $id)
            ->lockForUpdate()
            ->first();

        if (!$tth) {
            DB::rollBack();
            throw new \Exception('Data TTH tidak ditemukan');
        }

        // Only allow 0 -> 1 (tolak -> terima)
        if ($tth->Received === 0 && $request->action === 'terima') {

            DB::table(CustomerTTH::getTableWithSchema('t'))
                ->where('ID', $id)
                ->update([
                    'Received'     => 1,
                    'ReceivedDate' => now()->toDateString(),
                    'FailedReason' => null
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diubah menjadi terima'
            ]);
        }

        DB::rollBack();
        throw new \Exception('Aksi tidak diperbolehkan');

    } catch (\Throwable $e) {
        DB::rollBack();
        throw $e;
    }
}

    

    // Get alasan tolak options
    public function getFailedReasonOptions()
    {
        $reasons = [
            'Pemilik Sibuk',
            'Toko Tutup'
        ];

        return response()->json([
            'success' => true,
            'data' => $reasons
        ]);
    }

    // Helper function untuk menentukan unit hadiah
    private function getGiftUnit($giftName)
    {
        $lowerGift = strtolower($giftName);
        
        if (strpos($lowerGift, 'emas') !== false) {
            return 'Buah';
        } elseif (strpos($lowerGift, 'voucher') !== false) {
            return 'Lembar';
        } else {
            return 'Unit';
        }
    }
}