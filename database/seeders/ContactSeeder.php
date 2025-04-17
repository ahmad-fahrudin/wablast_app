<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Contact;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $users = User::all();

        // Show progress information
        $this->command->info('Creating contacts for ' . $users->count() . ' users...');
        $bar = $this->command->getOutput()->createProgressBar($users->count());

        foreach ($users as $user) {
            // Prepare batch of contacts for better performance
            $contacts = [];

            for ($i = 1; $i <= 100; $i++) {
                $contacts[] = [
                    'user_id' => $user->id,
                    'name' => $faker->name,
                    'phone' => '08' . $faker->numerify('##########'),
                    'is_active' => $faker->boolean(90),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert in chunks for better performance
            foreach (array_chunk($contacts, 20) as $contactChunk) {
                Contact::insert($contactChunk);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->info("\nContacts created successfully!");
    }
}
