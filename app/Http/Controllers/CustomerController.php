<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\CustomerTTH;
use App\Models\CustomerTTHDetail;

class CustomerController extends Controller
{
    public $FILTER_SHOP_NAME = 'Semua Toko';
    public function getCustomers(Request $request)
    {
        $shopName = $request->get('shop_name');
        $perPage = (int) ($request->get('per_page') ?? 10); // default 10 per page
    
        // Query utama
        $query = DB::table(Customer::getTableWithSchema('c'))
            ->select(
                'c.CustID',
                'c.Name',
                'c.Address',
                'c.BranchCode',
                'c.PhoneNo'
            )
            ->leftJoin(CustomerTTH::getTableWithSchema('t'), 'c.CustID', '=', 't.CustID')
            ->groupBy(
                'c.CustID',
                'c.Name',
                'c.Address',
                'c.BranchCode',
                'c.PhoneNo'
            );
    
        if ($shopName && $shopName !== $this->FILTER_SHOP_NAME) {
            $query->where('c.Name', 'like', '%' . $shopName . '%');
        }
    
        // ⬅️ PAGINATION HERE
        $paginated = $query->paginate($perPage);
    
        // Transform each customer item
        $data = $paginated->getCollection()->map(function ($customer) {
    
            // Get TTH docs
            $tthData = DB::table(CustomerTTH::getTableWithSchema('t'))
                ->where('t.CustID', $customer->CustID)
                ->get()
                ->map(function ($tth) {
                    $status = $this->determineStatus($tth->Received, $tth->FailedReason);
                    // Get TTH details
                    $details = DB::table(CustomerTTHDetail::getTableWithSchema('d'))
                        ->where('d.TTHNo', $tth->TTHNo)
                        ->where('d.TTOTTPNo', $tth->TTOTTPNo)
                        ->get()
                        ->map(function ($detail) {
                            return [
                                'ID' => $detail->ID,
                                'TTHNo' => $detail->TTHNo,
                                'TTOTTPNo' => $detail->TTOTTPNo,
                                'Jenis' => $detail->Jenis,
                                'Qty' => $detail->Qty,
                                'Unit' => $detail->Unit,
                            ];
                        });
    
                    return [
                        'ID' => $tth->ID,
                        'TTHNo' => $tth->TTHNo,
                        'TTOTTPNo' => $tth->TTOTTPNo,
                        'DocDate' => $tth->DocDate,
                        'Received' => $tth->Received,
                        'ReceivedDate' => $tth->ReceivedDate,
                        'FailedReason' => $tth->FailedReason,
                        'details' => $details,
                        'status' => $status,
                    ];
                });
            $overallStatus = $this->getOverallCustomerStatus($tthData);
            return [
                'CustID' => $customer->CustID,
                'Name' => $customer->Name,
                'Address' => $customer->Address,
                'PhoneNo' => $customer->PhoneNo,
                'BranchCode' => $customer->BranchCode,
                'TTHDocuments' => $tthData,
                'Status' => $overallStatus,
            ];
        });
    
        // Replace the collection inside paginator
        $paginated->setCollection($data);
    
        return response()->json([
            'success' => true,
            'data' => $paginated
        ]);
    }
    

    public function getFilterOptions()
    {
        $shops = DB::table(Customer::getTableWithSchema('c'))
            ->distinct()
            ->pluck('c.Name')
            ->prepend($this->FILTER_SHOP_NAME);

        return response()->json([
            'success' => true,
            'shops' => $shops
        ]);
    }

    private function determineStatus($received, $failedReason)
    {
        if ($received === 1) {
            return 'Sudah Diterima';
        } elseif ($received === 0 && !empty($failedReason)) {
            return 'Gagal Diberikan';
        } else {
            return 'Belum Diberikan';
        }
    }

    private function getOverallCustomerStatus($tthData)
    {
        $hasPending = false;
        $hasAccepted = false;
        $hasRejected = false;

        foreach ($tthData as $tth) {
            if ($tth['status'] === 'Belum Diberikan') {
                $hasPending = true;
            } elseif ($tth['status'] === 'Sudah Diterima') {
                $hasAccepted = true;
            } elseif ($tth['status'] === 'Gagal Diberikan') {
                $hasRejected = true;
            }
        }

        // Prioritaskan status yang ditampilkan
        if ($hasPending) {
            return 'Belum Diberikan';
        } elseif ($hasAccepted) {
            return 'Sudah Diterima';
        } elseif ($hasRejected) {
            return 'Gagal Diberikan';
        } else {
            return 'Belum Diberikan';
        }
    }

    // Test connection
    public function testConnection()
    {
        try {
            $result = DB::table(Customer::getTableWithSchema('c'))->limit(1)->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Database connection successful',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}