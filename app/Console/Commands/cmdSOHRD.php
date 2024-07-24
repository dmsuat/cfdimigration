<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB; //For database transactions
use App\Models\LoadedSO;
use SplFileObject;

class cmdSOHRD extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cmd-s-o-h-r-d';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filepath = $this->ask('Enter the path to the CSV file:');
        $casepath = $this->ask('Enter the file you want to import');

        switch($casepath) {

            case "LOADEDSO":
                if (!file_exists($filepath)) {
                    $this->error("File not found: $filepath");
                    return;
                }

                $file = new SplFileObject($filepath, 'r');
                $file->setFlags(SplFileObject::READ_CSV);
                $file->setCsvControl('|'); // Pipe delimiter

                // Skip header row
                $file->current(); 
                $file->next();
        
                try {
                    
                    // Wrap the operation in a database transaction
                    $rowCount = 0;
                    DB::beginTransaction();
                    foreach($file as $row) {
                        if ($rowCount > 0) {
                            if ($row && !empty($row[0])) {
                                // Create and save using the Eloquent model
                                LoadedSO::create([
                                    'Distributor'       => $row[0] ?? null,
                                    'Salesman'          => $row[1] ?? null,
                                    'Docno'             => $row[2] ?? null,
                                    'Location'          => $row[3] ?? null,
                                    'OrderDate'         => $row[4] ?? null,
                                    'ReqDelDate'        => $row[5] ?? null,
                                    'PaymentTerm'       => $row[6] ?? null,
                                    'AccountCode'       => $row[7] ?? null,
                                    'ProductCode'       => $row[8] ?? null,
                                    'NumberofOrders'    => $row[9] ?? null,
                                    'OrderUOM'          => $row[10] ?? null,
                                    'SystemDate'        => $row[11] ?? null,
                                    'SystemUser'        => $row[12] ?? null,
                                    'Rownum'            => $row[13] ?? null,
                                    'LoadedBy'          => $row[14] ?? null,
                                    'DateLoaded'        => $row[15] ?? null,
                                    'Status'            => $row[16] ?? null,
                                    'SONo'              => $row[17] ?? null,
                                    'SendStatus'        => $row[18] ?? null,
                                    'Remarks'           => $row[19] ?? null
                                ]);
                            }
                        }
                        $rowCount++;
                    }
                    // Commit the transaction
                    DB::commit();
                    $this->info("Rows successfully inserted into SPM.LoadedSO: $rowCount");
                    // dd($counter);
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error inserting rows: {$e->getMessage()}");
                }
                break;
            
            case "ARTRAN":
                
                break;
            default :
                
        }
    }
}
