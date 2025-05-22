<?php

namespace App\Interfaces\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use App\Application\Donation\Command\CreateDonationCommand;
use App\Application\Donation\Handler\CreateDonationHandler;
use App\Application\Donation\Handler\ListUserDonationsHandler;
use App\Application\Donation\Query\ListUserDonationsQuery;
use App\Application\DTO\Donation\MakeDonationDTO;
use App\Infrastructure\Persistence\Models\Donation;
use App\Interfaces\Http\Requests\MakeDonationHttpRequest;
use App\Interfaces\Http\Resources\DonationCollection;
use App\Interfaces\Http\Resources\DonationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            userId: Auth::id(),
            message: $validated['message'] ?? null,
        );

        $command = new CreateDonationCommand($dto);
        $donation = $handler->handle($command);

        if ($donation->payment_status === Donation::PAYMENT_STATUS_FAILED) {
            return response()->json([
                'message' => 'Payment failed. Your donation was not processed.',
                'donation' => new DonationResource($donation)
            ], 422); // Unprocessable Entity
        }

        return response()->json(new DonationResource($donation), 201);
    }

      // Authenticated user lists their own donations
      public function myDonations(Request $request, ListUserDonationsHandler $handler): DonationCollection
      {
          $query = new ListUserDonationsQuery(
              userId: Auth::id(),
              sortBy: $request->query('sort_by', 'created_at'),
              sortDirection: $request->query('sort_direction', 'desc'),
              perPage: $request->query('per_page', 15)
          );
          $donations = $handler->handle($query);
          // TODO: Create DonationCollection Resource if it doesn't exist
          return new DonationCollection($donations);
      }
}
