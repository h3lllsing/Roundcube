<?php

namespace Tests\Unit;

use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Voip;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    private ServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
        $this->provider = ServiceProvider::factory()->create();
    }

    public function test_fillable_attributes(): void
    {
        $fillable = $this->provider->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('cost', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('user_id', $fillable);
        $this->assertContains('module_id', $fillable);
    }

    public function test_password_is_encrypted(): void
    {
        $provider = ServiceProvider::factory()->create(['password' => 'secret-value']);

        $this->assertSame('secret-value', $provider->password);
        $this->assertNotSame('secret-value', $provider->getRawOriginal('password'));
    }

    public function test_hidden_attributes_not_serialized(): void
    {
        $provider = ServiceProvider::factory()->create(['password' => encrypt('secret')]);
        $array = $provider->toArray();

        $this->assertArrayNotHasKey('password', $array);
    }

    public function test_expiry_trackers_relationship(): void
    {
        $tracker = ExpiryTracker::factory()->create(['service_provider_id' => $this->provider->id]);

        $this->assertTrue($this->provider->expiryTrackers->contains($tracker));
    }

    public function test_other_services_relationship(): void
    {
        $service = OtherService::factory()->create(['service_provider_id' => $this->provider->id]);

        $this->assertTrue($this->provider->otherServices->contains($service));
    }

    public function test_voip_relationship(): void
    {
        $voip = Voip::factory()->create(['service_provider_id' => $this->provider->id]);

        $this->assertTrue($this->provider->voip->contains($voip));
    }

    public function test_domain_emails_relationship(): void
    {
        $email = DomainEmail::factory()->create(['service_provider_id' => $this->provider->id]);

        $this->assertTrue($this->provider->domainEmails->contains($email));
    }

    public function test_user_relationship(): void
    {
        $user = User::factory()->create();
        $provider = ServiceProvider::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($provider->user->is($user));
    }

    public function test_module_relationship(): void
    {
        $module = \App\Models\Module::factory()->create();
        $this->provider->update(['module_id' => $module->id]);
        $this->provider->refresh();

        $this->assertNotNull($this->provider->module);
        $this->assertTrue($this->provider->module->is($module));
    }

    public function test_start_date_cast(): void
    {
        $provider = ServiceProvider::factory()->create(['start_date' => '2026-01-15']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $provider->start_date);
    }

    public function test_expiry_date_cast(): void
    {
        $provider = ServiceProvider::factory()->create(['expiry_date' => '2026-12-31']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $provider->expiry_date);
    }
}
