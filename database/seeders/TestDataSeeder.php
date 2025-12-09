<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ServiceTemplate;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Skupiny produktů
        $barvy = ProductGroup::create(['name' => 'Barvy']);
        $pece = ProductGroup::create(['name' => 'Péče o vlasy']);
        $styling = ProductGroup::create(['name' => 'Styling']);
        $osetreni = ProductGroup::create(['name' => 'Ošetření']);

        // L'Oréal Professionnel produkty - Barvy
        Product::create([
            'name' => 'L\'Oréal INOA 7.0',
            'sku' => 'INOA-7.0',
            'product_group_id' => $barvy->id,
            'usage_type' => 'service',
            'package_size_grams' => 60,
            'stock_units' => 15.0,
            'min_units' => 5.0,
            'notes' => 'Permanentní barva bez amoniaku - světlá blond'
        ]);

        Product::create([
            'name' => 'L\'Oréal INOA 6.0',
            'sku' => 'INOA-6.0',
            'product_group_id' => $barvy->id,
            'usage_type' => 'service',
            'package_size_grams' => 60,
            'stock_units' => 12.0,
            'min_units' => 5.0,
            'notes' => 'Permanentní barva bez amoniaku - tmavá blond'
        ]);

        Product::create([
            'name' => 'L\'Oréal Dia Light 9.0',
            'sku' => 'DIA-9.0',
            'product_group_id' => $barvy->id,
            'usage_type' => 'service',
            'package_size_grams' => 50,
            'stock_units' => 8.0,
            'min_units' => 3.0,
            'notes' => 'Acidní barva pro šetrné barvení - velmi světlá blond'
        ]);

        Product::create([
            'name' => 'L\'Oréal Oxidant 6%',
            'sku' => 'OXI-6',
            'product_group_id' => $barvy->id,
            'usage_type' => 'service',
            'package_size_grams' => 1000,
            'stock_units' => 5.0,
            'min_units' => 2.0,
            'notes' => 'Krémový peroxid 20 Vol'
        ]);

        Product::create([
            'name' => 'L\'Oréal Oxidant 9%',
            'sku' => 'OXI-9',
            'product_group_id' => $barvy->id,
            'usage_type' => 'service',
            'package_size_grams' => 1000,
            'stock_units' => 4.0,
            'min_units' => 2.0,
            'notes' => 'Krémový peroxid 30 Vol'
        ]);

        Product::create([
            'name' => 'L\'Oréal Majirouge 6.60',
            'sku' => 'MAJI-6.60',
            'product_group_id' => $barvy->id,
            'usage_type' => 'service',
            'package_size_grams' => 50,
            'stock_units' => 6.0,
            'min_units' => 3.0,
            'notes' => 'Intenzivní červené odstíny'
        ]);

        // Péče o vlasy
        Product::create([
            'name' => 'L\'Oréal Série Expert Absolut Repair Shampoo',
            'sku' => 'AR-SHAMP-300',
            'product_group_id' => $pece->id,
            'usage_type' => 'both',
            'package_size_grams' => 300,
            'stock_units' => 8.0,
            'min_units' => 3.0,
            'notes' => 'Regenerační šampon pro poškozené vlasy'
        ]);

        Product::create([
            'name' => 'L\'Oréal Série Expert Absolut Repair Mask',
            'sku' => 'AR-MASK-250',
            'product_group_id' => $pece->id,
            'usage_type' => 'service',
            'package_size_grams' => 250,
            'stock_units' => 6.0,
            'min_units' => 2.0,
            'notes' => 'Intenzivní maska pro regeneraci vlasů'
        ]);

        Product::create([
            'name' => 'L\'Oréal Série Expert Silver Shampoo',
            'sku' => 'SIL-SHAMP-300',
            'product_group_id' => $pece->id,
            'usage_type' => 'both',
            'package_size_grams' => 300,
            'stock_units' => 5.0,
            'min_units' => 2.0,
            'notes' => 'Stříbrný šampon pro blond a šedivé vlasy'
        ]);

        // Styling
        Product::create([
            'name' => 'L\'Oréal Tecni.Art Fix Design',
            'sku' => 'TA-FIX-200',
            'product_group_id' => $styling->id,
            'usage_type' => 'both',
            'package_size_grams' => 200,
            'stock_units' => 10.0,
            'min_units' => 4.0,
            'notes' => 'Lokální fixační sprej'
        ]);

        Product::create([
            'name' => 'L\'Oréal Tecni.Art Volume Lift',
            'sku' => 'TA-VOL-250',
            'product_group_id' => $styling->id,
            'usage_type' => 'service',
            'package_size_grams' => 250,
            'stock_units' => 7.0,
            'min_units' => 3.0,
            'notes' => 'Sprej pro objem u kořínků'
        ]);

        Product::create([
            'name' => 'L\'Oréal Tecni.Art Pli Shaper',
            'sku' => 'TA-PLI-190',
            'product_group_id' => $styling->id,
            'usage_type' => 'service',
            'package_size_grams' => 190,
            'stock_units' => 5.0,
            'min_units' => 2.0,
            'notes' => 'Termo sprej pro tvarování'
        ]);

        // Ošetření
        Product::create([
            'name' => 'L\'Oréal Smartbond Kit',
            'sku' => 'SB-KIT',
            'product_group_id' => $osetreni->id,
            'usage_type' => 'service',
            'package_size_grams' => 500,
            'stock_units' => 3.0,
            'min_units' => 1.0,
            'notes' => 'Ochranné ošetření při barvení'
        ]);

        Product::create([
            'name' => 'L\'Oréal Steampod Serum',
            'sku' => 'SP-SERUM-50',
            'product_group_id' => $osetreni->id,
            'usage_type' => 'both',
            'package_size_grams' => 50,
            'stock_units' => 6.0,
            'min_units' => 2.0,
            'notes' => 'Sérum pro žehlení vlasů'
        ]);

        // Šablony úkonů
        ServiceTemplate::create(['name' => 'Melír', 'note' => 'Klasický melír s fólií', 'position' => 1]);
        ServiceTemplate::create(['name' => 'Celoplošné barvení', 'note' => 'Barvení celé délky vlasů', 'position' => 2]);
        ServiceTemplate::create(['name' => 'Odbarvení', 'note' => 'Kořínky', 'position' => 3]);
        ServiceTemplate::create(['name' => 'Střih dámský', 'note' => 'Střih + foukání', 'position' => 4]);
        ServiceTemplate::create(['name' => 'Střih pánský', 'note' => 'Klasický pánský střih', 'position' => 5]);
        ServiceTemplate::create(['name' => 'Regenerace', 'note' => 'Hloubková regenerace s maskou', 'position' => 6]);
        ServiceTemplate::create(['name' => 'Foukaná', 'note' => 'Mytí + foukaná', 'position' => 7]);
        ServiceTemplate::create(['name' => 'Steampod', 'note' => 'Žehlení s párou', 'position' => 8]);
        ServiceTemplate::create(['name' => 'Balayage', 'note' => 'Ručně malované melíry', 'position' => 9]);
        ServiceTemplate::create(['name' => 'Tónování', 'note' => 'Jemné tónování acidní barvou', 'position' => 10]);

        // Testovací klienti
        Client::create(['name' => 'Jana Nováková', 'phone' => '603 123 456', 'email' => 'jana@example.com', 'note' => 'Pravidelná zákaznice']);
        Client::create(['name' => 'Petra Svobodová', 'phone' => '604 234 567', 'email' => 'petra@example.com']);
        Client::create(['name' => 'Marie Dvořáková', 'phone' => '605 345 678', 'note' => 'Citlivá pokožka hlavy']);
        Client::create(['name' => 'Eva Černá', 'phone' => '606 456 789', 'email' => 'eva@example.com']);
        Client::create(['name' => 'Lucie Procházková', 'phone' => '607 567 890']);
    }
}
