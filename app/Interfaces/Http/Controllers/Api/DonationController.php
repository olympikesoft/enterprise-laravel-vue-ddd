<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MakeDonationHttpRequest;
use App\Http\Resources\DonationResource;
use App\Application\Donation\Command\CreateDonationCommand;
use App\Application\Donation\Handler\CreateDonationHandler;
use App\Application\DTO\Donation\MakeDonationDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DonationController extends Controller
{
    public function store(MakeDonationHttpRequest $request, CreateDonationHandler $handler): JsonResponse
    {
        $validated = $request->validated();


        $dto = new MakeDonationDTO(
            campaignId: (int) $validated['campaign_id'],
            amount: (float) $validated['amount'],
            currency: $validated['currency'],
            userId: Auth::id(), // Can be null if user is not logged in
            donorName: $validated['donor_name'] ?? (Auth::check() ? Auth::user()->name : null),
            message: $validated['message'] ?? null,
            paymentToken: $validated['payment_token'],
        );

        $command = new CreateDonationCommand($dto);
        $donation = $handler->handle($command);

        if ($donation->payment_status === \App\Infrastructure\Persistence\Models\Donation::PAYMENT_STATUS_FAILED) {
            return response()->json([
                'message' => 'Payment failed. Your donation was not processed.',
                'donation' => new DonationResource($donation) // Show the failed donation attempt
            ], 422); // Unprocessable Entity
        }

        return response()->json(new DonationResource($donation), 201);
    }
}
