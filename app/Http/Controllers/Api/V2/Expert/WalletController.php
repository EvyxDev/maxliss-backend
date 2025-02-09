<?php

namespace App\Http\Controllers\Api\V2\Expert;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletExpertResource;
use App\Models\ExpertTranaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $wallets = ExpertTranaction::where('expert_id',auth()->guard('expert')->user()->id)->get();

        $data['total_balance'] = auth()->guard('expert')->user()->amount; ;  
        $data['transactions'] =  WalletExpertResource::collection($wallets);
        return response()->json([
            'result' => true,
            'message' => 'Transactions fetched successfully',
            'data' =>$data
        ], 200);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
