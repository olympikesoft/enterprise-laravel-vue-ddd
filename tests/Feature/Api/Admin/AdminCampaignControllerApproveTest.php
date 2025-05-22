<?php

namespace Tests\Feature\Api\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Infrastructure\Persistence\Models\Campaign as EloquentCampaign;
use App\Application\Campaign\Handler\ApproveCampaignHandler;
use App\Application\Campaign\Command\ApproveCampaignCommand;
use App\Infrastructure\Persistence\Models\User;
use App\Interfaces\Http\Resources\CampaignResource;
use Mockery;
use Mockery\MockInterface;

class AdminCampaignControllerApproveTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser, $regularUser;
    protected EloquentCampaign $pendingCampaign;
    protected MockInterface $approveCampaignHandlerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create(['is_admin' => true]); // Or assign a role
        $this->regularUser = User::factory()->create();

        $this->pendingCampaign = EloquentCampaign::factory()->create([
            'status' => EloquentCampaign::STATUS_PENDING,
        ]);

        $this->approveCampaignHandlerMock = Mockery::mock(ApproveCampaignHandler::class);
        $this->app->instance(ApproveCampaignHandler::class, $this->approveCampaignHandlerMock);
    }

    public function test_admin_can_approve_campaign(): void
    {
        $approvedCampaignModel = clone $this->pendingCampaign;
        $approvedCampaignModel->status = EloquentCampaign::STATUS_ACTIVE;

        $this->approveCampaignHandlerMock
            ->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(function (ApproveCampaignCommand $command) {
                return $command->campaignId === $this->pendingCampaign->id &&
                       $command->adminUserId === $this->adminUser->id;
            }))
            ->andReturn($approvedCampaignModel);

        $response = $this->actingAs($this->adminUser, 'sanctum') // Ensure actingAs uses the 'sanctum' guard
                         ->postJson(route('api.admin.campaigns.approve', $this->pendingCampaign->id));

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => EloquentCampaign::STATUS_ACTIVE]);

        $expectedResource = (new CampaignResource($approvedCampaignModel))->resolve();
        $response->assertJson($expectedResource);
    }

    public function test_unauthenticated_user_cannot_approve_campaign(): void
    {
         $this->approveCampaignHandlerMock->shouldNotReceive('handle');

        $response = $this->postJson(route('api.admin.campaigns.approve', $this->pendingCampaign->id));

        $response->assertStatus(401);
    }
}
