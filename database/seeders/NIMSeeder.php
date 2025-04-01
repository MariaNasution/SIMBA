<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NIMSeeder extends Seeder
{
  public function run()
  {
    DB::table('nim')->insert([
      ['nim' => '11322003', 'nama' => 'Percobaan'],
      ['nim' => '11S20001', 'nama' => 'Samuel Adika Lumbantobing'],
      ['nim' => '11S20002', 'nama' => 'Yoel Ganda Aprilco Napitupulu'],
      ['nim' => '11S20003', 'nama' => 'Reinhard Hottua S'],
      ['nim' => '11S20004', 'nama' => 'Samuel Immanuel Sibuea'],
      ['nim' => '11320005', 'nama' => 'Lasria Sri Rezeki Rajagukguk'],
    ]);
  }
}
