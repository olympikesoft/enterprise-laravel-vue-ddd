<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Application\Donation\Handler\CreateDonationHandler;
use App\Application\Donation\Command\CreateDonationCommand;
use App\Application\DTO\Donation\MakeDonationDTO;
use App\Infrastructure\Persistence\Models\Donation as EloquentDonation;
use App\Infrastructure\Persistence\Models\Campaign as EloquentCampaign;
use App\Infrastructure\Persistence\Models\User;
use App\Interfaces\Http\Resources\DonationResource;
use Carbon\Carbon;
use Mockery;
use Mockery\MockInterface;

class DonationControllerStoreTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected EloquentCampaign $campaign;
    protected MockInterface $createDonationHandlerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->campaign = EloquentCampaign::factory()->create([
            'status' => EloquentCampaign::STATUS_ACTIVE
        ]);

        // Mock the handler
        $this->createDonationHandlerMock = Mockery::mock(CreateDonationHandler::class);
        $this->app->instance(CreateDonationHandler::class, $this->createDonationHandlerMock);
    }

    public function test_authenticated_user_can_create_donation_successfully(): void
    {
        $donationData = [
            'campaign_id' => $this->campaign->id,
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => 'USD',
            'message' => $this->faker->sentence,
            'payment_token' => 'tok_' . $this->faker->uuid,
        ];

        $mockedDonation = new EloquentDonation([
            'id' => 1,
            'user_id' => $this->user->id,
            'campaign_id' => $this->campaign->id,
            'amount' => $donationData['amount'],
            'currency' => $donationData['currency'],
            'message' => $donationData['message'],
            'payment_token' => $donationData['payment_token'],
            'payment_status' => EloquentDonation::PAYMENT_STATUS_COMPLETED,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->createDonationHandlerMock
            ->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(function (CreateDonationCommand $command) use ($donationData) {
                $dto = $command->dto;
                return $dto instanceof MakeDonationDTO &&
                       $dto->campaignId === $donationData['campaign_id'] &&
                       abs($dto->amount - $donationData['amount']) < 0.001 &&
                       $dto->currency === $donationData['currency'] &&
                       $dto->message === $donationData['message'] &&
                       $dto->userId === $this->user->id;
            }))
            ->andReturn($mockedDonation);

        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson(route('api.donations.store'), $donationData);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'amount' => $donationData['amount'],
                     'currency' => $donationData['currency'],
                     'payment_status' => EloquentDonation::PAYMENT_STATUS_COMPLETED
                 ]);

        // More detailed check of the resource structure
        $expectedResource = (new DonationResource($mockedDonation))->resolve();
        $response->assertJson($expectedResource);
    }

    public function test_authenticated_user_can_create_donation_without_message(): void
    {
        $donationData = [
            'campaign_id' => $this->campaign->id,
            'amount' => 50.00,
            'currency' => 'EUR',
            'payment_token' => 'tok_no_message_' . $this->faker->uuid,
            // No message provided
        ];

        $mockedDonation = new EloquentDonation([
            'id' => 2,
            'user_id' => $this->user->id,
            'campaign_id' => $this->campaign->id,
            'amount' => $donationData['amount'],
            'currency' => $donationData['currency'],
            'message' => null,
            'payment_token' => $donationData['payment_token'],
            'payment_status' => EloquentDonation::PAYMENT_STATUS_COMPLETED,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->createDonationHandlerMock
            ->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(function (CreateDonationCommand $command) use ($donationData) {
                $dto = $command->dto;
                return $dto instanceof MakeDonationDTO &&
                       $dto->campaignId === $donationData['campaign_id'] &&
                       $dto->message === null;
            }))
            ->andReturn($mockedDonation);

        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson(route('api.donations.store'), $donationData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['message' => null]);
    }

    public function test_donation_with_failed_payment_returns_422(): void
    {
        $donationData = [
            'campaign_id' => $this->campaign->id,
            'amount' => 100.00,
            'currency' => 'USD',
            'payment_token' => 'tok_failed_payment',
        ];

        $mockedFailedDonation = new EloquentDonation([
            'id' => 3,
            'user_id' => $this->user->id,
            'campaign_id' => $this->campaign->id,
            'amount' => $donationData['amount'],
            'currency' => $donationData['currency'],
            'payment_token' => $donationData['payment_token'],
            'payment_status' => EloquentDonation::PAYMENT_STATUS_FAILED,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->createDonationHandlerMock
            ->shouldReceive('handle')
            ->once()
            ->andReturn($mockedFailedDonation);

        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson(route('api.donations.store'), $donationData);

        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'message' => 'Payment failed. Your donation was not processed.',
                     'donation' => (new DonationResource($mockedFailedDonation))->resolve()
                 ]);
    }

    public function test_create_donation_fails_with_validation_errors_for_missing_campaign_id(): void
    {
        $donationData = [
            // 'campaign_id' is missing
            'amount' => 100.00,
            'currency' => 'USD',
            'payment_token' => 'tok_missing_campaign',
        ];

        $this->createDonationHandlerMock->shouldNotReceive('handle');

        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson(route('api.donations.store'), $donationData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['campaign_id']);
    }

    public function test_create_donation_fails_with_validation_errors_for_missing_amount(): void
    {
        $donationData = [
            'campaign_id' => $this->campaign->id,
            // 'amount' is missing
            'currency' => 'USD',
            'payment_token' => 'tok_missing_amount',
        ];

        $this->createDonationHandlerMock->shouldNotReceive('handle');

        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson(route('api.donations.store'), $donationData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['amount']);
    }


    public function test_create_donation_fails_with_validation_errors_for_invalid_amount(): void
    {
        $donationData = [
            'campaign_id' => $this->campaign->id,
            'amount' => -50.00, // Negative amount should be invalid
            'currency' => 'USD',
            'payment_token' => 'tok_invalid_amount',
        ];

        $this->createDonationHandlerMock->shouldNotReceive('handle');

        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson(route('api.donations.store'), $donationData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['amount']);
    }

    public function test_create_donation_fails_with_validation_errors_for_invalid_currency(): void
    {
        $donationData = [
            'campaign_id' => $this->campaign->id,
            'amount' => 100.00,
            'currency' => 'INVALID', // Invalid currency code
            'payment_token' => 'tok_invalid_currency',
        ];

        $this->createDonationHandlerMock->shouldNotReceive('handle');

        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson(route('api.donations.store'), $donationData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['currency']);
    }

}
