<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NIMSeeder extends Seeder
{
  public function run()
  {
    DB::table('nim')->insert([

      ['nim' => '11S20001'],
      ['nim' => '11S20002'],
      ['nim' => '11S20003'],
      ['nim' => '11S20004'],
      ['nim' => '11S20005'],
    ]);
  }
}
