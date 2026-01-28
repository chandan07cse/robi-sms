<?php

namespace App\Http\Controllers;

use AdaReach\Sms\Facades\AdaReach;
use AdaReach\Sms\Exceptions\AdaReachException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsController extends Controller
{
    /**
     * Send a single transactional SMS
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string',
        ]);

        try {
            $result = AdaReach::message()
                ->from(config('adarearch.default_sender'))
                ->to($request->phone)
                ->content("Your OTP is: {$request->otp}. Valid for 5 minutes.")
                ->transactional()
                ->send();

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                'message_id' => $result['messageId'],
                'cost' => $result['msgCost'],
            ]);

        } catch (AdaReachException $e) {
            Log::error('Failed to send OTP', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP',
                'error' => $e->getUserMessage(),
            ], 500);
        }
    }

    /**
     * Send promotional SMS to multiple recipients
     */
    public function sendPromotion(Request $request)
    {
        $request->validate([
            'phones' => 'required|array|max:400',
            'phones.*' => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            $result = AdaReach::message()
                ->from(config('adarearch.default_sender'))
                ->toMany($request->phones)
                ->content($request->message)
                ->promotional()
                ->send();

            return response()->json([
                'success' => true,
                'message' => 'Promotional SMS sent successfully',
                'message_id' => $result['messageId'],
                'cost' => $result['msgCost'],
                'count' => $result['msgCount'],
            ]);

        } catch (AdaReachException $e) {
            Log::error('Failed to send promotional SMS', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send promotional SMS',
                'error' => $e->getUserMessage(),
            ], 500);
        }
    }

    /**
     * Check SMS delivery status
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'message_id' => 'required|string',
            'phone' => 'required|string',
        ]);

        try {
            $status = AdaReach::checkStatus(
                sender: config('adarearch.default_sender'),
                messageId: $request->message_id,
                receiver: $request->phone
            );

            return response()->json([
                'success' => true,
                'status' => $status['status'],
                'description' => $status['description'],
                'cost' => $status['msgCost'],
            ]);

        } catch (AdaReachException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check status',
                'error' => $e->getUserMessage(),
            ], 500);
        }
    }

    /**
     * Check account balance
     */
    public function checkBalance()
    {
        try {
            $balance = AdaReach::checkBalance();

            return response()->json([
                'success' => true,
                'gui_balance' => $balance['guiBalance'],
                'api_balance' => $balance['apiBalance'],
            ]);

        } catch (AdaReachException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check balance',
                'error' => $e->getUserMessage(),
            ], 500);
        }
    }

    /**
     * Send order confirmation SMS
     */
    public function sendOrderConfirmation(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'order_id' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        try {
            $message = "Order #{$request->order_id} confirmed! Amount: ৳{$request->amount}. Thank you for shopping with us.";

            $result = AdaReach::message()
                ->from(config('adarearch.default_sender'))
                ->to($request->phone)
                ->content($message)
                ->transactional()
                ->send();

            return response()->json([
                'success' => true,
                'message' => 'Order confirmation sent',
                'message_id' => $result['messageId'],
            ]);

        } catch (AdaReachException $e) {
            Log::error('Failed to send order confirmation', [
                'order_id' => $request->order_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send confirmation',
            ], 500);
        }
    }
}
