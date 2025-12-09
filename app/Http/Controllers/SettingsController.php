<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = DB::table('app_settings')->pluck('value', 'key');
        
        return view('settings.index', [
            'salonName' => $settings['salon_name'] ?? '',
            'salonAddress' => $settings['salon_address'] ?? '',
            'salonPhone' => $settings['salon_phone'] ?? '',
            'salonEmail' => $settings['salon_email'] ?? '',
            'theme' => $settings['theme'] ?? 'dark',
        ]);
    }
    
    public function update(Request $request)
    {
        $data = $request->validate([
            'salon_name' => 'nullable|string|max:255',
            'salon_address' => 'nullable|string|max:255',
            'salon_phone' => 'nullable|string|max:50',
            'salon_email' => 'nullable|email|max:255',
        ]);
        
        foreach ($data as $key => $value) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }
        
        return redirect()->back()->with('status', 'Nastavení uloženo');
    }
    
    public function backup()
    {
        try {
            $dbPath = database_path('database.sqlite');
            $backupPath = storage_path('app/backups');
            
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sqlite';
            copy($dbPath, $backupPath . '/' . $filename);
            
            return response()->download($backupPath . '/' . $filename)->deleteFileAfterSend();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Chyba při vytváření zálohy: ' . $e->getMessage());
        }
    }
    
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sqlite,db',
        ]);
        
        $tempBackup = database_path('database_temp_backup.sqlite');
        
        try {
            $dbPath = database_path('database.sqlite');
            $uploadedFile = $request->file('backup_file');
            
            // Backup current database first
            copy($dbPath, $tempBackup);
            
            // Replace with uploaded file
            $uploadedFile->move(database_path(), 'database.sqlite');
            
            // Remove temp backup
            @unlink($tempBackup);
            
            return redirect()->route('settings.index')->with('status', 'Databáze obnovena');
        } catch (\Exception $e) {
            // Restore from temp if failed
            if (file_exists($tempBackup ?? '')) {
                copy($tempBackup, $dbPath);
                @unlink($tempBackup);
            }
            
            return redirect()->back()->with('error', 'Chyba při obnově databáze: ' . $e->getMessage());
        }
    }
    
    public function downloadTemplate()
    {
        $csv = "Název produktu;Skupina;Jednotka;Počáteční stav;Poznámka\n";
        $csv .= "Barva na vlasy - Hnědá;Barvy;ks;10;Příklad produktu\n";
        $csv .= "Šampon regenerační;Péče o vlasy;ks;5;\n";
        $csv .= "Kondicionér;Péče o vlasy;ks;8;\n";
        $csv .= "Gel na vlasy;Styling;g;500;Gramový produkt\n";
        
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="import_produktu_sablona.csv"')
            ->header('Content-Transfer-Encoding', 'binary');
    }
    
    public function importProducts(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);
        
        try {
            $file = $request->file('import_file');
            $content = file_get_contents($file->getRealPath());
            
            // Convert to UTF-8 if needed
            if (!mb_check_encoding($content, 'UTF-8')) {
                $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1250');
            }
            
            $lines = explode("\n", $content);
            $imported = 0;
            $errors = [];
            
            // Skip header
            array_shift($lines);
            
            foreach ($lines as $index => $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                $data = str_getcsv($line, ';');
                
                if (count($data) < 4) {
                    $errors[] = "Řádek " . ($index + 2) . ": Neplatný formát";
                    continue;
                }
                
                [$name, $groupName, $unit, $stock, $note] = array_pad($data, 5, '');
                
                // Validate
                if (empty($name)) {
                    $errors[] = "Řádek " . ($index + 2) . ": Chybí název produktu";
                    continue;
                }
                
                if (!in_array($unit, ['ks', 'g'])) {
                    $errors[] = "Řádek " . ($index + 2) . ": Jednotka musí být 'ks' nebo 'g'";
                    continue;
                }
                
                // Find or create group
                $group = null;
                if (!empty($groupName)) {
                    $group = DB::table('product_groups')->where('name', $groupName)->first();
                    if (!$group) {
                        $groupId = DB::table('product_groups')->insertGetId([
                            'name' => $groupName,
                            'accent_color' => '#7dd3fc',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $group = (object)['id' => $groupId];
                    }
                }
                
                // Create product
                DB::table('products')->insert([
                    'name' => $name,
                    'product_group_id' => $group->id ?? null,
                    'unit' => $unit,
                    'stock' => (float)$stock,
                    'note' => $note,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $imported++;
            }
            
            $message = "Importováno $imported produktů";
            if (!empty($errors)) {
                $message .= ". Chyby: " . implode(', ', array_slice($errors, 0, 3));
            }
            
            return redirect()->back()->with('status', $message);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Chyba při importu: ' . $e->getMessage());
        }
    }
}
