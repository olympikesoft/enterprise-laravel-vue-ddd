<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Infrastructure\Persistence\Models\Campaign as EloquentCampaign;
use App\Application\Campaign\Handler\UpdateCampaignHandler;
use App\Application\Campaign\Command\UpdateCampaignCommand;
use App\Application\DTO\Campaign\UpdateCampaignDTO;
use App\Infrastructure\Persistence\Models\User;
use App\Interfaces\Http\Resources\CampaignResource;
use Carbon\Carbon;
use Mockery;
use Mockery\MockInterface;

class CampaignControllerUpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $owner, $otherUser;
    protected EloquentCampaign $campaign;
    protected MockInterface $updateCampaignHandlerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create();
        $this->otherUser = User::factory()->create();

        $this->campaign = EloquentCampaign::factory()->create([
            'user_id' => $this->owner->id,
            'status' => EloquentCampaign::STATUS_PENDING, // Updatable status
        ]);

        $this->updateCampaignHandlerMock = Mockery::mock(UpdateCampaignHandler::class);
        $this->app->instance(UpdateCampaignHandler::class, $this->updateCampaignHandlerMock);
    }

    public function test_owner_can_update_their_campaign(): void
    {
        $updateData = [
            'title' => 'Updated Campaign Title',
            'description' => 'Updated description.',
        ];

        $updatedCampaignModel = clone $this->campaign;
        $updatedCampaignModel->title = $updateData['title'];
        $updatedCampaignModel->description = $updateData['description'];

        $this->updateCampaignHandlerMock
            ->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(function (UpdateCampaignCommand $command) use ($updateData) {
                $dto = $command->dto;
                $userId = $command->userId;
                return $dto instanceof UpdateCampaignDTO &&
                       $dto->campaignId === $this->campaign->id &&
                       $dto->title === $updateData['title'] &&
                       $dto->description === $updateData['description'] &&
                       $command->userId === $userId;
            }))
            ->andReturn($updatedCampaignModel);

        $response = $this->actingAs($this->owner, 'sanctum')
                         ->putJson(route('api.campaigns.update', $this->campaign->id), $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Updated Campaign Title']);

        $expectedResource = (new CampaignResource($updatedCampaignModel))->resolve();
        $response->assertJson($expectedResource);
    }

    public function test_non_owner_cannot_update_campaign(): void
    {
        $updateData = ['title' => 'Attempted Update'];

        $this->updateCampaignHandlerMock->shouldNotReceive('handle');

        $response = $this->actingAs($this->otherUser, 'sanctum')
                         ->putJson(route('api.campaigns.update', $this->campaign->id), $updateData);

        $response->assertStatus(403); // Forbidden (due to AuthorizationException in controller)
    }

    public function test_cannot_update_campaign_in_non_updatable_status_by_user(): void
    {
        $this->campaign->status = 'active';

        $updateData = [
            'title' => 'Attempted Update Title',
            'description' => 'Attempted Update Description',
        ];

        $this->updateCampaignHandlerMock
            ->shouldReceive('handle')
            ->once()
            ->andThrow(new \Illuminate\Auth\Access\AuthorizationException('Cannot update campaign in this status'));

        $response = $this->actingAs($this->owner, 'sanctum')
                         ->putJson(route('api.campaigns.update', $this->campaign->id), $updateData);

        $response->assertStatus(403);
    }

    public function test_update_campaign_validation_failure(): void
    {
        $updateData = ['title' => '']; // Invalid title

        $this->updateCampaignHandlerMock->shouldNotReceive('handle');

        $response = $this->actingAs($this->owner, 'sanctum')
                         ->putJson(route('api.campaigns.update', $this->campaign->id), $updateData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);
    }
}
