<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Application\Campaign\Handler\CreateCampaignHandler;
use App\Application\Campaign\Command\CreateCampaignCommand;
use App\Application\DTO\Campaign\CreateCampaignDTO;
use App\Infrastructure\Persistence\Models\Campaign as EloquentCampaign; // Eloquent Model
use App\Infrastructure\Persistence\Models\User as ModelsUser;
use App\Infrastructure\Persistence\Models\User;
use App\Interfaces\Http\Resources\CampaignResource;
use Carbon\Carbon;
use Mockery;
use Mockery\MockInterface;

class CampaignControllerStoreTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ModelsUser $user;
    protected MockInterface $createCampaignHandlerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(); // Authenticated user

        // Mock the handler
        $this->createCampaignHandlerMock = Mockery::mock(CreateCampaignHandler::class);
        $this->app->instance(CreateCampaignHandler::class, $this->createCampaignHandlerMock);
    }

    public function test_authenticated_user_can_create_campaign_successfully(): void
    {
        $startDate = Carbon::now()->addDay()->toDateString();
        $endDate = Carbon::now()->addMonth()->toDateString();

        $campaignData = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'goal_amount' => $this->faker->randomFloat(2, 100, 10000),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $mockedCampaign = new EloquentCampaign([
            'id' => 1,
            'user_id' => $this->user->id,
            'status' => EloquentCampaign::STATUS_PENDING,
            ...$campaignData,
            'start_date' => Carbon::parse($startDate),
            'end_date' => Carbon::parse($endDate),
        ]);


        $this->createCampaignHandlerMock
            ->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(function (CreateCampaignCommand $command) use ($campaignData, $startDate, $endDate) {
                $dto = $command->dto;
                return $dto instanceof CreateCampaignDTO &&
                       $dto->title === $campaignData['title'] &&
                       $dto->description === $campaignData['description'] &&
                       abs($dto->goalAmount - $campaignData['goal_amount']) < 0.001 && // For float comparison
                       $dto->startDate->equalTo(Carbon::parse($startDate)) &&
                       $dto->endDate->equalTo(Carbon::parse($endDate)) &&
                       $dto->userId === $this->user->id;
            }))
            ->andReturn($mockedCampaign); // Handler returns the created campaign

        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson(route('api.campaigns.store'), $campaignData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => $campaignData['title']]); // Basic check of resource output

        // More detailed check of the resource structure if needed
        $expectedResource = (new CampaignResource($mockedCampaign))->resolve();
        $response->assertJson($expectedResource);
    }

    public function test_create_campaign_fails_with_validation_errors_for_missing_title(): void
    {
        $campaignData = [
            // 'title' is missing
            'description' => $this->faker->paragraph,
            'goal_amount' => 1000,
            'start_date' => Carbon::now()->addDay()->toDateString(),
            'end_date' => Carbon::now()->addMonth()->toDateString(),
        ];

        $this->createCampaignHandlerMock->shouldNotReceive('handle'); // Handler should not be called

        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson(route('api.campaigns.store'), $campaignData);

        $response->assertStatus(422) // Unprocessable Entity for validation errors
                 ->assertJsonValidationErrors(['title']);
    }

    public function test_unauthenticated_user_cannot_create_campaign(): void
    {
        $campaignData = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'goal_amount' => 1000,
            'start_date' => Carbon::now()->addDay()->toDateString(),
            'end_date' => Carbon::now()->addMonth()->toDateString(),
        ];

        $this->createCampaignHandlerMock->shouldNotReceive('handle');

        $response = $this->postJson(route('api.campaigns.store'), $campaignData);

        $response->assertStatus(401); // Unauthorized
    }
}
