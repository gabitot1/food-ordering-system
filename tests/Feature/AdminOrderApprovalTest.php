<?php

use App\Models\Orders;
use App\Models\User;

function createOrder(array $attributes = []): Orders
{
    return Orders::create(array_merge([
        'order_number' => 'ORD-' . fake()->unique()->numerify('######'),
        'customer_name' => 'Test Customer',
        'address' => 'Sample Address',
        'contact_number' => '09123456789',
        'delivery_option' => 'pickup',
        'payment_method' => 'cash',
        'payment_status' => 'unpaid',
        'total' => 250,
        'status' => 'pending',
        'approval_status' => 'pending',
    ], $attributes));
}

it('allows an admin to approve an order', function () {
    $admin = User::factory()->create(['is_admin' => 1]);
    $order = createOrder();

    $response = $this->actingAs($admin)->patch(route('admin.orders.update', $order), [
        'status' => 'pending',
        'approval_status' => 'approved',
        'payment_status' => 'unpaid',
    ]);

    $response->assertRedirect();

    $order->refresh();

    expect($order->approval_status)->toBe('approved')
        ->and($order->approved_by)->toBe($admin->id)
        ->and($order->approved_at)->not->toBeNull()
        ->and($order->status)->toBe('pending');
});

it('blocks fulfillment updates before approval', function () {
    $admin = User::factory()->create(['is_admin' => 1]);
    $order = createOrder();

    $response = $this->actingAs($admin)->from(route('admin.orders'))->patch(route('admin.orders.update', $order), [
        'status' => 'preparing',
        'approval_status' => 'pending',
        'payment_status' => 'unpaid',
    ]);

    $response->assertSessionHasErrors('status');

    $order->refresh();

    expect($order->status)->toBe('pending')
        ->and($order->approval_status)->toBe('pending');
});

it('allows an admin to disapprove an order with a note', function () {
    $admin = User::factory()->create(['is_admin' => 1]);
    $order = createOrder();

    $response = $this->actingAs($admin)->patch(route('admin.orders.update', $order), [
        'status' => 'pending',
        'approval_status' => 'disapproved',
        'approval_note' => 'Kitchen cannot accept this order today.',
        'payment_status' => 'unpaid',
    ]);

    $response->assertRedirect();

    $order->refresh();

    expect($order->approval_status)->toBe('disapproved')
        ->and($order->status)->toBe('cancelled')
        ->and($order->approval_note)->toBe('Kitchen cannot accept this order today.')
        ->and($order->approved_by)->toBe($admin->id)
        ->and($order->approved_at)->not->toBeNull();
});
