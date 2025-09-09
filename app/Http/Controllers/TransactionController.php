<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
use App\Http\Resources\TransactionResource;
use Illuminate\Database\Eloquent\Collection;

class TransactionController extends ApiController
{
    public function index(): JsonResponse
    {
        $transactions = Transaction::latest()->paginate(5);
        $collection = TransactionResource::collection($transactions)->response()->getData();
        return $this->successResponse(
            data: [
                'transactions' => $collection->data,
                'links' => $collection->links,
                'meta' => $collection->meta,
            ],
        );
    }

    public function chart(): JsonResponse
    {
        $numOfMonths = 12;
        $transactionStatus = 1;
        $successfulTransactions = Transaction::getChartData(
            month: $numOfMonths,
            status: $transactionStatus
        )->get();
        $finalChartData = $this->chartData(
            transactions: $successfulTransactions,
            numOfMonths: $numOfMonths,
        );
        return $this->successResponse(
            data: $finalChartData,
        );
    }

    private function chartData(Collection $transactions, int $numOfMonths): array
    {
        $monthName = $transactions->map(function($transaction) {
            return verta($transaction->created_at)->format('%B %Y');
        });
        $amount = $transactions->map(function($transaction) {
            return $transaction->amount;
        });

        $result = [];
        foreach ($monthName as $index => $month) {
            if (!isset($result[$month])) {
                $result[$month] = 0;
            };
            $result[$month] += $amount[$index];
        };

        if (count($result) != $numOfMonths) {
            $sampleData = [];
            for ($i = 0; $i < $numOfMonths; $i++) {
                $month = verta()->subMonths($i)->format('%B %Y');
                $sampleData[$month] = 0;
            };
            $result = array_merge($sampleData, $result);
        };

        $finalData = [];
        foreach ($result as $month => $amount) {
            array_push($finalData, ['month' => $month, 'value' => $amount]);
        };
        return $finalData;
    }
}
