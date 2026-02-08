<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\ChatMessage;
use App\Notifications\ChatMessageNotification;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ChatModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_chat_access_requires_permission(): void
    {
        $tenant = Tenant::create([
            'name' => 'Tenant A',
            'slug' => 'tenant-a',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $this->withSession([TenantContext::SESSION_KEY => $tenant->id])
            ->actingAs($user)
            ->get('/chat')
            ->assertForbidden();
    }

    public function test_chat_message_send_within_tenant(): void
    {
        Notification::fake();

        $tenant = Tenant::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b',
            'is_active' => true,
        ]);

        $sender = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $recipient = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $access = Permission::create(['name' => 'chat-access']);
        $send = Permission::create(['name' => 'chat-message-send']);
        $sender->givePermissionTo([$access, $send]);

        $response = $this->withSession([TenantContext::SESSION_KEY => $tenant->id])
            ->actingAs($sender)
            ->post('/chat/messages', [
                'recipient_id' => $recipient->id,
                'message' => 'Hello from chat',
            ]);

        $response->assertOk()->assertJsonFragment([
            'recipient_id' => $recipient->id,
            'message' => 'Hello from chat',
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'tenant_id' => $tenant->id,
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'message' => 'Hello from chat',
        ]);

        Notification::assertSentTo($recipient, ChatMessageNotification::class);
    }

    public function test_chat_rejects_other_tenant(): void
    {
        $tenantA = Tenant::create([
            'name' => 'Tenant C',
            'slug' => 'tenant-c',
            'is_active' => true,
        ]);

        $tenantB = Tenant::create([
            'name' => 'Tenant D',
            'slug' => 'tenant-d',
            'is_active' => true,
        ]);

        $sender = User::factory()->create([
            'tenant_id' => $tenantA->id,
        ]);

        $recipient = User::factory()->create([
            'tenant_id' => $tenantB->id,
        ]);

        $access = Permission::create(['name' => 'chat-access']);
        $send = Permission::create(['name' => 'chat-message-send']);
        $sender->givePermissionTo([$access, $send]);

        $this->withSession([TenantContext::SESSION_KEY => $tenantA->id])
            ->actingAs($sender)
            ->post('/chat/messages', [
                'recipient_id' => $recipient->id,
                'message' => 'Cross tenant message',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('chat_messages', [
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'message' => 'Cross tenant message',
        ]);
    }

    public function test_chat_broadcast_sends_to_all_tenant_users(): void
    {
        Notification::fake();

        $tenant = Tenant::create([
            'name' => 'Tenant E',
            'slug' => 'tenant-e',
            'is_active' => true,
        ]);

        $sender = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $recipientOne = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $recipientTwo = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $access = Permission::create(['name' => 'chat-access']);
        $send = Permission::create(['name' => 'chat-message-send']);
        $sender->givePermissionTo([$access, $send]);

        $response = $this->withSession([TenantContext::SESSION_KEY => $tenant->id])
            ->actingAs($sender)
            ->post('/chat/messages', [
                'broadcast_all' => true,
                'message' => 'Broadcast message',
            ]);

        $response->assertOk()->assertJsonFragment([
            'message' => 'Broadcast message',
            'is_broadcast' => true,
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'tenant_id' => $tenant->id,
            'sender_id' => $sender->id,
            'recipient_id' => $sender->id,
            'message' => 'Broadcast message',
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'tenant_id' => $tenant->id,
            'sender_id' => $sender->id,
            'recipient_id' => $recipientOne->id,
            'message' => 'Broadcast message',
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'tenant_id' => $tenant->id,
            'sender_id' => $sender->id,
            'recipient_id' => $recipientTwo->id,
            'message' => 'Broadcast message',
        ]);

        Notification::assertSentTo($recipientOne, ChatMessageNotification::class);
        Notification::assertSentTo($recipientTwo, ChatMessageNotification::class);
    }

    public function test_chat_clear_requires_permission(): void
    {
        $tenant = Tenant::create([
            'name' => 'Tenant F',
            'slug' => 'tenant-f',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $this->withSession([TenantContext::SESSION_KEY => $tenant->id])
            ->actingAs($user)
            ->post('/chat/clear')
            ->assertForbidden();
    }

    public function test_chat_clear_deletes_tenant_history(): void
    {
        $tenant = Tenant::create([
            'name' => 'Tenant G',
            'slug' => 'tenant-g',
            'is_active' => true,
        ]);

        $otherTenant = Tenant::create([
            'name' => 'Tenant H',
            'slug' => 'tenant-h',
            'is_active' => true,
        ]);

        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $otherUser = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $foreignUser = User::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        Permission::create(['name' => 'chat-clear']);
        $admin->givePermissionTo(['chat-clear']);

        ChatMessage::create([
            'tenant_id' => $tenant->id,
            'sender_id' => $admin->id,
            'recipient_id' => $otherUser->id,
            'message' => 'Local message',
        ]);

        ChatMessage::create([
            'tenant_id' => $otherTenant->id,
            'sender_id' => $foreignUser->id,
            'recipient_id' => $foreignUser->id,
            'message' => 'Foreign message',
        ]);

        $this->withSession([TenantContext::SESSION_KEY => $tenant->id])
            ->actingAs($admin)
            ->post('/chat/clear')
            ->assertOk()
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseMissing('chat_messages', [
            'tenant_id' => $tenant->id,
            'message' => 'Local message',
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'tenant_id' => $otherTenant->id,
            'message' => 'Foreign message',
        ]);
    }
}
