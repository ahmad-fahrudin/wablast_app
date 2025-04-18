<?php

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\SubscriptionDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create subscription plans
        $subscriptions = [
            [
                'name' => 'Free',
                'price' => 0,
                'limit' => 100,
                'is_active' => true
            ],
            [
                'name' => 'Small',
                'price' => 50000,
                'limit' => 1000,
                'is_active' => true
            ],
            [
                'name' => 'Medium',
                'price' => 120000,
                'limit' => 5000,
                'is_active' => true
            ],
            [
                'name' => 'Large',
                'price' => 250000,
                'limit' => 15000,
                'is_active' => true
            ],
            [
                'name' => 'Enterprise',
                'price' => 500000,
                'limit' => 50000,
                'is_active' => true
            ],
        ];

        // Define all possible features
        $allFeatures = [
            'Unlimited Device Connection' => [false, false, true, true, true],
            'Auto Reply Messages' => [false, true, true, true, true],
            'Bulk Messaging' => [false, true, true, true, true],
            'Contact Management' => [true, true, true, true, true],
            'Message Templates' => [false, true, true, true, true],
            'Message History' => [true, true, true, true, true],
            'Message Scheduling' => [false, false, true, true, true],
            'Message Analytics' => [false, false, false, true, true],
            'API Access' => [false, false, true, true, true],
            'Priority Support' => [false, false, false, true, true],
            'Custom Webhooks' => [false, false, false, true, true],
            'Media Messages' => [false, true, true, true, true],
            'Group Management' => [false, false, true, true, true],
            'Message Broadcasting' => [false, false, true, true, true],
            'Multi-User Access' => [false, false, false, false, true],
            'Message Export' => [false, false, true, true, true],
            'Custom Reporting' => [false, false, false, true, true],
            'SMS Fallback' => [false, false, false, false, true],
            'Custom Branding' => [false, false, false, false, true],
            '24/7 Dedicated Support' => [false, false, false, false, true],
        ];

        foreach ($subscriptions as $index => $subscription) {
            $sub = Subscription::create($subscription);

            // Get the first 15 features
            $features = array_slice($allFeatures, 0, 15);

            $detailIndex = 0;
            foreach ($features as $feature => $availability) {
                SubscriptionDetail::create([
                    'subscription_id' => $sub->id,
                    'item' => $feature,
                    'is_checklist' => $availability[$index]
                ]);
                $detailIndex++;
            }
        }
    }
}
