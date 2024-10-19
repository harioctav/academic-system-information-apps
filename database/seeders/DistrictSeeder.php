<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DistrictSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Path ke file json
    $json = File::get(public_path('assets/json/districts.json'));

    // Decode JSON ke array
    $data = json_decode($json, true);

    $chunks = array_chunk($data, 1000);
    foreach ($chunks as $chunk) {
      foreach ($chunk as &$item) {
        $item['uuid'] = (string) Str::uuid();
        $item['created_at'] = now();
        $item['updated_at'] = now();
      }

      // Save to database
      District::insert($chunk);
    }
  }
}
