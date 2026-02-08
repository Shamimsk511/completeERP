<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ColorentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         \App\Models\Colorent::insert([
       [ 'name' => 'COLORANT OC Ochre 1 L',           'stock' => 1, 'price' => 910.00 ],
    [ 'name' => 'COLORANT OR Reddish Yellow 1 L',  'stock' => 1, 'price' => 3370.00 ],
    [ 'name' => 'COLORANT BC Consent Blue 1 L',    'stock' => 1, 'price' => 2320.00 ],
    [ 'name' => 'COLORANT BF Diluted Blue 1 L',    'stock' => 1, 'price' => 1215.00 ],
    [ 'name' => 'COLORANT BR Tinting Black 1 L',   'stock' => 1, 'price' => 1225.00 ],
    [ 'name' => 'COLORANT GC Concentrte Green 1 L','stock' => 1, 'price' => 2595.00 ],
    [ 'name' => 'COLORANT GF Diluted Green 1 L',   'stock' => 1, 'price' => 2585.00 ],
    [ 'name' => 'COLORANT LM Greenish Yellow 1 L', 'stock' => 1, 'price' => 3020.00 ],
    [ 'name' => 'COLORANT MG Magenta 1 L',         'stock' => 1, 'price' => 3700.00 ],
    [ 'name' => 'COLORANT NS Medium Yellow 1 L',   'stock' => 1, 'price' => 1510.00 ],
    [ 'name' => 'COLORANT NT Concentrte Black 1 L','stock' => 1, 'price' => 945.00  ],
    [ 'name' => 'COLORANT RD Bright Red (Int) 1 L','stock' => 1, 'price' => 2140.00 ],
    [ 'name' => 'COLORANT RE Bright Red (Ext) 1 L','stock' => 1, 'price' => 4630.00 ],
    [ 'name' => 'COLORANT SP Oxide Red 1 L',       'stock' => 1, 'price' => 1845.00 ],
    [ 'name' => 'COLORANT VB Red Violet 1 L',      'stock' => 1, 'price' => 1635.00 ],
    [ 'name' => 'COLORANT WT White 1 L',           'stock' => 1, 'price' => 1370.00 ]
         ]);
}
}